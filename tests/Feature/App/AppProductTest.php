<?php

namespace Tests\Feature\App;

use App\Models\Membership;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Database\Seeders\Landlord\PlansSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TenancyTestCase;

class AppProductTest extends TenancyTestCase
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

    public function test_lista_de_produtos_renderiza(): void
    {
        $this->get('/app/produtos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('App/Products/Index')->has('products'));
    }

    public function test_adiciona_produto(): void
    {
        $this->post('/app/produtos', ['name' => 'Caderno', 'price' => 1500, 'stock_qty' => 10, 'min_stock' => 3])
            ->assertRedirect();

        $this->assertSame(1, $this->tenant->run(fn () => Product::count()));
    }

    public function test_movimento_de_stock(): void
    {
        $id = $this->tenant->run(fn () => Product::create(['name' => 'Caneta', 'price' => 200, 'stock_qty' => 10, 'min_stock' => 5, 'status' => 'active'])->id);

        $this->post("/app/produtos/{$id}/movimentar", ['type' => 'entrada', 'quantity' => 5])->assertRedirect();
        $this->post("/app/produtos/{$id}/movimentar", ['type' => 'saida', 'quantity' => 3])->assertRedirect();

        [$stock, $movements] = $this->tenant->run(function () use ($id) {
            $p = Product::find($id);

            return [(float) $p->stock_qty, $p->movements()->count()];
        });

        $this->assertEqualsWithDelta(12, $stock, 0.001); // 10 + 5 - 3
        $this->assertSame(2, $movements);
    }
}
