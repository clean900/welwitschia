<?php

namespace Tests\Feature\App;

use App\Models\Employee;
use App\Models\Membership;
use App\Models\Tenant;
use App\Services\Accounting\AccountingService;
use App\Services\Fiscal\IrtCalculator;
use App\Services\Hr\PayrollService;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\InvoiceService;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Tests\TenancyTestCase;

class AppPdfTest extends TenancyTestCase
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

    public function test_pdf_de_factura(): void
    {
        $id = $this->tenant->run(function () {
            $svc = new InvoiceService(new AgtNumberGenerator());
            $inv = $svc->create(['items' => [['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]]]);
            $svc->issue($inv);

            return $inv->id;
        });

        $response = $this->get("/app/invoices/{$id}/pdf");
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_pdf_de_recibo(): void
    {
        $payslipId = $this->tenant->run(function () {
            Employee::create(['name' => 'João', 'base_salary' => 200000, 'status' => 'active']);
            $payroll = (new PayrollService(new IrtCalculator(), new AccountingService()))->process(2026, 6);

            return $payroll->payslips()->first()->id;
        });

        $response = $this->get("/app/recibos/{$payslipId}/pdf");
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
