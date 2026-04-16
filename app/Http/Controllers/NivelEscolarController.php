<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\NivelEscolar;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NivelEscolarController extends Controller
{
    use RespondsWithJson;

    /** GET /niveles */
    public function index()
    {
        $niveles = NivelEscolar::with(['grados' => fn($q) => $q->orderBy('numero')])
            ->orderBy('orden')
            ->get();

        // Si es AJAX devuelve JSON (para recargar tabla con jQuery)
        if (request()->ajax()) {
            return response()->json($niveles);
        }

        return view('niveles.index', compact('niveles'));
    }

    /** GET /niveles/{id} — solo AJAX */
    public function show(int $id)
    {
        $nivel = NivelEscolar::with(['grados'])->findOrFail($id);

        return response()->json($nivel);
    }

    /** GET /niveles/create */
    public function create()
    {
        return view('niveles.create');
    }

    /** POST /niveles */
   public function store(Request $request)
{
    // 1. Validación estricta: nombre y orden son obligatorios. 
    // Revoe es opcional según tu lógica anterior, pero aquí lo validamos.
    $data = $request->validate([
        'nombre' => ['required', 'string', 'max:100', 'unique:nivel_escolar,nombre'],
        'revoe'  => ['nullable', 'string', 'max:50', Rule::unique('nivel_escolar', 'revoe')->whereNotNull('revoe')],
        'orden'  => ['nullable', 'integer', 'min:1'],
    ], [
        'nombre.required' => 'El nombre del nivel es obligatorio.',
        'nombre.unique'   => 'Este nivel ya existe.',
        'orden.min'       => 'El orden debe ser al menos 1.',
    ]);

    // 2. Determinar el orden por defecto si viene vacío (al final de la lista)
    if (!$request->filled('orden')) {
        $data['orden'] = NivelEscolar::count() + 1;
    }

    // 3. LÓGICA DE EMPUJE: Si el orden ya existe, movemos los demás
    $nuevoOrden = $data['orden'];
    NivelEscolar::where('orden', '>=', $nuevoOrden)->increment('orden');

    // 4. Forzar estatus activo por defecto
    $data['activo'] = true;

    $nivel = NivelEscolar::create($data);

    Auditoria::registrar('nivel_escolar', $nivel->id, 'insert', null, $nivel->toArray());

    return $this->respuestaExito(
        redirectRoute: 'niveles.index',
        mensaje: "Nivel '{$nivel->nombre}' creado en la posición {$nivel->orden}."
    );
}

    /** GET /niveles/{id}/edit */
    public function edit(int $id)
    {
        $nivel = NivelEscolar::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($nivel);
        }

        return view('niveles.edit', compact('nivel'));
    }

    /** PUT /niveles/{id} */
    public function update(Request $request, int $id)
{
    $nivel = NivelEscolar::findOrFail($id);
    $ordenAnterior = $nivel->orden;
    $nuevoOrden = $request->input('orden');

    $data = $request->validate([
        'nombre' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('nivel_escolar', 'nombre')->ignore($id)],
        'orden'  => ['sometimes', 'required', 'integer', 'min:1'],
        'activo' => ['boolean'],
    ]);

    // LÓGICA DE REORDENAMIENTO
    if ($nuevoOrden && $nuevoOrden != $ordenAnterior) {
        if ($nuevoOrden < $ordenAnterior) {
            // Si el nivel sube (ej: de 3 a 1), los que están entre 1 y 2 suben un puesto (+1)
            NivelEscolar::where('id', '!=', $id)
                ->whereBetween('orden', [$nuevoOrden, $ordenAnterior - 1])
                ->increment('orden');
        } else {
            // Si el nivel baja (ej: de 1 a 3), los que están entre 2 y 3 bajan un puesto (-1)
            NivelEscolar::where('id', '!=', $id)
                ->whereBetween('orden', [$ordenAnterior + 1, $nuevoOrden])
                ->decrement('orden');
        }
    }

    $anteriorData = $nivel->toArray();
    $nivel->update($data);

    Auditoria::registrar('nivel_escolar', $nivel->id, 'update', $anteriorData, $nivel->fresh()->toArray());

    return $this->respuestaExito(
        redirectRoute: 'niveles.index',
        mensaje: "Nivel actualizado y lista reordenada."
    );
}

    /** DELETE /niveles/{id} */
    public function destroy(int $id)
    {
        $nivel = NivelEscolar::findOrFail($id);

        if ($nivel->grados()->whereHas('grupos', fn($q) => $q->where('activo', true))->exists()) {
            return $this->respuestaError(
                "No se puede desactivar '{$nivel->nombre}' porque tiene grupos activos."
            );
        }

        $nivel->update(['activo' => false]);

        Auditoria::registrar('nivel_escolar', $nivel->id, 'update', ['activo' => true], ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'niveles.index',
            mensaje: "Nivel '{$nivel->nombre}' desactivado correctamente."
        );
    }
}
