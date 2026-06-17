<?php

namespace Tests\Feature\Agt;

use App\Models\AgtSetting;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\InvoiceService;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Illuminate\Support\Facades\Http;
use Tests\TenancyTestCase;

class AgtSubmissionTest extends TenancyTestCase
{
    private Tenant $tenant;
    private string $softwareKey;
    private string $issuerKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->softwareKey = $this->key();
        $this->issuerKey = $this->key();
        $this->tenant = (new TenantProvisioningService())->register([
            'company_name' => 'Acme Lda', 'slug' => 'acme', 'plan' => 'business',
            'admin_name' => 'Ana', 'admin_email' => 'ana@acme.ao', 'admin_password' => 'password123',
        ]);
        tenancy()->end();
    }

    private function key(): string
    {
        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($res, $pem);

        return $pem;
    }

    public function test_emitir_factura_submete_a_agt_quando_configurado(): void
    {
        config([
            'agt.username' => 'u', 'agt.password' => 'p', 'agt.software_cert' => 'SW-1',
            'agt.private_key' => $this->softwareKey,
            'agt.base_url' => 'https://agt.test/sigt/fe/v1',
            'queue.default' => 'sync',
        ]);
        Http::fake([
            'agt.test/*solicitarSerie' => Http::response(['resultCode' => 'OK', 'seriesFEResult' => ['seriesCode' => 'A', 'authorizedQuantity' => '100']]),
            'agt.test/*registarFactura' => Http::response(['requestID' => 'REQ-1', 'errorList' => []]),
        ]);

        $this->tenant->run(function () {
            AgtSetting::create([
                'tax_registration_number' => '5000413178',
                'establishment_number' => '001',
                'private_key' => $this->issuerKey,
                'active' => true,
            ]);

            $svc = new InvoiceService(new AgtNumberGenerator());
            $invoice = $svc->create(['items' => [['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]]]);
            $svc->issue($invoice); // dispara InvoiceIssued → SubmitInvoiceToAgt

            $invoice->refresh();
            $this->assertSame('SUBMETIDA', $invoice->agt_status);
            $this->assertSame('REQ-1', $invoice->agt_request_id);
            $this->assertStringContainsString('FT A/', $invoice->number);
        });

        Http::assertSent(fn ($r) => str_contains($r->url(), '/registarFactura'));
    }

    public function test_sem_configuracao_nao_submete(): void
    {
        config(['agt.username' => null]);
        Http::fake();

        $this->tenant->run(function () {
            $svc = new InvoiceService(new AgtNumberGenerator());
            $invoice = $svc->create(['items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 5000]]]);
            $svc->issue($invoice);

            $this->assertNull($invoice->fresh()->agt_status);
        });

        Http::assertNothingSent();
    }
}
