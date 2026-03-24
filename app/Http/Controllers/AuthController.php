<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /auth/login
     * Autentica al usuario y devuelve un token Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('email', $request->email)
            ->where('activo', true)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Actualizar último acceso y cargar ciclo seleccionado
        $usuario->update(['ultimo_acceso' => now()]);

        // Si es usuario interno y no tiene ciclo seleccionado, asignar el activo
        if ($usuario->esInterno() && !$usuario->ciclo_seleccionado_id) {
            $cicloActivo = \App\Models\CicloEscolar::activo()->first();
            if ($cicloActivo) {
                $usuario->update(['ciclo_seleccionado_id' => $cicloActivo->id]);
            }
        }

        $token = $usuario->createToken('sge-token')->plainTextToken;

        Auditoria::registrar('usuario', $usuario->id, 'login', null, [
            'email' => $usuario->email,
            'rol'   => $usuario->rol,
        ]);

        return response()->json([
            'token'   => $token,
            'usuario' => [
                'id'                   => $usuario->id,
                'nombre'               => $usuario->nombre,
                'email'                => $usuario->email,
                'rol'                  => $usuario->rol,
                'ciclo_seleccionado_id'=> $usuario->ciclo_seleccionado_id,
            ],
        ]);
    }

    /**
     * POST /auth/logout
     * Revoca el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * GET /auth/me
     * Devuelve el usuario autenticado con su ciclo activo.
     */
    public function me(): JsonResponse
    {
        $usuario = auth()->user()->load('cicloSeleccionado');

        return response()->json($usuario);
    }
}
