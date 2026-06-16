<?php

namespace Tests\Feature\App;

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\InvoiceService;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppPaymentTest extends TenancyTestCase
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
            $inv = $svc->create(['items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10000]]]);
            $svc->issue($inv);
            Payment::create([
                'reference' => 'REF1', 'amount' => $inv->total, 'status' => Payment::CREATED,
                'payable_type' => Invoice::class, 'payable_id' => $inv->id,
            ]);
        });
        tenancy()->end();
        $this->actingAs(Membership::where('email', 'ana@acme.ao')->firstOrFail());
    }

    public function test_lista_de_cobrancas_renderiza(): void
    {
        $this->get('/app/cobrancas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Payments/Index')
                ->has('payments.data', 1)
                ->has('summary')
            );
    }
}
