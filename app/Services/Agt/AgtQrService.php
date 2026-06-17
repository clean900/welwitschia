<?php

namespace App\Services\Agt;

use App\Models\Invoice;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * QR Code fiscal da factura (faturação certificada AGT).
 * // VALIDAR COM AGT — campos e ordem do payload conforme especificação oficial.
 */
class AgtQrService
{
    public function dataUri(Invoice $invoice, array $company): string
    {
        $payload = implode('*', [
            'NIF:' . ($company['nif'] ?? ''),
            'DOC:' . $invoice->number,
            'DATA:' . optional($invoice->issued_at)->format('Y-m-d'),
            'TOTAL:' . number_format((float) $invoice->total, 2, '.', ''),
            'IVA:' . number_format((float) $invoice->iva_amount, 2, '.', ''),
            'HASH:' . InvoiceSigningService::shortCode($invoice->hash),
        ]);

        $writer = new Writer(new ImageRenderer(new RendererStyle(120), new SvgImageBackEnd()));

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($payload));
    }
}
