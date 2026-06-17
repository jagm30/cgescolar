<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Cfdi;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Services\FacturaComService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PortalPadreController extends Controller
{
    public function dashboard(): View
    {
        $alumnos = $this->alumnosDelPadre();
        $resumen = $this->resumenFamilia($alumnos);

        return view('portal.dashboard', compact('alumnos', 'resumen'));
    }

    public function hijos(): View|JsonResponse
    {
        $alumnos = $this->alumnosDelPadre();

        if (request()->ajax()) {
            return response()->json($alumnos);
        }

        return view('portal.hijos', compact('alumnos'));
    }

    public function estadoCuenta(int $alumnoId): View|JsonResponse|RedirectResponse
    {
        $this->verificarAccesoAlumno($alumnoId);

        $inscripcion = Inscripcion::query()
            ->with(['alumno', 'grupo.grado.nivel', 'ciclo'])
            ->where('alumno_id', $alumnoId)
            ->where('activo', true)
            ->latest('id')
            ->first();

        if (! $inscripcion) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Sin inscripcion activa.'], 404);
            }

            return back()->with('error', 'No tiene inscripcion activa.');
        }

        $cargos = Cargo::with(['concepto', 'detallesPagosVigentes'])
            ->where('inscripcion_id', $inscripcion->id)
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn (Cargo $cargo) => [
                'id' => $cargo->id,
                'concepto' => $cargo->concepto->nombre,
                'periodo' => $cargo->periodo,
                'monto_original' => $cargo->monto_original,
                'saldo_abonado' => $cargo->saldo_abonado,
                'saldo_pendiente' => max(0, $cargo->saldo_pendiente_base),
                'estado' => $cargo->detallesPagosVigentes->isNotEmpty() || $cargo->estado === 'condonado'
                    ? $cargo->estado_real
                    : 'pendiente',
                'fecha_vencimiento' => $cargo->fecha_vencimiento,
                'puede_facturar' => $cargo->detallesPagosVigentes->isNotEmpty(),
            ]);

        $resumen = [
            'total_cargado' => $cargos->sum('monto_original'),
            'total_pendiente' => $cargos->sum('saldo_pendiente'),
            'total_pagado' => $cargos->sum('saldo_abonado'),
            'total_cargos' => $cargos->count(),
            'cargos_vencidos' => $cargos->filter(fn (array $cargo) => str_contains($cargo['estado'], 'vencido'))->count(),
        ];

        $alumno = $inscripcion->alumno;

        if (request()->ajax()) {
            return response()->json(['resumen' => $resumen, 'cargos' => $cargos]);
        }

        return view('portal.estado-cuenta', compact('alumno', 'cargos', 'inscripcion', 'resumen'));
    }

    public function historialPagos(int $alumnoId): View|JsonResponse
    {
        $this->verificarAccesoAlumno($alumnoId);

        $pagos = Pago::with(['detalles.cargo.concepto', 'cfdis'])
            ->whereHas('detalles.cargo.inscripcion', fn ($query) => $query->where('alumno_id', $alumnoId))
            ->where('estado', 'vigente')
            ->orderByDesc('fecha_pago')
            ->get()
            ->map(fn (Pago $pago) => [
                'id' => $pago->id,
                'folio_recibo' => $pago->folio_recibo,
                'conceptos' => $pago->detalles->map(fn ($detalle) => $detalle->cargo->etiqueta)->join(', '),
                'monto_total' => $pago->monto_total,
                'fecha_pago' => $pago->fecha_pago,
                'forma_pago' => $pago->forma_pago,
                'tiene_factura' => $pago->cfdis->where('estado', 'vigente')->isNotEmpty(),
                'cfdi_id' => $pago->cfdis->where('estado', 'vigente')->first()?->id,
                'cfdi_uuid' => $pago->cfdis->where('estado', 'vigente')->first()?->uuid_sat,
            ]);

        $alumno = Alumno::findOrFail($alumnoId);

        if (request()->ajax()) {
            return response()->json($pagos);
        }

        return view('portal.historial-pagos', compact('alumno', 'pagos'));
    }

    public function razonesSociales(): View|JsonResponse
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

    public function descargarCfdi(int $cfdiId, string $formato, FacturaComService $factura): Response|RedirectResponse
    {
        if (! in_array($formato, ['pdf', 'xml'], true)) {
            abort(404);
        }

        $cfdi = Cfdi::with('pago.detalles.cargo.inscripcion')->findOrFail($cfdiId);

        $this->verificarAccesoCfdi($cfdi);

        if (! $cfdi->factura_uid) {
            return back()->with('error', 'No se puede descargar: CFDI sin UID de factura.com.');
        }

        try {
            $contenido = $factura->descargar($cfdi->factura_uid, $formato);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al descargar la factura: ' . $e->getMessage());
        }

        $nombre   = ($cfdi->folio ?? $cfdi->uuid_sat ?? "CFDI-{$cfdiId}") . ".{$formato}";
        $mimeType = $formato === 'pdf' ? 'application/pdf' : 'application/xml';

        return response($contenido, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ]);
    }

    private function verificarAccesoAlumno(int $alumnoId): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto?->familia_id) {
            abort(403, 'No tiene acceso a este alumno.');
        }

        $perteneceAFamilia = Alumno::where('id', $alumnoId)
            ->where('familia_id', $contacto->familia_id)
            ->exists();

        if (! $perteneceAFamilia) {
            abort(403, 'No tiene acceso a la informacion de este alumno.');
        }
    }

    private function verificarAccesoCfdi(Cfdi $cfdi): void
    {
        $contacto = auth()->user()->contactoFamiliar;

        if (! $contacto?->familia_id) {
            abort(403, 'No tiene acceso a esta factura.');
        }

        $alumnoIds = Alumno::where('familia_id', $contacto->familia_id)->pluck('id');

        $perteneceAFamilia = $cfdi->pago?->detalles
            ->filter(fn ($d) => $alumnoIds->contains($d->cargo?->inscripcion?->alumno_id))
            ->isNotEmpty() ?? false;

        if (! $perteneceAFamilia) {
            abort(403, 'No tiene acceso a esta factura.');
        }
    }

    private function alumnosDelPadre(): Collection
    {
        $contacto = auth()->user()->contactoFamiliar()->first();

        if (! $contacto?->familia_id) {
            return collect();
        }

        return Alumno::query()
            ->where('familia_id', $contacto->familia_id)
            ->where('estado', 'activo')
            ->whereHas('inscripciones', fn ($query) => $query->where('activo', true))
            ->with([
                'inscripciones' => fn ($query) => $query->where('activo', true)->latest('id'),
                'inscripciones.ciclo',
                'inscripciones.grupo.grado.nivel',
            ])
            ->get();
    }

    private function resumenFamilia(Collection $alumnos): array
    {
        $alumnoIds = $alumnos->pluck('id');

        $cargos = Cargo::query()
            ->with('detallesPagosVigentes')
            ->whereHas('inscripcion', fn ($query) => $query->whereIn('alumno_id', $alumnoIds))
            ->get();

        return [
            'hijos' => $alumnos->count(),
            'inscritos' => $alumnos->filter(fn (Alumno $alumno) => $alumno->inscripciones->where('activo', true)->isNotEmpty())->count(),
            'total_cargado' => $cargos->sum('monto_original'),
            'total_pagado' => $cargos->sum(fn (Cargo $cargo) => $cargo->saldo_abonado),
            'total_pendiente' => $cargos->sum(fn (Cargo $cargo) => max(0, $cargo->saldo_pendiente_base)),
            'cargos_vencidos' => $cargos->filter(fn (Cargo $cargo) => str_contains($cargo->estado_real, 'vencido'))->count(),
        ];
    }
}
