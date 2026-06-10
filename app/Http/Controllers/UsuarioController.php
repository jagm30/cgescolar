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
            ->paginate($mostrar); // Paginación nativa de Laravel

        if ($request->ajax()) {
            return response()->json($usuarios);
        }

        return view('usuarios.index', compact('usuarios'));
    }

    /** GET /usuarios/pendientes-portal
     * Contactos con acceso habilitado pero sin usuario creado.
     * Útil para que el admin sepa a quién le falta crear cuenta.
     */
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
        // Para rol padre: contactos que tienen acceso habilitado pero sin usuario
        $contactosPendientes = ContactoFamiliar::with('familia')
            ->where('tiene_acceso_portal', true)
            ->whereNull('usuario_id')
            ->get();

        return view('usuarios.create', compact('contactosPendientes'));
    }

    /** POST /usuarios - Modificado para recibir peticiones AJAX desde el Modal */

    public function store(Request $request)
    {
        try {
            // 1. Validamos los datos
            $request->validate([
                'nombre'   => 'required|string|max:255',
                'email'    => 'required|email|unique:usuario,email',
                'rol'      => 'required|string',
                'password' => 'required|string|min:6'
            ]);

            // 2. Creamos al usuario
            $usuario = Usuario::create([
                'nombre'        => $request->nombre,
                'email'         => $request->email,
                'password_hash' => Hash::make($request->password),
                'rol'           => $request->rol,
                'activo'        => true,
            ]);

            // === NUEVA LÍNEA PARA ENVIAR CORREO ===
            Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                'nombre'   => $usuario->nombre,
                'email'    => $usuario->email,
                'password' => $request->password,
                'rol'      => $usuario->rol
            ]));

            // 3. Preparamos los datos para el PDF en la sesión temporal
            $credenciales = [[
                'nombre'   => $usuario->nombre,
                'email'    => $usuario->email,
                'password' => $request->password,
                'rol'      => $usuario->rol
            ]];
            session()->flash('credenciales_nuevas', $credenciales);

            // 4. Registramos en la Auditoría
            Auditoria::registrar('usuario', $usuario->id, 'insert', null, [
                'nombre' => $usuario->nombre,
                'email'  => $usuario->email,
                'rol'    => $usuario->rol,
            ]);

            // 5. Devolvemos éxito
            return response()->json([
                'status' => 'success',
                'mensaje' => "Usuario creado correctamente."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error de validación (ej. el correo ya existe)
            return response()->json([
                'status' => 'error', 
                'mensaje' => $e->validator->errors()->first()
            ], 422);

        } catch (\Exception $e) {
            // Error fatal de PHP o Base de Datos
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

        // --- NUEVO CANDADO DE SEGURIDAD ---
        // Si el usuario intenta editar su propia cuenta y cambiar su rol, lo bloqueamos
        if ($usuario->id === auth()->id() && $request->rol !== $usuario->rol) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Medida de seguridad: No puedes cambiar tu propio rol para evitar la pérdida de acceso.'
            ], 403);
        }
        // ----------------------------------

        $request->validate([
            'rol' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:6']
        ]);

        $usuario->rol = $request->rol;
        
        $passwordPlana = $request->password;
        if (!empty($passwordPlana)) {
            $usuario->password_hash = Hash::make($passwordPlana);
            // === NUEVA LÍNEA PARA ENVIAR CORREO DE ACTUALIZACIÓN ===
            Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                'nombre'   => $usuario->nombre,
                'email'    => $usuario->email,
                'password' => $passwordPlana,
                'rol'      => $request->rol
            ]));
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

    
    public function generarUsuariosMasivos(Request $request)
    {
        $ids = $request->input('contacto_ids'); 
        $usuariosCreados = [];

        foreach ($ids as $id) {
            $contacto = ContactoFamiliar::with('familia')->findOrFail($id);
            
            // Evitar duplicados si ya tiene usuario
            if($contacto->usuario_id) continue;
            
            $passwordPlana = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8);

            // 1. ARMAMOS EL NOMBRE COMPLETO USANDO TUS COLUMNAS REALES
            $nombreCompleto = trim($contacto->nombre . ' ' . $contacto->ap_paterno . ' ' . $contacto->ap_materno);

            // 2. CREAMOS EL USUARIO
            $usuario = Usuario::create([
                'nombre'        => $nombreCompleto,
                'email'         => $contacto->email,
                'password_hash' => Hash::make($passwordPlana),
                'rol'           => 'padre',
                'activo'        => true,
            ]);

            $contacto->update(['usuario_id' => $usuario->id]);

            // === NUEVA LÍNEA PARA ENVIAR CORREO ===
            Mail::to($usuario->email)->send(new CredencialesAccesoMail([
                'nombre'   => $nombreCompleto,
                'email'    => $usuario->email,
                'password' => $passwordPlana,
                'rol'      => 'padre'
            ]));

            $usuariosCreados[] = [
                'nombre'   => $nombreCompleto, 
                'email'    => $usuario->email,
                'password' => $passwordPlana
            ];
        }

        // Guardamos los datos temporalmente en la sesión (Flash)
        session()->flash('credenciales_nuevas', $usuariosCreados);

        return response()->json([
            'status' => 'success',
            'mensaje' => count($usuariosCreados) . ' usuarios generados.'
        ]);
    }

    public function descargarCredencialesPdf()
    {
        // Recuperamos los datos de la sesión
        $credenciales = session('credenciales_nuevas');

        if (!$credenciales) {
            return abort(404, 'No hay credenciales recientes para imprimir o la sesión caducó.');
        }

        // Generamos el PDF usando una vista dedicada
        $pdf = Pdf::loadView('usuarios.pdf-credenciales', compact('credenciales'));
    
        return $pdf->stream('Credenciales_Colegio.pdf'); // Usa ->download() si prefieres que se descargue directo
    }
    /** POST /usuarios/{id}/reactivar - Nuevo método */
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

            // 1. Desvincular al Contacto Familiar
            ContactoFamiliar::where('usuario_id', $usuario->id)
                ->update([
                    'usuario_id' => null,
                    'tiene_acceso_portal' => 0 
                ]);

            // 2. Registrar en auditoría antes de destruirlo
            Auditoria::registrar('usuario', $id, 'delete', $usuario->toArray(), null);

            // 3. Borrar de la BD
            $usuario->delete();

            return response()->json([
                'status' => 'success',
                'mensaje' => 'Usuario eliminado de manera definitiva del sistema.'
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Atrapa errores de SQL (Casi seguro es por relaciones en otras tablas)
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Conflicto en Base de Datos: No se puede borrar porque el usuario tiene historial en otras tablas (Auditorías, etc). ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            // Atrapa cualquier otro error de PHP
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Fallo en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }   

}
