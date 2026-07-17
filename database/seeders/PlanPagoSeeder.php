<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanPagoSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('plan_pago')->exists()) {
            $this->command->warn('  PlanPagoSeeder: ya existen planes, omitiendo.');
            return;
        }

        // ── Planes mensuales por nivel (ciclo 2025-2026, id=2) ───────────────
        DB::table('plan_pago')->insert([
            ['ciclo_id' => 2, 'nivel_id' => 1, 'nombre' => 'Plan Mensual Maternal 2025-2026',   'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
            ['ciclo_id' => 2, 'nivel_id' => 2, 'nombre' => 'Plan Mensual Preescolar 2025-2026', 'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
            ['ciclo_id' => 2, 'nivel_id' => 3, 'nombre' => 'Plan Mensual Primaria 2025-2026',   'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
            ['ciclo_id' => 2, 'nivel_id' => 4, 'nombre' => 'Plan Mensual Secundaria 2025-2026', 'periodicidad' => 'mensual', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'activo' => true],
        ]);

        // ── Obtener IDs de planes y colegiaturas por nombre ──────────────────
        $plan = fn(string $nombre): int =>
            DB::table('plan_pago')->where('nombre', $nombre)->value('id');

        $concepto = fn(string $nombre): int =>
            DB::table('concepto_cobro')->where('nombre', $nombre)->value('id');

        // ── Conceptos por plan (colegiatura mensual) ─────────────────────────
        DB::table('plan_pago_concepto')->insert([
            ['plan_id' => $plan('Plan Mensual Maternal 2025-2026'),   'concepto_id' => $concepto('Colegiatura Maternal'),   'monto' => 2200.00],
            ['plan_id' => $plan('Plan Mensual Preescolar 2025-2026'), 'concepto_id' => $concepto('Colegiatura Preescolar'), 'monto' => 2500.00],
            ['plan_id' => $plan('Plan Mensual Primaria 2025-2026'),   'concepto_id' => $concepto('Colegiatura Primaria'),   'monto' => 3000.00],
            ['plan_id' => $plan('Plan Mensual Secundaria 2025-2026'), 'concepto_id' => $concepto('Colegiatura Secundaria'), 'monto' => 3500.00],
        ]);

        // ── Política de descuento: pronto pago antes del día 5 = 5% ─────────
        DB::table('politica_descuento')->insert([
            ['plan_id' => $plan('Plan Mensual Maternal 2025-2026'),   'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
            ['plan_id' => $plan('Plan Mensual Preescolar 2025-2026'), 'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
            ['plan_id' => $plan('Plan Mensual Primaria 2025-2026'),   'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
            ['plan_id' => $plan('Plan Mensual Secundaria 2025-2026'), 'nombre' => 'Pronto pago', 'tipo_valor' => 'porcentaje', 'valor' => 5.00, 'dia_limite' => 5, 'activo' => true],
        ]);

        // ── Política de recargo: después del día 10 = 5% ────────────────────
        DB::table('politica_recargo')->insert([
            ['plan_id' => $plan('Plan Mensual Maternal 2025-2026'),   'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 440.00, 'activo' => true, 'acumular_mensual' => false],
            ['plan_id' => $plan('Plan Mensual Preescolar 2025-2026'), 'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 500.00, 'activo' => true, 'acumular_mensual' => false],
            ['plan_id' => $plan('Plan Mensual Primaria 2025-2026'),   'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 600.00, 'activo' => true, 'acumular_mensual' => false],
            ['plan_id' => $plan('Plan Mensual Secundaria 2025-2026'), 'dia_limite_pago' => 10, 'tipo_recargo' => 'porcentaje', 'valor' => 5.00, 'tope_maximo' => 700.00, 'activo' => true, 'acumular_mensual' => false],
        ]);

        $this->command->info('  PlanPagoSeeder: 4 planes, conceptos, descuentos y recargos insertados.');
    }
}
