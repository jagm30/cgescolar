<?php

namespace App\Http\Controllers\Educativo\Sicap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Educativo\StoreMateriaRequest;
use App\Models\Materia;
use App\Models\PlanEstudios;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestión de materias de un plan de estudios.
 *
 * Las materias son sub-recursos de un plan y se gestionan via AJAX
 * directamente desde la vista de detalle del plan (sicap/planes/show).
 */
class MateriaController extends Controller
{
    use EducativoRespondsWithJson;

    /** POST /educativo/sicap/planes/{plan}/materias */
    public function store(StoreMateriaRequest $request, PlanEstudios $plan): JsonResponse
    {
        $datos          = $request->validated();
        $datos['orden'] = $plan->materias()->max('orden') + 1;

        $materia = $plan->materias()->create($datos);

        return $this->respuestaExito('Materia agregada.', $materia->load('plan'));
    }

    /** PUT /educativo/sicap/planes/{plan}/materias/{materia} */
    public function update(StoreMateriaRequest $request, PlanEstudios $plan, Materia $materia): JsonResponse
    {
        abort_unless($materia->plan_id === $plan->id, 404);

        $materia->update($request->validated());

        return $this->respuestaExito('Materia actualizada.', $materia);
    }

    /** DELETE /educativo/sicap/planes/{plan}/materias/{materia} */
    public function destroy(PlanEstudios $plan, Materia $materia): JsonResponse
    {
        abort_unless($materia->plan_id === $plan->id, 404);

        $materia->delete();

        return $this->respuestaExito('Materia eliminada.');
    }

    /**
     * POST /educativo/sicap/planes/{plan}/materias/reordenar
     *
     * Recibe un array de IDs en el orden deseado y actualiza
     * el campo `orden` de cada materia.
     */
    public function reordenar(Request $request, PlanEstudios $plan): JsonResponse
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        foreach ($request->ids as $posicion => $id) {
            $plan->materias()->where('id', $id)->update(['orden' => $posicion + 1]);
        }

        return $this->respuestaExito('Orden actualizado.');
    }
}
