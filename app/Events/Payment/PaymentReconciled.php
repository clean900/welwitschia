<?php

namespace App\Events\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Pagamento reconciliado com sucesso → dispara lançamento contabilístico (M25).
 */
class PaymentReconciled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment)
    {
    }
}
