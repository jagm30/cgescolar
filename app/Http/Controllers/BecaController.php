<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogoBecaRequest;
use App\Http\Requests\StoreBecaAlumnoRequest;
use App\Models\Alumno;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\CatalogoBeca;
use App\Models\CicloEscolar;
use App\Models\PlanPago;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class BecaController extends Controller
{
    use RespondsWithJson;

    public function catalogo()
    {
        $catalogo = CatalogoBeca::activo()->orderBy('nombre')->get();

        if (request()->ajax()) {
            return response()->json($catalogo);
        }

        return view('becas.catalogo', compact('catalogo'));
    }

    public function storeCatalogo(CatalogoBecaRequest $request)
    {
        $beca = CatalogoBeca::create($request->validated());

        return $this->respuestaExito(
            redirectRoute: 'becas.catalogo',
            jsonData: ['beca' => $beca],
            mensaje: "Beca '{$beca->nombre}' agregada al catálogo.",
            jsonStatus: 201
        );
    }

    public function create(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $catalogo = CatalogoBeca::activo()->orderBy('nombre')->get();
        $planes = PlanPago::with('nivel')
            ->where('ciclo_id', $cicloId)
            ->activo()
            ->orderBy('nombre')
            ->get();
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();
        $alumnos = Alumno::whereHas('inscripciones', function ($query) use ($cicloId) {
            $query->where('ciclo_id', $cicloId)->where('activo', true);
        })->orderBy('ap_paterno')->orderBy('ap_materno')->orderBy('nombre')->get();
        $cicloActual = CicloEscolar::find($cicloId);
        $alumnoActual = null;
        $alumnoId = $request->query('alumno_id') ?? session('_old_input.alumno_id');

        if ($alumnoId) {
            $alumnoActual = Alumno::with(['becas.catalogoBeca', 'becas.plan', 'becas.concepto'])
                ->find($alumnoId);
        }

        return view('becas.create', compact('catalogo', 'planes', 'ciclos', 'alumnos', 'alumnoActual', 'cicloActual'));
    }

    public function editCatalogo(int $id)
    {
        $beca = CatalogoBeca::findOrFail($id);

        return view('becas.edit_catalogo', compact('beca'));
    }

    public function updateCatalogo(CatalogoBecaRequest $request, int $id)
    {
        $beca = CatalogoBeca::findOrFail($id);
        $anterior = $beca->toArray();

        $beca->update($request->validated());
        Auditoria::registrar('catalogo_beca', $beca->id, 'update', $anterior, $beca->toArray());

        return $this->respuestaExito(
            redirectRoute: 'becas.catalogo',
            mensaje: "Beca '{$beca->nombre}' actualizada correctamente."
        );
    }

    public function destroyCatalogo(int $id)
    {
        $beca = CatalogoBeca::findOrFail($id);
        $anterior = $beca->toArray();

        $beca->update(['activo' => false]);
        Auditoria::registrar('catalogo_beca', $beca->id, 'update', $anterior, ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'becas.catalogo',
            mensaje: "Beca '{$beca->nombre}' desactivada correctamente."
        );
    }

    public function alumnoBecasActivas(int $alumnoId)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $alumno = Alumno::findOrFail($alumnoId);

        $becas = BecaAlumno::with(['catalogoBeca', 'plan', 'concepto'])
            ->where('alumno_id', $alumnoId)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->get();

        return response()->json([
            'alumno' => [
                'id' => $alumno->id,
                'nombre_completo' => $alumno->nombre_completo,
            ],
            'becas' => $becas->map(function ($beca) {
                return [
                    'id' => $beca->id,
                    'nombre' => $beca->catalogoBeca->nombre,
                    'plan' => $beca->plan?->nombre,
                    'destino' => $beca->destino_beca,
                    'tipo' => $beca->catalogoBeca->tipo,
                    'valor' => $beca->catalogoBeca->valor,
                    'vigencia_inicio' => $beca->vigencia_inicio?->format('d/m/Y'),
                    'vigencia_fin' => $beca->vigencia_fin?->format('d/m/Y'),
                ];
            }),
        ]);
    }

    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $becas = BecaAlumno::with(['catalogoBeca', 'alumno', 'plan', 'concepto', 'ciclo', 'creadoPor'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('alumno_id'), fn ($q) => $q->where('alumno_id', $request->alumno_id))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('alumno_id')
            ->get();

        if ($request->ajax()) {
            return response()->json($becas);
        }

        $catalogo = CatalogoBeca::activo()->get();
        $planes = PlanPago::where('ciclo_id', $cicloId)->activo()->get();

        return view('becas.index', compact('becas', 'catalogo', 'planes'));
    }

    public function store(StoreBecaAlumnoRequest $request)
    {
        $datos = $request->validated();

        $becasActivas = BecaAlumno::where('alumno_id', $datos['alumno_id'])
            ->where('ciclo_id', $datos['ciclo_id'])
            ->where('activo', true)
            ->get();

        if ($becasActivas->isNotEmpty() && ! $request->boolean('deshabilitar_beca_anterior')) {
            return $this->respuestaError(
                'Este alumno ya tiene una beca activa en el ciclo escolar. Marca la opción para deshabilitarla antes de asignar una nueva.'
            );
        }

        foreach ($becasActivas as $becaActiva) {
            $anterior = $becaActiva->toArray();
            $becaActiva->update(['activo' => false]);
            Auditoria::registrar('beca_alumno', $becaActiva->id, 'update', $anterior, ['activo' => false]);
        }

        $beca = BecaAlumno::create(array_merge(
            $datos,
            ['concepto_id' => null, 'creado_por' => auth()->id(), 'activo' => true]
        ));

        Auditoria::registrar('beca_alumno', $beca->id, 'insert', null, $beca->toArray());

        return $this->respuestaExito(
            redirectRoute: 'becas.index',
            jsonData: ['beca' => $beca->load(['catalogoBeca', 'alumno', 'plan', 'concepto'])],
            mensaje: 'Beca asignada correctamente.',
            jsonStatus: 201
        );
    }

    public function destroy(int $id)
    {
        $beca = BecaAlumno::findOrFail($id);
        $anterior = $beca->toArray();

        $beca->update(['activo' => false]);

        Auditoria::registrar('beca_alumno', $beca->id, 'update', $anterior, ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'becas.index',
            mensaje: 'Beca desactivada correctamente.'
        );
    }
}
