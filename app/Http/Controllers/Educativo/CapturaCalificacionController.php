<?php

namespace App\Http\Controllers\Educativo;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\CriterioEvaluacion;
use App\Models\Inscripcion;
use App\Models\PeriodoEvaluativo;
use App\Models\Personal;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Captura de calificaciones.
 *
 * Flujo:
 *  1. index()  — el docente ve sus asignaciones activas del ciclo actual.
 *  2. show()   — cuadrícula alumnos × períodos para una asignación.
 *  3. store()  — guarda (upsert) el lote de calificaciones vía AJAX.
 *
 * Un administrador puede acceder a cualquier asignación para revisar o capturar.
 */
class CapturaCalificacionController extends Controller
{
    use EducativoRespondsWithJson;

    /** GET /educativo/captura */
    public function index(Request $request): View
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        $asignaciones = $this->asignacionesParaUsuario($cicloId)
            ->with([
                'grupo.grado.nivel',
                'materia',
                'campoFormativo',
            ])
            ->get()
            ->groupBy(fn ($a) => $a->grupo->nombre_completo ?? $a->grupo->nombre);

        $ciclos = CicloEscolar::query()->orderByDesc('id')->get();

        return view('educativo.captura.index', [
            'asignaciones'      => $asignaciones,
            'ciclos'            => $ciclos,
            'cicloSeleccionado' => $cicloId,
        ]);
    }

    /**
     * GET /educativo/captura/{asignacion}
     *
     * Muestra la cuadrícula de captura: filas = alumnos inscritos en el grupo,
     * columnas = períodos del plan (todos; abiertos = editables).
     */
    public function show(AsignacionDocente $asignacion): View
    {
        $this->autorizarAcceso($asignacion);

        // Períodos del mismo plan y ciclo que la asignación, ordenados por número
        $planId   = $this->planIdDelGrupo($asignacion);
        $periodos = PeriodoEvaluativo::query()
            ->where('ciclo_id', $asignacion->ciclo_id)
            ->where('plan_id', $planId)
            ->orderBy('numero')
            ->get();

        // Alumnos activos inscritos en el grupo, ordenados alfabéticamente
        $inscripciones = Inscripcion::query()
            ->where('grupo_id', $asignacion->grupo_id)
            ->where('activo', true)
            ->with('alumno')
            ->get()
            ->sortBy(fn ($i) => $i->alumno->ap_paterno . ' ' . $i->alumno->ap_materno . ' ' . $i->alumno->nombre);

        // Calificaciones ya registradas: indexadas por [periodo_id][alumno_id]
        $calificacionesExistentes = Calificacion::query()
            ->deAsignacion($asignacion->id)
            ->whereIn('periodo_id', $periodos->pluck('id'))
            ->get()
            ->groupBy('periodo_id')
            ->map(fn ($grupo) => $grupo->keyBy('alumno_id'));

        // Criterios de la escala (solo si la escala es literal)
        $escala    = $this->escalaDelGrupo($asignacion);
        $criterios = $escala?->esLiteral()
            ? $escala->criterios()->orderBy('orden')->get()
            : collect();

        return view('educativo.captura.show', [
            'asignacion'              => $asignacion->load('grupo.grado.nivel', 'materia', 'campoFormativo'),
            'periodos'                => $periodos,
            'inscripciones'           => $inscripciones,
            'calificacionesExistentes' => $calificacionesExistentes,
            'criterios'               => $criterios,
            'escala'                  => $escala,
        ]);
    }

    /**
     * POST /educativo/captura/{asignacion}
     *
     * Recibe un array de calificaciones y hace upsert en bloque.
     * Estructura esperada:
     *   calificaciones[periodo_id][alumno_id] = valor (número, criterio_id o texto)
     */
    public function store(Request $request, AsignacionDocente $asignacion): JsonResponse
    {
        $this->autorizarAcceso($asignacion);

        $request->validate([
            'calificaciones'           => ['required', 'array'],
            'calificaciones.*'         => ['array'],
            'calificaciones.*.*'       => ['nullable', 'string', 'max:2000'],
        ]);

        $escala   = $this->escalaDelGrupo($asignacion);
        $esTexto  = $asignacion->esDePreescolar();
        $esLiteral = $escala?->esLiteral() ?? false;

        DB::transaction(function () use ($request, $asignacion, $esTexto, $esLiteral) {
            $ahora       = now();
            $usuarioId   = auth()->id();

            foreach ($request->input('calificaciones', []) as $periodoId => $alumnos) {
                // Solo se permite capturar en períodos abiertos
                $periodo = PeriodoEvaluativo::find((int) $periodoId);
                if (! $periodo || ! $periodo->estaAbierto()) {
                    continue;
                }

                foreach ($alumnos as $alumnoId => $valor) {
                    $registro = $this->construirRegistro(
                        (int) $alumnoId,
                        (int) $periodoId,
                        $asignacion->id,
                        $valor,
                        $esTexto,
                        $esLiteral,
                        $usuarioId,
                        $ahora,
                    );

                    Calificacion::updateOrCreate(
                        [
                            'alumno_id'     => $registro['alumno_id'],
                            'periodo_id'    => $registro['periodo_id'],
                            'asignacion_id' => $registro['asignacion_id'],
                        ],
                        $registro,
                    );
                }
            }
        });

        return $this->respuestaExito('Calificaciones guardadas correctamente.');
    }

    // ── Helpers privados ──────────────────────────────────

    /**
     * Devuelve el query base de asignaciones.
     * Si el usuario es docente, filtra solo sus asignaciones.
     * Si es administrador, devuelve todas las del ciclo.
     */
    private function asignacionesParaUsuario(int $cicloId)
    {
        $query = AsignacionDocente::query()
            ->activa()
            ->delCiclo($cicloId);

        if (auth()->user()->esDocente()) {
            $personal = Personal::where('usuario_id', auth()->id())->first();
            if ($personal) {
                $query->delDocente($personal->id);
            }
        }

        return $query;
    }

    /**
     * Verifica que el usuario tenga permiso para ver o capturar en esta asignación.
     * Un docente solo puede acceder a sus propias asignaciones.
     */
    private function autorizarAcceso(AsignacionDocente $asignacion): void
    {
        if (auth()->user()->esAdministrador()) {
            return;
        }

        $personal = Personal::where('usuario_id', auth()->id())->first();
        abort_unless(
            $personal && $asignacion->docente_id === $personal->id,
            403,
            'No tienes acceso a esta asignación.'
        );
    }

    /**
     * Resuelve el plan de estudios del grupo de la asignación
     * para encontrar sus períodos y escala correctos.
     */
    private function planIdDelGrupo(AsignacionDocente $asignacion): ?int
    {
        return $asignacion->materia?->plan_id
            ?? $asignacion->campoFormativo?->plan_id;
    }

    /** Devuelve la EscalaEvaluacion del plan asociado a la asignación. */
    private function escalaDelGrupo(AsignacionDocente $asignacion)
    {
        $plan = $asignacion->materia?->plan
            ?? $asignacion->campoFormativo?->plan;

        return $plan?->escala;
    }

    /**
     * Construye el array listo para upsert según el tipo de valor esperado.
     *
     * @param  mixed  $valor  Puede ser numérico, criterio_id o texto descriptivo.
     */
    private function construirRegistro(
        int $alumnoId,
        int $periodoId,
        int $asignacionId,
        mixed $valor,
        bool $esTexto,
        bool $esLiteral,
        int $usuarioId,
        \Carbon\Carbon $ahora,
    ): array {
        $base = [
            'alumno_id'          => $alumnoId,
            'periodo_id'         => $periodoId,
            'asignacion_id'      => $asignacionId,
            'valor_numerico'     => null,
            'criterio_id'        => null,
            'texto_descriptivo'  => null,
            'capturado_por'      => $usuarioId,
            'fecha_captura'      => $ahora,
        ];

        if ($esTexto) {
            $base['texto_descriptivo'] = $valor ?: null;
        } elseif ($esLiteral) {
            $base['criterio_id'] = is_numeric($valor) ? (int) $valor : null;
        } else {
            $base['valor_numerico'] = is_numeric($valor) ? (float) $valor : null;
        }

        return $base;
    }
}
