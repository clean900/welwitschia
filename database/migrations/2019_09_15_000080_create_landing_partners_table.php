<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Parceiros/clientes exibidos na landing ("empresas que confiam") — schema central,
 * geríveis pelo super-admin no back-office.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_partners');
    }
};
