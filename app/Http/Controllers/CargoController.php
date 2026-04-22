<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Traits\RespondsWithJson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    use RespondsWithJson;

    /** GET /cargos */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');
        $perPage = (int) $request->get('per_page', 30);

        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
        }

        $query = Cargo::with(['inscripcion.alumno', 'inscripcion.grupo.grado', 'concepto', 'detallesPagosVigentes'])
            ->whereHas('inscripcion', fn ($q) => $q->where('ciclo_id', $cicloId))
            ->when($request->filled('alumno_id'), fn ($q) => $q->whereHas(
                'inscripcion',
                fn ($q) => $q->where('alumno_id', $request->alumno_id)
            ))
            ->when($request->filled('estado'), function ($q) use ($request) {
                $this->aplicarFiltroEstado($q, $request->estado);
            })
            ->when($request->filled('periodo'), fn ($q) => $q->where('periodo', $request->periodo))
            ->orderBy('fecha_vencimiento');

        $cargos = $query->paginate($perPage);

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

        return view('cargos.index', compact('cargos', 'alumnos', 'periodos', 'ciclos', 'cicloId', 'resumen', 'perPage'));
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

    /** GET /cargos/{id}/preview */
    public function preview(int $id)
    {
        $cargo = Cargo::with([
            'inscripcion',
            'concepto',
            'asignacion.plan',
            'detallesPagosVigentes',
        ])->findOrFail($id);

        return response()->json($this->calcularPreviewCobro($cargo));
    }

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
}
