<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('settings')->exists()) {
            $this->command->warn('  SettingsSeeder: ya existe configuración, omitiendo.');
            return;
        }

        DB::table('settings')->insert([
            'nombre_escuela' => 'FUNDACION GENKI SCHOOL DE CHIAPAS',
            'logo_ruta'      => 'logo_reportes.png',
            'created_at'     => '2026-07-04 04:18:33',
            'updated_at'     => '2026-07-04 04:18:33',
        ]);

        $this->command->info('  SettingsSeeder: configuración de escuela insertada.');
    }
}
