<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ManualController extends Controller
{
    public function admisionesRecepcion(): Response
    {
        $pdf = Pdf::loadView('manuales.admisiones-recepcion-pdf');

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Manual-Admisiones-Recepcion.pdf');
    }
}
