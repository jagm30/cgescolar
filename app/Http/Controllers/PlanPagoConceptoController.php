<?php

namespace App\Http\Controllers;

use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\ConceptoCobro;
use App\Models\Cargo; // Agregado para la validación
use App\Models\Auditoria; // Agregado para limpiar el código
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanPagoConceptoController extends Controller
{
    public function index(int $planId)
    {
        $plan = PlanPago::with([
            'conceptos' => fn($q) => $q->orderBy('tipo'),
        ])->findOrFail($planId);

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

    public function store(Request $request, int $planId)
    {
        $plan = PlanPago::findOrFail($planId);

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
            DB::beginTransaction();
            $registrosCreados = [];

            foreach ($request->conceptos as $item) {
                $yaExiste = PlanPagoConcepto::where('plan_id', $planId)
                    ->where('concepto_id', $item['concepto_id'])
                    ->exists();

                if ($yaExiste) continue; 

                $registro = PlanPagoConcepto::create([
                    'plan_id'     => $planId,
                    'concepto_id' => $item['concepto_id'],
                    'monto'       => $item['monto'],
                ]);

                // CORRECCIÓN: Nombre de tabla consistente
                Auditoria::registrar(
                    'plan_pago_concepto', $registro->id, 'insert',
                    null, $registro->toArray()
                );

                $registrosCreados[] = $registro;
            }

            DB::commit();

            $mensaje = count($registrosCreados) . " conceptos procesados correctamente.";
            
            if ($request->ajax()) {
                return response()->json(['message' => $mensaje, 'registros' => $registrosCreados], 201);
            }

            return redirect()->route('planes.conceptos.index', $planId)->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al procesar los conceptos: ' . $e->getMessage());
        }
    }

    public function update(Request $request, int $planId, int $id)
    {
        $registro = PlanPagoConcepto::where('plan_id', $planId)->findOrFail($id);
        $anterior = $registro->toArray();

        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $registro->update($data);

        Auditoria::registrar(
            'plan_pago_concepto', $registro->id, 'update',
            $anterior, $registro->fresh()->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message'  => 'Monto actualizado.',
                'registro' => $registro->fresh()->load('concepto'),
            ]);
        }

        return redirect()->route('planes.conceptos.index', $planId)->with('success', 'Monto actualizado correctamente.');
    }

    public function destroy(int $planId, int $id)
    {
        // CORRECCIÓN 1: Necesitamos el objeto Plan para validar el ciclo_id
        $plan = PlanPago::findOrFail($planId);

        // CORRECCIÓN 2: Buscar el registro
        $registro = PlanPagoConcepto::where('plan_id', $planId)
            ->where('concepto_id', $id) 
            ->first();

        if (!$registro) {
            $registro = PlanPagoConcepto::find($id);
        }

        if (!$registro) {
            abort(404, "No se encontró el concepto asignado a este plan.");
        }

        $registro->load('concepto');

        // CORRECCIÓN 3: Validar cargos usando el ciclo del plan
        $tieneCargos = Cargo::where('concepto_id', $registro->concepto_id)
            ->whereHas('inscripcion', fn($q) => 
                $q->where('ciclo_id', $plan->ciclo_id) // Ahora $plan sí existe
            )->exists();

        if ($tieneCargos) {
            $msg = "No se puede quitar \"{$registro->concepto->nombre}\": ya se generaron cargos en este ciclo.";
            return request()->ajax() ? response()->json(['message' => $msg], 422) : back()->with('error', $msg);
        }

        $nombre = $registro->concepto->nombre;
        
        // Opcional: Auditoría de eliminación
        Auditoria::registrar('plan_pago_concepto', $registro->id, 'delete', $registro->toArray(), null);
        
        $registro->delete();

        return redirect()->route('planes.conceptos.index', $planId)
            ->with('success', "Concepto \"{$nombre}\" quitado del plan.");
    }
}