<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recibos de vencimento (linhas da folha) — schema do tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees');
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('allowances', 14, 2)->default(0);
            $table->decimal('gross', 14, 2)->default(0);
            $table->decimal('inss_employee', 14, 2)->default(0);
            $table->decimal('inss_employer', 14, 2)->default(0);
            $table->decimal('irt', 14, 2)->default(0);
            $table->decimal('net', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
