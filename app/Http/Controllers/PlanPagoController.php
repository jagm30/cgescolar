<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAsignacionPlanRequest;
use App\Http\Requests\StorePlanPagoRequest;
use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\Grupo;
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

    public function index(Request $request)
    {
        $planesPerPage = (int) $request->get('planes_per_page', 10);
        $asignacionesPerPage = (int) $request->get('asignaciones_per_page', 10);

        $cicloId = auth()->user()->ciclo_seleccionado_id ?? CicloEscolar::activo()->value('id');
        $planesPerPage = 15;
        $asignacionesPerPage = 10;

        $planes = PlanPago::with(['nivel', 'conceptos', 'politicasDescuentoActivas', 'politicaRecargoActiva'])
            ->withCount('asignaciones')
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('nivel_id'), fn ($q) => $q->where('nivel_id', $request->nivel_id))
            ->orderBy('nivel_id')
            ->orderBy('nombre')
            ->paginate($planesPerPage, ['*'], 'planes_page');

        if ($request->ajax()) {
            return response()->json($planes);
        }

        $niveles = NivelEscolar::activo()->get();
        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();
        $alumnos = Alumno::query()
            ->whereHas('inscripciones', function ($query) use ($cicloId) {
                $query->where('ciclo_id', $cicloId)->where('activo', true);
            })
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'nombre_completo' => $a->nombre_completo,
                ];
            });
        $grupos = Grupo::with(['grado.nivel'])
            ->whereHas('inscripciones', function ($query) use ($cicloId) {
                $query->where('ciclo_id', $cicloId)->where('activo', true);
            })
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get()
            ->map(function ($g) {
                return [
                    'id' => $g->id,
                    'nombre' => $g->nombre,
                    'grado' => $g->grado->nombre ?? '',
                    'nivel' => [
                        'nombre' => $g->grado->nivel->nombre ?? '',
                    ],
                ];
            });
        $asignaciones = AsignacionPlan::with(['plan.nivel', 'alumno', 'grupo.grado.nivel', 'nivel'])
            ->whereHas('plan', fn ($query) => $query->where('ciclo_id', $cicloId))
            ->latest('id')
            ->paginate($asignacionesPerPage, ['*'], 'asignaciones_page');
        $cicloActual = CicloEscolar::find($cicloId);
        $niveles = NivelEscolar::activo()->get();
        $conceptos = ConceptoCobro::activo()->orderBy('nombre')->get();

        // QUITAMOS 'ciclos' y 'cicloId' -> El Composer los inyecta solitos
        return view('planes.index', compact('planes', 'niveles', 'conceptos'));
    }

    public function show(int $id)
    {
        $plan = PlanPago::with([
            'ciclo',
            'nivel',
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

    /** POST /planes */
    public function store(StorePlanPagoRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $plan = PlanPago::create([
                'ciclo_id' => $data['ciclo_id'],
                'nivel_id' => $data['nivel_id'],
                'nombre' => $data['nombre'],
                'periodicidad' => $data['periodicidad'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'activo' => true,
            ]);

            foreach ($data['conceptos'] as $concepto) {
                PlanPagoConcepto::create([
                    'plan_id' => $plan->id,
                    'concepto_id' => $concepto['concepto_id'],
                    'monto' => $concepto['monto'],
                ]);
            }

            foreach ($data['descuentos'] ?? [] as $descuento) {
                PoliticaDescuento::create([
                    'plan_id' => $plan->id,
                    'nombre' => $descuento['nombre'],
                    'tipo_valor' => $descuento['tipo_valor'],
                    'valor' => $descuento['valor'],
                    'dia_limite' => $descuento['dia_limite'] ?? null,
                    'activo' => true,
                ]);
            }

            if (! empty($data['recargo'])) {
                PoliticaRecargo::create([
                    'plan_id' => $plan->id,
                    'dia_limite_pago' => $data['recargo']['dia_limite_pago'],
                    'tipo_recargo' => $data['recargo']['tipo_recargo'],
                    'valor' => $data['recargo']['valor'],
                    'tope_maximo' => $data['recargo']['tope_maximo'] ?? null,
                    'activo' => true,
                ]);
            }

            Auditoria::registrar('plan_pago', $plan->id, 'insert', null, $plan->toArray());
            DB::commit();

            session()->flash('success', "Plan '{$plan->nombre}' creado correctamente.");

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('planes.show', $plan->id),
                    'mensaje' => "Plan '{$plan->nombre}' creado correctamente.",
                ], 201);
            }

            return redirect()->route('planes.show', $plan->id);

            return $this->respuestaExito(
                redirectRoute: 'planes.show',
                // Cambiamos 'plane' por 'plan' y lo pasamos como un array simple
                redirectParams: ['plan' => $plan->id],
                jsonData: [
                    'plan' => $plan->load(['planPagoConceptos.concepto', 'politicasDescuento', 'politicasRecargo']),
                ],
                mensaje: "Plan '{$plan->nombre}' creado correctamente.",
                jsonStatus: 201,
                routeParams: [$plan->id]
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->respuestaError('Error al crear el plan: '.$e->getMessage());
        }
    }

    /** GET /planes/{id}/edit */
    public function edit(int $id)
    {
        $plan = PlanPago::with(['planPagoConceptos.concepto', 'politicasDescuento', 'politicasRecargo'])->findOrFail($id);
        $conceptos = ConceptoCobro::activo()->orderBy('nombre')->get();

        if (request()->ajax()) {
            return response()->json($plan);
        }

        // LIMPIEZA: El composer se encarga de los ciclos aquí también
        return view('planes.edit', compact('plan', 'conceptos'));
    }

    /** PUT /planes/{id} */
    public function update(Request $request, int $id)
    {
        $plan = PlanPago::findOrFail($id);
        $anterior = $plan->toArray();

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin' => ['sometimes', 'required', 'date', 'after:fecha_inicio'],
            'activo' => ['boolean'],
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
            redirectRoute: 'planes.asignar.index',
            jsonData: ['asignacion' => $asignacion->load('plan')],
            mensaje: 'Plan asignado correctamente.',
            jsonStatus: 201
        );
    }

    /** GET /planes/asignaciones */
    public function indexAsignaciones(Request $request)
    {
        $asignaciones = AsignacionPlan::with(['plan', 'alumno', 'grupo', 'nivel'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'data' => $asignaciones->map(function ($a) {
                    return [
                        'plan' => $a->plan->nombre,
                        'asignado_a' => $a->alumno?->nombre_completo ?? $a->grupo?->nombre ?? $a->nivel?->nombre ?? '-',
                        'origen' => ucfirst($a->origen),
                        'fecha_inicio' => $a->fecha_inicio?->format('d/m/Y') ?? '-',
                        'fecha_fin' => $a->fecha_fin?->format('d/m/Y') ?? '-',
                    ];
                })->all(),
                'pagination' => [
                    'current_page' => $asignaciones->currentPage(),
                    'last_page' => $asignaciones->lastPage(),
                    'total' => $asignaciones->total(),
                    'from' => $asignaciones->firstItem(),
                    'to' => $asignaciones->lastItem(),
                ],
            ]);
        }

        return view('planes.asignaciones', compact('asignaciones'));
    }

    /** GET /planes/asignacion/{alumnoId} — solo AJAX */
    public function asignacionDeAlumno(int $alumnoId)
    {
        // Se usa internamente para la lógica de búsqueda
        $cicloId = auth()->user()->ciclo_seleccionado_id ?? CicloEscolar::activo()->value('id');

        $inscripcion = Inscripcion::with('grupo.grado')
            ->where('alumno_id', $alumnoId)
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->first();

        if (! $inscripcion) {
            return response()->json(['message' => 'Sin inscripción activa en este ciclo.'], 404);
        }

        $nivelId = $inscripcion->grupo->grado->nivel_id;

        $asignacion = AsignacionPlan::with([
            'plan.planPagoConceptos.concepto',
            'plan.politicasDescuentoActivas',
            'plan.politicaRecargoActiva',
        ])
            ->where(function ($q) use ($alumnoId, $inscripcion, $nivelId) {
                $q->where(fn ($q) => $q->where('origen', 'individual')->where('alumno_id', $alumnoId))
                    ->orWhere(fn ($q) => $q->where('origen', 'grupo')->where('grupo_id', $inscripcion->grupo_id))
                    ->orWhere(fn ($q) => $q->where('origen', 'nivel')->where('nivel_id', $nivelId));
            })
            ->whereHas('plan', fn ($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
            ->orderByRaw("FIELD(origen, 'individual', 'grupo', 'nivel')")
            ->first();

        if (! $asignacion) {
            return response()->json(['message' => 'El alumno no tiene plan de pago asignado.'], 404);
        }

        return response()->json(['asignacion' => $asignacion, 'origen' => $asignacion->origen]);
    }

    public function clonarMasivo(Request $request)
    {
        // Valida que vengan IDs y el ciclo destino
        $request->validate([
            'plan_ids' => 'required|array',
            'ciclo_destino_id' => 'required|exists:ciclo_escolar,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->plan_ids as $id) {
                $original = PlanPago::with(['planPagoConceptos', 'politicasDescuento', 'politicaRecargo'])->find($id);
                if (! $original) {
                    continue;
                }

                // 1. Clona el Plan
                $nuevo = $original->replicate();
                $nuevo->ciclo_id = $request->ciclo_destino_id;
                // Opcional: Agregarle un prefijo al nombre
                $nuevo->nombre = $request->prefijo.' '.$original->nombre;
                $nuevo->save();

                // 2. Clona Conceptos, Descuentos y Recargo
                // Usamos la misma lógica del replicate() para cada relación
                foreach ($original->planPagoConceptos as $item) {
                    $c = $item->replicate();
                    $c->plan_id = $nuevo->id;
                    $c->save();
                }

                foreach ($original->politicasDescuento as $desc) {
                    $d = $desc->replicate();
                    $d->plan_id = $nuevo->id;
                    $d->save();
                }

                if ($original->politicaRecargo) {
                    $r = $original->politicaRecargo->replicate();
                    $r->plan_id = $nuevo->id;
                    $r->save();
                }
            }

            DB::commit();

            return back()->with('success', '¡Planes clonados correctamente al nuevo ciclo!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error en la clonación masiva: '.$e->getMessage());
        }
    }

    public function createAsignacion()
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $planes = PlanPago::with('planPagoConceptos.concepto')
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->get();

        // Transformar planes a estructura simples para JavaScript
        $planesData = $planes->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'conceptos' => $p->planPagoConceptos->map(function ($c) {
                    return [
                        'id' => $c->concepto_id,
                        'nombre' => $c->concepto->nombre,
                        'monto' => (float) $c->monto,
                    ];
                })->all(),
            ];
        })->all();

        $alumnos = Alumno::whereHas('inscripciones', function ($q) use ($cicloId) {
            $q->where('ciclo_id', $cicloId)->where('activo', true);
        })->get();

        $grupos = Grupo::with(['grado.nivel'])->get();
        $niveles = NivelEscolar::activo()->get();

        $asignaciones = AsignacionPlan::with(['plan', 'alumno', 'grupo', 'nivel'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('planes.asignar', compact(
            'planes',
            'planesData',
            'alumnos',
            'grupos',
            'niveles',
            'asignaciones'
        ));
    }
}
