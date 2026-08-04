<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Amplía `periodo` de varchar(7) a varchar(10) para soportar
     * el formato YYYY-MM-DD que usan los cargos de tipo cargo_recurrente
     * generados desde el POS (desayunos, comidas, etc.).
     *
     * Los cargos mensuales (colegiatura, inscripción) siguen usando YYYY-MM.
     * Los recurrentes del POS usan YYYY-MM-DD para permitir múltiples cobros
     * del mismo concepto en el mismo mes sin violar la restricción única.
     */
    public function up(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->string('periodo', 10)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cargo', function (Blueprint $table) {
            $table->string('periodo', 7)->change();
        });
    }
};
