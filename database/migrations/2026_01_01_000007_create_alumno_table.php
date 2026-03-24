<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('familia_id')->nullable()->constrained('familia')->nullOnDelete()
                  ->comment('null si el alumno no tiene hermanos registrados');
            $table->string('matricula')->unique();
            $table->string('nombre');
            $table->string('ap_paterno');
            $table->string('ap_materno')->nullable();
            $table->date('fecha_nacimiento');
            $table->string('curp', 18)->nullable()->unique();
            $table->string('genero', 20)->nullable();
            $table->string('estado')->default('activo')
                  ->comment('activo | baja_temporal | baja_definitiva | egresado');
            $table->string('foto_url')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_inscripcion')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->timestamp('creado_at')->useCurrent();

            $table->index('familia_id', 'idx_alumno_familia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno');
    }
};
