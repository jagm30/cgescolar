<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Cargo;
use App\Models\Condonacion;
use App\Models\CondonacionDetalle;
use App\Models\DescuentoCargo;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;

class CondonacionService
{
    /**
     * Crea una condonación y aplica los descuentos a los cargos indicados.
     * Por cada cargo se genera un DescuentoCargo (tipo monto_fijo) para que
     * el preview de cobro existente lo refleje automáticamente sin cambios.
     */
    public function crear(array $data): Condonacion
    {
        return DB::transaction(function () use ($data) {
            // El monto_total se recalcula sobre los ítems efectivamente aplicados;
            // los cargos ya pagados se saltan y no deben inflar el total.
            $condonacion = Condonacion::create([
                'alumno_id' => $data['alumno_id'],
                'ciclo_id' => $data['ciclo_id'],
                'monto_total' => 0,
                'motivo' => $data['motivo'],
                'estado' => 'activa',
                'creado_por' => auth()->id(),
            ]);

            $montoAplicado = 0;

            foreach ($data['detalles'] as $item) {
                $cargo = Cargo::findOrFail($item['cargo_id']);
                $monto = round((float) $item['monto'], 2);

                // Un cargo ya pagado no debe modificarse: el pago se registró
                // considerando la condonación vigente y alterarla rompería el historial.
                if ($cargo->estado === 'pagado') {
                    continue;
                }

                // Si el cargo ya tiene condonaciones activas, reemplazarlas:
                // eliminar el descuento y el detalle previos, y cancelar la
                // condonación padre si queda sin detalles.
                $this->reemplazarCondonacionesActivas($cargo);

                $descuento = DescuentoCargo::create([
                    'cargo_id' => $cargo->id,
                    'tipo' => 'monto_fijo',
                    'valor' => $monto,
                    'monto_aplicado' => $monto,
                    'motivo' => "Condonación #{$condonacion->id}: {$data['motivo']}",
                    'autorizado_por' => auth()->id(),
                    'creado_por' => auth()->id(),
                ]);

                CondonacionDetalle::create([
                    'condonacion_id' => $condonacion->id,
                    'cargo_id' => $cargo->id,
                    'descuento_cargo_id' => $descuento->id,
                    'monto_aplicado' => $monto,
                ]);

                $this->actualizarEstadoCargo($cargo->fresh());
                $montoAplicado += $monto;
            }

            $condonacion->update(['monto_total' => $montoAplicado]);

            Auditoria::registrar('condonacion', $condonacion->id, 'insert', null, [
                'alumno_id' => $data['alumno_id'],
                'monto_total' => $montoAplicado,
                'num_cargos' => count($data['detalles']),
            ]);

            return $condonacion;
        });
    }

    /**
     * Aplica la misma condonación a múltiples alumnos de un plan.
     * Para cada alumno, busca los cargos pendientes de los conceptos indicados
     * y aplica el monto configurado (respetando el saldo pendiente de cada cargo).
     *
     * @return array{creadas: int, omitidos: list<int>}
     *   - creadas:  número de condonaciones registradas
     *   - omitidos: IDs de alumnos sin cargos aplicables para los conceptos indicados
     */
    public function crearMasiva(array $data): array
    {
        $count    = 0;
        $omitidos = [];

        foreach ($data['alumno_ids'] as $alumnoId) {
            $inscripcion = Inscripcion::where('alumno_id', $alumnoId)
                ->where('ciclo_id', $data['ciclo_id'])
                ->orderByRaw('grupo_id IS NULL')
                ->first();

            if (! $inscripcion) {
                $omitidos[] = (int) $alumnoId;
                continue;
            }

            $detalles = $this->resolverDetallesParaAlumno($inscripcion->id, $data['conceptos']);

            if (empty($detalles)) {
                $omitidos[] = (int) $alumnoId;
                continue;
            }

            $this->crear([
                'alumno_id' => $alumnoId,
                'ciclo_id' => $data['ciclo_id'],
                'motivo' => $data['motivo'],
                'detalles' => $detalles,
            ]);

            $count++;
        }

        return ['creadas' => $count, 'omitidos' => $omitidos];
    }

    /**
     * Indica si el alumno ya tiene una condonación activa que cubra
     * cargos del plan indicado. Se usa para bloquear duplicados en
     * condonaciones individuales y masivas.
     */
    public function tieneCondonacionActivaDelPlan(int $alumnoId, int $planId): bool
    {
        return Condonacion::where('alumno_id', $alumnoId)
            ->activa()
            ->whereHas('detalles.cargo.asignacion', fn ($q) => $q->where('plan_id', $planId))
            ->exists();
    }

    /**
     * Cancela una condonación: elimina los descuentos asociados y
     * revierte el estado de los cargos que quedaron marcados como condonados.
     */
    public function cancelar(Condonacion $condonacion): void
    {
        DB::transaction(function () use ($condonacion) {
            foreach ($condonacion->detalles()->with('cargo')->get() as $detalle) {
                if ($detalle->descuento_cargo_id) {
                    DescuentoCargo::destroy($detalle->descuento_cargo_id);
                }

                $this->revertirEstadoCargo($detalle->cargo);
            }

            Auditoria::registrar('condonacion', $condonacion->id, 'update',
                ['estado' => 'activa'],
                ['estado' => 'cancelada']
            );

            $condonacion->update(['estado' => 'cancelada']);
        });
    }

