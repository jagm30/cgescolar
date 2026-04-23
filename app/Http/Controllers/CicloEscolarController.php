<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class CicloEscolarController extends Controller
{
    use RespondsWithJson;

    /** GET /ciclos */
public function index(Request $request)
{
    $query = CicloEscolar::orderByDesc('fecha_inicio');

    // Filtro por Estado
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    // Filtro por Año
    if ($request->filled('anio')) {
        $anio = $request->anio;
        $query->where(function($q) use ($anio) {
            $q->whereYear('fecha_inicio', $anio)
              ->orWhereYear('fecha_fin', $anio);
        });
    }

    // CAMBIO CRÍTICO: Usar paginate() en lugar de get()
    // Capturamos el valor de 'mostrar' (10, 25, 50) o usamos 10 por defecto
    $ciclos = $query->paginate($request->input('mostrar', 10));

    if ($request->ajax()) {
        return response()->json($ciclos);
    }

    return view('ciclos.index', compact('ciclos'));
}
    /** GET /ciclos/{id} */
    public function show(int $id)
    {
        $ciclo = CicloEscolar::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($ciclo);
        }

        return view('ciclos.show', compact('ciclo'));
    }

    /** GET /ciclos/create */
    public function create()
    {
        return view('ciclos.create');
    }

    /** POST /ciclos */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
            'estado'       => ['required', 'in:activo,cerrado,configuracion'],
        ], [
            'nombre.required'       => 'El nombre del ciclo es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after'       => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'estado.in'             => 'El estado debe ser: activo, cerrado o configuración.',
        ]);

        

        $ciclo = CicloEscolar::create($data);

        Auditoria::registrar('ciclo_escolar', $ciclo->id, 'insert', null, $ciclo->toArray());

        return $this->respuestaExito(
            redirectRoute: 'ciclos.index',
            jsonData: ['ciclo' => $ciclo],
            mensaje: "Ciclo '{$ciclo->nombre}' creado correctamente.",
            jsonStatus: 201
        );
    }

    /** GET /ciclos/{id}/edit */
    public function edit(int $id)
    {
        $ciclo = CicloEscolar::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($ciclo);
        }

        return view('ciclos.edit', compact('ciclo'));
    }

    /** PUT /ciclos/{id} */
    public function update(Request $request, int $id)
    {
        $ciclo    = CicloEscolar::findOrFail($id);
        $anterior = $ciclo->toArray();

        $data = $request->validate([
            'nombre'       => ['sometimes', 'required', 'string', 'max:50'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin'    => ['sometimes', 'required', 'date', 'after:fecha_inicio'],
            'estado'       => ['sometimes', 'required', 'in:activo,cerrado,configuracion'],
        ]);

        

        $ciclo->update($data);

        Auditoria::registrar('ciclo_escolar', $ciclo->id, 'update', $anterior, $ciclo->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'ciclos.index',
            jsonData: ['ciclo' => $ciclo->fresh()],
            mensaje: "Ciclo '{$ciclo->nombre}' actualizado correctamente."
        );
    }

    /** POST /ciclos/{id}/seleccionar
     * El usuario interno elige el ciclo con el que trabajará.
     */
    public function seleccionar(int $id)
    {
        $ciclo   = CicloEscolar::findOrFail($id);
        $usuario = auth()->user();

        if ($usuario->esPadre()) {
            return $this->respuestaError('Los padres de familia no pueden seleccionar ciclo.', '', 403);
        }

        $usuario->update(['ciclo_seleccionado_id' => $ciclo->id]);

        return $this->respuestaExito(
            redirectRoute: 'ciclos.index',
            jsonData: ['ciclo' => $ciclo],
            mensaje: "Ahora trabajas en el ciclo '{$ciclo->nombre}'."
        );
    }
    /** DELETE /ciclos/{id}/force */
public function forceDelete(int $id)
{
    $ciclo = CicloEscolar::findOrFail($id);

    // Validación de seguridad: No borrar si tiene grupos asociados
    if ($ciclo->grupos()->exists()) {
        return $this->respuestaError(
            "No se puede eliminar el ciclo '{$ciclo->nombre}' porque tiene grupos registrados. Primero elimine o mueva los grupos."
        );
    }

    $copia = $ciclo->toArray();
    $ciclo->delete(); // Borrado físico

    Auditoria::registrar('ciclo_escolar', $id, 'delete', $copia, null);

    return $this->respuestaExito(
        redirectRoute: 'ciclos.index',
        mensaje: "El ciclo escolar ha sido eliminado permanentemente."
    );
}
}
