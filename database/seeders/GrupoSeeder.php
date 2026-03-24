<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        // ciclo_id = 2 (2025-2026 activo)
        // grado_id según GradoSeeder:
        //   1=Maternal 1°
        //   2=Preescolar 1°, 3=Preescolar 2°, 4=Preescolar 3°
        //   5=Primaria 1°... 10=Primaria 6°
        //   11=Secundaria 1°, 12=Secundaria 2°, 13=Secundaria 3°

        $grupos = [];
        $gradosConGrupos = [
            1  => 'Maternal 1°',
            2  => 'Preescolar 1°',
            3  => 'Preescolar 2°',
            4  => 'Preescolar 3°',
            5  => 'Primaria 1°',
            6  => 'Primaria 2°',
            7  => 'Primaria 3°',
            8  => 'Primaria 4°',
            9  => 'Primaria 5°',
            10 => 'Primaria 6°',
            11 => 'Secundaria 1°',
            12 => 'Secundaria 2°',
            13 => 'Secundaria 3°',
        ];

        foreach ($gradosConGrupos as $gradoId => $etiqueta) {
            foreach (['A', 'B'] as $letra) {
                $grupos[] = [
                    'ciclo_id'    => 2,
                    'grado_id'    => $gradoId,
                    'nombre'      => $letra,
                    'cupo_maximo' => 25,
                    'docente'     => null,
                    'activo'      => true,
                ];
            }
        }

        DB::table('grupo')->insert($grupos);
    }
}
