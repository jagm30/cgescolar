<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnularPagoRequest;
use App\Http\Requests\StorePagoRequest;
use App\Models\Auditoria;
use App\Models\Cargo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    use RespondsWithJson;

    /** GET /pagos */
    public function index(Request $request)
    {
        $query = Pago::with(['cajero', 'detalles.cargo.concepto', 'detalles.cargo.inscripcion.alumno'])
            ->when($request->filled('alumno_id'), fn($q) => $q->whereHas(
                'detalles.cargo.inscripcion', fn($q) => $q->where('alumno_id', $request->alumno_id)
            ))
            ->when($request->filled('fecha_desde'), fn($q) => $q->where('fecha_pago', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn($q) => $q->where('fecha_pago', '<=', $request->fecha_hasta))
            ->when($request->filled('forma_pago'),  fn($q) => $q->where('forma_pago', $request->forma_pago))
            ->when($request->filled('estado'),      fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('folio'),       fn($q) => $q->where('folio_recibo', 'like', "%{$request->folio}%"))
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id');

        $pagos = $query->paginate($request->get('per_page', 30));

        if ($request->ajax()) {
            return response()->json($pagos);
        }

        return view('pagos.index', compact('pagos'));
    }

    /** GET /pagos/{id} */
    public function show(int $id)
    {
        $pago = Pago::with([
            'cajero', 'autorizadoPor',
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json(array_merge($pago->toArray(), [
                'total_descuentos' => $pago->total_descuentos,
                'total_recargos'   => $pago->total_recargos,
            ]));
        }

        return view('pagos.show', compact('pago'));
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
            $cajeroId    = auth()->id();
            $montoTotal  = 0;
            $detallesData = [];
            $cargoController = app(CargoController::class);

            foreach ($data['detalles'] as $detalle) {
                $cargo   = Cargo::with(['inscripcion', 'asignacion.plan', 'detallesPagosVigentes'])->findOrFail($detalle['cargo_id']);
                $preview = $cargoController->calcularPreviewCobro($cargo);

                $montoFinal = round(
                    $detalle['monto'] - $preview['descuento_beca'] - $preview['descuento_otros'] + $preview['recargo'],
                    2
                );

                $montoTotal    += max(0, $montoFinal);
                $detallesData[] = [
                    'cargo'         => $cargo,
                    'preview'       => $preview,
                    'monto_abonado' => $detalle['monto'],
                    'monto_final'   => max(0, $montoFinal),
                ];
            }

            // Encabezado del pago
            $pago = Pago::create([
                'cajero_id'    => $cajeroId,
                'monto_total'  => round($montoTotal, 2),
                'fecha_pago'   => $data['fecha_pago'],
                'forma_pago'   => $data['forma_pago'],
                'referencia'   => $data['referencia'] ?? null,
                'folio_recibo' => $this->generarFolioRecibo(),
                'estado'       => 'vigente',
            ]);

            foreach ($detallesData as $item) {
                $cargo   = $item['cargo'];
                $preview = $item['preview'];

                PagoDetalle::create([
                    'pago_id'          => $pago->id,
                    'cargo_id'         => $cargo->id,
                    'descuento_beca'   => $preview['descuento_beca'],
                    'descuento_otros'  => $preview['descuento_otros'],
                    'recargo_aplicado' => $preview['recargo'],
                    'monto_abonado'    => $item['monto_abonado'],
                    'monto_final'      => $item['monto_final'],
                ]);

                // Actualizar estado del cargo
                $nuevoSaldo  = $cargo->saldo_abonado + $item['monto_abonado'];
                $montoTotal  = $preview['total_a_cobrar'] + $cargo->saldo_abonado;
                $cargo->update(['estado' => $nuevoSaldo >= $montoTotal ? 'pagado' : 'parcial']);
            }

            Auditoria::registrar('pago', $pago->id, 'insert', null, [
                'folio_recibo' => $pago->folio_recibo,
                'monto_total'  => $pago->monto_total,
                'num_cargos'   => count($detallesData),
            ]);

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'pagos.show',
                jsonData: ['pago' => $pago->load(['cajero', 'detalles.cargo.concepto'])],
                mensaje: "Pago registrado. Folio: {$pago->folio_recibo}",
                jsonStatus: 201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respuestaError('Error al registrar el pago: ' . $e->getMessage());
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
                'estado'         => 'anulado',
                'motivo'         => $request->motivo,
                'autorizado_por' => auth()->id(),
            ]);

            foreach ($pago->detalles as $detalle) {
                $cargo         = $detalle->cargo;
                $saldoRestante = $cargo->fresh()->saldo_abonado;
                $cargo->update(['estado' => $saldoRestante > 0 ? 'parcial' : 'pendiente']);
            }

            Auditoria::registrar('pago', $pago->id, 'anulacion', $anterior, $pago->fresh()->toArray());

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'pagos.show',
                jsonData: ['pago' => $pago->fresh()->load('detalles.cargo')],
                mensaje: "Pago {$pago->folio_recibo} anulado correctamente."
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respuestaError('Error al anular el pago: ' . $e->getMessage());
        }
    }

    /** GET /pagos/corte */
    public function corte(Request $request)
    {
        $fecha    = $request->get('fecha', now()->toDateString());
        $cajeroId = auth()->id();

        $pagos = Pago::with(['detalles.cargo.concepto', 'detalles.cargo.inscripcion.alumno'])
            ->where('cajero_id', $cajeroId)
            ->where('fecha_pago', $fecha)
            ->where('estado', 'vigente')
            ->get();

        $resumen = [
            'fecha'         => $fecha,
            'total_pagos'   => $pagos->count(),
            'total_cargos'  => $pagos->sum(fn($p) => $p->detalles->count()),
            'total_cobrado' => $pagos->sum('monto_total'),
            'por_forma_pago'=> $pagos->groupBy('forma_pago')->map(fn($g) => [
                'cantidad' => $g->count(),
                'total'    => $g->sum('monto_total'),
            ]),
        ];

        if ($request->ajax()) {
            return response()->json(['resumen' => $resumen, 'pagos' => $pagos]);
        }

        return view('pagos.corte', compact('resumen', 'pagos', 'fecha'));
    }

    // ── Helper ───────────────────────────────────────────

    private function generarFolioRecibo(): string
    {
        $ultimo = Pago::max('id') ?? 0;
        return 'REC-' . now()->format('Y') . '-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }
}
