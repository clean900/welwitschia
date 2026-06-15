<?php

namespace App\Services\Payment;

use App\Events\Payment\PaymentReconciled;
use App\Events\Payment\PaymentRequiresManualReview;
use App\Models\AuditLog;
use App\Models\Payment;

/**
 * Reconciliação: compara o valor esperado com o valor pago no callback.
 * diff ≤ 0.01 AOA → RECONCILED. diff > 0.01 → MANUAL_REVIEW.
 */
class ReconciliationEngine
{
    public const TOLERANCE = 0.01;

    public function __construct(protected PaymentStateEngine $stateEngine)
    {
    }

    public function reconcile(Payment $payment): Payment
    {
        $expected = (float) $payment->amount;
        $paid = (float) ($payment->paid_amount ?? $payment->webhook_payload['amount'] ?? 0);
        $diff = round(abs($expected - $paid), 2);

        if ($diff <= self::TOLERANCE) {
            $this->stateEngine->transitionToReconciled($payment);
            AuditLog::record('payment.reconciled', [
                'payment_id' => $payment->id,
                'expected' => $expected,
                'paid' => $paid,
            ], Payment::class, $payment->id);
            PaymentReconciled::dispatch($payment);

            return $payment;
        }

        $this->stateEngine->transitionToManualReview($payment);
        AuditLog::record('payment.manual_review', [
            'payment_id' => $payment->id,
            'expected' => $expected,
            'paid' => $paid,
            'difference' => $diff,
        ], Payment::class, $payment->id);
        PaymentRequiresManualReview::dispatch($payment, $diff);

        return $payment;
    }
}
