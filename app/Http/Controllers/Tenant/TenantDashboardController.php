<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Tenant\OnboardingService;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Painel principal da empresa (/app).
 */
class TenantDashboardController extends Controller
{
    public function index(OnboardingService $onboarding): Response
    {
        // Receita recebida nos últimos 6 meses (pagamentos reconciliados).
        $payments = Payment::where('status', Payment::RECONCILED)->get();
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $months[] = [
                'label' => ucfirst($m->translatedFormat('M')),
                'value' => round((float) $payments->filter(
                    fn ($p) => optional($p->paid_at ?? $p->reconciled_at)->format('Y-m') === $key
                )->sum('paid_amount'), 2),
            ];
        }

        return Inertia::render('Tenant/Dashboard', [
            'metrics' => [
                'invoiced' => (float) Invoice::whereIn('status', ['issued', 'paid'])->sum('total'),
                'received' => (float) Invoice::where('status', 'paid')->sum('total'),
                'outstanding' => (float) Invoice::where('status', 'issued')->sum('total'),
                'invoices_issued' => Invoice::where('status', 'issued')->count(),
                'invoices_paid' => Invoice::where('status', 'paid')->count(),
                'employees' => Employee::where('status', 'active')->count(),
            ],
            'revenue' => $months,
            'activity' => AuditLog::orderByDesc('id')->limit(6)->get()->map(fn (AuditLog $l) => [
                'event' => $l->event,
                'at' => $l->created_at?->diffForHumans(),
            ]),
            'onboarding' => $onboarding->status(),
        ]);
    }
}
