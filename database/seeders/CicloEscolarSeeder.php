<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CicloEscolarSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ciclo_escolar')->insert([
            [
                'nombre'       => '2024-2025',
                'fecha_inicio' => '2024-08-26',
                'fecha_fin'    => '2025-06-27',
                'estado'       => 'cerrado',
                'creado_at'    => now(),
            ],
            [
                'nombre'       => '2025-2026',
                'fecha_inicio' => '2025-08-25',
                'fecha_fin'    => '2026-06-26',
                'estado'       => 'activo',
                'creado_at'    => now(),
            ],
            [
                'nombre'       => '2026-2027',
                'fecha_inicio' => '2026-08-24',
                'fecha_fin'    => '2027-06-25',
                'estado'       => 'configuracion',
                'creado_at'    => now(),
            ],
        ]);
    }
}
