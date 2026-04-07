<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\Grado;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    use RespondsWithJson;

    /** GET /grupos */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
        ?? CicloEscolar::activo()->value('id');

        // Usar ciclo_id del request si viene (para el selector del wizard)
        if ($request->filled('ciclo_id')) {
            $cicloId = $request->ciclo_id;
        }

        $grupos = Grupo::with(['grado.nivel'])
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            // ── ESTE FILTRO FALTABA ──────────────────────────────
            ->when($request->filled('nivel_id'), fn($q) => $q->whereHas(
                'grado', fn($q) => $q->where('nivel_id', $request->nivel_id)
            ))
            // ────────────────────────────────────────────────────
            ->when($request->filled('grado_id'), fn($q) => $q->where('grado_id', $request->grado_id))
            ->when($request->filled('activo'),   fn($q) => $q->where('activo', $request->boolean('activo')))
            ->withCount(['inscripciones as alumnos_inscritos' => fn($q) => $q->where('activo', true)])
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get()
            ->map(fn($g) => array_merge($g->toArray(), [
                'disponibles' => $g->cupo_maximo ? max(0, $g->cupo_maximo - $g->alumnos_inscritos) : null,
            ]));

        if ($request->ajax()) {
            return response()->json($grupos);
        }

        $niveles = NivelEscolar::activo()->get();
        $grados  = Grado::with('nivel')->orderBy('nivel_id')->orderBy('numero')->get();
        $ciclo   = CicloEscolar::find($cicloId);

        return view('grupos.index', compact('grupos', 'niveles', 'grados', 'ciclo'));
    }

    /** GET /grupos/{id} */
    public function show(int $id)
    {
        $grupo = Grupo::with([
            'grado.nivel', 'ciclo',
            'inscripciones' => fn($q) => $q->where('activo', true)
                ->with(['alumno' => fn($q) => $q->select('id', 'matricula', 'nombre', 'ap_paterno', 'ap_materno', 'estado')]),
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($grupo);
        }

        return view('grupos.show', compact('grupo'));
    }

    /** GET /grupos/create */
    public function create()
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $grados = Grado::with('nivel')->orderBy('nivel_id')->orderBy('numero')->get();
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();

        return view('grupos.create', compact('grados', 'ciclos', 'cicloId'));
    }

    /** POST /grupos */
    public function store(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $data = $request->validate([
            'grado_id'    => ['required', 'exists:grado,id'],
            'nombre'      => ['required', 'string', 'max:10'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1', 'max:100'],
            'docente'     => ['nullable', 'string', 'max:200'],
            'ciclo_id'    => ['nullable', 'exists:ciclo_escolar,id'],
        ], [
            'grado_id.required' => 'Debe seleccionar el grado.',
            'nombre.required'   => 'El nombre del grupo es obligatorio.',
        ]);

        $data['ciclo_id'] = $data['ciclo_id'] ?? $cicloId;
        $data['activo']   = true;
        $data['nombre']   = strtoupper($data['nombre']);

        $existe = Grupo::where('ciclo_id', $data['ciclo_id'])
            ->where('grado_id', $data['grado_id'])
            ->where('nombre', $data['nombre'])
            ->exists();

        if ($existe) {
            return $this->respuestaError(
                "Ya existe el grupo '{$data['nombre']}' para ese grado en este ciclo."
            );
        }

        $grupo = Grupo::create($data);

        Auditoria::registrar('grupo', $grupo->id, 'insert', null, $grupo->toArray());

        return $this->respuestaExito(
            redirectRoute: 'grupos.index',
            jsonData: ['grupo' => $grupo->load(['grado.nivel', 'ciclo'])],
            mensaje: "Grupo '{$grupo->nombre}' creado correctamente.",
            jsonStatus: 201
        );
    }

    /** GET /grupos/{id}/edit */
    public function edit(int $id)
    {
        $grupo  = Grupo::with(['grado.nivel'])->findOrFail($id);
        $grados = Grado::with('nivel')->orderBy('nivel_id')->orderBy('numero')->get();

        if (request()->ajax()) {
            return response()->json($grupo);
        }

        return view('grupos.edit', compact('grupo', 'grados'));
    }

    /** PUT /grupos/{id} */
    public function update(Request $request, int $id)
    {
        $grupo    = Grupo::findOrFail($id);
        $anterior = $grupo->toArray();

        $data = $request->validate([
            'nombre'      => ['sometimes', 'required', 'string', 'max:10'],
            'cupo_maximo' => ['nullable', 'integer', 'min:1', 'max:100'],
            'docente'     => ['nullable', 'string', 'max:200'],
            'activo'      => ['boolean'],
        ]);

        if (isset($data['nombre'])) {
            $data['nombre'] = strtoupper($data['nombre']);
            $duplicado = Grupo::where('ciclo_id', $grupo->ciclo_id)
                ->where('grado_id', $grupo->grado_id)
                ->where('nombre', $data['nombre'])
                ->where('id', '!=', $id)
                ->exists();

            if ($duplicado) {
                return $this->respuestaError(
                    "Ya existe el grupo '{$data['nombre']}' para ese grado en este ciclo."
                );
            }
        }

        if (isset($data['cupo_maximo'])) {
            $inscritos = $grupo->inscripciones()->where('activo', true)->count();
            if ($data['cupo_maximo'] < $inscritos) {
                return $this->respuestaError(
                    "El cupo ({$data['cupo_maximo']}) no puede ser menor a los alumnos inscritos ({$inscritos})."
                );
            }
        }

        $grupo->update($data);

        Auditoria::registrar('grupo', $grupo->id, 'update', $anterior, $grupo->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'grupos.index',
            jsonData: ['grupo' => $grupo->fresh()->load(['grado.nivel'])],
            mensaje: "Grupo '{$grupo->nombre}' actualizado correctamente."
        );
    }

    /** DELETE /grupos/{id} */
    public function destroy(int $id)
    {
        $grupo = Grupo::findOrFail($id);

        $inscritos = $grupo->inscripciones()->where('activo', true)->count();
        if ($inscritos > 0) {
            return $this->respuestaError(
                "No se puede desactivar el grupo '{$grupo->nombre}' porque tiene {$inscritos} alumno(s) inscrito(s)."
            );
        }

        $grupo->update(['activo' => false]);

        Auditoria::registrar('grupo', $grupo->id, 'update', ['activo' => true], ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'grupos.index',
            mensaje: "Grupo '{$grupo->nombre}' desactivado correctamente."
        );
    }

    /** POST /grupos/{id}/cambiar-alumno */
    public function cambiarAlumno(Request $request, int $id)
    {
        $data = $request->validate([
            'alumno_id'        => ['required', 'exists:alumno,id'],
            'grupo_destino_id' => ['required', 'exists:grupo,id'],
        ]);

        $grupoOrigen  = Grupo::findOrFail($id);
        $grupoDestino = Grupo::findOrFail($data['grupo_destino_id']);

        if ($grupoOrigen->ciclo_id !== $grupoDestino->ciclo_id) {
            return $this->respuestaError('No se puede mover un alumno entre grupos de diferentes ciclos.');
        }

        if ($grupoDestino->cupo_maximo) {
            $inscritosDestino = $grupoDestino->inscripciones()->where('activo', true)->count();
            if ($inscritosDestino >= $grupoDestino->cupo_maximo) {
                return $this->respuestaError(
                    "El grupo '{$grupoDestino->nombre}' ha alcanzado su cupo máximo."
                );
            }
        }

        $inscripcion = Inscripcion::where('alumno_id', $data['alumno_id'])
            ->where('ciclo_id', $grupoOrigen->ciclo_id)
            ->where('activo', true)
            ->first();

        if (!$inscripcion) {
            return $this->respuestaError('El alumno no tiene inscripción activa en este ciclo.');
        }

        $anterior = $inscripcion->toArray();
        $inscripcion->update(['grupo_id' => $grupoDestino->id]);

        Auditoria::registrar('inscripcion', $inscripcion->id, 'update', $anterior, $inscripcion->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'grupos.index',
            jsonData: ['inscripcion' => $inscripcion->fresh()->load(['alumno', 'grupo.grado.nivel'])],
            mensaje: "Alumno movido al grupo '{$grupoDestino->nombre}' correctamente."
        );
    }
}
