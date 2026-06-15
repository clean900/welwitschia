<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gateway SMS por tenant (TelcoSMS). API Key encriptada — ACTIVADA PELO ADMIN.
 * O cliente nunca vê a api_key_enc; vê apenas estado, consumo, Sender ID, templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('telcosms');
            $table->text('api_key_enc')->nullable();
            $table->string('sender_id')->nullable();        // ex: "EMPRESA XYZ"
            $table->decimal('price_per_sms', 8, 2)->default(0);
            $table->boolean('active')->default(false);
            $table->unsignedBigInteger('activated_by_admin')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};
