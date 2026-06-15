<?php

namespace App\Services\Payment;

use App\Exceptions\InvalidPaymentTransition;
use App\Models\AuditLog;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Motor de estados do pagamento + idempotência.
 *
 * CREATED → PENDING → PAID → RECONCILED
 *                   ↘ REJECTED ↘ EXPIRED
 *          PAID → MANUAL_REVIEW (diff > 0.01 AOA)
 *
 * Idempotência: pg_advisory_xact_lock por referência + idempotency_key único.
 * Um callback ProxyPay duplicado NUNCA cria/transiciona dois pagamentos.
 */
class PaymentStateEngine
{
    /** Transições válidas da state machine. */
    public const TRANSITIONS = [
        Payment::CREATED => [Payment::PENDING, Payment::PAID, Payment::REJECTED, Payment::EXPIRED],
        Payment::PENDING => [Payment::PAID, Payment::REJECTED, Payment::EXPIRED],
        Payment::PAID => [Payment::RECONCILED, Payment::MANUAL_REVIEW],
        Payment::MANUAL_REVIEW => [Payment::RECONCILED, Payment::REJECTED],
        Payment::RECONCILED => [],
        Payment::REJECTED => [],
        Payment::EXPIRED => [],
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Aplica uma transição validada. Lança se for inválida.
     */
    public function transition(Payment $payment, string $to, array $context = []): Payment
    {
        $from = $payment->status;
        if (! $this->canTransition($from, $to)) {
            throw InvalidPaymentTransition::between($from, $to);
        }

        $payment->status = $to;
        $payment->fill($context);
        $payment->save();

        AuditLog::record('payment.transition', [
            'payment_id' => $payment->id,
            'from' => $from,
            'to' => $to,
            'reference' => $payment->reference,
        ], Payment::class, $payment->id);

        return $payment;
    }

    /**
     * Processa um callback ProxyPay de forma idempotente.
     * Devolve o pagamento; um callback repetido é no-op (devolve o existente).
     */
    public function processCallback(array $payload): Payment
    {
        $reference = (string) ($payload['reference'] ?? $payload['amount'] ?? '');
        $idempotencyKey = (string) ($payload['id'] ?? hash('sha256', json_encode($payload)));

        return DB::transaction(function () use ($payload, $reference, $idempotencyKey) {
            // Lock por referência — serializa callbacks concorrentes do mesmo pagamento.
            DB::statement('SELECT pg_advisory_xact_lock(hashtext(?))', [$reference]);

            // Já processámos exactamente este evento? → no-op idempotente.
            $already = Payment::where('idempotency_key', $idempotencyKey)->first();
            if ($already) {
                return $already;
            }

            $payment = Payment::where('reference', $reference)->first();
            if (! $payment) {
                $payment = Payment::create([
                    'reference' => $reference,
                    'amount' => (float) ($payload['amount'] ?? 0),
                    'currency' => $payload['currency'] ?? 'AOA',
                    'status' => Payment::CREATED,
                ]);
            }

            // Já está pago/reconciliado e o evento é outro → não reabrir.
            if (in_array($payment->status, [Payment::PAID, Payment::RECONCILED], true)) {
                $payment->idempotency_key = $idempotencyKey;
                $payment->save();

                return $payment;
            }

            $paidAmount = (float) ($payload['amount'] ?? $payment->amount);

            return $this->transition($payment, Payment::PAID, [
                'idempotency_key' => $idempotencyKey,
                'webhook_payload' => $payload,
                'paid_amount' => $paidAmount,
                'paid_at' => now(),
                'entity' => $payload['entity'] ?? $payment->entity,
            ]);
        });
    }

    // --- Atalhos de transição ---

    public function markPending(Payment $p): Payment
    {
        return $this->transition($p, Payment::PENDING);
    }

    public function markRejected(Payment $p): Payment
    {
        return $this->transition($p, Payment::REJECTED);
    }

    public function markExpired(Payment $p): Payment
    {
        return $this->transition($p, Payment::EXPIRED);
    }

    public function transitionToReconciled(Payment $p): Payment
    {
        return $this->transition($p, Payment::RECONCILED, ['reconciled_at' => now()]);
    }

    public function transitionToManualReview(Payment $p): Payment
    {
        return $this->transition($p, Payment::MANUAL_REVIEW);
    }
}
