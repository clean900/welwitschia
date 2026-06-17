<?php

namespace Tests\Feature\Agt;

use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\InvoiceService;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Tests\TenancyTestCase;

class SaftExportTest extends TenancyTestCase
{
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->tenant = (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda', 'slug' => 'acme', 'plan' => 'business',
            'admin_name' => 'Ana', 'admin_email' => 'ana@acme.ao', 'admin_password' => 'password123',
        ]);
        $this->tenant->run(function () {
            $svc = new InvoiceService(new AgtNumberGenerator());
            $inv = $svc->create(['items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]]]);
            $svc->issue($inv);
        });
        tenancy()->end();
        $this->actingAs(Membership::where('email', 'ana@acme.ao')->firstOrFail());
    }

    public function test_exporta_saft_xml(): void
    {
        $response = $this->get('/app/saft');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('content-type'));
        $response->assertSee('<AuditFile>', false);
        $response->assertSee('FT WLW/', false); // número da factura no XML
    }
}
