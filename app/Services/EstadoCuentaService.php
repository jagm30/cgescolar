<?php

namespace App\Services;

use App\Models\Cargo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Lógica compartida para el cálculo de descuentos y recargos proyectados
 * en el estado de cuenta de un alumno.
 *
 * Replica la misma lógica que AlumnoController::calcularBecaCargo y
 * ::calcularPoliticaCargo, pero expuesta como servicio reutilizable para
 * PortalPadreController sin duplicar código.
 */
class EstadoCuentaService
{
    /**
     * Proyecta el descuento de beca aplicable a un cargo pendiente.
     *
     * Busca en orden: beca por plan → beca por concepto → beca global del ciclo.
     * Descuenta lo que ya fue acreditado en abonos anteriores para no duplicar
     * el beneficio en pagos parciales.
     *
     * @return array{0: float, 1: float|null} [monto_descuento, porcentaje_o_null]
     */
    public function calcularBecaCargo(
        Cargo $cargo,
        float $saldoBase,
        float $becaYaAplicada,
        Collection $becasPorPlan,
        Collection $becasPorConcepto,
        Collection $becasGlobales,
    ): array {
        // 1. Beca asociada al plan de pago del cargo
        $becaItem = $cargo->asignacion?->plan_id
            ? $becasPorPlan->get($cargo->asignacion->plan_id)
            : null;

        // 2. Beca asociada al concepto específico
        $becaItem ??= $becasPorConcepto->get($cargo->concepto_id);

        // 3. Beca global del alumno (sin plan ni concepto): aplica según ciclo
        if (! $becaItem && $becasGlobales->isNotEmpty()) {
            $cicloDelCargo = $cargo->inscripcion?->ciclo_id;
            $becaItem = $becasGlobales->first(
                fn ($b) => $cicloDelCargo && $b->ciclo_id === $cicloDelCargo
            );
        }

        if (! $becaItem) {
            return [0.0, null];
        }

        $descuentoTotal = $becaItem->calcularDescuento((float) $cargo->monto_original);
        $descuento = min($saldoBase, max(0.0, round($descuentoTotal - $becaYaAplicada, 2)));
        $porcentaje = $becaItem->catalogoBeca->tipo === 'porcentaje'
            ? (float) $becaItem->catalogoBeca->valor
            : null;

        return [$descuento, $porcentaje];
    }

    /**
     * Proyecta el descuento por pronto pago o el recargo por mora
     * según la política del plan de pago asignado al cargo.
     *
     * Si el cargo está vencido → calcula recargo.
     * Si está vigente y dentro del período de pronto pago → calcula descuento.
     *
     * @return array{0: float, 1: float} [descuento_pronto_pago, recargo_aplicado]
     */
    public function calcularPoliticaCargo(
        Cargo $cargo,
        float $saldoBase,
        bool $vencido,
        Carbon $hoyFecha,
    ): array {
        $plan = $cargo->asignacion->plan;

        if ($vencido) {
            $mesesRetraso = (int) $cargo->fecha_vencimiento->diffInMonths($hoyFecha) + 1;
            $pr = $plan->politicasRecargo->firstWhere('activo', true);
            $mesVencimiento = $cargo->fecha_vencimiento->month;
            $mesExento = $pr && ! $pr->aplicaEnMes($mesVencimiento);
            $recargo = ($pr && ! $mesExento) ? $pr->calcular($saldoBase, $mesesRetraso) : 0.0;

            return [0.0, $recargo];
        }

        $pd = $plan->politicasDescuentoActivas->first(fn ($p) => $p->aplicaHoy());

        return [$pd ? $pd->calcular($saldoBase) : 0.0, 0.0];
    }
}
