<?php

namespace Tests\Feature\Payment;

use App\Exceptions\InvalidPaymentTransition;
use App\Models\Payment;
use App\Services\Payment\PaymentStateEngine;
use Tests\TenancyTestCase;

class PaymentStateMachineTest extends TenancyTestCase
{
    public function test_fluxo_completo_de_transicoes_validas(): void
    {
        $tenant = $this->makeTenant('sm1');

        $tenant->run(function () {
            $engine = new PaymentStateEngine();
            $p = Payment::create(['reference' => 'R1', 'amount' => 1000, 'status' => Payment::CREATED]);

            $engine->transition($p, Payment::PENDING);
            $this->assertSame(Payment::PENDING, $p->fresh()->status);

            $engine->transition($p, Payment::PAID);
            $engine->transitionToReconciled($p);
            $this->assertSame(Payment::RECONCILED, $p->fresh()->status);
            $this->assertNotNull($p->fresh()->reconciled_at);
        });
    }

    public function test_transicao_invalida_lanca_excepcao(): void
    {
        $tenant = $this->makeTenant('sm2');

        $tenant->run(function () {
            $engine = new PaymentStateEngine();
            $p = Payment::create(['reference' => 'R2', 'amount' => 1000, 'status' => Payment::CREATED]);

            // CREATED → RECONCILED não é permitido.
            $this->expectException(InvalidPaymentTransition::class);
            $engine->transition($p, Payment::RECONCILED);
        });
    }

    public function test_estado_terminal_nao_transiciona(): void
    {
        $tenant = $this->makeTenant('sm3');

        $tenant->run(function () {
            $engine = new PaymentStateEngine();
            $this->assertFalse($engine->canTransition(Payment::RECONCILED, Payment::PAID));
            $this->assertFalse($engine->canTransition(Payment::REJECTED, Payment::PAID));
            $this->assertTrue($engine->canTransition(Payment::PAID, Payment::MANUAL_REVIEW));
        });
    }
}
