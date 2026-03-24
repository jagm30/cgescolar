<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamiliaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('familia')->insert([
            ['apellido_familia' => 'Familia López García',    'observaciones' => null, 'activo' => true, 'creado_at' => now()],
            ['apellido_familia' => 'Familia Martínez Ruiz',   'observaciones' => null, 'activo' => true, 'creado_at' => now()],
            ['apellido_familia' => 'Familia Hernández Cruz',  'observaciones' => null, 'activo' => true, 'creado_at' => now()],
            ['apellido_familia' => 'Familia Sánchez Morales', 'observaciones' => null, 'activo' => true, 'creado_at' => now()],
        ]);
    }
}
