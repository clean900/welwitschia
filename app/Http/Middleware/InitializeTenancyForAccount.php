<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inicializa a tenancy a partir do tenant_id da conta autenticada.
 * Domínio único: a empresa é resolvida pela conta, não pelo subdomínio.
 * Corre SEMPRE depois do middleware 'auth'.
 */
class InitializeTenancyForAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = Auth::user();

        if ($account && $account->tenant_id) {
            $tenant = Tenant::find($account->tenant_id);
            if ($tenant) {
                tenancy()->initialize($tenant);
            }
        }

        return $next($request);
    }
}
