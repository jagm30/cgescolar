<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogo_beca', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo')->comment('porcentaje | monto_fijo');
            $table->decimal('valor', 10, 2)
                  ->comment('Si tipo=porcentaje: 0-100. Si tipo=monto_fijo: monto en pesos.');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_at')->useCurrent();
        });

        Schema::create('beca_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_beca_id')->constrained('catalogo_beca')->restrictOnDelete();
            $table->foreignId('alumno_id')->constrained('alumno')->cascadeOnDelete();
            $table->foreignId('ciclo_id')->constrained('ciclo_escolar')->restrictOnDelete();
            $table->foreignId('concepto_id')->constrained('concepto_cobro')->restrictOnDelete()
                  ->comment('Debe tener aplica_beca = true');
            $table->date('vigencia_inicio');
            $table->date('vigencia_fin')->nullable()->comment('null = aplica todo el ciclo');
            $table->text('motivo')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->nullable()->constrained('usuario')->nullOnDelete();
            $table->timestamp('creado_at')->useCurrent();

            $table->unique(
                ['alumno_id', 'catalogo_beca_id', 'concepto_id', 'ciclo_id'],
                'uq_beca_alumno_ciclo'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beca_alumno');
        Schema::dropIfExists('catalogo_beca');
    }
};
