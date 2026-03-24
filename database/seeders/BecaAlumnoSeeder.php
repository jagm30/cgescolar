<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BecaAlumnoSeeder extends Seeder
{
    public function run(): void
    {
        // catalogo_beca_id: 1=Excelencia, 2=Hermanos, 3=Trabajador, 4=Especial
        // concepto_id: 3=Colegiatura Primaria, 4=Colegiatura Secundaria
        // creado_por = 1 (admin)

        DB::table('beca_alumno')->insert([
            // Juan López (alumno 1, Primaria) — Beca Excelencia 50%
            [
                'catalogo_beca_id' => 1,
                'alumno_id'        => 1,
                'ciclo_id'         => 2,
                'concepto_id'      => 3,
                'vigencia_inicio'  => '2025-09-01',
                'vigencia_fin'     => null,
                'motivo'           => 'Promedio 9.8 ciclo anterior',
                'activo'           => true,
                'creado_por'       => 1,
                'creado_at'        => now(),
            ],
            // Ana López (alumno 2, Preescolar) — Beca Hermanos 15%
            [
                'catalogo_beca_id' => 2,
                'alumno_id'        => 2,
                'ciclo_id'         => 2,
                'concepto_id'      => 2,
                'vigencia_inicio'  => '2025-09-01',
                'vigencia_fin'     => null,
                'motivo'           => 'Segundo hermano inscrito (Juan López)',
                'activo'           => true,
                'creado_por'       => 1,
                'creado_at'        => now(),
            ],
            // Diego Hernández (alumno 5, Secundaria) — Beca Especial $500 fijos
            [
                'catalogo_beca_id' => 4,
                'alumno_id'        => 5,
                'ciclo_id'         => 2,
                'concepto_id'      => 4,
                'vigencia_inicio'  => '2025-09-01',
                'vigencia_fin'     => null,
                'motivo'           => 'Descuento autorizado por dirección',
                'activo'           => true,
                'creado_por'       => 1,
                'creado_at'        => now(),
            ],
        ]);
    }
}
