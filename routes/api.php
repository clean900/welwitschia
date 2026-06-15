<?php

use App\Http\Controllers\Central\RegisterTenantController;
use App\Http\Controllers\Tenant\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Welwitschia ERP (central / landlord)
|--------------------------------------------------------------------------
*/

Route::get('v1/ping', fn () => response()->json(['pong' => true, 'ts' => now()->toIso8601String()]));

// Registo de nova empresa (tenant) — passo 1 do wizard. Rota central.
Route::post('v1/register-tenant', [RegisterTenantController::class, 'store'])->name('tenant.register');

/**
 * Callback ProxyPay por tenant (path-based): /api/webhooks/proxypay/{tenant}
 * Inicializa o tenant pelo path e valida HMAC antes de despachar o job.
 */
Route::prefix('webhooks')->group(function () {
    Route::post('proxypay/{tenant}', [PaymentWebhookController::class, 'proxypay'])
        ->middleware(['tenant.path', 'hmac'])
        ->name('webhooks.proxypay');
});