    // ── Helpers privados ─────────────────────────────────

    /**
     * Construye los detalles de condonación para un alumno dado.
     * Aplica el monto configurado a cada cargo pendiente/parcial del concepto,
     * respetando el saldo pendiente neto de cada cargo.
     */
    private function resolverDetallesParaAlumno(int $inscripcionId, array $conceptos): array
    {
        $detalles = [];

        foreach ($conceptos as $concepto) {
            // Conceptos con monto 0 se omiten (el usuario los dejó en blanco intencionalmente)
            if (round((float) $concepto['monto'], 2) <= 0) {
                continue;
            }

            // Se incluyen cargos 'condonado' para permitir reemplazar la condonación activa.
            $cargos = Cargo::where('inscripcion_id', $inscripcionId)
                ->where('concepto_id', $concepto['concepto_id'])
                ->whereIn('estado', ['pendiente', 'parcial', 'condonado'])
                ->get();

            foreach ($cargos as $cargo) {
                // Descuentos que NO pertenecen a condonaciones activas (se conservarán).
                // Los de condonaciones activas se eliminarán al reemplazar, así que no
                // se restan aquí para obtener el saldo real disponible.
                $idsDescuentosCondonados = CondonacionDetalle::where('cargo_id', $cargo->id)
                    ->whereHas('condonacion', fn ($q) => $q->activa())
                    ->pluck('descuento_cargo_id')
                    ->filter()
                    ->all();

                $descuentosPermanentes = (float) DescuentoCargo::where('cargo_id', $cargo->id)
                    ->when(! empty($idsDescuentosCondonados), fn ($q) => $q->whereNotIn('id', $idsDescuentosCondonados))
                    ->sum('monto_aplicado');

                $saldoPendiente = round(
                    (float) $cargo->monto_original - $cargo->saldo_abonado - $descuentosPermanentes,
                    2
                );

                if ($saldoPendiente <= 0) {
                    continue;
                }

                $monto = min(round((float) $concepto['monto'], 2), $saldoPendiente);
                $detalles[] = ['cargo_id' => $cargo->id, 'monto' => $monto];
            }
        }

        return $detalles;
    }

    /**
     * Elimina las condonaciones activas previas de un cargo para permitir
     * que se aplique una nueva. Si la condonación padre queda sin detalles
     * se cancela automáticamente.
     */
    private function reemplazarCondonacionesActivas(Cargo $cargo): void
    {
        // Cargos pagados son intocables: la condonación ya fue considerada en el cobro.
        if ($cargo->estado === 'pagado') {
            return;
        }

        $detallesViejos = CondonacionDetalle::where('cargo_id', $cargo->id)
            ->whereHas('condonacion', fn ($q) => $q->activa())
            ->with('condonacion')
            ->get();

        foreach ($detallesViejos as $detalle) {
            if ($detalle->descuento_cargo_id) {
                DescuentoCargo::destroy($detalle->descuento_cargo_id);
            }

            $condonacionPadre = $detalle->condonacion;
            $detalle->delete();

            if ($condonacionPadre && $condonacionPadre->detalles()->count() === 0) {
                $condonacionPadre->update(['estado' => 'cancelada']);
            }
        }

        // Si el cargo estaba marcado como 'condonado' por la condonación que acabamos de
        // eliminar, resetear su estado para que actualizarEstadoCargo lo recalcule
        // correctamente con el nuevo monto. Sin este reset el cargo queda congelado en
        // 'condonado' aunque el nuevo descuento sea parcial.
        if ($cargo->estado === 'condonado') {
            $nuevoEstado = $cargo->saldo_abonado > 0 ? 'parcial' : 'pendiente';
            $cargo->update(['estado' => $nuevoEstado]);
        }
    }

    /**
     * Marca el cargo como 'condonado' si los descuentos acumulados
     * cubren el saldo pendiente restante.
     */
    private function actualizarEstadoCargo(Cargo $cargo): void
    {
        $totalDescuentos = DescuentoCargo::where('cargo_id', $cargo->id)
            ->sum('monto_aplicado');

        $saldoAbonado = $cargo->saldo_abonado;

        if (($saldoAbonado + (float) $totalDescuentos) >= (float) $cargo->monto_original) {
            $cargo->update(['estado' => 'condonado']);
        }
    }

    /**
     * Revierte el estado del cargo a pendiente o parcial según lo que tenga abonado,
     * siempre que los descuentos restantes no cubran el total original.
     */
    private function revertirEstadoCargo(Cargo $cargo): void
    {
        if ($cargo->estado !== 'condonado') {
            return;
        }

        $totalDescuentos = (float) DescuentoCargo::where('cargo_id', $cargo->id)
            ->sum('monto_aplicado');

        $saldoAbonado = $cargo->saldo_abonado;

        if (($saldoAbonado + $totalDescuentos) < (float) $cargo->monto_original) {
            $nuevoEstado = $saldoAbonado > 0 ? 'parcial' : 'pendiente';
            $cargo->update(['estado' => $nuevoEstado]);
        }
    }
}
