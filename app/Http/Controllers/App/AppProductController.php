<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Stock\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Produtos / Stock — /app/produtos (M51).
 */
class AppProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Products/Index', [
            'products' => Product::orderBy('name')->paginate(20)->through(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'unit' => $p->unit,
                'price' => (float) $p->price,
                'stock_qty' => (float) $p->stock_qty,
                'min_stock' => (float) $p->min_stock,
                'low' => $p->isLowStock(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:12',
            'price' => 'required|numeric|min:0',
            'stock_qty' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
        ]);

        Product::create($data + ['status' => 'active']);

        return back()->with('success', 'Produto adicionado.');
    }

    public function move(Request $request, string $product, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:entrada,saida',
            'quantity' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $stock->move(Product::findOrFail($product), $data['type'], $data['quantity'], $data['note'] ?? null);

        return back()->with('success', 'Stock actualizado.');
    }

    public function destroy(string $product): RedirectResponse
    {
        Product::findOrFail($product)->delete();

        return back()->with('success', 'Produto removido.');
    }
}
