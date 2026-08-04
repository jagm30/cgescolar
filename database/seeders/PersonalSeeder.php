<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalSeeder extends Seeder
{
    /**
     * Registros de personal vinculados a los usuarios del sistema.
     * usuario_id se resuelve por email en tiempo de ejecución.
     * Campos genéricos (telefono, domicilio) deben actualizarse por empleado.
     */
    private const PERSONAL = [
        // ── Administradores ──────────────────────────────────────────────────
        [
            'numero_empleado' => 'EMP-001',
            'nombre'          => 'ADMINISTRADOR',
            'ap_paterno'      => 'GENERAL',
            'ap_materno'      => null,
            'email'           => 'admin@escuela.edu.mx',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-002',
            'nombre'          => 'CARMEN GPE.',
            'ap_paterno'      => 'VILLEGAS',
            'ap_materno'      => 'PEREZ',
            'email'           => 'cvillegas@vyce.com.mx',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-003',
            'nombre'          => 'VERONICA',
            'ap_paterno'      => 'PEREZ',
            'ap_materno'      => 'ZUÑIGA',
            'email'           => 'tesoreriagenkituxtla@gmail.com',
            'tipo'            => 'administrativo',
        ],
        // ── Caja ─────────────────────────────────────────────────────────────
        [
            'numero_empleado' => 'EMP-004',
            'nombre'          => 'GUADALUPE',
            'ap_paterno'      => 'HERNANDEZ',
            'ap_materno'      => 'PEREZ',
            'email'           => 'auxiliargenkituxtla@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-005',
            'nombre'          => 'PAOLA LIZETH',
            'ap_paterno'      => 'DE LOS SANTOS',
            'ap_materno'      => 'SOLIS',
            'email'           => 'auxiliarcontable2genki@gmail.com',
            'tipo'            => 'administrativo',
        ],
        // ── Recepción ────────────────────────────────────────────────────────
        [
            'numero_empleado' => 'EMP-006',
            'nombre'          => 'JULYSA MIROSLAVA',
            'ap_paterno'      => 'CARRASCO',
            'ap_materno'      => 'GRAJALES',
            'email'           => 'direccionprimaria.genkischool@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-007',
            'nombre'          => 'YANIFIATH',
            'ap_paterno'      => 'GUIZAR',
            'ap_materno'      => 'MANZUR',
            'email'           => 'dir.primariagenkischooltuxtla@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-008',
            'nombre'          => 'NASHIELY ITZAYANA',
            'ap_paterno'      => 'ESPINOSA',
            'ap_materno'      => 'PEREZ',
            'email'           => 'direcciongenkischool@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-009',
            'nombre'          => 'EDY NELSON',
            'ap_paterno'      => 'SOMOZA',
            'ap_materno'      => 'MARROQUIN',
            'email'           => 'direccionacademica.genkischool@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-010',
            'nombre'          => 'KAREEM GUADALUPE',
            'ap_paterno'      => 'HERNANDEZ',
            'ap_materno'      => 'PEREZ',
            'email'           => 'coord.tec.k.genkischool1@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-011',
            'nombre'          => 'LANDI MAGALLY',
            'ap_paterno'      => 'MORALES',
            'ap_materno'      => 'CARPIO',
            'email'           => 'coordinacionprimariagenki@gmail.com',
            'tipo'            => 'administrativo',
        ],
        // ── Admisiones ───────────────────────────────────────────────────────
        [
            'numero_empleado' => 'EMP-012',
            'nombre'          => 'IVONNE LIZETH',
            'ap_paterno'      => 'MARTINEZ',
            'ap_materno'      => 'VELAZQUEZ',
            'email'           => 'admisionesgenkischool@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-013',
            'nombre'          => 'LIDIA',
            'ap_paterno'      => 'FLORES',
            'ap_materno'      => 'PICHARDO',
            'email'           => 'coordinacion@genkischool.com.mx',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-014',
            'nombre'          => 'KAREN GUADALUPE',
            'ap_paterno'      => 'VAZQUEZ',
            'ap_materno'      => 'VAZQUEZ',
            'email'           => 'psicopedagogico.genki@gmail.com',
            'tipo'            => 'administrativo',
        ],
        [
            'numero_empleado' => 'EMP-015',
            'nombre'          => 'VALERIA MONSERRATH',
            'ap_paterno'      => 'MENA',
            'ap_materno'      => 'MORENO',
            'email'           => 'psicopedagogicopreescolar.genki@gmail.com',
            'tipo'            => 'administrativo',
        ],
    ];

    public function run(): void
    {
        $rows = collect(self::PERSONAL)
            ->reject(fn($p) => DB::table('personal')->where('email', $p['email'])->exists())
            ->map(fn($p) => [
                'tiene_acceso_sistema' => true,
                'usuario_id'           => DB::table('usuario')->where('email', $p['email'])->value('id'),
                'numero_empleado'      => $p['numero_empleado'],
                'nombre'               => $p['nombre'],
                'ap_paterno'           => $p['ap_paterno'],
                'ap_materno'           => $p['ap_materno'],
                'telefono'             => '0000-000-000',
                'email'                => $p['email'],
                'rfc'                  => null,
                'tipo'                 => $p['tipo'],
                'domicilio'            => 'Pendiente de actualizar',
                'foto_url'             => null,
                'activo'               => true,
                'creado_at'            => now(),
            ])
            ->values()
            ->toArray();

        if (empty($rows)) {
            $this->command->warn('  PersonalSeeder: todos los registros ya existen, omitiendo.');
            return;
        }

        DB::table('personal')->insert($rows);

        $this->command->info("  PersonalSeeder: " . count($rows) . " registro(s) de personal insertado(s).");
    }
}
