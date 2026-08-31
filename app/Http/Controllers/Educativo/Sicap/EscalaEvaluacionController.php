<?php

namespace App\Http\Controllers\Educativo\Sicap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Educativo\StoreCriterioEvaluacionRequest;
use App\Http\Requests\Educativo\StoreEscalaEvaluacionRequest;
use App\Models\CriterioEvaluacion;
use App\Models\EscalaEvaluacion;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestión de escalas de evaluación.
 *
 * Cada escala define cómo se califica en un nivel académico.
 * Los criterios de escalas literales se gestionan desde la vista de detalle.
 */
class EscalaEvaluacionController extends Controller
{
    use EducativoRespondsWithJson;

    /** GET /educativo/sicap/escalas */
    public function index(): View
    {
        $escalas = EscalaEvaluacion::query()
            ->withCount('planesEstudios')
            ->orderBy('nombre')
            ->get();

        return view('educativo.sicap.escalas.index', ['escalas' => $escalas]);
    }

    /** GET /educativo/sicap/escalas/create */
    public function create(): View
    {
        return view('educativo.sicap.escalas.form', ['escala' => new EscalaEvaluacion()]);
    }

    /** POST /educativo/sicap/escalas */
    public function store(StoreEscalaEvaluacionRequest $request): RedirectResponse|JsonResponse
    {
        $escala = EscalaEvaluacion::create($request->validated());

        return $this->respuestaExito(
            'Escala creada correctamente.',
            $escala,
            route('educativo.sicap.escalas.show', $escala)
        );
    }

    /** GET /educativo/sicap/escalas/{escala} */
    public function show(EscalaEvaluacion $escala): View
    {
        $escala->load(['criterios', 'planesEstudios.nivel']);

        return view('educativo.sicap.escalas.show', ['escala' => $escala]);
    }

    /** GET /educativo/sicap/escalas/{escala}/edit */
    public function edit(EscalaEvaluacion $escala): View
    {
        return view('educativo.sicap.escalas.form', ['escala' => $escala]);
    }

    /** PUT /educativo/sicap/escalas/{escala} */
    public function update(StoreEscalaEvaluacionRequest $request, EscalaEvaluacion $escala): RedirectResponse|JsonResponse
    {
        $escala->update($request->validated());

        return $this->respuestaExito(
            'Escala actualizada correctamente.',
            $escala,
            route('educativo.sicap.escalas.show', $escala)
        );
    }

    /** DELETE /educativo/sicap/escalas/{escala} */
    public function destroy(EscalaEvaluacion $escala): RedirectResponse|JsonResponse
    {
        if ($escala->planesEstudios()->exists()) {
            return $this->respuestaError('No se puede eliminar: la escala está asignada a uno o más planes de estudio.');
        }

        $escala->delete();

        return $this->respuestaExito('Escala eliminada.', redirect: route('educativo.sicap.escalas.index'));
    }

    // ── Criterios (gestionados inline desde show) ─────────

    /** POST /educativo/sicap/escalas/{escala}/criterios */
    public function storeCriterio(StoreCriterioEvaluacionRequest $request, EscalaEvaluacion $escala): JsonResponse
    {
        $criterio = $escala->criterios()->create($request->validated());

        return $this->respuestaExito('Criterio agregado.', $criterio);
    }

    /** PUT /educativo/sicap/escalas/{escala}/criterios/{criterio} */
    public function updateCriterio(StoreCriterioEvaluacionRequest $request, EscalaEvaluacion $escala, CriterioEvaluacion $criterio): JsonResponse
    {
        abort_unless($criterio->escala_id === $escala->id, 404);

        $criterio->update($request->validated());

        return $this->respuestaExito('Criterio actualizado.', $criterio);
    }

    /** DELETE /educativo/sicap/escalas/{escala}/criterios/{criterio} */
    public function destroyCriterio(EscalaEvaluacion $escala, CriterioEvaluacion $criterio): JsonResponse
    {
        abort_unless($criterio->escala_id === $escala->id, 404);

        $criterio->delete();

        return $this->respuestaExito('Criterio eliminado.');
    }
}
