<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Auditoria;
use App\Models\ContactoFamiliar;
use App\Models\NivelEscolar;
use App\Models\Personal;
use App\Models\Usuario;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesAccesoMail;
use App\Mail\AlertaModificacionUsuarioMail; // <-- Importamos la nueva alerta

class UsuarioController extends Controller
{
    use RespondsWithJson;

    /** GET /usuarios - Modificado con Paginación Dinámica */
    public function index(Request $request)
    {
        $mostrar = $request->input('mostrar', 10);
        $esDirector = auth()->user()->esDirectorSeccion();

        $usuarios = Usuario::with('cicloSeleccionado')
            ->when($esDirector, fn($q) => $q->where('rol', 'padre'))
            ->when(!$esDirector && $request->filled('rol'), fn($q) => $q->where('rol', $request->rol))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->activo))
            ->when($request->filled('buscar'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            }))
            ->when(
                $request->filled('seccion_id') && ($request->rol === 'padre' || $esDirector),
                fn($q) => $q->whereHas(
                    'contactoFamiliar.familia.alumnos.inscripciones.grupo.grado',
                    fn($gq) => $gq->where('nivel_id', $request->seccion_id)
                )
            )
            ->orderBy('rol')->orderBy('nombre')
            ->paginate($mostrar);

        if ($request->ajax()) {
            return response()->json($usuarios);
        }

        $niveles = NivelEscolar::activo()->get();

        return view('usuarios.index', [
            'usuarios'   => $usuarios,
            'esDirector' => $esDirector,
            'niveles'    => $niveles,
        ]);
    }

    /** GET /usuarios/pendientes-portal (UNIFICADO: Padres + Personal) */
    public function pendientesPortal(Request $request)
    {
        $usuario = auth()->user();

        $padres = ContactoFamiliar::with([
                'familia.alumnos.inscripciones' => fn($q) => $q->where('activo', true)->with('grupo.grado'),
            ])
            ->where('tiene_acceso_portal', true)
            ->whereNull('usuario_id')
            ->get()
            ->map(function($c) {
                $nivelIds = collect();

                foreach ($c->familia?->alumnos ?? [] as $alumno) {
                    foreach ($alumno->inscripciones as $inscripcion) {
                        $nivelId = $inscripcion->grupo?->grado?->nivel_id;
                        if ($nivelId) {
                            $nivelIds->push($nivelId);
                        }
                    }
                }

                $alumnos = $c->familia?->alumnos
                    ->map(fn($a) => trim("{$a->nombre} {$a->ap_paterno} {$a->ap_materno}"))
                    ->implode(', ') ?: '—';

                return (object)[
                    'id'              => $c->id,
                    'tipo'            => 'contacto',
                    'nombre_completo' => trim("{$c->nombre} {$c->ap_paterno} {$c->ap_materno}"),
                    'referencia'      => $c->familia->apellido_familia ?? 'Sin Familia',
                    'email'           => $c->email,
                    'nivel_ids'       => $nivelIds->unique()->values()->implode(','),
                    'alumnos'         => $alumnos,
                ];
            });

        // Director de sección solo gestiona padres de familia
        if ($usuario->esDirectorSeccion()) {
            $pendientes = $padres->sortBy('referencia');
            $rolesDisponibles = ['padre' => 'Padre de Familia'];
        } else {
            $empleados = Personal::where('tiene_acceso_sistema', true)
                ->whereNull('usuario_id')
                ->get()
                ->map(function($p) {
                    return (object)[
                        'id'              => $p->id,
                        'tipo'            => 'personal',
                        'nombre_completo' => trim("{$p->nombre} {$p->ap_paterno} {$p->ap_materno}"),
                        'referencia'      => $p->tipo ?? 'Sin Puesto',
                        'email'           => $p->email,
                    ];
                });

            $pendientes = $padres->concat($empleados)->sortBy('referencia');

            $rolesDisponibles = [
                'recepcion'              => 'Recepción',
                'caja'                   => 'Caja',
                'admisiones'             => 'Admisiones',
                'informacion_admisiones' => 'Información y Admisiones',
                'director_seccion'       => 'Director de Sección',
            ];

            if ($usuario->esAdministrador()) {
                $rolesDisponibles['administrador'] = 'Administrador';
            }
        }

        if ($request->ajax()) {
            return response()->json($pendientes);
        }

        return view('usuarios.pendientes-portal', [
            'pendientes'       => $pendientes,
            'rolesDisponibles' => $rolesDisponibles,
            'esDirector'       => $usuario->esDirectorSeccion(),
            'niveles'          => NivelEscolar::activo()->get(),
        ]);
    }

    /** GET /usuarios/create */
    public function create()
    {
        $contactosPendientes = ContactoFamiliar::with('familia')
            ->where('tiene_acceso_portal', true)
            ->whereNull('usuario_id')
            ->get();

        return view('usuarios.create', compact('contactosPendientes'));
    }

    /** POST /usuarios */
    public function store(Request $request)
    {
        try {
            $esDirector = auth()->user()->esDirectorSeccion();

            $request->validate([
                'nombre'   => 'required|string|max:255',
                'email'    => 'required|email|unique:usuario,email',
                'rol'      => 'required|string',
                'password' => 'required|string|min:6',
                'seccion'  => 'nullable|string|in:maternal,preescolar,primaria,secundaria,todas',
            ], [
                'email.unique' => "El correo '{$request->email}' ya está registrado en el sistema.",
            ]);

            // Director de sección solo puede crear cuentas de padre
            $rolFinal = $esDirector ? 'padre' : $request->rol;

            $usuario = Usuario::create([
                'nombre'        => $request->nombre,
                'email'         => $request->email,
                'password_hash' => Hash::make($request->password),
                'rol'           => $rolFinal,
                'seccion'       => $request->seccion,
                'activo'        => true,
            ]);

            $estadoCorreo = "";
            try {
                Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                    'nombre'   => $usuario->nombre,
                    'email'    => $usuario->email,
                    'password' => $request->password,
                    'rol'      => $usuario->rol
                ]));
                $estadoCorreo = " Correo con credenciales enviado con éxito.";
            } catch (\Exception $e) {
                $estadoCorreo = " (Advertencia: No se pudo enviar el correo electrónico).";
            }

            $mensajeFinal = "Usuario creado correctamente." . $estadoCorreo;

            $credenciales = [[
                'nombre'   => $usuario->nombre,
                'email'    => $usuario->email,
                'password' => $request->password,
                'rol'      => $usuario->rol
            ]];
            session()->put('credenciales_nuevas', $credenciales);
            session()->put('mensaje_persistente', $mensajeFinal);

            Auditoria::registrar('usuario', $usuario->id, 'insert', null, $usuario->toArray());

            return response()->json([
                'status' => 'success',
                'mensaje' => $mensajeFinal
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error', 
                'mensaje' => $e->validator->errors()->first()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'mensaje' => 'Fallo en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /** GET /usuarios/{id}/edit */
    public function edit(int $id)
    {
        $usuario = Usuario::findOrFail($id);

        if (request()->ajax()) {
            return response()->json($usuario);
        }

        return view('usuarios.edit', compact('usuario'));
    }

    /** PUT /usuarios/{id} - Editar */
    public function update(Request $request, int $id)
    {
        $usuario = Usuario::findOrFail($id);
        $anterior = $usuario->toArray();

        if ($usuario->id === auth()->id() && $request->rol !== $usuario->rol) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Medida de seguridad: No puedes cambiar tu propio rol para evitar la pérdida de acceso.'
            ], 403);
        }

        $request->validate([
            'rol'     => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:6'],
            'seccion'  => ['nullable', 'string', 'in:maternal,preescolar,primaria,secundaria,todas'],
        ]);

        $usuario->rol    = $request->rol;
        $usuario->seccion = $request->seccion;
        
        $estadoCorreo = "";
        $passwordPlana = $request->password;
        
        if (!empty($passwordPlana)) {
            $usuario->password_hash = Hash::make($passwordPlana);
            
            try {
                Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                    'nombre'   => $usuario->nombre,
                    'email'    => $usuario->email,
                    'password' => $passwordPlana,
                    'rol'      => $request->rol
                ]));
                $estadoCorreo = " Correo de actualización de contraseña enviado.";
            } catch (\Exception $e) {
                $estadoCorreo = " (Advertencia: Falló el envío del correo).";
            }
        }
        $usuario->save();

        Auditoria::registrar('usuario', $usuario->id, 'update', $anterior, $usuario->fresh()->toArray());

        // ── ALERTA DE SEGURIDAD A ADMINISTRADORES ──
        $cambiosDetectados = [];
        if ($anterior['rol'] !== $usuario->rol) {
            $cambiosDetectados[] = "Rol modificado de '{$anterior['rol']}' a '{$usuario->rol}'";
        }
        if (!empty($passwordPlana)) {
            $cambiosDetectados[] = "Contraseña de acceso reseteada/modificada";
        }

        if (!empty($cambiosDetectados)) {
            $admins = Usuario::where('rol', 'administrador')->where('activo', true)->pluck('email');
            if ($admins->isNotEmpty()) {
                try {
                    Mail::to($admins)->send(
                        new AlertaModificacionUsuarioMail(
                            $usuario->nombre,
                            $usuario->email,
                            $usuario->rol,
                            implode(" y ", $cambiosDetectados),
                            auth()->user()->nombre
                        )
                    );
                } catch (\Exception $e) {
                    // Falla silenciosa si no hay internet o error SMTP
                }
            }
        }

        if ($request->boolean('generar_pdf')) {
            $credenciales = [[
                'nombre'   => $usuario->nombre,
                'email'    => $usuario->email,
                'password' => !empty($passwordPlana) ? $passwordPlana : '*(Se mantuvo la contraseña anterior)*',
                'rol'      => $usuario->rol
            ]];
            session()->flash('credenciales_nuevas', $credenciales);
        }

        $mensajeFinal = "Usuario actualizado correctamente." . $estadoCorreo;
        session()->put('mensaje_persistente', $mensajeFinal);

        return response()->json([
            'status' => 'success',
            'mensaje' => "Usuario actualizado correctamente.",
            'pdf_generado' => $request->boolean('generar_pdf')
        ]);
    }

    /** DELETE /usuarios/{id} — desactiva */
    public function destroy(int $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return $this->respuestaError('No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => false]);

        Auditoria::registrar('usuario', $usuario->id, 'update', ['activo' => true], ['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'usuarios.index',
            mensaje: "Usuario '{$usuario->nombre}' desactivado correctamente."
        );
    }

    /** GET /perfil */
    public function perfil()
    {
        $usuario = auth()->user()->load('cicloSeleccionado');

        if (request()->ajax()) {
            return response()->json($usuario);
        }

        return view('usuarios.perfil', compact('usuario'));
    }

    /** POST /perfil/foto */
    public function actualizarFoto(Request $request)
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'foto.required' => 'Selecciona una imagen.',
            'foto.image'    => 'El archivo debe ser una imagen.',
            'foto.mimes'    => 'Solo se permiten formatos JPG, PNG o WEBP.',
            'foto.max'      => 'La imagen no debe superar 2 MB.',
        ]);

        $usuario = auth()->user();

        if ($usuario->foto_perfil) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($usuario->foto_perfil);
        }

        $ruta = $request->file('foto')->store('fotos-perfil', 'public');

        $usuario->update(['foto_perfil' => $ruta]);

        return $this->respuestaExito(
            redirectRoute: 'usuarios.perfil',
            jsonData: ['foto_url' => $usuario->foto_url],
            mensaje: 'Foto de perfil actualizada correctamente.',
        );
    }

    /** POST /usuarios/generar-masivos (PADRES Y PERSONAL) */
    public function generarUsuariosMasivos(Request $request)
    {
        try {
        $contactoIds  = $request->input('contacto_ids', []);
        $enviarCorreo = (bool) $request->input('enviar_correo', true);

        // Director de sección solo puede dar de alta padres de familia
        $personalDatos = auth()->user()->esDirectorSeccion()
            ? []
            : $request->input('personal_datos', []);

        // Validar correos duplicados antes de crear cualquier usuario
        $emailsRepetidos = [];

        foreach ($contactoIds as $id) {
            $contacto = ContactoFamiliar::find($id);
            if ($contacto && !$contacto->usuario_id && Usuario::where('email', $contacto->email)->exists()) {
                $emailsRepetidos[] = $contacto->email;
            }
        }

        foreach ($personalDatos as $empData) {
            $empleado = Personal::find($empData['id']);
            if ($empleado && !$empleado->usuario_id && Usuario::where('email', $empleado->email)->exists()) {
                $emailsRepetidos[] = $empleado->email;
            }
        }

        if (!empty($emailsRepetidos)) {
            return response()->json([
                'status'  => 'error',
                'mensaje' => 'Los siguientes correos ya están registrados en el sistema: ' . implode(', ', $emailsRepetidos),
            ], 422);
        }

        $usuariosCreados = [];
        $enviados = 0;
        $fallidos = 0;

        foreach ($contactoIds as $id) {
            $contacto = ContactoFamiliar::with('familia.alumnos')->findOrFail($id);
            if ($contacto->usuario_id) continue;

            $passwordPlana  = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
            $nombreCompleto = trim($contacto->nombre . ' ' . $contacto->ap_paterno . ' ' . $contacto->ap_materno);

            $alumnosCollection = $contacto->familia?->alumnos ?? collect();

            $alumnos = $alumnosCollection
                ->map(fn($a) => trim("{$a->nombre} {$a->ap_paterno} {$a->ap_materno}"))
                ->implode(', ') ?: null;

            $primerAlumno      = $alumnosCollection->first();
            $apellidosAlumno   = $primerAlumno
                ? trim("{$primerAlumno->ap_paterno} {$primerAlumno->ap_materno}")
                : null;

            $usuario = Usuario::create([
                'nombre'        => $nombreCompleto,
                'email'         => $contacto->email,
                'password_hash' => Hash::make($passwordPlana),
                'rol'           => 'padre',
                'activo'        => true,
            ]);
            $contacto->update(['usuario_id' => $usuario->id]);

            if ($enviarCorreo) {
                try {
                    Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                        'nombre'   => $nombreCompleto,
                        'email'    => $usuario->email,
                        'password' => $passwordPlana,
                        'rol'      => 'padre',
                    ]));
                    $enviados++;
                } catch (\Exception $e) {
                    $fallidos++;
                }
            }

            $usuariosCreados[] = ['nombre' => $nombreCompleto, 'email' => $usuario->email, 'password' => $passwordPlana, 'rol' => 'padre', 'alumnos' => $alumnos, 'apellidos_alumno' => $apellidosAlumno];
        }

        foreach ($personalDatos as $empData) {
            $empleado = Personal::findOrFail($empData['id']);
            if ($empleado->usuario_id) continue;

            $passwordPlana  = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
            $nombreCompleto = trim($empleado->nombre . ' ' . $empleado->ap_paterno . ' ' . $empleado->ap_materno);
            $rolSeleccionado = $empData['rol'] ?? 'recepcion';

            $usuario = Usuario::create([
                'nombre'        => $nombreCompleto,
                'email'         => $empleado->email,
                'password_hash' => Hash::make($passwordPlana),
                'rol'           => $rolSeleccionado,
                'activo'        => true,
            ]);
            $empleado->update(['usuario_id' => $usuario->id]);

            if ($enviarCorreo) {
                try {
                    Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                        'nombre'   => $nombreCompleto,
                        'email'    => $usuario->email,
                        'password' => $passwordPlana,
                        'rol'      => $rolSeleccionado,
                    ]));
                    $enviados++;
                } catch (\Exception $e) {
                    $fallidos++;
                }
            }

            $usuariosCreados[] = ['nombre' => $nombreCompleto, 'email' => $usuario->email, 'password' => $passwordPlana, 'rol' => $rolSeleccionado];
        }

        $msgCorreo = $enviarCorreo
            ? "Correos enviados: {$enviados}. Fallidos: {$fallidos}."
            : 'Correo omitido (desactivado). Descarga el PDF para entregar las credenciales.';

        $mensajeNotificacion = count($usuariosCreados) . " usuarios generados. {$msgCorreo}";
        
        // put() en lugar de flash() para que el PDF sobreviva más de una solicitud
        session()->put('credenciales_nuevas', $usuariosCreados);
        session()->put('mensaje_persistente', $mensajeNotificacion);

        return response()->json([
            'status'   => 'success',
            'mensaje'  => $mensajeNotificacion,
            'pdf_url'  => route('usuarios.credencialesPdf'),
        ]);

        } catch (\Illuminate\Database\QueryException $e) {
            $mensaje = str_contains($e->getMessage(), 'Duplicate entry')
                ? 'Uno o más correos electrónicos ya están registrados en el sistema. Verifica los datos antes de continuar.'
                : 'Error de base de datos: ' . $e->getMessage();

            return response()->json(['status' => 'error', 'mensaje' => $mensaje], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'mensaje' => 'Error inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** POST /usuarios/{id}/resetear-password-pdf */
    public function resetearPasswordPdf(int $id, Request $request)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->rol !== 'padre') {
            return response()->json(['status' => 'error', 'mensaje' => 'Esta acción solo aplica para padres de familia.'], 403);
        }

        $passwordPlana = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
        $usuario->password_hash = Hash::make($passwordPlana);
        $usuario->save();

        Auditoria::registrar('usuario', $usuario->id, 'update', ['password_hash' => '[oculto]'], ['password_hash' => '[nuevo hash]', 'accion' => 'reset rápido desde tabla']);

        $estadoCorreo = '';
        if ($request->boolean('enviar_correo')) {
            try {
                Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                    'nombre'   => $usuario->nombre,
                    'email'    => $usuario->email,
                    'password' => $passwordPlana,
                    'rol'      => $usuario->rol,
                ]));
                $estadoCorreo = ' Correo enviado correctamente.';
            } catch (\Exception $e) {
                $estadoCorreo = ' (Advertencia: no se pudo enviar el correo).';
            }
        }

        session()->put('credenciales_nuevas', [[
            'nombre'   => $usuario->nombre,
            'email'    => $usuario->email,
            'password' => $passwordPlana,
            'rol'      => $usuario->rol,
        ]]);

        return response()->json([
            'status'  => 'success',
            'mensaje' => "Contraseña de {$usuario->nombre} regenerada correctamente.{$estadoCorreo}",
            'pdf_url' => route('usuarios.credencialesPdf'),
        ]);
    }

    public function descargarCredencialesPdf()
    {
        $credenciales = session('credenciales_nuevas');

        if (!$credenciales) {
            return abort(404, 'No hay credenciales recientes para imprimir o la sesión caducó.');
        }

        // Limpiar sesión después de generar el PDF para no mostrar datos obsoletos
        session()->forget('credenciales_nuevas');

        if (count($credenciales) === 1) {
            $base = $credenciales[0]['apellidos_alumno'] ?? $credenciales[0]['nombre'];
            $nombreLimpio  = preg_replace('/[^A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s]/u', '', $base);
            $nombreLimpio  = str_replace(' ', '_', trim($nombreLimpio));
            $nombreArchivo = "acceso_{$nombreLimpio}.pdf";
        } else {
            $nombreArchivo = 'credenciales_' . now()->format('Y-m-d') . '_(' . count($credenciales) . '_usuarios).pdf';
        }

        $pdf = Pdf::loadView('usuarios.pdf-credenciales', compact('credenciales'));

        return $pdf->download($nombreArchivo);
    }

    /** POST /usuarios/{id}/reactivar */
    public function reactivar(int $id)
    {
        $usuario = Usuario::findOrFail($id);
        $anterior = $usuario->toArray();

        $usuario->update(['activo' => true]);
        Auditoria::registrar('usuario', $usuario->id, 'update', $anterior, $usuario->fresh()->toArray());

        return redirect()->route('usuarios.index')->with('mensaje', "Usuario reactivado con éxito.");
    }

   /** DELETE /usuarios/{id}/forzar-eliminar */
    public function forzarEliminar(int $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            if ($usuario->id === auth()->id()) {
                return response()->json([
                    'status' => 'error', 
                    'mensaje' => 'No puedes eliminar tu propia cuenta.'
                ], 403);
            }

            ContactoFamiliar::where('usuario_id', $usuario->id)
                ->update([
                    'usuario_id' => null,
                    'tiene_acceso_portal' => 0 
                ]);

            Personal::where('usuario_id', $usuario->id)
                ->update([
                    'usuario_id' => null,
                    'tiene_acceso_sistema' => 0 
                ]);

            Auditoria::registrar('usuario', $id, 'delete', $usuario->toArray(), null);

            $usuario->delete();

            return response()->json([
                'status' => 'success',
                'mensaje' => 'Usuario eliminado de manera definitiva del sistema.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Conflicto en Base de Datos: No se puede borrar porque el usuario tiene historial en otras tablas (Auditorías, etc). ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Fallo en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }   
}