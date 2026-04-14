<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\ContactoFamiliar;
use App\Models\Familia;
use App\Models\Usuario;
use App\Models\AlumnoContacto;
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
     */
    public function index(Request $request)
    {
        $query = Familia::query()
            ->withCount([
                'alumnos as alumnos_count',
                'alumnos as alumnos_activos_count' => fn($q) => $q->where('estado', 'activo'),
            ])
            ->with([
                'alumnos' => fn($q) => $q->orderBy('ap_paterno'),
                'contactos',   // sin orderBy — se ordena en la vista con sortBy('pivot.orden')
            ]);

        // Búsqueda por nombre de familia o alumno
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($query) use ($q) {
                $query->where('apellido_familia', 'like', "%{$q}%")
                      ->orWhereHas('alumnos', fn($q2) =>
                            $q2->where('nombre', 'like', "%{$q}%")
                               ->orWhere('ap_paterno', 'like', "%{$q}%")
                               ->orWhere('matricula', 'like', "%{$q}%")
                      )
                      ->orWhereHas('contactos', fn($q2) =>
                            $q2->where('nombre', 'like', "%{$q}%")
                               ->orWhere('ap_paterno', 'like', "%{$q}%")
                               ->orWhere('telefono_celular', 'like', "%{$q}%")
                      );
            });
        }

        // Filtro activo/inactivo (default: todas)
        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $familias = $query
            ->orderBy('apellido_familia')
            ->paginate(20)
            ->withQueryString();

        return view('familias.index', compact('familias'));
    }

    /**
     * GET /familias/create
     */
    public function create()
    {
        return view('familias.create');
    }

    /**
     * POST /familias
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'apellido_familia'                    => ['required', 'string', 'max:200'],
            'observaciones'                       => ['nullable', 'string', 'max:1000'],
            'contactos'                           => ['required', 'array', 'min:1', 'max:3'],
            'contactos.*.nombre'                  => ['required', 'string', 'max:100'],
            'contactos.*.ap_paterno'              => ['nullable', 'string', 'max:100'],
            'contactos.*.ap_materno'              => ['nullable', 'string', 'max:100'],
            'contactos.*.telefono_celular'        => ['required', 'string', 'max:20'],
            'contactos.*.email'                   => ['nullable', 'email', 'max:200'],
            'contactos.*.curp'                    => ['nullable', 'string', 'size:18'],
            'contactos.*.telefono_trabajo'         => ['nullable', 'string', 'max:20'],
            'contactos.*.tiene_acceso_portal'     => ['boolean'],
            // parentesco, tipo, orden, autorizado_recoger, es_responsable_pago
            // son del pivot alumno_contacto — se asignan al inscribir al alumno
        ], [
            'apellido_familia.required'              => 'El nombre de la familia es obligatorio.',
            'contactos.required'                     => 'Agrega al menos un contacto familiar.',
            'contactos.*.nombre.required'            => 'El nombre del contacto es obligatorio.',
            'contactos.*.telefono_celular.required'  => 'El teléfono del contacto es obligatorio.',
            'contactos.*.curp.size'                  => 'La CURP debe tener exactamente 18 caracteres.',
        ]);

        // Crear la familia
        $familia = Familia::create([
            'apellido_familia' => $data['apellido_familia'],
            'observaciones'    => $data['observaciones'] ?? null,
            'activo'           => true,
        ]);

        // Crear los contactos
        // IMPORTANTE: contacto_familiar solo tiene datos personales del contacto.
        // Los datos del pivot (parentesco, tipo, orden, permisos) van en alumno_contacto
        // y solo se crean cuando se vincula el contacto a un alumno específico.
        foreach ($data['contactos'] as $ctcData) {
            ContactoFamiliar::create([
                'familia_id'          => $familia->id,
                'nombre'              => $ctcData['nombre'],
                'ap_paterno'          => $ctcData['ap_paterno']          ?? null,
                'ap_materno'          => $ctcData['ap_materno']          ?? null,
                'telefono_celular'    => $ctcData['telefono_celular']    ?? null,
                'telefono_trabajo'    => $ctcData['telefono_trabajo']    ?? null,
                'email'               => $ctcData['email']               ?? null,
                'curp'                => $ctcData['curp']                ?? null,
                'tiene_acceso_portal' => $ctcData['tiene_acceso_portal'] ?? false,
                // usuario_id: null — se asigna después desde el panel de usuarios
                // foto_url: null — se sube desde el perfil del contacto
            ]);
        }

        \App\Models\Auditoria::registrar(
            'familia',
            $familia->id,
            'insert',
            null,
            $familia->toArray()
        );

        return redirect()
            ->route('familias.show', $familia->id)
            ->with('success', "Familia \"{$familia->apellido_familia}\" registrada correctamente.");
    }
    /**
     * GET /familias/{id}
     * Si viene ?_modal=1 devuelve solo el partial HTML para el modal.
     * Si no, devuelve la vista completa.
     */
    public function show(Request $request, int $id)
    {
        $familia = \App\Models\Familia::with([
            'alumnos' => fn($q) => $q->with([
                'inscripciones' => fn($q) => $q
                    ->with(['grupo.grado.nivel', 'ciclo'])
                    ->orderByDesc('id'),
            ])->orderBy('ap_paterno'),
    
            'contactos' => fn($q) => $q->with([
                'alumnoContactos' => fn($q) => $q
                    ->with('alumno:id,nombre,ap_paterno')
                    ->where('activo', true),
            ]),
        ])->findOrFail($id);
    
        if ($request->has('_modal')) {
            // Devolver el partial con manejo de errores explícito
            try {
                $html = view('familias._modal', compact('familia'))->render();
                return response($html, 200)
                    ->header('Content-Type', 'text/html');
            } catch (\Throwable $e) {
                // Devolver el error real para poder depurarlo
                return response(
                    '<div class="alert alert-danger" style="margin:16px;">' .
                    '<strong>Error:</strong> ' . e($e->getMessage()) .
                    '<br><small>' . e($e->getFile()) . ':' . $e->getLine() . '</small>' .
                    '</div>',
                    500
                )->header('Content-Type', 'text/html');
            }
        }
    
        return view('familias.show', compact('familia'));
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
     * PUT /familias/contactos/{contactoId}
     * Actualiza los datos de un contacto familiar y su pivot con el alumno.
     * Solo AJAX — llamado desde la vista edit de alumno.
     */
    public function actualizarContacto(Request $request, int $contactoId)
    {
        $contacto = \App\Models\ContactoFamiliar::findOrFail($contactoId);

        $data = $request->validate([
            'nombre'              => ['required', 'string', 'max:100'],
            'ap_paterno'          => ['nullable', 'string', 'max:100'],
            'ap_materno'          => ['nullable', 'string', 'max:100'],
            'telefono_celular'    => ['required', 'string', 'max:20'],
            'email'               => ['nullable', 'email', 'max:200'],
            'parentesco'          => ['nullable', 'string', 'in:padre,madre,abuelo,tio,otro'],
            'autorizado_recoger'  => ['boolean'],
            'es_responsable_pago' => ['boolean'],
            'tiene_acceso_portal' => ['boolean'],
        ], [
            'nombre.required'           => 'El nombre del contacto es obligatorio.',
            'telefono_celular.required' => 'El teléfono es obligatorio.',
            'email.email'               => 'El formato del correo no es válido.',
        ]);

        $anterior = $contacto->toArray();

        // Actualizar datos del contacto
        $contacto->update([
            'nombre'              => $data['nombre'],
            'ap_paterno'          => $data['ap_paterno'] ?? null,
            'ap_materno'          => $data['ap_materno'] ?? null,
            'telefono_celular'    => $data['telefono_celular'],
            'email'               => $data['email'] ?? null,
            'tiene_acceso_portal' => $data['tiene_acceso_portal'] ?? false,
        ]);

        // Actualizar pivot alumno_contacto (parentesco, permisos)
        if (isset($data['parentesco'])) {
            \App\Models\AlumnoContacto::where('contacto_id', $contacto->id)
                ->update([
                    'parentesco'          => $data['parentesco'],
                    'autorizado_recoger'  => $data['autorizado_recoger']  ?? false,
                    'es_responsable_pago' => $data['es_responsable_pago'] ?? false,
                ]);
        }

        \App\Models\Auditoria::registrar(
            'contacto_familiar',
            $contacto->id,
            'update',
            $anterior,
            $contacto->fresh()->toArray()
        );

        return response()->json([
            'message'  => "Contacto '{$contacto->nombre}' actualizado correctamente.",
            'contacto' => $contacto->fresh(),
        ]);
    }
    /**
     * POST /familias/contactos
     * Crea un nuevo contacto familiar y lo vincula al alumno.
     * Solo AJAX — llamado desde la vista edit de alumno.
     */
    public function agregarContacto(Request $request)
    {
        $data = $request->validate([
            'alumno_id'           => ['required', 'integer', 'exists:alumno,id'],
            'familia_id'          => ['nullable', 'integer', 'exists:familia,id'],
            'nombre'              => ['required', 'string', 'max:100'],
            'ap_paterno'          => ['nullable', 'string', 'max:100'],
            'ap_materno'          => ['nullable', 'string', 'max:100'],
            'telefono_celular'    => ['required', 'string', 'max:20'],
            'email'               => ['nullable', 'email', 'max:200'],
            'curp'                => ['nullable', 'string', 'size:18'],
            'parentesco'          => ['required', 'string', 'in:padre,madre,abuelo,tio,otro'],
            'tipo'                => ['required', 'string', 'in:padre,tutor,tercero_autorizado'],
            'orden'               => ['integer', 'min:1', 'max:3'],
            'autorizado_recoger'  => ['boolean'],
            'es_responsable_pago' => ['boolean'],
            'tiene_acceso_portal' => ['boolean'],
        ], [
            'alumno_id.exists'          => 'El alumno no existe.',
            'nombre.required'           => 'El nombre del contacto es obligatorio.',
            'telefono_celular.required' => 'El teléfono es obligatorio.',
            'parentesco.required'       => 'El parentesco es obligatorio.',
            'tipo.required'             => 'El tipo de contacto es obligatorio.',
            'curp.size'                 => 'La CURP debe tener exactamente 18 caracteres.',
        ]);
    
        // Si el alumno tiene familia, usar ese familia_id
        $alumno = \App\Models\Alumno::findOrFail($data['alumno_id']);
        $familiaId = $data['familia_id'] ?? $alumno->familia_id;
    
        // Crear el contacto
        $contacto = \App\Models\ContactoFamiliar::create([
            'familia_id'          => $familiaId,
            'nombre'              => $data['nombre'],
            'ap_paterno'          => $data['ap_paterno'] ?? null,
            'ap_materno'          => $data['ap_materno'] ?? null,
            'telefono_celular'    => $data['telefono_celular'],
            'email'               => $data['email'] ?? null,
            'curp'                => $data['curp'] ?? null,
            'tiene_acceso_portal' => $data['tiene_acceso_portal'] ?? false,
        ]);
    
        // Vincular al alumno en la tabla pivot alumno_contacto
        $pivot = \App\Models\AlumnoContacto::create([
            'alumno_id'           => $alumno->id,
            'contacto_id'         => $contacto->id,
            'parentesco'          => $data['parentesco'],
            'tipo'                => $data['tipo'],
            'orden'               => $data['orden'] ?? 2,
            'autorizado_recoger'  => $data['autorizado_recoger']  ?? false,
            'es_responsable_pago' => $data['es_responsable_pago'] ?? false,
            'activo'              => true,
        ]);
    
        \App\Models\Auditoria::registrar(
            'contacto_familiar',
            $contacto->id,
            'insert',
            null,
            $contacto->toArray()
        );
    
        return response()->json([
            'message'  => "Contacto '{$contacto->nombre}' agregado correctamente.",
            'contacto' => $contacto->fresh(),
            'pivot'    => $pivot,
        ], 201);
    }

    /**
     * DELETE /familias/contactos/{contactoId}
     * Elimina un contacto familiar, verificando que quede al menos uno.
     */
    public function eliminarContacto(int $contactoId)
    {
        $contacto = \App\Models\ContactoFamiliar::findOrFail($contactoId);

        // Buscar el vínculo activo de este contacto con un alumno
        $alumnoContacto = \App\Models\AlumnoContacto::where('contacto_id', $contactoId)
            ->where('activo', true)
            ->first();

        if ($alumnoContacto) {
            // Contar cuántos contactos activos tiene ese alumno
            $totalContactos = \App\Models\AlumnoContacto::where('alumno_id', $alumnoContacto->alumno_id)
                ->where('activo', true)
                ->count();

            if ($totalContactos <= 1) {
                return response()->json([
                    'message' => 'No se puede eliminar el único contacto del alumno. Debe haber al menos uno.'
                ], 422);
            }
        }

        $nombre = trim($contacto->nombre . ' ' . $contacto->ap_paterno);

        // Eliminar pivot primero
        \App\Models\AlumnoContacto::where('contacto_id', $contactoId)->delete();

        // Eliminar el contacto
        $contacto->delete();

        \App\Models\Auditoria::registrar(
            'contacto_familiar',
            $contactoId,
            'delete',
            ['nombre' => $nombre],
            null
        );

        return response()->json([
            'message' => "Contacto '{$nombre}' eliminado correctamente."
        ]);
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
