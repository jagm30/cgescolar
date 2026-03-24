<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripcion')->restrictOnDelete();
            $table->foreignId('concepto_id')->constrained('concepto_cobro')->restrictOnDelete();
            $table->foreignId('asignacion_id')->nullable()->constrained('asignacion_plan')->nullOnDelete();
            $table->foreignId('generado_por')->nullable()->constrained('usuario')->nullOnDelete()
                  ->comment('Admin que ejecutó la generación');
            $table->decimal('monto_original', 10, 2)->comment('Monto base del plan. Inmutable.');
            $table->date('fecha_vencimiento');
            $table->string('estado')->default('pendiente')
                  ->comment('pendiente | parcial | pagado | condonado — vencido se calcula en tiempo real');
            $table->string('periodo', 7)->comment('Ej: 2025-09');
            $table->timestamp('generado_at')->useCurrent();

            $table->unique(['inscripcion_id', 'concepto_id', 'periodo'], 'uq_cargo_periodo');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargo');
    }
};
