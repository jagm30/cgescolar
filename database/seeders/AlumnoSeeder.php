<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        // familia_id: 1=López García, 2=Martínez Ruiz, 3=Hernández Cruz, 4=Sánchez Morales
        // Familia 1 tiene 2 hermanos (Juan y Ana)
        // Familia 2 tiene 2 hermanos (Luis y Sofía)
        // Familia 3 tiene 1 hijo (Diego)
        // Familia 4 tiene 1 hijo (Valeria)

        DB::table('alumno')->insert([
            [
                'familia_id'       => 1,
                'matricula'        => '2025-0001',
                'nombre'           => 'Juan',
                'ap_paterno'       => 'López',
                'ap_materno'       => 'García',
                'fecha_nacimiento' => '2015-03-12',
                'curp'             => 'LOGJ150312HMCPRC01',
                'genero'           => 'M',
                'estado'           => 'activo',
                'foto_url'         => null,
                'fecha_inscripcion'=> '2025-08-20',
                'creado_at'        => now(),
            ],
            [
                'familia_id'       => 1,
                'matricula'        => '2025-0002',
                'nombre'           => 'Ana',
                'ap_paterno'       => 'López',
                'ap_materno'       => 'García',
                'fecha_nacimiento' => '2018-07-25',
                'curp'             => 'LOGA180725MMCPGR02',
                'genero'           => 'F',
                'estado'           => 'activo',
                'foto_url'         => null,
                'fecha_inscripcion'=> '2025-08-20',
                'creado_at'        => now(),
            ],
            [
                'familia_id'       => 2,
                'matricula'        => '2025-0003',
                'nombre'           => 'Luis',
                'ap_paterno'       => 'Martínez',
                'ap_materno'       => 'Ruiz',
                'fecha_nacimiento' => '2013-11-05',
                'curp'             => 'MARL131105HMCRZS03',
                'genero'           => 'M',
                'estado'           => 'activo',
                'foto_url'         => null,
                'fecha_inscripcion'=> '2025-08-20',
                'creado_at'        => now(),
            ],
            [
                'familia_id'       => 2,
                'matricula'        => '2025-0004',
                'nombre'           => 'Sofía',
                'ap_paterno'       => 'Martínez',
                'ap_materno'       => 'Ruiz',
                'fecha_nacimiento' => '2016-04-18',
                'curp'             => 'MARS160418MMCRZF04',
                'genero'           => 'F',
                'estado'           => 'activo',
                'foto_url'         => null,
                'fecha_inscripcion'=> '2025-08-20',
                'creado_at'        => now(),
            ],
            [
                'familia_id'       => 3,
                'matricula'        => '2025-0005',
                'nombre'           => 'Diego',
                'ap_paterno'       => 'Hernández',
                'ap_materno'       => 'Cruz',
                'fecha_nacimiento' => '2012-09-30',
                'curp'             => 'HECD120930HMCRZG05',
                'genero'           => 'M',
                'estado'           => 'activo',
                'foto_url'         => null,
                'fecha_inscripcion'=> '2025-08-20',
                'creado_at'        => now(),
            ],
            [
                'familia_id'       => 4,
                'matricula'        => '2025-0006',
                'nombre'           => 'Valeria',
                'ap_paterno'       => 'Sánchez',
                'ap_materno'       => 'Morales',
                'fecha_nacimiento' => '2019-01-14',
                'curp'             => 'SAMV190114MMCNRL06',
                'genero'           => 'F',
                'estado'           => 'activo',
                'foto_url'         => null,
                'fecha_inscripcion'=> '2025-08-20',
                'creado_at'        => now(),
            ],
        ]);
    }
}
