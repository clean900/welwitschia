<?php

namespace App\Listeners;

use App\Events\Invoice\InvoiceIssued;
use App\Services\Agt\AgtSubmissionService;
use Illuminate\Support\Facades\Log;

/**
 * Ao emitir uma factura, submete-a à AGT — apenas se a integração estiver configurada.
 */
class SubmitInvoiceToAgt
{
    public function __construct(protected AgtSubmissionService $agt)
    {
    }

    public function handle(InvoiceIssued $event): void
    {
        if (! $this->agt->enabled()) {
            return;
        }

        try {
            $this->agt->submit($event->invoice);
        } catch (\Throwable $e) {
            // Nunca bloquear a emissão; a factura fica por submeter para reprocessamento.
            $event->invoice->update(['agt_status' => 'ERRO']);
            Log::error('Submissão AGT falhou', ['invoice' => $event->invoice->number, 'error' => $e->getMessage()]);
        }
    }
}
