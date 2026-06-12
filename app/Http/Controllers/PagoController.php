<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnularPagoRequest;
use App\Http\Requests\StorePagoRequest;
use App\Models\Auditoria;
use App\Models\Cargo;
use App\Models\ConceptoCobro;
use App\Models\ConfigFiscal;
use App\Models\NivelEscolar;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\RazonSocialContacto;
use App\Models\Usuario;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    use RespondsWithJson;

    /** GET /pagos */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 30);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 30;
        }

        $base = Pago::query()
            ->when($request->filled('alumno_id'), fn ($q) => $q->whereHas(
                'detalles.cargo.inscripcion', fn ($q) => $q->where('alumno_id', $request->alumno_id)
            ))
            ->when($request->filled('fecha_desde'), fn ($q) => $q->where('fecha_pago', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn ($q) => $q->where('fecha_pago', '<=', $request->fecha_hasta))
            ->when($request->filled('forma_pago'), fn ($q) => $q->where('forma_pago', $request->forma_pago))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('folio'), fn ($q) => $q->where('folio_recibo', 'like', "%{$request->folio}%"));

        $resumen = [
            'total'        => (clone $base)->count(),
            'vigentes'     => (clone $base)->where('estado', 'vigente')->count(),
            'anulados'     => (clone $base)->where('estado', 'anulado')->count(),
            'total_cobrado'=> (clone $base)->where('estado', 'vigente')->sum('monto_total'),
        ];

        $pagos = (clone $base)
            ->with(['cajero', 'detalles.cargo.concepto', 'detalles.cargo.inscripcion.alumno', 'cfdis', 'cfdiGlobal'])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json($pagos);
        }

        $configFiscal = ConfigFiscal::first();

        return view('pagos.index', compact('pagos', 'resumen', 'perPage', 'configFiscal'));
    }

    /** GET /pagos/{id} */
    public function show(int $id)
    {
        $pago = Pago::with([
            'cajero', 'autorizadoPor',
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis.razonSocial',
            'cfdiGlobal',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json(array_merge($pago->toArray(), [
                'total_descuentos' => $pago->total_descuentos,
                'total_recargos'   => $pago->total_recargos,
            ]));
        }

        // RFCs disponibles de los alumnos incluidos en este pago
        $alumnoIds = $pago->detalles
            ->map(fn ($d) => $d->cargo?->inscripcion?->alumno_id)
            ->filter()->unique()->values();

        $razonesDisponibles = RazonSocialContacto::query()
            ->where('activo', true)
            ->whereHas('contacto.alumnos', fn ($q) => $q->whereIn('alumno.id', $alumnoIds))
            ->with('contacto')
            ->get();

        $configFiscal = ConfigFiscal::first();

        return view('pagos.show', compact('pago', 'razonesDisponibles', 'configFiscal'));
    }

    /** GET /pagos/{pago}/form-factura  — datos para el modal de facturación (AJAX) */
    public function formFactura(int $id)
    {
        $pago = Pago::with([
            'detalles.cargo.inscripcion.alumno',
            'cfdis' => fn ($q) => $q->where('estado', 'vigente'),
        ])->findOrFail($id);

        $alumnoIds = $pago->detalles
            ->map(fn ($d) => $d->cargo?->inscripcion?->alumno_id)
            ->filter()->unique()->values();

        $razones = RazonSocialContacto::query()
            ->where('activo', true)
            ->whereHas('contacto.alumnos', fn ($q) => $q->whereIn('alumno.id', $alumnoIds))
            ->with('contacto')
            ->get()
            ->map(fn ($rs) => [
                'id'          => $rs->id,
                'rfc'         => $rs->rfc,
                'razon_social'=> $rs->razon_social,
                'contacto'    => $rs->contacto?->nombre_completo,
            ]);

        $alumnos = $pago->detalles
            ->map(fn ($d) => $d->cargo?->inscripcion?->alumno)
            ->filter()->unique('id')
            ->map(fn ($a) => trim("{$a->ap_paterno} {$a->ap_materno}, {$a->nombre}"))
            ->values();

        return response()->json([
            'pago_id'        => $pago->id,
            'folio'          => $pago->folio_recibo,
            'monto'          => number_format($pago->monto_total, 2),
            'ya_facturado'   => $pago->cfdis->isNotEmpty(),
            'alumnos'        => $alumnos,
            'razones'        => $razones,
        ]);
    }

    /** GET /pagos/create */
    public function create(Request $request)
    {
        // Puede venir con cargo_id preseleccionado desde la pantalla del alumno
        $cargo = $request->filled('cargo_id')
            ? Cargo::with(['inscripcion.alumno', 'concepto', 'asignacion.plan'])->find($request->cargo_id)
            : null;

        return view('pagos.create', compact('cargo'));
    }

    /**
     * POST /pagos
     * Registra un pago con uno o varios cargos en una sola exhibición.
     */
    public function store(StorePagoRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $cajeroId = auth()->id();
            $montoTotal = 0;
            $detallesData = [];
            $cargoController = app(CargoController::class);

            foreach ($data['detalles'] as $detalle) {
                $cargo = Cargo::with([
                    'inscripcion',
                    'descuentos',
                    'asignacion.plan.politicasDescuentoActivas',
                    'asignacion.plan.politicaRecargoActiva',
                    'detallesPagosVigentes',
                ])->findOrFail($detalle['cargo_id']);
                $preview = $cargoController->calcularPreviewCobro($cargo);

                $montoCobrado = round((float) $detalle['monto'], 2);

                $montoTotal += $montoCobrado;
                $detallesData[] = [
                    'cargo' => $cargo,
                    'preview' => $preview,
                    'monto_abonado' => $montoCobrado,
                    'monto_final' => $montoCobrado,
                ];
            }

            // Encabezado del pago
            $pago = Pago::create([
                'cajero_id' => $cajeroId,
                'monto_total' => round($montoTotal, 2),
                'fecha_pago' => $data['fecha_pago'],
                'forma_pago' => $data['forma_pago'],
                'referencia' => $data['referencia'] ?? null,
                'folio_recibo' => $this->generarFolioRecibo(),
                'estado' => 'vigente',
            ]);

            foreach ($detallesData as $item) {
                $cargo = $item['cargo'];
                $preview = $item['preview'];

                PagoDetalle::create([
                    'pago_id' => $pago->id,
                    'cargo_id' => $cargo->id,
                    'descuento_beca' => $preview['descuento_beca'],
                    'descuento_otros' => $preview['descuento_otros'],
                    'recargo_aplicado' => $preview['recargo'],
                    'monto_abonado' => $item['monto_abonado'],
                    'monto_final' => $item['monto_final'],
                ]);

                // Actualizar estado del cargo
                $nuevoSaldo = round($cargo->saldo_abonado + $item['monto_abonado'], 2);
                $totalExigible = round($preview['total_a_cobrar'] + $cargo->saldo_abonado, 2);
                $cargo->update(['estado' => $nuevoSaldo >= $totalExigible ? 'pagado' : 'parcial']);
            }

            Auditoria::registrar('pago', $pago->id, 'insert', null, [
                'folio_recibo' => $pago->folio_recibo,
                'monto_total' => $pago->monto_total,
                'num_cargos' => count($detallesData),
            ]);

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'pagos.show',
                jsonData: ['pago' => $pago->load(['cajero', 'detalles.cargo.concepto'])],
                mensaje: "Pago registrado. Folio: {$pago->folio_recibo}",
                jsonStatus: 201,
                routeParams: [$pago->id]
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->respuestaError('Error al registrar el pago: '.$e->getMessage());
        }
    }

    /**
     * POST /pagos/{id}/anular
     * Anula el pago y revierte el estado de todos los cargos afectados.
     */
    public function anular(AnularPagoRequest $request, int $id)
    {
        $pago = Pago::with('detalles.cargo')->findOrFail($id);

        DB::beginTransaction();
        try {
            $anterior = $pago->toArray();

            $pago->update([
                'estado' => 'anulado',
                'motivo' => $request->motivo,
                'autorizado_por' => auth()->id(),
            ]);

            foreach ($pago->detalles as $detalle) {
                $cargo = $detalle->cargo;
                $saldoRestante = $cargo->fresh()->saldo_abonado;
                $cargo->update(['estado' => $saldoRestante > 0 ? 'parcial' : 'pendiente']);
            }

            Auditoria::registrar('pago', $pago->id, 'anulacion', $anterior, $pago->fresh()->toArray());

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'pagos.show',
                jsonData: ['pago' => $pago->fresh()->load('detalles.cargo')],
                mensaje: "Pago {$pago->folio_recibo} anulado correctamente.",
                routeParams: [$pago->id]
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->respuestaError('Error al anular el pago: '.$e->getMessage());
        }
    }

    /** GET /pagos/corte */
    public function corte(Request $request)
    {
        $fecha    = $request->get('fecha', now()->toDateString());
        $usuario  = auth()->user();
        $esAdmin  = $usuario->esAdministrador();

        $baseVigente = Pago::query()
            ->where('fecha_pago', $fecha)
            ->where('estado', 'vigente')
            ->when(! $esAdmin, fn ($q) => $q->where('cajero_id', $usuario->id))
            ->when($esAdmin && $request->filled('cajero_id'), fn ($q) => $q->where('cajero_id', $request->cajero_id));

        $pagos = (clone $baseVigente)
            ->with(['cajero', 'detalles.cargo.concepto', 'detalles.cargo.inscripcion.alumno'])
            ->orderBy('id')
            ->get();

        $totalAnulados = Pago::query()
            ->where('fecha_pago', $fecha)
            ->where('estado', 'anulado')
            ->when(! $esAdmin, fn ($q) => $q->where('cajero_id', $usuario->id))
            ->when($esAdmin && $request->filled('cajero_id'), fn ($q) => $q->where('cajero_id', $request->cajero_id))
            ->count();

        $resumen = [
            'fecha'          => $fecha,
            'total_pagos'    => $pagos->count(),
            'total_cargos'   => $pagos->sum(fn ($p) => $p->detalles->count()),
            'total_cobrado'  => $pagos->sum('monto_total'),
            'total_anulados' => $totalAnulados,
            'por_forma_pago' => $pagos->groupBy('forma_pago')->map(fn ($g) => [
                'cantidad' => $g->count(),
                'total'    => $g->sum('monto_total'),
            ]),
        ];

        // Desglose por cajero (solo admin)
        $porCajero = $esAdmin
            ? $pagos->groupBy('cajero_id')->map(fn ($g) => [
                'cajero'   => $g->first()->cajero,
                'cantidad' => $g->count(),
                'total'    => $g->sum('monto_total'),
                'pagos'    => $g,
            ])->values()
            : null;

        $cajeros = $esAdmin
            ? Usuario::internos()->activo()->orderBy('nombre')->get()
            : null;

        if ($request->ajax()) {
            return response()->json(['resumen' => $resumen, 'pagos' => $pagos]);
        }

        return view('pagos.corte', compact('resumen', 'pagos', 'fecha', 'esAdmin', 'porCajero', 'cajeros'));
    }

    /** GET /pagos/detalle-ingresos */
    public function detalleIngresos(Request $request): \Illuminate\View\View
    {
        $fechaDesde = $request->get('fecha_desde', now()->startOfMonth()->toDateString());
        $fechaHasta = $request->get('fecha_hasta', now()->toDateString());

        $conceptos = ConceptoCobro::query()->orderBy('nombre')->get();
        $niveles   = NivelEscolar::query()->activo()->get();

        $detalles = PagoDetalle::query()
            ->whereHas('pago', fn ($q) => $q
                ->where('estado', 'vigente')
                ->whereBetween('fecha_pago', [$fechaDesde, $fechaHasta])
                ->when($request->filled('forma_pago'), fn ($q) => $q->where('forma_pago', $request->forma_pago))
            )
            ->when($request->filled('concepto_id'), fn ($q) => $q->whereHas(
                'cargo', fn ($q) => $q->where('concepto_id', $request->concepto_id)
            ))
            ->when($request->filled('nivel_id'), fn ($q) => $q->whereHas(
                'cargo.inscripcion.grupo.grado', fn ($q) => $q->where('nivel_id', $request->nivel_id)
            ))
            ->when($request->filled('periodo'), fn ($q) => $q->whereHas(
                'cargo', fn ($q) => $q->where('periodo', $request->periodo)
            ))
            ->with(['pago.cajero', 'cargo.concepto', 'cargo.inscripcion.alumno', 'cargo.inscripcion.grupo.grado.nivel'])
            ->get();

        // Agrupar por concepto + periodo (clave compuesta para máximo detalle)
        $porConcepto = $detalles
            ->groupBy(fn ($d) => ($d->cargo?->concepto_id ?? 0).':'.($d->cargo?->periodo ?? ''))
            ->map(fn ($grupo) => [
                'concepto' => $grupo->first()->cargo?->concepto,
                'periodo'  => $grupo->first()->cargo?->periodo,
                'periodo_label' => $grupo->first()->cargo?->periodo_label,
                'cantidad' => $grupo->count(),
                'total'    => $grupo->sum('monto_abonado'),
            ])
            ->filter(fn ($g) => $g['concepto'] !== null)
            ->sortByDesc('total')
            ->values();

        $pagosUnicos = $detalles
            ->groupBy('pago_id')
            ->map(fn ($g) => $g->first()->pago)
            ->filter()
            ->sortByDesc(fn ($p) => $p->fecha_pago)
            ->values();

        $resumen = [
            'total_cobrado'   => $detalles->sum('monto_abonado'),
            'total_pagos'     => $pagosUnicos->count(),
            'total_conceptos' => $porConcepto->count(),
        ];

        return view('pagos.detalle_ingresos', compact(
            'conceptos', 'niveles', 'porConcepto', 'pagosUnicos',
            'resumen', 'fechaDesde', 'fechaHasta'
        ));
    }

    // ── Helper ───────────────────────────────────────────

    private function generarFolioRecibo(): string
    {
        $ultimo = Pago::max('id') ?? 0;

        return 'REC-'.now()->format('Y').'-'.str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }
}
