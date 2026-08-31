<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Respuestas duales JSON / Redirect para el módulo Educativo.
 *
 * Firma diferente al trait de producción (RespondsWithJson) para evitar
 * colisiones. Pasar $redirect como URL directa (route()), no como nombre.
 */
trait EducativoRespondsWithJson
{
    /**
     * Respuesta de éxito.
     *
     * - Si la petición es AJAX → JSON con { message, data }.
     * - Si es una petición normal → redirect con flash 'success'.
     *
     * @param  Model|array|null  $jsonData  Datos que se incluyen en la respuesta JSON.
     * @param  string            $redirect  URL de redirección (usar route() helper).
     */
    protected function respuestaExito(
        string $mensaje,
        Model|array|null $jsonData = null,
        string $redirect = '',
    ): JsonResponse|RedirectResponse {
        if (request()->ajax()) {
            $payload = ['message' => $mensaje];

            if ($jsonData instanceof Model) {
                $payload['data'] = $jsonData->toArray();
            } elseif (is_array($jsonData)) {
                $payload['data'] = $jsonData;
            }

            return response()->json($payload);
        }

        $destino = $redirect ?: url()->previous();

        return redirect($destino)->with('success', $mensaje);
    }

    /**
     * Respuesta de error.
     *
     * - AJAX → JSON 422 con { message }.
     * - Normal → back() con flash 'error'.
     */
    protected function respuestaError(
        string $mensaje,
        int $status = 422,
    ): JsonResponse|RedirectResponse {
        if (request()->ajax()) {
            return response()->json(['message' => $mensaje], $status);
        }

        return back()->with('error', $mensaje);
    }
}
