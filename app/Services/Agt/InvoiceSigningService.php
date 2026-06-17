<?php

namespace App\Services\Agt;

use App\Models\Invoice;

/**
 * Assinatura digital encadeada das faturas (faturação certificada AGT).
 *
 * // VALIDAR COM AGT — o formato exacto da string assinada, o algoritmo e a
 * // chave devem seguir a especificação técnica oficial da AGT. Esta é a
 * // estrutura técnica (modelo encadeado tipo Portugal) pronta a mapear.
 */
class InvoiceSigningService
{
    /**
     * Assina a factura encadeando-a à anterior e guarda hash + previous_hash.
     */
    public function sign(Invoice $invoice): void
    {
        $previous = Invoice::whereNotNull('hash')
            ->where('id', '<', $invoice->id)
            ->orderByDesc('id')
            ->value('hash') ?? '';

        // string canónica: data;numero;total;hash_anterior  (// VALIDAR formato AGT)
        $data = sprintf(
            '%s;%s;%s;%s',
            optional($invoice->issued_at)->format('Y-m-d'),
            $invoice->number,
            number_format((float) $invoice->total, 2, '.', ''),
            $previous,
        );

        $signature = '';
        openssl_sign($data, $signature, $this->privateKey(), OPENSSL_ALGO_SHA256);

        $invoice->forceFill([
            'previous_hash' => $previous,
            'hash' => base64_encode($signature),
        ])->save();
    }

    /**
     * Código curto a imprimir no documento (caracteres 1,11,21,31 do hash) — modelo AGT/PT.
     */
    public static function shortCode(?string $hash): string
    {
        if (! $hash) {
            return '';
        }

        return collect([0, 10, 20, 30])
            ->map(fn ($i) => $hash[$i] ?? '')
            ->implode('');
    }

    protected function privateKey(): \OpenSSLAsymmetricKey
    {
        $pem = config('agt.private_key') ?: $this->developmentKey();

        return openssl_pkey_get_private($pem);
    }

    /**
     * Chave RSA de TESTE persistida em storage. NÃO é válida para a AGT.
     */
    protected function developmentKey(): string
    {
        // Caminho central (não afectado pela tenancy) — a chave do software é única.
        $path = base_path('storage/agt-dev-key.pem');

        if (! file_exists($path)) {
            $res = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            openssl_pkey_export($res, $pem);
            file_put_contents($path, $pem);
        }

        return (string) file_get_contents($path);
    }
}
