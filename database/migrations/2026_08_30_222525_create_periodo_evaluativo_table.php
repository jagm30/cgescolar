<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Períodos evaluativos.
 *
 * Cada ciclo escolar genera uno o más períodos por plan de estudios
 * (3 trimestres para Primaria/Secundaria/Preescolar, 2 bimestres para Preparatoria).
 *
 * El administrador controla el estado: pendiente → abierto → cerrado.
 * Solo cuando está abierto los docentes pueden capturar calificaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodo_evaluativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')
                ->constrained('ciclo_escolar')
                ->restrictOnDelete();
            $table->foreignId('plan_id')
                ->constrained('plan_estudios')
                ->restrictOnDelete();

            $table->string('nombre', 50);          // "1er Trimestre", "2do Bimestre"
            $table->tinyInteger('numero');          // 1, 2, 3...
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Control de captura
            $table->enum('estado', ['pendiente', 'abierto', 'cerrado'])->default('pendiente');
            $table->dateTime('fecha_apertura_captura')->nullable();
            $table->dateTime('fecha_cierre_captura')->nullable();

            $table->unique(['ciclo_id', 'plan_id', 'numero'], 'uq_periodo_ciclo_plan_numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodo_evaluativo');
    }
};
