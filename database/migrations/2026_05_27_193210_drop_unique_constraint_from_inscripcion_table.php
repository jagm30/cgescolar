<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion', function (Blueprint $table) {
            // La FK usa el índice único como soporte; hay que soltarla primero
            $table->dropForeign('inscripcion_alumno_id_foreign');
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
            $table->dropForeign('inscripcion_alumno_id_foreign');
            $table->dropIndex('inscripcion_alumno_ciclo_idx');

            $table->unique(['alumno_id', 'ciclo_id'], 'inscripcion_alumno_id_ciclo_id_unique');

            $table->foreign('alumno_id', 'inscripcion_alumno_id_foreign')
                  ->references('id')->on('alumno')->cascadeOnDelete();
        });
    }
};
