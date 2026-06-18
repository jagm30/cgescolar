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
use Barryvdh\DomPDF\Facade\Pdf;

class GrupoController extends Controller
{
    use RespondsWithJson;

    /** GET /grupos */
    public function index(Request $request)
    {
        // 1. Determinamos el ciclo que se está consultando
        $cicloId = $request->filled('ciclo_id')
            ? $request->ciclo_id
            : (auth()->user()->ciclo_seleccionado_id ?? CicloEscolar::activo()->value('id'));

        $query = Grupo::with(['grado.nivel'])->where('ciclo_id', $cicloId);

        // 2. Lógica de Estatus (Filtro Activo/Inactivo)
        if ($request->estatus == 'inactivos') {
            $query->where('activo', false);
        } elseif ($request->estatus == 'todos') {
            // No filtramos nada
        } else {
            $query->activo();
        }

        // 3. Paginación y conteo de alumnos inscritos
        $baseQuery = $query
            ->when($request->filled('nivel_id'), fn($q) => $q->whereHas(
                'grado',
                fn($q) => $q->where('nivel_id', $request->nivel_id)
            ))
            ->when($request->filled('grado_id'), fn($q) => $q->where('grado_id', $request->grado_id))
            ->withCount(['inscripciones as alumnos_inscritos' => fn($q) => $q->where('activo', true)])
            ->orderBy('grado_id')
            ->orderBy('nombre');

        // Cuando es AJAX para poblar un select (ciclo_id + nivel_id), devolver array plano
        if ($request->ajax() && $request->filled('nivel_id')) {
            return response()->json(
                $baseQuery->get()->map(fn($g) => array_merge($g->toArray(), [
                    'disponibles' => $g->cupo_maximo ? max(0, $g->cupo_maximo - $g->alumnos_inscritos) : null,
                ]))
            );
        }

        $gruposPaginados = $baseQuery->paginate(10);

        // 4. Transformación para calcular lugares disponibles
        $gruposPaginados->getCollection()->transform(fn($g) => array_merge($g->toArray(), [
            'disponibles' => $g->cupo_maximo ? max(0, $g->cupo_maximo - $g->alumnos_inscritos) : null,
        ]));

        $grupos = $gruposPaginados->appends($request->except('page'));

        if ($request->ajax()) {
            return response()->json($grupos);
        }

        // 5. Variables para la vista
        $niveles = NivelEscolar::activo()->get();
        $grados  = Grado::with('nivel')->orderBy('nivel_id')->orderBy('numero')->get();
        $ciclo   = CicloEscolar::find($cicloId);

        // --- AQUÍ ESTÁ EL CAMBIO CLAVE ---
        // Traemos todos los ciclos para que el Modal de Migración pueda mostrarlos
        $ciclosDisponibles = CicloEscolar::orderBy('fecha_inicio', 'desc')->get();

        $disenos = \App\Models\Credencial::all();

        return view('grupos.index', compact('grupos', 'niveles', 'grados', 'ciclo', 'ciclosDisponibles', 'disenos'));
    }
    /** GET /grupos/{id} */
    public function show(int $id)
    {
        $grupo = Grupo::with([
            'grado.nivel',
            'ciclo',
            'inscripciones' => fn($q) => $q->with([
                'alumno' => fn($q) => $q->select('id', 'matricula', 'nombre', 'ap_paterno', 'ap_materno', 'estado')
            ]),
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($grupo);
        }

        // 1. OBTENEMOS LOS GRADOS (Versión limpia sin JOIN manual)
        // Usamos el modelo para que Laravel use los nombres de tabla correctos automáticamente
        $grados = \App\Models\Grado::with('nivel')->get()->sortBy(function ($grado) {
            return $grado->nivel->id . '-' . $grado->numero;
        });

        // 2. Grupos disponibles para cambios (tu lógica original)
        $gruposDisponibles = Grupo::where('ciclo_id', $grupo->ciclo_id)
            ->where('grado_id', $grupo->grado_id)
            ->with('grado')
            ->withCount(['inscripciones as inscripciones_count' => function ($query) {
                $query->where('activo', true);
            }])
            ->get();
        // 3. OBTENEMOS LOS CICLOS ESCOLARES
        $ciclosDisponibles = \App\Models\CicloEscolar::where('id', '!=', $grupo->ciclo_id) // Excluye el actual
            ->where('id', '>', $grupo->ciclo_id) // Solo IDs mayores (ciclos creados después)
            ->whereIn('estado', ['activo', 'configuracion'])
            ->orderBy('id', 'asc')
            ->get();

        return view('grupos.show', compact('grupo', 'gruposDisponibles', 'grados', 'ciclosDisponibles'));
    }
    /** GET /grupos/grados-por-ciclo?ciclo_id=X */
public function gradosPorCiclo(Request $request)
{
    $query = Grado::with('nivel')
        ->whereHas('grupos', fn($q) => $q->where('ciclo_id', $request->ciclo_id));

    // LÓGICA INTELIGENTE: Filtramos a partir del grado de origen
    if ($request->filled('grado_origen_id')) {
        $gradoOrigen = Grado::find($request->grado_origen_id);

        if ($gradoOrigen) {
            $query->where(function($q) use ($gradoOrigen) {
                // Opción A: Es un nivel educativo superior (ej. de Preescolar a Primaria)
                $q->where('nivel_id', '>', $gradoOrigen->nivel_id)
                  // Opción B: Es el mismo nivel, pero un año superior o igual (ej. 1° a 2°)
                  ->orWhere(function($sub) use ($gradoOrigen) {
                      $sub->where('nivel_id', $gradoOrigen->nivel_id)
                          ->where('numero', '>=', $gradoOrigen->numero);
                  });
            });
        }
    }

    $grados = $query->orderBy('nivel_id')
        ->orderBy('numero')
        ->get()
        ->map(fn($gr) => [
            'id'    => $gr->id,
            'label' => $gr->nivel->nombre . ' - ' . $gr->numero . '°',
        ]);

    return response()->json($grados);
}

    /** GET /grupos/grupos-por-ciclo-grado?ciclo_id=X&grado_id=Y */
    public function gruposPorCicloGrado(Request $request)
    {
        $grupos = Grupo::where('ciclo_id', $request->ciclo_id)
            ->where('grado_id', $request->grado_id)
            ->where('activo', true)
            ->withCount(['inscripciones as alumnos_count' => fn($q) => $q->where('activo', true)])
            ->orderBy('nombre')
            ->get()
            ->map(fn($g) => [
                'id'    => $g->id,
                'label' => 'Grupo ' . $g->nombre . ' (' . $g->alumnos_count . ' alumnos)',
            ]);

        return response()->json($grupos);
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
        // El request ahora SÍ traerá el ciclo_id gracias al input oculto en el HTML
        $cicloId = $request->ciclo_id
            ?? auth()->user()->ciclo_selected_id
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

        $data['ciclo_id'] = $cicloId;
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

        $mensajeExito = "Grupo '{$grupo->nombre}' creado correctamente.";

        if ($request->ajax()) {
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

    public function generarReporte(int $id)
    {
        // Filtramos las inscripciones para traer solo las ACTIVAS en el reporte
        $grupo = Grupo::with([
            'grado',
            'inscripciones' => fn($q) => $q->where('activo', true)->with('alumno')
        ])->findOrFail($id);

        if (ob_get_length()) ob_end_clean();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('grupos.reportes.lista_pdf', compact('grupo'));

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("Lista_{$grupo->nombre}.pdf");
    }

    public function reportePagos(int $id)
    {
        $grupo = Grupo::with([
            'grado.nivel',
            'ciclo',
            'inscripciones' => fn($q) => $q->where('activo', true)->with([
                'alumno',
                'cargos' => fn($q) => $q->with('concepto')->orderBy('fecha_vencimiento'),
            ]),
        ])->findOrFail($id);

        if (ob_get_length()) ob_end_clean();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('grupos.reportes.pagos_pdf', compact('grupo'));

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream("Pagos_{$grupo->nombre}.pdf");
    }
    public function migrarEstructura(Request $request)
    {
        // Corregimos el nombre de la tabla a 'ciclo_escolar'
        $request->validate([
            'ciclo_destino_id' => 'required|exists:ciclo_escolar,id',
            'ciclo_origen_id' => 'required|exists:ciclo_escolar,id',
        ]);

        // 1. Obtenemos los grupos del ciclo origen
        $gruposOrigen = Grupo::where('ciclo_id', $request->ciclo_origen_id)->get();

        if ($gruposOrigen->isEmpty()) {
            return back()->with('error', "No se encontraron grupos en el ciclo de origen.");
        }

        $contador = 0;

        foreach ($gruposOrigen as $grupo) {
            // 2. Evitamos duplicados en el ciclo destino
            $existe = Grupo::where('ciclo_id', $request->ciclo_destino_id)
                ->where('nombre', $grupo->nombre)
                ->where('grado_id', $grupo->grado_id)
                ->exists();

            if (!$existe) {
                // 3. Creamos el nuevo grupo (sin alumnos)
                Grupo::create([
                    'nombre'      => $grupo->nombre,
                    'grado_id'    => $grupo->grado_id,
                    'ciclo_id'    => $request->ciclo_destino_id,
                    'docente'     => $grupo->docente, // Puedes dejarlo null si prefieres maestros nuevos
                    'cupo_maximo' => $grupo->cupo_maximo,
                    'activo'      => true
                ]);
                $contador++;
            }
        }

        // ── AQUÍ ESTÁ LA CORRECCIÓN ──
        if ($contador === 0) {
            return back()->with('warning', "Atención: La estructura ya había sido migrada anteriormente o los grupos ya existen en el ciclo destino. No se crearon nuevos salones.");
        }

        return back()->with('success', "¡Estructura migrada! Se crearon $contador salones en el nuevo ciclo.");
    }
    public function promocionarMasivo(Request $request)
    {
        $request->validate([
            'inscripciones_ids'  => 'required|array',
            'ciclo_destino_id'   => 'required|exists:ciclo_escolar,id',
            'grado_destino_id'   => 'required|exists:grado,id',
            'grupo_destino_id'   => 'required|exists:grupo,id',   // ← ahora viene del select
            'grupo_origen_id'    => 'required|exists:grupo,id',
        ]);

        $grupoDestino = Grupo::findOrFail($request->grupo_destino_id);
        $contador = 0;

        try {
            \DB::transaction(function () use ($request, $grupoDestino, &$contador) {
                foreach ($request->inscripciones_ids as $inscripcionId) {
                    $inscripcionActual = \App\Models\Inscripcion::findOrFail($inscripcionId);
                    $alumno = $inscripcionActual->alumno;

                    $inscripcionActual->update([
                        'activo'        => false,
                        'observaciones' => ($inscripcionActual->observaciones ?? '') .
                            " | Promocionado al grupo {$grupoDestino->nombre} ({$grupoDestino->ciclo->nombre})"
                    ]);

                    \App\Models\Inscripcion::create([
                        'alumno_id' => $alumno->id,
                        'ciclo_id'  => $request->ciclo_destino_id,
                        'grado_id'  => $request->grado_destino_id,
                        'grupo_id'  => $grupoDestino->id,
                        'fecha'     => now()->format('Y-m-d'),
                        'activo'    => true,
                        'estado'    => 'inscrito',
                    ]);

                    $alumno->update(['estado' => 'activo']);
                    $contador++;
                }
            });

            return redirect()->route('grupos.show', $request->grupo_origen_id)
                ->with('success', "Se promocionaron {$contador} alumnos al grupo '{$grupoDestino->nombre}' correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error en base de datos: ' . $e->getMessage());
        }
    }
}
