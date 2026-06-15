<?php

use App\Http\Controllers\Central\RegisterTenantWebController;
use App\Http\Controllers\ProfileController;
use App\Models\Plan;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rotas centrais (apex: welwitschia.ao / localhost)
|--------------------------------------------------------------------------
| Restringidas ao domínio central para não colidirem com os subdomínios
| dos tenants (acme.welwitschia.ao), definidos em routes/tenant.php.
*/

Route::domain(config('tenancy.tenant_base_domain', 'localhost'))->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'plansCount' => Plan::where('is_active', true)->count(),
        ]);
    });

    // Wizard público de registo de empresa (cria um tenant).
    Route::get('/registar-empresa', [RegisterTenantWebController::class, 'create'])->name('registar.empresa');
    Route::post('/registar-empresa', [RegisterTenantWebController::class, 'store']);
    Route::get('/registar-empresa/sucesso/{slug}', [RegisterTenantWebController::class, 'success'])->name('registar.sucesso');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});
