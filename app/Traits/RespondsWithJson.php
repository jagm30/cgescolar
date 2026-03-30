<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

trait RespondsWithJson
{
    protected function respuestaExito(
        string $redirectRoute,
        array $jsonData = [],
        string $mensaje = 'Operacion realizada correctamente.',
        int $jsonStatus = 200,
        array $routeParams = []
    ): JsonResponse|RedirectResponse {
        if (request()->ajax()) {
            return response()->json(array_merge(
                ['message' => $mensaje],
                $jsonData
            ), $jsonStatus);
        }

        return redirect()->route($redirectRoute, $routeParams)
            ->with('success', $mensaje);
    }

    protected function respuestaError(
        string $mensaje,
        string $redirectRoute = '',
        int $jsonStatus = 422
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
