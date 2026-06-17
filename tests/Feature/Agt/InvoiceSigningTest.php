<?php

namespace Tests\Feature\Agt;

use App\Services\Agt\InvoiceSigningService;
use App\Services\Invoice\AgtNumberGenerator;
use App\Services\Invoice\InvoiceService;
use Database\Seeders\Tenant\PgcAngolaSeeder;
use Tests\TenancyTestCase;

class InvoiceSigningTest extends TenancyTestCase
{
    public function test_faturas_sao_assinadas_e_encadeadas(): void
    {
        $this->makeTenant('agt')->run(function () {
            (new PgcAngolaSeeder())->run();
            $svc = new InvoiceService(new AgtNumberGenerator());

            $a = $svc->create(['items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 1000, 'iva_rate' => 14]]]);
            $svc->issue($a);
            $b = $svc->create(['items' => [['description' => 'Y', 'quantity' => 1, 'unit_price' => 2000, 'iva_rate' => 14]]]);
            $svc->issue($b);

            $a->refresh();
            $b->refresh();

            $this->assertNotEmpty($a->hash);
            $this->assertSame('', $a->previous_hash);              // 1ª factura: sem anterior
            $this->assertSame($a->hash, $b->previous_hash);         // encadeamento
            $this->assertNotSame($a->hash, $b->hash);
            $this->assertSame(4, strlen(InvoiceSigningService::shortCode($a->hash)));
        });
    }
}
