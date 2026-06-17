<?php

namespace App\Services\Agt;

use App\Models\AgtSetting;
use App\Models\Invoice;

/**
 * Constrói o objecto `document` do registarFactura a partir de uma Invoice.
 * // VALIDAR campos/códigos (taxCode, unitOfMeasure, etc.) com a spec da AGT.
 */
class AgtDocumentBuilder
{
    public function __construct(protected AgtFeClient $client)
    {
    }

    public function build(Invoice $invoice, AgtSetting $setting, string $documentNo): array
    {
        $document = [
            'documentNo' => $documentNo,
            'documentStatus' => 'N',
            'documentType' => $invoice->document_type ?: 'FT',
            'documentDate' => optional($invoice->issued_at)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'systemEntryDate' => now()->toIso8601String(),
            'taxRegistrationNumber' => $setting->tax_registration_number,
            'customerTaxID' => $invoice->customer_nif ?? '',
            'customerCountry' => $invoice->customer_country ?: 'AO',
            'companyName' => $invoice->customer_name ?? 'Consumidor Final',
            'lines' => $invoice->items->values()->map(fn ($it, $i) => [
                'lineNumber' => (string) ($i + 1),
                'productDescription' => $it->description,
                'quantity' => (string) (float) $it->quantity,
                'unitOfMeasure' => 'UN',
                'unitPrice' => (string) (float) $it->unit_price,
                'creditAmount' => (string) (float) $it->line_total,
                'taxes' => [[
                    'taxType' => 'IVA',
                    'taxCountryRegion' => 'AO',
                    'taxCode' => 'NOR',
                    'taxPercentage' => (string) (float) $it->iva_rate,
                ]],
            ])->all(),
            'documentTotals' => [
                'taxPayable' => (string) (float) $invoice->iva_amount,
                'netTotal' => (string) (float) $invoice->subtotal,
                'grossTotal' => (string) (float) $invoice->total,
            ],
        ];

        $document['jwsDocumentSignature'] = $this->client->signDocument($document, (string) $setting->private_key);

        return $document;
    }
}
