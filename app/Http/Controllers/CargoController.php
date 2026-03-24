<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\Inscripcion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CargoController extends Controller
{
    /**
     * GET /cargos
     * Lista cargos del ciclo activo con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $query = Cargo::with(['inscripcion.alumno', 'inscripcion.grupo', 'concepto', 'pagosVigentes'])
            ->whereHas('inscripcion', fn($q) => $q->where('ciclo_id', $cicloId))
            ->when($request->filled('alumno_id'), fn($q) => $q->whereHas('inscripcion',
                fn($q) => $q->where('alumno_id', $request->alumno_id)))
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('periodo'), fn($q) => $q->where('periodo', $request->periodo))
            ->orderBy('fecha_vencimiento');

        $cargos = $query->paginate($request->get('per_page', 30));

        // Agregar estado_real calculado a cada cargo
        $cargos->getCollection()->transform(fn($c) => array_merge(
            $c->toArray(),
            ['estado_real' => $c->estado_real, 'saldo_pendiente' => $c->saldo_pendiente_base]
        ));

        return response()->json($cargos);
    }

    /** GET /cargos/{id} */
    public function show(int $id): JsonResponse
    {
        $cargo = Cargo::with([
            'inscripcion.alumno',
            'concepto',
            'asignacion.plan',
            'pagosVigentes',
            'descuentos',
        ])->findOrFail($id);

        // Calcular descuentos en tiempo real
        $preview = $this->calcularPreviewCobro($cargo);

        return response()->json([
            'cargo'   => array_merge($cargo->toArray(), [
                'estado_real'    => $cargo->estado_real,
                'saldo_abonado'  => $cargo->saldo_abonado,
            ]),
            'preview_cobro' => $preview,
        ]);
    }

    /**
     * POST /cargos/generar
     * Genera cargos para un ciclo según las asignaciones de planes.
     * Solo administrador.
     */
    public function generar(Request $request): JsonResponse
    {
        if (auth()->user()->rol !== 'administrador') {
            return response()->json(['message' => 'Sin permisos.'], 403);
        }

        $request->validate([
            'ciclo_id' => ['required', 'exists:ciclo_escolar,id'],
        ]);

        $cicloId   = $request->ciclo_id;
        $generadoPor = auth()->id();

        DB::beginTransaction();

        try {
            $inscripciones = Inscripcion::with(['grupo.grado.nivel', 'alumno'])
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
                ->get();

            $totalGenerados = 0;
            $yaExistian     = 0;

            foreach ($inscripciones as $inscripcion) {
                $nivelId = $inscripcion->grupo->grado->nivel_id;

                // Obtener plan según jerarquía individual > grupo > nivel
                $asignacion = AsignacionPlan::with('plan.planPagoConceptos')
                    ->where(function ($q) use ($inscripcion, $nivelId) {
                        $q->where(fn($q) => $q->where('origen', 'individual')->where('alumno_id', $inscripcion->alumno_id))
                          ->orWhere(fn($q) => $q->where('origen', 'grupo')->where('grupo_id', $inscripcion->grupo_id))
                          ->orWhere(fn($q) => $q->where('origen', 'nivel')->where('nivel_id', $nivelId));
                    })
                    ->whereHas('plan', fn($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
                    ->orderByRaw("FIELD(origen, 'individual', 'grupo', 'nivel')")
                    ->first();

                if (!$asignacion) continue;

                $plan    = $asignacion->plan;
                $periodos = $this->calcularPeriodos($plan->fecha_inicio, $plan->fecha_fin, $plan->periodicidad);

                foreach ($plan->planPagoConceptos as $planConcepto) {
                    foreach ($periodos as $periodo) {
                        // Evitar duplicados — índice único (inscripcion_id, concepto_id, periodo)
                        $existe = Cargo::where('inscripcion_id', $inscripcion->id)
                            ->where('concepto_id', $planConcepto->concepto_id)
                            ->where('periodo', $periodo['periodo'])
                            ->exists();

                        if ($existe) {
                            $yaExistian++;
                            continue;
                        }

                        Cargo::create([
                            'inscripcion_id'    => $inscripcion->id,
                            'concepto_id'       => $planConcepto->concepto_id,
                            'asignacion_id'     => $asignacion->id,
                            'generado_por'      => $generadoPor,
                            'monto_original'    => $planConcepto->monto,
                            'fecha_vencimiento' => $periodo['vencimiento'],
                            'estado'            => 'pendiente',
                            'periodo'           => $periodo['periodo'],
                        ]);

                        $totalGenerados++;
                    }
                }
            }

            Auditoria::registrar('cargo', 0, 'insert', null, [
                'ciclo_id'        => $cicloId,
                'total_generados' => $totalGenerados,
                'ya_existian'     => $yaExistian,
            ]);

            DB::commit();

            return response()->json([
                'message'         => "Generación completada.",
                'total_generados' => $totalGenerados,
                'ya_existian'     => $yaExistian,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al generar cargos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * GET /cargos/{id}/preview
     * Calcula en tiempo real el total a cobrar con descuentos y recargos.
     * Usado por el cajero antes de confirmar el pago.
     */
    public function preview(int $id): JsonResponse
    {
        $cargo   = Cargo::with(['inscripcion.alumno', 'concepto', 'asignacion.plan', 'pagosVigentes'])->findOrFail($id);
        $preview = $this->calcularPreviewCobro($cargo);

        return response()->json($preview);
    }

    // ── Helpers privados ─────────────────────────────────

    /**
     * Calcula en tiempo real descuentos, recargos y total a cobrar.
     */
    public function calcularPreviewCobro(Cargo $cargo): array
    {
        $montoOriginal = (float) $cargo->monto_original;
        $cicloId       = $cargo->inscripcion->ciclo_id;
        $alumnoId      = $cargo->inscripcion->alumno_id;
        $conceptoId    = $cargo->concepto_id;
        $plan          = $cargo->asignacion?->plan;

        // 1. Beca activa
        $descuentoBeca  = 0;
        $becaAplicada   = null;
        $beca = BecaAlumno::vigenteHoy()
            ->where('alumno_id', $alumnoId)
            ->where('concepto_id', $conceptoId)
            ->where('ciclo_id', $cicloId)
            ->with('catalogoBeca')
            ->first();

        if ($beca) {
            $descuentoBeca = $beca->calcularDescuento($montoOriginal);
            $becaAplicada  = $beca->catalogoBeca->nombre;
        }

        // 2. Políticas de descuento del plan (pronto pago, etc.)
        $descuentoOtros    = 0;
        $descuentosAplicados = [];
        if ($plan) {
            foreach ($plan->politicasDescuentoActivas as $politica) {
                if ($politica->aplicaHoy()) {
                    $monto = $politica->calcularDescuento($montoOriginal);
                    $descuentoOtros      += $monto;
                    $descuentosAplicados[] = ['nombre' => $politica->nombre, 'monto' => $monto];
                }
            }
        }

        // 3. Recargo por mora
        $recargo       = 0;
        $recargoDetalle = null;
        if ($plan) {
            $politicaRecargo = $plan->politicaRecargoActiva();
            if ($politicaRecargo) {
                $recargo = $politicaRecargo->calcularRecargo($montoOriginal, $cargo->fecha_vencimiento);
                if ($recargo > 0) {
                    $recargoDetalle = ['tipo' => $politicaRecargo->tipo_recargo, 'monto' => $recargo];
                }
            }
        }

        // 4. Saldo ya abonado
        $saldoAbonado = $cargo->saldo_abonado;

        // 5. Total final
        $totalACobrar = max(0, $montoOriginal - $descuentoBeca - $descuentoOtros + $recargo - $saldoAbonado);

        return [
            'cargo_id'            => $cargo->id,
            'periodo'             => $cargo->periodo,
            'monto_original'      => $montoOriginal,
            'descuento_beca'      => $descuentoBeca,
            'beca_aplicada'       => $becaAplicada,
            'descuento_otros'     => $descuentoOtros,
            'descuentos_detalle'  => $descuentosAplicados,
            'recargo'             => $recargo,
            'recargo_detalle'     => $recargoDetalle,
            'saldo_ya_abonado'    => $saldoAbonado,
            'total_a_cobrar'      => round($totalACobrar, 2),
            'estado_real'         => $cargo->estado_real,
            'fecha_vencimiento'   => $cargo->fecha_vencimiento,
        ];
    }

    /**
     * Calcula los periodos y fechas de vencimiento según la periodicidad del plan.
     */
    private function calcularPeriodos(string $fechaInicio, string $fechaFin, string $periodicidad): array
    {
        $inicio  = \Carbon\Carbon::parse($fechaInicio);
        $fin     = \Carbon\Carbon::parse($fechaFin);
        $periodos = [];

        $intervalo = match($periodicidad) {
            'mensual'    => '1 month',
            'bimestral'  => '2 months',
            'semestral'  => '6 months',
            'anual'      => '1 year',
            'unico'      => null,
            default      => '1 month',
        };

        if ($periodicidad === 'unico') {
            return [[
                'periodo'     => $inicio->format('Y-m'),
                'vencimiento' => $inicio->copy()->day(10)->format('Y-m-d'),
            ]];
        }

        $actual = $inicio->copy();
        while ($actual->lte($fin)) {
            $periodos[] = [
                'periodo'     => $actual->format('Y-m'),
                'vencimiento' => $actual->copy()->day(10)->format('Y-m-d'),
            ];
            $actual->add($intervalo);
        }

        return $periodos;
    }
}
