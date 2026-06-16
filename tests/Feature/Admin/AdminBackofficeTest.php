<?php

namespace Tests\Feature\Admin;

use App\Models\PlatformAdmin;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AdminBackofficeTest extends TenancyTestCase
{
    private PlatformAdmin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->admin = PlatformAdmin::create([
            'name' => 'Bráulio', 'email' => 'admin@welwitschia.ao', 'password' => Hash::make('password123'),
        ]);
        (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda', 'slug' => 'acme', 'plan' => 'business',
            'admin_name' => 'Ana', 'admin_email' => 'ana@acme.ao', 'admin_password' => 'password123',
        ]);
        tenancy()->end();
    }

    public function test_login_do_admin_renderiza(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Login'));
    }

    public function test_convidado_e_redireccionado_para_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_login_valido_do_admin(): void
    {
        $this->post('/admin/login', ['email' => 'admin@welwitschia.ao', 'password' => 'password123'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin, 'admin');
    }

    public function test_painel_lista_empresas_e_metricas(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('metrics.companies', 1)
                ->has('companies', 1)
                ->has('byPlan')
            );
    }

    public function test_suspender_empresa(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/empresas/acme/suspender')
            ->assertRedirect();

        $this->assertDatabaseHas('tenants', ['id' => 'acme', 'status' => 'suspended']);
    }
}
