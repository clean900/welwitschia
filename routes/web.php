<?php

use App\Http\Controllers\App\AppAccountingController;
use App\Http\Controllers\App\AppCustomerController;
use App\Http\Controllers\App\AppEmployeeController;
use App\Http\Controllers\App\AppFiscalController;
use App\Http\Controllers\App\AppInvoiceController;
use App\Http\Controllers\App\AppOnboardingController;
use App\Http\Controllers\App\AppPaymentController;
use App\Http\Controllers\App\AppPayrollController;
use App\Http\Controllers\App\AppProductController;
use App\Http\Controllers\App\AppPurchaseOrderController;
use App\Http\Controllers\App\AppSalesOrderController;
use App\Http\Controllers\App\AppSupplierController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPartnerController;
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
        'partners' => \App\Models\LandingPartner::visible()->get(['name', 'logo_url']),
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

    // Faturação
    Route::get('/invoices', [AppInvoiceController::class, 'index'])->name('app.invoices.index');
    Route::get('/invoices/criar', [AppInvoiceController::class, 'create'])->name('app.invoices.create');
    Route::post('/invoices', [AppInvoiceController::class, 'store'])->name('app.invoices.store');
    Route::get('/invoices/{invoice}', [AppInvoiceController::class, 'show'])->name('app.invoices.show');
    Route::get('/invoices/{invoice}/pdf', [AppInvoiceController::class, 'pdf'])->name('app.invoices.pdf');
    Route::post('/invoices/{invoice}/emitir', [AppInvoiceController::class, 'issue'])->name('app.invoices.issue');
    Route::post('/invoices/{invoice}/cancelar', [AppInvoiceController::class, 'cancel'])->name('app.invoices.cancel');
    Route::post('/invoices/{invoice}/cobrar', [AppInvoiceController::class, 'requestPayment'])->name('app.invoices.charge');

    // Configuração / integrações
    Route::get('/onboarding', [AppOnboardingController::class, 'index'])->name('app.onboarding');
    Route::post('/onboarding/proxypay', [AppOnboardingController::class, 'saveProxyPay'])->name('app.onboarding.proxypay');
    Route::post('/onboarding/sms', [AppOnboardingController::class, 'saveSms'])->name('app.onboarding.sms');

    // RH & Salários
    Route::get('/colaboradores', [AppEmployeeController::class, 'index'])->name('app.employees.index');
    Route::post('/colaboradores', [AppEmployeeController::class, 'store'])->name('app.employees.store');
    Route::delete('/colaboradores/{employee}', [AppEmployeeController::class, 'destroy'])->name('app.employees.destroy');

    Route::get('/salarios', [AppPayrollController::class, 'index'])->name('app.payrolls.index');
    Route::post('/salarios', [AppPayrollController::class, 'store'])->name('app.payrolls.store');
    Route::get('/salarios/{payroll}', [AppPayrollController::class, 'show'])->name('app.payrolls.show');
    Route::get('/recibos/{payslip}/pdf', [AppPayrollController::class, 'payslipPdf'])->name('app.payslips.pdf');

    // Clientes (CRM)
    Route::get('/clientes', [AppCustomerController::class, 'index'])->name('app.customers.index');
    Route::post('/clientes', [AppCustomerController::class, 'store'])->name('app.customers.store');
    Route::delete('/clientes/{customer}', [AppCustomerController::class, 'destroy'])->name('app.customers.destroy');

    // Vendas (encomendas)
    Route::get('/vendas', [AppSalesOrderController::class, 'index'])->name('app.sales.index');
    Route::get('/vendas/criar', [AppSalesOrderController::class, 'create'])->name('app.sales.create');
    Route::post('/vendas', [AppSalesOrderController::class, 'store'])->name('app.sales.store');
    Route::get('/vendas/{order}', [AppSalesOrderController::class, 'show'])->name('app.sales.show');
    Route::post('/vendas/{order}/confirmar', [AppSalesOrderController::class, 'confirm'])->name('app.sales.confirm');
    Route::post('/vendas/{order}/facturar', [AppSalesOrderController::class, 'invoice'])->name('app.sales.invoice');

    // Compras (ordens de compra) + Fornecedores
    Route::get('/fornecedores', [AppSupplierController::class, 'index'])->name('app.suppliers.index');
    Route::post('/fornecedores', [AppSupplierController::class, 'store'])->name('app.suppliers.store');
    Route::delete('/fornecedores/{supplier}', [AppSupplierController::class, 'destroy'])->name('app.suppliers.destroy');
    Route::get('/compras', [AppPurchaseOrderController::class, 'index'])->name('app.purchases.index');
    Route::get('/compras/criar', [AppPurchaseOrderController::class, 'create'])->name('app.purchases.create');
    Route::post('/compras', [AppPurchaseOrderController::class, 'store'])->name('app.purchases.store');
    Route::get('/compras/{order}', [AppPurchaseOrderController::class, 'show'])->name('app.purchases.show');
    Route::post('/compras/{order}/confirmar', [AppPurchaseOrderController::class, 'confirm'])->name('app.purchases.confirm');
    Route::post('/compras/{order}/receber', [AppPurchaseOrderController::class, 'receive'])->name('app.purchases.receive');

    // Produtos / Stock
    Route::get('/produtos', [AppProductController::class, 'index'])->name('app.products.index');
    Route::post('/produtos', [AppProductController::class, 'store'])->name('app.products.store');
    Route::post('/produtos/{product}/movimentar', [AppProductController::class, 'move'])->name('app.products.move');
    Route::delete('/produtos/{product}', [AppProductController::class, 'destroy'])->name('app.products.destroy');

    // Cobranças
    Route::get('/cobrancas', [AppPaymentController::class, 'index'])->name('app.payments.index');

    // Contabilidade (PGC)
    Route::get('/contabilidade', [AppAccountingController::class, 'trialBalance'])->name('app.accounting.balance');
    Route::get('/contabilidade/razao', [AppAccountingController::class, 'journal'])->name('app.accounting.journal');

    // Exportação fiscal SAF-T (AO)
    Route::get('/saft', [AppFiscalController::class, 'saft'])->name('app.fiscal.saft');
});

/*
| Back-office da plataforma (/admin) — staff Welwitschia, guard 'admin'.
*/
Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/empresas/{tenant}/suspender', [AdminDashboardController::class, 'suspend'])->name('admin.companies.suspend');
        Route::get('/empresas/{tenant}', [AdminCompanyController::class, 'show'])->name('admin.companies.show');
        Route::post('/empresas/{tenant}/sms', [AdminCompanyController::class, 'activateSms'])->name('admin.companies.sms');
        Route::post('/empresas/{tenant}/agt', [AdminCompanyController::class, 'saveAgt'])->name('admin.companies.agt');

        // Parceiros da landing
        Route::get('/parceiros', [AdminPartnerController::class, 'index'])->name('admin.partners.index');
        Route::post('/parceiros', [AdminPartnerController::class, 'store'])->name('admin.partners.store');
        Route::post('/parceiros/{partner}/toggle', [AdminPartnerController::class, 'toggle'])->name('admin.partners.toggle');
        Route::delete('/parceiros/{partner}', [AdminPartnerController::class, 'destroy'])->name('admin.partners.destroy');
    });
});
