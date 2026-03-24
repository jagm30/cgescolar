<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plan_pago')->restrictOnDelete();
            $table->foreignId('alumno_id')->nullable()->constrained('alumno')->nullOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupo')->nullOnDelete();
            $table->foreignId('nivel_id')->nullable()->constrained('nivel_escolar')->nullOnDelete();
            $table->string('origen')->comment('individual | grupo | nivel');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Nota: validar a nivel aplicación que solo uno de
            // alumno_id, grupo_id o nivel_id tenga valor según origen
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_plan');
    }
};
