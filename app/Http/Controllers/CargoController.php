<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\Inscripcion;
use App\Traits\RespondsWithJson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CargoController extends Controller
{
    use RespondsWithJson;

    /** GET /cargos */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $query = Cargo::with(['inscripcion.alumno', 'inscripcion.grupo.grado', 'concepto', 'detallesPagosVigentes'])
            ->whereHas('inscripcion', fn ($q) => $q->where('ciclo_id', $cicloId))
            ->when($request->filled('alumno_id'), fn ($q) => $q->whereHas(
                'inscripcion', fn ($q) => $q->where('alumno_id', $request->alumno_id)
            ))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $this->aplicarFiltroEstado($q, $request->estado);
            })
            ->when($request->filled('periodo'), fn ($q) => $q->where('periodo', $request->periodo))
            ->orderBy('fecha_vencimiento');

        $cargos = $query->paginate($request->get('per_page', 30));

        if ($request->ajax()) {
            return response()->json($cargos);
        }

        $alumnos = Alumno::query()
            ->whereHas('inscripciones', function ($query) use ($cicloId) {
                $query->where('ciclo_id', $cicloId)->where('activo', true);
            })
            ->orderBy('ap_paterno')
            ->orderBy('ap_materno')
            ->orderBy('nombre')
            ->get();
        $periodos = Cargo::query()
            ->whereHas('inscripcion', fn ($q) => $q->where('ciclo_id', $cicloId))
            ->select('periodo')
            ->distinct()
            ->orderByDesc('periodo')
            ->pluck('periodo');
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();
        $resumenBase = Cargo::query()
            ->whereHas('inscripcion', fn ($q) => $q->where('ciclo_id', $cicloId));

        $resumen = [
            'total' => (clone $resumenBase)->count(),
            'pendientes' => (clone $resumenBase)
                ->where('estado', 'pendiente')
                ->whereDate('fecha_vencimiento', '>=', today())
                ->count(),
            'vencidos' => (clone $resumenBase)
                ->where('estado', 'pendiente')
                ->whereDate('fecha_vencimiento', '<', today())
                ->count(),
            'parciales' => (clone $resumenBase)
                ->where('estado', 'parcial')
                ->count(),
            'pagados' => (clone $resumenBase)
                ->where('estado', 'pagado')
                ->count(),
        ];

        $resumen['parciales_vencidos'] = (clone $resumenBase)
            ->where('estado', 'parcial')
            ->whereDate('fecha_vencimiento', '<', today())
            ->count();

        return view('cargos.index', compact('cargos', 'alumnos', 'periodos', 'ciclos', 'cicloId', 'resumen'));
    }

    /** GET /cargos/{id} */
    public function show(int $id)
    {
        $cargo = Cargo::with([
            'inscripcion.alumno',
            'concepto',
            'asignacion.plan',
            'detallesPagosVigentes.pago',
            'descuentos',
        ])->findOrFail($id);

        $preview = $this->calcularPreviewCobro($cargo);

        if (request()->ajax()) {
            return response()->json([
                'cargo' => array_merge($cargo->toArray(), [
                    'estado_real' => $cargo->estado_real,
                    'saldo_abonado' => $cargo->saldo_abonado,
                ]),
                'preview_cobro' => $preview,
            ]);
        }

        return view('cargos.show', compact('cargo', 'preview'));
    }

    /**
     * POST /cargos/generar
     * Genera cargos para todos los alumnos del ciclo según sus planes.
     */
    public function generar(Request $request)
    {
        $request->validate([
            'ciclo_id' => ['required', 'exists:ciclo_escolar,id'],
        ]);

        $cicloId = $request->ciclo_id;
        $generadoPor = auth()->id();

        DB::beginTransaction();
        try {
            $inscripciones = Inscripcion::with(['grupo.grado.nivel', 'alumno'])
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
                ->get();

            $totalGenerados = 0;
            $yaExistian = 0;

            foreach ($inscripciones as $inscripcion) {
                $nivelId = $inscripcion->grupo->grado->nivel_id;

                $asignacion = AsignacionPlan::with('plan.planPagoConceptos')
                    ->where(function ($q) use ($inscripcion, $nivelId) {
                        $q->where(fn ($q) => $q->where('origen', 'individual')->where('alumno_id', $inscripcion->alumno_id))
                            ->orWhere(fn ($q) => $q->where('origen', 'grupo')->where('grupo_id', $inscripcion->grupo_id))
                            ->orWhere(fn ($q) => $q->where('origen', 'nivel')->where('nivel_id', $nivelId));
                    })
                    ->whereHas('plan', fn ($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
                    ->orderByRaw("FIELD(origen, 'individual', 'grupo', 'nivel')")
                    ->first();

                if (! $asignacion) {
                    continue;
                }

                $plan = $asignacion->plan;
                $periodos = $this->calcularPeriodos($plan->fecha_inicio, $plan->fecha_fin, $plan->periodicidad);

                foreach ($plan->planPagoConceptos as $planConcepto) {
                    foreach ($periodos as $periodo) {
                        $existe = Cargo::where('inscripcion_id', $inscripcion->id)
                            ->where('concepto_id', $planConcepto->concepto_id)
                            ->where('periodo', $periodo['periodo'])
                            ->exists();

                        if ($existe) {
                            $yaExistian++;

                            continue;
                        }

                        Cargo::create([
                            'inscripcion_id' => $inscripcion->id,
                            'concepto_id' => $planConcepto->concepto_id,
                            'asignacion_id' => $asignacion->id,
                            'generado_por' => $generadoPor,
                            'monto_original' => $planConcepto->monto,
                            'fecha_vencimiento' => $periodo['vencimiento'],
                            'estado' => 'pendiente',
                            'periodo' => $periodo['periodo'],
                        ]);
                        $totalGenerados++;
                    }
                }
            }

            Auditoria::registrar('cargo', 0, 'insert', null, [
                'ciclo_id' => $cicloId, 'total_generados' => $totalGenerados,
            ]);

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'cargos.index',
                jsonData: ['total_generados' => $totalGenerados, 'ya_existian' => $yaExistian],
                mensaje: "Generación completada. {$totalGenerados} cargos nuevos."
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->respuestaError('Error al generar cargos: '.$e->getMessage());
        }
    }

    /** GET /cargos/{id}/preview — solo AJAX */
    public function preview(int $id)
    {
        $cargo = Cargo::with([
            'inscripcion', 'concepto', 'asignacion.plan', 'detallesPagosVigentes',
        ])->findOrFail($id);

        return response()->json($this->calcularPreviewCobro($cargo));
    }

    // ── Cálculo de preview (reutilizado por PagoController) ──

    private function aplicarFiltroEstado(Builder $query, string $estado): void
    {
        match ($estado) {
            'pendiente' => $query->where('estado', 'pendiente')
                ->whereDate('fecha_vencimiento', '>=', today()),
            'vencido' => $query->where('estado', 'pendiente')
                ->whereDate('fecha_vencimiento', '<', today()),
            'parcial_vencido' => $query->where('estado', 'parcial')
                ->whereDate('fecha_vencimiento', '<', today()),
            'parcial' => $query->where('estado', 'parcial')
                ->whereDate('fecha_vencimiento', '>=', today()),
            default => $query->where('estado', $estado),
        };
    }

    public function calcularPreviewCobro(Cargo $cargo): array
    {
        $montoOriginal = (float) $cargo->monto_original;
        $cicloId = $cargo->inscripcion->ciclo_id;
        $alumnoId = $cargo->inscripcion->alumno_id;
        $conceptoId = $cargo->concepto_id;
        $plan = $cargo->asignacion?->plan;

        // Beca vigente
        $descuentoBeca = 0;
        $becaAplicada = null;
        $beca = BecaAlumno::vigenteHoy()
            ->where('alumno_id', $alumnoId)
            ->where('concepto_id', $conceptoId)
            ->where('ciclo_id', $cicloId)
            ->with('catalogoBeca')
            ->first();

        if ($beca) {
            $descuentoBeca = $beca->calcularDescuento($montoOriginal);
            $becaAplicada = $beca->catalogoBeca->nombre;
        }

        // Descuentos por política del plan
        $descuentoOtros = 0;
        $descuentosDetalle = [];
        if ($plan) {
            foreach ($plan->politicasDescuentoActivas as $politica) {
                if ($politica->aplicaHoy()) {
                    $monto = $politica->calcularDescuento($montoOriginal);
                    $descuentoOtros += $monto;
                    $descuentosDetalle[] = ['nombre' => $politica->nombre, 'monto' => $monto];
                }
            }
        }

        // Recargo por mora
        $recargo = 0;
        $recargoDetalle = null;
        if ($plan && $plan->politicaRecargoActiva) {
            $pol = $plan->politicaRecargoActiva;
            if ($pol) {
                $recargo = $pol->calcularRecargo($montoOriginal, $cargo->fecha_vencimiento);
                if ($recargo > 0) {
                    $recargoDetalle = ['tipo' => $pol->tipo_recargo, 'monto' => $recargo];
                }
            }
        }

        $descuentoManual = round((float) $cargo->descuentos()->sum('monto_aplicado'), 2);
        $totalACobrar = max(
            0,
            $montoOriginal - $descuentoBeca - $descuentoOtros - $descuentoManual + $recargo - $cargo->saldo_abonado
        );

        return [
            'cargo_id' => $cargo->id,
            'periodo' => $cargo->periodo,
            'monto_original' => $montoOriginal,
            'descuento_beca' => round($descuentoBeca, 2),
            'beca_aplicada' => $becaAplicada,
            'descuento_otros' => round($descuentoOtros, 2),
            'descuento_manual' => $descuentoManual,
            'descuentos_detalle' => $descuentosDetalle,
            'recargo' => round($recargo, 2),
            'recargo_detalle' => $recargoDetalle,
            'saldo_ya_abonado' => round($cargo->saldo_abonado, 2),
            'total_a_cobrar' => round($totalACobrar, 2),
            'estado_real' => $cargo->estado_real,
            'fecha_vencimiento' => $cargo->fecha_vencimiento,
        ];
    }

    // ── Helper privado ───────────────────────────────────

    private function calcularPeriodos(string $fechaInicio, string $fechaFin, string $periodicidad): array
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        $periodos = [];

        if ($periodicidad === 'unico') {
            return [['periodo' => $inicio->format('Y-m'), 'vencimiento' => $inicio->copy()->day(10)->format('Y-m-d')]];
        }

        $intervalo = match ($periodicidad) {
            'mensual' => '1 month',
            'bimestral' => '2 months',
            'semestral' => '6 months',
            'anual' => '1 year',
            default => '1 month',
        };

        $actual = $inicio->copy();
        while ($actual->lte($fin)) {
            $periodos[] = ['periodo' => $actual->format('Y-m'), 'vencimiento' => $actual->copy()->day(10)->format('Y-m-d')];
            $actual->add($intervalo);
        }

        return $periodos;
    }
}
