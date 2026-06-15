<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Folhas de salário mensais (schema do tenant).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('status')->default('processed'); // processed | paid
            $table->timestamp('processed_at')->nullable();
            $table->decimal('total_gross', 16, 2)->default(0);
            $table->decimal('total_inss', 16, 2)->default(0);
            $table->decimal('total_irt', 16, 2)->default(0);
            $table->decimal('total_net', 16, 2)->default(0);
            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
