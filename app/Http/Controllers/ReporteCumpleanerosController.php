<?php

namespace App\Http\Controllers;

use App\Http\Requests\CumpleanerosRequest;
use App\Models\CicloEscolar;
use App\Models\Setting;
use App\Services\CumpleanerosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReporteCumpleanerosController extends Controller
{
    /** GET /familias/cumpleaneros — Cumpleaños de contactos familiares por grupo */
    public function index(CumpleanerosRequest $request, CumpleanerosService $service): View
    {
        $cicloId = $this->cicloId();

        return view('familias.cumpleaneros', [
            'grupos' => $cicloId ? $service->porGrupo($cicloId, $request->mes(), $request->grupoId()) : collect(),
            'gruposFiltro' => $cicloId ? $service->gruposDelCiclo($cicloId) : collect(),
            'mes' => $request->mes(),
            'grupoId' => $request->grupoId(),
        ]);
    }

    /** GET /familias/cumpleaneros/pdf */
    public function pdf(CumpleanerosRequest $request, CumpleanerosService $service): Response
    {
        $cicloId = $this->cicloId();

        if (ob_get_length()) {
            ob_end_clean();
        }

        return Pdf::loadView('familias.cumpleaneros_pdf', [
            'grupos' => $cicloId ? $service->porGrupo($cicloId, $request->mes(), $request->grupoId()) : collect(),
            'ciclo' => CicloEscolar::find($cicloId),
            'mes' => $request->mes(),
            'setting' => Setting::find(1),
        ])
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'helvetica')
            ->setPaper('letter', 'portrait')
            ->stream('Cumpleaneros_'.now()->format('Y-m-d').'.pdf');
    }

    /** Ciclo seleccionado por el usuario o, en su defecto, el ciclo activo. */
    private function cicloId(): ?int
    {
        return auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');
    }
}
