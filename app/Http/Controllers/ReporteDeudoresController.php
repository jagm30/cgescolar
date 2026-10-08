<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\CicloEscolar;
use App\Models\ConceptoCobro;
use App\Models\Inscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReporteDeudoresController extends Controller
{
    /** Estados de cargo incluidos cuando el usuario no elige ninguno. */
    private const ESTADOS_DEFAULT = ['vencido'];

    /** GET /reportes/deudores */
    public function index(Request $request)
    {
        $deudores = $this->consultarDeudores($request);

        $resumen = [
            'total_deudores' => $deudores->count(),
            'total_pendientes' => $deudores->sum('pendientes'),
            'total_vencidos' => $deudores->sum('vencidos'),
            'total_parciales' => $deudores->sum('parciales'),
            'gran_total' => round($deudores->sum('total_adeudo'), 2),
        ];

        if ($request->ajax()) {
            return response()->json(['deudores' => $deudores, 'resumen' => $resumen]);
        }

        return view('reportes.deudores', [
            'deudores' => $deudores,
            'resumen' => $resumen,
            'ciclos' => CicloEscolar::orderByDesc('fecha_inicio')->get(),
            'cicloId' => $this->cicloId($request),
            'estados' => $this->estados($request),
            'buscar' => $this->buscar($request),
            'conceptoId' => $this->conceptoId($request),
            'conceptos' => ConceptoCobro::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * GET /reportes/deudores/{inscripcion}/cargos
     * Desglose de cargos adeudados de una inscripción, cargado bajo demanda desde la vista.
     */
    public function cargos(Request $request, int $inscripcion): View
    {
        $cargoFilter = $this->buildCargoFilter($this->estados($request), now()->toDateString(), $this->conceptoId($request));

        $cargos = $cargoFilter(Cargo::query())
            ->where('inscripcion_id', $inscripcion)
            ->with(['concepto', 'detallesPagosVigentes'])
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn (Cargo $c) => $this->detallarCargo($c));

        return view('reportes.partials.deudores_cargos', ['cargos' => $cargos]);
    }

    /** GET /reportes/deudores/pdf */
    public function pdf(Request $request)
    {
        $deudores = $this->consultarDeudores($request);

        $resumen = [
            'total_deudores' => $deudores->count(),
            'total_pendientes' => $deudores->sum('pendientes'),
            'total_vencidos' => $deudores->sum('vencidos'),
            'total_parciales' => $deudores->sum('parciales'),
            'gran_total' => round($deudores->sum('total_adeudo'), 2),
        ];

        return $this->streamPdf('reportes.deudores_pdf', $request, $deudores, $resumen, 'Reporte_Deudores_');
    }

    /** GET /reportes/deudores/pdf-detalle */
    public function pdfDetalle(Request $request)
    {
        $deudores = $this->consultarDeudores($request);

        $resumen = [
            'total_deudores' => $deudores->count(),
            'gran_total' => round($deudores->sum('total_adeudo'), 2),
        ];

        return $this->streamPdf('reportes.deudores_detalle_pdf', $request, $deudores, $resumen, 'Deudores_Detalle_');
    }

    /**
     * Genera y transmite un PDF del reporte con los filtros de la petición.
     *
     * @param  array<string, mixed>  $resumen
     */
    private function streamPdf(string $vista, Request $request, Collection $deudores, array $resumen, string $prefijo)
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView($vista, [
            'deudores' => $deudores,
            'resumen' => $resumen,
            'ciclo' => CicloEscolar::find($this->cicloId($request)),
            'estados' => $this->estados($request),
            'buscar' => $this->buscar($request),
            'concepto' => ConceptoCobro::find($this->conceptoId($request)),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream($prefijo.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Deudores del ciclo según los filtros de estado y búsqueda de la petición,
     * ordenados de mayor a menor adeudo.
     */
    private function consultarDeudores(Request $request): Collection
    {
        $buscar = $this->buscar($request);
        $cargoFilter = $this->buildCargoFilter($this->estados($request), now()->toDateString(), $this->conceptoId($request));

        return Inscripcion::query()
            ->with([
                'alumno',
                'grupo.grado.nivel',
                'cargos' => fn ($q) => $cargoFilter($q)
                    ->with(['concepto', 'detallesPagosVigentes'])
                    ->orderBy('fecha_vencimiento'),
            ])
            ->where('ciclo_id', $this->cicloId($request))
            ->where('activo', true)
            ->whereHas('cargos', fn ($q) => $cargoFilter($q))
            ->when($buscar !== '', fn ($q) => $q->whereHas('alumno', fn ($a) => $this->filtrarPorNombre($a, $buscar)))
            ->get()
            ->filter(fn ($ins) => $ins->cargos->isNotEmpty())   // descartar si no quedó ningún cargo tras el filtro
            ->map(fn (Inscripcion $ins) => $this->resumirDeudor($ins))
            ->sortByDesc('total_adeudo')
            ->values();
    }

    private function cicloId(Request $request): ?int
    {
        $cicloId = $request->get('ciclo_id')
            ?? auth()->user()->ciclo_seleccionado_id
            ?? CicloEscolar::activo()->value('id');

        return $cicloId !== null ? (int) $cicloId : null;
    }

    /** @return string[] */
    private function estados(Request $request): array
    {
        return $request->has('estados')
            ? (array) $request->get('estados')
            : self::ESTADOS_DEFAULT;
    }

    private function buscar(Request $request): string
    {
        return trim((string) $request->get('buscar', ''));
    }

    /** Concepto de cobro elegido en el filtro; null cuando se muestran todos. */
    private function conceptoId(Request $request): ?int
    {
        return $request->filled('concepto_id') ? (int) $request->get('concepto_id') : null;
    }

    /**
     * Restringe la consulta de alumnos a los que coincidan con cada palabra buscada
     * en apellido paterno, apellido materno o nombre(s).
     */
    private function filtrarPorNombre(Builder $query, string $buscar): Builder
    {
        foreach (preg_split('/\s+/', $buscar) as $palabra) {
            $query->where(fn ($q) => $q
                ->where('ap_paterno', 'like', "%{$palabra}%")
                ->orWhere('ap_materno', 'like', "%{$palabra}%")
                ->orWhere('nombre', 'like', "%{$palabra}%"));
        }

        return $query;
    }

    /**
     * Construye el resumen de adeudo de una inscripción, con el desglose de cada cargo.
     *
     * @return array<string, mixed>
     */
    private function resumirDeudor(Inscripcion $ins): array
    {
        $cargos = $ins->cargos->map(fn (Cargo $c) => $this->detallarCargo($c));

        return [
            'alumno' => $ins->alumno,
            'inscripcion_id' => $ins->id,
            'grupo' => $ins->grupo,
            'nivel' => $ins->grupo?->grado?->nivel,
            'pendientes' => $cargos->where('estado', 'pendiente')->count(),
            'vencidos' => $cargos->where('estado', 'vencido')->count(),
            'parciales' => $cargos->where('estado', 'parcial')->count(),
            'meses_vencidos' => $cargos->where('vencido', true)->pluck('mes')->unique()->values(),
            'total_cargos' => $cargos->count(),
            'total_adeudo' => round($cargos->sum('saldo_pendiente'), 2),
            'cargos' => $cargos,
        ];
    }

    /**
     * Datos de un cargo adeudado para el reporte: concepto, mes, vencimiento y saldo.
     *
     * @return array<string, mixed>
     */
    private function detallarCargo(Cargo $cargo): array
    {
        $abonado = (float) $cargo->detallesPagosVigentes->sum('monto_abonado');
        $vencido = $cargo->fecha_vencimiento?->lt(today()) ?? false;

        return [
            'concepto' => $cargo->concepto?->nombre ?? '—',
            'mes' => $cargo->periodo_label
                ?: ucfirst($cargo->fecha_vencimiento?->translatedFormat('F Y') ?? '—'),
            'fecha_vencimiento' => $cargo->fecha_vencimiento,
            'monto_original' => (float) $cargo->monto_original,
            'saldo_abonado' => $abonado,
            'saldo_pendiente' => max(0, (float) $cargo->monto_original - $abonado),
            'vencido' => $vencido,
            'estado' => in_array($cargo->estado, ['pendiente', 'parcial'], true) && $vencido ? 'vencido' : $cargo->estado,
        ];
    }

    /**
     * Devuelve un closure que aplica los filtros de estado (y de concepto, si se eligió)
     * sobre una query de Cargo.
     *
     * Las categorías no se traslapan: 'pendiente' y 'parcial' son cargos aún no vencidos
     * (sin y con abono); 'vencido' es todo cargo con fecha pasada, tenga o no abono.
     *
     * @param  string[]  $estados  Valores posibles: 'pendiente', 'vencido', 'parcial'
     */
    private function buildCargoFilter(array $estados, string $hoy, ?int $conceptoId = null): \Closure
    {
        return function ($query) use ($estados, $hoy, $conceptoId) {
            return $query
                ->when($conceptoId !== null, fn ($q) => $q->where('concepto_id', $conceptoId))
                ->where(function ($q) use ($estados, $hoy) {
                    if (in_array('pendiente', $estados, true)) {
                        $q->orWhere(
                            fn ($s) => $s->where('estado', 'pendiente')
                                ->whereDate('fecha_vencimiento', '>=', $hoy)
                        );
                    }
                    // Vencido incluye los cargos con abono parcial cuya fecha ya pasó.
                    if (in_array('vencido', $estados, true)) {
                        $q->orWhere(
                            fn ($s) => $s->whereIn('estado', ['pendiente', 'parcial'])
                                ->whereDate('fecha_vencimiento', '<', $hoy)
                        );
                    }
                    if (in_array('parcial', $estados, true)) {
                        $q->orWhere(
                            fn ($s) => $s->where('estado', 'parcial')
                                ->whereDate('fecha_vencimiento', '>=', $hoy)
                        );
                    }
                });
        };
    }
}
