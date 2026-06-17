<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Encomendas de venda — /app/vendas (M42-44).
 */
class AppSalesOrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Sales/Index', [
            'orders' => SalesOrder::latest()->paginate(20)->through(fn (SalesOrder $o) => [
                'id' => $o->id,
                'number' => $o->number,
                'customer' => $o->customer_name,
                'total' => (float) $o->total,
                'status' => $o->status,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('App/Sales/Create', [
            'customers' => Customer::active()->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }

    public function store(Request $request, SalesOrderService $service): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'nullable|integer',
            'customer_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.iva_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $order = $service->create($data);

        return redirect()->route('app.sales.show', $order->id)->with('success', 'Encomenda criada.');
    }

    public function show(string $order): Response
    {
        $order = SalesOrder::with('items')->findOrFail($order);

        return Inertia::render('App/Sales/Show', [
            'order' => [
                ...$order->only(['id', 'number', 'customer_name', 'status', 'invoice_id']),
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

    public function confirm(string $order, SalesOrderService $service): RedirectResponse
    {
        $service->confirm(SalesOrder::findOrFail($order));

        return back()->with('success', 'Encomenda confirmada.');
    }

    public function invoice(string $order, SalesOrderService $service): RedirectResponse
    {
        $invoice = $service->invoice(SalesOrder::findOrFail($order));

        return redirect()->route('app.invoices.show', $invoice->id)->with('success', 'Factura emitida a partir da encomenda.');
    }
}
