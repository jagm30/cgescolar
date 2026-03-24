<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanPagoSeeder extends Seeder
{
    public function run(): void
    {
        // Planes de pago mensual por nivel (ciclo 2025-2026)
        // nivel_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria
        // concepto_id colegiaturas: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria

        $planes = [
            ['ciclo_id' => 2, 'nivel_id' => 1, 'nombre' => 'Plan Mensual Maternal 2025-2026',   'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
            ['ciclo_id' => 2, 'nivel_id' => 2, 'nombre' => 'Plan Mensual Preescolar 2025-2026', 'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
            ['ciclo_id' => 2, 'nivel_id' => 3, 'nombre' => 'Plan Mensual Primaria 2025-2026',   'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
            ['ciclo_id' => 2, 'nivel_id' => 4, 'nombre' => 'Plan Mensual Secundaria 2025-2026', 'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
        ];

        DB::table('plan_pago')->insert($planes);
        // plan_id: 1=Maternal, 2=Preescolar, 3=Primaria, 4=Secundaria

        // Conceptos por plan (colegiatura)
        DB::table('plan_pago_concepto')->insert([
            ['plan_id' => 1, 'concepto_id' => 1, 'monto' => 2200.00], // Maternal
            ['plan_id' => 2, 'concepto_id' => 2, 'monto' => 2500.00], // Preescolar
            ['plan_id' => 3, 'concepto_id' => 3, 'monto' => 3000.00], // Primaria
            ['plan_id' => 4, 'concepto_id' => 4, 'monto' => 3500.00], // Secundaria
        ]);

        // Políticas de descuento (pronto pago antes del día 5 = 5%)
        DB::table('politica_descuento')->insert([
            ['plan_id' => 1, 'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
            ['plan_id' => 2, 'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
            ['plan_id' => 3, 'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
            ['plan_id' => 4, 'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
        ]);

        // Políticas de recargo (después del día 10 = 5%, tope 20%)
        DB::table('politica_recargo')->insert([
            ['plan_id' => 1, 'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 440.00,  'activo' => true],
            ['plan_id' => 2, 'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 500.00,  'activo' => true],
            ['plan_id' => 3, 'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 600.00,  'activo' => true],
            ['plan_id' => 4, 'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 700.00,  'activo' => true],
        ]);
    }
}
