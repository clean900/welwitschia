<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Tenant\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Wizard de registo de empresa (Inertia, domínio central).
 */
class RegisterTenantWebController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('RegisterTenant', [
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get([
                'name', 'slug', 'description', 'price_monthly', 'price_annual', 'max_users',
            ]),
        ]);
    }

    public function store(Request $request, TenantProvisioningService $provisioning): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'slug' => 'required|alpha_dash|min:3|max:63',
            'nif' => 'nullable|string|max:20',
            'plan' => 'required|string|exists:plans,slug',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $tenant = $provisioning->register($data);

        return redirect()->route('registar.sucesso', ['slug' => $tenant->slug]);
    }

    public function success(string $slug): Response
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();

        return Inertia::render('RegisterTenantSuccess', [
            'company' => $tenant->name,
            'subdomain' => $tenant->slug . '.welwitschia.ao',
        ]);
    }
}
