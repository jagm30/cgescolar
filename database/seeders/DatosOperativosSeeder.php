<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatosOperativosSeeder extends Seeder
{
    /**
     * Carga los datos operativos del ciclo 2025-2026:
     *   - familia        (250 registros)
     *   - alumno         (293 registros)
     *   - contacto_familiar (416 registros)
     *   - alumno_contacto   (512 registros)
     *   - inscripcion    (293 registros)
     *
     * Los datos provienen del archivo:
     *   database/seeders/sql/datos_operativos.sql
     *
     * El archivo incluye SET FOREIGN_KEY_CHECKS=0/1, por lo que
     * el orden de inserción no depende de FK constraints.
     */
    public function run(): void
    {
        if (DB::table('alumno')->exists()) {
            $this->command->warn('  DatosOperativosSeeder: ya existen alumnos, omitiendo.');
            return;
        }

        $archivo = database_path('seeders/sql/datos_operativos.sql');

        if (! file_exists($archivo)) {
            $this->command->error("  DatosOperativosSeeder: archivo no encontrado: {$archivo}");
            return;
        }

        $this->command->info('  DatosOperativosSeeder: ejecutando SQL de datos operativos...');

        DB::unprepared(file_get_contents($archivo));

        $alumnos     = DB::table('alumno')->count();
        $contactos   = DB::table('contacto_familiar')->count();
        $familias    = DB::table('familia')->count();
        $inscripciones = DB::table('inscripcion')->count();

        $this->command->info("  DatosOperativosSeeder: {$familias} familias | {$alumnos} alumnos | {$contactos} contactos | {$inscripciones} inscripciones.");
    }
}
