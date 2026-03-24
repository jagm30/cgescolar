<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_id')->constrained('ciclo_escolar')->restrictOnDelete();
            $table->foreignId('grado_id')->constrained('grado')->restrictOnDelete();
            $table->string('nombre')->comment('A, B, C...');
            $table->unsignedSmallInteger('cupo_maximo')->nullable();
            $table->string('docente')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
