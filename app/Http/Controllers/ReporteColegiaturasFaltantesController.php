<?php

namespace App\Http\Controllers;

use App\Models\CicloEscolar;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteColegiaturasFaltantesController extends Controller
{
    /** GET /reportes/colegiaturas-faltantes — Alumnos activos sin cargo de colegiatura del mes seleccionado */
    public function index(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();
        $ciclo = $ciclos->firstWhere('id', $cicloId);

        $periodos = $ciclo ? $this->periodosDelCiclo($ciclo) : collect();
        $periodo = $this->resolverPeriodoSeleccionado($request->get('periodo'), $periodos);

        $alumnos = $periodo
            ? $this->buscarAlumnosSinColegiatura($cicloId, $periodo)
            : collect();

        if ($request->ajax()) {
            return response()->json(['alumnos' => $alumnos, 'periodo' => $periodo]);
        }

        return view('reportes.colegiaturas-faltantes', [
            'alumnos' => $alumnos,
            'ciclos' => $ciclos,
            'cicloId' => $cicloId,
            'periodos' => $periodos,
            'periodo' => $periodo,
        ]);
    }

    /** Alumnos con inscripción activa en el ciclo que no tienen cargo de colegiatura para el periodo dado. */
    private function buscarAlumnosSinColegiatura(?int $cicloId, string $periodo)
    {
        return Inscripcion::query()
            ->with(['alumno', 'grupo.grado.nivel'])
            ->where('ciclo_id', $cicloId)
            ->activa()
            ->whereDoesntHave('cargos', function ($q) use ($periodo) {
                $q->where('periodo', $periodo)
                    ->whereHas('concepto', fn ($c) => $c->colegiatura());
            })
            ->get()
            ->map(fn (Inscripcion $ins) => [
                'alumno' => $ins->alumno,
                'inscripcion_id' => $ins->id,
                'grupo' => $ins->grupo,
                'nivel' => $ins->grupo?->grado?->nivel,
            ])
            ->sortBy(fn ($a) => $a['alumno']->ap_paterno.$a['alumno']->ap_materno.$a['alumno']->nombre)
            ->values();
    }

    /** Lista los periodos 'YYYY-MM' (con etiqueta en español) que abarca el rango del ciclo. */
    private function periodosDelCiclo(CicloEscolar $ciclo): Collection
    {
        $periodos = collect();
        $cursor = $ciclo->fecha_inicio->copy()->startOfMonth();
        $fin = $ciclo->fecha_fin->copy()->startOfMonth();

        while ($cursor->lte($fin)) {
            $periodos->push([
                'valor' => $cursor->format('Y-m'),
                'label' => ucfirst($cursor->translatedFormat('F Y')),
            ]);
            $cursor->addMonth();
        }

        return $periodos;
    }

    /** Determina el periodo a consultar: el solicitado si es válido, si no el mes actual, si no el primero del ciclo. */
    private function resolverPeriodoSeleccionado(?string $solicitado, Collection $periodos): ?string
    {
        if ($solicitado && $periodos->contains('valor', $solicitado)) {
            return $solicitado;
        }

        return $periodos->firstWhere('valor', now()->format('Y-m'))['valor']
            ?? $periodos->first()['valor']
            ?? null;
    }
}
