<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\ConceptoCobro;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConceptoCobroController extends Controller
{
    use RespondsWithJson;

    /** GET /conceptos */
    public function index(Request $request)
    {
        // 1. Armamos la consulta base con sus filtros (sin el get ni el orden)
        $query = ConceptoCobro::when(
            $request->filled('tipo'),
            fn($q) => $q->where('tipo', $request->tipo)
        )
            ->when(
                $request->filled('activo'),
                fn($q) => $q->where('activo', $request->boolean('activo'))
            )
            ->when(
                $request->filled('buscar'),
                fn($q) => $q->where('nombre', 'like', "%{$request->buscar}%")
            );

        // 2. TRUCO DE INGENIERO: Clonamos la consulta y sacamos TODO para las estadísticas
        $totales = (clone $query)->get();

        // 3. A la consulta original le aplicamos el orden y la paginación para la tabla
        $conceptos = $query->orderBy('tipo')
            ->orderBy('nombre')
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json($conceptos);
        }

        // 4. Mandamos ambas variables a la vista
        return view('conceptos.index', compact('conceptos', 'totales'));
    }

    /** GET /conceptos/{id} — solo AJAX */
    public function show(int $id)
    {
        $concepto = ConceptoCobro::findOrFail($id);

        return response()->json($concepto);
    }

    /** GET /conceptos/create */
    public function create()
    {
        return view('conceptos.create');
    }

    /** POST /conceptos */
    public function store(Request $request)
    {
        $request->merge([
            'aplica_beca'    => $request->has('aplica_beca') ? 1 : 0,
            'aplica_recargo' => $request->has('aplica_recargo') ? 1 : 0,
            'activo'         => $request->has('activo') ? 1 : 0,
        ]);

        $data = $request->validate([
            'nombre'        => ['required', 'string', 'max:200', 'unique:concepto_cobro,nombre'],
            'descripcion'   => ['nullable', 'string', 'max:500'],
            'tipo'          => ['required', 'in:colegiatura,inscripcion,cargo_unico,cargo_recurrente'],
            'aplica_beca'   => ['boolean'],
            'aplica_recargo' => ['boolean'],
            'clave_sat'     => ['nullable', 'string', 'max:20'],
            'activo'        => ['boolean'],
            'monto'         => ['nullable', 'numeric', 'min:0'], // <--- NUEVO
        ], [
            'nombre.required' => 'El nombre del concepto es obligatorio.',
            'nombre.unique'   => 'Ya existe un concepto con este nombre.',
            'tipo.required'   => 'Debe seleccionar el tipo de concepto.',
            'tipo.in'         => 'El tipo debe ser: colegiatura, inscripción, cargo único o cargo recurrente.',
            'monto.numeric'   => 'El monto debe ser un valor numérico.',
        ]);

        // Reglas de negocio
        if ($data['tipo'] !== 'colegiatura') {
            $data['aplica_beca'] = false;
        }

        // Si no es cargo recurrente o único, limpiamos el monto
        if (!in_array($data['tipo'], ['cargo_unico', 'cargo_recurrente'])) {
            $data['monto'] = null;
        }

        $concepto = ConceptoCobro::create($data);

        Auditoria::registrar('concepto_cobro', $concepto->id, 'insert', null, $concepto->toArray());

        return $this->respuestaExito(
            redirectRoute: 'conceptos.index',
            jsonData: ['concepto' => $concepto],
            mensaje: "Concepto '{$concepto->nombre}' creado correctamente.",
            jsonStatus: 201
        );
    }

    /** PUT /conceptos/{id} */
    public function update(Request $request, int $id)
    {
        $concepto = ConceptoCobro::findOrFail($id);
        $anterior = $concepto->toArray();

        $request->merge([
            'aplica_beca'    => $request->has('aplica_beca') ? 1 : 0,
            'aplica_recargo' => $request->has('aplica_recargo') ? 1 : 0,
            'activo'         => $request->has('activo') ? 1 : 0,
        ]);

        $data = $request->validate([
            'nombre'        => [
                'sometimes',
                'required',
                'string',
                'max:200',
                Rule::unique('concepto_cobro', 'nombre')->ignore($id)
            ],
            'descripcion'   => ['nullable', 'string', 'max:500'],
            'tipo'          => ['sometimes', 'required', 'in:colegiatura,inscripcion,cargo_unico,cargo_recurrente'],
            'aplica_beca'   => ['boolean'],
            'aplica_recargo' => ['boolean'],
            'clave_sat'     => ['nullable', 'string', 'max:20'],
            'activo'        => ['boolean'],
            'monto'         => ['nullable', 'numeric', 'min:0'], // <--- NUEVO
        ], [
            'nombre.unique' => 'Ya existe otro concepto con este nombre.',
            'tipo.in'       => 'El tipo debe ser: colegiatura, inscripción, cargo único o cargo recurrente.',
        ]);

        if (!$data['aplica_beca']) {
            $tieneBecas = $concepto->becasAlumno()->where('activo', true)->exists();
            if ($tieneBecas) {
                return $this->respuestaError(
                    "No se puede desactivar 'Aplica beca' porque existen becas activas asignadas a este concepto."
                );
            }
        }

        $tipoFinal = $data['tipo'] ?? $concepto->tipo;
        if ($tipoFinal !== 'colegiatura') {
            $data['aplica_beca'] = false;
        }

        // Si cambia de tipo, procesamos el monto
        if (!in_array($tipoFinal, ['cargo_unico', 'cargo_recurrente'])) {
            $data['monto'] = null;
        }

        $concepto->update($data);

        Auditoria::registrar('concepto_cobro', $concepto->id, 'update', $anterior, $concepto->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'conceptos.index',
            jsonData: ['concepto' => $concepto->fresh()],
            mensaje: "Concepto '{$concepto->nombre}' actualizado correctamente."
        );
    }
    /** DELETE /conceptos/{id} — desactiva, no elimina */
    public function destroy(int $id)
    {
        $concepto = ConceptoCobro::findOrFail($id);

        // No desactivar si tiene cargos pendientes o planes activos
        $tieneCargos = $concepto->cargos()
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->exists();

        if ($tieneCargos) {
            return $this->respuestaError(
                "No se puede desactivar '{$concepto->nombre}' porque tiene cargos pendientes de cobro."
            );
        }

        $tienePlanes = $concepto->planesPago()
            ->where('activo', true)
            ->exists();

        if ($tienePlanes) {
            return $this->respuestaError(
                "No se puede desactivar '{$concepto->nombre}' porque está incluido en planes de pago activos."
            );
        }

        $concepto->update(['activo' => false]);

        Auditoria::registrar('concepto_cobro', $concepto->id, 'update', ['activo' => true], ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'conceptos.index',
            mensaje: "Concepto '{$concepto->nombre}' desactivado correctamente."
        );
    }
}
