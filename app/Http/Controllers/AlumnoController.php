<?php

namespace App\Http\Controllers;

use App\Enums\MotivoBaja;
use App\Enums\TipoInscripcion;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\AlumnoContacto;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ContactoFamiliar;
use App\Models\Credencial;
use App\Models\DocumentoAlumno;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\HistorialBaja;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\Prospecto;
use App\Models\Setting;
use App\Traits\RespondsWithJson;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AlumnoController extends Controller
{
    use RespondsWithJson;

    /** GET /alumnos */
    public function index(Request $request): View|JsonResponse
    {
        $cicloId = $this->cicloActualId();

        $query = Alumno::with([
            'familia',
            'inscripciones' => fn ($q) => $q
                ->where('activo', true)
                ->with('grupo.grado.nivel'),
        ])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('nivel_id'), fn ($q) => $q->whereHas('inscripciones', fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->whereHas('grupo.grado', fn ($q) => $q->where('nivel_id', $request->nivel_id))
            ))
            ->when($request->filled('grupo_id'), fn ($q) => $q->whereHas('inscripciones', fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->where('grupo_id', $request->grupo_id)
            ))
            ->when($request->filled('buscar'), fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$request->buscar}%")
                ->orWhere('ap_paterno', 'like', "%{$request->buscar}%")
                ->orWhere('matricula', 'like', "%{$request->buscar}%")
                ->orWhere('curp', 'like', "%{$request->buscar}%")
            ))
            ->orderBy('ap_paterno')
            ->orderBy('nombre');

        if ($request->ajax()) {
            return response()->json($query->paginate($request->get('per_page', 20)));
        }

        $alumnos = $query->paginate(20);

        // Determina el plan efectivo por alumno a partir de sus cargos reales del ciclo actual.
        // Mapea inscripcion_id → alumno_id. Se usa un loop explícito para preservar claves
        // enteras (flatMap/collapse usa array_merge internamente y las re-indexaría a 0).
        $inscIdAAlumnoId = [];
        foreach ($alumnos as $a) {
            foreach ($a->inscripciones as $i) {
                $inscIdAAlumnoId[$i->id] = $a->id;
            }
        }

        $planPorAlumno = collect();

        if (! empty($inscIdAAlumnoId)) {
            $planPorAlumno = Cargo::whereIn('inscripcion_id', array_keys($inscIdAAlumnoId))
                ->whereNotNull('asignacion_id')
                ->whereHas('asignacion.plan', fn ($q) => $q->where('ciclo_id', $cicloId))
                ->with('asignacion.plan')
                ->get()
                ->unique('inscripcion_id')
                ->mapWithKeys(fn ($c) => [
                    $inscIdAAlumnoId[$c->inscripcion_id] => $c->asignacion?->plan,
                ]);
        }

        return view('alumnos.index', [
            'alumnos' => $alumnos,
            'planPorAlumno' => $planPorAlumno,
            'niveles' => NivelEscolar::activo()->get(),
            'grupos' => Grupo::with('grado')->where('ciclo_id', $cicloId)->activo()->get(),
            'cicloId' => $cicloId,
            'statsActivos' => Alumno::where('estado', 'activo')->count(),
            'statsTotal' => Alumno::count(),
            'statsInscritos' => Inscripcion::where('ciclo_id', $cicloId)->distinct('alumno_id')->count('alumno_id'),
            'disenos' => Credencial::all(),
        ]);
    }

    /** GET /alumnos/{id} */
    public function show(int $id): View|JsonResponse
    {
        $alumno = Alumno::with([
            'familia.contactos.razonesSociales',
            'inscripciones.grupo.grado.nivel',
            'contactos',
            'documentos',
            'becas.catalogoBeca',
            'becas.plan',
            'becas.concepto',
            'historialBajas.ciclo',
            'historialBajas.registradoPor',
            'fichaMedica',
            'condicionesMedicas',
            'medicamentosAutorizados.contactoAutoriza',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($alumno);
        }

        return view('alumnos.show', compact('alumno'));
    }

    /** GET /alumnos/create */
    public function create(Request $request): View
    {
        $cicloId = $this->cicloActualId();
        $prospectoOrigen = $request->filled('prospecto_id')
            ? Prospecto::find($request->integer('prospecto_id'))
            : null;

        return view('alumnos.create', [
            'niveles' => NivelEscolar::activo()->get(),
            'grupos' => Grupo::with('grado.nivel')->where('ciclo_id', $cicloId)->activo()->get(),
            'familias' => Familia::where('activo', true)->orderBy('apellido_familia')->get(),
            'prospectoOrigen' => $prospectoOrigen,
            'datosPrecargados' => $this->obtenerDatosPrecargados($prospectoOrigen, $cicloId),
        ]);
    }

    /**
     * POST /alumnos
     * Registra familia (si es nueva) + alumno + inscripción +
     * contactos + documentos en una sola transacción.
     */
    public function store(StoreAlumnoRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        try {
            $alumno = DB::transaction(function () use ($data, $request): Alumno {
                $familiaId = ! empty($data['familia_id'])
                    ? (int) $data['familia_id']
                    : Familia::create(['apellido_familia' => $data['apellido_familia']])->id;

                $alumno = Alumno::create([
                    'familia_id' => $familiaId,
                    'matricula' => $this->generarMatricula($data['ciclo_id']),
                    'nombre' => $data['nombre'],
                    'ap_paterno' => $data['ap_paterno'],
                    'ap_materno' => $data['ap_materno'] ?? null,
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'curp' => $data['curp'] ?? null,
                    'genero' => $data['genero'] ?? null,
                    'foto_url' => null,
                    'observaciones' => $data['observaciones'] ?? null,
                    'fecha_inscripcion' => $data['fecha_inscripcion'],
                    'estado' => 'activo',
                    // Domicilio
                    'calle' => $data['calle'] ?? null,
                    'colonia' => $data['colonia'] ?? null,
                    'codigo_postal' => $data['codigo_postal'] ?? null,
                    'ciudad' => $data['ciudad'] ?? null,
                    'estado_residencia' => $data['estado_residencia'] ?? null,
                    'religion' => $data['religion'] ?? null,
                ]);

                if ($request->hasFile('foto')) {
                    $alumno->update(['foto_url' => $request->file('foto')->store('alumnos/fotos', 'public')]);
                }

                Inscripcion::create([
                    'alumno_id' => $alumno->id,
                    'ciclo_id' => $data['ciclo_id'],
                    'grupo_id' => $data['grupo_id'],
                    'fecha' => $data['fecha_inscripcion'],
                    'activo' => true,
                ]);

                $this->procesarContactos($data['contactos'], $alumno->id, $familiaId, $request);

                foreach ($this->documentosPorGrupo($data['grupo_id']) as $doc) {
                    DocumentoAlumno::create([
                        'alumno_id' => $alumno->id,
                        'tipo_documento' => $doc,
                        'estado' => 'pendiente',
                    ]);
                }

                if (! empty($data['prospecto_id'])) {
                    Prospecto::where('id', $data['prospecto_id'])
                        ->update(['alumno_id' => $alumno->id, 'etapa' => 'inscrito']);
                }

                Auditoria::registrar('alumno', $alumno->id, 'insert', null, $alumno->toArray());

                return $alumno;
            });
        } catch (\Throwable $e) {
            return $this->respuestaError('Error al registrar el alumno: '.$e->getMessage());
        }

        $mensaje = "Alumno '{$alumno->nombre} {$alumno->ap_paterno}' registrado. Matrícula: {$alumno->matricula}";

        if (request()->ajax()) {
            return response()->json([
                'message' => $mensaje,
                'alumno' => $alumno->load(['familia', 'inscripciones.grupo', 'contactos']),
            ], 201);
        }

        return redirect()->route('alumnos.show', $alumno->id)->with('success', $mensaje);
    }

    /** GET /alumnos/{id}/edit */
    public function edit(int $id): View|JsonResponse
    {
        $alumno = Alumno::with(['familia', 'contactos.usuario'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($alumno);
        }

        return view('alumnos.edit', [
            'alumno'        => $alumno,
            'inscripciones' => $alumno->inscripciones()->with('ciclo', 'grupo.ciclo', 'grupo.grado.nivel')->get(),
            'niveles'       => NivelEscolar::activo()->get(),
            'familias'      => \App\Models\Familia::orderBy('apellido_familia')->get(['id', 'apellido_familia']),
        ]);
    }

    /** PUT /alumnos/{id} */
    public function update(UpdateAlumnoRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $alumno = Alumno::findOrFail($id);
        $anterior = $alumno->toArray();
        $campos = $request->validated();

        // Separar campos de inscripción — viven en tabla aparte
        $grupoId = $campos['grupo_id'] ?? null;
        $cicloId = $campos['ciclo_id'] ?? null;
        unset($campos['grupo_id'], $campos['nivel_id'], $campos['ciclo_id']);

        if ($request->hasFile('foto')) {
            if ($alumno->foto_url) {
                Storage::disk('public')->delete($alumno->foto_url);
            }
            $campos['foto_url'] = $request->file('foto')->store('alumnos/fotos', 'public');
        }

        $alumno->update($campos);

        foreach ($request->file('fotos_contacto', []) as $contactoId => $fotoFile) {
            $contacto = ContactoFamiliar::find($contactoId);
            if (! $contacto) {
                continue;
            }
            if ($contacto->foto_url) {
                Storage::disk('public')->delete($contacto->foto_url);
            }
            $contacto->update(['foto_url' => $fotoFile->store('contactos/fotos', 'public')]);
        }

        if ($cicloId) {
            $inscActiva = $alumno->inscripciones()
                ->where('activo', true)
                ->where('tipo', TipoInscripcion::Regular)
                ->latest('id')
                ->first();

            if ($inscActiva) {
                if ((int) $inscActiva->ciclo_id === (int) $cicloId) {
                    $inscActiva->update(['grupo_id' => $grupoId ?: null]);
                } else {
                    $inscActiva->update(['activo' => false]);
                    $this->crearInscripcionRegular($alumno->id, (int) $cicloId, $grupoId);
                }
            } else {
                $this->crearInscripcionRegular($alumno->id, (int) $cicloId, $grupoId);
            }

            // Si estaba de baja y se le asigna inscripción, reactivarlo
            if (in_array($alumno->estado, ['baja_temporal', 'baja_definitiva'])) {
                $alumno->update(['estado' => 'activo', 'fecha_baja' => null]);

                $alumno->historialBajas()
                    ->whereNull('fecha_reactivacion')
                    ->latest('fecha_baja')
                    ->first()
                    ?->update(['fecha_reactivacion' => today()]);
            }
        }

        Auditoria::registrar('alumno', $alumno->id, 'update', $anterior, $alumno->fresh()->toArray());

        $mensaje = 'Datos del alumno actualizados correctamente.';

        if (request()->ajax()) {
            return response()->json(['message' => $mensaje, 'alumno' => $alumno->fresh()]);
        }

        return redirect()->route('alumnos.show', $alumno->id)->with('success', $mensaje);
    }

    /**
     * GET /alumnos/{id}/hermanos
     * Solo AJAX — usada desde la ficha del alumno.
     */
    public function hermanos(int $id): JsonResponse
    {
        $alumno = Alumno::findOrFail($id);

        if (! $alumno->familia_id) {
            return response()->json([]);
        }

        $hermanos = Alumno::where('familia_id', $alumno->familia_id)
            ->where('id', '!=', $alumno->id)
            ->with(['inscripciones' => fn ($q) => $q->where('activo', true)->with('grupo.grado.nivel')])
            ->get();

        return response()->json($hermanos);
    }

    /**
     * GET /alumnos/{id}/estado-cuenta
     * Devuelve cargos del ciclo activo. Usada tanto en vista como AJAX.
     */
    public function estadoCuenta(Request $request, int $id): View
    {
        $alumno = Alumno::with([
            'inscripciones.grupo.grado.nivel',
            'inscripciones.ciclo',
        ])->findOrFail($id);

        $inscripcionActual = $alumno->inscripciones
            ->where('activo', true)
            ->sortByDesc('id')
            ->first(fn ($i) => $i->grupo_id !== null)
            ?? $alumno->inscripciones->where('activo', true)->sortByDesc('id')->first();

        $ciclos = CicloEscolar::whereHas('inscripciones', fn ($q) => $q->where('alumno_id', $alumno->id))
            ->orderByDesc('fecha_inicio')
            ->get();

        $cargos = Cargo::with([
            'concepto',
            'inscripcion:id,ciclo_id',
            'detallesPagosVigentes.pago:id,folio_recibo,fecha_pago,forma_pago,referencia,estado',
            'asignacion.plan.politicasDescuentoActivas',
            'asignacion.plan.politicasRecargo',
            'condonacionDetalles.condonacion:id,motivo,estado',
            'descuentos',
        ])
            ->whereHas('inscripcion', fn ($q) => $q->where('alumno_id', $alumno->id))
            ->withSum('detallesPagosVigentes as total_abonado', 'monto_final')
            ->when($request->filled('ciclo_id'), fn ($q) => $q->whereHas(
                'inscripcion', fn ($sq) => $sq->where('ciclo_id', $request->ciclo_id)
            ))
            ->orderBy('fecha_vencimiento')
            ->get();

        // Cargar todas las becas vigentes del alumno sin filtrar por ciclo de inscripción.
        // El matching correcto ocurre en calcularBecaCargo via plan_id/concepto_id,
        // ya que un plan de pago ya pertenece a un ciclo específico.
        $becas = BecaAlumno::with(['catalogoBeca', 'plan', 'concepto'])
            ->where('alumno_id', $alumno->id)
            ->where('activo', true)
            ->where(fn ($q) => $q->whereNull('vigencia_fin')->orWhere('vigencia_fin', '>=', now()))
            ->get();

        $becasPorPlan      = $becas->whereNotNull('plan_id')->keyBy('plan_id');
        $becasPorConcepto  = $becas->whereNotNull('concepto_id')->keyBy('concepto_id');
        // Becas globales: sin plan ni concepto específico, aplican a todos los cargos del ciclo
        $becasGlobales     = $becas->filter(fn ($b) => $b->plan_id === null && $b->concepto_id === null);

        $resumen = $this->calcularResumenCargos($cargos, $becasPorPlan, $becasPorConcepto, $becasGlobales);

        return view('alumnos.estado-cuenta', compact(
            'alumno', 'inscripcionActual', 'ciclos', 'cargos', 'resumen', 'becas'
        ));
    }

    /**
     * POST /alumnos/{id}/inscripcion-anticipada
     * Registra una inscripción anticipada al ciclo siguiente para un alumno ya inscrito.
     */
    public function registrarAnticipada(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'ciclo_id' => 'required|exists:ciclo_escolar,id',
            'grupo_id' => 'nullable|exists:grupo,id',
            'fecha' => 'required|date',
        ]);

        $alumno = Alumno::findOrFail($id);

        $yaInscrito = $alumno->inscripciones()
            ->where('ciclo_id', $request->ciclo_id)
            ->where('activo', true)
            ->exists();

        if ($yaInscrito) {
            return $this->respuestaError('El alumno ya tiene una inscripción activa en el ciclo seleccionado.');
        }

        $cicloDestino = CicloEscolar::findOrFail($request->ciclo_id);

        if ($cicloDestino->estado === 'activo') {
            return $this->respuestaError('Para inscribir en el ciclo activo usa la inscripción regular.');
        }

        $inscripcion = Inscripcion::create([
            'alumno_id' => $alumno->id,
            'ciclo_id' => $request->ciclo_id,
            'grupo_id' => $request->grupo_id ?: null,
            'fecha' => $request->fecha,
            'activo' => true,
            'tipo' => TipoInscripcion::Anticipada,
        ]);

        Auditoria::registrar('inscripcion', $inscripcion->id, 'insert', null, $inscripcion->toArray());

        $mensaje = "Inscripción anticipada al ciclo '{$cicloDestino->nombre}' registrada correctamente.";

        if (request()->ajax()) {
            return response()->json([
                'message' => $mensaje,
                'inscripcion' => $inscripcion->load('ciclo', 'grupo.grado.nivel'),
            ], 201);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * DELETE /inscripciones/{id}
     * Desactiva la inscripción sin borrarla (preserva cargos financieros).
     */
    public function quitarDelGrupo(int $id): RedirectResponse
    {
        $inscripcion = Inscripcion::with('alumno')->findOrFail($id);
        $inscripcion->update(['activo' => false]);

        return back()->with('success', "Se ha quitado a {$inscripcion->alumno->nombre} del grupo correctamente.");
    }

    /**
     * PATCH /alumnos/{id}/dar-baja
     */
    public function darBaja(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'tipo_baja' => 'required|in:baja_temporal,baja_definitiva',
            'motivo_categoria' => 'required|in:cambio_escuela,traslado,economico,familiar,salud,conducta,rendimiento,otro',
            'motivo_detalle' => 'nullable|string|max:1000',
        ]);

        $alumno = Alumno::findOrFail($id);
        $cicloActual = CicloEscolar::where('estado', 'activo')->first();

        DB::transaction(function () use ($request, $alumno, $cicloActual) {
            $alumno->update(['estado' => $request->tipo_baja, 'fecha_baja' => today()]);
            $alumno->inscripciones()->where('activo', true)->update(['activo' => false]);

            HistorialBaja::create([
                'alumno_id' => $alumno->id,
                'ciclo_id' => $cicloActual?->id,
                'registrado_por' => auth()->id(),
                'tipo' => $request->tipo_baja,
                'motivo_categoria' => $request->motivo_categoria,
                'motivo_detalle' => $request->motivo_detalle,
                'fecha_baja' => today(),
            ]);
        });

        return back()->with('success', 'Se registró la baja correctamente en el expediente.');
    }

    /**
     * GET /alumnos/bajas
     * Reporte de alumnos dados de baja con historial de motivos.
     */
    public function reporteBajas(Request $request): View
    {
        $bajas = HistorialBaja::with(['alumno', 'ciclo', 'registradoPor'])
            ->whereNull('fecha_reactivacion')
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('motivo_categoria'), fn ($q) => $q->where('motivo_categoria', $request->motivo_categoria))
            ->when($request->filled('ciclo_id'), fn ($q) => $q->where('ciclo_id', $request->ciclo_id))
            ->when($request->filled('buscar'), fn ($q) => $q->whereHas('alumno', fn ($sq) => $sq
                ->where('nombre', 'like', "%{$request->buscar}%")
                ->orWhere('ap_paterno', 'like', "%{$request->buscar}%")
                ->orWhere('matricula', 'like', "%{$request->buscar}%")
            ))
            ->orderByDesc('fecha_baja')
            ->paginate(25)
            ->withQueryString();

        return view('alumnos.bajas', [
            'bajas' => $bajas,
            'ciclos' => CicloEscolar::orderByDesc('fecha_inicio')->get(),
            'motivos' => MotivoBaja::cases(),
        ]);
    }

    /** POST /alumnos/promocionar-masivo */
    public function promocionarMasivo(Request $request): RedirectResponse
    {
        $request->validate([
            'inscripciones_ids' => 'required|array',
            'ciclo_destino_id' => 'required|exists:ciclo_escolar,id',
            'grado_destino_id' => 'required|exists:grados,id',
            'grupo_origen_id' => 'required',
        ]);

        $contador = 0;

        try {
            DB::transaction(function () use ($request, &$contador) {
                foreach ($request->inscripciones_ids as $inscripcionId) {
                    $inscripcionActual = Inscripcion::findOrFail($inscripcionId);
                    $alumno = $inscripcionActual->alumno;

                    $inscripcionActual->update(['activo' => false]);

                    $anticipada = $alumno->inscripciones()
                        ->where('ciclo_id', $request->ciclo_destino_id)
                        ->where('tipo', TipoInscripcion::Anticipada)
                        ->where('activo', true)
                        ->first();

                    if ($anticipada) {
                        $anticipada->update(['tipo' => TipoInscripcion::Regular]);
                    } else {
                        $this->crearInscripcionRegular($alumno->id, $request->ciclo_destino_id, null);
                    }

                    $alumno->update(['estado' => 'activo']);
                    $contador++;
                }
            });

            return redirect()
                ->route('grupos.show', $request->grupo_origen_id)
                ->with('success', "¡Éxito! Se han promocionado {$contador} alumnos correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Hubo un error al promocionar: '.$e->getMessage());
        }
    }

    /**
     * POST /grupos/{id}/egresar-todo
     * Procesa a múltiples alumnos de un grupo (egreso o cierre de ciclo).
     */
    public function egresarTodo(Request $request, int $grupo_id): RedirectResponse
    {
        $ids = $request->input('inscripciones_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No seleccionaste ningún alumno para procesar.');
        }

        try {
            DB::transaction(function () use ($ids) {
                Inscripcion::whereIn('id', $ids)
                    ->with('alumno', 'grupo.grado')
                    ->get()
                    ->each(function (Inscripcion $inscripcion) {
                        if ($inscripcion->grupo->grado->nombre == '6') {
                            $inscripcion->alumno->update(['estado' => 'egresado', 'fecha_baja' => now()]);
                        }
                        $inscripcion->update(['activo' => false]);
                    });
            });

            return back()->with('success', '¡Proceso completado! Se actualizaron '.count($ids).' alumnos.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar: '.$e->getMessage());
        }
    }

    /** GET /alumnos/{id}/reporte */
    public function reporteAlumno(Request $request, int $id)
    {
        $alumno = Alumno::with([
            'familia.alumnos',
            'inscripciones.grupo.grado.nivel',
            'inscripciones.ciclo',
            'contactos.razonesSociales',
            'fichaMedica',
            'condicionesMedicas',
            'medicamentosAutorizados.contactoAutoriza',
        ])->findOrFail($id);

        if (ob_get_length()) {
            ob_end_clean();
        }

        $setting = Setting::first();

        $pdf = Pdf::loadView('alumnos.reportes.perfil_pdf', [
            'alumno'       => $alumno,
            'base64'       => $this->logoBase64($setting),
            'setting'      => $setting,
            'cicloActualId'=> $this->cicloActualId(),
        ]);

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("Reporte_{$alumno->nombre}_{$alumno->ap_paterno}.pdf");
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function cicloActualId(): int
    {
        return auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');
    }

    private function crearInscripcionRegular(int $alumnoId, int $cicloId, ?int $grupoId): Inscripcion
    {
        return Inscripcion::create([
            'alumno_id' => $alumnoId,
            'ciclo_id' => $cicloId,
            'grupo_id' => $grupoId,
            'fecha' => now()->toDateString(),
            'activo' => true,
            'tipo' => TipoInscripcion::Regular,
        ]);
    }

    private function procesarContactos(array $contactos, int $alumnoId, int $familiaId, Request $request): void
    {
        $vinculados = [];

        foreach ($contactos as $index => $datos) {
            $contacto = null;

            if (! empty($datos['curp'])) {
                $contacto = ContactoFamiliar::where('curp', $datos['curp'])->first();
            }
            if (! $contacto && ! empty($datos['telefono_celular'])) {
                $contacto = ContactoFamiliar::where('telefono_celular', $datos['telefono_celular'])->first();
            }

            if ($contacto) {
                if (! $contacto->familia_id) {
                    $contacto->update(['familia_id' => $familiaId]);
                }
            } else {
                $contacto = ContactoFamiliar::create([
                    'familia_id' => $familiaId,
                    'tiene_acceso_portal' => $datos['tiene_acceso_portal'] ?? false,
                    'usuario_id' => null,
                    'nombre' => $datos['nombre'],
                    'ap_paterno' => $datos['ap_paterno'] ?? null,
                    'ap_materno' => $datos['ap_materno'] ?? null,
                    'telefono_celular' => $datos['telefono_celular'],
                    'telefono_trabajo' => $datos['telefono_trabajo'] ?? null,
                    'telefono_2' => $datos['telefono_2'] ?? null,
                    'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                    'email' => $datos['email'] ?? null,
                    'curp' => $datos['curp'] ?? null,
                    'lugar_trabajo' => $datos['lugar_trabajo'] ?? null,
                    'puesto' => $datos['puesto'] ?? null,
                    'nivel_estudios' => $datos['nivel_estudios'] ?? null,
                    'profesion' => $datos['profesion'] ?? null,
                    'vive' => $datos['vive'] ?? true,
                ]);
            }

            if ($request->hasFile("fotos_contacto.{$index}")) {
                $contacto->update([
                    'foto_url' => $request->file("fotos_contacto.{$index}")->store('contactos/fotos', 'public'),
                ]);
            }

            if (in_array($contacto->id, $vinculados)) {
                continue;
            }

            AlumnoContacto::create([
                'alumno_id' => $alumnoId,
                'contacto_id' => $contacto->id,
                'parentesco' => $datos['parentesco'],
                'tipo' => $datos['tipo'],
                'orden' => $datos['orden'],
                'autorizado_recoger' => $datos['autorizado_recoger'] ?? false,
                'es_responsable_pago' => $datos['es_responsable_pago'] ?? false,
                'activo' => true,
            ]);

            $vinculados[] = $contacto->id;
        }
    }

    /**
     * Calcula el resumen financiero de los cargos.
     * Como efecto secundario, anota en cada $cargo las propiedades
     * calculadas que necesita la vista (beca_descuento_calc, recargo_calc, etc.).
     */
    private function calcularResumenCargos(
        Collection $cargos,
        Collection $becasPorPlan,
        Collection $becasPorConcepto,
        Collection $becasGlobales = new Collection,
    ): array {
        $hoyFecha = today();
        $totales = [
            'total_cargado'      => 0.0,
            'total_pagado'       => 0.0,
            'total_condonado'    => 0.0,
            'total_vencido'      => 0.0,
            'total_recargos'     => 0.0,
            'total_descuentos'   => 0.0,
            'total_becas'        => 0.0,
            'total_condonaciones'=> 0.0,
            'total_cargos'       => $cargos->count(),
            'cargos_pendientes'  => 0,
            'cargos_vencidos'    => 0,
        ];

        foreach ($cargos as $cargo) {
            $abonado = (float) ($cargo->total_abonado ?? 0);
            $saldoBase = max(0, (float) $cargo->monto_original - (float) $cargo->monto_cubierto);
            $vencido = $hoyFecha->gt($cargo->fecha_vencimiento);
            $esPendiente = ! in_array($cargo->estado_real, ['pagado', 'condonado']) && $saldoBase > 0;

            [$becaDescuento, $becaPorcentaje] = $esPendiente
                ? $this->calcularBecaCargo($cargo, $saldoBase, $becasPorPlan, $becasPorConcepto, $becasGlobales)
                : [0.0, null];

            [$descuento, $recargo, $mesesRetraso] = ($esPendiente && $cargo->asignacion?->plan)
                ? $this->calcularPoliticaCargo($cargo, $saldoBase, $vencido, $hoyFecha)
                : [0.0, 0.0, 0];

            // Descuento por condonación (misma lógica que CobrosController::enriquecerCargo)
            $condonacionDesc = $esPendiente
                ? (float) $cargo->descuentos->sum('monto_aplicado')
                : 0.0;

            // Anotar en el modelo para la vista
            $cargo->beca_descuento_calc        = $becaDescuento;
            $cargo->beca_porcentaje            = $becaPorcentaje;
            $cargo->descuento_calc             = $descuento;
            $cargo->recargo_calc               = $recargo;
            $cargo->meses_retraso              = $mesesRetraso;
            $cargo->descuento_condonacion_calc = $condonacionDesc;
            $cargo->monto_a_pagar_hoy          = max(0, $saldoBase - $becaDescuento - $descuento - $condonacionDesc + $recargo);

            $totales['total_cargado'] += (float) $cargo->monto_original;
            $totales['total_pagado'] += $abonado;

            if ($cargo->estado === 'condonado') {
                $totales['total_condonado'] += (float) $cargo->monto_original;
            }

            if ($esPendiente) {
                $totales['total_becas']        += $becaDescuento;
                $totales['total_condonaciones'] += $condonacionDesc;
                if ($vencido) {
                    $totales['total_vencido']   += $cargo->monto_a_pagar_hoy;
                    $totales['total_recargos']  += $recargo;
                    $totales['cargos_vencidos']++;
                } else {
                    $totales['total_descuentos'] += $descuento;
                    $totales['cargos_pendientes']++;
                }
            }
        }

        $totalCubierto = $cargos->sum(fn (Cargo $c) => min((float) $c->monto_original, (float) $c->monto_cubierto));
        $saldoPendienteBase = max(0, $totales['total_cargado'] - $totalCubierto - $totales['total_condonado']);

        return array_merge($totales, [
            'saldo_pendiente' => $saldoPendienteBase,
            'total_a_pagar_hoy' => max(0, $saldoPendienteBase - $totales['total_becas'] - $totales['total_condonaciones'] + $totales['total_recargos'] - $totales['total_descuentos']),
        ]);
    }

    /** Resuelve el descuento de beca aplicable a un cargo (plan → concepto → global). */
    private function calcularBecaCargo(
        Cargo $cargo,
        float $saldoBase,
        Collection $becasPorPlan,
        Collection $becasPorConcepto,
        Collection $becasGlobales = new Collection,
    ): array {
        // 1. Beca asociada al plan de pago del cargo
        $becaItem = $cargo->asignacion?->plan_id
            ? $becasPorPlan->get($cargo->asignacion->plan_id)
            : null;

        // 2. Beca asociada al concepto específico
        $becaItem ??= $becasPorConcepto->get($cargo->concepto_id);

        // 3. Beca global del alumno (sin plan ni concepto específico)
        //    Se aplica solo si pertenece al mismo ciclo del cargo (via inscripcion)
        if (! $becaItem && $becasGlobales->isNotEmpty()) {
            $cicloDelCargo = $cargo->inscripcion?->ciclo_id;
            $becaItem = $becasGlobales->first(
                fn ($b) => $cicloDelCargo && $b->ciclo_id === $cicloDelCargo
            );
        }

        if (! $becaItem) {
            return [0.0, null];
        }

        $descuento  = min($becaItem->calcularDescuento((float) $cargo->monto_original), $saldoBase);
        $porcentaje = $becaItem->catalogoBeca->tipo === 'porcentaje'
            ? (float) $becaItem->catalogoBeca->valor
            : null;

        return [$descuento, $porcentaje];
    }

    /** Resuelve el recargo o descuento por política del plan de pago. */
    private function calcularPoliticaCargo(Cargo $cargo, float $saldoBase, bool $vencido, Carbon $hoyFecha): array
    {
        $plan = $cargo->asignacion->plan;

        if ($vencido) {
            $mesesRetraso = (int) $cargo->fecha_vencimiento->diffInMonths($hoyFecha) + 1;
            $pr = $plan->politicasRecargo->firstWhere('activo', true);

            return [0.0, $pr ? $pr->calcular($saldoBase, $mesesRetraso) : 0.0, $mesesRetraso];
        }

        $pd = $plan->politicasDescuentoActivas->first(fn ($p) => $p->aplicaHoy());

        return [$pd ? $pd->calcular($saldoBase) : 0.0, 0.0, 0];
    }

    /** Devuelve el logo codificado en base64.
     *  Usa el logo de configuración (imgs_escuela/reportes/{logo_ruta}),
     *  con fallback al archivo estático logo_reportes.png.
     */
    private function logoBase64(?Setting $setting = null): string
    {
        $candidatos = array_filter([
            $setting?->logo_ruta
                ? public_path('imgs_escuela/reportes/'.$setting->logo_ruta)
                : null,
            public_path('imgs_escuela/reportes/logo_reportes.png'),
        ]);

        foreach ($candidatos as $path) {
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);

                return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
            }
        }

        return '';
    }

    private function generarMatricula(int $cicloId): string
    {
        $ciclo = CicloEscolar::find($cicloId);
        $año = substr($ciclo->nombre, 0, 4);
        $ultimo = Alumno::where('matricula', 'like', "{$año}-%")
            ->orderByDesc('matricula')
            ->value('matricula');
        $siguiente = $ultimo ? (int) substr($ultimo, -4) + 1 : 1;

        return $año.'-'.str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }

    private function documentosPorGrupo(int $grupoId): array
    {
        $nivel = Grupo::with('grado.nivel')->find($grupoId)?->grado?->nivel?->nombre ?? '';

        $base = ['Acta de nacimiento', 'CURP', 'Comprobante de domicilio', 'Fotos tamaño infantil'];

        return match (true) {
            in_array($nivel, ['Maternal', 'Preescolar']) => array_merge($base, ['Cartilla de vacunación']),
            $nivel === 'Primaria' => array_merge($base, ['Boletas ciclo anterior']),
            $nivel === 'Secundaria' => array_merge($base, ['Boletas ciclo anterior', 'Certificado de estudios primaria']),
            default => $base,
        };
    }

    private function obtenerDatosPrecargados(?Prospecto $prospecto, int $cicloId): array
    {
        if (! $prospecto) {
            return ['alumno' => [], 'apellido_familia' => '', 'contactos' => []];
        }

        $nombre = $prospecto->nombre;
        $apPaterno = $prospecto->ap_paterno;
        $apMaterno = $prospecto->ap_materno;

        if (! $apPaterno) {
            [$nombre, $apPaterno, $apMaterno] = $this->separarNombreCompleto($prospecto->nombre);
        }

        [$contactoNombre, $contactoApPaterno, $contactoApMaterno] = $this->separarNombreCompleto($prospecto->contacto_nombre);

        $apellidoFamilia = trim(collect([$apPaterno, $apMaterno])->filter()->implode(' '));

        return [
            'alumno' => [
                'nombre' => $nombre,
                'ap_paterno' => $apPaterno,
                'ap_materno' => $apMaterno,
                'fecha_nacimiento' => $prospecto->fecha_nacimiento?->format('Y-m-d'),
                'fecha_inscripcion' => now()->format('Y-m-d'),
                'ciclo_id' => $prospecto->ciclo_id ?: $cicloId,
                'nivel_id' => $prospecto->nivel_interes_id,
                'prospecto_id' => $prospecto->id,
            ],
            'apellido_familia' => $apellidoFamilia ? 'Familia '.$apellidoFamilia : '',
            'contactos' => [[
                'nombre' => $contactoNombre,
                'ap_paterno' => $contactoApPaterno,
                'ap_materno' => $contactoApMaterno,
                'telefono_celular' => $prospecto->contacto_telefono,
                'telefono_trabajo' => '',
                'email' => $prospecto->contacto_email,
                'curp' => '',
                'parentesco' => 'otro',
                'tipo' => 'tutor',
                'orden' => 1,
                'autorizado_recoger' => true,
                'es_responsable_pago' => true,
                'tiene_acceso_portal' => false,
            ]],
        ];
    }

    private function separarNombreCompleto(?string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim((string) $nombreCompleto), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($partes)) {
            return ['', '', ''];
        }
        if (count($partes) === 1) {
            return [$partes[0], '', ''];
        }
        if (count($partes) === 2) {
            return [$partes[0], $partes[1], ''];
        }

        $apMaterno = array_pop($partes);
        $apPaterno = array_pop($partes);

        return [implode(' ', $partes), $apPaterno, $apMaterno];
    }
}
