<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Asegura que las peticiones AJAX reciban JSON en errores de autenticación
 * y validación, en lugar de redirecciones HTML.
 */
class ForceJsonOnAjax
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->ajax()) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
