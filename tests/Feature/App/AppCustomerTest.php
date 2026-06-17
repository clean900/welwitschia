<?php

namespace Tests\Feature\App;

use App\Models\Customer;
use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppCustomerTest extends TenancyTestCase
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
        tenancy()->end();
        $this->actingAs(Membership::where('email', 'ana@acme.ao')->firstOrFail());
    }

    public function test_lista_de_clientes_renderiza(): void
    {
        $this->get('/app/clientes')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('App/Customers/Index')->has('customers'));
    }

    public function test_adiciona_cliente(): void
    {
        $this->post('/app/clientes', ['name' => 'Cliente Alfa', 'nif' => '5000000000', 'credit_limit' => 50000])
            ->assertRedirect();

        $this->assertSame(1, $this->tenant->run(fn () => Customer::count()));
    }

    public function test_remove_cliente(): void
    {
        $id = $this->tenant->run(fn () => Customer::create(['name' => 'X', 'status' => 'active'])->id);

        $this->delete("/app/clientes/{$id}")->assertRedirect();

        $this->assertSame(0, $this->tenant->run(fn () => Customer::count()));
    }
}
