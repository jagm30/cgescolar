<?php

namespace App\Services\Educativo;

use App\Enums\TipoMateria;
use App\Models\Alumno;
use App\Models\AsignacionDocente;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Inscripcion;
use App\Models\PeriodoEvaluativo;
use App\Models\PlanEstudios;
use Illuminate\Support\Collection;

/**
 * Lógica de promedios y armado de boleta.
 *
 * Construye el resumen de calificaciones de un alumno en un ciclo escolar,
 * diferenciando entre materias SEP, institucionales y campos formativos
 * (Preescolar).
 */
class PromedioService
{
    /**
     * Arma el resumen completo para generar la boleta de un alumno.
     *
     * @return array{
     *   alumno: Alumno,
     *   ciclo: CicloEscolar,
     *   grupo: \App\Models\Grupo|null,
     *   plan: PlanEstudios|null,
     *   periodos: Collection,
     *   esPreescolar: bool,
     *   filas: Collection,
     *   promedio_sep: float|null,
     *   promedio_institucional: float|null,
     * }
     */
    public function resumenBoleta(Alumno $alumno, int $cicloId): array
    {
        $ciclo = CicloEscolar::find($cicloId);

        // Inscripción activa del alumno en este ciclo
        $inscripcion = Inscripcion::query()
            ->where('alumno_id', $alumno->id)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->with('grupo.grado.nivel')
            ->first();

        $grupo = $inscripcion?->grupo;

        // Plan de estudios del nivel del grupo
        $plan = $grupo
            ? $this->resolverPlan($grupo->grado->nivel_id, $cicloId)
            : null;

        // Períodos evaluativos del plan y ciclo, ordenados
        $periodos = $plan
            ? PeriodoEvaluativo::query()
                ->where('ciclo_id', $cicloId)
                ->where('plan_id', $plan->id)
                ->orderBy('numero')
                ->get()
            : collect();

        // Asignaciones activas del grupo en el ciclo, con relaciones necesarias
        $asignaciones = $grupo
            ? AsignacionDocente::query()
                ->activa()
                ->delCiclo($cicloId)
                ->where('grupo_id', $grupo->id)
                ->with(['materia', 'campoFormativo', 'docente'])
                ->get()
            : collect();

        $esPreescolar = $asignaciones->first()?->esDePreescolar() ?? false;

        // Calificaciones del alumno para todas sus asignaciones en el ciclo
        $todasLasCalificaciones = Calificacion::query()
            ->where('alumno_id', $alumno->id)
            ->whereIn('asignacion_id', $asignaciones->pluck('id'))
            ->whereIn('periodo_id', $periodos->pluck('id'))
            ->with('criterio')
            ->get()
            ->groupBy('asignacion_id')
            ->map(fn ($grupo) => $grupo->keyBy('periodo_id'));

        // Construir filas para la boleta
        $filas = $asignaciones->map(function (AsignacionDocente $asignacion) use (
            $periodos,
            $todasLasCalificaciones,
            $esPreescolar,
        ) {
            $calsAsignacion = $todasLasCalificaciones[$asignacion->id] ?? collect();

            $calPorPeriodo = $periodos->mapWithKeys(
                fn ($p) => [$p->id => $calsAsignacion[$p->id] ?? null]
            );

            return [
                'asignacion'    => $asignacion,
                'nombre'        => $asignacion->etiquetaContenido(),
                'tipo'          => $asignacion->materia?->tipo ?? null,
                'calificaciones' => $calPorPeriodo,
                'promedio'      => $esPreescolar
                    ? null
                    : $this->calcularPromedio($calsAsignacion, $asignacion),
            ];
        });

        return [
            'alumno'                => $alumno,
            'ciclo'                 => $ciclo,
            'grupo'                 => $grupo,
            'plan'                  => $plan,
            'periodos'              => $periodos,
            'esPreescolar'          => $esPreescolar,
            'filas'                 => $filas,
            'promedio_sep'          => $esPreescolar ? null : $this->promedioTipo($filas, TipoMateria::Sep),
            'promedio_institucional' => $esPreescolar ? null : $this->promedioTipo($filas, TipoMateria::Institucional),
        ];
    }

    // ── Helpers privados ──────────────────────────────────

    /**
     * Resuelve el plan de estudios vigente para un nivel en un ciclo.
     * Prioriza el plan específico del ciclo sobre el genérico.
     */
    private function resolverPlan(int $nivelId, int $cicloId): ?PlanEstudios
    {
        return PlanEstudios::query()
            ->activo()
            ->where('nivel_id', $nivelId)
            ->where(fn ($q) => $q->where('ciclo_id', $cicloId)->orWhereNull('ciclo_id'))
            ->orderByRaw('CASE WHEN ciclo_id = ? THEN 0 ELSE 1 END', [$cicloId])
            ->first();
    }

    /**
     * Calcula el promedio de una asignación para un alumno.
     *
     * - Escala numérica: media aritmética de valor_numerico.
     * - Escala literal:  media aritmética de criterio.valor_numerico.
     * - Campo formativo: null (descriptivo, sin promedio).
     */
    private function calcularPromedio(Collection $calificaciones, AsignacionDocente $asignacion): ?float
    {
        if ($calificaciones->isEmpty()) {
            return null;
        }

        $valores = $calificaciones->map(function (Calificacion $cal) {
            if (! is_null($cal->valor_numerico)) {
                return (float) $cal->valor_numerico;
            }

            if ($cal->criterio_id && $cal->criterio) {
                return (float) $cal->criterio->valor_numerico;
            }

            return null;
        })->filter(fn ($v) => ! is_null($v));

        if ($valores->isEmpty()) {
            return null;
        }

        return round($valores->avg(), 1);
    }

    /**
     * Promedio de un tipo de materia (SEP o Institucional) sobre las filas de la boleta.
     * Solo considera filas con promedio calculado.
     */
    private function promedioTipo(Collection $filas, TipoMateria $tipo): ?float
    {
        $promedios = $filas
            ->filter(fn ($fila) => $fila['tipo'] === $tipo)
            ->pluck('promedio')
            ->filter(fn ($v) => ! is_null($v));

        if ($promedios->isEmpty()) {
            return null;
        }

        return round($promedios->avg(), 1);
    }
}
