<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoria imutável com hash-chain (append-only).
 * chain_hash = SHA-256(prev_chain_hash + payload_hash). NUNCA update/delete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event');
            $table->string('auditable_type')->nullable();
            $table->string('auditable_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->jsonb('payload')->nullable();
            $table->char('payload_hash', 64);          // SHA-256 do payload
            $table->char('prev_chain_hash', 64)->nullable();
            $table->char('chain_hash', 64);            // SHA-256(prev_chain_hash + payload_hash)
            $table->timestamp('created_at')->nullable(); // append-only: sem updated_at

            $table->index('event');
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
