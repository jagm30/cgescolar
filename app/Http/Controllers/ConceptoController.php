<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConceptoCobro as Concepto;
use App\Traits\RespondsWithJson;
use App\Models\Auditoria;


class ConceptoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conceptos = Concepto::orderBy('nombre')->get();
        if (request()->ajax()) {
            return response()->json($conceptos);
        }
        return view('conceptos.index', compact('conceptos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Se normaliza los checkboxes antes de validar. 
        // Si no vienen en el request, se le  asigna 0 (falso).
        $request->merge([
            'aplica_beca'    => $request->has('aplica_beca') ? 1 : 0,
            'aplica_recargo' => $request->has('aplica_recargo') ? 1 : 0,
            'activo'         => $request->has('activo') ? 1 : 0,
        ]);

       
        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:100'],
            'descripcion'    => ['nullable', 'string', 'max:255'],
            'tipo'           => ['required', 'in:colegiatura,inscripcion,cargo_unico,cargo_recurrente'],
            'aplica_beca'    => ['boolean'],
            'aplica_recargo' => ['boolean'],
            'clave_sat'      => ['nullable', 'string', 'max:50'],
            'activo'         => ['boolean'],
        ], [
            'nombre.required' => 'El nombre del concepto es obligatorio.',
            'tipo.required'   => 'El tipo de concepto es obligatorio.',
            'tipo.in'         => 'El tipo seleccionado no es válido.',
        ]);
    
        $concepto = Concepto::create($data);

        Auditoria::create([
            'usuario_id'     => auth()->id(),
            'tabla_afectada' => 'conceptos_cobro',
            'registro_id'    => $concepto->id,
            'accion'         => "Creó el concepto: {$data['nombre']} de tipo {$data['tipo']}",
            'ip'             => $request->ip(),
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Concepto creado exitosamente.']);
        }

        return redirect()->route('conceptos.index')->with('success', 'Concepto creado exitosamente.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, string $id)
{

    $concepto = Concepto::findOrFail($id);

    // Guarda una copia exacta de cómo estaban los datos ANTES de cambiarlos
    // Usa toArray() para convertir todo el modelo a un arreglo fácilmente
    $datosAnteriores = $concepto->toArray();

    //  Normaliza los checkboxes (Igual que en el store)
    $request->merge([
        'aplica_beca'    => $request->has('aplica_beca') ? 1 : 0,
        'aplica_recargo' => $request->has('aplica_recargo') ? 1 : 0,
        'activo'         => $request->has('activo') ? 1 : 0,
    ]);

    $data = $request->validate([
        'nombre'         => ['required', 'string', 'max:100'],
        'descripcion'    => ['nullable', 'string', 'max:255'],
        'tipo'           => ['required', 'in:colegiatura,inscripcion,cargo_unico,cargo_recurrente'],
        'aplica_beca'    => ['boolean'],
        'aplica_recargo' => ['boolean'],
        'clave_sat'      => ['nullable', 'string', 'max:50'],
        'activo'         => ['boolean'],
    ], [
        'nombre.required' => 'El nombre del concepto es obligatorio.',
        'tipo.required'   => 'El tipo de concepto es obligatorio.',
        'tipo.in'         => 'El tipo seleccionado no es válido.',
    ]);

    $concepto->update($data);

    Auditoria::create([
        'usuario_id'       => auth()->id(),
        'tabla_afectada'   => 'conceptos_cobro',
        'registro_id'      => $concepto->id,
        'accion'           => "Actualizó el concepto: {$concepto->nombre}",
        
        'datos_anteriores' => json_encode($datosAnteriores), 
       
        'datos_nuevos'     => json_encode($concepto->fresh()->toArray()), 
        'ip'               => $request->ip(),
    ]);

    return redirect()->route('conceptos.index')->with('success', 'Concepto actualizado exitosamente.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $concepto = Concepto::findOrFail($id);

        $estadoAnterior = $concepto->activo;


        $concepto->update(['activo' => false]);

        Auditoria::create([
            'usuario_id'       => auth()->id(),
            'tabla_afectada'   => 'conceptos_cobro',
            'registro_id'      => $concepto->id,
            'accion'           => "Desactivó el concepto: {$concepto->nombre}",
            'datos_anteriores' => json_encode(['activo' => $estadoAnterior]),
            'datos_nuevos'     => json_encode(['activo' => false]),
            'ip'               => request()->ip(),
        ]);

        return redirect()->route('conceptos.index')->with('success', 'Concepto desactivado exitosamente.');
    }
}
