<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBecaAlumnoRequest;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\CatalogoBeca;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class BecaController extends Controller
{
    use RespondsWithJson;

    /** GET /becas/catalogo */
    public function catalogo()
    {
        $catalogo = CatalogoBeca::activo()->orderBy('nombre')->get();

        if (request()->ajax()) {
            return response()->json($catalogo);
        }

        return view('becas.catalogo', compact('catalogo'));
    }

    /** POST /becas/catalogo */
    public function storeCatalogo(Request $request)
    {
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo'        => ['required', 'in:porcentaje,monto_fijo'],
            'valor'       => ['required', 'numeric', 'min:0.01'],
        ]);

        $beca = CatalogoBeca::create($data);

        return $this->respuestaExito(
            redirectRoute: 'becas.catalogo',
            jsonData: ['beca' => $beca],
            mensaje: "Beca '{$beca->nombre}' agregada al catálogo.",
            jsonStatus: 201
        );
    }

    /** GET /becas */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $becas = BecaAlumno::with(['catalogoBeca', 'alumno', 'concepto', 'creadoPor'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('alumno_id'), fn($q) => $q->where('alumno_id', $request->alumno_id))
            ->when($request->filled('activo'),    fn($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('alumno_id')
            ->get();

        if ($request->ajax()) {
            return response()->json($becas);
        }

        $catalogo  = CatalogoBeca::activo()->get();
        $conceptos = ConceptoCobro::where('aplica_beca', true)->activo()->get();

        return view('becas.index', compact('becas', 'catalogo', 'conceptos'));
    }

    /** POST /becas */
    public function store(StoreBecaAlumnoRequest $request)
    {
        $beca = BecaAlumno::create(array_merge(
            $request->validated(),
            ['creado_por' => auth()->id()]
        ));

        Auditoria::registrar('beca_alumno', $beca->id, 'insert', null, $beca->toArray());

        return $this->respuestaExito(
            redirectRoute: 'becas.index',
            jsonData: ['beca' => $beca->load(['catalogoBeca', 'alumno', 'concepto'])],
            mensaje: 'Beca asignada correctamente.',
            jsonStatus: 201
        );
    }

    /** DELETE /becas/{id} */
    public function destroy(int $id)
    {
        $beca     = BecaAlumno::findOrFail($id);
        $anterior = $beca->toArray();

        $beca->update(['activo' => false]);

        Auditoria::registrar('beca_alumno', $beca->id, 'update', $anterior, ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'becas.index',
            mensaje: 'Beca desactivada correctamente.'
        );
    }
}
