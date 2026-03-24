<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBecaAlumnoRequest;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\CatalogoBeca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BecaController extends Controller
{
    /** GET /becas/catalogo */
    public function catalogo(): JsonResponse
    {
        $catalogo = CatalogoBeca::activo()->orderBy('nombre')->get();

        return response()->json($catalogo);
    }

    /** POST /becas/catalogo */
    public function storeCatalogo(Request $request): JsonResponse
    {
        $this->soloAdmin();

        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo'        => ['required', 'in:porcentaje,monto_fijo'],
            'valor'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $beca = CatalogoBeca::create($data);

        return response()->json($beca, 201);
    }

    /** GET /becas — becas asignadas en el ciclo activo */
    public function index(Request $request): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $becas = BecaAlumno::with(['catalogoBeca', 'alumno', 'concepto', 'creadoPor'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('alumno_id'), fn($q) => $q->where('alumno_id', $request->alumno_id))
            ->when($request->filled('activo'),    fn($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('alumno_id')
            ->get();

        return response()->json($becas);
    }

    /** POST /becas */
    public function store(StoreBecaAlumnoRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['creado_por' => auth()->id()]);

        $beca = BecaAlumno::create($data);

        Auditoria::registrar('beca_alumno', $beca->id, 'insert', null, $beca->toArray());

        return response()->json($beca->load(['catalogoBeca', 'alumno', 'concepto']), 201);
    }

    /** DELETE /becas/{id} — desactiva la beca */
    public function destroy(int $id): JsonResponse
    {
        $this->soloAdmin();

        $beca     = BecaAlumno::findOrFail($id);
        $anterior = $beca->toArray();

        $beca->update(['activo' => false]);

        Auditoria::registrar('beca_alumno', $beca->id, 'update', $anterior, ['activo' => false]);

        return response()->json(['message' => 'Beca desactivada correctamente.']);
    }

    private function soloAdmin(): void
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }
}
