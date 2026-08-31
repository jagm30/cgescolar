<?php

namespace App\Http\Controllers\Educativo;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Inscripcion;
use App\Services\Educativo\PromedioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Boleta de calificaciones.
 *
 * Genera la boleta de un alumno en un ciclo escolar dado.
 * Disponible en previsualización HTML y descarga PDF.
 *
 * Acceso: administrador puede ver cualquier alumno.
 *         El padre podrá acceder en la Fase 6 (portal padres).
 */
class BoletaController extends Controller
{
    public function __construct(private readonly PromedioService $promedioService)
    {
    }

    /**
     * GET /educativo/boleta
     *
     * Buscador de alumnos para abrir su boleta.
     */
    public function index(Request $request): View
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        $termino  = $request->string('q')->trim()->toString();
        $ciclos   = CicloEscolar::query()->orderByDesc('id')->get();

        $alumnos = $termino
            ? Inscripcion::query()
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
                ->whereHas('alumno', function ($q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                      ->orWhere('ap_paterno', 'like', "%{$termino}%")
                      ->orWhere('ap_materno', 'like', "%{$termino}%")
                      ->orWhere('matricula', 'like', "%{$termino}%");
                })
                ->with(['alumno', 'grupo.grado.nivel'])
                ->get()
                ->sortBy(fn ($i) => $i->alumno->ap_paterno)
            : collect();

        return view('educativo.boleta.index', [
            'alumnos'           => $alumnos,
            'ciclos'            => $ciclos,
            'cicloSeleccionado' => $cicloId,
            'termino'           => $termino,
        ]);
    }

    /**
     * GET /educativo/boleta/{alumno}
     *
     * Previsualización HTML de la boleta con botón de descarga PDF.
     */
    public function show(Request $request, Alumno $alumno): View
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        $resumen = $this->promedioService->resumenBoleta($alumno, $cicloId);
        $ciclos  = CicloEscolar::query()->orderByDesc('id')->get();

        return view('educativo.boleta.show', array_merge($resumen, [
            'ciclos'            => $ciclos,
            'cicloSeleccionado' => $cicloId,
        ]));
    }

    /**
     * GET /educativo/boleta/{alumno}/pdf
     *
     * Descarga la boleta como PDF usando DomPDF.
     */
    public function pdf(Request $request, Alumno $alumno): Response
    {
        $cicloId = $request->integer('ciclo_id')
            ?: auth()->user()->ciclo_seleccionado_id
            ?: CicloEscolar::activo()->value('id');

        $resumen = $this->promedioService->resumenBoleta($alumno, $cicloId);

        $pdf = Pdf::loadView('educativo.boleta.pdf', $resumen)
            ->setPaper('letter', 'portrait');

        $nombreArchivo = 'boleta_'
            . str($alumno->ap_paterno . '_' . $alumno->nombre)->slug('_')
            . '_ciclo_' . $cicloId
            . '.pdf';

        return $pdf->download($nombreArchivo);
    }
}
