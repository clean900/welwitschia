<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colaboradores (schema do tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nif', 20)->nullable();
            $table->string('position')->nullable();
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('allowances', 14, 2)->default(0); // subsídios (VALIDAR tributação)
            $table->date('hire_date')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('bank_account')->nullable();
            $table->string('status')->default('active'); // active | terminated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
