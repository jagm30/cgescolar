<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignacionPlanSeeder extends Seeder
{
    public function run(): void
    {
        // Asignaciones a nivel (aplica a todos los alumnos del nivel)
        // plan_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria
        // nivel_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria
        DB::table('asignacion_plan')->insert([
            ['plan_id' => 1, 'alumno_id' => null, 'grupo_id' => null, 'nivel_id' => 1, 'origen' => 'nivel', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30'],
            ['plan_id' => 2, 'alumno_id' => null, 'grupo_id' => null, 'nivel_id' => 2, 'origen' => 'nivel', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30'],
            ['plan_id' => 3, 'alumno_id' => null, 'grupo_id' => null, 'nivel_id' => 3, 'origen' => 'nivel', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30'],
            ['plan_id' => 4, 'alumno_id' => null, 'grupo_id' => null, 'nivel_id' => 4, 'origen' => 'nivel', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30'],
        ]);
    }
}
