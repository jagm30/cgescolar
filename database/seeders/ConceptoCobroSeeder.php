<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptoCobroSeeder extends Seeder
{
    /**
     * Conceptos de cobro reales extraídos del archivo conceptos.xlsx.
     *
     * tipo válidos : colegiatura | inscripcion | cargo_unico | cargo_recurrente
     * aplica_beca  : true solo en colegiaturas, inscripciones con beca y
     *                activaciones de beca Genki según el archivo fuente.
     */
    private const CONCEPTOS = [
        // ── Maternal ─────────────────────────────────────────────────────────
        [
            'nombre'         => 'Inscripción Anual Maternal',
            'tipo'           => 'inscripcion',
            'monto'          => 3000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Activación Beca Genki Maternal',
            'tipo'           => 'cargo_unico',
            'monto'          => 7000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Colegiatura Maternal',
            'tipo'           => 'colegiatura',
            'monto'          => 6400.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Paquete de Libros Maternal',
            'tipo'           => 'cargo_unico',
            'monto'          => 1500.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],

        // ── Preescolar ───────────────────────────────────────────────────────
        [
            'nombre'         => 'Inscripción Anual Preescolar',
            'tipo'           => 'inscripcion',
            'monto'          => 3000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Activación Beca Genki Preescolar',
            'tipo'           => 'cargo_unico',
            'monto'          => 7000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Colegiatura Preescolar',
            'tipo'           => 'colegiatura',
            'monto'          => 6400.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121501',
            'descripcion'    => null,
        ],

        // ── Primaria ─────────────────────────────────────────────────────────
        [
            'nombre'         => 'Inscripción Anual Primaria',
            'tipo'           => 'inscripcion',
            'monto'          => 3000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121503',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Activación Beca Genki Primaria',
            'tipo'           => 'cargo_unico',
            'monto'          => 7000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121503',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Colegiatura Primaria',
            'tipo'           => 'colegiatura',
            'monto'          => 7600.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121503',
            'descripcion'    => null,
        ],

        // ── Secundaria ───────────────────────────────────────────────────────
        [
            'nombre'         => 'Inscripción Anual Secundaria',
            'tipo'           => 'inscripcion',
            'monto'          => 3000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121503',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Activación Beca Genki Secundaria',
            'tipo'           => 'cargo_unico',
            'monto'          => 7000.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121503',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Colegiatura Secundaria',
            'tipo'           => 'colegiatura',
            'monto'          => 7600.00,
            'aplica_beca'    => true,
            'aplica_recargo' => true,
            'clave_sat'      => '86121503',
            'descripcion'    => null,
        ],

        // ── Servicios generales ──────────────────────────────────────────────
        [
            'nombre'         => 'Seguro por Accidentes',
            'tipo'           => 'cargo_unico',
            'monto'          => 1500.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => '84131603',
            'descripcion'    => 'Seguro de accidentes escolares',
        ],
        [
            'nombre'         => 'Plataforma',
            'tipo'           => 'cargo_unico',
            'monto'          => 3700.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => '43232500',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Clubs',
            'tipo'           => 'cargo_unico',
            'monto'          => 1000.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => '86131500',
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Club',
            'tipo'           => 'cargo_unico',
            'monto'          => 500.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => '86131500',
            'descripcion'    => null,
        ],

        // ── Trámites y documentos ────────────────────────────────────────────
        [
            'nombre'         => 'Constancias de Estudio y/o Presupuestales',
            'tipo'           => 'cargo_unico',
            'monto'          => 150.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Cartas de Buena Conducta',
            'tipo'           => 'cargo_unico',
            'monto'          => 150.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Credenciales Extras',
            'tipo'           => 'cargo_unico',
            'monto'          => 150.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],

        // ── Cafetería / otros ────────────────────────────────────────────────
        [
            'nombre'         => 'Desayunos',
            'tipo'           => 'cargo_recurrente',
            'monto'          => 100.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Comidas',
            'tipo'           => 'cargo_recurrente',
            'monto'          => 100.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Retardos de Salida',
            'tipo'           => 'cargo_recurrente',
            'monto'          => 200.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Material de Japonés',
            'tipo'           => 'cargo_recurrente',
            'monto'          => 500.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
        [
            'nombre'         => 'Copias del Ciclo',
            'tipo'           => 'cargo_unico',
            'monto'          => 150.00,
            'aplica_beca'    => false,
            'aplica_recargo' => false,
            'clave_sat'      => null,
            'descripcion'    => null,
        ],
    ];

    public function run(): void
    {
        $rows = collect(self::CONCEPTOS)
            ->reject(fn($c) => DB::table('concepto_cobro')->where('nombre', $c['nombre'])->exists())
            ->map(fn($c) => [...$c, 'activo' => true])
            ->values()
            ->toArray();

        if (empty($rows)) {
            $this->command->warn('  ConceptoCobroSeeder: todos los conceptos ya existen, omitiendo.');
            return;
        }

        DB::table('concepto_cobro')->insert($rows);

        $total = count($rows);
        $this->command->info("  ConceptoCobroSeeder: {$total} concepto(s) insertado(s).");
    }
}
