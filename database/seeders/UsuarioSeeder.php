<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // ciclo_seleccionado_id = 2 (ciclo 2025-2026 activo)
        DB::table('usuario')->insert([
            [
                'ciclo_seleccionado_id' => 2,
                'nombre'               => 'Administrador General',
                'email'                => 'admin@escuela.edu.mx',
                'password_hash'        => Hash::make('Admin2025!'),
                'rol'                  => 'administrador',
                'activo'               => true,
                'ultimo_acceso'        => null,
                'creado_at'            => now(),
            ],
            [
                'ciclo_seleccionado_id' => 2,
                'nombre'               => 'María Cajero López',
                'email'                => 'caja@escuela.edu.mx',
                'password_hash'        => Hash::make('Caja2025!'),
                'rol'                  => 'caja',
                'activo'               => true,
                'ultimo_acceso'        => null,
                'creado_at'            => now(),
            ],
            [
                'ciclo_seleccionado_id' => 2,
                'nombre'               => 'Ana Recepción Gómez',
                'email'                => 'recepcion@escuela.edu.mx',
                'password_hash'        => Hash::make('Recepcion2025!'),
                'rol'                  => 'recepcion',
                'activo'               => true,
                'ultimo_acceso'        => null,
                'creado_at'            => now(),
            ],
            // Usuarios padre (se vinculan en ContactoFamiliarSeeder)
            [
                'ciclo_seleccionado_id' => null,
                'nombre'               => 'Roberto López García',
                'email'                => 'roberto.lopez@gmail.com',
                'password_hash'        => Hash::make('Padre2025!'),
                'rol'                  => 'padre',
                'activo'               => true,
                'ultimo_acceso'        => null,
                'creado_at'            => now(),
            ],
            [
                'ciclo_seleccionado_id' => null,
                'nombre'               => 'María García Ruiz',
                'email'                => 'maria.garcia@gmail.com',
                'password_hash'        => Hash::make('Padre2025!'),
                'rol'                  => 'padre',
                'activo'               => true,
                'ultimo_acceso'        => null,
                'creado_at'            => now(),
            ],
            [
                'ciclo_seleccionado_id' => null,
                'nombre'               => 'Carlos Martínez Vega',
                'email'                => 'carlos.martinez@gmail.com',
                'password_hash'        => Hash::make('Padre2025!'),
                'rol'                  => 'padre',
                'activo'               => true,
                'ultimo_acceso'        => null,
                'creado_at'            => now(),
            ],
        ]);
    }
}
