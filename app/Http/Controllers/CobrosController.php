<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Auditoria;
use App\Models\BecaAlumno;
use App\Models\Cargo;
use App\Models\ConceptoCobro;
use App\Models\DescuentoCargo;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\PagoDetalle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CobrosController extends Controller
{
    /** GET /cobros — Pantalla principal: buscador de alumno */
    public function index(Request $request): View
    {
        $busqueda = $request->get('q', '');

        $alumnos = $request->filled('q')
            ? Alumno::with([
                'inscripciones' => fn ($q) => $q
                    ->where('activo', true)
                    ->orderByRaw('grupo_id IS NULL')
                    ->with('grupo.grado.nivel', 'ciclo'),
            ])
                ->where(fn ($q) => $q
                    ->where('nombre', 'like', "%{$busqueda}%")
                    ->orWhere('ap_paterno', 'like', "%{$busqueda}%")
                    ->orWhere('matricula', 'like', "%{$busqueda}%")
                    ->orWhere('curp', 'like', "%{$busqueda}%")
                )
                ->where('estado', 'activo')
                ->limit(10)
                ->get()
            : collect();

        return view('cobros.index', compact('alumnos', 'busqueda'));
    }

    /** GET /cobros/alumno/{alumnoId} — Pantalla de cobro: cargos pendientes */
    public function alumno(int $alumnoId): View
    {
        $alumno = Alumno::with([
            'familia',
            'inscripciones' => fn ($q) => $q
                ->where('activo', true)
                ->with('grupo.grado.nivel', 'ciclo')
                ->orderByDesc('id'),
        ])->findOrFail($alumnoId);

        $inscripcionActual = $alumno->inscripciones->first();

        // Fallback: si no hay inscripción activa, usar la más reciente de cualquier ciclo
        $inscripcionParaCobro = $inscripcionActual
            ?? Inscripcion::with('grupo.grado.nivel', 'ciclo')
                ->where('alumno_id', $alumnoId)
                ->orderByDesc('id')
                ->first();

        $becasAlumno = BecaAlumno::with('catalogoBeca')
            ->where('alumno_id', $alumnoId)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('vigencia_fin')
                    ->orWhere('vigencia_fin', '>=', today());
            })
            ->get();
        $becasPorPlan = $becasAlumno->whereNotNull('plan_id')->keyBy('plan_id');
        $becasPorConcepto = $becasAlumno->whereNotNull('concepto_id')->keyBy('concepto_id');

        $hoy = now();
        $hoyFecha = today();

        $cargos = Cargo::with([
            'concepto',
            'detallesPagosVigentes',
            'inscripcion.ciclo',
            'asignacion.plan.politicasDescuentoActivas',
            'asignacion.plan.politicasRecargo',
            'descuentos',
        ])
            ->whereHas('inscripcion', fn ($q) => $q->where('alumno_id', $alumnoId))
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->withSum('detallesPagosVigentes as total_abonado', 'monto_abonado')
            ->orderBy('fecha_vencimiento')
            ->get()
            ->map(fn ($cargo) => $this->enriquecerCargo($cargo, $hoy, $hoyFecha, $becasPorPlan, $becasPorConcepto));

        $conceptos = ConceptoCobro::where('activo', true)
            ->whereIn('tipo', ['cargo_unico', 'cargo_recurrente', 'inscripcion'])
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        return view('cobros.alumno', compact(
            'alumno',
            'inscripcionActual',
            'inscripcionParaCobro',
            'cargos',
            'conceptos'
        ));
    }

    /** GET /cobros/buscar-alumno (AJAX) — Búsqueda rápida para autocomplete */
    public function buscarAlumno(Request $request): JsonResponse
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $alumnos = Alumno::with([
            'inscripciones' => fn ($q) => $q
                ->where('activo', true)
                ->with('grupo.grado.nivel')
                ->orderByDesc('id'),
        ])
            ->where(fn ($query) => $query
                ->where('nombre', 'like', "%{$q}%")
                ->orWhere('ap_paterno', 'like', "%{$q}%")
                ->orWhere('matricula', 'like', "%{$q}%")
            )
            ->where('estado', 'activo')
            ->limit(8)
            ->get()
            ->map(function ($a) {
                $insc = $a->inscripciones->first();

                return [
                    'id' => $a->id,
                    'nombre' => "{$a->nombre} {$a->ap_paterno} {$a->ap_materno}",
                    'matricula' => $a->matricula,
                    'grupo' => trim(
                        ($insc?->grupo?->grado?->nivel?->nombre ?? '').' '.
                        ($insc?->grupo?->grado?->nombre ?? '').'° '.
                        ($insc?->grupo?->nombre ?? '')
                    ),
                    'url' => route('cobros.alumno', $a->id),
                ];
            });

        return response()->json($alumnos);
    }

    /** POST /cobros/registrar — Procesa el pago: crea pago + pago_detalle(s) */
    public function registrar(Request $request): RedirectResponse
    {
        $request->validate([
            'alumno_id' => ['required', 'integer', 'exists:alumno,id'],
            'forma_pago' => ['required', 'in:efectivo,transferencia,tarjeta,cheque'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'fecha_pago' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.tipo' => ['required', 'in:cargo,nuevo'],
            'items.*.cargo_id' => ['required_if:items.*.tipo,cargo', 'integer'],
            'items.*.monto_abonado' => ['required', 'numeric', 'min:0.01'],
            'items.*.descuento_beca' => ['nullable', 'numeric', 'min:0'],
            'items.*.descuento_pronto_pago' => ['nullable', 'numeric', 'min:0'],
            'items.*.descuento_otros' => ['nullable', 'numeric', 'min:0'],
            'items.*.recargo' => ['nullable', 'numeric', 'min:0'],
            'items.*.concepto_id' => ['required_if:items.*.tipo,nuevo', 'integer'],
            'items.*.inscripcion_id' => ['required_if:items.*.tipo,nuevo', 'integer'],
        ], [
            'items.required' => 'Debe seleccionar al menos un concepto a cobrar.',
            'items.*.monto_abonado.min' => 'El monto a cobrar debe ser mayor a cero.',
            'forma_pago.required' => 'Selecciona la forma de pago.',
        ]);

        try {
            [$pagoId, $folio] = DB::transaction(function () use ($request): array {
                $cajeroId = auth()->id();
                $montoTotal = 0;
                $detalles = [];

                foreach ($request->items as $item) {
                    $detalle = $this->resolverItemPago($item, $cajeroId, $request->fecha_pago);
                    $detalles[] = $detalle;
                    $montoTotal += $detalle['montoFinal'];
                }

                $folio = $this->generarFolio();

                $pago = Pago::create([
                    'cajero_id' => $cajeroId,
                    'monto_total' => $montoTotal,
                    'fecha_pago' => $request->fecha_pago,
                    'forma_pago' => $request->forma_pago,
                    'referencia' => $request->referencia,
                    'folio_recibo' => $folio,
                    'estado' => 'vigente',
                ]);

                foreach ($detalles as $d) {
                    $this->crearDetallePago($pago->id, $d);
                }

                Auditoria::registrar('pago', $pago->id, 'insert', null, [
                    'folio' => $folio,
                    'alumno_id' => $request->alumno_id,
                    'monto_total' => $montoTotal,
                    'items' => count($detalles),
                ]);

                return [$pago->id, $folio];
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Error al registrar el pago: '.$e->getMessage());
        }

        return redirect()->route('cobros.recibo', $pagoId)->with('success', "Pago registrado. Folio: {$folio}");
    }

    /** GET /cobros/recibo/{pagoId} — Recibo de pago */
    public function recibo(int $pagoId): View
    {
        $pago = $this->cargarPago($pagoId);
        $alumno = $this->alumnoDelPago($pago);

        return view('cobros.recibo', compact('pago', 'alumno'));
    }

    /** GET /cobros/recibo/{pagoId}/pdf — Descarga el recibo en PDF */
    public function descargarPdf(int $pagoId)
    {
        $pago = $this->cargarPago($pagoId);
        $alumno = $this->alumnoDelPago($pago);

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf = Pdf::loadView('cobros.reportes.recibo_pdf', compact('pago', 'alumno'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("Recibo_Folio_{$pago->folio_recibo}.pdf");
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Carga un pago con todas las relaciones necesarias para el recibo.
     * Usada tanto en la vista web como en la descarga PDF.
     */
    private function cargarPago(int $pagoId): Pago
    {
        return Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'detalles.cargo.condonacionDetalles',
            'cajero',
        ])->findOrFail($pagoId);
    }

    /** Obtiene el alumno a partir de los detalles de un pago. */
    private function alumnoDelPago(Pago $pago): ?Alumno
    {
        return $pago->detalles->first()?->cargo?->inscripcion?->alumno;
    }

    /**
     * Enriquece un cargo con los valores calculados que necesita la vista de cobros:
     * pendiente, vencimiento, becas, recargos y descuentos por política.
     */
    private function enriquecerCargo(
        Cargo $cargo,
        Carbon $hoy,
        Carbon $hoyFecha,
        Collection $becasPorPlan,
        Collection $becasPorConcepto,
    ): Cargo {
        $abonado = (float) ($cargo->total_abonado ?? 0);
        $pendiente = max(0, round((float) $cargo->monto_original - $abonado, 2));
        $vencido = $hoyFecha->gt($cargo->fecha_vencimiento);

        $cargo->abonado = $abonado;
        $cargo->pendiente = $pendiente;
        $cargo->vencido = $vencido;
        $cargo->dias_atraso = $vencido ? $cargo->fecha_vencimiento->diffInDays($hoy) : 0;

        [$becaDescuento, $becaPorcentaje] = $this->calcularBecaCobro($cargo, $pendiente, $becasPorPlan, $becasPorConcepto);

        [$descuento, $recargo, $mesesRetraso, $pd] = ($pendiente > 0 && $cargo->asignacion?->plan)
            ? $this->calcularPoliticaCobro($cargo, $pendiente, $vencido, $hoyFecha)
            : [0.0, 0.0, 0, null];

        $descuentoCondonacion = (float) $cargo->descuentos->sum('monto_aplicado');

        $cargo->beca_descuento_calc = $becaDescuento;
        $cargo->beca_porcentaje = $becaPorcentaje;
        $cargo->recargo_calc = $recargo;
        $cargo->descuento_calc = $descuento;
        $cargo->descuento_tipo = $pd?->tipo_valor;
        $cargo->descuento_valor = $pd ? (float) $pd->valor : 0.0;
        $cargo->meses_retraso = $mesesRetraso;
        $cargo->descuento_condonacion_calc = $descuentoCondonacion;
        $cargo->monto_a_pagar_hoy = max(0, $pendiente - $becaDescuento - $descuento - $descuentoCondonacion + $recargo);

        return $cargo;
    }

    /** Calcula el descuento de beca aplicable a un cargo. */
    private function calcularBecaCobro(Cargo $cargo, float $pendiente, Collection $becasPorPlan, Collection $becasPorConcepto): array
    {
        if ($pendiente <= 0) {
            return [0.0, null];
        }

        $beca = $cargo->asignacion?->plan_id
            ? $becasPorPlan->get($cargo->asignacion->plan_id)
            : null;
        $beca ??= $becasPorConcepto->get($cargo->concepto_id);

        if (! $beca) {
            return [0.0, null];
        }

        // La beca aplica si la fecha de vencimiento del cargo está dentro de la vigencia
        if ($beca->vigencia_inicio && $cargo->fecha_vencimiento->lt($beca->vigencia_inicio)) {
            return [0.0, null];
        }

        $descuento = $beca->calcularDescuento($pendiente);
        $porcentaje = $beca->catalogoBeca->tipo === 'porcentaje' ? (float) $beca->catalogoBeca->valor : null;

        return [$descuento, $porcentaje];
    }

    /**
     * Calcula el recargo o descuento por política del plan de pago.
     * Retorna [$descuento, $recargo, $mesesRetraso, $politicaDescuento].
     * $politicaDescuento se necesita para derivar descuento_tipo y descuento_valor en la vista.
     */
    private function calcularPoliticaCobro(Cargo $cargo, float $pendiente, bool $vencido, Carbon $hoyFecha): array
    {
        $plan = $cargo->asignacion->plan;

        if ($vencido) {
            $mesesRetraso = (int) $cargo->fecha_vencimiento->diffInMonths($hoyFecha) + 1;
            $pr = $plan->politicasRecargo->firstWhere('activo', true);

            return [0.0, $pr ? $pr->calcular($pendiente, $mesesRetraso) : 0.0, $mesesRetraso, null];
        }

        $pd = $plan->politicasDescuentoActivas->first(fn ($p) => $p->aplicaHoy());

        return [$pd ? $pd->calcular($pendiente) : 0.0, 0.0, 0, $pd];
    }

    /**
     * Resuelve un ítem del formulario de cobro:
     * crea el cargo si es nuevo o lo carga si existe, y calcula montos.
     */
    private function resolverItemPago(array $item, int $cajeroId, string $fechaPago): array
    {
        $abonado = (float) $item['monto_abonado'];
        $descBeca = (float) ($item['descuento_beca'] ?? 0);
        $descProntoPago = (float) ($item['descuento_pronto_pago'] ?? 0);
        $descOtros = (float) ($item['descuento_otros'] ?? 0);
        $recargo = (float) ($item['recargo'] ?? 0);
        $montoFinal = round($abonado - $descBeca - $descProntoPago - $descOtros + $recargo, 2);

        if ($montoFinal <= 0) {
            throw new \Exception('El monto final de un concepto no puede ser cero o negativo.');
        }

        $cargo = $item['tipo'] === 'nuevo'
            ? Cargo::create([
                'inscripcion_id' => $item['inscripcion_id'],
                'concepto_id' => $item['concepto_id'],
                'generado_por' => $cajeroId,
                'monto_original' => $abonado,
                'fecha_vencimiento' => $fechaPago,
                'estado' => 'pagado',
                'periodo' => now()->format('Y-m'),
            ])
            : Cargo::findOrFail($item['cargo_id']);

        return compact('cargo', 'abonado', 'descBeca', 'descProntoPago', 'descOtros', 'recargo', 'montoFinal');
    }

    /** Crea el registro PagoDetalle y actualiza el estado del cargo asociado. */
    private function crearDetallePago(int $pagoId, array $d): void
    {
        PagoDetalle::create([
            'pago_id' => $pagoId,
            'cargo_id' => $d['cargo']->id,
            'descuento_beca' => $d['descBeca'],
            'descuento_pronto_pago' => $d['descProntoPago'],
            'descuento_otros' => $d['descOtros'],
            'recargo_aplicado' => $d['recargo'],
            'monto_abonado' => $d['abonado'],
            'monto_final' => $d['montoFinal'],
        ]);

        if ($d['cargo']->estado === 'pagado') {
            return;
        }

        $totalAbonado = PagoDetalle::where('cargo_id', $d['cargo']->id)
            ->whereHas('pago', fn ($q) => $q->where('estado', 'vigente'))
            ->sum('monto_abonado');

        $totalCondonado = (float) DescuentoCargo::where('cargo_id', $d['cargo']->id)
            ->sum('monto_aplicado');

        $cubierto = (float) $totalAbonado + $totalCondonado;

        $d['cargo']->update([
            'estado' => $cubierto >= (float) $d['cargo']->monto_original ? 'pagado' : 'parcial',
        ]);
    }

    /** Genera el folio único del recibo con formato R{YYYYMMDD}-{0001}. */
    private function generarFolio(): string
    {
        $prefijo = 'R'.now()->format('Ymd');
        $ultimo = Pago::where('folio_recibo', 'like', "{$prefijo}%")->orderByDesc('folio_recibo')->value('folio_recibo');
        $siguiente = $ultimo ? (int) substr($ultimo, -4) + 1 : 1;

        return $prefijo.'-'.str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}
