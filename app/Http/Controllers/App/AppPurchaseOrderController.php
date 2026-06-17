<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\Purchase\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Ordens de compra — /app/compras (M50).
 */
class AppPurchaseOrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Purchases/Index', [
            'orders' => PurchaseOrder::latest()->paginate(20)->through(fn (PurchaseOrder $o) => [
                'id' => $o->id,
                'number' => $o->number,
                'supplier' => $o->supplier_name,
                'total' => (float) $o->total,
                'status' => $o->status,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('App/Purchases/Create', [
            'suppliers' => Supplier::active()->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }

    public function store(Request $request, PurchaseOrderService $service): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => 'nullable|integer',
            'supplier_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.iva_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $order = $service->create($data);

        return redirect()->route('app.purchases.show', $order->id)->with('success', 'Ordem de compra criada.');
    }

    public function show(string $order): Response
    {
        $order = PurchaseOrder::with('items')->findOrFail($order);

        return Inertia::render('App/Purchases/Show', [
            'order' => [
                ...$order->only(['id', 'number', 'supplier_name', 'status']),
                'subtotal' => (float) $order->subtotal,
                'iva_amount' => (float) $order->iva_amount,
                'total' => (float) $order->total,
                'items' => $order->items->map(fn ($i) => [
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                    'iva_rate' => (float) $i->iva_rate,
                    'line_total' => (float) $i->line_total,
                ]),
            ],
        ]);
    }

    public function confirm(string $order, PurchaseOrderService $service): RedirectResponse
    {
        $service->confirm(PurchaseOrder::findOrFail($order));

        return back()->with('success', 'Ordem confirmada.');
    }

    public function receive(string $order, PurchaseOrderService $service): RedirectResponse
    {
        $service->receive(PurchaseOrder::findOrFail($order));

        return back()->with('success', 'Recebido — stock actualizado.');
    }
}
