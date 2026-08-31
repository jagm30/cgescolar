<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Planes de estudio.
 *
 * Currículo de un nivel académico. Puede ser genérico (ciclo_id null)
 * o específico para un ciclo cuando hay cambio de materias.
 * Regla de resolución: se prefiere el plan con ciclo_id coincidente;
 * si no existe, se usa el genérico.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ahora que escala_evaluacion ya existe, añadimos la FK de criterio_evaluacion
        Schema::table('criterio_evaluacion', function (Blueprint $table) {
            $table->foreign('escala_id')
                ->references('id')
                ->on('escala_evaluacion')
                ->cascadeOnDelete();
        });

        Schema::create('plan_estudios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_id')
                ->constrained('nivel_escolar')
                ->restrictOnDelete();
            $table->foreignId('escala_id')
                ->constrained('escala_evaluacion')
                ->restrictOnDelete();

            // null = aplica a todos los ciclos; valor = específico para ese ciclo
            $table->foreignId('ciclo_id')
                ->nullable()
                ->constrained('ciclo_escolar')
                ->nullOnDelete();

            $table->string('nombre', 150);
            $table->enum('tipo_periodo', ['trimestral', 'bimestral']);
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_estudios');

        Schema::table('criterio_evaluacion', function (Blueprint $table) {
            $table->dropForeign(['escala_id']);
        });
    }
};
