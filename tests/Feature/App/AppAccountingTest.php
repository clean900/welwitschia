<?php

namespace Tests\Feature\App;

use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\InvoiceService;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppAccountingTest extends TenancyTestCase
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
        // Gera um lançamento (venda) ao emitir uma factura.
        $this->tenant->run(function () {
            $svc = new InvoiceService(new AgtNumberGenerator());
            $inv = $svc->create(['items' => [['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]]]);
            $svc->issue($inv);
        });
        tenancy()->end();
        $this->actingAs(Membership::where('email', 'ana@acme.ao')->firstOrFail());
    }

    public function test_balancete_renderiza_equilibrado(): void
    {
        $this->get('/app/contabilidade')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Accounting/TrialBalance')
                ->has('accounts')
                ->where('totals.debit', fn ($d) => $d > 0)
                ->where('totals.credit', fn ($c) => $c > 0)
            );
    }

    public function test_razao_lista_lancamentos(): void
    {
        $this->get('/app/contabilidade/razao')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Accounting/Journal')
                ->has('entries.data', 1)
            );
    }
}
