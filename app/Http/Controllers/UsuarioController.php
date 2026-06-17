<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Auditoria;
use App\Models\ContactoFamiliar;
use App\Models\Usuario;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\CredencialesAccesoMail;


class UsuarioController extends Controller
{
    use RespondsWithJson;

    /** GET /usuarios - Modificado con Paginación Dinámica */
    public function index(Request $request)
    {
        $mostrar = $request->input('mostrar', 10);

        $usuarios = Usuario::with('cicloSeleccionado')
            ->when($request->filled('rol'),    fn($q) => $q->where('rol', $request->rol))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->activo))
            ->when($request->filled('buscar'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            }))
            ->orderBy('rol')->orderBy('nombre')
            ->paginate($mostrar);

        if ($request->ajax()) {
            return response()->json($usuarios);
        }

        return view('usuarios.index', compact('usuarios'));
    }

    /** GET /usuarios/pendientes-portal */
    public function pendientesPortal()
    {
        $pendientes = ContactoFamiliar::with('familia')
            ->where('tiene_acceso_portal', true)
            ->whereNull('usuario_id')
            ->orderBy('familia_id')
            ->get();

        if (request()->ajax()) {
            return response()->json($pendientes);
        }

        return view('usuarios.pendientes-portal', compact('pendientes'));
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
            $request->validate([
                'nombre'   => 'required|string|max:255',
                'email'    => 'required|email|unique:usuario,email',
                'rol'      => 'required|string',
                'password' => 'required|string|min:6'
            ]);

            $usuario = Usuario::create([
                'nombre'        => $request->nombre,
                'email'         => $request->email,
                'password_hash' => Hash::make($request->password),
                'rol'           => $request->rol,
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
            session()->flash('credenciales_nuevas', $credenciales);
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
            'rol' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:6']
        ]);

        $usuario->rol = $request->rol;
        
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

    /** POST /usuarios/generar-masivos */
    public function generarUsuariosMasivos(Request $request)
    {
        $ids = $request->input('contacto_ids'); 
        $usuariosCreados = [];

        $enviados = 0;
        $fallidos = 0;

        foreach ($ids as $id) {
            $contacto = ContactoFamiliar::with('familia')->findOrFail($id);
            if($contacto->usuario_id) continue;
            
            $passwordPlana = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);
            $nombreCompleto = trim($contacto->nombre . ' ' . $contacto->ap_paterno . ' ' . $contacto->ap_materno);

            $usuario = Usuario::create([
                'nombre'        => $nombreCompleto,
                'email'         => $contacto->email,
                'password_hash' => Hash::make($passwordPlana),
                'rol'           => 'padre',
                'activo'        => true,
            ]);
            $contacto->update(['usuario_id' => $usuario->id]);

            try {
                Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                    'nombre'   => $nombreCompleto,
                    'email'    => $usuario->email,
                    'password' => $passwordPlana,
                    'rol'      => 'padre'
                ]));
                $enviados++;
            } catch (\Exception $e) {
                $fallidos++; 
            }

            $usuariosCreados[] = [
                'nombre'   => $nombreCompleto, 
                'email'    => $usuario->email,
                'password' => $passwordPlana,
                'rol'      => 'padre'
            ];
        }

        $mensajeNotificacion = count($usuariosCreados) . " usuarios generados. Correos enviados: {$enviados}. Fallidos: {$fallidos}.";
        
        session()->flash('credenciales_nuevas', $usuariosCreados);
        session()->put('mensaje_persistente', $mensajeNotificacion);

        return response()->json([
            'status' => 'success',
            'mensaje' => $mensajeNotificacion
        ]);
    }

    public function descargarCredencialesPdf()
    {
        $credenciales = session('credenciales_nuevas');

        if (!$credenciales) {
            return abort(404, 'No hay credenciales recientes para imprimir o la sesión caducó.');
        }

        $pdf = Pdf::loadView('usuarios.pdf-credenciales', compact('credenciales'));
    
        return $pdf->stream('Credenciales_Colegio.pdf'); 
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