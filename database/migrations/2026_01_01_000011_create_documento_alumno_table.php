<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->cascadeOnDelete();
            $table->string('tipo_documento');
            $table->string('estado')->default('pendiente')
                  ->comment('pendiente | entregado | no_aplica');
            $table->string('archivo_url')->nullable();
            $table->date('fecha_entrega')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_alumno');
    }
};
