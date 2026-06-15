<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plano de contas PGC Angola (schema do tenant).
 * // VALIDAR COM CONSULTOR FISCAL AO — códigos e estrutura segundo Decreto 82/01.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();          // ex: 12, 31, 71
            $table->string('name');
            $table->unsignedTinyInteger('class');       // 1..8
            $table->string('type');                     // asset|liability|equity|income|expense
            $table->string('normal_balance');           // debit|credit
            $table->boolean('is_postable')->default(true);
            $table->string('parent_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
