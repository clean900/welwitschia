<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gateway de pagamento por tenant (ProxyPay). API Key encriptada AES-256.
 * Configurado pelo CLIENTE no onboarding.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('proxypay');
            $table->string('environment')->default('sandbox'); // sandbox | production
            $table->text('api_key_enc')->nullable();
            $table->text('api_secret_enc')->nullable();
            $table->text('webhook_secret_enc')->nullable();
            $table->string('merchant_id')->nullable();
            $table->json('config')->nullable();
            $table->boolean('active')->default(false);
            $table->unsignedBigInteger('activated_by')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'environment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
