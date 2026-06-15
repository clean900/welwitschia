<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoice\BillingService;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Faturação na app web do tenant (/app/invoices).
 */
class AppInvoiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('App/Invoices/Index', [
            'invoices' => Invoice::latest()->paginate(15)->through(fn (Invoice $i) => [
                'id' => $i->id,
                'number' => $i->number,
                'customer_name' => $i->customer_name,
                'total' => (float) $i->total,
                'status' => $i->status,
                'issued_at' => $i->issued_at?->format('Y-m-d'),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('App/Invoices/Create');
    }

    public function store(Request $request, InvoiceService $service): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_nif' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.iva_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = $service->create($data);

        return redirect()->route('app.invoices.show', $invoice->id)
            ->with('success', 'Rascunho criado.');
    }

    public function show(Invoice $invoice): Response
    {
        return Inertia::render('App/Invoices/Show', [
            'invoice' => [
                ...$invoice->only(['id', 'number', 'customer_name', 'customer_nif', 'subtotal', 'iva_amount', 'total', 'status']),
                'subtotal' => (float) $invoice->subtotal,
                'iva_amount' => (float) $invoice->iva_amount,
                'total' => (float) $invoice->total,
                'issued_at' => $invoice->issued_at?->format('Y-m-d'),
                'items' => $invoice->items->map(fn ($it) => [
                    'description' => $it->description,
                    'quantity' => (float) $it->quantity,
                    'unit_price' => (float) $it->unit_price,
                    'iva_rate' => (float) $it->iva_rate,
                    'line_total' => (float) $it->line_total,
                ]),
            ],
            'payment' => $invoice->payment ? [
                'reference' => $invoice->payment->reference,
                'status' => $invoice->payment->status,
            ] : null,
        ]);
    }

    public function issue(Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $service->issue($invoice);

        return back()->with('success', "Factura emitida: {$invoice->number}.");
    }

    public function cancel(Invoice $invoice, InvoiceService $service): RedirectResponse
    {
        $service->cancel($invoice);

        return back()->with('success', 'Factura cancelada.');
    }

    public function requestPayment(Request $request, Invoice $invoice, BillingService $billing): RedirectResponse
    {
        $data = $request->validate(['phone' => 'required|string|max:20']);

        try {
            $payment = $billing->requestPayment($invoice, $data['phone']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Não foi possível gerar a cobrança (verifique a ligação ProxyPay): ' . $e->getMessage());
        }

        return back()->with('success', "Referência {$payment->reference} gerada e SMS enviado.");
    }
}
