<?php

namespace App\Services\Agt;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Cliente da API de Facturação Eletrónica da AGT (REST).
 * // VALIDAR formatos/campos contra a homologação (sifphml).
 */
class AgtFeClient
{
    public function __construct(protected JwsSigner $signer)
    {
    }

    /** Solicita uma série de numeração à AGT para um tipo de documento. */
    public function solicitarSerie(string $taxNumber, string $documentType, string $year, string $establishment, string $issuerKeyPem): array
    {
        $requestId = (string) Str::uuid();

        return $this->http()->post('/solicitarSerie', [
            'schemaVersion' => config('agt.schema_version'),
            'submissionUUID' => (string) Str::uuid(),
            'taxRegistrationNumber' => $taxNumber,
            'submissionTimeStamp' => now()->toIso8601String(),
            'softwareInfo' => $this->softwareInfo(),
            'seriesYear' => $year,
            'documentType' => $documentType,
            'establishmentNumber' => $establishment,
            'jwsSignature' => $this->signer->sign([
                'taxRegistrationNumber' => $taxNumber,
                'requestID' => $requestId,
            ], $issuerKeyPem),
            'seriesContingencyIndicator' => 'N',
        ])->json();
    }

    /** Submete uma ou mais facturas (máx. 30) à AGT. */
    public function registarFactura(string $taxNumber, array $documents): array
    {
        return $this->http()->post('/registarFactura', [
            'schemaVersion' => config('agt.schema_version'),
            'submissionUUID' => (string) Str::uuid(),
            'taxRegistrationNumber' => $taxNumber,
            'submissionTimeStamp' => now()->toIso8601String(),
            'softwareInfo' => $this->softwareInfo(),
            'numberOfEntries' => (string) count($documents),
            'documents' => $documents,
        ])->json();
    }

    /** Consulta o estado de validação de um documento. */
    public function obterEstado(string $taxNumber, string $documentNo): array
    {
        return $this->http()->post('/obterEstado', [
            'schemaVersion' => config('agt.schema_version'),
            'submissionUUID' => (string) Str::uuid(),
            'taxRegistrationNumber' => $taxNumber,
            'submissionTimeStamp' => now()->toIso8601String(),
            'softwareInfo' => $this->softwareInfo(),
            'documentNo' => $documentNo,
        ])->json();
    }

    /**
     * Assina a chave do documento (jwsDocumentSignature) com a chave privada do emissor.
     * Campos exigidos pela spec, por ordem.
     */
    public function signDocument(array $document, string $issuerKeyPem): string
    {
        return $this->signer->sign([
            'documentNo' => $document['documentNo'],
            'taxRegistrationNumber' => $document['taxRegistrationNumber'],
            'documentType' => $document['documentType'],
            'documentDate' => $document['documentDate'],
            'customerTaxID' => $document['customerTaxID'] ?? '',
            'customerCountry' => $document['customerCountry'] ?? 'AO',
            'companyName' => $document['companyName'] ?? '',
            'documentTotals' => $document['documentTotals'],
        ], $issuerKeyPem);
    }

    protected function softwareInfo(): array
    {
        $detail = [
            'productId' => config('agt.product_id'),
            'productVersion' => config('agt.product_version'),
            'softwareValidationNumber' => config('agt.software_cert'),
        ];

        return [
            'softwareInfoDetail' => $detail,
            'jwsSoftwareSignature' => $this->signer->sign($detail, (string) config('agt.private_key')),
        ];
    }

    protected function http(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('agt.base_url'), '/'))
            ->withHeaders([
                'Username' => config('agt.username'),
                'Password' => config('agt.password'),
            ])
            ->acceptJson()
            ->timeout(20);
    }
}
