<?php

namespace App\Http\Controllers;

use App\Models\PlanPago;
use App\Models\PoliticaDescuento;
use App\Models\PoliticaRecargo;
use Illuminate\Http\Request;

/**
 * Maneja políticas de descuento Y de recargo de un plan.
 * Ambas están relacionadas al plan_pago y se gestionan juntas.
 *
 * Rutas:
 *   GET    /planes/{planId}/politicas                → index()
 *   POST   /planes/{planId}/politicas/descuento      → storeDescuento()
 *   PUT    /planes/{planId}/politicas/descuento/{id} → updateDescuento()
 *   DELETE /planes/{planId}/politicas/descuento/{id} → destroyDescuento()
 *   POST   /planes/{planId}/politicas/recargo        → storeRecargo()
 *   PUT    /planes/{planId}/politicas/recargo/{id}   → updateRecargo()
 *   DELETE /planes/{planId}/politicas/recargo/{id}   → destroyRecargo()
 */
class PoliticaController extends Controller
{
    // ══════════════════════════════════════════════════
    // INDEX — muestra descuentos y recargo del plan
    // ══════════════════════════════════════════════════

    /** GET /planes/{planId}/politicas */
    public function index(int $planId)
    {
        $plan = PlanPago::with([
            'politicasDescuento' => fn($q) => $q->orderBy('nombre'),
            'politicaRecargo',
        ])->findOrFail($planId);

        if (request()->ajax()) {
            return response()->json([
                'plan'                => $plan,
                'politicas_descuento' => $plan->politicasDescuento,
                'politica_recargo'    => $plan->politicaRecargo,
            ]);
        }

        return view('planes.politicas', compact('plan'));
    }

    // ══════════════════════════════════════════════════
    // DESCUENTOS
    // ══════════════════════════════════════════════════

