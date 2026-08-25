<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCondonacionRequest;
use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\Condonacion;
use App\Models\DescuentoCargo;
use App\Models\PlanPago;
use App\Services\CondonacionService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CondonacionController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly CondonacionService $service) {}

    /** GET /condonaciones */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $query = Condonacion::with(['alumno', 'ciclo', 'creadoPor', 'detalles.cargo.asignacion.plan:id,nombre'])
            ->where('ciclo_id', $cicloId)
            ->when($request->filled('alumno_id'), fn ($q) => $q->where('alumno_id', $request->alumno_id))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('creado_at');

        $condonaciones = $query->paginate((int) $request->get('per_page', 20));

        $alumnos = Alumno::query()
            ->whereHas('inscripciones', fn ($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
            ->orderBy('ap_paterno')->orderBy('nombre')
            ->get();

        return view('condonaciones.index', compact('condonaciones', 'alumnos', 'cicloId'));
    }

    /** GET /condonaciones/crear */
    public function create()
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id')
            ?? CicloEscolar::orderByDesc('fecha_inicio')->value('id');

        $cicloActual = $cicloId ? CicloEscolar::find($cicloId) : null;

        $alumnos = Alumno::query()
            ->whereHas('inscripciones', fn ($q) => $q->where('ciclo_id', $cicloId)->where('activo', true))
            ->orderBy('ap_paterno')->orderBy('nombre')
            ->get();

        $planes = PlanPago::where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('condonaciones.create', compact('alumnos', 'cicloActual', 'planes'));
    }

    /** POST /condonaciones */
    public function store(StoreCondonacionRequest $request): JsonResponse|RedirectResponse
    {
        $condonacion = $this->service->crear($request->validated());

        return $this->respuestaExito(
            redirectRoute: 'condonaciones.show',
            routeParams: [$condonacion->id],
            mensaje: 'Condonación registrada correctamente.',
            jsonData: ['id' => $condonacion->id]
        );
    }

    /** GET /condonaciones/{id} */
    public function show(int $id)
    {
        $condonacion = Condonacion::with([
            'alumno',
            'ciclo',
            'creadoPor',
            'detalles.cargo.concepto',
            'detalles.descuentoCargo',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($condonacion);
        }

        return view('condonaciones.show', compact('condonacion'));
    }

    /** DELETE /condonaciones/{id} — cancela la condonación */
    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        $condonacion = Condonacion::with('detalles')->findOrFail($id);

        if ($condonacion->estado === 'cancelada') {
            return $this->respuestaError('Esta condonación ya fue cancelada.');
        }

        $this->service->cancelar($condonacion);

        return $this->respuestaExito(
            redirectRoute: 'condonaciones.index',
            mensaje: 'Condonación cancelada. Los descuentos han sido revertidos.'
        );
    }

    /** POST /condonaciones/masiva */
    public function storeMasiva(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plan_pago,id'],
            'ciclo_id' => ['required', 'integer', 'exists:ciclo_escolar,id'],
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
            'alumno_ids' => ['required', 'array', 'min:1'],
            'alumno_ids.*' => ['required', 'integer', 'exists:alumno,id'],
            'conceptos' => ['required', 'array', 'min:1'],
            'conceptos.*.concepto_id' => ['required', 'integer', 'exists:concepto_cobro,id'],
            'conceptos.*.monto' => ['required', 'numeric', 'min:0'],
        ], [
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'alumno_ids.required' => 'Debes seleccionar al menos un alumno.',
            'alumno_ids.min' => 'Debes seleccionar al menos un alumno.',
        ]);

        // El ciclo correcto es el del plan, no el del formulario (que depende del ciclo seleccionado
        // por el usuario y puede diferir del ciclo al que pertenece el plan elegido).
        $data['ciclo_id'] = PlanPago::where('id', $data['plan_id'])->value('ciclo_id');

        // Conceptos con monto 0 se descartan antes de llegar al service
        $data['conceptos'] = array_values(
            array_filter($data['conceptos'], fn ($c) => (float) $c['monto'] > 0)
        );

        if (empty($data['conceptos'])) {
            return $this->respuestaError('Debes indicar un monto mayor a $0.00 en al menos un concepto.');
        }

        $resultado = $this->service->crearMasiva($data);
        $creadas   = $resultado['creadas'];
        $omitidos  = $resultado['omitidos'];

        // Cargar nombres de los omitidos (aplica tanto si hubo éxitos como si no)
        $nombresOmitidos = [];
        if (! empty($omitidos)) {
            $nombresOmitidos = Alumno::whereIn('id', $omitidos)
                ->orderBy('ap_paterno')->orderBy('nombre')
                ->get(['nombre', 'ap_paterno', 'ap_materno'])
                ->map(fn ($a) => trim("{$a->nombre} {$a->ap_paterno} {$a->ap_materno}"))
                ->all();
        }

        // Ningún cargo aplicable en ningún alumno seleccionado
        if ($creadas === 0) {
            $avisoMensaje = empty($nombresOmitidos)
                ? 'No se encontraron cargos pendientes para los alumnos seleccionados.'
                : 'Ningún alumno seleccionado tiene cargos pendientes para los conceptos indicados.';

            if (request()->ajax()) {
                return response()->json([
                    'message'  => $avisoMensaje,
                    'omitidos' => $nombresOmitidos,
                ], 422);
            }

            return redirect()->route('condonaciones.index')
                ->with('error', $avisoMensaje)
                ->with('omitidos_masiva', $nombresOmitidos);
        }

        $mensaje = "Se registraron {$creadas} condonación(es) correctamente.";

        if (request()->ajax()) {
            return response()->json([
                'message'               => $mensaje,
                'condonaciones_creadas' => $creadas,
                'omitidos'              => $nombresOmitidos,
            ]);
        }

        return redirect()->route('condonaciones.index')
            ->with('success', $mensaje)
            ->with('omitidos_masiva', $nombresOmitidos);
    }

    /**
     * GET /condonaciones/cargos-alumno/{alumnoId}  (AJAX)
     * Devuelve los cargos pendientes o parciales del alumno en el ciclo activo.
     */
    public function cargosAlumno(int $alumnoId, Request $request): JsonResponse
    {
        $cicloId = $request->input('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $cargos = Cargo::with('concepto')
            ->whereHas('inscripcion', fn ($q) => $q
                ->where('alumno_id', $alumnoId)
                ->where('ciclo_id', $cicloId)
            )
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn (Cargo $cargo) => [
                'id' => $cargo->id,
                'etiqueta' => $cargo->etiqueta,
                'periodo' => $cargo->periodo,
                'monto_original' => (float) $cargo->monto_original,
                'saldo_abonado' => $cargo->saldo_abonado,
                'descuentos_previos' => (float) DescuentoCargo::where('cargo_id', $cargo->id)->sum('monto_aplicado'),
                'saldo_pendiente' => round(
                    (float) $cargo->monto_original
                    - $cargo->saldo_abonado
                    - (float) DescuentoCargo::where('cargo_id', $cargo->id)->sum('monto_aplicado'),
                    2
                ),
                'fecha_vencimiento' => $cargo->fecha_vencimiento?->format('d/m/Y'),
                'estado' => $cargo->estado,
            ]);

        return response()->json($cargos);
    }
}
