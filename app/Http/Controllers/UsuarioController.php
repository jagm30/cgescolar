<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Auditoria;
use App\Models\ContactoFamiliar;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /** GET /usuarios */
    public function index(Request $request): JsonResponse
    {
        $this->soloAdmin();

        $usuarios = Usuario::with('cicloSeleccionado')
            ->when($request->filled('rol'),    fn($q) => $q->where('rol', $request->rol))
            ->when($request->filled('activo'), fn($q) => $q->where('activo', $request->boolean('activo')))
            ->when($request->filled('buscar'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('email', 'like', "%{$request->buscar}%");
            }))
            ->orderBy('rol')->orderBy('nombre')
            ->get();

        return response()->json($usuarios);
    }

    /** GET /usuarios/pendientes-portal
     * Contactos con acceso habilitado pero sin usuario creado.
     */
    public function pendientesPortal(): JsonResponse
    {
        $this->soloAdmin();

        $pendientes = ContactoFamiliar::with('familia')
            ->where('tiene_acceso_portal', true)
            ->whereNull('usuario_id')
            ->orderBy('familia_id')
            ->get();

        return response()->json($pendientes);
    }

    /** POST /usuarios */
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $data    = $request->validated();
        $usuario = Usuario::create([
            'nombre'        => $data['nombre'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'rol'           => $data['rol'],
            'activo'        => $data['activo'] ?? true,
        ]);

        // Si es padre, vincular con el contacto familiar
        if ($data['rol'] === 'padre' && !empty($data['contacto_id'])) {
            ContactoFamiliar::where('id', $data['contacto_id'])
                ->update(['usuario_id' => $usuario->id]);
        }

        Auditoria::registrar('usuario', $usuario->id, 'insert', null, [
            'nombre' => $usuario->nombre,
            'email'  => $usuario->email,
            'rol'    => $usuario->rol,
        ]);

        return response()->json($usuario, 201);
    }

    /** PUT /usuarios/{id} */
    public function update(UpdateUsuarioRequest $request, int $id): JsonResponse
    {
        $this->soloAdmin();

        $usuario  = Usuario::findOrFail($id);
        $anterior = $usuario->toArray();
        $data     = $request->validated();

        if (!empty($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
        }
        unset($data['password'], $data['password_confirmation']);

        $usuario->update($data);

        Auditoria::registrar('usuario', $usuario->id, 'update', $anterior, $usuario->fresh()->toArray());

        return response()->json($usuario->fresh());
    }

    /** DELETE /usuarios/{id} — solo desactiva */
    public function destroy(int $id): JsonResponse
    {
        $this->soloAdmin();

        $usuario = Usuario::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            return response()->json(['message' => 'No puede desactivar su propia cuenta.'], 422);
        }

        $usuario->update(['activo' => false]);

        Auditoria::registrar('usuario', $usuario->id, 'update', ['activo' => true], ['activo' => false]);

        return response()->json(['message' => 'Usuario desactivado correctamente.']);
    }

    /**
     * GET /usuarios/perfil
     * Perfil del usuario autenticado con su ciclo seleccionado.
     */
    public function perfil(): JsonResponse
    {
        $usuario = auth()->user()->load('cicloSeleccionado');

        $datos = $usuario->toArray();

        // Si es padre, agregar sus hijos
        if ($usuario->esPadre()) {
            $contacto = $usuario->contactoFamiliar()->with('familia.alumnos.inscripciones.grupo')->first();
            $datos['hijos'] = $contacto?->familia?->alumnos ?? [];
        }

        return response()->json($datos);
    }

    private function soloAdmin(): void
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }
}
