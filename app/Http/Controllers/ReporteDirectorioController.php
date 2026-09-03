<?php

namespace App\Http\Controllers;

use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReporteDirectorioController extends Controller
{
    /** GET /reportes/directorio-familiar/pdf */
    public function pdf(Request $request): Response
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $ciclo = CicloEscolar::find($cicloId);

        $grupos = Grupo::with([
            'grado.nivel',
            'inscripciones' => fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
                ->with([
                    'alumno.contactos' => fn ($q) => $q
                        ->wherePivot('activo', true)
                        ->orderByPivot('orden'),
                ]),
        ])
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->whereHas('inscripciones', fn ($q) => $q
                ->where('ciclo_id', $cicloId)
                ->where('activo', true)
            )
            ->get()
            ->sortBy([
                fn ($g) => $g->grado?->nivel?->nombre ?? '',
                fn ($g) => $g->grado?->nombre ?? '',
                fn ($g) => $g->nombre,
            ]);

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView('reportes.directorio_familiar_pdf', [
            'grupos' => $grupos,
            'ciclo' => $ciclo,
            'setting' => Setting::find(1),
        ])
            ->setOption('isPhpEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'helvetica')
            ->setPaper('letter', 'portrait');

        return $pdf->stream('Directorio_Familiar_'.now()->format('Y-m-d').'.pdf');
    }
}
