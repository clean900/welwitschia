<?php

namespace Tests\Feature\Admin;

use App\Models\PlatformAdmin;
use App\Models\SmsGateway;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AdminCompanyTest extends TenancyTestCase
{
    private PlatformAdmin $admin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->admin = PlatformAdmin::create([
            'name' => 'Bráulio', 'email' => 'admin@welwitschia.ao', 'password' => Hash::make('password123'),
        ]);
        $this->tenant = (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda', 'slug' => 'acme', 'plan' => 'business',
            'admin_name' => 'Ana', 'admin_email' => 'ana@acme.ao', 'admin_password' => 'password123',
        ]);
        tenancy()->end();
    }

    public function test_detalhe_da_empresa_renderiza(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get('/admin/empresas/acme')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Company')
                ->where('company.name', 'Acme Lda')
                ->where('sms', null)
            );
    }

    public function test_admin_activa_sms_da_empresa(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/empresas/acme/sms', ['api_key' => 'telco_key_123', 'sender_id' => 'ACME'])
            ->assertRedirect();

        $gateway = $this->tenant->run(fn () => SmsGateway::where('provider', 'telcosms')->first());
        $this->assertNotNull($gateway);
        $this->assertTrue($gateway->active);
        $this->assertSame('ACME', $gateway->sender_id);
    }

    public function test_admin_configura_emissor_agt(): void
    {
        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($res, $pem);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/empresas/acme/agt', [
                'tax_registration_number' => '5000413178',
                'establishment_number' => '001',
                'private_key' => $pem,
            ])->assertRedirect();

        $setting = $this->tenant->run(fn () => \App\Models\AgtSetting::where('active', true)->first());
        $this->assertNotNull($setting);
        $this->assertSame('5000413178', $setting->tax_registration_number);
        // Chave decriptada pelo cast e utilizável (o TrimStrings corta o \n final, irrelevante).
        $this->assertStringContainsString('BEGIN PRIVATE KEY', $setting->private_key);
        $this->assertNotFalse(openssl_pkey_get_private($setting->private_key));
    }
}
