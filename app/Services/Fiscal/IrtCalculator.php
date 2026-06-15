<?php

namespace App\Services\Fiscal;

/**
 * Cálculo do IRT (Imposto sobre o Rendimento do Trabalho) — Angola, Grupo A.
 *
 * // VALIDAR COM CONSULTOR FISCAL AO — tabela e taxas devem ser confirmadas
 * // contra a tabela em vigor publicada pela AGT antes de uso em produção.
 *
 * Base de incidência = rendimento bruto − contribuição INSS do trabalhador (3%).
 * Tabela progressiva por escalões com parcela fixa + taxa sobre o excesso.
 */
class IrtCalculator
{
    /** Taxa INSS suportada pelo trabalhador. */
    public const INSS_EMPLOYEE_RATE = 0.03; // 3%

    /** Taxa INSS suportada pela entidade empregadora. */
    public const INSS_EMPLOYER_RATE = 0.08; // 8%

    /**
     * Escalões IRT mensais (AOA). Cada linha: [limite_superior, parcela_fixa, taxa_excesso].
     * limite_superior = null → último escalão (sem topo).
     * // VALIDAR COM CONSULTOR FISCAL AO
     */
    protected const BRACKETS = [
        [100000, 0, 0.00],
        [150000, 0, 0.13],
        [200000, 6500, 0.16],
        [300000, 14500, 0.18],
        [500000, 32500, 0.19],
        [1000000, 70500, 0.20],
        [1500000, 170500, 0.21],
        [2000000, 275500, 0.22],
        [2500000, 385500, 0.23],
        [null, 500500, 0.24],
    ];

    /** Contribuição INSS do trabalhador (3%). */
    public function inssEmployee(float $gross): float
    {
        return round(max(0, $gross) * self::INSS_EMPLOYEE_RATE, 2);
    }

    /** Contribuição INSS da entidade empregadora (8%). */
    public function inssEmployer(float $gross): float
    {
        return round(max(0, $gross) * self::INSS_EMPLOYER_RATE, 2);
    }

    /** Base de incidência do IRT = bruto − INSS trabalhador. */
    public function taxableBase(float $gross): float
    {
        return round(max(0, $gross) - $this->inssEmployee($gross), 2);
    }

    /** IRT devido sobre um rendimento bruto mensal. */
    public function calculate(float $gross): float
    {
        if ($gross <= 0) {
            return 0.0;
        }

        $base = $this->taxableBase($gross);
        $lowerBound = 0;

        foreach (self::BRACKETS as [$upper, $fixed, $rate]) {
            if ($upper === null || $base <= $upper) {
                $excess = max(0, $base - $lowerBound);

                return round($fixed + $excess * $rate, 2);
            }
            $lowerBound = $upper;
        }

        return 0.0;
    }

    /** Taxa marginal aplicável à base indicada. */
    public function marginalRate(float $base): float
    {
        $lowerBound = 0;
        foreach (self::BRACKETS as [$upper, $fixed, $rate]) {
            if ($upper === null || $base <= $upper) {
                return $rate;
            }
            $lowerBound = $upper;
        }

        return 0.0;
    }

    /** Líquido a receber = bruto − INSS trabalhador − IRT. */
    public function netSalary(float $gross): float
    {
        return round($gross - $this->inssEmployee($gross) - $this->calculate($gross), 2);
    }
}
