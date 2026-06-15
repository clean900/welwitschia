<?php

namespace Tests\Feature\Payment;

use App\Jobs\ProcessProxyPayCallback;
use App\Models\Payment;
use App\Services\Payment\PaymentStateEngine;
use App\Services\Payment\ReconciliationEngine;
use Tests\TenancyTestCase;

class PaymentIdempotencyTest extends TenancyTestCase
{
    public function test_callback_duplicado_nao_cria_dois_pagamentos(): void
    {
        $tenant = $this->makeTenant('idem');

        $tenant->run(function () {
            $engine = new PaymentStateEngine();
            Payment::create(['reference' => 'REF-100', 'amount' => 5000, 'status' => Payment::CREATED]);

            $payload = ['id' => 'evt_1', 'reference' => 'REF-100', 'amount' => 5000, 'entity' => '00123'];

            $p1 = $engine->processCallback($payload);
            $p2 = $engine->processCallback($payload); // callback duplicado

            $this->assertSame(1, Payment::count());
            $this->assertSame($p1->id, $p2->id);
            $this->assertSame(Payment::PAID, $p1->fresh()->status);
            $this->assertNotNull($p1->fresh()->paid_at);
        });
    }

    public function test_job_processa_e_reconcilia_valor_exacto(): void
    {
        $tenant = $this->makeTenant('recon');

        $tenant->run(function () {
            Payment::create(['reference' => 'REF-9', 'amount' => 5000, 'status' => Payment::CREATED]);
            $payload = ['id' => 'evt_9', 'reference' => 'REF-9', 'amount' => 5000];

            (new ProcessProxyPayCallback($payload))->handle(
                new PaymentStateEngine(),
                new ReconciliationEngine(new PaymentStateEngine())
            );

            $this->assertSame(Payment::RECONCILED, Payment::first()->status);
        });
    }

    public function test_diferenca_de_valor_vai_para_revisao_manual(): void
    {
        $tenant = $this->makeTenant('manual');

        $tenant->run(function () {
            Payment::create(['reference' => 'REF-7', 'amount' => 5000, 'status' => Payment::CREATED]);
            $payload = ['id' => 'evt_7', 'reference' => 'REF-7', 'amount' => 4000]; // 1000 a menos

            (new ProcessProxyPayCallback($payload))->handle(
                new PaymentStateEngine(),
                new ReconciliationEngine(new PaymentStateEngine())
            );

            $this->assertSame(Payment::MANUAL_REVIEW, Payment::first()->status);
        });
    }
}
