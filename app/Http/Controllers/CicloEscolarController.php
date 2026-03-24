<?php

namespace App\Http\Controllers;

use App\Models\CicloEscolar;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CicloEscolarController extends Controller
{
    /** GET /ciclos */
    public function index(): JsonResponse
    {
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();

        return response()->json($ciclos);
    }

    /** GET /ciclos/{id} */
    public function show(int $id): JsonResponse
    {
        $ciclo = CicloEscolar::findOrFail($id);

        return response()->json($ciclo);
    }

    /** POST /ciclos */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('administrador');

        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
            'estado'       => ['required', 'in:activo,cerrado,configuracion'],
        ]);

        // Solo puede haber un ciclo activo al mismo tiempo
        if ($data['estado'] === 'activo') {
            CicloEscolar::where('estado', 'activo')->update(['estado' => 'cerrado']);
        }

        $ciclo = CicloEscolar::create($data);

        return response()->json($ciclo, 201);
    }

    /** PUT /ciclos/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('administrador');

        $ciclo = CicloEscolar::findOrFail($id);

        $data = $request->validate([
            'nombre'       => ['sometimes', 'required', 'string', 'max:50'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin'    => ['sometimes', 'required', 'date', 'after:fecha_inicio'],
            'estado'       => ['sometimes', 'required', 'in:activo,cerrado,configuracion'],
        ]);

        // Si se activa este ciclo, desactivar el anterior
        if (isset($data['estado']) && $data['estado'] === 'activo') {
            CicloEscolar::where('estado', 'activo')
                        ->where('id', '!=', $id)
                        ->update(['estado' => 'cerrado']);
        }

        $ciclo->update($data);

        return response()->json($ciclo);
    }

    /**
     * POST /ciclos/{id}/seleccionar
     * El usuario interno guarda el ciclo con el que desea trabajar.
     */
    public function seleccionar(int $id): JsonResponse
    {
        $ciclo   = CicloEscolar::findOrFail($id);
        $usuario = auth()->user();

        if ($usuario->esPadre()) {
            return response()->json(['message' => 'Los padres de familia no pueden seleccionar ciclo.'], 403);
        }

        $usuario->update(['ciclo_seleccionado_id' => $ciclo->id]);

        return response()->json([
            'message' => "Ciclo '{$ciclo->nombre}' seleccionado correctamente.",
            'ciclo'   => $ciclo,
        ]);
    }

    /**
     * GET /ciclos/activo
     * Devuelve el ciclo activo del sistema.
     */
    public function activo(): JsonResponse
    {
        $ciclo = CicloEscolar::activo()->first();

        if (!$ciclo) {
            return response()->json(['message' => 'No hay ningún ciclo activo configurado.'], 404);
        }

        return response()->json($ciclo);
    }

    // ── Helper privado ───────────────────────────────────

    private function authorize(string $rol): void
    {
        if (auth()->user()->rol !== $rol) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }
    }
}
