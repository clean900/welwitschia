<?php

namespace App\Services\Sales;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\Invoice\InvoiceService;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Encomendas de venda: criar → confirmar → faturar (gera factura + saída de stock).
 */
class SalesOrderService
{
    public function __construct(
        protected InvoiceService $invoices,
        protected StockService $stock,
    ) {
    }

    public function create(array $data): SalesOrder
    {
        return DB::transaction(function () use ($data) {
            $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;

            $order = SalesOrder::create([
                'number' => 'ENC-' . Str::upper(Str::random(8)),
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->name ?? ($data['customer_name'] ?? null),
                'status' => 'draft',
            ]);

            $subtotal = 0;
            $ivaTotal = 0;
            foreach ($data['items'] as $item) {
                $product = ! empty($item['product_id']) ? Product::find($item['product_id']) : null;
                $qty = (float) $item['quantity'];
                $price = (float) ($item['unit_price'] ?? $product?->price ?? 0);
                $rate = (float) ($item['iva_rate'] ?? 14);
                $line = round($qty * $price, 2);

                $order->items()->create([
                    'product_id' => $product?->id,
                    'description' => $item['description'] ?? $product?->name ?? 'Artigo',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'iva_rate' => $rate,
                    'line_total' => $line,
                ]);

                $subtotal += $line;
                $ivaTotal += iva_amount($line, $rate);
            }

            $order->update([
                'subtotal' => $subtotal,
                'iva_amount' => $ivaTotal,
                'total' => round($subtotal + $ivaTotal, 2),
            ]);

            return $order->fresh('items');
        });
    }

    public function confirm(SalesOrder $order): SalesOrder
    {
        if ($order->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Só rascunhos podem ser confirmados.']);
        }

        $order->update(['status' => 'confirmed']);
        AuditLog::record('sales_order.confirmed', ['number' => $order->number], SalesOrder::class, $order->id);

        return $order;
    }

    public function invoice(SalesOrder $order): Invoice
    {
        if ($order->status !== 'confirmed') {
            throw ValidationException::withMessages(['status' => 'Confirme a encomenda antes de facturar.']);
        }

        return DB::transaction(function () use ($order) {
            $invoice = $this->invoices->create([
                'customer_name' => $order->customer_name,
                'items' => $order->items->map(fn ($i) => [
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'iva_rate' => (float) $i->iva_rate,
                ])->all(),
            ]);
            $this->invoices->issue($invoice);

            // Saída de stock para as linhas com produto.
            foreach ($order->items as $item) {
                if ($item->product_id && ($product = Product::find($item->product_id))) {
                    $this->stock->move($product, 'saida', (float) $item->quantity, 'Venda ' . $invoice->number);
                }
            }

            $order->update(['status' => 'invoiced', 'invoice_id' => $invoice->id]);
            AuditLog::record('sales_order.invoiced', ['number' => $order->number, 'invoice' => $invoice->number], SalesOrder::class, $order->id);

            return $invoice;
        });
    }
}
