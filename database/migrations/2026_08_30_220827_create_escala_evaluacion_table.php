<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Escalas de evaluación.
 *
 * Define cómo se califica en cada nivel académico:
 * numérica (6–10) o literal (MA, A, EnP, I).
 * Los criterios de una escala literal se almacenan en criterio_evaluacion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escala_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['numerica', 'literal']);

            // Solo relevantes para escalas numéricas
            $table->decimal('valor_minimo', 5, 2)->nullable();
            $table->decimal('valor_maximo', 5, 2)->nullable();
            $table->decimal('valor_aprobatorio', 5, 2)->nullable();

            $table->boolean('activa')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escala_evaluacion');
    }
};
