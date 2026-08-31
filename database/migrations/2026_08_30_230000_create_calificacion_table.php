<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de calificaciones capturadas por el docente.
 *
 * Exactamente uno de estos tres campos debe tener valor según el tipo de asignación:
 *   - valor_numerico  → escala numérica (Primaria, Secundaria, Preparatoria)
 *   - criterio_id     → escala literal  (planes con criterios A/B/C)
 *   - texto_descriptivo → campo formativo (Preescolar, texto libre acotado)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificacion', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('alumno_id');
            $table->unsignedBigInteger('periodo_id');
            $table->unsignedBigInteger('asignacion_id');

            // Valor según el tipo de escala del plan
            $table->decimal('valor_numerico', 5, 2)->nullable();
            $table->unsignedBigInteger('criterio_id')->nullable();
            $table->text('texto_descriptivo')->nullable();

            // Auditoría
            $table->unsignedBigInteger('capturado_por');
            $table->timestamp('fecha_captura')->useCurrent();

            // Una sola calificación por alumno / período / asignación
            $table->unique(['alumno_id', 'periodo_id', 'asignacion_id'], 'calificacion_unica');

            // Claves foráneas
            $table->foreign('alumno_id')
                ->references('id')->on('alumno')
                ->onDelete('cascade');

            $table->foreign('periodo_id')
                ->references('id')->on('periodo_evaluativo')
                ->onDelete('cascade');

            $table->foreign('asignacion_id')
                ->references('id')->on('asignacion_docente')
                ->onDelete('cascade');

            $table->foreign('criterio_id')
                ->references('id')->on('criterio_evaluacion')
                ->onDelete('set null');

            $table->foreign('capturado_por')
                ->references('id')->on('usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificacion');
    }
};
