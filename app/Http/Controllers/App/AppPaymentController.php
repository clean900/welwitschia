<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cobranças — pagamentos/referências ProxyPay da empresa (/app/cobrancas).
 */
class AppPaymentController extends Controller
{
    public function index(): Response
    {
        $payments = Payment::with('payable')->latest()->paginate(20)->through(fn (Payment $p) => [
            'reference' => $p->reference,
            'amount' => (float) $p->amount,
            'paid_amount' => $p->paid_amount !== null ? (float) $p->paid_amount : null,
            'status' => $p->status,
            'invoice' => $p->payable instanceof Invoice ? $p->payable->number : null,
            'date' => $p->created_at?->format('Y-m-d'),
        ]);

        return Inertia::render('App/Payments/Index', [
            'payments' => $payments,
            'summary' => [
                'reconciled' => (float) Payment::where('status', Payment::RECONCILED)->sum('paid_amount'),
                'pending' => (float) Payment::whereIn('status', [Payment::CREATED, Payment::PENDING])->sum('amount'),
            ],
        ]);
    }
}
