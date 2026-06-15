<?php

namespace App\Services\Invoice;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

/**
 * Gera números de documento sequenciais no formato AGT (Angola).
 *
 * // VALIDAR COM CONSULTOR FISCAL AO — o formato/série deve ser confirmado
 * // contra as regras de facturação certificada da AGT.
 *
 * Formato: "FT WLW/2026/0001" (tipo SERIE/ano/sequencial). Atómico via lock.
 */
class AgtNumberGenerator
{
    public function next(string $prefix = 'FT', string $series = 'WLW'): string
    {
        $year = (int) now()->format('Y');

        return DB::transaction(function () use ($prefix, $series, $year) {
            $sequence = NumberSequence::where('prefix', $prefix)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = NumberSequence::create(['prefix' => $prefix, 'year' => $year, 'last_number' => 0]);
                // Re-lock após criação para serializar concorrência.
                $sequence = NumberSequence::where('id', $sequence->id)->lockForUpdate()->first();
            }

            $sequence->last_number++;
            $sequence->save();

            return sprintf('%s %s/%d/%04d', $prefix, $series, $year, $sequence->last_number);
        });
    }
}
