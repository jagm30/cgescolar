<?php

namespace App\Http\Controllers;

use App\Enums\TipoInscripcion;
use App\Http\Requests\StoreAsignacionPlanRequest;
use App\Http\Requests\StorePlanPagoRequest;
use App\Models\Alumno;
use App\Models\AsignacionPlan;
use App\Models\AsignacionPlanConcepto;
use App\Models\Auditoria;
use App\Models\Cargo;
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
use Carbon\Carbon;
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

        return view('planes.index', compact(
            'planes',
            'ciclos',
            'niveles',
            'alumnos',
            'grupos',
            'asignaciones',
            'cicloActual',
            'planesPerPage',
            'asignacionesPerPage',
            'niveles',
            'conceptos'
        ));
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
        $validated = $request->validated();
        $conceptosSeleccionados = collect($request->input('conceptos', []))
            ->map(fn ($conceptoId) => (int) $conceptoId)
            ->unique()
            ->values();

        // Crear la asignación sin los conceptos
        DB::beginTransaction();

        try {
            $asignacionData = collect($validated)->except('conceptos')->toArray();
            $asignacion = AsignacionPlan::create($asignacionData);

            $planConceptos = PlanPagoConcepto::where('plan_id', $asignacion->plan_id)
                ->whereIn('id', $conceptosSeleccionados)
                ->get();

            foreach ($planConceptos as $planConcepto) {
                AsignacionPlanConcepto::create([
                    'asignacion_id' => $asignacion->id,
                    'concepto_id' => $planConcepto->concepto_id,
                    'monto' => $planConcepto->monto,
                ]);
            }

            $asignacion->load(['plan', 'conceptosSeleccionados']);
            $cargosGenerados = $this->generarCargosParaAsignacion($asignacion);

            Auditoria::registrar('asignacion_plan', $asignacion->id, 'insert', null, $asignacion->toArray());

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'planes.asignar.index',
                jsonData: [
                    'asignacion' => $asignacion->load('plan'),
                    'cargos_generados' => $cargosGenerados,
                ],
                mensaje: "Plan asignado correctamente. Se generaron {$cargosGenerados} cargos.",
                jsonStatus: 201
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->respuestaError('Error al asignar el plan: '.$e->getMessage());
        }
    }

    /** GET /planes/asignaciones */
    public function indexAsignaciones(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id ?? CicloEscolar::activo()->value('id');

        $planesAsignados = PlanPago::with('nivel')
            ->withCount('asignaciones')
            ->where('ciclo_id', $cicloId)
            ->whereHas('asignaciones')
            ->orderBy('nombre')
            ->paginate(10, ['*'], 'asignaciones_page');

        if ($request->ajax()) {
            return response()->json([
                'data' => $planesAsignados->map(function ($plan) {
                    return [
                        'plan' => $plan->nombre,
                        'nivel' => $plan->nivel?->nombre ?? '-',
                        'asignaciones' => $plan->asignaciones_count,
                        'fecha_inicio' => $plan->fecha_inicio?->format('d/m/Y') ?? '-',
                        'fecha_fin' => $plan->fecha_fin?->format('d/m/Y') ?? '-',
                    ];
                })->all(),
                'pagination' => [
                    'current_page' => $planesAsignados->currentPage(),
                    'last_page' => $planesAsignados->lastPage(),
                    'total' => $planesAsignados->total(),
                    'from' => $planesAsignados->firstItem(),
                    'to' => $planesAsignados->lastItem(),
                ],
            ]);
        }

        return view('planes.asignaciones', compact('planesAsignados'));
    }

    public function planesDisponibles(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $origen = $request->get('origen');
        $alumnoId = $request->integer('alumno_id');
        $grupoId = $request->integer('grupo_id');
        $nivelId = $request->integer('nivel_id');

        if (! in_array($origen, ['individual', 'grupo', 'nivel'], true)) {
            return response()->json([]);
        }

        if (($origen === 'individual' && ! $alumnoId)
            || ($origen === 'grupo' && ! $grupoId)
            || ($origen === 'nivel' && ! $nivelId)) {
            return response()->json([]);
        }

        $planIds = collect();
        $asignacionesQuery = AsignacionPlan::query()
            ->whereHas('plan', fn ($query) => $query->where('ciclo_id', $cicloId)->where('activo', true))
            ->whereHas('cargos');

        if ($origen === 'individual' && $alumnoId) {
            $inscripcion = Inscripcion::with('grupo.grado')
                ->where('alumno_id', $alumnoId)
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
                ->first();

            if (! $inscripcion) {
                return response()->json([]);
            }

            $nivelIdAlumno = $inscripcion->grupo?->grado?->nivel_id;

            $planIds = $asignacionesQuery->where(function ($query) use ($alumnoId, $inscripcion, $nivelIdAlumno) {
                $query->where(fn ($query) => $query->where('origen', 'individual')->where('alumno_id', $alumnoId))
                    ->orWhere(fn ($query) => $query->where('origen', 'grupo')->where('grupo_id', $inscripcion->grupo_id));

                if ($nivelIdAlumno) {
                    $query->orWhere(fn ($query) => $query->where('origen', 'nivel')->where('nivel_id', $nivelIdAlumno));
                }
            })->pluck('plan_id');
        } elseif ($origen === 'grupo' && $grupoId) {
            $grupo = Grupo::with('grado')->find($grupoId);
            $nivelIdGrupo = $grupo?->grado?->nivel_id;

            $planIds = $asignacionesQuery->where(function ($query) use ($grupoId, $nivelIdGrupo) {
                $query->where(fn ($query) => $query->where('origen', 'grupo')->where('grupo_id', $grupoId));

                if ($nivelIdGrupo) {
                    $query->orWhere(fn ($query) => $query->where('origen', 'nivel')->where('nivel_id', $nivelIdGrupo));
                }
            })->pluck('plan_id');
        } elseif ($origen === 'nivel' && $nivelId) {
            $planIds = $asignacionesQuery
                ->where('origen', 'nivel')
                ->where('nivel_id', $nivelId)
                ->pluck('plan_id');
        }

        $planIds = $planIds->unique()->values()->all();

        $planesDisponibles = PlanPago::where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->when(! empty($planIds), fn ($query) => $query->whereNotIn('id', $planIds))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'fecha_inicio', 'fecha_fin']);

        return response()->json($planesDisponibles->map(function ($plan) {
            return [
                'id' => $plan->id,
                'nombre' => $plan->nombre,
                'fecha_inicio' => $plan->fecha_inicio?->format('Y-m-d'),
                'fecha_fin' => $plan->fecha_fin?->format('Y-m-d'),
            ];
        }));
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
            ->orderByRaw('grupo_id IS NULL')
            ->first();

        if (! $inscripcion) {
            return response()->json(['message' => 'Sin inscripción activa en este ciclo.'], 404);
        }

        $nivelId = $inscripcion->grupo?->grado?->nivel_id;

        $asignaciones = AsignacionPlan::with([
            'conceptosSeleccionados.concepto',
            'plan.nivel',
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
            ->get();

        if ($asignaciones->isEmpty()) {
            return response()->json(['message' => 'El alumno no tiene planes de pago asignados.'], 404);
        }

        return response()->json(['asignaciones' => $asignaciones]);
    }

    public function clonarMasivo(Request $request)
    {
        $request->validate([
            'plan_ids' => 'required|array',
            'ciclo_destino_id' => 'required|exists:ciclo_escolar,id',
            // El sufijo es opcional, pero le damos un valor por defecto si es el mismo ciclo
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->plan_ids as $id) {
                $original = PlanPago::with(['planPagoConceptos', 'politicasDescuento', 'politicaRecargo'])->find($id);
                if (! $original) {
                    continue;
                }

                // 1. Replicamos el Plan
                $nuevo = $original->replicate();
                $nuevo->ciclo_id = $request->ciclo_destino_id;

                // ── LÓGICA INTELIGENTE DE NOMBRE ──
                if ($request->ciclo_destino_id == $original->ciclo_id) {
                    // Si es el mismo ciclo, forzamos un distintivo para evitar nombres idénticos
                    $sufijo = $request->filled('sufijo') ? $request->sufijo : '(COPIA)';
                    $nuevo->nombre = $original->nombre.' '.$sufijo;
                } else {
                    // Si es ciclo diferente, usamos el sufijo del usuario o el nombre original limpio
                    $nuevo->nombre = trim($original->nombre.' '.$request->sufijo);
                }

                $nuevo->save();

                // 2. Clona Relaciones (Conceptos, Descuentos y Recargos)
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

                // Opcional: Registrar en auditoría la clonación
                Auditoria::registrar('plan_pago', $nuevo->id, 'insert', null, ['nota' => "Clonado desde el ID: {$original->id}"]);
            }

            DB::commit();

            return back()->with('success', '¡Proceso de clonación completado con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error en la clonación: '.$e->getMessage());
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

        // Transformar planes a estructura simple para JavaScript
        $planesData = $planes->map(function ($p) {
            return [
                'id'           => $p->id,
                'nombre'       => $p->nombre,
                'periodicidad' => $p->periodicidad,
                'fecha_inicio' => $p->fecha_inicio?->format('Y-m-d'),
                'fecha_fin'    => $p->fecha_fin?->format('Y-m-d'),
                'conceptos'    => $p->planPagoConceptos->map(function ($c) {
                    return [
                        'id'          => $c->id,
                        'concepto_id' => $c->concepto_id,
                        'nombre'      => $c->concepto->nombre,
                        'tipo'        => $c->concepto->tipo,
                        'monto'       => (float) $c->monto,
                    ];
                })->all(),
            ];
        })->all();

        $alumnos = Alumno::where('estado', 'activo')->orderBy('ap_paterno')->orderBy('nombre')->get();

        $grupos = Grupo::with(['grado.nivel'])->get();
        $niveles = NivelEscolar::activo()->get();

        $asignaciones = AsignacionPlan::with(['plan', 'alumno', 'grupo', 'nivel'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $preAlumnoId = (int) request('alumno_id') ?: null;

        return view('planes.asignar', compact(
            'planes',
            'planesData',
            'alumnos',
            'grupos',
            'niveles',
            'asignaciones',
            'preAlumnoId'
        ));
    }

    private function generarCargosParaAsignacion(AsignacionPlan $asignacion): int
    {
        $asignacion->loadMissing(['plan', 'conceptosSeleccionados']);

        $plan = $asignacion->plan;

        // Respetar fechas personalizadas de la asignación; caer a las del plan si no se proporcionaron
        $fechaInicio = ($asignacion->fecha_inicio ?? $plan->fecha_inicio)->format('Y-m-d');
        $fechaFin    = ($asignacion->fecha_fin    ?? $plan->fecha_fin)->format('Y-m-d');

        $periodos = $this->calcularPeriodos($fechaInicio, $fechaFin, $plan->periodicidad);
        $inscripciones = $this->obtenerInscripcionesParaAsignacion($asignacion);
        $cargosAfectados = 0;

        foreach ($inscripciones as $inscripcion) {
            foreach ($asignacion->conceptosSeleccionados as $conceptoSeleccionado) {
                foreach ($periodos as $periodo) {
                    $cargo = Cargo::with('asignacion')
                        ->where('inscripcion_id', $inscripcion->id)
                        ->where('concepto_id', $conceptoSeleccionado->concepto_id)
                        ->where('periodo', $periodo['periodo'])
                        ->first();

                    $payload = [
                        'inscripcion_id' => $inscripcion->id,
                        'concepto_id' => $conceptoSeleccionado->concepto_id,
                        'asignacion_id' => $asignacion->id,
                        'generado_por' => auth()->id(),
                        'monto_original' => $conceptoSeleccionado->monto,
                        'fecha_vencimiento' => $periodo['vencimiento'],
                        'estado' => 'pendiente',
                        'periodo' => $periodo['periodo'],
                    ];

                    if (! $cargo) {
                        Cargo::create($payload);
                        $cargosAfectados++;

                        continue;
                    }

                    if ($this->puedeReemplazarCargoExistente($cargo, $asignacion)) {
                        $cargo->fill($payload);
                        $cargo->save();
                        $cargosAfectados++;
                    }
                }
            }
        }

        return $cargosAfectados;
    }

    private function obtenerInscripcionesParaAsignacion(AsignacionPlan $asignacion)
    {
        $cicloId = $asignacion->plan->ciclo_id;

        // Para asignaciones individuales en ciclos en configuración:
        // si el alumno aún no tiene inscripción en ese ciclo, crear una
        // inscripción anticipada automáticamente para que el cargo quede
        // correctamente vinculado al nuevo ciclo.
        if ($asignacion->origen === 'individual') {
            $asignacion->plan->loadMissing('ciclo');

            $tieneInscripcion = Inscripcion::where('alumno_id', $asignacion->alumno_id)
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
                ->exists();

            if (! $tieneInscripcion && $asignacion->plan->ciclo?->estado === 'configuracion') {
                Inscripcion::create([
                    'alumno_id' => $asignacion->alumno_id,
                    'ciclo_id' => $cicloId,
                    'grupo_id' => null,
                    'fecha' => now()->toDateString(),
                    'activo' => true,
                    'tipo' => TipoInscripcion::Anticipada,
                ]);
            }
        }

        return Inscripcion::query()
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->when($asignacion->origen === 'individual', fn ($query) => $query
                ->where('alumno_id', $asignacion->alumno_id)
                ->orderByRaw('grupo_id IS NULL')
                ->limit(1))
            ->when($asignacion->origen === 'grupo', fn ($query) => $query->where('grupo_id', $asignacion->grupo_id))
            ->when($asignacion->origen === 'nivel', fn ($query) => $query->whereHas(
                'grupo.grado',
                fn ($query) => $query->where('nivel_id', $asignacion->nivel_id)
            ))
            ->get();
    }

    private function puedeReemplazarCargoExistente(Cargo $cargo, AsignacionPlan $nuevaAsignacion): bool
    {
        if ((float) $cargo->saldo_abonado > 0 || $cargo->estado !== 'pendiente') {
            return false;
        }

        if (! $cargo->asignacion_id || (int) $cargo->asignacion_id === (int) $nuevaAsignacion->id) {
            return true;
        }

        return $this->prioridadOrigen($nuevaAsignacion->origen) < $this->prioridadOrigen($cargo->asignacion?->origen);
    }

    private function prioridadOrigen(?string $origen): int
    {
        return match ($origen) {
            'individual' => 1,
            'grupo' => 2,
            'nivel' => 3,
            default => 99,
        };
    }

    private function calcularPeriodos(string $fechaInicio, string $fechaFin, string $periodicidad): array
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        $periodos = [];

        if ($periodicidad === 'unico') {
            return [[
                'periodo' => $inicio->format('Y-m'),
                'vencimiento' => $inicio->copy()->day(10)->format('Y-m-d'),
            ]];
        }

        $intervalo = match ($periodicidad) {
            'mensual' => '1 month',
            'bimestral' => '2 months',
            'semestral' => '6 months',
            'anual' => '1 year',
            default => '1 month',
        };

        $actual = $inicio->copy();

        while ($actual->lte($fin)) {
            $periodos[] = [
                'periodo' => $actual->format('Y-m'),
                'vencimiento' => $actual->copy()->day(10)->format('Y-m-d'),
            ];
            $actual->add($intervalo);
        }

        return $periodos;
    }
}
