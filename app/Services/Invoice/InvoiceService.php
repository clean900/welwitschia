<?php

namespace App\Services\Invoice;

use App\Events\Invoice\InvoiceIssued;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Services\Automation\WebhookDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * CRUD de facturas (schema do tenant). Calcula IVA (14% Angola) e emite com
 * numeração AGT. // VALIDAR COM CONSULTOR FISCAL AO
 */
class InvoiceService
{
    public function __construct(protected AgtNumberGenerator $numbers)
    {
    }

    /**
     * @param  array{customer_name?:string, customer_nif?:string, items: array<int, array{description:string, quantity:float, unit_price:float, iva_rate?:float}>}  $data
     */
    public function create(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'number' => 'RASCUNHO-' . Str::upper(Str::random(8)),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_nif' => $data['customer_nif'] ?? null,
                'status' => 'draft',
                'currency' => 'AOA',
            ]);

            $subtotal = 0;
            $ivaTotal = 0;

            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $rate = (float) ($item['iva_rate'] ?? 14);
                $lineTotal = round($qty * $price, 2);

                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'iva_rate' => $rate,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;
                $ivaTotal += iva_amount($lineTotal, $rate);
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'iva_amount' => $ivaTotal,
                'total' => round($subtotal + $ivaTotal, 2),
            ]);

            return $invoice->fresh('items');
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Só rascunhos podem ser emitidos.']);
        }

        $invoice->update([
            'number' => $this->numbers->next('FT'),
            'status' => 'issued',
            'issued_at' => now(),
            'due_at' => now()->addDays(15),
        ]);

        // Assinatura digital encadeada (faturação certificada AGT).
        app(\App\Services\Agt\InvoiceSigningService::class)->sign($invoice);

        AuditLog::record('invoice.issued', [
            'number' => $invoice->number,
            'total' => (float) $invoice->total,
        ], Invoice::class, $invoice->id);

        InvoiceIssued::dispatch($invoice);
        WebhookDispatcher::send('invoice.issued', [
            'number' => $invoice->number,
            'total' => (float) $invoice->total,
            'customer' => $invoice->customer_name,
        ]);

        return $invoice;
    }

    public function cancel(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'paid') {
            throw ValidationException::withMessages(['status' => 'Uma factura paga não pode ser cancelada.']);
        }

        $invoice->update(['status' => 'cancelled']);

        AuditLog::record('invoice.cancelled', ['number' => $invoice->number], Invoice::class, $invoice->id);

        return $invoice;
    }
}
