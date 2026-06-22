<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoContacto;
use App\Models\Auditoria;
use App\Models\ContactoFamiliar;
use App\Models\Familia;
use App\Models\Usuario;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FamiliaController extends Controller
{
    use RespondsWithJson;

    /** GET /familias */
    public function index(Request $request): View
    {
        $familias = Familia::query()
            ->withCount([
                'alumnos as alumnos_count',
                'alumnos as alumnos_activos_count' => fn ($q) => $q->where('estado', 'activo'),
            ])
            ->with([
                'alumnos' => fn ($q) => $q->orderBy('ap_paterno'),
                'contactos', // se ordena en la vista con sortBy('pivot.orden')
            ])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($q) => $q
                ->where('apellido_familia', 'like', "%{$request->q}%")
                ->orWhereHas('alumnos', fn ($q) => $q
                    ->where('nombre', 'like', "%{$request->q}%")
                    ->orWhere('ap_paterno', 'like', "%{$request->q}%")
                    ->orWhere('matricula', 'like', "%{$request->q}%")
                )
                ->orWhereHas('contactos', fn ($q) => $q
                    ->where('nombre', 'like', "%{$request->q}%")
                    ->orWhere('ap_paterno', 'like', "%{$request->q}%")
                    ->orWhere('telefono_celular', 'like', "%{$request->q}%")
                )
            ))
            ->when($request->filled('activo'), fn ($q) => $q->where('activo', $request->boolean('activo')))
            ->orderBy('apellido_familia')
            ->paginate(20)
            ->withQueryString();

        return view('familias.index', [
            'familias' => $familias,
            'totalActivas' => Familia::where('activo', true)->count(),
            'totalAlumnos' => Alumno::where('estado', 'activo')->count(),
        ]);
    }

    /** GET /familias/create */
    public function create(): View
    {
        return view('familias.create');
    }

    /** POST /familias */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'apellido_familia' => ['required', 'string', 'max:200'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'contactos' => ['required', 'array', 'min:1', 'max:3'],
            'contactos.*.nombre' => ['required', 'string', 'max:100'],
            'contactos.*.ap_paterno' => ['nullable', 'string', 'max:100'],
            'contactos.*.ap_materno' => ['nullable', 'string', 'max:100'],
            'contactos.*.telefono_celular' => ['required', 'string', 'max:20'],
            'contactos.*.email' => ['nullable', 'email', 'max:200'],
            'contactos.*.curp' => ['nullable', 'string', 'size:18'],
            'contactos.*.telefono_trabajo' => ['nullable', 'string', 'max:20'],
            'contactos.*.tiene_acceso_portal' => ['boolean'],
        ], [
            'apellido_familia.required' => 'El nombre de la familia es obligatorio.',
            'contactos.required' => 'Agrega al menos un contacto familiar.',
            'contactos.*.nombre.required' => 'El nombre del contacto es obligatorio.',
            'contactos.*.telefono_celular.required' => 'El teléfono del contacto es obligatorio.',
            'contactos.*.curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
        ]);

        $familia = Familia::create([
            'apellido_familia' => $data['apellido_familia'],
            'observaciones' => $data['observaciones'] ?? null,
            'activo' => true,
        ]);

        // contacto_familiar solo almacena datos personales del contacto.
        // Los datos del pivot (parentesco, tipo, orden, permisos) van en
        // alumno_contacto y se crean al vincular el contacto a un alumno.
        foreach ($data['contactos'] as $ctcData) {
            ContactoFamiliar::create([
                'familia_id' => $familia->id,
                'nombre' => $ctcData['nombre'],
                'ap_paterno' => $ctcData['ap_paterno'] ?? null,
                'ap_materno' => $ctcData['ap_materno'] ?? null,
                'telefono_celular' => $ctcData['telefono_celular'] ?? null,
                'telefono_trabajo' => $ctcData['telefono_trabajo'] ?? null,
                'email' => $ctcData['email'] ?? null,
                'curp' => $ctcData['curp'] ?? null,
                'tiene_acceso_portal' => $ctcData['tiene_acceso_portal'] ?? false,
            ]);
        }

        Auditoria::registrar('familia', $familia->id, 'insert', null, $familia->toArray());

        return redirect()
            ->route('familias.show', $familia->id)
            ->with('success', "Familia \"{$familia->apellido_familia}\" registrada correctamente.");
    }

    /**
     * GET /familias/{id}
     * Con ?_modal=1 devuelve el partial HTML para el modal; si no, la vista completa.
     */
    public function show(Request $request, int $id): View|Response
    {
        $familia = Familia::with([
            'alumnos' => fn ($q) => $q->with([
                'inscripciones' => fn ($q) => $q
                    ->with(['grupo.grado.nivel', 'ciclo'])
                    ->orderByDesc('id'),
            ])->orderBy('ap_paterno'),
            'contactos' => fn ($q) => $q->with([
                'alumnoContactos' => fn ($q) => $q
                    ->with('alumno:id,nombre,ap_paterno')
                    ->where('activo', true),
                'razonesSociales' => fn ($q) => $q
                    ->where('activo', true)
                    ->orderByDesc('es_principal')
                    ->orderBy('id'),
            ]),
        ])->findOrFail($id);

        if ($request->has('_modal')) {
            try {
                $html = view('familias._modal', compact('familia'))->render();

                return response($html, 200)->header('Content-Type', 'text/html');
            } catch (\Throwable $e) {
                return response(
                    '<div class="alert alert-danger" style="margin:16px;">'.
                    '<strong>Error:</strong> '.e($e->getMessage()).
                    '<br><small>'.e($e->getFile()).':'.$e->getLine().'</small>'.
                    '</div>',
                    500
                )->header('Content-Type', 'text/html');
            }
        }

        return view('familias.show', compact('familia'));
    }

    /** GET /familias/{id}/edit */
    public function edit(int $id): View|JsonResponse
    {
        $familia = Familia::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($familia);
        }

        return view('familias.edit', compact('familia'));
    }

    /** PUT /familias/{id} */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $familia = Familia::findOrFail($id);
        $anterior = $familia->toArray();

        $data = $request->validate([
            'apellido_familia' => ['sometimes', 'required', 'string', 'max:200'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'activo' => ['boolean'],
        ]);

        $familia->update($data);

        Auditoria::registrar('familia', $familia->id, 'update', $anterior, $familia->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: ['familia' => $familia->fresh()],
            mensaje: "Familia '{$familia->apellido_familia}' actualizada correctamente."
        );
    }

    // ══════════════════════════════════════════════════════════
    // GESTIÓN DE CONTACTOS Y ACCESO AL PORTAL
    // ══════════════════════════════════════════════════════════

    /**
     * GET /familias/{id}/contactos
     * Lista contactos con estado de acceso al portal. Solo AJAX.
     */
    public function contactos(int $id): JsonResponse
    {
        $contactos = Familia::findOrFail($id)
            ->contactos()
            ->with('usuario')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre_completo' => trim("{$c->nombre} {$c->ap_paterno} {$c->ap_materno}"),
                'telefono_celular' => $c->telefono_celular,
                'email' => $c->email,
                'tiene_acceso_portal' => $c->tiene_acceso_portal,
                'usuario_id' => $c->usuario_id,
                'usuario_email' => $c->usuario?->email,
                'usuario_activo' => $c->usuario?->activo,
                'estado_portal' => $this->estadoPortal($c),
            ]);

        return response()->json($contactos);
    }

    /**
     * GET /familias/{id}/contactos-enlace
     * Retorna datos de los contactos para pre-llenar el formulario de creación de alumno.
     */
    public function contactosParaEnlace(int $id): JsonResponse
    {
        $contactos = Familia::findOrFail($id)
            ->contactos()
            ->get()
            ->map(fn ($c) => [
                'nombre' => $c->nombre,
                'ap_paterno' => $c->ap_paterno ?? '',
                'ap_materno' => $c->ap_materno ?? '',
                'telefono_celular' => $c->telefono_celular,
                'email' => $c->email ?? '',
                'curp' => $c->curp ?? '',
                'tiene_acceso_portal' => $c->tiene_acceso_portal ? '1' : '0',
            ]);

        return response()->json($contactos);
    }

    /**
     * PUT /familias/contactos/{contactoId}
     * Actualiza datos del contacto y su pivot con el alumno. Solo AJAX.
     */
    public function actualizarContacto(Request $request, int $contactoId): JsonResponse
    {
        $contacto = ContactoFamiliar::findOrFail($contactoId);
        $anterior = $contacto->toArray();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['nullable', 'string', 'max:100'],
            'ap_materno' => ['nullable', 'string', 'max:100'],
            'telefono_celular' => ['required', 'string', 'max:20'],
            'telefono_2' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:200'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'lugar_trabajo' => ['nullable', 'string', 'max:200'],
            'puesto' => ['nullable', 'string', 'max:100'],
            'nivel_estudios' => ['nullable', 'string', 'max:100'],
            'profesion' => ['nullable', 'string', 'max:100'],
            'vive' => ['boolean'],
            'parentesco' => ['nullable', 'string', 'in:padre,madre,abuelo,tio,otro'],
            'tipo' => ['nullable', 'string', 'in:padre,tutor,tercero_autorizado'],
            'orden' => ['nullable', 'integer', 'min:1', 'max:3'],
            'autorizado_recoger' => ['boolean'],
            'es_responsable_pago' => ['boolean'],
            'tiene_acceso_portal' => ['boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ], [
            'nombre.required' => 'El nombre del contacto es obligatorio.',
            'telefono_celular.required' => 'El teléfono es obligatorio.',
            'email.email' => 'El formato del correo no es válido.',
        ]);

        $campos = [
            'nombre' => $data['nombre'],
            'ap_paterno' => $data['ap_paterno'] ?? null,
            'ap_materno' => $data['ap_materno'] ?? null,
            'telefono_celular' => $data['telefono_celular'],
            'telefono_2' => $data['telefono_2'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'email' => $data['email'] ?? null,
            'lugar_trabajo' => $data['lugar_trabajo'] ?? null,
            'puesto' => $data['puesto'] ?? null,
            'nivel_estudios' => $data['nivel_estudios'] ?? null,
            'profesion' => $data['profesion'] ?? null,
            'vive' => $data['vive'] ?? true,
            'tiene_acceso_portal' => $data['tiene_acceso_portal'] ?? false,
        ];

        if ($request->hasFile('foto')) {
            if ($contacto->foto_url) {
                Storage::disk('public')->delete($contacto->foto_url);
            }
            $campos['foto_url'] = $request->file('foto')->store('contactos/fotos', 'public');
        }

        $contacto->update($campos);

        AlumnoContacto::where('contacto_id', $contacto->id)->update([
            'parentesco' => $data['parentesco'] ?? null,
            'tipo' => $data['tipo'] ?? null,
            'orden' => $data['orden'] ?? null,
            'autorizado_recoger' => $data['autorizado_recoger'] ?? false,
            'es_responsable_pago' => $data['es_responsable_pago'] ?? false,
        ]);

        Auditoria::registrar('contacto_familiar', $contacto->id, 'update', $anterior, $contacto->fresh()->toArray());

        return response()->json([
            'message' => "Contacto '{$contacto->nombre}' actualizado correctamente.",
            'contacto' => $contacto->fresh(),
        ]);
    }

    /**
     * POST /familias/contactos/{contactoId}/foto
     * Sube o reemplaza la foto de un contacto existente. Solo AJAX.
     */
    public function subirFotoContacto(Request $request, int $contactoId): JsonResponse
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Selecciona una imagen.',
            'foto.image' => 'El archivo debe ser una imagen.',
            'foto.mimes' => 'Solo se permiten JPG, PNG o WEBP.',
            'foto.max' => 'La imagen no debe superar los 2 MB.',
        ]);

        $contacto = ContactoFamiliar::findOrFail($contactoId);

        if ($contacto->foto_url) {
            Storage::disk('public')->delete($contacto->foto_url);
        }

        $ruta = $request->file('foto')->store('contactos/fotos', 'public');
        $contacto->update(['foto_url' => $ruta]);

        return response()->json([
            'message' => 'Foto actualizada correctamente.',
            'foto_url' => asset('storage/'.$ruta),
        ]);
    }

    /**
     * POST /familias/contactos
     * Crea un nuevo contacto y lo vincula al alumno. Solo AJAX.
     */
    public function agregarContacto(Request $request): JsonResponse
    {
        $data = $request->validate([
            'alumno_id' => ['required', 'integer', 'exists:alumno,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familia,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'ap_paterno' => ['nullable', 'string', 'max:100'],
            'ap_materno' => ['nullable', 'string', 'max:100'],
            'telefono_celular' => ['required', 'string', 'max:20'],
            'telefono_2' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:200'],
            'curp' => ['nullable', 'string', 'size:18'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'lugar_trabajo' => ['nullable', 'string', 'max:200'],
            'puesto' => ['nullable', 'string', 'max:100'],
            'nivel_estudios' => ['nullable', 'string', 'max:100'],
            'profesion' => ['nullable', 'string', 'max:100'],
            'vive' => ['boolean'],
            'parentesco' => ['required', 'string', 'in:padre,madre,abuelo,tio,otro'],
            'tipo' => ['required', 'string', 'in:padre,tutor,tercero_autorizado'],
            'orden' => ['integer', 'min:1', 'max:3'],
            'autorizado_recoger' => ['boolean'],
            'es_responsable_pago' => ['boolean'],
            'tiene_acceso_portal' => ['boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ], [
            'alumno_id.exists' => 'El alumno no existe.',
            'nombre.required' => 'El nombre del contacto es obligatorio.',
            'telefono_celular.required' => 'El teléfono es obligatorio.',
            'parentesco.required' => 'El parentesco es obligatorio.',
            'tipo.required' => 'El tipo de contacto es obligatorio.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
        ]);

        $alumno = Alumno::findOrFail($data['alumno_id']);
        $familiaId = $data['familia_id'] ?? $alumno->familia_id;

        $contacto = ContactoFamiliar::create([
            'familia_id' => $familiaId,
            'nombre' => $data['nombre'],
            'ap_paterno' => $data['ap_paterno'] ?? null,
            'ap_materno' => $data['ap_materno'] ?? null,
            'telefono_celular' => $data['telefono_celular'],
            'telefono_2' => $data['telefono_2'] ?? null,
            'email' => $data['email'] ?? null,
            'curp' => $data['curp'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'lugar_trabajo' => $data['lugar_trabajo'] ?? null,
            'puesto' => $data['puesto'] ?? null,
            'nivel_estudios' => $data['nivel_estudios'] ?? null,
            'profesion' => $data['profesion'] ?? null,
            'vive' => $data['vive'] ?? true,
            'tiene_acceso_portal' => $data['tiene_acceso_portal'] ?? false,
        ]);

        if ($request->hasFile('foto')) {
            $contacto->update([
                'foto_url' => $request->file('foto')->store('contactos/fotos', 'public'),
            ]);
        }

        $pivot = AlumnoContacto::create([
            'alumno_id' => $alumno->id,
            'contacto_id' => $contacto->id,
            'parentesco' => $data['parentesco'],
            'tipo' => $data['tipo'],
            'orden' => $data['orden'] ?? 2,
            'autorizado_recoger' => $data['autorizado_recoger'] ?? false,
            'es_responsable_pago' => $data['es_responsable_pago'] ?? false,
            'activo' => true,
        ]);

        Auditoria::registrar('contacto_familiar', $contacto->id, 'insert', null, $contacto->toArray());

        return response()->json([
            'message' => "Contacto '{$contacto->nombre}' agregado correctamente.",
            'contacto' => $contacto->fresh(),
            'pivot' => $pivot,
        ], 201);
    }

    /**
     * DELETE /familias/contactos/{contactoId}
     * Elimina un contacto verificando que quede al menos uno activo.
     */
    public function eliminarContacto(int $contactoId): JsonResponse
    {
        $contacto = ContactoFamiliar::findOrFail($contactoId);
        $alumnoContacto = AlumnoContacto::where('contacto_id', $contactoId)
            ->where('activo', true)
            ->first();

        if ($alumnoContacto) {
            $totalContactos = AlumnoContacto::where('alumno_id', $alumnoContacto->alumno_id)
                ->where('activo', true)
                ->count();

            if ($totalContactos <= 1) {
                return response()->json([
                    'message' => 'No se puede eliminar el único contacto del alumno. Debe haber al menos uno.',
                ], 422);
            }
        }

        $nombre = trim("{$contacto->nombre} {$contacto->ap_paterno}");

        AlumnoContacto::where('contacto_id', $contactoId)->delete();
        $contacto->delete();

        Auditoria::registrar('contacto_familiar', $contactoId, 'delete', ['nombre' => $nombre], null);

        return response()->json(['message' => "Contacto '{$nombre}' eliminado correctamente."]);
    }

    /**
     * POST /familias/contactos/{contactoId}/habilitar-portal
     * Activa la bandera de acceso al portal (no crea el usuario).
     */
    public function habilitarPortal(int $contactoId): RedirectResponse|JsonResponse
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
     * Revoca el acceso al portal y desactiva el usuario si existe.
     */
    public function deshabilitarPortal(int $contactoId): RedirectResponse|JsonResponse
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::with('usuario')->findOrFail($contactoId);
        $anterior = $contacto->toArray();

        try {
            DB::transaction(function () use ($contacto, $anterior) {
                if ($contacto->usuario) {
                    $contacto->usuario->update(['activo' => false]);
                }

                $contacto->update(['tiene_acceso_portal' => false]);

                Auditoria::registrar('contacto_familiar', $contacto->id, 'update', $anterior, [
                    'tiene_acceso_portal' => false,
                    'usuario_desactivado' => $contacto->usuario_id,
                ]);
            });
        } catch (\Throwable $e) {
            return $this->respuestaError('Error al deshabilitar acceso: '.$e->getMessage());
        }

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: ['contacto' => $contacto->fresh()],
            mensaje: "Acceso al portal deshabilitado para {$contacto->nombre}."
        );
    }

    /**
     * POST /familias/contactos/{contactoId}/crear-usuario
     * Crea el usuario del portal para un contacto con tiene_acceso_portal = true.
     */
    public function crearUsuario(Request $request, int $contactoId): RedirectResponse|JsonResponse
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::findOrFail($contactoId);

        if (! $contacto->tiene_acceso_portal) {
            return $this->respuestaError('El contacto no tiene habilitado el acceso al portal. Habilítalo primero.');
        }

        if ($contacto->usuario_id) {
            return $this->respuestaError("Este contacto ya tiene un usuario asignado ({$contacto->usuario?->email}).");
        }

        if (empty($contacto->email)) {
            return $this->respuestaError('El contacto no tiene correo electrónico registrado. Actualiza sus datos primero.');
        }

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:200', 'unique:usuario,email'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'email.unique' => 'Este correo ya está registrado en el sistema.',
        ]);

        $email = $data['email'] ?? $contacto->email;
        $password = $data['password'] ?? Str::random(10);

        if (Usuario::where('email', $email)->exists()) {
            return $this->respuestaError("El correo {$email} ya está registrado en el sistema.");
        }

        try {
            $usuario = DB::transaction(function () use ($contacto, $email, $password): Usuario {
                $usuario = Usuario::create([
                    'nombre' => trim("{$contacto->nombre} {$contacto->ap_paterno}"),
                    'email' => $email,
                    'password_hash' => Hash::make($password),
                    'rol' => 'padre',
                    'activo' => true,
                ]);

                $contacto->update(['usuario_id' => $usuario->id]);

                Auditoria::registrar('usuario', $usuario->id, 'insert', null, [
                    'contacto_id' => $contacto->id,
                    'email' => $email,
                    'rol' => 'padre',
                ]);

                return $usuario;
            });
        } catch (\Throwable $e) {
            return $this->respuestaError('Error al crear el usuario: '.$e->getMessage());
        }

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: [
                'usuario' => $usuario,
                'password_inicial' => $password, // Mostrar al admin para entregársela al padre
            ],
            mensaje: "Usuario creado para {$contacto->nombre}. Email: {$email}",
            jsonStatus: 201
        );
    }

    /**
     * POST /familias/contactos/{contactoId}/resetear-password
     * Genera una nueva contraseña temporal para el usuario padre. Solo admin.
     */
    public function resetearPassword(int $contactoId): RedirectResponse|JsonResponse
    {
        $this->soloAdmin();

        $contacto = ContactoFamiliar::with('usuario')->findOrFail($contactoId);

        if (! $contacto->usuario_id || ! $contacto->usuario) {
            return $this->respuestaError('Este contacto no tiene un usuario registrado en el sistema.');
        }

        $nuevaPassword = Str::random(10);

        $contacto->usuario->update([
            'password_hash' => Hash::make($nuevaPassword),
            'activo' => true, // Reactivar si estaba desactivado
        ]);

        Auditoria::registrar('usuario', $contacto->usuario_id, 'update', [], [
            'accion' => 'reseteo_password',
            'contacto_id' => $contacto->id,
        ]);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            jsonData: [
                'nueva_password' => $nuevaPassword, // Mostrar al admin
                'email' => $contacto->usuario->email,
            ],
            mensaje: "Contraseña reseteada para {$contacto->nombre}. Entrega la nueva contraseña al padre de familia."
        );
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /** Determina el estado de acceso al portal de un contacto. */
    private function estadoPortal(ContactoFamiliar $contacto): string
    {
        if (! $contacto->tiene_acceso_portal) {
            return 'sin_acceso';
        }
        if (! $contacto->usuario_id) {
            return 'pendiente';
        } // Habilitado pero sin usuario
        if (! $contacto->usuario?->activo) {
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
