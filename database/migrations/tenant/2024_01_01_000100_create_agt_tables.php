<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Integração AGT FE — configuração do emissor, séries e campos na factura.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Configuração do emissor (empresa): NIF + chave privada RSA registada na AGT.
        Schema::create('agt_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tax_registration_number');         // NIF do emissor
            $table->string('establishment_number')->default('001');
            $table->text('private_key')->nullable();           // chave do emissor (encriptada)
            $table->boolean('active')->default(false);
            $table->timestamps();
        });

        // Séries atribuídas pela AGT (por tipo de documento e ano).
        Schema::create('agt_series', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');                   // FT, FR, ND, NC...
            $table->unsignedSmallInteger('year');
            $table->string('series_code');
            $table->unsignedInteger('authorized_qty')->default(0);
            $table->unsignedInteger('current_number')->default(0);
            $table->string('establishment_number')->default('001');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['document_type', 'year']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('document_type')->default('FT')->after('number');
            $table->string('customer_country')->default('AO')->after('customer_nif');
            $table->string('agt_status')->nullable();           // SUBMETIDA | VALIDADA | REJEITADA
            $table->string('agt_request_id')->nullable();
            $table->timestamp('agt_submitted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'customer_country', 'agt_status', 'agt_request_id', 'agt_submitted_at']);
        });
        Schema::dropIfExists('agt_series');
        Schema::dropIfExists('agt_settings');
    }
};
