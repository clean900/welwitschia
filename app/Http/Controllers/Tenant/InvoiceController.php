<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Invoice\BillingService;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Invoice::with('items')->latest()->paginate(20));
    }

    public function store(Request $request, InvoiceService $service): JsonResponse
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

        return response()->json($service->create($data), 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load('items'));
    }

    public function issue(Invoice $invoice, InvoiceService $service): JsonResponse
    {
        return response()->json($service->issue($invoice));
    }

    public function cancel(Invoice $invoice, InvoiceService $service): JsonResponse
    {
        return response()->json($service->cancel($invoice));
    }

    public function requestPayment(Request $request, Invoice $invoice, BillingService $billing): JsonResponse
    {
        $data = $request->validate(['phone' => 'required|string|max:20']);

        $payment = $billing->requestPayment($invoice, $data['phone']);

        return response()->json([
            'reference' => $payment->reference,
            'amount' => (float) $payment->amount,
            'status' => $payment->status,
        ], 201);
    }
}
