<?php

namespace Tests\Feature\Tenant;

use App\Services\Tenant\OnboardingService;
use Tests\TenancyTestCase;

class OnboardingTest extends TenancyTestCase
{
    public function test_cliente_configura_proxypay_chave_encriptada_e_escondida(): void
    {
        $tenant = $this->makeTenant('onb1');

        $tenant->run(function () {
            $gateway = (new OnboardingService())->configureProxyPay('pk_secret_123', 'sandbox');

            $this->assertTrue($gateway->active);
            // Encriptada em repouso, recuperável, e nunca serializada.
            $this->assertNotSame('pk_secret_123', $gateway->api_key_enc);
            $this->assertSame('pk_secret_123', $gateway->getApiKey());
            $this->assertArrayNotHasKey('api_key_enc', $gateway->toArray());
        });
    }

    public function test_admin_activa_sms_com_sender_id(): void
    {
        $tenant = $this->makeTenant('onb2');

        $tenant->run(function () {
            $service = new OnboardingService();
            $sms = $service->activateSms('sms_key_xyz', 'ACME', 7);

            $this->assertTrue($sms->active);
            $this->assertSame('ACME', $sms->sender_id);
            $this->assertSame(7, $sms->activated_by_admin);
            $this->assertArrayNotHasKey('api_key_enc', $sms->toArray());

            $status = $service->status();
            $this->assertFalse($status['proxypay']);
            $this->assertTrue($status['sms']);
        });
    }
}
