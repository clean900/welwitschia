<?php

namespace App\Services\Hr;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\Accounting\AccountingService;
use App\Services\Fiscal\IrtCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Processa a folha salarial mensal: bruto, INSS (3%), IRT, líquido por colaborador,
 * e lança a contabilidade dos salários (PGC Angola).
 * // VALIDAR COM CONSULTOR FISCAL AO
 */
class PayrollService
{
    public function __construct(
        protected IrtCalculator $irt,
        protected AccountingService $accounting,
    ) {
    }

    public function process(int $year, int $month): Payroll
    {
        if (Payroll::where('year', $year)->where('month', $month)->exists()) {
            throw ValidationException::withMessages(['period' => "A folha de {$month}/{$year} já foi processada."]);
        }

        return DB::transaction(function () use ($year, $month) {
            $payroll = Payroll::create([
                'year' => $year,
                'month' => $month,
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            $totals = ['gross' => 0.0, 'inss' => 0.0, 'inss_er' => 0.0, 'irt' => 0.0, 'net' => 0.0];

            foreach (Employee::active()->get() as $employee) {
                $gross = $employee->grossSalary();
                $inss = $this->irt->inssEmployee($gross);
                $inssEr = $this->irt->inssEmployer($gross);
                $irt = $this->irt->calculate($gross);
                $net = round($gross - $inss - $irt, 2);

                $payroll->payslips()->create([
                    'employee_id' => $employee->id,
                    'base_salary' => $employee->base_salary,
                    'allowances' => $employee->allowances,
                    'gross' => $gross,
                    'inss_employee' => $inss,
                    'inss_employer' => $inssEr,
                    'irt' => $irt,
                    'net' => $net,
                ]);

                $totals['gross'] += $gross;
                $totals['inss'] += $inss;
                $totals['inss_er'] += $inssEr;
                $totals['irt'] += $irt;
                $totals['net'] += $net;
            }

            $payroll->update([
                'total_gross' => round($totals['gross'], 2),
                'total_inss' => round($totals['inss'], 2),
                'total_irt' => round($totals['irt'], 2),
                'total_net' => round($totals['net'], 2),
            ]);

            if ($totals['gross'] > 0) {
                $this->postLedger($payroll, $totals, $year, $month);
            }

            AuditLog::record('payroll.processed', [
                'period' => "{$month}/{$year}",
                'employees' => $payroll->payslips()->count(),
                'total_net' => round($totals['net'], 2),
            ], Payroll::class, $payroll->id);

            return $payroll->load('payslips');
        });
    }

    /**
     * Lançamento PGC:
     *   Dr 62 Custos com o Pessoal (bruto + INSS patronal)
     *   Cr 33 Remunerações a Pagar (líquido)
     *   Cr 3442 Estado-IRT (IRT retido)
     *   Cr 35 INSS a Pagar (INSS trabalhador + patronal)
     */
    protected function postLedger(Payroll $payroll, array $totals, int $year, int $month): void
    {
        $this->accounting->post(
            "Processamento de salários {$month}/{$year}",
            [
                ['account' => '62', 'debit' => round($totals['gross'] + $totals['inss_er'], 2)],
                ['account' => '33', 'credit' => round($totals['net'], 2)],
                ['account' => '3442', 'credit' => round($totals['irt'], 2)],
                ['account' => '35', 'credit' => round($totals['inss'] + $totals['inss_er'], 2)],
            ],
            $payroll->processed_at,
            $payroll,
            "SAL-{$year}-{$month}",
        );
    }
}
