<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->restrictOnDelete();
            $table->foreignId('ciclo_id')->constrained('ciclo_escolar')->restrictOnDelete();
            $table->foreignId('grupo_id')->constrained('grupo')->restrictOnDelete();
            $table->date('fecha');
            $table->boolean('activo')->default(true);

            $table->unique(['alumno_id', 'ciclo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion');
    }
};
