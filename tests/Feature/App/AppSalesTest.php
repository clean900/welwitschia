<?php

namespace Tests\Feature\App;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppSalesTest extends TenancyTestCase
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

    public function test_lista_e_criacao_renderizam(): void
    {
        $this->get('/app/vendas')->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Sales/Index'));
        $this->get('/app/vendas/criar')->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Sales/Create')->has('products'));
    }

    public function test_ciclo_venda_completo(): void
    {
        $ids = $this->tenant->run(function () {
            $c = Customer::create(['name' => 'Cliente X', 'status' => 'active']);
            $p = Product::create(['name' => 'Caneta', 'price' => 1000, 'stock_qty' => 10, 'min_stock' => 2, 'status' => 'active']);

            return ['customer' => $c->id, 'product' => $p->id];
        });

        $this->post('/app/vendas', [
            'customer_id' => $ids['customer'],
            'items' => [['product_id' => $ids['product'], 'description' => 'Caneta', 'quantity' => 3, 'unit_price' => 1000, 'iva_rate' => 14]],
        ])->assertRedirect();

        $orderId = $this->tenant->run(fn () => SalesOrder::firstOrFail()->id);

        $this->post("/app/vendas/{$orderId}/confirmar")->assertRedirect();
        $this->post("/app/vendas/{$orderId}/facturar")->assertRedirect();

        [$status, $stock, $invoices] = $this->tenant->run(fn () => [
            SalesOrder::find($orderId)->status,
            (float) Product::firstOrFail()->stock_qty,
            Invoice::count(),
        ]);

        $this->assertSame('invoiced', $status);
        $this->assertEqualsWithDelta(7, $stock, 0.001); // 10 − 3
        $this->assertSame(1, $invoices);
    }
}
