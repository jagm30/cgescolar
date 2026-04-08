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
     * Agrega un concepto al plan con su monto.
     */
    public function store(Request $request, int $planId)
    {
        $plan = PlanPago::findOrFail($planId);

        $data = $request->validate([
            'concepto_id' => ['required', 'integer', 'exists:concepto_cobro,id'],
            'monto'       => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ], [
            'concepto_id.exists' => 'El concepto seleccionado no existe.',
            'monto.min'          => 'El monto debe ser mayor a cero.',
        ]);

        // Verificar que no esté ya asignado
        $yaExiste = PlanPagoConcepto::where('plan_id', $planId)
            ->where('concepto_id', $data['concepto_id'])
            ->exists();

        if ($yaExiste) {
            return response()->json([
                'message' => 'Este concepto ya está asignado al plan.',
            ], 422);
        }

        $registro = PlanPagoConcepto::create([
            'plan_id'     => $planId,
            'concepto_id' => $data['concepto_id'],
            'monto'       => $data['monto'],
        ]);

        $registro->load('concepto');

        \App\Models\Auditoria::registrar(
            'plan_pago_concepto', $registro->id, 'insert',
            null, $registro->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message'  => "Concepto \"{$registro->concepto->nombre}\" agregado al plan.",
                'registro' => $registro,
            ], 201);
        }

        return redirect()->route('planes.conceptos.index', $planId)
            ->with('success', "Concepto agregado correctamente.");
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
        $registro = PlanPagoConcepto::where('plan_id', $planId)
            ->with('concepto')
            ->findOrFail($id);

        // Verificar que no se hayan generado cargos con este concepto en este plan
        $tieneCargos = \App\Models\Cargo::where('concepto_id', $registro->concepto_id)
            ->whereHas('inscripcion.grupo', fn($q) =>
                $q->where('ciclo_id', $registro->plan->ciclo_id)
            )->exists();

        if ($tieneCargos) {
            $msg = "No se puede quitar \"{$registro->concepto->nombre}\": ya se generaron cargos con este concepto.";

            if (request()->ajax()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $nombre = $registro->concepto->nombre ?? 'concepto';
        \App\Models\Auditoria::registrar('plan_pago_concepto', $registro->id, 'delete', $registro->toArray(), null);
        $registro->delete();

        if (request()->ajax()) {
            return response()->json(['message' => "Concepto \"{$nombre}\" quitado del plan."]);
        }

        return redirect()->route('planes.conceptos.index', $planId)
            ->with('success', "Concepto \"{$nombre}\" quitado del plan.");
    }
}
