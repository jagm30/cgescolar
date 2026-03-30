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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipo'   => ['required', 'in:colegiatura,inscripcion,cargo_unico,cargo_recurrente'],
            'aplica_beca' => ['required', 'boolean'],
            'aplica_recargo' => ['required', 'boolean'],
            'clave_sat' => ['nullable', 'string', 'max:50'],
            'activo' => ['required', 'boolean'],
        ], [
            'nombre.required' => 'El nombre del concepto es obligatorio.',
            'tipo.required'   => 'El tipo de concepto es obligatorio.',
            'tipo.in'         => 'El tipo de concepto debe ser: colegiatura, inscripción, cargo único o cargo recurrente.',
            'monto.required'  => 'El monto del concepto es obligatorio.',
            'monto.numeric'   => 'El monto del concepto debe ser un número.',
            'monto.min'       => 'El monto del concepto no puede ser negativo.',
        ]);

        Concepto::create($data);
        Auditoria::create([
            'usuario_id' => auth()->id(),
            'accion'     => "Creó el concepto de cobro: {$data['nombre']} ({$data['tipo']}, $ {$data['monto']})",
        ]);
        return $this->success('Concepto creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
