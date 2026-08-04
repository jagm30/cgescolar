<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Contraseña generada: [3 letras ap. paterno][3 letras ap. materno]2025!
     * Ejemplo: Villegas + Perez → VilPer2025!
     * Admin: Admin2025!
     */
    private const USUARIOS = [
        // ── Administradores ──────────────────────────────────────────────────
        [
            'nombre'      => 'Administrador General',
            'email'       => 'admin@escuela.edu.mx',
            'rol'         => 'administrador',
            'ap_paterno'  => 'Admin',
            'ap_materno'  => '2025',         // genera Admin202 → se sobreescribe abajo
            '_password'   => 'Admin2025!',   // contraseña fija para el admin genérico
        ],
        [
            'nombre'     => 'Carmen Guadalupe Villegas Perez',
            'email'      => 'cvillegas@vyce.com.mx',
            'rol'        => 'administrador',
            'ap_paterno' => 'Villegas',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Veronica Perez Zuñiga',
            'email'      => 'tesoreriagenkituxtla@gmail.com',
            'rol'        => 'administrador',
            'ap_paterno' => 'Perez',
            'ap_materno' => 'Zuniga',
        ],
        // ── Caja ─────────────────────────────────────────────────────────────
        [
            'nombre'     => 'Guadalupe Hernandez Perez',
            'email'      => 'auxiliargenkituxtla@gmail.com',
            'rol'        => 'caja',
            'ap_paterno' => 'Hernandez',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Paola Lizeth De Los Santos Solis',
            'email'      => 'auxiliarcontable2genki@gmail.com',
            'rol'        => 'caja',
            'ap_paterno' => 'Santos',
            'ap_materno' => 'Solis',
        ],
        // ── Recepción ────────────────────────────────────────────────────────
        [
            'nombre'     => 'Julysa Miroslava Carrasco Grajales',
            'email'      => 'direccionprimaria.genkischool@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Carrasco',
            'ap_materno' => 'Grajales',
        ],
        [
            'nombre'     => 'Yanifiath Guizar Manzur',
            'email'      => 'dir.primariagenkischooltuxtla@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Guizar',
            'ap_materno' => 'Manzur',
        ],
        [
            'nombre'     => 'Nashiely Itzayana Espinosa Perez',
            'email'      => 'direcciongenkischool@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Espinosa',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Edy Nelson Somoza Marroquin',
            'email'      => 'direccionacademica.genkischool@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Somoza',
            'ap_materno' => 'Marroquin',
        ],
        [
            'nombre'     => 'Kareem Guadalupe Hernandez Perez',
            'email'      => 'coord.tec.k.genkischool1@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Hernandez',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Landi Magally Morales Carpio',
            'email'      => 'coordinacionprimariagenki@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Morales',
            'ap_materno' => 'Carpio',
        ],
        // ── Admisiones ───────────────────────────────────────────────────────
        [
            'nombre'     => 'Ivonne Lizeth Martinez Velazquez',
            'email'      => 'admisionesgenkischool@gmail.com',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Martinez',
            'ap_materno' => 'Velazquez',
        ],
        [
            'nombre'     => 'Lidia Flores Pichardo',
            'email'      => 'coordinacion@genkischool.com.mx',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Flores',
            'ap_materno' => 'Pichardo',
        ],
        [
            'nombre'     => 'Karen Guadalupe Vazquez Vazquez',
            'email'      => 'psicopedagogico.genki@gmail.com',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Vazquez',
            'ap_materno' => 'Vazquez',
        ],
        [
            'nombre'     => 'Valeria Monserrath Mena Moreno',
            'email'      => 'psicopedagogicopreescolar.genki@gmail.com',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Mena',
            'ap_materno' => 'Moreno',
        ],
    ];

    public function run(): void
    {
        $rows = collect(self::USUARIOS)
            ->reject(fn($u) => DB::table('usuario')->where('email', $u['email'])->exists())
            ->map(fn($u) => [
                'ciclo_seleccionado_id' => null,
                'nombre'                => $u['nombre'],
                'email'                 => $u['email'],
                'password_hash'         => Hash::make($u['_password'] ?? $this->generarPassword($u['ap_paterno'], $u['ap_materno'])),
                'rol'                   => $u['rol'],
                'activo'                => true,
                'ultimo_acceso'         => null,
                'creado_at'             => now(),
            ])
            ->values()
            ->toArray();

        if (empty($rows)) {
            $this->command->warn('  UsuarioSeeder: todos los usuarios ya existen, omitiendo.');
            return;
        }

        DB::table('usuario')->insert($rows);

        $this->command->info("  UsuarioSeeder: " . count($rows) . " usuario(s) insertado(s).");
    }

    private function generarPassword(string $apPaterno, string $apMaterno): string
    {
        $pat = ucfirst(strtolower(mb_substr($apPaterno, 0, 3)));
        $mat = ucfirst(strtolower(mb_substr($apMaterno, 0, 3)));

        return "{$pat}{$mat}2025!";
    }
}
