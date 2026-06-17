<?php

namespace App\Services\Agt;

use App\Models\AgtSetting;
use App\Models\AuditLog;
use App\Models\Invoice;

/**
 * Orquestra a submissão de uma factura à AGT: série → assinatura → registarFactura.
 */
class AgtSubmissionService
{
    public function __construct(
        protected AgtFeClient $client,
        protected AgtSeriesService $series,
        protected AgtDocumentBuilder $builder,
    ) {
    }

    /** A submissão só ocorre se houver credenciais + emissor configurado. */
    public function enabled(): bool
    {
        return (bool) config('agt.username') && AgtSetting::where('active', true)->exists();
    }

    public function submit(Invoice $invoice): array
    {
        $setting = AgtSetting::where('active', true)->firstOrFail();
        $documentType = $invoice->document_type ?: 'FT';

        $documentNo = $this->series->nextDocumentNo($documentType);
        $document = $this->builder->build($invoice, $setting, $documentNo);

        $response = $this->client->registarFactura($setting->tax_registration_number, [$document]);
        $errors = $response['errorList'] ?? [];

        $invoice->update([
            'number' => $documentNo,                      // adopta a numeração da série AGT
            'agt_request_id' => $response['requestID'] ?? null,
            'agt_status' => empty($errors) ? 'SUBMETIDA' : 'REJEITADA',
            'agt_submitted_at' => now(),
        ]);

        AuditLog::record('agt.submitted', [
            'documentNo' => $documentNo,
            'requestID' => $response['requestID'] ?? null,
            'errors' => $errors,
        ], Invoice::class, $invoice->id);

        return $response;
    }
}
