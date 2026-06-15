<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facturas (schema do tenant). Numeração no formato AGT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();   // ex: FT welwitschia/2026/0001
            $table->string('customer_name')->nullable();
            $table->string('customer_nif', 20)->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('iva_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default('AOA');
            $table->string('status')->default('draft'); // draft | issued | paid | cancelled
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
