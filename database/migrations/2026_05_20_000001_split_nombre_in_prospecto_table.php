<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table('prospecto')
            ->orderBy('id')
            ->each(function (object $prospecto): void {
                $partes = preg_split('/\s+/', trim((string) $prospecto->nombre), -1, PREG_SPLIT_NO_EMPTY);

                if (count($partes) < 2) {
                    return;
                }

                $apMaterno = count($partes) > 2 ? array_pop($partes) : null;
                $apPaterno = array_pop($partes);

                DB::table('prospecto')
                    ->where('id', $prospecto->id)
                    ->update([
                        'nombre' => implode(' ', $partes),
                        'ap_paterno' => $apPaterno,
                        'ap_materno' => $apMaterno,
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('prospecto')
            ->orderBy('id')
            ->each(function (object $prospecto): void {
                DB::table('prospecto')
                    ->where('id', $prospecto->id)
                    ->update([
                        'nombre' => trim(preg_replace('/\s+/', ' ', "{$prospecto->nombre} {$prospecto->ap_paterno} {$prospecto->ap_materno}")),
                    ]);
            });

        Schema::table('prospecto', function (Blueprint $table) {
            $table->dropColumn(['ap_paterno', 'ap_materno']);
        });
    }
};
