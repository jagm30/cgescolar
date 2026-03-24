<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanPagoRequest;
use App\Http\Requests\StoreAsignacionPlanRequest;
use App\Models\AsignacionPlan;
use App\Models\Auditoria;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\PoliticaDescuento;
use App\Models\PoliticaRecargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanPagoController extends Controller
{
    /** GET /planes */
    public function index(Request $request): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $planes = PlanPago::with(['nivel', 'conceptos', 'politicasDescuentoActivas', 'politicaRecargoActiva'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('nivel_id'), fn($q) => $q->where('nivel_id', $request->nivel_id))
            ->when($request->filled('activo'),   fn($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('nivel_id')
            ->get();

        return response()->json($planes);
    }

    /** GET /planes/{id} */
    public function show(int $id): JsonResponse
    {
        $plan = PlanPago::with([
            'ciclo',
            'nivel',
            'planPagoConceptos.concepto',
            'politicasDescuento',
            'politicasRecargo',
            'asignaciones',
        ])->findOrFail($id);

        return response()->json($plan);
    }

    /**
     * POST /planes
     * Crea plan con conceptos, políticas de descuento y recargo
     * en una sola transacción.
     */
    public function store(StorePlanPagoRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // ── Plan ──────────────────────────────────────
            $plan = PlanPago::create([
                'ciclo_id'    => $data['ciclo_id'],
                'nivel_id'    => $data['nivel_id'],
                'nombre'      => $data['nombre'],
                'periodicidad'=> $data['periodicidad'],
                'fecha_inicio'=> $data['fecha_inicio'],
                'fecha_fin'   => $data['fecha_fin'],
                'activo'      => true,
            ]);

            // ── Conceptos ─────────────────────────────────
            foreach ($data['conceptos'] as $concepto) {
                PlanPagoConcepto::create([
                    'plan_id'    => $plan->id,
                    'concepto_id'=> $concepto['concepto_id'],
                    'monto'      => $concepto['monto'],
                ]);
            }

            // ── Políticas de descuento ────────────────────
            foreach ($data['descuentos'] ?? [] as $descuento) {
                PoliticaDescuento::create([
                    'plan_id'    => $plan->id,
                    'nombre'     => $descuento['nombre'],
                    'tipo_valor' => $descuento['tipo_valor'],
                    'valor'      => $descuento['valor'],
                    'dia_limite' => $descuento['dia_limite'] ?? null,
                    'activo'     => true,
                ]);
            }

            // ── Política de recargo (máximo 1) ─────────────
            if (!empty($data['recargo'])) {
                PoliticaRecargo::create([
                    'plan_id'         => $plan->id,
                    'dia_limite_pago' => $data['recargo']['dia_limite_pago'],
                    'tipo_recargo'    => $data['recargo']['tipo_recargo'],
                    'valor'           => $data['recargo']['valor'],
                    'tope_maximo'     => $data['recargo']['tope_maximo'] ?? null,
                    'activo'          => true,
                ]);
            }

            Auditoria::registrar('plan_pago', $plan->id, 'insert', null, $plan->toArray());

            DB::commit();

            return response()->json(
                $plan->load(['planPagoConceptos.concepto', 'politicasDescuento', 'politicasRecargo']),
                201
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el plan: ' . $e->getMessage()], 500);
        }
    }

    /** DELETE /planes/{id} — solo desactiva, no elimina */
    public function destroy(int $id): JsonResponse
    {
        $this->soloAdmin();

        $plan = PlanPago::findOrFail($id);

        // Verificar que no tenga asignaciones activas
        if ($plan->asignaciones()->exists()) {
            return response()->json([
                'message' => 'No se puede desactivar el plan porque tiene asignaciones activas.',
            ], 422);
        }

        $plan->update(['activo' => false]);

        Auditoria::registrar('plan_pago', $plan->id, 'update', ['activo' => true], ['activo' => false]);

        return response()->json(['message' => 'Plan desactivado correctamente.']);
    }

    /**
     * POST /planes/asignar
     * Asigna un plan a alumno, grupo o nivel.
     */
    public function asignar(StoreAsignacionPlanRequest $request): JsonResponse
    {
        $this->soloAdmin();

        $data = $request->validated();

        $asignacion = AsignacionPlan::create($data);

        Auditoria::registrar('asignacion_plan', $asignacion->id, 'insert', null, $asignacion->toArray());

        return response()->json($asignacion->load('plan'), 201);
    }

    /**
     * GET /planes/asignacion/{alumnoId}
     * Obtiene el plan vigente de un alumno según jerarquía:
     * individual > grupo > nivel.
     */
    public function asignacionDeAlumno(int $alumnoId): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $inscripcion = \App\Models\Inscripcion::with('grupo.grado')
            ->where('alumno_id', $alumnoId)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->first();

        if (!$inscripcion) {
            return response()->json(['message' => 'El alumno no tiene inscripción activa en este ciclo.'], 404);
        }

        $nivelId = $inscripcion->grupo->grado->nivel_id;

        // Prioridad: individual > grupo > nivel
        $asignacion = AsignacionPlan::with(['plan.planPagoConceptos.concepto', 'plan.politicasDescuentoActivas', 'plan.politicaRecargoActiva'])
            ->where(function ($q) use ($alumnoId, $inscripcion, $nivelId) {
                $q->where(fn($q) => $q->where('origen', 'individual')->where('alumno_id', $alumnoId))
                  ->orWhere(fn($q) => $q->where('origen', 'grupo')->where('grupo_id', $inscripcion->grupo_id))
                  ->orWhere(fn($q) => $q->where('origen', 'nivel')->where('nivel_id', $nivelId));
            })
            ->whereHas('plan', fn($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
            ->orderByRaw("FIELD(origen, 'individual', 'grupo', 'nivel')")
            ->first();

        if (!$asignacion) {
            return response()->json(['message' => 'El alumno no tiene plan de pago asignado.'], 404);
        }

        return response()->json([
            'asignacion' => $asignacion,
            'origen'     => $asignacion->origen,
        ]);
    }

    // ── Helper ───────────────────────────────────────────

    private function soloAdmin(): void
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }
}
