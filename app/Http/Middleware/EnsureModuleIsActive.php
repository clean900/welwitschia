<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia acesso a rotas de módulos não activos para o tenant actual.
 * Uso: ->middleware('module:frota_gps')
 */
class EnsureModuleIsActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenant = tenant();

        if (! $tenant || ! $tenant->hasModuleActive($module)) {
            abort(403, "Módulo '{$module}' não está activo para este tenant.");
        }

        return $next($request);
    }
}
