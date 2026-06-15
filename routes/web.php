<?php

use App\Http\Controllers\Central\RegisterTenantWebController;
use App\Http\Controllers\CompanyAuthController;
use App\Http\Controllers\Tenant\TenantDashboardController;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rotas web — domínio único (welwitschia.ao)
|--------------------------------------------------------------------------
*/

// --- Público ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => true,
        'plansCount' => Plan::where('is_active', true)->count(),
    ]);
})->name('home');

Route::get('/registar-empresa', [RegisterTenantWebController::class, 'create'])->name('registar.empresa');
Route::post('/registar-empresa', [RegisterTenantWebController::class, 'store']);
Route::get('/registar-empresa/sucesso/{slug}', [RegisterTenantWebController::class, 'success'])->name('registar.sucesso');

// --- Login da empresa (conta) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [CompanyAuthController::class, 'create'])->name('login');
    Route::post('/login', [CompanyAuthController::class, 'store']);
});
Route::post('/logout', [CompanyAuthController::class, 'destroy'])->middleware('auth')->name('logout');

// --- Workspace da empresa (autenticado; tenancy resolvida pela conta) ---
Route::middleware(['auth', 'tenant.account'])->prefix('app')->group(function () {
    Route::get('/', [TenantDashboardController::class, 'index'])->name('app.dashboard');
});
