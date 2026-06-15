<?php

use App\Services\Fiscal\IrtCalculator;

if (! function_exists('format_currency')) {
    /**
     * Formata um valor em AOA no formato angolano: 1.234.567,89 Kz.
     */
    function format_currency(float $value, string $suffix = 'Kz'): string
    {
        return number_format($value, 2, ',', '.') . ' ' . $suffix;
    }
}

if (! function_exists('irt_rate')) {
    /**
     * Taxa marginal de IRT para a base de incidência indicada.
     * // VALIDAR COM CONSULTOR FISCAL AO
     */
    function irt_rate(float $taxableBase): float
    {
        return (new IrtCalculator())->marginalRate($taxableBase);
    }
}

if (! function_exists('irt_amount')) {
    /** IRT devido sobre um rendimento bruto mensal. */
    function irt_amount(float $gross): float
    {
        return (new IrtCalculator())->calculate($gross);
    }
}

if (! function_exists('inss_employee')) {
    /** Contribuição INSS do trabalhador (3%). */
    function inss_employee(float $gross): float
    {
        return (new IrtCalculator())->inssEmployee($gross);
    }
}

if (! function_exists('iva_amount')) {
    /**
     * Valor do IVA sobre uma base. Taxa normal Angola = 14%.
     * // VALIDAR COM CONSULTOR FISCAL AO
     */
    function iva_amount(float $base, float $rate = 14.0): float
    {
        return round(max(0, $base) * ($rate / 100), 2);
    }
}
