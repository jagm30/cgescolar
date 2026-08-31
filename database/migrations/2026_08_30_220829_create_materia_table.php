<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Materias de un plan de estudios.
 *
 * Aplica a Primaria, Secundaria y Preparatoria.
 * Preescolar usa campo_formativo en su lugar.
 * El campo `tipo` distingue materias SEP de institucionales
 * para el cálculo de promedios diferenciados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')
                ->constrained('plan_estudios')
                ->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('clave_sep', 30)->nullable(); // Clave oficial SEP si aplica
            $table->enum('tipo', ['sep', 'institucional']);
            $table->tinyInteger('horas_semanales')->default(0);
            $table->tinyInteger('orden')->default(0);   // Para ordenar en boleta y captura
            $table->boolean('activa')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia');
    }
};
