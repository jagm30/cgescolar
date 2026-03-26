<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar el rol del usuario autenticado.
 *
 * Uso en rutas:
 *   ->middleware('rol:administrador')
 *   ->middleware('rol:administrador,caja')
 *   ->middleware('rol:administrador,caja,recepcion')
 */
class CheckRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return $request->ajax()
                ? response()->json(['message' => 'No autenticado.'], 401)
                : redirect()->route('login');
        }

        if (!in_array(auth()->user()->rol, $roles)) {
            if ($request->ajax()) {
                return response()->json(['message' => 'No tienes permisos para realizar esta acción.'], 403);
            }
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
