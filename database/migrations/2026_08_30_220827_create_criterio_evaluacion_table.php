<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Criterios de una escala literal.
 *
 * Cada criterio representa un nivel de la escala descriptiva
 * (ej: MA = Muy Avanzado, valor numérico 10) y su equivalente
 * numérico, necesario para calcular promedios.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterio_evaluacion', function (Blueprint $table) {
            $table->id();
            // FK agregada en 2026_08_30_220828_create_plan_estudios_table
            // (escala_evaluacion se crea en la misma tanda y corre después alfabéticamente)
            $table->unsignedBigInteger('escala_id');
            $table->string('etiqueta', 20);       // Ej: "MA", "A", "EnP", "I"
            $table->string('descripcion', 100);    // Ej: "Muy Avanzado"
            $table->decimal('valor_numerico', 5, 2); // Equivalente para cálculo de promedio
            $table->tinyInteger('orden')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterio_evaluacion');
    }
};
