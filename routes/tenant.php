<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AccountingController;
use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\EmployeeController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\Tenant\PayrollController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Http\Controllers\Tenant\TenantWebAuthController;
use App\Http\Controllers\Tenant\TwoFactorController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes (subdomínio: acme.welwitschia.ao)
|--------------------------------------------------------------------------
| Isoladas por Route::domain para não colidirem com as rotas centrais.
*/

$base = config('tenancy.tenant_base_domain', 'localhost');

// --- App web do tenant (Inertia, sessão) ---
Route::domain('{tenant}.' . $base)->middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/login', [TenantWebAuthController::class, 'create'])->name('tenant.login');
    Route::post('/login', [TenantWebAuthController::class, 'store']);

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [TenantWebAuthController::class, 'destroy'])->name('tenant.logout');
        Route::get('/', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');
    });
});

// --- API token do tenant (Sanctum) ---
Route::domain('{tenant}.' . $base)->middleware([
    'api',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::post('2fa/enable', [TwoFactorController::class, 'enable']);
        Route::post('2fa/confirm', [TwoFactorController::class, 'confirm']);

        Route::post('onboarding/proxypay', [OnboardingController::class, 'proxypay']);
        Route::post('onboarding/sms', [OnboardingController::class, 'sms']);
        Route::get('onboarding/status', [OnboardingController::class, 'status']);

        Route::get('invoices', [InvoiceController::class, 'index']);
        Route::post('invoices', [InvoiceController::class, 'store']);
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
        Route::post('invoices/{invoice}/request-payment', [InvoiceController::class, 'requestPayment']);

        Route::get('accounting/journal', [AccountingController::class, 'journal']);
        Route::get('accounting/trial-balance', [AccountingController::class, 'trialBalance']);

        Route::get('employees', [EmployeeController::class, 'index']);
        Route::post('employees', [EmployeeController::class, 'store']);
        Route::get('employees/{employee}', [EmployeeController::class, 'show']);
        Route::put('employees/{employee}', [EmployeeController::class, 'update']);
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy']);

        Route::get('payrolls', [PayrollController::class, 'index']);
        Route::post('payrolls', [PayrollController::class, 'store']);
        Route::get('payrolls/{payroll}', [PayrollController::class, 'show']);
    });
});
