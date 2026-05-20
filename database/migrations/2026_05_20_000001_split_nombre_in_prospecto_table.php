<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separa el campo 'nombre' de la tabla prospecto en tres columnas
     * para que coincida con la estructura de la tabla alumno:
     * nombre, ap_paterno, ap_materno.
     *
     * IMPORTANTE: Si ya hay datos en producción, ejecutar primero
     * el script de transformación que divide el campo nombre existente.
     */
    public function up(): void
    {
        Schema::table('prospecto', function (Blueprint $table) {
            // Renombrar 'nombre' a 'nombre' solo cambia el orden,
            // agregamos los dos apellidos después de él
            $table->string('ap_paterno')->nullable()->after('nombre');
            $table->string('ap_materno')->nullable()->after('ap_paterno');
        });
    }

    public function down(): void
    {
        Schema::table('prospecto', function (Blueprint $table) {
            $table->dropColumn(['ap_paterno', 'ap_materno']);
        });
    }
};
