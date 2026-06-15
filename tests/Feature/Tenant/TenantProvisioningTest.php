<?php

namespace Tests\Feature\Tenant;

use App\Models\User;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TenancyTestCase;

class TenantProvisioningTest extends TenancyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
    }

    public function test_provisiona_tenant_com_schema_admin_e_role(): void
    {
        $tenant = (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda',
            'slug' => 'acme',
            'plan' => 'business',
            'admin_name' => 'Ana Admin',
            'admin_email' => 'ana@acme.ao',
            'admin_password' => 'password123',
        ]);

        $this->assertDatabaseHas('tenants', ['id' => 'acme', 'status' => 'trial']);
        $this->assertDatabaseHas('subscriptions', ['tenant_id' => 'acme']);

        $tenant->run(function () {
            $this->assertSame(1, User::count());
            $admin = User::first();
            $this->assertTrue($admin->hasRole('tenant_admin'));
            $this->assertSame(9, Role::count());
        });
    }

    public function test_endpoint_central_regista_tenant(): void
    {
        $response = $this->postJson('/api/v1/register-tenant', [
            'company_name' => 'Beta SA',
            'slug' => 'beta',
            'plan' => 'starter',
            'admin_name' => 'Bruno',
            'admin_email' => 'bruno@beta.ao',
            'admin_password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('tenant.id', 'beta')
            ->assertJsonPath('tenant.subdomain', 'beta.welwitschia.ao');
    }

    public function test_slug_duplicado_e_rejeitado(): void
    {
        $svc = new TenantProvisioningService();
        $payload = [
            'company_name' => 'Gamma', 'slug' => 'gamma', 'plan' => 'starter',
            'admin_name' => 'G', 'admin_email' => 'g@gamma.ao', 'admin_password' => 'password123',
        ];
        $svc->register($payload);

        $this->expectException(ValidationException::class);
        $svc->register($payload);
    }
}
