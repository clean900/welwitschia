<?php

namespace Tests\Feature\Invoice;

use App\Jobs\ProcessProxyPayCallback;
use App\Models\Payment;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\BillingService;
use App\Services\Invoice\InvoiceService;
use App\Services\Payment\PaymentStateEngine;
use App\Services\Payment\ProxyPayService;
use App\Services\Payment\ReconciliationEngine;
use App\Services\Sms\TelcoSmsService;
use Database\Seeders\Tenant\PgcAngolaSeeder;
use Tests\TenancyTestCase;

class BillingCycleTest extends TenancyTestCase
{
    public function test_factura_calcula_iva_e_emite_com_numero_agt(): void
    {
        $this->makeTenant('inv1')->run(function () {
            (new PgcAngolaSeeder())->run();
            $service = new InvoiceService(new AgtNumberGenerator());

            $invoice = $service->create([
                'customer_name' => 'Cliente X',
                'items' => [
                    ['description' => 'Serviço mensal', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14],
                ],
            ]);

            $this->assertEquals('10000.00', $invoice->subtotal);
            $this->assertEquals('1400.00', $invoice->iva_amount);
            $this->assertEquals('11400.00', $invoice->total);
            $this->assertSame('draft', $invoice->status);

            $service->issue($invoice);
            $this->assertStringContainsString('FT WLW/', $invoice->fresh()->number);
            $this->assertSame('issued', $invoice->fresh()->status);
        });
    }

    public function test_numeracao_agt_e_sequencial(): void
    {
        $this->makeTenant('inv2')->run(function () {
            $generator = new AgtNumberGenerator();
            $this->assertStringEndsWith('/0001', $generator->next('FT'));
            $this->assertStringEndsWith('/0002', $generator->next('FT'));
        });
    }

    public function test_ciclo_completo_referencia_sms_e_factura_paga(): void
    {
        $this->makeTenant('inv3')->run(function () {
            (new PgcAngolaSeeder())->run();
            // Mocks de rede — sem chamadas reais a ProxyPay/TelcoSMS.
            $proxypay = $this->createMock(ProxyPayService::class);
            $proxypay->method('createReference')->willReturn('987654321');
            $sms = $this->createMock(TelcoSmsService::class);
            $sms->expects($this->once())->method('send')->willReturn(true);

            $invoiceService = new InvoiceService(new AgtNumberGenerator());
            $invoice = $invoiceService->create([
                'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10000]],
            ]);
            $invoiceService->issue($invoice);

            // Gera referência + SMS
            $payment = (new BillingService($proxypay, $sms))->requestPayment($invoice, '+244923348653');
            $this->assertSame('987654321', $payment->reference);
            $this->assertSame(Payment::CREATED, $payment->status);

            // Callback ProxyPay com valor exacto → reconcilia → factura paga
            (new ProcessProxyPayCallback(['id' => 'evt1', 'reference' => '987654321', 'amount' => 11400]))
                ->handle(new PaymentStateEngine(), new ReconciliationEngine(new PaymentStateEngine()));

            $this->assertSame(Payment::RECONCILED, $payment->fresh()->status);
            $this->assertSame('paid', $invoice->fresh()->status);
        });
    }
}
