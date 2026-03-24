<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concepto_cobro', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo')->comment('colegiatura | inscripcion | cargo_unico | cargo_recurrente');
            $table->boolean('aplica_beca')->default(false)
                  ->comment('Solo true en conceptos tipo colegiatura');
            $table->boolean('aplica_recargo')->default(false);
            $table->string('clave_sat')->nullable()->comment('Clave producto/servicio SAT para CFDI');
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concepto_cobro');
    }
};
