<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condonacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->restrictOnDelete();
            $table->foreignId('ciclo_id')->constrained('ciclo_escolar')->restrictOnDelete();
            $table->decimal('monto_total', 10, 2)->comment('Suma de los montos aplicados a cargos');
            $table->text('motivo');
            $table->string('estado', 20)->default('activa')->comment('activa | cancelada');
            $table->foreignId('creado_por')->constrained('usuario')->restrictOnDelete();
            $table->timestamp('creado_at')->useCurrent();

            $table->index('alumno_id');
            $table->index('estado');
        });

        Schema::create('condonacion_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condonacion_id')->constrained('condonacion')->cascadeOnDelete();
            $table->foreignId('cargo_id')->constrained('cargo')->restrictOnDelete();
            $table->foreignId('descuento_cargo_id')->nullable()->constrained('descuento_cargo')->nullOnDelete();
            $table->decimal('monto_aplicado', 10, 2);
            $table->timestamp('creado_at')->useCurrent();

            $table->unique(['condonacion_id', 'cargo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condonacion_detalle');
        Schema::dropIfExists('condonacion');
    }
};
