<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Services\Payment\PaymentStateEngine;
use App\Services\Payment\ReconciliationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Processa um callback ProxyPay na fila de alta prioridade 'payments'.
 * Idempotente — um callback duplicado é no-op.
 */
class ProcessProxyPayCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'payments';
    public $tries = 3;
    public $timeout = 60;

    public function __construct(public array $payload)
    {
    }

    public function handle(PaymentStateEngine $engine, ReconciliationEngine $reconciliation): void
    {
        $payment = $engine->processCallback($this->payload);

        // Só reconcilia o que acabou de ficar PAID neste callback.
        if ($payment->status === Payment::PAID) {
            $reconciliation->reconcile($payment);
        }
    }
}
