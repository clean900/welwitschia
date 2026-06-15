<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // Welwitschia — colunas de negócio do tenant (empresa cliente)
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('nif', 20)->nullable();          // NIF angolano (10 dígitos)
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('status')->default('active');    // active | suspended | trial | cancelled
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
            $table->json('data')->nullable();

            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