    /** POST /planes/{planId}/politicas/descuento */
    public function storeDescuento(Request $request, int $planId)
    {
        PlanPago::findOrFail($planId); // valida que el plan exista

        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'tipo_valor' => ['required', 'in:porcentaje,monto_fijo'],
            'valor'      => ['required', 'numeric', 'min:0.01'],
            'dia_limite' => ['nullable', 'integer', 'min:1', 'max:31'],
            'activo'     => ['boolean'],
        ], [
            'tipo_valor.in' => 'El tipo debe ser "porcentaje" o "monto_fijo".',
            'valor.min'     => 'El valor debe ser mayor a cero.',
            'dia_limite.min'=> 'El día límite debe ser entre 1 y 31.',
        ]);

        // Validación extra: porcentaje no puede superar 100
        if ($data['tipo_valor'] === 'porcentaje' && $data['valor'] > 100) {
            if ($request->ajax()) {
                return response()->json(['message' => 'El porcentaje no puede ser mayor a 100.'], 422);
            }
            return back()->withErrors(['valor' => 'El porcentaje no puede ser mayor a 100.'])->withInput();
        }

        $politica = PoliticaDescuento::create([
            'plan_id'    => $planId,
            'nombre'     => $data['nombre'],
            'tipo_valor' => $data['tipo_valor'],
            'valor'      => $data['valor'],
            'dia_limite' => $data['dia_limite'] ?? null,
            'activo'     => $data['activo'] ?? true,
        ]);

        \App\Models\Auditoria::registrar(
            'politica_descuento', $politica->id, 'insert',
            null, $politica->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message'  => "Descuento \"{$politica->nombre}\" creado.",
                'politica' => $politica,
            ], 201);
        }

        return redirect()->route('planes.politicas.index', $planId)
            ->with('success', "Descuento \"{$politica->nombre}\" creado correctamente.");
    }

    /** PUT /planes/{planId}/politicas/descuento/{id} */
    public function updateDescuento(Request $request, int $planId, int $id)
    {
        $politica = PoliticaDescuento::where('plan_id', $planId)->findOrFail($id);
        $anterior = $politica->toArray();

        $data = $request->validate([
            'nombre'     => ['required', 'string', 'max:255'],
            'tipo_valor' => ['required', 'in:porcentaje,monto_fijo'],
            'valor'      => ['required', 'numeric', 'min:0.01'],
            'dia_limite' => ['nullable', 'integer', 'min:1', 'max:31'],
            'activo'     => ['boolean'],
        ]);

        if ($data['tipo_valor'] === 'porcentaje' && $data['valor'] > 100) {
            if ($request->ajax()) {
                return response()->json(['message' => 'El porcentaje no puede ser mayor a 100.'], 422);
            }
            return back()->withErrors(['valor' => 'El porcentaje no puede ser mayor a 100.'])->withInput();
        }

        $politica->update($data);

        \App\Models\Auditoria::registrar(
            'politica_descuento', $politica->id, 'update',
            $anterior, $politica->fresh()->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message'  => "Descuento \"{$politica->nombre}\" actualizado.",
                'politica' => $politica->fresh(),
            ]);
        }

        return redirect()->route('planes.politicas.index', $planId)
            ->with('success', "Descuento actualizado correctamente.");
    }

    /** DELETE /planes/{planId}/politicas/descuento/{id} */
    public function destroyDescuento(int $planId, int $id)
    {
        $politica = PoliticaDescuento::where('plan_id', $planId)->findOrFail($id);
        $nombre   = $politica->nombre;

        \App\Models\Auditoria::registrar(
            'politica_descuento', $politica->id, 'delete',
            $politica->toArray(), null
        );

        $politica->delete();

        if (request()->ajax()) {
            return response()->json(['message' => "Descuento \"{$nombre}\" eliminado."]);
        }

        return redirect()->route('planes.politicas.index', $planId)
            ->with('success', "Descuento \"{$nombre}\" eliminado.");
    }

    // ══════════════════════════════════════════════════
    // RECARGO
    // Un plan debe tener como máximo UNA política de recargo activa.
    // ══════════════════════════════════════════════════

    /** POST /planes/{planId}/politicas/recargo */
    public function storeRecargo(Request $request, int $planId)
    {
        PlanPago::findOrFail($planId);

        // Solo puede haber un recargo activo por plan
        $recargoActivo = PoliticaRecargo::where('plan_id', $planId)
            ->where('activo', true)
            ->exists();

        if ($recargoActivo) {
            $msg = 'El plan ya tiene una política de recargo activa. Desactívala antes de crear una nueva.';

            if ($request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $data = $request->validate([
            'dia_limite_pago' => ['required', 'integer', 'min:1', 'max:31'],
            'tipo_recargo'    => ['required', 'in:porcentaje,monto_fijo'],
            'valor'           => ['required', 'numeric', 'min:0.01'],
            'tope_maximo'     => ['nullable', 'numeric', 'min:0'],
            'activo'          => ['boolean'],
        ], [
            'dia_limite_pago.required' => 'El día límite de pago es obligatorio.',
            'tipo_recargo.in'          => 'El tipo debe ser "porcentaje" o "monto_fijo".',
            'valor.min'                => 'El valor debe ser mayor a cero.',
        ]);

        if ($data['tipo_recargo'] === 'porcentaje' && $data['valor'] > 100) {
            if ($request->ajax()) {
                return response()->json(['message' => 'El porcentaje de recargo no puede ser mayor a 100.'], 422);
            }
            return back()->withErrors(['valor' => 'El porcentaje de recargo no puede ser mayor a 100.'])->withInput();
        }

        $recargo = PoliticaRecargo::create([
            'plan_id'         => $planId,
            'dia_limite_pago' => $data['dia_limite_pago'],
            'tipo_recargo'    => $data['tipo_recargo'],
            'valor'           => $data['valor'],
            'tope_maximo'     => $data['tope_maximo'] ?? null,
            'activo'          => $data['activo'] ?? true,
        ]);

        \App\Models\Auditoria::registrar(
            'politica_recargo', $recargo->id, 'insert',
            null, $recargo->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Política de recargo creada.',
                'recargo' => $recargo,
            ], 201);
        }

        return redirect()->route('planes.politicas.index', $planId)
            ->with('success', 'Política de recargo creada correctamente.');
    }

    /** PUT /planes/{planId}/politicas/recargo/{id} */
    public function updateRecargo(Request $request, int $planId, int $id)
    {
        $recargo  = PoliticaRecargo::where('plan_id', $planId)->findOrFail($id);
        $anterior = $recargo->toArray();

        $data = $request->validate([
            'dia_limite_pago' => ['required', 'integer', 'min:1', 'max:31'],
            'tipo_recargo'    => ['required', 'in:porcentaje,monto_fijo'],
            'valor'           => ['required', 'numeric', 'min:0.01'],
            'tope_maximo'     => ['nullable', 'numeric', 'min:0'],
            'activo'          => ['boolean'],
        ]);

        if ($data['tipo_recargo'] === 'porcentaje' && $data['valor'] > 100) {
            if ($request->ajax()) {
                return response()->json(['message' => 'El porcentaje de recargo no puede ser mayor a 100.'], 422);
            }
            return back()->withErrors(['valor' => 'El porcentaje de recargo no puede ser mayor a 100.'])->withInput();
        }

        $recargo->update($data);

        \App\Models\Auditoria::registrar(
            'politica_recargo', $recargo->id, 'update',
            $anterior, $recargo->fresh()->toArray()
        );

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Política de recargo actualizada.',
                'recargo' => $recargo->fresh(),
            ]);
        }

        return redirect()->route('planes.politicas.index', $planId)
            ->with('success', 'Política de recargo actualizada correctamente.');
    }

    /** DELETE /planes/{planId}/politicas/recargo/{id} */
    public function destroyRecargo(int $planId, int $id)
    {
        $recargo = PoliticaRecargo::where('plan_id', $planId)->findOrFail($id);

        \App\Models\Auditoria::registrar(
            'politica_recargo', $recargo->id, 'delete',
            $recargo->toArray(), null
        );

        $recargo->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Política de recargo eliminada.']);
        }

        return redirect()->route('planes.politicas.index', $planId)
            ->with('success', 'Política de recargo eliminada.');
    }
}
