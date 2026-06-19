<?php

namespace Database\Seeders;

use App\Models\Grupo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscripcionSeeder extends Seeder
{
    public function run(): void
    {
        // Construye mapa nombre_grupo => grupo_id para evitar IDs hardcodeados.
        $grupoId = Grupo::all()->pluck('id', 'nombre');

        DB::table('inscripcion')->insert([
            // Juan López — Primaria 4° (Sonic)
            ['alumno_id' => 1, 'ciclo_id' => 2, 'grupo_id' => $grupoId['Sonic'],    'fecha' => '2025-08-20', 'activo' => true],
            // Ana López — Preescolar 2° (Elefantes)
            ['alumno_id' => 2, 'ciclo_id' => 2, 'grupo_id' => $grupoId['Elefantes'], 'fecha' => '2025-08-20', 'activo' => true],
            // Luis Martínez — Secundaria 1° (Shibuya)
            ['alumno_id' => 3, 'ciclo_id' => 2, 'grupo_id' => $grupoId['Shibuya'],  'fecha' => '2025-08-20', 'activo' => true],
            // Sofía Martínez — Primaria 1° (Hamtaro)
            ['alumno_id' => 4, 'ciclo_id' => 2, 'grupo_id' => $grupoId['Hamtaro'],  'fecha' => '2025-08-20', 'activo' => true],
            // Diego Hernández — Secundaria 1° (Shibuya)
            ['alumno_id' => 5, 'ciclo_id' => 2, 'grupo_id' => $grupoId['Shibuya'],  'fecha' => '2025-08-20', 'activo' => true],
            // Valeria Sánchez — Maternal 1° (Conejos)
            ['alumno_id' => 6, 'ciclo_id' => 2, 'grupo_id' => $grupoId['Conejos'],  'fecha' => '2025-08-20', 'activo' => true],
        ]);
    }
}
