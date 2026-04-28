<?php

namespace App\Http\Controllers;

use App\Models\CicloEscolar;
use App\Models\Inscripcion;
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
