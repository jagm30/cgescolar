<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beca_alumno', function (Blueprint $table) {
            $table->dropForeign(['alumno_id']);
            $table->dropForeign(['catalogo_beca_id']);
            $table->dropForeign(['concepto_id']);
            $table->dropUnique('uq_beca_alumno_ciclo');

            $table->foreignId('plan_id')
                ->nullable()
                ->after('ciclo_id')
                ->constrained('plan_pago')
                ->restrictOnDelete();

            $table->foreignId('concepto_id')
                ->nullable()
                ->comment('Compatibilidad con becas historicas asignadas por concepto')
                ->change();

            $table->foreign('alumno_id')->references('id')->on('alumno')->cascadeOnDelete();
            $table->foreign('catalogo_beca_id')->references('id')->on('catalogo_beca')->restrictOnDelete();
            $table->foreign('concepto_id')->references('id')->on('concepto_cobro')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('beca_alumno', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropForeign(['concepto_id']);
            $table->dropColumn('plan_id');

            $table->foreignId('concepto_id')
                ->nullable(false)
                ->comment('Debe tener aplica_beca = true')
                ->change();

            $table->foreign('concepto_id')->references('id')->on('concepto_cobro')->restrictOnDelete();
            $table->unique(
                ['alumno_id', 'catalogo_beca_id', 'concepto_id', 'ciclo_id'],
                'uq_beca_alumno_ciclo'
            );
        });
    }
};
