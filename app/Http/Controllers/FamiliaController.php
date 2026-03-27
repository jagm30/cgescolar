<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\ContactoFamiliar;
use App\Models\Familia;
use App\Models\Usuario;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FamiliaController extends Controller
{
    use RespondsWithJson;

    /**
     * GET /familias
     * Lista todas las familias con sus alumnos activos.
     * Recepción puede ver, solo admin puede crear/editar.
     */
    public function index(Request $request)
    {
        $familias = Familia::with([
                'alumnos' => fn($q) => $q->orderBy('ap_paterno'),
                'contactos',
            ])
            ->when($request->filled('buscar'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('apellido_familia', 'like', "%{$request->buscar}%")
                  ->orWhereHas('alumnos', fn($q) => $q
                      ->where('ap_paterno', 'like', "%{$request->buscar}%")
                      ->orWhere('nombre',   'like', "%{$request->buscar}%")
                      ->orWhere('matricula','like', "%{$request->buscar}%")
                  )
                  ->orWhereHas('contactos', fn($q) => $q
                      ->where('nombre',           'like', "%{$request->buscar}%")
                      ->orWhere('telefono_celular','like', "%{$request->buscar}%")
                      ->orWhere('email',           'like', "%{$request->buscar}%")
                  );
            }))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->boolean('activo')))
            ->withCount('alumnos')
            ->orderBy('apellido_familia')
            ->paginate($request->get('per_page', 20));

        if ($request->ajax()) {
            return response()->json($familias);
        }

        return view('familias.index', compact('familias'));
    }

    /**
     * GET /familias/{id}
     * Detalle completo: alumnos, contactos, acceso al portal
     * y resumen de estado de cuenta del ciclo activo.
     */
    public function show(int $id)
    {
        $cicloId = auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $familia = Familia::with([
            'alumnos' => fn($q) => $q->with([
                'inscripciones' => fn($q) => $q
                    ->where('ciclo_id', $cicloId)
                    ->where('activo', true)
                    ->with('grupo.grado.nivel'),
                'documentos',
            ]),
            'contactos' => fn($q) => $q->with('usuario'),
        ])->findOrFail($id);

        // Estado de cuenta resumido por alumno
        $estadoCuenta = $familia->alumnos->map(function ($alumno) use ($cicloId) {
            $inscripcion = $alumno->inscripciones->first();
            if (!$inscripcion) return null;

            $cargos = $inscripcion->cargos()
                ->with('detallesPagosVigentes')
                ->get();

            return [
                'alumno_id'       => $alumno->id,
                'alumno'          => $alumno->nombre . ' ' . $alumno->ap_paterno,
                'total_pendiente' => $cargos->sum(fn($c) => $c->saldo_pendiente_base),
                'total_pagado'    => $cargos->sum(fn($c) => $c->saldo_abonado),
                'cargos_vencidos' => $cargos->filter(fn($c) => str_contains($c->estado_real, 'vencido'))->count(),
            ];
        })->filter()->values();

        if (request()->ajax()) {
            return response()->json(compact('familia', 'estadoCuenta'));
        }

        return view('familias.show', compact('familia', 'estadoCuenta', 'cicloId'));
    }

    /**
     * GET /familias/create
     * Solo administrador.
     */
    public function create()
    {
        return view('familias.create');
    }

    /**
     * POST /familias
     * Crea una familia sin alumnos (se vincularán al registrar alumnos).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'apellido_familia' => ['required', 'string', 'max:200'],
            'observaciones'    => ['nullable', 'string', 'max:1000'],
        ], [
            'apellido_familia.required' => 'El apellido de la familia es obligatorio.',
        ]);

        $data['activo'] = true;
        $familia = Familia::create($data);

        Auditoria::registrar('familia', $familia->id, 'insert', null, $familia->toArray());

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: ['familia' => $familia],
            mensaje: "Familia '{$familia->apellido_familia}' creada correctamente.",
            jsonStatus: 201
        );
    }

    /**
     * GET /familias/{id}/edit
     */
    public function edit(int $id)
    {
        $familia = Familia::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($familia);
        }

        return view('familias.edit', compact('familia'));
    }

    /**
     * PUT /familias/{id}
     */
    public function update(Request $request, int $id)
    {
        $familia  = Familia::findOrFail($id);
        $anterior = $familia->toArray();

        $data = $request->validate([
            'apellido_familia' => ['sometimes', 'required', 'string', 'max:200'],
            'observaciones'    => ['nullable', 'string', 'max:1000'],
            'activo'           => ['boolean'],
        ]);

        $familia->update($data);

        Auditoria::registrar('familia', $familia->id, 'update', $anterior, $familia->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: ['familia' => $familia->fresh()],
            mensaje: "Familia '{$familia->apellido_familia}' actualizada correctamente."
        );
    }

    // =======================================================
    // GESTIÓN DE CONTACTOS Y ACCESO AL PORTAL
    // =======================================================

    /**
     * GET /familias/{id}/contactos
     * Lista contactos de la familia con estado de acceso al portal.
     * Solo AJAX — se usa desde la vista show con jQuery.
     */
    public function contactos(int $id)
    {
        $familia = Familia::findOrFail($id);

        $contactos = $familia->contactos()
            ->with('usuario')
            ->get()
            ->map(fn($c) => [
                'id'                  => $c->id,
                'nombre_completo'     => trim("{$c->nombre} {$c->ap_paterno} {$c->ap_materno}"),
                'telefono_celular'    => $c->telefono_celular,
                'email'               => $c->email,
                'tiene_acceso_portal' => $c->tiene_acceso_portal,
                'usuario_id'          => $c->usuario_id,
                'usuario_email'       => $c->usuario?->email,
                'usuario_activo'      => $c->usuario?->activo,
                'estado_portal'       => $this->estadoPortal($c),
            ]);

        return response()->json($contactos);
    }

    /**
     * POST /familias/contactos/{contactoId}/habilitar-portal
     * El admin marca que este contacto DEBE tener acceso al portal.
     * No crea el usuario — solo activa la bandera.
     */
    public function habilitarPortal(int $contactoId)
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::findOrFail($contactoId);
        $anterior = $contacto->toArray();

        $contacto->update(['tiene_acceso_portal' => true]);

        Auditoria::registrar('contacto_familiar', $contacto->id, 'update', $anterior, ['tiene_acceso_portal' => true]);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: ['contacto' => $contacto->fresh()],
            mensaje: "Acceso al portal habilitado para {$contacto->nombre}. Ahora puedes crearle un usuario."
        );
    }

    /**
     * POST /familias/contactos/{contactoId}/deshabilitar-portal
     * Revoca el acceso al portal del contacto.
     * Si tiene usuario, lo desactiva también.
     */
    public function deshabilitarPortal(int $contactoId)
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::with('usuario')->findOrFail($contactoId);
        $anterior = $contacto->toArray();

        DB::beginTransaction();
        try {
            // Desactivar usuario si existe
            if ($contacto->usuario) {
                $contacto->usuario->update(['activo' => false]);
            }

            $contacto->update(['tiene_acceso_portal' => false]);

            Auditoria::registrar('contacto_familiar', $contacto->id, 'update', $anterior, [
                'tiene_acceso_portal' => false,
                'usuario_desactivado' => $contacto->usuario_id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respuestaError('Error al deshabilitar acceso: ' . $e->getMessage());
        }

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: ['contacto' => $contacto->fresh()],
            mensaje: "Acceso al portal deshabilitado para {$contacto->nombre}."
        );
    }

    /**
     * POST /familias/contactos/{contactoId}/crear-usuario
     * Crea el usuario del portal para un contacto que ya tiene
     * tiene_acceso_portal = true pero aún no tiene usuario_id.
     */
    public function crearUsuario(Request $request, int $contactoId)
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::findOrFail($contactoId);

        // Validaciones de negocio
        if (!$contacto->tiene_acceso_portal) {
            return $this->respuestaError(
                "El contacto no tiene habilitado el acceso al portal. Habilítalo primero."
            );
        }

        if ($contacto->usuario_id) {
            return $this->respuestaError(
                "Este contacto ya tiene un usuario asignado ({$contacto->usuario?->email})."
            );
        }

        if (empty($contacto->email)) {
            return $this->respuestaError(
                "El contacto no tiene correo electrónico registrado. Actualiza sus datos primero."
            );
        }

        $data = $request->validate([
            'email'    => ['nullable', 'email', 'max:200', 'unique:usuario,email'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'email.unique' => 'Este correo ya está registrado en el sistema.',
        ]);

        // Usar email del contacto si no se especificó uno distinto
        $email    = $data['email']    ?? $contacto->email;
        $password = $data['password'] ?? Str::random(10);

        // Verificar que el email no esté ya en uso
        if (Usuario::where('email', $email)->exists()) {
            return $this->respuestaError("El correo {$email} ya está registrado en el sistema.");
        }

        DB::beginTransaction();
        try {
            $usuario = Usuario::create([
                'nombre'        => trim("{$contacto->nombre} {$contacto->ap_paterno}"),
                'email'         => $email,
                'password_hash' => Hash::make($password),
                'rol'           => 'padre',
                'activo'        => true,
            ]);

            $contacto->update(['usuario_id' => $usuario->id]);

            Auditoria::registrar('usuario', $usuario->id, 'insert', null, [
                'contacto_id' => $contacto->id,
                'email'       => $email,
                'rol'         => 'padre',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->respuestaError('Error al crear el usuario: ' . $e->getMessage());
        }

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: [
                'usuario'          => $usuario,
                'password_inicial' => $password, // Mostrar al admin para entregársela al padre
            ],
            mensaje: "Usuario creado para {$contacto->nombre}. Email: {$email}",
            jsonStatus: 201
        );
    }

    /**
     * POST /familias/contactos/{contactoId}/resetear-password
     * Genera una nueva contraseña temporal para el usuario padre.
     * Solo administrador.
     */
    public function resetearPassword(int $contactoId)
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::with('usuario')->findOrFail($contactoId);

        if (!$contacto->usuario_id || !$contacto->usuario) {
            return $this->respuestaError(
                "Este contacto no tiene un usuario registrado en el sistema."
            );
        }

        $nuevaPassword = Str::random(10);

        $contacto->usuario->update([
            'password_hash' => Hash::make($nuevaPassword),
            'activo'        => true, // Reactivar si estaba desactivado
        ]);

        Auditoria::registrar('usuario', $contacto->usuario_id, 'update', [], [
            'accion'      => 'reseteo_password',
            'contacto_id' => $contacto->id,
        ]);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: [
                'nueva_password' => $nuevaPassword, // Mostrar al admin
                'email'          => $contacto->usuario->email,
            ],
            mensaje: "Contraseña reseteada para {$contacto->nombre}. Entrega la nueva contraseña al padre de familia."
        );
    }

    // =======================================================
    // Helpers privados
    // =======================================================

    /**
     * Determina el estado del portal de un contacto en texto legible.
     */
    private function estadoPortal(ContactoFamiliar $contacto): string
    {
        if (!$contacto->tiene_acceso_portal) {
            return 'sin_acceso';
        }

        if (!$contacto->usuario_id) {
            return 'pendiente'; // Habilitado pero sin usuario creado
        }

        if (!$contacto->usuario?->activo) {
            return 'desactivado';
        }

        return 'activo';
    }

    private function soloAdmin(): void
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }
}
