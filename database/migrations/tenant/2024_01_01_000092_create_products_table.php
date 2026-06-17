<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Produtos / artigos (catálogo + stock) — schema do tenant. M51 do plano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 50)->nullable();
            $table->string('unit', 12)->default('un');
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('stock_qty', 14, 2)->default(0);
            $table->decimal('min_stock', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
