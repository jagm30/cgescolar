<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos formativos (exclusivo Preescolar).
 *
 * En lugar de materias con calificación numérica, Preescolar usa
 * campos formativos donde el docente captura una evaluación descriptiva
 * en texto libre con límite de caracteres.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campo_formativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')
                ->constrained('plan_estudios')
                ->cascadeOnDelete();
            $table->string('nombre', 150);          // Ej: "Lenguaje y Comunicación"
            $table->smallInteger('max_caracteres')->default(500); // Límite del párrafo descriptivo
            $table->tinyInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campo_formativo');
    }
};
