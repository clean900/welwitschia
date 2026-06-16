<?php

namespace Tests\Feature\App;

use App\Models\Employee;
use App\Models\Membership;
use App\Models\Payroll;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppHrTest extends TenancyTestCase
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

    public function test_lista_de_colaboradores_renderiza(): void
    {
        $this->get('/app/colaboradores')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('App/Hr/Employees')->has('employees'));
    }

    public function test_adiciona_colaborador(): void
    {
        $this->post('/app/colaboradores', ['name' => 'João Pedro', 'position' => 'Técnico', 'base_salary' => 200000])
            ->assertRedirect();

        $count = $this->tenant->run(fn () => Employee::count());
        $this->assertSame(1, $count);
    }

    public function test_processa_folha_e_mostra_recibos(): void
    {
        $this->tenant->run(fn () => Employee::create(['name' => 'João', 'base_salary' => 200000, 'status' => 'active']));

        $this->post('/app/salarios', ['year' => 2026, 'month' => 6])->assertRedirect();

        [$payrolls, $slips] = $this->tenant->run(fn () => [Payroll::count(), Payroll::first()?->payslips()->count()]);
        $this->assertSame(1, $payrolls);
        $this->assertSame(1, $slips);
    }
}
