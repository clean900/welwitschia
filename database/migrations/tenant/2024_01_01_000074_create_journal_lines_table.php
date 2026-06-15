<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Linhas dos lançamentos (débito/crédito por conta) — partidas dobradas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->string('account_code');
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->timestamps();

            $table->index('account_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
