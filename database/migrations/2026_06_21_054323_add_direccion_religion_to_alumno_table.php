<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            $table->string('calle')->nullable()->after('observaciones');
            $table->string('colonia')->nullable()->after('calle');
            $table->string('codigo_postal', 10)->nullable()->after('colonia');
            $table->string('ciudad')->nullable()->after('codigo_postal');
            $table->string('estado_residencia')->nullable()->after('ciudad');
            $table->string('religion')->nullable()->after('estado_residencia');
        });
    }

    public function down(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            $table->dropColumn(['calle', 'colonia', 'codigo_postal', 'ciudad', 'estado_residencia', 'religion']);
        });
    }
};
