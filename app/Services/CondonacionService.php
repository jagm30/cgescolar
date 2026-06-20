<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Cargo;
use App\Models\Condonacion;
use App\Models\CondonacionDetalle;
use App\Models\DescuentoCargo;
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
            $montoTotal = collect($data['detalles'])->sum('monto');

            $condonacion = Condonacion::create([
                'alumno_id' => $data['alumno_id'],
                'ciclo_id' => $data['ciclo_id'],
                'monto_total' => $montoTotal,
                'motivo' => $data['motivo'],
                'estado' => 'activa',
                'creado_por' => auth()->id(),
            ]);

            foreach ($data['detalles'] as $item) {
                $cargo = Cargo::findOrFail($item['cargo_id']);
                $monto = round((float) $item['monto'], 2);

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

                $this->actualizarEstadoCargo($cargo);
            }

            Auditoria::registrar('condonacion', $condonacion->id, 'insert', null, [
                'alumno_id' => $data['alumno_id'],
                'monto_total' => $montoTotal,
                'num_cargos' => count($data['detalles']),
            ]);

            return $condonacion;
        });
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
