<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnularPagoRequest;
use App\Http\Requests\StorePagoRequest;
use App\Models\Auditoria;
use App\Models\Cargo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    /** GET /pagos */
    public function index(Request $request): JsonResponse
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

        return response()->json($query->paginate($request->get('per_page', 30)));
    }

    /** GET /pagos/{id} */
    public function show(int $id): JsonResponse
    {
        $pago = Pago::with([
            'cajero',
            'autorizadoPor',
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis',
        ])->findOrFail($id);

        return response()->json(array_merge($pago->toArray(), [
            'total_descuentos' => $pago->total_descuentos,
            'total_recargos'   => $pago->total_recargos,
        ]));
    }

    /**
     * POST /pagos
     * Registra un pago con uno o varios cargos en una sola exhibición.
     *
     * Flujo:
     *   1. Calcular preview de cada cargo (beca, descuento, recargo)
     *   2. Crear encabezado pago con monto_total = suma de monto_final
     *   3. Crear un pago_detalle por cada cargo
     *   4. Actualizar estado de cada cargo (pagado / parcial)
     *   5. Auditoría
     */
    public function store(StorePagoRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $cajeroId    = auth()->id();
            $montoTotal  = 0;
            $detallesData = [];

            // ── Paso 1: Calcular preview de cada cargo ────
            $cargoController = app(CargoController::class);

            foreach ($data['detalles'] as $detalle) {
                $cargo   = Cargo::with(['inscripcion', 'asignacion.plan', 'detallesPagosVigentes'])->findOrFail($detalle['cargo_id']);
                $preview = $cargoController->calcularPreviewCobro($cargo);

                $montoFinal = round(
                    $detalle['monto']
                    - $preview['descuento_beca']
                    - $preview['descuento_otros']
                    + $preview['recargo'],
                    2
                );

                $montoTotal += $montoFinal;

                $detallesData[] = [
                    'cargo'           => $cargo,
                    'preview'         => $preview,
                    'monto_abonado'   => $detalle['monto'],
                    'monto_final'     => max(0, $montoFinal),
                ];
            }

            // ── Paso 2: Crear encabezado del pago ─────────
            $pago = Pago::create([
                'cajero_id'    => $cajeroId,
                'monto_total'  => round($montoTotal, 2),
                'fecha_pago'   => $data['fecha_pago'],
                'forma_pago'   => $data['forma_pago'],
                'referencia'   => $data['referencia'] ?? null,
                'folio_recibo' => $this->generarFolioRecibo(),
                'estado'       => 'vigente',
            ]);

            // ── Paso 3: Crear detalles y actualizar cargos ─
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

                // ── Paso 4: Actualizar estado del cargo ────
                $nuevoSaldoAbonado = $cargo->saldo_abonado + $item['monto_abonado'];
                $montoACobrarTotal = $preview['total_a_cobrar'] + $cargo->saldo_abonado;

                $nuevoEstado = $nuevoSaldoAbonado >= $montoACobrarTotal ? 'pagado' : 'parcial';
                $cargo->update(['estado' => $nuevoEstado]);
            }

            // ── Paso 5: Auditoría ─────────────────────────
            Auditoria::registrar('pago', $pago->id, 'insert', null, [
                'folio_recibo' => $pago->folio_recibo,
                'monto_total'  => $pago->monto_total,
                'num_cargos'   => count($detallesData),
            ]);

            DB::commit();

            return response()->json(
                $pago->load(['cajero', 'detalles.cargo.concepto']),
                201
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar el pago: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /pagos/{id}/anular
     * Anula el pago completo y todos sus detalles.
     * Los cargos afectados regresan a pendiente o parcial.
     */
    public function anular(AnularPagoRequest $request, int $id): JsonResponse
    {
        $pago = Pago::with('detalles.cargo')->findOrFail($id);

        DB::beginTransaction();

        try {
            $anterior = $pago->toArray();

            // Anular encabezado
            $pago->update([
                'estado'         => 'anulado',
                'motivo'         => $request->motivo,
                'autorizado_por' => auth()->id(),
            ]);

            // Recalcular estado de cada cargo afectado
            foreach ($pago->detalles as $detalle) {
                $cargo = $detalle->cargo;

                // El saldo abonado ya no incluye este pago anulado
                $saldoRestante = $cargo->fresh()->saldo_abonado;
                $nuevoEstado   = $saldoRestante > 0 ? 'parcial' : 'pendiente';
                $cargo->update(['estado' => $nuevoEstado]);
            }

            Auditoria::registrar('pago', $pago->id, 'anulacion', $anterior, $pago->fresh()->toArray());

            DB::commit();

            return response()->json([
                'message' => 'Pago anulado correctamente.',
                'pago'    => $pago->fresh()->load('detalles.cargo'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al anular el pago: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /pagos/corte
     * Resumen del día para el cajero.
     */
    public function corte(Request $request): JsonResponse
    {
        $fecha    = $request->get('fecha', now()->toDateString());
        $cajeroId = auth()->id();

        $pagos = Pago::with(['detalles.cargo.concepto', 'detalles.cargo.inscripcion.alumno'])
            ->where('cajero_id', $cajeroId)
            ->where('fecha_pago', $fecha)
            ->where('estado', 'vigente')
            ->get();

        $resumen = [
            'fecha'          => $fecha,
            'total_pagos'    => $pagos->count(),
            'total_cargos'   => $pagos->sum(fn($p) => $p->detalles->count()),
            'total_cobrado'  => $pagos->sum('monto_total'),
            'por_forma_pago' => $pagos->groupBy('forma_pago')->map(fn($g) => [
                'cantidad' => $g->count(),
                'total'    => $g->sum('monto_total'),
            ]),
        ];

        return response()->json([
            'resumen' => $resumen,
            'pagos'   => $pagos,
        ]);
    }

    // ── Helper ───────────────────────────────────────────

    private function generarFolioRecibo(): string
    {
        $ultimo = Pago::max('id') ?? 0;
        return 'REC-' . now()->format('Y') . '-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }
}
