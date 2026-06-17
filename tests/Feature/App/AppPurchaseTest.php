<?php

namespace Tests\Feature\App;

use App\Models\Membership;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppPurchaseTest extends TenancyTestCase
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

    public function test_fornecedores_e_compras_renderizam(): void
    {
        $this->get('/app/fornecedores')->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Suppliers/Index'));
        $this->get('/app/compras')->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Purchases/Index'));
        $this->get('/app/compras/criar')->assertOk()->assertInertia(fn (Assert $p) => $p->component('App/Purchases/Create'));
    }

    public function test_ciclo_compra_da_entrada_de_stock(): void
    {
        $ids = $this->tenant->run(function () {
            $s = Supplier::create(['name' => 'Fornecedor X', 'status' => 'active']);
            $p = Product::create(['name' => 'Resma', 'price' => 500, 'stock_qty' => 5, 'min_stock' => 2, 'status' => 'active']);

            return ['supplier' => $s->id, 'product' => $p->id];
        });

        $this->post('/app/compras', [
            'supplier_id' => $ids['supplier'],
            'items' => [['product_id' => $ids['product'], 'description' => 'Resma', 'quantity' => 10, 'unit_price' => 500, 'iva_rate' => 14]],
        ])->assertRedirect();

        $orderId = $this->tenant->run(fn () => PurchaseOrder::firstOrFail()->id);

        $this->post("/app/compras/{$orderId}/confirmar")->assertRedirect();
        $this->post("/app/compras/{$orderId}/receber")->assertRedirect();

        [$status, $stock] = $this->tenant->run(fn () => [
            PurchaseOrder::find($orderId)->status,
            (float) Product::firstOrFail()->stock_qty,
        ]);

        $this->assertSame('received', $status);
        $this->assertEqualsWithDelta(15, $stock, 0.001); // 5 + 10
    }
}
