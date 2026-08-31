<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asignaciones de docente.
 *
 * Vincula un docente con una materia (o campo formativo) en un grupo
 * durante un ciclo escolar. Es la fuente de verdad de qué puede capturar
 * cada docente al iniciar sesión.
 *
 * - Primaria / Secundaria / Preparatoria: materia_id requerido, campo_id null.
 * - Preescolar: campo_id requerido, materia_id null.
 *   (Un docente puede tener varios campos asignados en el mismo grupo.)
 *
 * Unicidad: un docente no puede tener la misma materia en el mismo grupo y ciclo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_docente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('docente_id')
                ->constrained('personal')
                ->restrictOnDelete();
            $table->foreignId('grupo_id')
                ->constrained('grupo')
                ->restrictOnDelete();
            $table->foreignId('ciclo_id')
                ->constrained('ciclo_escolar')
                ->restrictOnDelete();

            // Exactamente uno de estos debe estar presente
            $table->foreignId('materia_id')
                ->nullable()
                ->constrained('materia')
                ->nullOnDelete();
            $table->foreignId('campo_id')
                ->nullable()
                ->constrained('campo_formativo')
                ->nullOnDelete();

            $table->boolean('activa')->default(true);

            $table->unique(
                ['docente_id', 'grupo_id', 'ciclo_id', 'materia_id'],
                'uq_asignacion_materia'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_docente');
    }
};
