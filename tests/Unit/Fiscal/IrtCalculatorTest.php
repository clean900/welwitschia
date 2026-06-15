<?php

namespace Tests\Unit\Fiscal;

use App\Services\Fiscal\IrtCalculator;
use PHPUnit\Framework\TestCase;

class IrtCalculatorTest extends TestCase
{
    private IrtCalculator $irt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->irt = new IrtCalculator();
    }

    public function test_salario_zero_nao_paga_irt_nem_inss(): void
    {
        $this->assertSame(0.0, $this->irt->calculate(0));
        $this->assertSame(0.0, $this->irt->inssEmployee(0));
    }

    public function test_inss_trabalhador_3_por_cento(): void
    {
        $this->assertEqualsWithDelta(3000, $this->irt->inssEmployee(100000), 0.001);
        $this->assertEqualsWithDelta(7500, $this->irt->inssEmployee(250000), 0.001);
    }

    public function test_inss_empregador_8_por_cento(): void
    {
        $this->assertEqualsWithDelta(8000, $this->irt->inssEmployer(100000), 0.001);
    }

    public function test_inss_total_11_por_cento(): void
    {
        $gross = 200000;
        $total = $this->irt->inssEmployee($gross) + $this->irt->inssEmployer($gross);
        $this->assertEqualsWithDelta($gross * 0.11, $total, 0.001);
    }

    public function test_primeiro_escalao_isento(): void
    {
        // Base após INSS abaixo de 100.000 → isento de IRT.
        $this->assertSame(0.0, $this->irt->calculate(90000));
    }

    public function test_irt_e_progressivo(): void
    {
        $low = $this->irt->calculate(200000);
        $mid = $this->irt->calculate(500000);
        $high = $this->irt->calculate(1500000);

        $this->assertGreaterThan(0, $low);
        $this->assertGreaterThan($low, $mid);
        $this->assertGreaterThan($mid, $high);
    }

    public function test_liquido_e_menor_que_bruto(): void
    {
        $gross = 300000;
        $net = $this->irt->netSalary($gross);
        $this->assertLessThan($gross, $net);
        $this->assertGreaterThan(0, $net);
    }

    public function test_taxa_marginal_dentro_dos_limites(): void
    {
        $this->assertSame(0.0, $this->irt->marginalRate(50000));
        $this->assertSame(0.24, $this->irt->marginalRate(5000000));
    }
}
