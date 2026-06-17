<?php

namespace App\Listeners;

use App\Events\Payment\PaymentReconciled;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Services\Automation\WebhookDispatcher;

/**
 * Quando um pagamento ligado a uma factura é reconciliado, marca a factura paga.
 */
class MarkInvoicePaid
{
    public function handle(PaymentReconciled $event): void
    {
        $payment = $event->payment;
        $payable = $payment->payable; // morphTo

        if ($payable instanceof Invoice && $payable->status !== 'paid') {
            $payable->update(['status' => 'paid']);

            AuditLog::record('invoice.paid', [
                'number' => $payable->number,
                'payment_id' => $payment->id,
            ], Invoice::class, $payable->id);

            WebhookDispatcher::send('payment.reconciled', [
                'invoice' => $payable->number,
                'reference' => $payment->reference,
                'amount' => (float) ($payment->paid_amount ?? $payment->amount),
            ]);
        }
    }
}
