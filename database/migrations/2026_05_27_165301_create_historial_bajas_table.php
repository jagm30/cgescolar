<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_bajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->cascadeOnDelete();
            $table->foreignId('ciclo_id')->nullable()->constrained('ciclo_escolar')->nullOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('usuario')->nullOnDelete();
            $table->string('tipo')->comment('baja_temporal | baja_definitiva');
            $table->string('motivo_categoria')->comment('cambio_escuela | traslado | economico | familiar | salud | conducta | rendimiento | otro');
            $table->text('motivo_detalle')->nullable();
            $table->date('fecha_baja');
            $table->date('fecha_reactivacion')->nullable();
            $table->timestamp('creado_at')->useCurrent();

            $table->index('alumno_id');
            $table->index('ciclo_id');
            $table->index(['tipo', 'motivo_categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_bajas');
    }
};
