<?php

namespace Tests\Feature\App;

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppInvoiceTest extends TenancyTestCase
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

    public function test_lista_de_faturas_renderiza(): void
    {
        $this->get('/app/invoices')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('App/Invoices/Index')->has('invoices'));
    }

    public function test_cria_rascunho_e_emite(): void
    {
        $this->post('/app/invoices', [
            'customer_name' => 'Cliente X',
            'items' => [['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]],
        ])->assertRedirect();

        $id = $this->tenant->run(fn () => Invoice::firstOrFail()->id);

        $this->post("/app/invoices/{$id}/emitir")->assertRedirect();

        [$status, $number, $total] = $this->tenant->run(function () {
            $inv = Invoice::firstOrFail();

            return [$inv->status, $inv->number, (float) $inv->total];
        });

        $this->assertSame('issued', $status);
        $this->assertStringContainsString('FT WLW/', $number);
        $this->assertEqualsWithDelta(11400, $total, 0.001);
    }

    public function test_cobrar_sem_proxypay_falha_graciosamente(): void
    {
        $this->post('/app/invoices', [
            'customer_name' => 'Cliente Y',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 5000]],
        ]);
        $id = $this->tenant->run(fn () => Invoice::firstOrFail()->id);
        $this->post("/app/invoices/{$id}/emitir");

        // Sem gateway ProxyPay configurado → erro amigável, sem rebentar.
        $this->post("/app/invoices/{$id}/cobrar", ['phone' => '+244923348653'])
            ->assertRedirect();
        $this->assertNotNull(session('error'));
    }
}
