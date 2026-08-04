<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        // nivel_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria
        $grados = [
            // Maternal (2 grados)
            ['nivel_id' => 1, 'numero' => 1],
            ['nivel_id' => 1, 'numero' => 2],

            // Preescolar (3 grados)
            ['nivel_id' => 2, 'numero' => 1],
            ['nivel_id' => 2, 'numero' => 2],
            ['nivel_id' => 2, 'numero' => 3],

            // Primaria (6 grados)
            ['nivel_id' => 3, 'numero' => 1],
            ['nivel_id' => 3, 'numero' => 2],
            ['nivel_id' => 3, 'numero' => 3],
            ['nivel_id' => 3, 'numero' => 4],
            ['nivel_id' => 3, 'numero' => 5],
            ['nivel_id' => 3, 'numero' => 6],

            // Secundaria (3 grados)
            ['nivel_id' => 4, 'numero' => 1],
            ['nivel_id' => 4, 'numero' => 2],
            ['nivel_id' => 4, 'numero' => 3],
        ];

        DB::table('grado')->insert($grados);
    }
}
