<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class TenantAppTest extends TenancyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
    }

    private function provisionAcme(): Tenant
    {
        return (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda',
            'slug' => 'acme',
            'plan' => 'starter',
            'admin_name' => 'Ana Admin',
            'admin_email' => 'ana@acme.ao',
            'admin_password' => 'password123',
        ]);
    }

    public function test_login_renderiza_no_subdominio(): void
    {
        $this->provisionAcme();

        $this->get('http://acme.localhost/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Login')
                ->where('company', 'Acme Lda')
            );
    }

    public function test_convidado_e_redireccionado_para_login_do_tenant(): void
    {
        $this->provisionAcme();

        $this->get('http://acme.localhost/')
            ->assertRedirect('http://acme.localhost/login');
    }

    public function test_login_valido_autentica_o_utilizador_do_tenant(): void
    {
        $this->provisionAcme();

        $this->post('http://acme.localhost/login', [
            'email' => 'ana@acme.ao',
            'password' => 'password123',
        ])->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_login_invalido_falha(): void
    {
        $this->provisionAcme();

        $this->post('http://acme.localhost/login', [
            'email' => 'ana@acme.ao',
            'password' => 'errada',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_dashboard_renderiza_para_autenticado(): void
    {
        $tenant = $this->provisionAcme();
        $user = $tenant->run(fn () => User::first());

        $this->actingAs($user)
            ->get('http://acme.localhost/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Dashboard')
                ->where('company', 'Acme Lda')
                ->has('metrics')
                ->has('onboarding')
            );
    }
}
