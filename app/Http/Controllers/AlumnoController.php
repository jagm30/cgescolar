<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\AlumnoContacto;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ContactoFamiliar;
use App\Models\DocumentoAlumno;
use App\Models\Familia;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\NivelEscolar;
use App\Models\Prospecto;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlumnoController extends Controller
{
    use RespondsWithJson;

    /** GET /alumnos */
    public function index(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $query = Alumno::with([
            'familia',
            'inscripciones' => fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->with('grupo.grado.nivel'),
        ])
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->estado))
            ->when($request->filled('nivel_id'), fn ($q) => $q->whereHas(
                'inscripciones', fn ($q) => $q
                    ->where('ciclo_id', $cicloId)
                    ->whereHas('grupo.grado', fn ($q) => $q->where('nivel_id', $request->nivel_id))
            ))
            ->when($request->filled('grupo_id'), fn ($q) => $q->whereHas(
                'inscripciones', fn ($q) => $q
                    ->where('ciclo_id', $cicloId)
                    ->where('grupo_id', $request->grupo_id)
            ))
            ->when($request->filled('buscar'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                    ->orWhere('ap_paterno', 'like', "%{$request->buscar}%")
                    ->orWhere('matricula', 'like', "%{$request->buscar}%")
                    ->orWhere('curp', 'like', "%{$request->buscar}%");
            }))
            ->orderBy('ap_paterno')
            ->orderBy('nombre');

        if ($request->ajax()) {
            return response()->json($query->paginate($request->get('per_page', 20)));
        }

        $alumnos = $query->paginate(20);
        $niveles = NivelEscolar::activo()->get();
        $grupos = Grupo::with('grado')->where('ciclo_id', $cicloId)->activo()->get();

        // Estadísticas globales para cabecera
        $statsActivos = Alumno::where('estado', 'activo')->count();
        $statsTotal = Alumno::count();
        $statsInscritos = Inscripcion::where('ciclo_id', $cicloId)->distinct('alumno_id')->count('alumno_id');

        return view('alumnos.index', compact(
            'alumnos', 'niveles', 'grupos', 'cicloId',
            'statsActivos', 'statsTotal', 'statsInscritos'
        ));
    }

    /** GET /alumnos/{id} */
    public function show(int $id)
    {
        $alumno = Alumno::with([
            'familia',
            'inscripciones.grupo.grado.nivel',
            'contactos',
            'documentos',
            'becas.catalogoBeca',
        ])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($alumno);
        }

        return view('alumnos.show', compact('alumno'));
    }

    /** GET /alumnos/create */
    public function create(Request $request)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $niveles = NivelEscolar::activo()->get();
        $grupos = Grupo::with('grado.nivel')->where('ciclo_id', $cicloId)->activo()->get();
        $familias = Familia::where('activo', true)->orderBy('apellido_familia')->get();
        $prospectoOrigen = $request->filled('prospecto_id')
            ? Prospecto::find($request->integer('prospecto_id'))
            : null;
        $datosPrecargados = $this->obtenerDatosPrecargados($prospectoOrigen, $cicloId);

        return view('alumnos.create', compact('niveles', 'grupos', 'familias', 'prospectoOrigen', 'datosPrecargados'));
    }

    /**
     * POST /alumnos
     * Registra familia (si es nueva) + alumno + inscripción +
     * contactos + documentos en una sola transacción.
     */
    public function store(StoreAlumnoRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // ── 1. Familia ────────────────────────────────
            if (! empty($data['familia_id'])) {
                $familiaId = $data['familia_id'];
            } else {
                $familia = Familia::create(['apellido_familia' => $data['apellido_familia']]);
                $familiaId = $familia->id;
            }

            // ── 2. Alumno ─────────────────────────────────
            $matricula = $this->generarMatricula($data['ciclo_id']);

            $alumno = Alumno::create([
                'familia_id' => $familiaId,
                'matricula' => $matricula,
                'nombre' => $data['nombre'],
                'ap_paterno' => $data['ap_paterno'],
                'ap_materno' => $data['ap_materno'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'curp' => $data['curp'] ?? null,
                'genero' => $data['genero'] ?? null,
                'foto_url' => null, // se actualiza abajo si viene archivo
                'observaciones' => $data['observaciones'] ?? null,
                'fecha_inscripcion' => $data['fecha_inscripcion'],
                'estado' => 'activo',
            ]);

            // ── 2b. Foto del alumno ───────────────────────
            // $request->file('foto') NO está en $data (validated) porque es un
            // archivo, no un campo de texto. Se procesa por separado DESPUÉS
            // de tener el id del alumno recién creado.
            if ($request->hasFile('foto')) {
                $ruta = $request->file('foto')->store('alumnos/fotos', 'public');
                $alumno->update(['foto_url' => $ruta]);
            }

            // ── 3. Inscripción ────────────────────────────
            Inscripcion::create([
                'alumno_id' => $alumno->id,
                'ciclo_id' => $data['ciclo_id'],
                'grupo_id' => $data['grupo_id'],
                'fecha' => $data['fecha_inscripcion'],
                'activo' => true,
            ]);

            // ── 4. Contactos ──────────────────────────────
            foreach ($data['contactos'] as $contactoData) {
                $contacto = null;

                if (! empty($contactoData['curp'])) {
                    $contacto = ContactoFamiliar::where('curp', $contactoData['curp'])->first();
                }
                if (! $contacto && ! empty($contactoData['telefono_celular'])) {
                    $contacto = ContactoFamiliar::where('telefono_celular', $contactoData['telefono_celular'])->first();
                }

                if ($contacto) {
                    if (! $contacto->familia_id) {
                        $contacto->update(['familia_id' => $familiaId]);
                    }
                } else {
                    $contacto = ContactoFamiliar::create([
                        'familia_id' => $familiaId,
                        'tiene_acceso_portal' => $contactoData['tiene_acceso_portal'] ?? false,
                        'usuario_id' => null,
                        'nombre' => $contactoData['nombre'],
                        'ap_paterno' => $contactoData['ap_paterno'] ?? null,
                        'ap_materno' => $contactoData['ap_materno'] ?? null,
                        'telefono_celular' => $contactoData['telefono_celular'],
                        'telefono_trabajo' => $contactoData['telefono_trabajo'] ?? null,
                        'email' => $contactoData['email'] ?? null,
                        'curp' => $contactoData['curp'] ?? null,
                    ]);
                }

                AlumnoContacto::create([
                    'alumno_id' => $alumno->id,
                    'contacto_id' => $contacto->id,
                    'parentesco' => $contactoData['parentesco'],
                    'tipo' => $contactoData['tipo'],
                    'orden' => $contactoData['orden'],
                    'autorizado_recoger' => $contactoData['autorizado_recoger'] ?? false,
                    'es_responsable_pago' => $contactoData['es_responsable_pago'] ?? false,
                    'activo' => true,
                ]);
            }

            // ── 5. Documentos requeridos ──────────────────
            foreach ($this->documentosPorGrupo($data['grupo_id']) as $doc) {
                DocumentoAlumno::create([
                    'alumno_id' => $alumno->id,
                    'tipo_documento' => $doc,
                    'estado' => 'pendiente',
                ]);
            }

            // ── 6. Vincular prospecto si aplica ───────────
            if (! empty($data['prospecto_id'])) {
                Prospecto::where('id', $data['prospecto_id'])
                    ->update(['alumno_id' => $alumno->id, 'etapa' => 'inscrito']);
            }

            // ── 7. Auditoría ──────────────────────────────
            Auditoria::registrar('alumno', $alumno->id, 'insert', null, $alumno->toArray());

            DB::commit();

            $mensaje = "Alumno '{$alumno->nombre} {$alumno->ap_paterno}' registrado. Matrícula: {$alumno->matricula}";

            if (request()->ajax()) {
                return response()->json([
                    'message' => $mensaje,
                    'alumno' => $alumno->load(['familia', 'inscripciones.grupo', 'contactos']),
                ], 201);
            }

            return redirect()
                ->route('alumnos.show', $alumno->id)
                ->with('success', $mensaje);

        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->respuestaError('Error al registrar el alumno: '.$e->getMessage());
        }
    }

    /** GET /alumnos/{id}/edit */
    public function edit(int $id)
    {
        $alumno = Alumno::with(['familia', 'contactos'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($alumno);
        }
        $inscripciones = $alumno->inscripciones()->with('grupo.ciclo', 'grupo.grado.nivel')->get();
        $niveles = NivelEscolar::activo()->get();

        return view('alumnos.edit', compact('alumno', 'inscripciones', 'niveles'));
    }

    /** PUT /alumnos/{id} */
    public function update(UpdateAlumnoRequest $request, int $id)
    {
        $alumno = Alumno::findOrFail($id);
        $anterior = $alumno->toArray();

        $campos = $request->validated();

        // Procesar foto si viene en el request
        // Al igual que en store(), el archivo no está en validated()
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($alumno->foto_url) {
                Storage::disk('public')->delete($alumno->foto_url);
            }
            $campos['foto_url'] = $request->file('foto')->store('alumnos/fotos', 'public');
        }

        $alumno->update($campos);

        Auditoria::registrar('alumno', $alumno->id, 'update', $anterior, $alumno->fresh()->toArray());

        $mensaje = 'Datos del alumno actualizados correctamente.';

        if (request()->ajax()) {
            return response()->json([
                'message' => $mensaje,
                'alumno' => $alumno->fresh(),
            ]);
        }

        return redirect()
            ->route('alumnos.show', $alumno->id)
            ->with('success', $mensaje);
    }

    /**
     * GET /alumnos/{id}/hermanos
     * Solo AJAX — usada desde la ficha del alumno.
     */
    public function hermanos(int $id)
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
    public function estadoCuenta(Request $request, int $id)
    {
        $alumno = Alumno::with([
            'inscripciones.grupo.grado.nivel',
            'inscripciones.ciclo',
        ])->findOrFail($id);

        $inscripcionActual = $alumno->inscripciones
            ->where('activo', true)
            ->sortByDesc('id')
            ->first();

        // Ciclos en los que el alumno ha estado inscrito (para el selector de filtro)
        $ciclosAlumno = CicloEscolar::whereHas('inscripciones', fn ($q) => $q->where('alumno_id', $alumno->id)
        )->orderByDesc('fecha_inicio')->get();

        // Cargos con detalles de pagos vigentes y políticas del plan
        $cargosQuery = Cargo::with([
            'concepto',
            'detallesPagosVigentes.pago:id,folio_recibo,fecha_pago,forma_pago,referencia,estado',
            'asignacion.plan.politicasDescuentoActivas',
            'asignacion.plan.politicasRecargo',
        ])
            ->whereHas('inscripcion', fn ($q) => $q->where('alumno_id', $alumno->id))
            ->withSum('detallesPagosVigentes as total_abonado', 'monto_abonado');

        if ($request->filled('ciclo_id')) {
            $cargosQuery->whereHas('inscripcion', fn ($q) => $q->where('ciclo_id', $request->ciclo_id)
            );
        }

        $cargos = $cargosQuery->orderBy('fecha_vencimiento')->get();

        // Becas activas en el ciclo actual, indexadas por concepto_id para lookup O(1)
        $becas = BecaAlumno::with(['catalogoBeca', 'concepto'])
            ->where('alumno_id', $alumno->id)
            ->where('activo', true)
            ->when($inscripcionActual, fn ($q) => $q->where('ciclo_id', $inscripcionActual->ciclo_id)
            )
            ->where(fn ($q) => $q
                ->whereNull('vigencia_fin')
                ->orWhere('vigencia_fin', '>=', now())
            )
            ->get()
            ->keyBy('concepto_id');

        // ── Resumen ───────────────────────────────────────
        $hoy = now();
        $totalCargado = 0;
        $totalPagado = 0;
        $totalCondonado = 0;
        $totalVencido = 0;
        $totalRecargos = 0;
        $totalDescuentos = 0;
        $totalBecas = 0;
        $cargosPendientes = 0;
        $cargosVencidos = 0;

        foreach ($cargos as $cargo) {
            $abonado = (float) ($cargo->total_abonado ?? 0);
            $saldoBase = max(0, (float) $cargo->monto_original - $abonado);
            $vencido = $hoy->gt($cargo->fecha_vencimiento);
            $esPendiente = ! in_array($cargo->estado, ['pagado', 'condonado']) && $saldoBase > 0;

            // ── Descuento de beca para este concepto ──
            $becaDescuento = 0.0;
            $becaPorcentaje = null;

            if ($esPendiente) {
                $becaItem = $becas->get($cargo->concepto_id);
                if ($becaItem) {
                    $becaDescuento = min(
                        $becaItem->calcularDescuento((float) $cargo->monto_original),
                        $saldoBase
                    );
                    $becaPorcentaje = $becaItem->catalogoBeca->tipo === 'porcentaje'
                        ? (float) $becaItem->catalogoBeca->valor
                        : null;
                }
            }

            // ── Calcular recargo / descuento según política del plan ──
            $descuento = 0.0;
            $recargo = 0.0;

            if ($esPendiente && $cargo->asignacion?->plan) {
                $plan = $cargo->asignacion->plan;

                if ($vencido) {
                    // Meses de retraso: meses completos desde el vencimiento + 1
                    $mesesRetraso = (int) $cargo->fecha_vencimiento->diffInMonths($hoy) + 1;

                    // Cargo vencido → aplicar recargo si existe política activa
                    $pr = $plan->politicasRecargo->firstWhere('activo', true);
                    if ($pr) {
                        $recargo = $pr->calcular($saldoBase, $mesesRetraso);
                    }
                } else {
                    $mesesRetraso = 0;

                    // Cargo vigente → aplicar descuento si existe política que aplique hoy
                    $pd = $plan->politicasDescuentoActivas->first(fn ($p) => $p->aplicaHoy());
                    if ($pd) {
                        $descuento = $pd->calcular($saldoBase);
                    }
                }
            } else {
                $mesesRetraso = 0;
            }

            // Guardar valores calculados en el modelo (accesibles en la vista)
            $cargo->beca_descuento_calc = $becaDescuento;
            $cargo->beca_porcentaje = $becaPorcentaje;
            $cargo->descuento_calc = $descuento;
            $cargo->recargo_calc = $recargo;
            $cargo->meses_retraso = $mesesRetraso;
            $cargo->monto_a_pagar_hoy = max(0, $saldoBase - $becaDescuento - $descuento + $recargo);

            // ── Acumuladores ──
            $totalCargado += (float) $cargo->monto_original;

            if ($cargo->estado === 'condonado') {
                $totalCondonado += (float) $cargo->monto_original;
            }

            $totalPagado += $abonado;

            if ($esPendiente) {
                $totalBecas += $becaDescuento;
                if ($vencido) {
                    $totalVencido += $cargo->monto_a_pagar_hoy;
                    $totalRecargos += $recargo;
                    $cargosVencidos++;
                } else {
                    $totalDescuentos += $descuento;
                    $cargosPendientes++;
                }
            }
        }

        $saldoPendienteBase = max(0, $totalCargado - $totalPagado - $totalCondonado);

        $resumen = [
            'total_cargado' => $totalCargado,
            'total_pagado' => $totalPagado,
            'total_condonado' => $totalCondonado,
            'saldo_pendiente' => $saldoPendienteBase,
            'total_vencido' => $totalVencido,
            'total_recargos' => $totalRecargos,
            'total_descuentos' => $totalDescuentos,
            'total_becas' => $totalBecas,
            'total_a_pagar_hoy' => max(0, $saldoPendienteBase - $totalBecas + $totalRecargos - $totalDescuentos),
            'total_cargos' => $cargos->count(),
            'cargos_pendientes' => $cargosPendientes,
            'cargos_vencidos' => $cargosVencidos,
        ];

        return view('alumnos.estado-cuenta', compact(
            'alumno', 'inscripcionActual', 'ciclosAlumno', 'cargos', 'resumen', 'becas'
        ));
    }

    // ── Helpers privados ─────────────────────────────────

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
            return [
                'alumno' => [],
                'apellido_familia' => '',
                'contactos' => [],
            ];
        }

        [$nombre, $apPaterno, $apMaterno] = $this->separarNombreCompleto($prospecto->nombre);
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
        $nombre = implode(' ', $partes);

        return [$nombre, $apPaterno, $apMaterno];
    }
}
