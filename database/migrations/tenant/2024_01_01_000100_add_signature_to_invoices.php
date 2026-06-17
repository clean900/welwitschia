<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assinatura digital / cadeia de hash por factura (faturação certificada AGT).
 * // VALIDAR COM AGT — formato do hash/assinatura conforme especificação oficial.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('hash')->nullable();          // assinatura base64 do documento
            $table->text('previous_hash')->nullable();  // encadeamento ao documento anterior
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['hash', 'previous_hash']);
        });
    }
};
