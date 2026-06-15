<?php

namespace App\Listeners;

use App\Events\Invoice\InvoiceIssued;
use App\Services\Accounting\AccountingService;

/**
 * Lançamento de venda ao emitir factura (PGC Angola):
 *   Dr 31 Clientes (total)
 *   Cr 71 Vendas (subtotal)
 *   Cr 3443 Estado-IVA (IVA liquidado)
 */
class RecordSaleLedgerEntry
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function handle(InvoiceIssued $event): void
    {
        $invoice = $event->invoice;

        $lines = [
            ['account' => '31', 'debit' => (float) $invoice->total],
            ['account' => '71', 'credit' => (float) $invoice->subtotal],
        ];

        if ((float) $invoice->iva_amount > 0) {
            $lines[] = ['account' => '3443', 'credit' => (float) $invoice->iva_amount];
        }

        $this->accounting->post(
            "Venda — factura {$invoice->number}",
            $lines,
            $invoice->issued_at,
            $invoice,
            $invoice->number,
        );
    }
}
