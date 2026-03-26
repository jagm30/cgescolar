<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAsignacionPlanRequest;
use App\Http\Requests\StorePlanPagoRequest;
use App\Models\AsignacionPlan;
use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\PlanPago;
use App\Models\PlanPagoConcepto;
use App\Models\PoliticaDescuento;
use App\Models\PoliticaRecargo;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanPagoController extends Controller
{
    use RespondsWithJson;

    /** GET /planes */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $planes = PlanPago::with(['nivel', 'conceptos', 'politicasDescuentoActivas', 'politicaRecargoActiva'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('nivel_id'), fn($q) => $q->where('nivel_id', $request->nivel_id))
            ->orderBy('nivel_id')
            ->get();

        if ($request->ajax()) {
            return response()->json($planes);
        }

        $niveles = NivelEscolar::activo()->get();

        return view('planes.index', compact('planes', 'niveles'));
    }

    /** GET /planes/{id} */
    public function show(int $id)
    {
        $plan = PlanPago::with([
            'ciclo', 'nivel',
            'planPagoConceptos.concepto',
            'politicasDescuento',
            'politicasRecargo',
            'asignaciones',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($plan);
        }

        return view('planes.show', compact('plan'));
    }

    /** GET /planes/create */
    public function create()
    {
        $cicloId  = auth()->user()->ciclo_seleccionado_id ?? CicloEscolar::activo()->value('id');
        $ciclos   = CicloEscolar::orderByDesc('fecha_inicio')->get();
        $niveles  = NivelEscolar::activo()->get();
        $conceptos = ConceptoCobro::activo()->orderBy('nombre')->get();

        return view('planes.create', compact('ciclos', 'niveles', 'conceptos', 'cicloId'));
    }

    /** POST /planes */
    public function store(StorePlanPagoRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $plan = PlanPago::create([
                'ciclo_id'    => $data['ciclo_id'],
                'nivel_id'    => $data['nivel_id'],
                'nombre'      => $data['nombre'],
                'periodicidad'=> $data['periodicidad'],
                'fecha_inicio'=> $data['fecha_inicio'],
                'fecha_fin'   => $data['fecha_fin'],
                'activo'      => true,
            ]);

            foreach ($data['conceptos'] as $concepto) {
                PlanPagoConcepto::create([
                    'plan_id'    => $plan->id,
                    'concepto_id'=> $concepto['concepto_id'],
                    'monto'      => $concepto['monto'],
                ]);
            }

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

            return $this->respuestaExito(
                redirectRoute: 'planes.show',
                jsonData: ['plan' => $plan->load(['planPagoConceptos.concepto', 'politicasDescuento', 'politicasRecargo'])],
                mensaje: "Plan '{$plan->nombre}' creado correctamente.",
                jsonStatus: 201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respuestaError('Error al crear el plan: ' . $e->getMessage());
        }
    }

    /** GET /planes/{id}/edit */
    public function edit(int $id)
    {
        $plan     = PlanPago::with(['planPagoConceptos.concepto', 'politicasDescuento', 'politicasRecargo'])->findOrFail($id);
        $conceptos = ConceptoCobro::activo()->orderBy('nombre')->get();

        if (request()->ajax()) {
            return response()->json($plan);
        }

        return view('planes.edit', compact('plan', 'conceptos'));
    }

    /** PUT /planes/{id} — solo nombre y fechas */
    public function update(Request $request, int $id)
    {
        $plan     = PlanPago::findOrFail($id);
        $anterior = $plan->toArray();

        $data = $request->validate([
            'nombre'       => ['sometimes', 'required', 'string', 'max:200'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin'    => ['sometimes', 'required', 'date', 'after:fecha_inicio'],
            'activo'       => ['boolean'],
        ]);

        $plan->update($data);
        Auditoria::registrar('plan_pago', $plan->id, 'update', $anterior, $plan->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'planes.index',
            jsonData: ['plan' => $plan->fresh()],
            mensaje: "Plan '{$plan->nombre}' actualizado correctamente."
        );
    }

    /** DELETE /planes/{id} */
    public function destroy(int $id)
    {
        $plan = PlanPago::findOrFail($id);

        if ($plan->asignaciones()->exists()) {
            return $this->respuestaError('No se puede desactivar el plan porque tiene asignaciones activas.');
        }

        $plan->update(['activo' => false]);
        Auditoria::registrar('plan_pago', $plan->id, 'update', ['activo' => true], ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'planes.index',
            mensaje: "Plan '{$plan->nombre}' desactivado correctamente."
        );
    }

    /** POST /planes/asignar */
    public function asignar(StoreAsignacionPlanRequest $request)
    {
        $asignacion = AsignacionPlan::create($request->validated());
        Auditoria::registrar('asignacion_plan', $asignacion->id, 'insert', null, $asignacion->toArray());

        return $this->respuestaExito(
            redirectRoute: 'planes.index',
            jsonData: ['asignacion' => $asignacion->load('plan')],
            mensaje: 'Plan asignado correctamente.',
            jsonStatus: 201
        );
    }

    /** GET /planes/asignacion/{alumnoId} — solo AJAX */
    public function asignacionDeAlumno(int $alumnoId)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $inscripcion = Inscripcion::with('grupo.grado')
            ->where('alumno_id', $alumnoId)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->first();

        if (!$inscripcion) {
            return response()->json(['message' => 'Sin inscripción activa en este ciclo.'], 404);
        }

        $nivelId = $inscripcion->grupo->grado->nivel_id;

        $asignacion = AsignacionPlan::with([
                'plan.planPagoConceptos.concepto',
                'plan.politicasDescuentoActivas',
                'plan.politicaRecargoActiva',
            ])
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

        return response()->json(['asignacion' => $asignacion, 'origen' => $asignacion->origen]);
    }
}
