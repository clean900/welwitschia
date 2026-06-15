<?php

namespace Tests\Feature\Tenant;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Tests\TenancyTestCase;

class TenantIsolationTest extends TenancyTestCase
{
    public function test_dados_de_um_tenant_nao_aparecem_noutro(): void
    {
        $alpha = $this->makeTenant('alpha');
        $beta = $this->makeTenant('beta');

        $alpha->run(function () {
            Invoice::create(['number' => 'FT/2026/1', 'total' => 1000, 'status' => 'issued']);
        });

        $beta->run(function () {
            // Mesmo número de factura — permitido porque os schemas são isolados.
            Invoice::create(['number' => 'FT/2026/1', 'total' => 2000, 'status' => 'issued']);
        });

        $alpha->run(function () {
            $this->assertSame(1, Invoice::count());
            $this->assertEquals('1000.00', Invoice::first()->total);
        });

        $beta->run(function () {
            $this->assertSame(1, Invoice::count());
            $this->assertEquals('2000.00', Invoice::first()->total);
        });
    }

    public function test_middleware_activa_o_schema_correcto(): void
    {
        $gamma = $this->makeTenant('gamma');

        $gamma->run(function () {
            $searchPath = DB::selectOne('show search_path');
            $this->assertStringContainsString('tenant_gamma', json_encode($searchPath));
        });

        // Fora de contexto de tenant, não há tenant activo.
        $this->assertNull(tenant());
    }
}
