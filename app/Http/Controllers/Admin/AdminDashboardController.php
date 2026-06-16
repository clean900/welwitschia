<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Painel do back-office: empresas + métricas da plataforma.
 */
class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $planNames = Plan::pluck('name', 'id');
        $planPrices = Plan::pluck('price_monthly', 'id');
        $tenants = Tenant::orderByDesc('created_at')->get();

        $companies = $tenants->map(fn (Tenant $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'status' => $t->status,
            'plan' => $planNames[$t->plan_id] ?? '—',
            'created_at' => $t->created_at?->format('Y-m-d'),
        ]);

        $byPlan = $tenants->groupBy('plan_id')
            ->map(fn ($g, $planId) => ['plan' => $planNames[$planId] ?? '—', 'count' => $g->count()])
            ->values();

        $mrr = $tenants->where('status', '!=', 'suspended')
            ->sum(fn (Tenant $t) => (float) ($planPrices[$t->plan_id] ?? 0));

        return Inertia::render('Admin/Dashboard', [
            'admin' => Auth::guard('admin')->user()->only(['name', 'email']),
            'metrics' => [
                'companies' => $tenants->count(),
                'mrr' => round($mrr, 2),
                'memberships' => Membership::count(),
            ],
            'byPlan' => $byPlan,
            'companies' => $companies,
        ]);
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['status' => $tenant->status === 'suspended' ? 'active' : 'suspended']);

        return back()->with('success', "Empresa {$tenant->name} actualizada.");
    }
}
