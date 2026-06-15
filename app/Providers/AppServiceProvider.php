<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Os listeners (MarkInvoicePaid, RecordSaleLedgerEntry, RecordReceiptLedgerEntry)
     * são auto-descobertos pelo Laravel a partir de app/Listeners — não registar à mão
     * para não disparar em duplicado.
     */
    public function boot(): void
    {
        //
    }
}
