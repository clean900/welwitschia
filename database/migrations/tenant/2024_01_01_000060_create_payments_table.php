<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pagamentos (schema do tenant). Motor com state machine + idempotência.
 * Estados: CREATED → PENDING → PAID → RECONCILED (↘ REJECTED ↘ EXPIRED ↘ MANUAL_REVIEW)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();          // referência ProxyPay
            $table->string('entity')->nullable();           // entidade ProxyPay
            $table->nullableMorphs('payable');              // factura, subscrição, módulo...
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('AOA');
            $table->string('status')->default('CREATED');
            $table->string('idempotency_key')->nullable()->unique(); // callback dedupe
            $table->jsonb('webhook_payload')->nullable();
            $table->decimal('paid_amount', 14, 2)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
