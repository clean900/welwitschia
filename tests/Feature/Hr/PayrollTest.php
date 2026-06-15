<?php

namespace Tests\Feature\Hr;

use App\Models\Account;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\Payroll;
use App\Services\Accounting\AccountingService;
use App\Services\Fiscal\IrtCalculator;
use App\Services\Hr\PayrollService;
use Database\Seeders\Tenant\PgcAngolaSeeder;
use Illuminate\Validation\ValidationException;
use Tests\TenancyTestCase;

class PayrollTest extends TenancyTestCase
{
    private function service(): PayrollService
    {
        return new PayrollService(new IrtCalculator(), new AccountingService());
    }

    public function test_processa_folha_calcula_irt_inss_e_liquido(): void
    {
        $this->makeTenant('hr1')->run(function () {
            (new PgcAngolaSeeder())->run();
            Employee::create(['name' => 'João', 'base_salary' => 200000, 'status' => 'active']);
            Employee::create(['name' => 'Maria', 'base_salary' => 100000, 'allowances' => 20000, 'status' => 'active']);

            $payroll = $this->service()->process(2026, 6);
            $this->assertSame(2, $payroll->payslips->count());

            $irt = new IrtCalculator();
            foreach ($payroll->payslips as $slip) {
                $gross = (float) $slip->gross;
                $this->assertEqualsWithDelta($irt->inssEmployee($gross), (float) $slip->inss_employee, 0.001);
                $this->assertEqualsWithDelta($irt->calculate($gross), (float) $slip->irt, 0.001);
                $this->assertEqualsWithDelta(
                    $gross - $irt->inssEmployee($gross) - $irt->calculate($gross),
                    (float) $slip->net,
                    0.001,
                );
            }

            // Totais consistentes
            $this->assertEqualsWithDelta(320000, (float) $payroll->total_gross, 0.001); // 200000 + 120000
        });
    }

    public function test_folha_gera_lancamento_contabilistico_equilibrado(): void
    {
        $this->makeTenant('hr2')->run(function () {
            (new PgcAngolaSeeder())->run();
            Employee::create(['name' => 'João', 'base_salary' => 200000, 'status' => 'active']);

            $this->service()->process(2026, 6);

            $entry = JournalEntry::where('source_type', Payroll::class)->first();
            $this->assertNotNull($entry);
            $this->assertTrue($entry->isBalanced());

            $totals = (new AccountingService())->trialBalance();
            $this->assertSame($totals['debit'], $totals['credit']);

            // Custos com pessoal (62) = bruto + INSS patronal (8%) = 200000 + 16000
            $this->assertEqualsWithDelta(216000, Account::where('code', '62')->first()->balance(), 0.001);
        });
    }

    public function test_folha_do_mesmo_periodo_nao_repete(): void
    {
        $this->makeTenant('hr3')->run(function () {
            (new PgcAngolaSeeder())->run();
            Employee::create(['name' => 'João', 'base_salary' => 100000, 'status' => 'active']);

            $this->service()->process(2026, 6);

            $this->expectException(ValidationException::class);
            $this->service()->process(2026, 6);
        });
    }
}
