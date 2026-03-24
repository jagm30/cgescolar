<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactoFamiliarSeeder extends Seeder
{
    public function run(): void
    {
        // usuario_id: 4=Roberto López, 5=María García, 6=Carlos Martínez
        DB::table('contacto_familiar')->insert([
            // Familia 1 López García — papá con acceso (usuario 4), mamá con acceso (usuario 5)
            [
                'familia_id'          => 1,
                'tiene_acceso_portal' => true,
                'usuario_id'          => 4,
                'nombre'              => 'Roberto',
                'ap_paterno'          => 'López',
                'ap_materno'          => 'Vega',
                'telefono_celular'    => '5512345678',
                'telefono_trabajo'    => null,
                'email'               => 'roberto.lopez@gmail.com',
                'curp'                => 'LOVR820315HMCPGB01',
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
            [
                'familia_id'          => 1,
                'tiene_acceso_portal' => true,
                'usuario_id'          => 5,
                'nombre'              => 'María',
                'ap_paterno'          => 'García',
                'ap_materno'          => 'Ruiz',
                'telefono_celular'    => '5587654321',
                'telefono_trabajo'    => '5551234567',
                'email'               => 'maria.garcia@gmail.com',
                'curp'                => 'GARM850720MMCRZR02',
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
            // Familia 1 — abuela sin acceso
            [
                'familia_id'          => 1,
                'tiene_acceso_portal' => false,
                'usuario_id'          => null,
                'nombre'              => 'Carmen',
                'ap_paterno'          => 'Vega',
                'ap_materno'          => 'Torres',
                'telefono_celular'    => '5599887766',
                'telefono_trabajo'    => null,
                'email'               => null,
                'curp'                => null,
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
            // Familia 2 Martínez Ruiz — papá con acceso (usuario 6), mamá pendiente
            [
                'familia_id'          => 2,
                'tiene_acceso_portal' => true,
                'usuario_id'          => 6,
                'nombre'              => 'Carlos',
                'ap_paterno'          => 'Martínez',
                'ap_materno'          => 'Soto',
                'telefono_celular'    => '5544332211',
                'telefono_trabajo'    => null,
                'email'               => 'carlos.martinez@gmail.com',
                'curp'                => 'MASC780912HMCRTS03',
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
            [
                'familia_id'          => 2,
                'tiene_acceso_portal' => true,
                'usuario_id'          => null, // pendiente de crear usuario
                'nombre'              => 'Laura',
                'ap_paterno'          => 'Ruiz',
                'ap_materno'          => 'Mendoza',
                'telefono_celular'    => '5566778899',
                'telefono_trabajo'    => null,
                'email'               => 'laura.ruiz@gmail.com',
                'curp'                => 'RUML800405MMCZDL04',
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
            // Familia 3 Hernández Cruz — mamá sin acceso
            [
                'familia_id'          => 3,
                'tiene_acceso_portal' => false,
                'usuario_id'          => null,
                'nombre'              => 'Patricia',
                'ap_paterno'          => 'Cruz',
                'ap_materno'          => 'Lara',
                'telefono_celular'    => '5511223344',
                'telefono_trabajo'    => null,
                'email'               => 'patricia.cruz@gmail.com',
                'curp'                => null,
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
            // Familia 4 Sánchez Morales — mamá con acceso pendiente
            [
                'familia_id'          => 4,
                'tiene_acceso_portal' => true,
                'usuario_id'          => null,
                'nombre'              => 'Elena',
                'ap_paterno'          => 'Sánchez',
                'ap_materno'          => 'Peña',
                'telefono_celular'    => '5577889900',
                'telefono_trabajo'    => null,
                'email'               => 'elena.sanchez@gmail.com',
                'curp'                => 'SAPE900630MMCNLN05',
                'foto_url'            => null,
                'creado_at'           => now(),
            ],
        ]);
    }
}
