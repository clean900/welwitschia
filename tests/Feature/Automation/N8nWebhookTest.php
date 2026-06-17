<?php

namespace Tests\Feature\Automation;

use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Support\Facades\Http;
use Tests\TenancyTestCase;

class N8nWebhookTest extends TenancyTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        config(['queue.default' => 'sync']);
    }

    public function test_evento_dispara_webhook_para_n8n_quando_configurado(): void
    {
        config(['services.n8n.url' => 'http://n8n.test/webhook']);
        Http::fake();

        (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda', 'slug' => 'acme', 'plan' => 'business',
            'admin_name' => 'Ana', 'admin_email' => 'ana@acme.ao', 'admin_password' => 'password123',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'http://n8n.test/webhook/tenant.created'
            && $request['event'] === 'tenant.created'
            && $request['data']['company'] === 'Acme Lda');
    }

    public function test_sem_url_nao_dispara_nada(): void
    {
        config(['services.n8n.url' => null]);
        Http::fake();

        (new TenantProvisioningService())->register([
            'company_name' => 'Beta', 'slug' => 'beta', 'plan' => 'starter',
            'admin_name' => 'B', 'admin_email' => 'b@beta.ao', 'admin_password' => 'password123',
        ]);

        Http::assertNothingSent();
    }
}
