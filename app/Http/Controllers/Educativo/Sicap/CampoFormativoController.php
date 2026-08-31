<?php

namespace App\Http\Controllers\Educativo\Sicap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Educativo\StoreCampoFormativoRequest;
use App\Models\CampoFormativo;
use App\Models\PlanEstudios;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;

/**
 * Gestión de campos formativos de un plan de estudios (Preescolar).
 *
 * Los campos formativos son sub-recursos de un plan y se gestionan
 * via AJAX desde la vista de detalle del plan (sicap/planes/show).
 */
class CampoFormativoController extends Controller
{
    use EducativoRespondsWithJson;

    /** POST /educativo/sicap/planes/{plan}/campos */
    public function store(StoreCampoFormativoRequest $request, PlanEstudios $plan): JsonResponse
    {
        $datos          = $request->validated();
        $datos['orden'] = $plan->camposFormativos()->max('orden') + 1;

        $campo = $plan->camposFormativos()->create($datos);

        return $this->respuestaExito('Campo formativo agregado.', $campo);
    }

    /** PUT /educativo/sicap/planes/{plan}/campos/{campo} */
    public function update(StoreCampoFormativoRequest $request, PlanEstudios $plan, CampoFormativo $campo): JsonResponse
    {
        abort_unless($campo->plan_id === $plan->id, 404);

        $campo->update($request->validated());

        return $this->respuestaExito('Campo formativo actualizado.', $campo);
    }

    /** DELETE /educativo/sicap/planes/{plan}/campos/{campo} */
    public function destroy(PlanEstudios $plan, CampoFormativo $campo): JsonResponse
    {
        abort_unless($campo->plan_id === $plan->id, 404);

        $campo->delete();

        return $this->respuestaExito('Campo formativo eliminado.');
    }
}
