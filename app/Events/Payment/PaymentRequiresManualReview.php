<?php

namespace App\Events\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Diferença de valor > 0.01 AOA → revisão manual.
 */
class PaymentRequiresManualReview
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment, public float $difference)
    {
    }
}
