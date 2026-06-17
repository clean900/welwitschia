<?php

namespace App\Services\Agt;

use App\Models\AgtSeries;
use App\Models\AgtSetting;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Gere as séries atribuídas pela AGT e aloca o próximo documentNo.
 * Corre em contexto de tenant.
 */
class AgtSeriesService
{
    public function __construct(protected AgtFeClient $client)
    {
    }

    public function nextDocumentNo(string $documentType): string
    {
        $setting = AgtSetting::where('active', true)->firstOrFail();
        $year = (int) now()->format('Y');

        return DB::transaction(function () use ($setting, $documentType, $year) {
            $series = AgtSeries::where('document_type', $documentType)
                ->where('year', $year)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $series || ! $series->hasCapacity()) {
                $series = $this->requestSeries($setting, $documentType, $year);
            }

            $series->current_number++;
            $series->save();

            // Formato documentNo (SAF-T(AO)): "<tipo> <série>/<sequencial>".
            // // VALIDAR formato exacto com a homologação da AGT.
            return sprintf('%s %s/%d', $documentType, $series->series_code, $series->current_number);
        });
    }

    protected function requestSeries(AgtSetting $setting, string $documentType, int $year): AgtSeries
    {
        $response = $this->client->solicitarSerie(
            $setting->tax_registration_number,
            $documentType,
            (string) $year,
            $setting->establishment_number,
            (string) $setting->private_key,
        );

        $result = $response['seriesFEResult'] ?? null;
        if (! $result || ($response['resultCode'] ?? '') !== 'OK') {
            throw new RuntimeException('AGT recusou a série: ' . json_encode($response['errorList'] ?? $response));
        }

        return AgtSeries::create([
            'document_type' => $documentType,
            'year' => $year,
            'series_code' => $result['seriesCode'],
            'authorized_qty' => (int) ($result['authorizedQuantity'] ?? 0),
            'current_number' => 0,
            'establishment_number' => $setting->establishment_number,
            'status' => 'active',
        ]);
    }
}
