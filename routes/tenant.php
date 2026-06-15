<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\Tenant\TwoFactorController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    // Dashboard do tenant (subdomínio da empresa). O '/' central fica para web.php.
    Route::get('/dashboard', function () {
        return response()->json([
            'tenant' => tenant('id'),
            'name' => tenant('name'),
        ]);
    })->name('tenant.dashboard');
});

/*
| API do tenant (subdomínio): login, 2FA e wizard de onboarding.
*/
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('tenant.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::post('2fa/enable', [TwoFactorController::class, 'enable']);
        Route::post('2fa/confirm', [TwoFactorController::class, 'confirm']);

        Route::post('onboarding/proxypay', [OnboardingController::class, 'proxypay']);
        Route::post('onboarding/sms', [OnboardingController::class, 'sms']);
        Route::get('onboarding/status', [OnboardingController::class, 'status']);

        // Facturação + ciclo de cobrança
        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::post('invoices', [InvoiceController::class, 'store']);
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
        Route::post('invoices/{invoice}/request-payment', [InvoiceController::class, 'requestPayment']);
    });
});
