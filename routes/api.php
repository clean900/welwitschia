<?php

use App\Http\Controllers\Tenant\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Welwitschia ERP
|--------------------------------------------------------------------------
*/

Route::get('v1/ping', fn () => response()->json(['pong' => true, 'ts' => now()->toIso8601String()]));

/**
 * Callback ProxyPay por tenant (path-based): /api/webhooks/proxypay/{tenant}
 * Inicializa o tenant pelo path e valida HMAC antes de despachar o job.
 */
Route::prefix('webhooks')->group(function () {
    Route::post('proxypay/{tenant}', [PaymentWebhookController::class, 'proxypay'])
        ->middleware(['tenant.path', 'hmac'])
        ->name('webhooks.proxypay');
});
