<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Grado;
use App\Models\NivelEscolar;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    use RespondsWithJson;

    /** GET /grados */
    public function index(Request $request)
{
    // 1. Iniciamos la consulta con la relación
    $query = Grado::with(['nivel'])
        // Filtro por Nivel Escolar
        ->when($request->filled('nivel_id'), function ($q) use ($request) {
            return $q->where('nivel_id', $request->nivel_id);
        })
        // Filtro por Número de Grado (EL QUE TE FALTABA)
        ->when($request->filled('numero'), function ($q) use ($request) {
            return $q->where('numero', $request->numero);
        })
        // Orden lógico
        ->orderBy('nivel_id')
        ->orderBy('numero');

    // 2. Manejo de la cantidad de registros a mostrar
    $mostrar = $request->get('mostrar', 10);
    
    if ($mostrar == -1) {
        $grados = $query->get(); // Traer todos
    } else {
        // Usamos paginate para que Laravel maneje el límite, 
        // pero para DataTables simple con get() basta si no usas links de paginación de Laravel
        $grados = $query->take($mostrar)->get(); 
    }

    // 3. Respuesta AJAX (DataTables)
    if ($request->ajax()) {
        return response()->json($grados);
    }

    $niveles = NivelEscolar::activo()->get();

    return view('grados.index', compact('grados', 'niveles'));
}

    /** GET /grados/{id} — AJAX */
    public function show(int $id)
    {
        $grado = Grado::with(['nivel'])->findOrFail($id);

        return response()->json($grado);
    }

    /** GET /grados/create */
    public function create()
    {
        $niveles = NivelEscolar::activo()->get();

        return view('grados.create', compact('niveles'));
    }

    /** POST /grados */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nivel_id' => ['required', 'exists:nivel_escolar,id'],
            'nombre'   => ['required', 'string', 'max:20'],
            'numero'   => ['required', 'integer', 'min:1'],
        ], [
            'nivel_id.required' => 'Debe seleccionar el nivel educativo.',
            'nivel_id.exists'   => 'El nivel seleccionado no existe.',
            'nombre.required'   => 'El nombre del grado es obligatorio.',
            'numero.required'   => 'El número del grado es obligatorio.',
        ]);

        $existe = Grado::where('nivel_id', $data['nivel_id'])
            ->where('numero', $data['numero'])
            ->exists();

        if ($existe) {
            return $this->respuestaError(
                "Ya existe el grado {$data['numero']}° en ese nivel."
            );
        }

        $grado = Grado::create($data);

        Auditoria::registrar('grado', $grado->id, 'insert', null, $grado->toArray());

        return $this->respuestaExito(
            redirectRoute: 'grados.index',
            jsonData: ['grado' => $grado->load('nivel')],
            mensaje: "Grado '{$grado->nombre}' creado correctamente.",
            jsonStatus: 201
        );
    }

    /** GET /grados/{id}/edit */
    public function edit(int $id)
    {
        $grado   = Grado::findOrFail($id);
        $niveles = NivelEscolar::activo()->get();

        if (request()->ajax()) {
            return response()->json($grado->load('nivel'));
        }

        return view('grados.edit', compact('grado', 'niveles'));
    }

    /** PUT /grados/{id} */
    public function update(Request $request, int $id)
    {
        $grado    = Grado::findOrFail($id);
        $anterior = $grado->toArray();

        $data = $request->validate([
            'nivel_id' => ['sometimes', 'required', 'exists:nivel_escolar,id'],
            'nombre'   => ['sometimes', 'required', 'string', 'max:20'],
            'numero'   => ['sometimes', 'required', 'integer', 'min:1'],
        ]);

        $nivelId = $data['nivel_id'] ?? $grado->nivel_id;
        $numero  = $data['numero']   ?? $grado->numero;

        $duplicado = Grado::where('nivel_id', $nivelId)
            ->where('numero', $numero)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicado) {
            return $this->respuestaError("Ya existe el grado {$numero}° en ese nivel.");
        }

        $grado->update($data);

        Auditoria::registrar('grado', $grado->id, 'update', $anterior, $grado->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'grados.index',
            jsonData: ['grado' => $grado->fresh()->load('nivel')],
            mensaje: "Grado '{$grado->nombre}' actualizado correctamente."
        );
    }

    /** DELETE /grados/{id} */
    public function destroy(int $id)
    {
        $grado = Grado::findOrFail($id);

        if ($grado->grupos()->exists()) {
            return $this->respuestaError(
                "No se puede eliminar '{$grado->nombre}' porque tiene grupos asociados."
            );
        }

        $grado->delete();

        Auditoria::registrar('grado', $id, 'delete', $grado->toArray(), null);

        return $this->respuestaExito(
            redirectRoute: 'grados.index',
            mensaje: "Grado '{$grado->nombre}' eliminado correctamente."
        );
    }
}
