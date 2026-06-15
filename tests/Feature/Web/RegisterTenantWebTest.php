<?php

namespace Tests\Feature\Web;

use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class RegisterTenantWebTest extends TenancyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
    }

    public function test_wizard_renderiza_com_planos(): void
    {
        $this->get('/registar-empresa')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('RegisterTenant')
                ->has('plans', 4)
            );
    }

    public function test_submissao_cria_empresa_e_redirecciona_para_sucesso(): void
    {
        $response = $this->post('/registar-empresa', [
            'company_name' => 'Acme Lda',
            'slug' => 'acme',
            'plan' => 'starter',
            'admin_name' => 'Ana',
            'admin_email' => 'ana@acme.ao',
            'admin_password' => 'password123',
            'admin_password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/registar-empresa/sucesso/acme');
        $this->assertDatabaseHas('tenants', ['id' => 'acme']);

        $this->get('/registar-empresa/sucesso/acme')
            ->assertInertia(fn (Assert $page) => $page
                ->component('RegisterTenantSuccess')
                ->where('subdomain', 'acme.welwitschia.ao')
            );
    }

    public function test_password_nao_confirmada_falha(): void
    {
        $this->post('/registar-empresa', [
            'company_name' => 'Beta', 'slug' => 'beta', 'plan' => 'starter',
            'admin_name' => 'B', 'admin_email' => 'b@beta.ao',
            'admin_password' => 'password123', 'admin_password_confirmation' => 'diferente',
        ])->assertSessionHasErrors('admin_password');
    }
}
