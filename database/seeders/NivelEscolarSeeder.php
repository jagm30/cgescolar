<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelEscolarSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('nivel_escolar')->insert([
            ['nombre' => 'Maternal',   'orden' => 1, 'activo' => true],
            ['nombre' => 'Preescolar', 'orden' => 2, 'activo' => true],
            ['nombre' => 'Primaria',   'orden' => 3, 'activo' => true],
            ['nombre' => 'Secundaria', 'orden' => 4, 'activo' => true],
        ]);
    }
}
