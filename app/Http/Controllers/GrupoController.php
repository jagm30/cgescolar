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

    if ($request->filled('ciclo_id')) {
        $cicloId = $request->ciclo_id;
    }

    $query = Grupo::with(['grado.nivel'])->where('ciclo_id', $cicloId);

    // ── LÓGICA DE ESTATUS (Usando tu Scope o Manual) ──
    if ($request->estatus == 'inactivos') {
        $query->where('activo', false);
    } elseif ($request->estatus == 'todos') {
        // No filtramos por activo para que salgan todos
    } else {
        // Por defecto (o si elige activos)
        $query->activo(); 
    }

    $gruposPaginados = $query
        ->when($request->filled('nivel_id'), fn($q) => $q->whereHas(
            'grado', fn($q) => $q->where('nivel_id', $request->nivel_id)
        ))
        ->when($request->filled('grado_id'), fn($q) => $q->where('grado_id', $request->grado_id))
        ->withCount(['inscripciones as alumnos_inscritos' => fn($q) => $q->where('activo', true)])
        ->orderBy('grado_id')
        ->orderBy('nombre')
        ->paginate(10);

    // Transformación para los disponibles
    $gruposPaginados->getCollection()->transform(fn($g) => array_merge($g->toArray(), [
        'disponibles' => $g->cupo_maximo ? max(0, $g->cupo_maximo - $g->alumnos_inscritos) : null,
    ]));

    $grupos = $gruposPaginados->appends($request->except('page'));
    
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
    $cicloId = auth()->user()->ciclo_selected_id 
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
        $msj = "Ya existe el grupo '{$data['nombre']}' para ese grado en este ciclo.";
        return $request->ajax() 
            ? response()->json(['status' => 'error', 'mensaje' => $msj], 422)
            : back()->withErrors(['nombre' => $msj])->withInput();
    }

    $grupo = Grupo::create($data);

    Auditoria::registrar('grupo', $grupo->id, 'insert', null, $grupo->toArray());

    // ── CONFIGURACIÓN PARA EL TOAST DE HTML ──
    $mensajeExito = "Grupo '{$grupo->nombre}' creado correctamente.";

    if ($request->ajax()) {
        // MANDAMOS EL MENSAJE A LA SESIÓN MANUALMENTE
        // Esto es lo que hará que tu @if(session()->has('success')) lo vea al recargar
        session()->flash('success', $mensajeExito);

        return $this->respuestaExito(
            redirectRoute: 'grupos.index',
            jsonData: ['grupo' => $grupo->load(['grado.nivel', 'ciclo'])],
            mensaje: $mensajeExito,
            jsonStatus: 201
        );
    }

    return redirect()->route('grupos.index')->with('success', $mensajeExito);
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

    // 1. Validación de nombre duplicado
    if (isset($data['nombre'])) {
        $data['nombre'] = strtoupper($data['nombre']);
        $duplicado = Grupo::where('ciclo_id', $grupo->ciclo_id)
            ->where('grado_id', $grupo->grado_id)
            ->where('nombre', $data['nombre'])
            ->where('id', '!=', $id) // Con esto ya no se encuentra a sí mismo
            ->exists();

        if ($duplicado) {
            return $this->respuestaError(
                "Ya existe el grupo '{$data['nombre']}' para ese grado en este ciclo."
            );
        }
    }

    // 2. Validación de cupo vs inscritos
    if (isset($data['cupo_maximo'])) {
        $inscritos = $grupo->inscripciones()->where('activo', true)->count();
        if ($data['cupo_maximo'] < $inscritos) {
            return $this->respuestaError(
                "El cupo ({$data['cupo_maximo']}) no puede ser menor a los alumnos inscritos ({$inscritos})."
            );
        }
    }

    $grupo->update($data);

    // 3. Registro de auditoría
    Auditoria::registrar('grupo', $grupo->id, 'update', $anterior, $grupo->fresh()->toArray());

    return $this->respuestaExito(
        redirectRoute: 'grupos.index',
        jsonData: ['grupo' => $grupo->fresh()->load(['grado.nivel'])],
        mensaje: "Grupo '{$grupo->nombre}' actualizado correctamente."
    );
    }

    /** DELETE /grupos/{id} */
    public function destroy(Grupo $grupo)
    {
    if ($grupo->inscripciones()->count() > 0) {
        $msj = "No se puede eliminar el grupo porque tiene alumnos inscritos.";
        return request()->ajax() 
            ? response()->json(['status' => 'error', 'mensaje' => $msj], 422)
            : back()->with('error', $msj);
    }

    $grupo->delete();
    
    if (request()->ajax()) {
        session()->flash('success', 'Grupo eliminado correctamente.');
        return response()->json(['status' => 'success']);
    }

    return redirect()->route('grupos.index')->with('success', 'Grupo eliminado correctamente.');
    }
    public function toggleStatus(Grupo $grupo)
    {
    $nuevoEstado = !$grupo->activo;
    $grupo->update(['activo' => $nuevoEstado]);
    
    $accion = $nuevoEstado ? 'activado' : 'desactivado';
    session()->flash('success', "El grupo ha sido {$accion} correctamente.");

    return request()->ajax() ? response()->json(['status' => 'success']) : back();
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
