<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigFiscalSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('config_fiscal')->exists()) {
            $this->command->warn('  ConfigFiscalSeeder: ya existe configuración fiscal, omitiendo.');
            return;
        }

        DB::table('config_fiscal')->insert([
            'rfc'                => 'FGS200207LT1',
            'razon_social'       => 'FUNDACION GENKI SCHOOL DE CHIAPAS',
            'regimen_fiscal'     => '603',
            'cer_url'            => null,
            'key_url'            => null,
            'serie'              => 'F',
            'serie_id'           => 5497018,
            'folio_actual'       => 1,
            'publico_general_uid' => null,
        ]);

        $this->command->info('  ConfigFiscalSeeder: configuración fiscal insertada.');
    }
}
