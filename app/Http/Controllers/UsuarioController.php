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

class UsuarioController extends Controller
{
    use RespondsWithJson;

    /** GET /usuarios */
    public function index(Request $request)
    {
        $usuarios = Usuario::with('cicloSeleccionado')
            ->when($request->filled('rol'),    fn($q) => $q->where('rol', $request->rol))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->boolean('activo')))
            ->when($request->filled('buscar'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            }))
            ->orderBy('rol')->orderBy('nombre')
            ->get();

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

    /** POST /usuarios */
    public function store(StoreUsuarioRequest $request)
    {
        $data    = $request->validated();
        $usuario = Usuario::create([
            'nombre'        => $data['nombre'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'rol'           => $data['rol'],
            'activo'        => $data['activo'] ?? true,
        ]);

        if ($data['rol'] === 'padre' && !empty($data['contacto_id'])) {
            ContactoFamiliar::where('id', $data['contacto_id'])
                ->update(['usuario_id' => $usuario->id]);
        }

        Auditoria::registrar('usuario', $usuario->id, 'insert', null, [
            'nombre' => $usuario->nombre,
            'email'  => $usuario->email,
            'rol'    => $usuario->rol,
        ]);

        return $this->respuestaExito(
            redirectRoute: 'usuarios.index',
            jsonData: ['usuario' => $usuario],
            mensaje: "Usuario '{$usuario->nombre}' creado correctamente.",
            jsonStatus: 201
        );
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

    /** PUT /usuarios/{id} */
    public function update(UpdateUsuarioRequest $request, int $id)
    {
        $usuario  = Usuario::findOrFail($id);
        $anterior = $usuario->toArray();
        $data     = $request->validated();

        if (!empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password'], $data['password_confirmation']);

        $usuario->update($data);

        Auditoria::registrar('usuario', $usuario->id, 'update', $anterior, $usuario->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'usuarios.index',
            jsonData: ['usuario' => $usuario->fresh()],
            mensaje: "Usuario '{$usuario->nombre}' actualizado correctamente."
        );
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
}
