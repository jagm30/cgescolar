<?php

namespace App\View\Composers;

use App\Models\Alumno;
use App\Models\Cargo;
use Illuminate\View\View;

class PortalNotificacionesComposer
{
    public function compose(View $view): void
    {
        $notificaciones = [
            'cargos_vencidos' => 0,
            'total_vencido' => 0.0,
            'pagos' => [],
        ];

        $usuario = auth()->user();

        if (! $usuario || ! $usuario->esPadre()) {
            $view->with('notificacionesPortal', $notificaciones);

            return;
        }

        $contacto = $usuario->contactoFamiliar()->first();

        if (! $contacto) {
            $view->with('notificacionesPortal', $notificaciones);

            return;
        }

        $alumnoIds = Alumno::query()
            ->where('estado', 'activo')
            ->whereHas('contactos', fn ($query) => $query
                ->where('contacto_familiar.id', $contacto->id)
                ->where('alumno_contacto.tiene_acceso_portal', true)
                ->where('alumno_contacto.activo', true)
            )
            ->whereHas('inscripciones', fn ($query) => $query->where('activo', true))
            ->pluck('id');

        if ($alumnoIds->isEmpty()) {
            $view->with('notificacionesPortal', $notificaciones);

            return;
        }

        $cargos = Cargo::query()
            ->with([
                'concepto',
                'detallesPagosVigentes',
                'condonacionDetalles',
                'inscripcion.alumno',
            ])
            ->whereHas('inscripcion', fn ($query) => $query->whereIn('alumno_id', $alumnoIds))
            ->orderBy('fecha_vencimiento')
            ->get();

        $fechaLimiteAtraso = today()->subMonth();

        foreach ($cargos as $cargo) {
            $descuentos = (float) $cargo->detallesPagosVigentes->sum('descuento_beca')
                + (float) $cargo->detallesPagosVigentes->sum('descuento_pronto_pago')
                + (float) $cargo->detallesPagosVigentes->sum('descuento_otros');
            $recargo = (float) $cargo->detallesPagosVigentes->sum('recargo_aplicado');
            $condonacion = (float) $cargo->condonacionDetalles->sum('monto_aplicado');
            $cobrado = (float) $cargo->detallesPagosVigentes->sum('monto_final');
            $pendiente = max(0.0, $cargo->monto_original - $descuentos - $condonacion + $recargo - $cobrado);

            if (
                $pendiente > 0
                && str_contains($cargo->estado_real, 'vencido')
                && $cargo->fecha_vencimiento->lessThanOrEqualTo($fechaLimiteAtraso)
            ) {
                $notificaciones['cargos_vencidos']++;
                $notificaciones['total_vencido'] += $pendiente;
                $notificaciones['pagos'][] = [
                    'alumno' => $cargo->inscripcion->alumno->nombre_completo,
                    'concepto' => $cargo->etiqueta,
                    'monto' => $pendiente,
                    'url' => route('portal.estado-cuenta', $cargo->inscripcion->alumno_id),
                ];
            }
        }

        $view->with('notificacionesPortal', $notificaciones);
    }
}
