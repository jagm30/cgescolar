<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscripcionSeeder extends Seeder
{
    public function run(): void
    {
        // ciclo_id = 2 (2025-2026)
        // Grupos (GrupoSeeder, ciclo 2025-2026, orden alfabético por grado):
        //   grupo 1 = Maternal 1° A,  grupo 2 = Maternal 1° B
        //   grupo 3 = Preescolar 1° A, grupo 4 = Preescolar 1° B
        //   grupo 5 = Preescolar 2° A, grupo 6 = Preescolar 2° B
        //   grupo 7 = Preescolar 3° A, grupo 8 = Preescolar 3° B
        //   grupo 9 = Primaria 1° A,  grupo 10 = Primaria 1° B
        //   grupo 11 = Primaria 2° A, grupo 12 = Primaria 2° B
        //   ...
        //   grupo 21 = Secundaria 1° A, grupo 22 = Secundaria 1° B
        //   grupo 23 = Secundaria 2° A, grupo 24 = Secundaria 2° B
        //   grupo 25 = Secundaria 3° A, grupo 26 = Secundaria 3° B

        DB::table('inscripcion')->insert([
            // Juan López — Primaria 4° A (grupo 17)
            ['alumno_id' => 1, 'ciclo_id' => 2, 'grupo_id' => 17, 'fecha' => '2025-08-20', 'activo' => true],
            // Ana López — Preescolar 2° A (grupo 5)
            ['alumno_id' => 2, 'ciclo_id' => 2, 'grupo_id' => 5,  'fecha' => '2025-08-20', 'activo' => true],
            // Luis Martínez — Secundaria 1° A (grupo 21)
            ['alumno_id' => 3, 'ciclo_id' => 2, 'grupo_id' => 21, 'fecha' => '2025-08-20', 'activo' => true],
            // Sofía Martínez — Primaria 1° B (grupo 10)
            ['alumno_id' => 4, 'ciclo_id' => 2, 'grupo_id' => 10, 'fecha' => '2025-08-20', 'activo' => true],
            // Diego Hernández — Secundaria 2° A (grupo 23)
            ['alumno_id' => 5, 'ciclo_id' => 2, 'grupo_id' => 23, 'fecha' => '2025-08-20', 'activo' => true],
            // Valeria Sánchez — Maternal 1° A (grupo 1)
            ['alumno_id' => 6, 'ciclo_id' => 2, 'grupo_id' => 1,  'fecha' => '2025-08-20', 'activo' => true],
        ]);
    }
}
