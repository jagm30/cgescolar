<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelEscolarSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nivel_escolar')->insert([
            ['nombre' => 'Maternal',   'revoe' => null,         'orden' => 1, 'activo' => true],
            ['nombre' => 'Preescolar', 'revoe' => 'REVOE-12345','orden' => 2, 'activo' => true],
            ['nombre' => 'Primaria',   'revoe' => 'REVOE-23456','orden' => 3, 'activo' => true],
            ['nombre' => 'Secundaria', 'revoe' => 'REVOE-34567','orden' => 4, 'activo' => true],
        ]);
    }
}
