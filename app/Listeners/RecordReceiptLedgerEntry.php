<?php

namespace App\Listeners;

use App\Events\Payment\PaymentReconciled;
use App\Models\Invoice;
use App\Services\Accounting\AccountingService;

/**
 * Lançamento de recebimento ao reconciliar um pagamento de factura:
 *   Dr 12 Depósitos à Ordem (valor pago)
 *   Cr 31 Clientes (valor pago)
 */
class RecordReceiptLedgerEntry
{
    public function __construct(protected AccountingService $accounting)
    {
    }

    public function handle(PaymentReconciled $event): void
    {
        $payment = $event->payment;
        $payable = $payment->payable;

        if (! $payable instanceof Invoice) {
            return;
        }

        $amount = (float) ($payment->paid_amount ?? $payment->amount);

        $this->accounting->post(
            "Recebimento — factura {$payable->number}",
            [
                ['account' => '12', 'debit' => $amount],
                ['account' => '31', 'credit' => $amount],
            ],
            $payment->reconciled_at ?? now(),
            $payment,
            $payable->number,
        );
    }
}
