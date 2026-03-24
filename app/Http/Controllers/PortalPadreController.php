<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalPadreController extends Controller
{
    public function __construct()
    {
        // Solo padres de familia pueden acceder al portal
        if (auth()->check() && !auth()->user()->esPadre()) {
            abort(403, 'Esta sección es exclusiva para padres de familia.');
        }
    }

    /**
     * GET /portal/hijos
     * Lista todos los alumnos de la familia del padre autenticado.
     */
    public function hijos(): JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar()
            ->with('familia.alumnos.inscripciones.grupo.grado.nivel')
            ->first();

        if (!$contacto?->familia) {
            return response()->json(['message' => 'No tiene alumnos asociados a su cuenta.'], 404);
        }

        return response()->json($contacto->familia->alumnos);
    }

    /**
     * GET /portal/hijos/{alumnoId}/estado-cuenta
     * Estado de cuenta del alumno en el ciclo activo.
     */
    public function estadoCuenta(int $alumnoId): JsonResponse
    {
        $this->verificarAccesoAlumno($alumnoId);

        $cicloId = CicloEscolar::activo()->value('id');

        $inscripcion = \App\Models\Inscripcion::where('alumno_id', $alumnoId)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->first();

        if (!$inscripcion) {
            return response()->json(['message' => 'No tiene inscripción activa en el ciclo vigente.'], 404);
        }

        $cargos = Cargo::with(['concepto', 'pagosVigentes'])
            ->where('inscripcion_id', $inscripcion->id)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn($cargo) => [
                'id'               => $cargo->id,
                'concepto'         => $cargo->concepto->nombre,
                'periodo'          => $cargo->periodo,
                'monto_original'   => $cargo->monto_original,
                'saldo_abonado'    => $cargo->saldo_abonado,
                'saldo_pendiente'  => $cargo->saldo_pendiente_base,
                'estado'           => $cargo->estado_real,
                'fecha_vencimiento'=> $cargo->fecha_vencimiento,
                'puede_facturar'   => $cargo->estado === 'pagado',
            ]);

        $resumen = [
            'total_pendiente' => $cargos->sum('saldo_pendiente'),
            'total_pagado'    => $cargos->sum('saldo_abonado'),
            'cargos_vencidos' => $cargos->filter(fn($c) => str_contains($c['estado'], 'vencido'))->count(),
        ];

        return response()->json(['resumen' => $resumen, 'cargos' => $cargos]);
    }

    /**
     * GET /portal/hijos/{alumnoId}/pagos
     * Historial de pagos del alumno.
     */
    public function historialPagos(int $alumnoId): JsonResponse
    {
        $this->verificarAccesoAlumno($alumnoId);

        $pagos = Pago::with(['cargo.concepto', 'cfdis'])
            ->whereHas('cargo.inscripcion', fn($q) => $q->where('alumno_id', $alumnoId))
            ->where('estado', 'vigente')
            ->orderByDesc('fecha_pago')
            ->get()
            ->map(fn($pago) => [
                'id'             => $pago->id,
                'folio_recibo'   => $pago->folio_recibo,
                'folio_grupo'    => $pago->folio_grupo,
                'concepto'       => $pago->cargo->concepto->nombre,
                'periodo'        => $pago->cargo->periodo,
                'monto_abonado'  => $pago->monto_abonado,
                'fecha_pago'     => $pago->fecha_pago,
                'forma_pago'     => $pago->forma_pago,
                'tiene_factura'  => $pago->cfdis->where('estado', 'vigente')->isNotEmpty(),
                'cfdi_uuid'      => $pago->cfdis->where('estado', 'vigente')->first()?->uuid_sat,
            ]);

        return response()->json($pagos);
    }

    /**
     * GET /portal/razones-sociales
     * Lista las razones sociales del contacto autenticado.
     */
    public function razonesSociales(): JsonResponse
    {
        $contacto = auth()->user()->contactoFamiliar()
            ->with('razonesSociales')
            ->first();

        if (!$contacto) {
            return response()->json([]);
        }

        return response()->json($contacto->razonesSociales()->activa()->get());
    }

    // ── Helper ───────────────────────────────────────────

    /**
     * Verifica que el alumno pertenezca a la familia del padre autenticado.
     */
    private function verificarAccesoAlumno(int $alumnoId): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (!$contacto || !$contacto->familia_id) {
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
