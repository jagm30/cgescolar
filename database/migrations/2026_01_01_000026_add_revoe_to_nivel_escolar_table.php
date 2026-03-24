<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el número REVOE (Reconocimiento de Validez Oficial de Estudios)
     * a la tabla nivel_escolar. Cada nivel tiene su propio número asignado
     * por la SEP.
     */
    public function up(): void
    {
        Schema::table('nivel_escolar', function (Blueprint $table) {
            $table->string('revoe', 50)
                  ->nullable()
                  ->after('nombre')
                  ->comment('Número de Reconocimiento de Validez Oficial de Estudios otorgado por la SEP');
        });
    }

    public function down(): void
    {
        Schema::table('nivel_escolar', function (Blueprint $table) {
            $table->dropColumn('revoe');
        });
    }
};
