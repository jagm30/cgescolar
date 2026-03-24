<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\NivelEscolar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NivelEscolarController extends Controller
{
    /** GET /niveles */
    public function index(Request $request): JsonResponse
    {
        $niveles = NivelEscolar::when(
                $request->filled('activo'),
                fn($q) => $q->where('activo', $request->boolean('activo'))
            )
            ->orderBy('orden')
            ->get();

        return response()->json($niveles);
    }

    /** GET /niveles/{id} */
    public function show(int $id): JsonResponse
    {
        $nivel = NivelEscolar::with(['grados'])->findOrFail($id);

        return response()->json($nivel);
    }

    /** POST /niveles */
    public function store(Request $request): JsonResponse
    {
        $this->soloAdmin();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:nivel_escolar,nombre'],
            'revoe'  => ['nullable', 'string', 'max:50'],
            'orden'  => ['required', 'integer', 'min:1'],
            'activo' => ['boolean'],
        ]);

        $nivel = NivelEscolar::create($data);

        Auditoria::registrar('nivel_escolar', $nivel->id, 'insert', null, $nivel->toArray());

        return response()->json($nivel, 201);
    }

    /** PUT /niveles/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->soloAdmin();

        $nivel    = NivelEscolar::findOrFail($id);
        $anterior = $nivel->toArray();

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:100',
                         \Illuminate\Validation\Rule::unique('nivel_escolar', 'nombre')->ignore($id)],
            'revoe'  => ['nullable', 'string', 'max:50'],
            'orden'  => ['sometimes', 'required', 'integer', 'min:1'],
            'activo' => ['boolean'],
        ]);

        $nivel->update($data);

        Auditoria::registrar('nivel_escolar', $nivel->id, 'update', $anterior, $nivel->fresh()->toArray());

        return response()->json($nivel->fresh());
    }

    /** DELETE /niveles/{id} — solo desactiva */
    public function destroy(int $id): JsonResponse
    {
        $this->soloAdmin();

        $nivel = NivelEscolar::findOrFail($id);

        // Verificar que no tenga grupos activos
        if ($nivel->grados()->whereHas('grupos', fn($q) => $q->where('activo', true))->exists()) {
            return response()->json([
                'message' => 'No se puede desactivar el nivel porque tiene grupos activos asignados.',
            ], 422);
        }

        $nivel->update(['activo' => false]);

        Auditoria::registrar('nivel_escolar', $nivel->id, 'update', ['activo' => true], ['activo' => false]);

        return response()->json(['message' => "Nivel '{$nivel->nombre}' desactivado correctamente."]);
    }

    // ── Helper ───────────────────────────────────────────

    private function soloAdmin(): void
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }
}
