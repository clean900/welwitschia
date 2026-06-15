<?php

namespace App\Events\Invoice;

use App\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Disparado quando uma factura é emitida → lançamento de venda no razão.
 */
class InvoiceIssued
{
    use Dispatchable, SerializesModels;

    public function __construct(public Invoice $invoice)
    {
    }
}
