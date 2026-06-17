<?php

namespace App\Services\Purchase;

use App\Models\AuditLog;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Ordens de compra: criar → confirmar → receber (entrada de stock).
 */
class PurchaseOrderService
{
    public function __construct(protected StockService $stock)
    {
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $supplier = ! empty($data['supplier_id']) ? Supplier::find($data['supplier_id']) : null;

            $order = PurchaseOrder::create([
                'number' => 'OC-' . Str::upper(Str::random(8)),
                'supplier_id' => $supplier?->id,
                'supplier_name' => $supplier?->name ?? ($data['supplier_name'] ?? null),
                'status' => 'draft',
            ]);

            $subtotal = 0;
            $ivaTotal = 0;
            foreach ($data['items'] as $item) {
                $product = ! empty($item['product_id']) ? Product::find($item['product_id']) : null;
                $qty = (float) $item['quantity'];
                $price = (float) ($item['unit_price'] ?? 0);
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

    public function confirm(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Só rascunhos podem ser confirmados.']);
        }

        $order->update(['status' => 'confirmed']);
        AuditLog::record('purchase_order.confirmed', ['number' => $order->number], PurchaseOrder::class, $order->id);

        return $order;
    }

    public function receive(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== 'confirmed') {
            throw ValidationException::withMessages(['status' => 'Confirme a ordem antes de receber.']);
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_id && ($product = Product::find($item->product_id))) {
                    $this->stock->move($product, 'entrada', (float) $item->quantity, 'Compra ' . $order->number);
                }
            }

            $order->update(['status' => 'received']);
            AuditLog::record('purchase_order.received', ['number' => $order->number], PurchaseOrder::class, $order->id);

            return $order;
        });
    }
}
