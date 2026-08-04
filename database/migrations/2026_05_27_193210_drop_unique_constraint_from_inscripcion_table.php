<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            // La FK usa el índice único como soporte; hay que soltarla primero.
            // dropForeign(['alumno_id']) en vez del nombre de constraint: SQLite solo
            // puede reconstruir la FK cuando conoce las columnas (usado en tests).
            $table->dropForeign(['alumno_id']);
            $table->dropUnique('inscripcion_alumno_id_ciclo_id_unique');

            // Índice normal para rendimiento en búsquedas por alumno+ciclo
            $table->index(['alumno_id', 'ciclo_id'], 'inscripcion_alumno_ciclo_idx');

            // Restaurar FK sobre alumno_id
            $table->foreign('alumno_id', 'inscripcion_alumno_id_foreign')
                ->references('id')->on('alumno')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            $table->dropForeign(['alumno_id']);
            $table->dropIndex('inscripcion_alumno_ciclo_idx');

            $table->unique(['alumno_id', 'ciclo_id'], 'inscripcion_alumno_id_ciclo_id_unique');

            $table->foreign('alumno_id', 'inscripcion_alumno_id_foreign')
                ->references('id')->on('alumno')->cascadeOnDelete();
        });
    }
};
