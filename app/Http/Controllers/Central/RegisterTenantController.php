<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Registo de uma nova empresa (tenant) — passo 1 do wizard. Rota central.
 */
class RegisterTenantController extends Controller
{
    public function store(Request $request, TenantProvisioningService $provisioning): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'slug' => 'required|alpha_dash|min:3|max:63',
            'nif' => 'nullable|string|max:20',
            'plan' => 'required|string|exists:plans,slug',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8',
        ]);

        $tenant = $provisioning->register($data);

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => $tenant->status,
                'subdomain' => $tenant->slug . '.welwitschia.ao',
            ],
        ], 201);
    }
}
