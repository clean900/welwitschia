<?php

namespace Tests\Feature\App;

use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class SingleDomainAppTest extends TenancyTestCase
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
            'plan' => 'business',
            'admin_name' => 'Ana Silva',
            'admin_email' => 'ana@acme.ao',
            'admin_password' => 'password123',
        ]);
    }

    public function test_login_unico_renderiza(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Auth/CompanyLogin'));
    }

    public function test_workspace_exige_autenticacao(): void
    {
        $this->get('/app')->assertRedirect('/login');
    }

    public function test_login_valido_entra_no_workspace(): void
    {
        $this->provisionAcme();

        $this->post('/login', ['email' => 'ana@acme.ao', 'password' => 'password123'])
            ->assertRedirect(route('app.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_invalido_falha(): void
    {
        $this->provisionAcme();

        $this->post('/login', ['email' => 'ana@acme.ao', 'password' => 'errada'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_email_duplicado_e_rejeitado_no_registo(): void
    {
        $this->provisionAcme();

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new TenantProvisioningService())->register([
            'company_name' => 'Outra', 'slug' => 'outra', 'plan' => 'starter',
            'admin_name' => 'X', 'admin_email' => 'ana@acme.ao', 'admin_password' => 'password123',
        ]);
    }

    public function test_dashboard_mostra_dados_da_empresa(): void
    {
        $this->provisionAcme();
        $membership = Membership::where('email', 'ana@acme.ao')->firstOrFail();

        $this->actingAs($membership)
            ->get('/app')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Tenant/Dashboard')
                ->where('company', 'Acme Lda')
                ->has('metrics')
                ->has('onboarding')
            );
    }
}
