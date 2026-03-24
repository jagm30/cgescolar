<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciclo_escolar', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->comment('Ej: 2025-2026');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('configuracion')->comment('activo | cerrado | configuracion');
            $table->timestamp('creado_at')->useCurrent();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ciclo_escolar');
    }
};
