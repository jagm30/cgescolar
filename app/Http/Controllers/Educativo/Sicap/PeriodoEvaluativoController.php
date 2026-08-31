<?php

namespace App\Http\Controllers\Educativo\Sicap;

use App\Enums\EstadoPeriodo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Educativo\StorePeriodoEvaluativoRequest;
use App\Models\CicloEscolar;
use App\Models\PeriodoEvaluativo;
use App\Models\PlanEstudios;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de períodos evaluativos.
 *
 * El administrador puede:
 *  - Generar los períodos de un ciclo automáticamente a partir de los planes activos.
 *  - Abrir/cerrar cada período para habilitar o bloquear la captura de calificaciones.
 *  - Crear o editar períodos manualmente si lo requiere.
 */
class PeriodoEvaluativoController extends Controller
{
    use EducativoRespondsWithJson;

    /** GET /educativo/sicap/periodos */
    public function index(Request $request): View
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        $periodos = PeriodoEvaluativo::query()
            ->with(['plan.nivel', 'plan.escala'])
            ->delCiclo($cicloId)
            ->orderBy('plan_id')
            ->orderBy('numero')
            ->get()
            ->groupBy(fn($p) => $p->plan->nivel->nombre);

        $ciclos = CicloEscolar::query()->orderByDesc('id')->get();
        $planes = PlanEstudios::query()->activo()->with('nivel')->orderBy('nivel_id')->get();

        return view('educativo.sicap.periodos.index', [
            'periodos'       => $periodos,
            'ciclos'         => $ciclos,
            'planes'         => $planes,
            'cicloSeleccionado' => $cicloId,
        ]);
    }

    /** GET /educativo/sicap/periodos/create */
    public function create(): View
    {
        return view('educativo.sicap.periodos.form', [
            'periodo' => new PeriodoEvaluativo(),
            'ciclos'  => CicloEscolar::query()->orderByDesc('id')->get(),
            'planes'  => PlanEstudios::query()->activo()->with('nivel')->orderBy('nivel_id')->get(),
        ]);
    }

    /** POST /educativo/sicap/periodos */
    public function store(StorePeriodoEvaluativoRequest $request): RedirectResponse|JsonResponse
    {
        $periodo = PeriodoEvaluativo::create($request->validated());

        return $this->respuestaExito(
            'Período creado correctamente.',
            $periodo,
            route('educativo.sicap.periodos.index')
        );
    }

    /** GET /educativo/sicap/periodos/{periodo}/edit */
    public function edit(PeriodoEvaluativo $periodo): View
    {
        return view('educativo.sicap.periodos.form', [
            'periodo' => $periodo,
            'ciclos'  => CicloEscolar::query()->orderByDesc('id')->get(),
            'planes'  => PlanEstudios::query()->activo()->with('nivel')->orderBy('nivel_id')->get(),
        ]);
    }

    /** PUT /educativo/sicap/periodos/{periodo} */
    public function update(StorePeriodoEvaluativoRequest $request, PeriodoEvaluativo $periodo): RedirectResponse|JsonResponse
    {
        $periodo->update($request->validated());

        return $this->respuestaExito(
            'Período actualizado.',
            $periodo,
            route('educativo.sicap.periodos.index')
        );
    }

    /** DELETE /educativo/sicap/periodos/{periodo} */
    public function destroy(PeriodoEvaluativo $periodo): RedirectResponse|JsonResponse
    {
        if (! $periodo->estaPendiente()) {
            return $this->respuestaError('Solo se pueden eliminar períodos en estado Pendiente.');
        }

        $periodo->delete();

        return $this->respuestaExito('Período eliminado.', redirect: route('educativo.sicap.periodos.index'));
    }

    // ── Cambios de estado ─────────────────────────────────

    /** PATCH /educativo/sicap/periodos/{periodo}/abrir */
    public function abrir(PeriodoEvaluativo $periodo): JsonResponse
    {
        if (! $periodo->estaPendiente()) {
            return $this->respuestaError('Solo se pueden abrir períodos en estado Pendiente.');
        }

        $periodo->abrir();

        return $this->respuestaExito('Período abierto. Los docentes ya pueden capturar calificaciones.', $periodo);
    }

    /** PATCH /educativo/sicap/periodos/{periodo}/cerrar */
    public function cerrar(PeriodoEvaluativo $periodo): JsonResponse
    {
        if (! $periodo->estaAbierto()) {
            return $this->respuestaError('Solo se pueden cerrar períodos que estén abiertos.');
        }

        $periodo->cerrar();

        return $this->respuestaExito('Período cerrado. La captura de calificaciones ha finalizado.', $periodo);
    }

    // ── Generación automática ─────────────────────────────

    /**
     * POST /educativo/sicap/periodos/generar
     *
     * Genera automáticamente los períodos de todos los planes activos
     * para el ciclo indicado. Omite los que ya existen (idempotente).
     */
    public function generar(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'ciclo_id' => ['required', 'exists:ciclo_escolar,id'],
        ], [
            'ciclo_id.required' => 'Selecciona un ciclo escolar.',
        ]);

        $cicloId  = $request->integer('ciclo_id');
        $generados = 0;

        // Tomar planes activos: genéricos o específicos del ciclo
        $planes = PlanEstudios::query()
            ->activo()
            ->where(fn ($q) => $q->whereNull('ciclo_id')->orWhere('ciclo_id', $cicloId))
            ->get();

        foreach ($planes as $plan) {
            $total  = $plan->tipo_periodo->totalPeriodos();
            $nombre = $plan->tipo_periodo->nombrePeriodo();

            for ($n = 1; $n <= $total; $n++) {
                $existe = PeriodoEvaluativo::query()
                    ->where('ciclo_id', $cicloId)
                    ->where('plan_id', $plan->id)
                    ->where('numero', $n)
                    ->exists();

                if ($existe) {
                    continue;
                }

                PeriodoEvaluativo::create([
                    'ciclo_id' => $cicloId,
                    'plan_id'  => $plan->id,
                    'nombre'   => $this->ordinalEs($n) . ' ' . $nombre,
                    'numero'   => $n,
                    'estado'   => EstadoPeriodo::Pendiente->value,
                ]);

                $generados++;
            }
        }

        $mensaje = $generados > 0
            ? "Se generaron {$generados} período(s) correctamente."
            : 'Todos los períodos ya existían. No se crearon duplicados.';

        return $this->respuestaExito($mensaje, redirect: route('educativo.sicap.periodos.index', ['ciclo_id' => $cicloId]));
    }

    // ── Helpers privados ──────────────────────────────────

    /** Devuelve el ordinal en español: 1 → "1er", 2 → "2do", 3 → "3er". */
    private function ordinalEs(int $n): string
    {
        return match ($n) {
            1 => '1er',
            2 => '2do',
            3 => '3er',
            4 => '4to',
            5 => '5to',
            6 => '6to',
            default => "{$n}°",
        };
    }
}
