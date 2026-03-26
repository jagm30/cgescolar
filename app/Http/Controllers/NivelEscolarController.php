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
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:nivel_escolar,nombre'],
            'revoe'  => ['nullable', 'string', 'max:50',
                         Rule::unique('nivel_escolar', 'revoe')->whereNotNull('revoe')],
            'orden'  => ['required', 'integer', 'min:1'],
            'activo' => ['boolean'],
        ], [
            'nombre.required' => 'El nombre del nivel es obligatorio.',
            'nombre.unique'   => 'Ya existe un nivel con este nombre.',
            'revoe.unique'    => 'Este REVOE ya está registrado en otro nivel.',
            'orden.required'  => 'El orden es obligatorio.',
        ]);

        $nivel = NivelEscolar::create($data);

        Auditoria::registrar('nivel_escolar', $nivel->id, 'insert', null, $nivel->toArray());

        return $this->respuestaExito(
            redirectRoute: 'niveles.index',
            jsonData: ['nivel' => $nivel],
            mensaje: "Nivel '{$nivel->nombre}' creado correctamente.",
            jsonStatus: 201
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
        $nivel    = NivelEscolar::findOrFail($id);
        $anterior = $nivel->toArray();

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:100',
                         Rule::unique('nivel_escolar', 'nombre')->ignore($id)],
            'revoe'  => ['nullable', 'string', 'max:50',
                         Rule::unique('nivel_escolar', 'revoe')->ignore($id)->whereNotNull('revoe')],
            'orden'  => ['sometimes', 'required', 'integer', 'min:1'],
            'activo' => ['boolean'],
        ], [
            'nombre.unique' => 'Ya existe otro nivel con este nombre.',
            'revoe.unique'  => 'Este REVOE ya está registrado en otro nivel.',
        ]);

        $nivel->update($data);

        Auditoria::registrar('nivel_escolar', $nivel->id, 'update', $anterior, $nivel->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'niveles.index',
            jsonData: ['nivel' => $nivel->fresh()],
            mensaje: "Nivel '{$nivel->nombre}' actualizado correctamente."
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
