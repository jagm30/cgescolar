<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asignacion_plan_concepto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignacion_plan')->cascadeOnDelete();
            $table->foreignId('concepto_id')->constrained('concepto_cobro')->restrictOnDelete();
            $table->decimal('monto', 10, 2)->comment('Monto específico para esta asignación');

            $table->unique(['asignacion_id', 'concepto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_plan_concepto');
    }
};
