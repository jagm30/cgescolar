<?php

namespace App\Http\Controllers;

use App\Models\CicloEscolar;
use App\Models\Inscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteDeudoresController extends Controller
{
    /** GET /reportes/deudores */
    public function index(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        // Estados seleccionados; por defecto todos
        $estados = $request->has('estados')
            ? (array) $request->get('estados')
            : ['pendiente', 'vencido', 'parcial'];

        $ciclos = CicloEscolar::orderByDesc('fecha_inicio')->get();

        $hoy           = now()->toDateString();
        $cargoFilter   = $this->buildCargoFilter($estados, $hoy);

        $inscripciones = Inscripcion::query()
            ->with([
                'alumno',
                'grupo.grado.nivel',
                'cargos' => fn ($q) => $cargoFilter($q)->with('detallesPagosVigentes'),
            ])
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->whereHas('cargos', fn ($q) => $cargoFilter($q))
            ->get();

        $deudores = $inscripciones
            ->filter(fn ($ins) => $ins->cargos->isNotEmpty())   // descartar si no quedó ningún cargo tras el filtro
            ->map(function (Inscripcion $ins) use ($hoy) {
                $cargos = $ins->cargos;

                $pendientes = $cargos->where('estado', 'pendiente')
                    ->filter(fn ($c) => $c->fecha_vencimiento >= $hoy);
                $vencidos   = $cargos->where('estado', 'pendiente')
                    ->filter(fn ($c) => $c->fecha_vencimiento < $hoy);
                $parciales  = $cargos->where('estado', 'parcial');

                $totalAdeudo = $cargos->sum(fn ($c) => max(0, $c->monto_original - $c->saldo_abonado));

                return [
                    'alumno'         => $ins->alumno,
                    'inscripcion_id' => $ins->id,
                    'grupo'          => $ins->grupo,
                    'nivel'          => $ins->grupo?->grado?->nivel,
                    'pendientes'     => $pendientes->count(),
                    'vencidos'       => $vencidos->count(),
                    'parciales'      => $parciales->count(),
                    'total_cargos'   => $cargos->count(),
                    'total_adeudo'   => round($totalAdeudo, 2),
                ];
            })
            ->sortByDesc('total_adeudo')
            ->values();

        $resumen = [
            'total_deudores'   => $deudores->count(),
            'total_pendientes' => $deudores->sum('pendientes'),
            'total_vencidos'   => $deudores->sum('vencidos'),
            'total_parciales'  => $deudores->sum('parciales'),
            'gran_total'       => round($deudores->sum('total_adeudo'), 2),
        ];

        if ($request->ajax()) {
            return response()->json(['deudores' => $deudores, 'resumen' => $resumen]);
        }

        return view('reportes.deudores', compact('deudores', 'resumen', 'ciclos', 'cicloId', 'estados'));
    }

    /** GET /reportes/deudores/pdf */
    public function pdf(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $estados = $request->has('estados')
            ? (array) $request->get('estados')
            : ['pendiente', 'vencido', 'parcial'];

        $ciclo = CicloEscolar::find($cicloId);
        $hoy   = now()->toDateString();
        $cargoFilter = $this->buildCargoFilter($estados, $hoy);

        $inscripciones = Inscripcion::query()
            ->with([
                'alumno',
                'grupo.grado.nivel',
                'cargos' => fn ($q) => $cargoFilter($q)->with('detallesPagosVigentes'),
            ])
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->whereHas('cargos', fn ($q) => $cargoFilter($q))
            ->get();

        $deudores = $inscripciones
            ->filter(fn ($ins) => $ins->cargos->isNotEmpty())
            ->map(function (Inscripcion $ins) use ($hoy) {
                $cargos     = $ins->cargos;
                $pendientes = $cargos->where('estado', 'pendiente')->filter(fn ($c) => $c->fecha_vencimiento >= $hoy);
                $vencidos   = $cargos->where('estado', 'pendiente')->filter(fn ($c) => $c->fecha_vencimiento < $hoy);
                $parciales  = $cargos->where('estado', 'parcial');
                $totalAdeudo = $cargos->sum(fn ($c) => max(0, $c->monto_original - $c->saldo_abonado));

                return [
                    'alumno'       => $ins->alumno,
                    'grupo'        => $ins->grupo,
                    'nivel'        => $ins->grupo?->grado?->nivel,
                    'pendientes'   => $pendientes->count(),
                    'vencidos'     => $vencidos->count(),
                    'parciales'    => $parciales->count(),
                    'total_cargos' => $cargos->count(),
                    'total_adeudo' => round($totalAdeudo, 2),
                ];
            })
            ->sortByDesc('total_adeudo')
            ->values();

        $resumen = [
            'total_deudores'   => $deudores->count(),
            'total_pendientes' => $deudores->sum('pendientes'),
            'total_vencidos'   => $deudores->sum('vencidos'),
            'total_parciales'  => $deudores->sum('parciales'),
            'gran_total'       => round($deudores->sum('total_adeudo'), 2),
        ];

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView('reportes.deudores_pdf', compact('deudores', 'resumen', 'ciclo', 'estados'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('Reporte_Deudores_' . now()->format('Y-m-d') . '.pdf');
    }

    /** GET /reportes/deudores/pdf-detalle */
    public function pdfDetalle(Request $request)
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        $estados     = $request->has('estados')
            ? (array) $request->get('estados')
            : ['pendiente', 'vencido', 'parcial'];

        $ciclo       = CicloEscolar::find($cicloId);
        $hoy         = now()->toDateString();
        $cargoFilter = $this->buildCargoFilter($estados, $hoy);

        $inscripciones = Inscripcion::query()
            ->with([
                'alumno',
                'grupo.grado.nivel',
                'cargos' => fn ($q) => $cargoFilter($q)
                    ->with(['concepto', 'detallesPagosVigentes'])
                    ->orderBy('fecha_vencimiento'),
            ])
            ->where('ciclo_id', $cicloId)
            ->where('activo', true)
            ->whereHas('cargos', fn ($q) => $cargoFilter($q))
            ->get();

        $deudores = $inscripciones
            ->filter(fn ($ins) => $ins->cargos->isNotEmpty())
            ->map(fn (Inscripcion $ins) => [
                'alumno'       => $ins->alumno,
                'grupo'        => $ins->grupo,
                'nivel'        => $ins->grupo?->grado?->nivel,
                'total_adeudo' => round($ins->cargos->sum(fn ($c) => max(0, $c->monto_original - $c->saldo_abonado)), 2),
                'cargos'       => $ins->cargos->map(fn ($c) => [
                    'concepto'         => $c->concepto?->nombre ?? '—',
                    'periodo_label'    => $c->periodo_label ?? '—',
                    'fecha_vencimiento'=> $c->fecha_vencimiento,
                    'monto_original'   => $c->monto_original,
                    'saldo_abonado'    => $c->saldo_abonado,
                    'saldo_pendiente'  => max(0, $c->monto_original - $c->saldo_abonado),
                    'estado'           => $c->fecha_vencimiento < $hoy && $c->estado === 'pendiente'
                                            ? 'vencido'
                                            : $c->estado,
                ]),
            ])
            ->sortByDesc('total_adeudo')
            ->values();

        $resumen = [
            'total_deudores' => $deudores->count(),
            'gran_total'     => round($deudores->sum('total_adeudo'), 2),
        ];

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView('reportes.deudores_detalle_pdf', compact('deudores', 'resumen', 'ciclo', 'estados'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream('Deudores_Detalle_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Devuelve un closure que aplica los filtros de estado sobre una query de Cargo.
     *
     * @param  string[]  $estados  Valores posibles: 'pendiente', 'vencido', 'parcial'
     */
    private function buildCargoFilter(array $estados, string $hoy): \Closure
    {
        return function ($query) use ($estados, $hoy) {
            return $query->where(function ($q) use ($estados, $hoy) {
                if (in_array('pendiente', $estados, true)) {
                    $q->orWhere(
                        fn ($s) => $s->where('estado', 'pendiente')
                                     ->whereDate('fecha_vencimiento', '>=', $hoy)
                    );
                }
                if (in_array('vencido', $estados, true)) {
                    $q->orWhere(
                        fn ($s) => $s->where('estado', 'pendiente')
                                     ->whereDate('fecha_vencimiento', '<', $hoy)
                    );
                }
                if (in_array('parcial', $estados, true)) {
                    $q->orWhere('estado', 'parcial');
                }
            });
        };
    }
}
