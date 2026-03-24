<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        // Genera cargos mensuales sep 2025 - jun 2026 (10 meses)
        // para los 6 alumnos de prueba.
        //
        // inscripcion_id: 1=Juan(Primaria), 2=Ana(Preescolar),
        //                 3=Luis(Secundaria), 4=Sofía(Primaria),
        //                 5=Diego(Secundaria), 6=Valeria(Maternal)
        // concepto_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria
        // asignacion_id: 1=Maternal nivel, 2=Preescolar nivel,
        //                3=Primaria nivel, 4=Secundaria nivel
        // generado_por = 1 (admin)

        $meses = [
            ['periodo' => '2025-09', 'vencimiento' => '2025-09-10'],
            ['periodo' => '2025-10', 'vencimiento' => '2025-10-10'],
            ['periodo' => '2025-11', 'vencimiento' => '2025-11-10'],
            ['periodo' => '2025-12', 'vencimiento' => '2025-12-10'],
            ['periodo' => '2026-01', 'vencimiento' => '2026-01-10'],
            ['periodo' => '2026-02', 'vencimiento' => '2026-02-10'],
            ['periodo' => '2026-03', 'vencimiento' => '2026-03-10'],
            ['periodo' => '2026-04', 'vencimiento' => '2026-04-10'],
            ['periodo' => '2026-05', 'vencimiento' => '2026-05-10'],
            ['periodo' => '2026-06', 'vencimiento' => '2026-06-10'],
        ];

        // inscripcion → concepto, asignacion, monto
        $alumnos = [
            ['inscripcion_id' => 1, 'concepto_id' => 3, 'asignacion_id' => 3, 'monto' => 3000.00], // Juan - Primaria
            ['inscripcion_id' => 2, 'concepto_id' => 2, 'asignacion_id' => 2, 'monto' => 2500.00], // Ana - Preescolar
            ['inscripcion_id' => 3, 'concepto_id' => 4, 'asignacion_id' => 4, 'monto' => 3500.00], // Luis - Secundaria
            ['inscripcion_id' => 4, 'concepto_id' => 3, 'asignacion_id' => 3, 'monto' => 3000.00], // Sofía - Primaria
            ['inscripcion_id' => 5, 'concepto_id' => 4, 'asignacion_id' => 4, 'monto' => 3500.00], // Diego - Secundaria
            ['inscripcion_id' => 6, 'concepto_id' => 1, 'asignacion_id' => 1, 'monto' => 2200.00], // Valeria - Maternal
        ];

        $cargos = [];
        foreach ($alumnos as $alumno) {
            foreach ($meses as $mes) {
                $cargos[] = [
                    'inscripcion_id'    => $alumno['inscripcion_id'],
                    'concepto_id'       => $alumno['concepto_id'],
                    'asignacion_id'     => $alumno['asignacion_id'],
                    'generado_por'      => 1,
                    'monto_original'    => $alumno['monto'],
                    'fecha_vencimiento' => $mes['vencimiento'],
                    'estado'            => 'pendiente',
                    'periodo'           => $mes['periodo'],
                    'generado_at'       => now(),
                ];
            }
        }

        // Marcar algunos meses anteriores como pagados
        $pagados = ['2025-09', '2025-10', '2025-11'];
        foreach ($cargos as &$cargo) {
            if (in_array($cargo['periodo'], $pagados)) {
                $cargo['estado'] = 'pagado';
            }
        }

        DB::table('cargo')->insert($cargos);
    }
}
