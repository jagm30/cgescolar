<?php

namespace App\Http\Controllers\Educativo\Sicap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Educativo\StoreAsignacionDocenteRequest;
use App\Models\AsignacionDocente;
use App\Models\CampoFormativo;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\NivelEscolar;
use App\Models\Personal;
use App\Models\PlanEstudios;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de asignaciones de docentes.
 *
 * Permite al administrador vincular docentes con materias o campos
 * formativos en grupos específicos de un ciclo escolar.
 */
class AsignacionDocenteController extends Controller
{
    use EducativoRespondsWithJson;

    /** GET /educativo/sicap/asignaciones */
    public function index(Request $request): View
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        $asignaciones = AsignacionDocente::query()
            ->with([
                'docente',
                'grupo.grado.nivel',
                'materia',
                'campoFormativo',
                'ciclo',
            ])
            ->delCiclo($cicloId)
            ->orderBy('docente_id')
            ->get()
            ->groupBy(fn($a) => $a->docente->nombre_completo ?? $a->docente->nombre);

        $ciclos = CicloEscolar::query()->orderByDesc('id')->get();

        return view('educativo.sicap.asignaciones.index', [
            'asignaciones'      => $asignaciones,
            'ciclos'            => $ciclos,
            'cicloSeleccionado' => $cicloId,
        ]);
    }

    /** GET /educativo/sicap/asignaciones/create */
    public function create(Request $request): View
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        return view('educativo.sicap.asignaciones.form', [
            'asignacion' => new AsignacionDocente(),
            'docentes'   => Personal::query()->activo()->docentes()->orderBy('nombre')->get(),
            'grupos'     => Grupo::query()->delCiclo($cicloId)->with('grado.nivel')->orderBy('id')->get(),
            'materias'   => Materia::query()->activa()->with('plan.nivel')->orderBy('nombre')->get(),
            'campos'     => CampoFormativo::query()->activo()->with('plan.nivel')->orderBy('nombre')->get(),
            'ciclos'     => CicloEscolar::query()->orderByDesc('id')->get(),
            'cicloId'    => $cicloId,
        ]);
    }

    /** POST /educativo/sicap/asignaciones */
    public function store(StoreAsignacionDocenteRequest $request): RedirectResponse|JsonResponse
    {
        $asignacion = AsignacionDocente::create($request->validated());

        return $this->respuestaExito(
            'Asignación creada correctamente.',
            $asignacion,
            route('educativo.sicap.asignaciones.index', ['ciclo_id' => $asignacion->ciclo_id])
        );
    }

    /** GET /educativo/sicap/asignaciones/{asignacion}/edit */
    public function edit(AsignacionDocente $asignacion): View
    {
        return view('educativo.sicap.asignaciones.form', [
            'asignacion' => $asignacion,
            'docentes'   => Personal::query()->activo()->docentes()->orderBy('nombre')->get(),
            'grupos'     => Grupo::query()->delCiclo($asignacion->ciclo_id)->with('grado.nivel')->orderBy('id')->get(),
            'materias'   => Materia::query()->activa()->with('plan.nivel')->orderBy('nombre')->get(),
            'campos'     => CampoFormativo::query()->activo()->with('plan.nivel')->orderBy('nombre')->get(),
            'ciclos'     => CicloEscolar::query()->orderByDesc('id')->get(),
            'cicloId'    => $asignacion->ciclo_id,
        ]);
    }

    /** PUT /educativo/sicap/asignaciones/{asignacion} */
    public function update(StoreAsignacionDocenteRequest $request, AsignacionDocente $asignacion): RedirectResponse|JsonResponse
    {
        $asignacion->update($request->validated());

        return $this->respuestaExito(
            'Asignación actualizada.',
            $asignacion,
            route('educativo.sicap.asignaciones.index', ['ciclo_id' => $asignacion->ciclo_id])
        );
    }

    /** DELETE /educativo/sicap/asignaciones/{asignacion} */
    public function destroy(AsignacionDocente $asignacion): RedirectResponse|JsonResponse
    {
        $cicloId = $asignacion->ciclo_id;
        $asignacion->delete();

        return $this->respuestaExito(
            'Asignación eliminada.',
            redirect: route('educativo.sicap.asignaciones.index', ['ciclo_id' => $cicloId])
        );
    }

    /**
     * PATCH /educativo/sicap/asignaciones/{asignacion}/toggle
     * Activa o desactiva una asignación sin eliminarla.
     */
    public function toggle(AsignacionDocente $asignacion): JsonResponse
    {
        $asignacion->update(['activa' => ! $asignacion->activa]);

        $estado = $asignacion->activa ? 'activada' : 'desactivada';

        return $this->respuestaExito("Asignación {$estado}.", $asignacion);
    }
}
