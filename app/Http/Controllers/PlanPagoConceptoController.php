<?php

namespace App\Http\Controllers;

use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\ConceptoCobro;
use Illuminate\Http\Request;

class PlanPagoConceptoController extends Controller
{
    /**
     * GET /planes/{planId}/conceptos
     * Lista los conceptos asignados al plan con sus montos.
     */
    public function index(int $planId)
    {
        $plan = PlanPago::with([
            'conceptos' => fn($q) => $q->orderBy('tipo'),
        ])->findOrFail($planId);

        // Conceptos disponibles para agregar (los que no están en el plan)
        $conceptosDisponibles = ConceptoCobro::where('activo', true)
            ->whereNotIn('id', $plan->conceptos->pluck('id'))
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        if (request()->ajax()) {
            return response()->json([
                'plan'                  => $plan,
                'conceptos_disponibles' => $conceptosDisponibles,
            ]);
        }

        return view('planes.conceptos', compact('plan', 'conceptosDisponibles'));
    }

/**
     * POST /planes/{planId}/conceptos
     * Agrega múltiples conceptos al plan con sus montos.
     */
    public function store(Request $request, int $planId)
    {
        $plan = PlanPago::findOrFail($planId);

        // 1. Validamos que venga el array de conceptos
        $request->validate([
            'conceptos' => ['required', 'array', 'min:1'],
            'conceptos.*.concepto_id' => ['required', 'integer', 'exists:concepto_cobro,id'],
            'conceptos.*.monto'       => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ], [
            'conceptos.required' => 'Debes añadir al menos un concepto.',
            'conceptos.*.concepto_id.exists' => 'Uno de los conceptos seleccionados no existe.',
            'conceptos.*.monto.min' => 'Los montos deben ser mayores a cero.',
        ]);

        try {
            \DB::beginTransaction();
            $registrosCreados = [];

            foreach ($request->conceptos as $item) {
                // 2. Verificar que no esté ya asignado (Evitar duplicados)
                $yaExiste = PlanPagoConcepto::where('plan_id', $planId)
                    ->where('concepto_id', $item['concepto_id'])
                    ->exists();

                if ($yaExiste) {
                    // Si ya existe, nos saltamos este o podrías lanzar un error
                    continue; 
                }

                // 3. Crear el registro
                $registro = PlanPagoConcepto::create([
                    'plan_id'     => $planId,
                    'concepto_id' => $item['concepto_id'],
                    'monto'       => $item['monto'],
                ]);

                // 4. Auditoría por cada concepto insertado
                \App\Models\Auditoria::registrar(
                    'plan_pago_conceptos', $registro->id, 'insert',
                    null, $registro->toArray()
                );

                $registrosCreados[] = $registro;
            }

            \DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'message' => count($registrosCreados) . " conceptos procesados correctamente.",
                    'registros' => $registrosCreados,
                ], 201);
            }

            return redirect()->route('planes.conceptos.index', $planId)
                ->with('success', count($registrosCreados) . " conceptos agregados correctamente al plan.");

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Error al procesar los conceptos: ' . $e->getMessage());
        }
    }

    /**
     * PUT /planes/{planId}/conceptos/{id}
     * Actualiza el monto de un concepto en el plan.
     */
    public function update(Request $request, int $planId, int $id)
    {
        $registro = PlanPagoConcepto::where('plan_id', $planId)
            ->findOrFail($id);

        $anterior = $registro->toArray();

        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ], [
            'monto.min' => 'El monto debe ser mayor a cero.',
        ]);

        $registro->update($data);

        \App\Models\Auditoria::registrar(
            'plan_pago_concepto', $registro->id, 'update',
            $anterior, $registro->fresh()->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message'  => 'Monto actualizado.',
                'registro' => $registro->fresh()->load('concepto'),
            ]);
        }

        return redirect()->route('planes.conceptos.index', $planId)
            ->with('success', 'Monto actualizado correctamente.');
    }

    /**
     * DELETE /planes/{planId}/conceptos/{id}
     * Quita un concepto del plan.
     */
public function destroy(int $planId, int $id)
{
    // BUSCAMOS POR LOS DOS CAMPOS:
    // $planId es el ID del plan
    // $id es el ID del concepto que viene del botón
    $registro = PlanPagoConcepto::where('plan_id', $planId)
        ->where('concepto_id', $id) 
        ->first(); // Primero intentamos buscarlo así

    // Si no lo encuentra por concepto_id, intentamos por su propia llave primaria 
    // (por si acaso sí estabas mandando el ID de la tabla intermedia)
    if (!$registro) {
        $registro = PlanPagoConcepto::find($id);
    }

    // Si de plano no existe de ninguna forma, mandamos el error manual
    if (!$registro) {
        abort(404, "No se encontró el concepto asignado a este plan.");
    }

    // ... AQUÍ SIGUE TU LÓGICA DE CARGOS ...
    $registro->load('concepto'); // Cargamos la relación para el mensaje y validación
    
    // Verificar cargos (Tu código actual)
    $tieneCargos = \App\Models\Cargo::where('concepto_id', $registro->concepto_id)
        ->whereHas('inscripcion.grupo', fn($q) => 
            $q->where('ciclo_id', $plan->ciclo_id ?? null) 
        )->exists();

    if ($tieneCargos) {
        $msg = "No se puede quitar \"{$registro->concepto->nombre}\": ya se generaron cargos.";
        return request()->ajax() ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
    }

    $nombre = $registro->concepto->nombre;
    $registro->delete();

    return redirect()->route('planes.conceptos.index', $planId)
        ->with('success', "Concepto \"{$nombre}\" quitado del plan.");
}
}
