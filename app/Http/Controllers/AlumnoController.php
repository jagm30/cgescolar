<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\AlumnoContacto;
use App\Models\Auditoria;
use App\Models\ContactoFamiliar;
use App\Models\DocumentoAlumno;
use App\Models\Familia;
use App\Models\Inscripcion;
use App\Models\Prospecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    /** GET /alumnos */
    public function index(Request $request): JsonResponse
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $query = Alumno::with(['familia', 'inscripciones' => function ($q) use ($cicloId) {
                $q->where('ciclo_id', $cicloId)->with('grupo.grado.nivel');
            }])
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('nivel_id'), fn($q) => $q->whereHas('inscripciones', function ($q) use ($request, $cicloId) {
                $q->where('ciclo_id', $cicloId)
                  ->whereHas('grupo.grado', fn($q) => $q->where('nivel_id', $request->nivel_id));
            }))
            ->when($request->filled('grupo_id'), fn($q) => $q->whereHas('inscripciones', fn($q) =>
                $q->where('ciclo_id', $cicloId)->where('grupo_id', $request->grupo_id)
            ))
            ->when($request->filled('buscar'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('ap_paterno', 'like', "%{$request->buscar}%")
                  ->orWhere('matricula', 'like', "%{$request->buscar}%")
                  ->orWhere('curp', 'like', "%{$request->buscar}%");
            }))
            ->orderBy('ap_paterno')
            ->orderBy('nombre');

        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    /** GET /alumnos/{id} */
    public function show(int $id): JsonResponse
    {
        $alumno = Alumno::with([
            'familia',
            'inscripciones.grupo.grado.nivel',
            'contactos',
            'documentos',
            'becas.catalogoBeca',
        ])->findOrFail($id);

        return response()->json($alumno);
    }

    /**
     * POST /alumnos
     * Registra alumno, familia (si es nueva), contactos, inscripción
     * y documentos requeridos en una sola transacción.
     */
    public function store(StoreAlumnoRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            // ── 1. Familia ────────────────────────────────
            if (!empty($data['familia_id'])) {
                $familiaId = $data['familia_id'];
            } else {
                $familia   = Familia::create(['apellido_familia' => $data['apellido_familia']]);
                $familiaId = $familia->id;
            }

            // ── 2. Alumno ─────────────────────────────────
            $matricula = $this->generarMatricula($data['ciclo_id']);

            $alumno = Alumno::create([
                'familia_id'        => $familiaId,
                'matricula'         => $matricula,
                'nombre'            => $data['nombre'],
                'ap_paterno'        => $data['ap_paterno'],
                'ap_materno'        => $data['ap_materno'] ?? null,
                'fecha_nacimiento'  => $data['fecha_nacimiento'],
                'curp'              => $data['curp'] ?? null,
                'genero'            => $data['genero'] ?? null,
                'foto_url'          => $data['foto_url'] ?? null,
                'observaciones'     => $data['observaciones'] ?? null,
                'fecha_inscripcion' => $data['fecha_inscripcion'],
                'estado'            => 'activo',
            ]);

            // ── 3. Inscripción ────────────────────────────
            Inscripcion::create([
                'alumno_id' => $alumno->id,
                'ciclo_id'  => $data['ciclo_id'],
                'grupo_id'  => $data['grupo_id'],
                'fecha'     => $data['fecha_inscripcion'],
                'activo'    => true,
            ]);

            // ── 4. Contactos ──────────────────────────────
            foreach ($data['contactos'] as $contactoData) {
                // Buscar contacto existente por CURP o teléfono
                $contacto = null;

                if (!empty($contactoData['curp'])) {
                    $contacto = ContactoFamiliar::where('curp', $contactoData['curp'])->first();
                }

                if (!$contacto && !empty($contactoData['telefono_celular'])) {
                    $contacto = ContactoFamiliar::where('telefono_celular', $contactoData['telefono_celular'])->first();
                }

                if ($contacto) {
                    // Actualizar familia_id si no lo tenía
                    if (!$contacto->familia_id) {
                        $contacto->update(['familia_id' => $familiaId]);
                    }
                } else {
                    // Crear nuevo contacto
                    $contacto = ContactoFamiliar::create([
                        'familia_id'          => $familiaId,
                        'tiene_acceso_portal' => $contactoData['tiene_acceso_portal'] ?? false,
                        'usuario_id'          => null,
                        'nombre'              => $contactoData['nombre'],
                        'ap_paterno'          => $contactoData['ap_paterno'] ?? null,
                        'ap_materno'          => $contactoData['ap_materno'] ?? null,
                        'telefono_celular'    => $contactoData['telefono_celular'],
                        'telefono_trabajo'    => $contactoData['telefono_trabajo'] ?? null,
                        'email'               => $contactoData['email'] ?? null,
                        'curp'                => $contactoData['curp'] ?? null,
                    ]);
                }

                // Vincular contacto con alumno
                AlumnoContacto::create([
                    'alumno_id'           => $alumno->id,
                    'contacto_id'         => $contacto->id,
                    'parentesco'          => $contactoData['parentesco'],
                    'tipo'                => $contactoData['tipo'],
                    'orden'               => $contactoData['orden'],
                    'autorizado_recoger'  => $contactoData['autorizado_recoger'] ?? false,
                    'es_responsable_pago' => $contactoData['es_responsable_pago'] ?? false,
                    'activo'              => true,
                ]);
            }

            // ── 5. Documentos requeridos ──────────────────
            $documentos = $this->documentosPorNivel($data['ciclo_id'], $data['grupo_id']);
            foreach ($documentos as $doc) {
                DocumentoAlumno::create([
                    'alumno_id'      => $alumno->id,
                    'tipo_documento' => $doc,
                    'estado'         => 'pendiente',
                ]);
            }

            // ── 6. Actualizar prospecto si aplica ─────────
            if (!empty($data['prospecto_id'])) {
                Prospecto::where('id', $data['prospecto_id'])
                         ->update(['alumno_id' => $alumno->id, 'etapa' => 'inscrito']);
            }

            // ── 7. Auditoría ──────────────────────────────
            Auditoria::registrar('alumno', $alumno->id, 'insert', null, $alumno->toArray());

            DB::commit();

            return response()->json(
                $alumno->load(['familia', 'inscripciones.grupo', 'contactos', 'documentos']),
                201
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar el alumno: ' . $e->getMessage()], 500);
        }
    }

    /** PUT /alumnos/{id} */
    public function update(UpdateAlumnoRequest $request, int $id): JsonResponse
    {
        $alumno   = Alumno::findOrFail($id);
        $anterior = $alumno->toArray();

        $alumno->update($request->validated());

        Auditoria::registrar('alumno', $alumno->id, 'update', $anterior, $alumno->fresh()->toArray());

        return response()->json($alumno->fresh());
    }

    /**
     * GET /alumnos/{id}/hermanos
     * Devuelve los hermanos del alumno en la misma familia.
     */
    public function hermanos(int $id): JsonResponse
    {
        $alumno = Alumno::findOrFail($id);

        if (!$alumno->familia_id) {
            return response()->json([]);
        }

        $hermanos = Alumno::where('familia_id', $alumno->familia_id)
            ->where('id', '!=', $alumno->id)
            ->with(['inscripciones' => fn($q) => $q->where('activo', true)->with('grupo.grado.nivel')])
            ->get();

        return response()->json($hermanos);
    }

    /**
     * GET /alumnos/{id}/estado-cuenta
     * Devuelve todos los cargos del alumno en el ciclo actual con su estado.
     */
    public function estadoCuenta(int $id): JsonResponse
    {
        $alumno  = Alumno::findOrFail($id);
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? \App\Models\CicloEscolar::activo()->value('id');

        $inscripcion = $alumno->inscripciones()
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->first();

        if (!$inscripcion) {
            return response()->json(['message' => 'El alumno no tiene inscripción activa en este ciclo.'], 404);
        }

        $cargos = $inscripcion->cargos()
            ->with('concepto', 'pagosVigentes', 'descuentos')
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(function ($cargo) {
                return [
                    'id'               => $cargo->id,
                    'concepto'         => $cargo->concepto->nombre,
                    'periodo'          => $cargo->periodo,
                    'monto_original'   => $cargo->monto_original,
                    'saldo_abonado'    => $cargo->saldo_abonado,
                    'saldo_pendiente'  => $cargo->saldo_pendiente_base,
                    'estado_real'      => $cargo->estado_real,
                    'fecha_vencimiento'=> $cargo->fecha_vencimiento,
                ];
            });

        $resumen = [
            'total_cargos'    => $cargos->count(),
            'total_pagado'    => $cargos->sum('saldo_abonado'),
            'total_pendiente' => $cargos->sum('saldo_pendiente'),
            'cargos_vencidos' => $cargos->filter(fn($c) => str_contains($c['estado_real'], 'vencido'))->count(),
        ];

        return response()->json(['resumen' => $resumen, 'cargos' => $cargos]);
    }

    // ── Helpers privados ─────────────────────────────────

    private function generarMatricula(int $cicloId): string
    {
        $ciclo = \App\Models\CicloEscolar::find($cicloId);
        $año   = substr($ciclo->nombre, 0, 4);
        $ultimo = Alumno::where('matricula', 'like', "{$año}-%")
            ->orderByDesc('matricula')
            ->value('matricula');

        $siguiente = $ultimo ? (int) substr($ultimo, -4) + 1 : 1;

        return $año . '-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }

    private function documentosPorNivel(int $cicloId, int $grupoId): array
    {
        $grupo  = \App\Models\Grupo::with('grado.nivel')->find($grupoId);
        $nivel  = $grupo?->grado?->nivel?->nombre ?? '';

        $base = ['Acta de nacimiento', 'CURP', 'Comprobante de domicilio', 'Fotos tamaño infantil'];

        return match(true) {
            in_array($nivel, ['Maternal', 'Preescolar']) => array_merge($base, ['Cartilla de vacunación']),
            $nivel === 'Primaria'   => array_merge($base, ['Boletas ciclo anterior']),
            $nivel === 'Secundaria' => array_merge($base, ['Boletas ciclo anterior', 'Certificado de estudios primaria']),
            default => $base,
        };
    }
}
