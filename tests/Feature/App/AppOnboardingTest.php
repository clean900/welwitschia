<?php

namespace Tests\Feature\App;

use App\Models\Membership;
use App\Models\PaymentGateway;
use App\Models\SmsGateway;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppOnboardingTest extends TenancyTestCase
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

    public function test_pagina_renderiza_com_estado_por_configurar(): void
    {
        $this->get('/app/onboarding')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('App/Onboarding/Index')
                ->where('status.proxypay', false)
                ->where('status.sms', false)
            );
    }

    public function test_configura_proxypay(): void
    {
        $this->post('/app/onboarding/proxypay', [
            'api_key' => 'pk_test_123', 'environment' => 'sandbox',
        ])->assertRedirect();

        $active = $this->tenant->run(fn () => PaymentGateway::where('provider', 'proxypay')->where('active', true)->exists());
        $this->assertTrue($active);
    }

    public function test_activa_sms_com_sender_id(): void
    {
        $this->post('/app/onboarding/sms', [
            'api_key' => 'sms_key_xyz', 'sender_id' => 'ACME',
        ])->assertRedirect();

        $gateway = $this->tenant->run(fn () => SmsGateway::where('provider', 'telcosms')->first());
        $this->assertNotNull($gateway);
        $this->assertTrue($gateway->active);
        $this->assertSame('ACME', $gateway->sender_id);
    }
}
