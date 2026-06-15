<?php

namespace Tests\Feature\Accounting;

use App\Exceptions\UnbalancedJournalEntry;
use App\Jobs\ProcessProxyPayCallback;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingService;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\BillingService;
use App\Services\Invoice\InvoiceService;
use App\Services\Payment\PaymentStateEngine;
use App\Services\Payment\ProxyPayService;
use App\Services\Payment\ReconciliationEngine;
use App\Services\Sms\TelcoSmsService;
use Database\Seeders\Tenant\PgcAngolaSeeder;
use Tests\TenancyTestCase;

class AccountingTest extends TenancyTestCase
{
    public function test_lancamento_desequilibrado_e_rejeitado(): void
    {
        $this->makeTenant('acc1')->run(function () {
            (new PgcAngolaSeeder())->run();

            $this->expectException(UnbalancedJournalEntry::class);
            (new AccountingService())->post('Teste', [
                ['account' => '12', 'debit' => 100],
                ['account' => '31', 'credit' => 90],
            ]);
        });
    }

    public function test_emissao_de_factura_gera_lancamento_de_venda(): void
    {
        $this->makeTenant('acc2')->run(function () {
            (new PgcAngolaSeeder())->run();

            $service = new InvoiceService(new AgtNumberGenerator());
            $invoice = $service->create([
                'items' => [['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]],
            ]);
            $service->issue($invoice);

            $entry = JournalEntry::where('source_type', \App\Models\Invoice::class)->first();
            $this->assertNotNull($entry);
            $this->assertTrue($entry->isBalanced());
            $this->assertEquals('11400.00', $entry->total_debit);

            // Clientes (31) deve 11400, IVA (3443) 1400.
            $this->assertEqualsWithDelta(11400, Account::where('code', '31')->first()->balance(), 0.001);
            $this->assertEqualsWithDelta(1400, Account::where('code', '3443')->first()->balance(), 0.001);
        });
    }

    public function test_ciclo_completo_balancete_confere(): void
    {
        $this->makeTenant('acc3')->run(function () {
            (new PgcAngolaSeeder())->run();

            $proxypay = $this->createMock(ProxyPayService::class);
            $proxypay->method('createReference')->willReturn('555000111');
            $sms = $this->createMock(TelcoSmsService::class);
            $sms->method('send')->willReturn(true);

            $invoiceService = new InvoiceService(new AgtNumberGenerator());
            $invoice = $invoiceService->create([
                'items' => [['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 10000, 'iva_rate' => 14]],
            ]);
            $invoiceService->issue($invoice);

            (new BillingService($proxypay, $sms))->requestPayment($invoice, '+244923348653');

            (new ProcessProxyPayCallback(['id' => 'evt', 'reference' => '555000111', 'amount' => 11400]))
                ->handle(new PaymentStateEngine(), new ReconciliationEngine(new PaymentStateEngine()));

            // Dois lançamentos: venda + recebimento.
            $this->assertSame(2, JournalEntry::count());

            // Balancete fecha.
            $totals = (new AccountingService())->trialBalance();
            $this->assertSame($totals['debit'], $totals['credit']);

            // Clientes (31) saldado a zero após recebimento; Bancos (12) com o valor pago.
            $this->assertEqualsWithDelta(0, Account::where('code', '31')->first()->balance(), 0.001);
            $this->assertEqualsWithDelta(11400, Account::where('code', '12')->first()->balance(), 0.001);
            $this->assertEqualsWithDelta(10000, Account::where('code', '71')->first()->balance(), 0.001);
        });
    }
}
