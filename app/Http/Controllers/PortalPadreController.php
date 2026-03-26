<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\Pago;
use Illuminate\Http\Request;

class PortalPadreController extends Controller
{
    /**
     * GET /portal/hijos
     * Lista todos los hijos del padre autenticado.
     */
    public function hijos()
    {
        $contacto = auth()->user()
            ->contactoFamiliar()
            ->with('familia.alumnos.inscripciones.grupo.grado.nivel')
            ->first();

        $alumnos = $contacto?->familia?->alumnos ?? collect();

        if (request()->ajax()) {
            return response()->json($alumnos);
        }

        return view('portal.hijos', compact('alumnos'));
    }

    /**
     * GET /portal/hijos/{alumnoId}/estado-cuenta
     * Estado de cuenta del alumno en el ciclo activo.
     */
    public function estadoCuenta(int $alumnoId)
    {
        $this->verificarAccesoAlumno($alumnoId);

        $cicloId     = CicloEscolar::activo()->value('id');
        $inscripcion = \App\Models\Inscripcion::where('alumno_id', $alumnoId)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->first();

        if (!$inscripcion) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Sin inscripción activa.'], 404);
            }
            return back()->with('error', 'No tiene inscripción activa en el ciclo vigente.');
        }

        $cargos = Cargo::with(['concepto', 'detallesPagosVigentes'])
            ->where('inscripcion_id', $inscripcion->id)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn($cargo) => [
                'id'                => $cargo->id,
                'concepto'          => $cargo->concepto->nombre,
                'periodo'           => $cargo->periodo,
                'monto_original'    => $cargo->monto_original,
                'saldo_abonado'     => $cargo->saldo_abonado,
                'saldo_pendiente'   => $cargo->saldo_pendiente_base,
                'estado'            => $cargo->estado_real,
                'fecha_vencimiento' => $cargo->fecha_vencimiento,
                'puede_facturar'    => $cargo->estado === 'pagado',
            ]);

        $resumen = [
            'total_pendiente' => $cargos->sum('saldo_pendiente'),
            'total_pagado'    => $cargos->sum('saldo_abonado'),
            'cargos_vencidos' => $cargos->filter(fn($c) => str_contains($c['estado'], 'vencido'))->count(),
        ];

        $alumno = \App\Models\Alumno::find($alumnoId);

        if (request()->ajax()) {
            return response()->json(['resumen' => $resumen, 'cargos' => $cargos]);
        }

        return view('portal.estado-cuenta', compact('alumno', 'cargos', 'resumen'));
    }

    /**
     * GET /portal/hijos/{alumnoId}/pagos
     * Historial de pagos del alumno.
     */
    public function historialPagos(int $alumnoId)
    {
        $this->verificarAccesoAlumno($alumnoId);

        $pagos = Pago::with(['detalles.cargo.concepto', 'cfdis'])
            ->whereHas('detalles.cargo.inscripcion', fn($q) => $q->where('alumno_id', $alumnoId))
            ->where('estado', 'vigente')
            ->orderByDesc('fecha_pago')
            ->get()
            ->map(fn($pago) => [
                'id'            => $pago->id,
                'folio_recibo'  => $pago->folio_recibo,
                'conceptos'     => $pago->detalles->map(fn($d) => $d->cargo->concepto->nombre)->join(', '),
                'monto_total'   => $pago->monto_total,
                'fecha_pago'    => $pago->fecha_pago,
                'forma_pago'    => $pago->forma_pago,
                'tiene_factura' => $pago->cfdis->where('estado', 'vigente')->isNotEmpty(),
                'cfdi_uuid'     => $pago->cfdis->where('estado', 'vigente')->first()?->uuid_sat,
            ]);

        $alumno = \App\Models\Alumno::find($alumnoId);

        if (request()->ajax()) {
            return response()->json($pagos);
        }

        return view('portal.historial-pagos', compact('alumno', 'pagos'));
    }

    /**
     * GET /portal/razones-sociales
     * RFCs registrados del contacto autenticado.
     */
    public function razonesSociales()
    {
        $contacto = auth()->user()->contactoFamiliar()
            ->with('razonesSociales')
            ->first();

        $razonesSociales = $contacto?->razonesSociales()->activa()->get() ?? collect();

        if (request()->ajax()) {
            return response()->json($razonesSociales);
        }

        return view('portal.razones-sociales', compact('razonesSociales'));
    }

    // ── Helper ───────────────────────────────────────────

    private function verificarAccesoAlumno(int $alumnoId): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (!$contacto?->familia_id) {
            abort(403, 'No tiene acceso a este alumno.');
        }

        $perteneceAFamilia = \App\Models\Alumno::where('id', $alumnoId)
            ->where('familia_id', $contacto->familia_id)
            ->exists();

        if (!$perteneceAFamilia) {
            abort(403, 'No tiene acceso a la información de este alumno.');
        }
    }
}
