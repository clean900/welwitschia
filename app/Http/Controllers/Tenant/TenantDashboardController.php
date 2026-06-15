<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Invoice;
use App\Services\Tenant\OnboardingService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Painel principal da empresa (subdomínio).
 */
class TenantDashboardController extends Controller
{
    public function index(OnboardingService $onboarding): Response
    {
        return Inertia::render('Tenant/Dashboard', [
            'company' => tenant('name'),
            'user' => Auth::user()->only(['name', 'email']),
            'metrics' => [
                'invoices_issued' => Invoice::where('status', 'issued')->count(),
                'invoices_paid' => Invoice::where('status', 'paid')->count(),
                'outstanding' => (float) Invoice::where('status', 'issued')->sum('total'),
                'revenue_paid' => (float) Invoice::where('status', 'paid')->sum('total'),
                'employees' => Employee::where('status', 'active')->count(),
            ],
            'onboarding' => $onboarding->status(),
        ]);
    }
}
