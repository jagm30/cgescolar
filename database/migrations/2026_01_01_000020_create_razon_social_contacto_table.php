<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('razon_social_contacto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contacto_id')->constrained('contacto_familiar')->cascadeOnDelete()
                  ->comment('RFCs de la persona física. Aplican para todos los hijos de la familia');
            $table->string('rfc', 13);
            $table->string('razon_social');
            $table->string('regimen_fiscal', 10);
            $table->string('domicilio_fiscal', 10)
                  ->comment('Código postal requerido por SAT desde 2022');
            $table->string('uso_cfdi_default', 10)
                  ->comment('Ej: D10 Servicios educativos');
            $table->boolean('es_principal')->default(false)
                  ->comment('RFC que se preselecciona al facturar');
            $table->foreignId('registrado_por')->nullable()->constrained('usuario')->nullOnDelete()
                  ->comment('Cajero que capturó los datos');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_at')->useCurrent();

            $table->unique(['contacto_id', 'rfc'], 'uq_rfc_contacto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('razon_social_contacto');
    }
};
