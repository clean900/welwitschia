<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lançamentos contabilísticos (cabeçalho) — schema do tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('description');
            $table->string('reference')->nullable();
            // source_id é string: suporta id bigint (factura) e uuid (pagamento).
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->index(['source_type', 'source_id']);
            $table->decimal('total_debit', 16, 2)->default(0);
            $table->decimal('total_credit', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
