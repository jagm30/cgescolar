<?php

namespace App\Http\Controllers\Educativo\Sicap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Educativo\StorePlanEstudiosRequest;
use App\Models\CicloEscolar;
use App\Models\EscalaEvaluacion;
use App\Models\NivelEscolar;
use App\Models\PlanEstudios;
use App\Traits\EducativoRespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Gestión de planes de estudio.
 *
 * Un plan agrupa el currículo de un nivel académico.
 * Las materias y campos formativos se gestionan desde la vista de detalle (show).
 */
class PlanEstudiosController extends Controller
{
    use EducativoRespondsWithJson;

    /** GET /educativo/sicap/planes */
    public function index(): View
    {
        $planes = PlanEstudios::query()
            ->with(['nivel', 'escala', 'ciclo'])
            ->withCount(['materias', 'camposFormativos'])
            ->orderBy('nivel_id')
            ->orderByDesc('activo')
            ->get();

        return view('educativo.sicap.planes.index', ['planes' => $planes]);
    }

    /** GET /educativo/sicap/planes/create */
    public function create(): View
    {
        return view('educativo.sicap.planes.form', [
            'plan'    => new PlanEstudios(),
            'niveles' => NivelEscolar::query()->orderBy('orden')->get(),
            'escalas' => EscalaEvaluacion::query()->activa()->orderBy('nombre')->get(),
            'ciclos'  => CicloEscolar::query()->orderByDesc('id')->get(),
        ]);
    }

    /** POST /educativo/sicap/planes */
    public function store(StorePlanEstudiosRequest $request): RedirectResponse|JsonResponse
    {
        $plan = PlanEstudios::create($request->validated());

        return $this->respuestaExito(
            'Plan de estudios creado correctamente.',
            $plan,
            route('educativo.sicap.planes.show', $plan)
        );
    }

    /** GET /educativo/sicap/planes/{plan} */
    public function show(PlanEstudios $plan): View
    {
        $plan->load(['nivel', 'escala.criterios', 'ciclo', 'materias', 'camposFormativos']);

        return view('educativo.sicap.planes.show', ['plan' => $plan]);
    }

    /** GET /educativo/sicap/planes/{plan}/edit */
    public function edit(PlanEstudios $plan): View
    {
        return view('educativo.sicap.planes.form', [
            'plan'    => $plan,
            'niveles' => NivelEscolar::query()->orderBy('orden')->get(),
            'escalas' => EscalaEvaluacion::query()->activa()->orderBy('nombre')->get(),
            'ciclos'  => CicloEscolar::query()->orderByDesc('id')->get(),
        ]);
    }

    /** PUT /educativo/sicap/planes/{plan} */
    public function update(StorePlanEstudiosRequest $request, PlanEstudios $plan): RedirectResponse|JsonResponse
    {
        $plan->update($request->validated());

        return $this->respuestaExito(
            'Plan de estudios actualizado correctamente.',
            $plan,
            route('educativo.sicap.planes.show', $plan)
        );
    }

    /** DELETE /educativo/sicap/planes/{plan} */
    public function destroy(PlanEstudios $plan): RedirectResponse|JsonResponse
    {
        if ($plan->materias()->exists() || $plan->camposFormativos()->exists()) {
            return $this->respuestaError('No se puede eliminar: el plan tiene materias o campos formativos asociados. Elimínalos primero.');
        }

        $plan->delete();

        return $this->respuestaExito('Plan eliminado.', redirect: route('educativo.sicap.planes.index'));
    }
}
