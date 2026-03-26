<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Trait para controladores que manejan tanto peticiones AJAX (jQuery)
 * como formularios tradicionales de Blade.
 *
 * store/update/destroy usan estos helpers para retornar
 * JSON si es AJAX o redirect() si es formulario normal.
 */
trait RespondsWithJson
{
    /**
     * Respuesta exitosa: JSON para AJAX, redirect para formulario.
     *
     * @param  string  $redirectRoute  Nombre de la ruta a redirigir
     * @param  array   $jsonData       Datos a incluir en la respuesta JSON
     * @param  string  $mensaje        Mensaje de éxito
     * @param  int     $jsonStatus     Código HTTP para la respuesta JSON
     */
    protected function respuestaExito(
        string $redirectRoute,
        array  $jsonData = [],
        string $mensaje  = 'Operación realizada correctamente.',
        int    $jsonStatus = 200
    ): JsonResponse|RedirectResponse {
        if (request()->ajax()) {
            return response()->json(array_merge(
                ['message' => $mensaje],
                $jsonData
            ), $jsonStatus);
        }

        return redirect()->route($redirectRoute)
            ->with('success', $mensaje);
    }

    /**
     * Respuesta de error: JSON para AJAX, redirect con error para formulario.
     */
    protected function respuestaError(
        string $mensaje,
        string $redirectRoute = '',
        int    $jsonStatus    = 422
    ): JsonResponse|RedirectResponse {
        if (request()->ajax()) {
            return response()->json(['message' => $mensaje], $jsonStatus);
        }

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with('error', $mensaje);
        }

        return back()->with('error', $mensaje);
    }
}
