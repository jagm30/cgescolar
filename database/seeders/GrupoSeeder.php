<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        // Construye mapa "nivel_id-numero" => grado_id dinámicamente
        // para no depender de IDs de auto-incremento.
        $gradoMap = Grado::all()->mapWithKeys(
            fn($g) => ["{$g->nivel_id}-{$g->numero}" => $g->id]
        );

        // nivel_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria
        // ciclo_id = 2 (ciclo 2025-2026 activo)
        $definicion = [
            // Maternal
            ['nivel_id' => 1, 'numero' => 1, 'nombre' => 'Conejos'],
            ['nivel_id' => 1, 'numero' => 2, 'nombre' => 'Gatos'],

            // Preescolar
            ['nivel_id' => 2, 'numero' => 1, 'nombre' => 'Pandas'],
            ['nivel_id' => 2, 'numero' => 1, 'nombre' => 'Pingüinos'],
            ['nivel_id' => 2, 'numero' => 2, 'nombre' => 'Elefantes'],
            ['nivel_id' => 2, 'numero' => 2, 'nombre' => 'Jaguares'],
            ['nivel_id' => 2, 'numero' => 3, 'nombre' => 'Dragones'],
            ['nivel_id' => 2, 'numero' => 3, 'nombre' => 'Halcones'],

            // Primaria
            ['nivel_id' => 3, 'numero' => 1, 'nombre' => 'Hamtaro'],
            ['nivel_id' => 3, 'numero' => 1, 'nombre' => 'Doraemon'],
            ['nivel_id' => 3, 'numero' => 2, 'nombre' => 'Totoro'],
            ['nivel_id' => 3, 'numero' => 2, 'nombre' => 'Kiki'],
            ['nivel_id' => 3, 'numero' => 3, 'nombre' => 'Pokemon'],
            ['nivel_id' => 3, 'numero' => 3, 'nombre' => 'Yokai'],
            ['nivel_id' => 3, 'numero' => 4, 'nombre' => 'Sonic'],
            ['nivel_id' => 3, 'numero' => 4, 'nombre' => 'Pacman'],
            ['nivel_id' => 3, 'numero' => 5, 'nombre' => 'Dragon B'],
            ['nivel_id' => 3, 'numero' => 5, 'nombre' => 'Saint S'],
            ['nivel_id' => 3, 'numero' => 6, 'nombre' => 'Gundam'],

            // Secundaria
            ['nivel_id' => 4, 'numero' => 1, 'nombre' => 'Shibuya'],
        ];

        $grupos = array_map(fn($def) => [
            'ciclo_id'    => 2,
            'grado_id'    => $gradoMap["{$def['nivel_id']}-{$def['numero']}"],
            'nombre'      => $def['nombre'],
            'cupo_maximo' => 25,
            'docente'     => null,
            'activo'      => true,
        ], $definicion);

        DB::table('grupo')->insert($grupos);
    }
}
