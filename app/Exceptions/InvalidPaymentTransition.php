<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidPaymentTransition extends RuntimeException
{
    public static function between(string $from, string $to): self
    {
        return new self("Transição de pagamento inválida: {$from} → {$to}.");
    }
}
