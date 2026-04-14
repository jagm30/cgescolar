<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\ConceptoCobro;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CobrosController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // GET /cobros
    // Pantalla principal: buscador de alumno
    // ══════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $alumnos   = collect();
        $busqueda  = $request->get('q', '');

        if ($request->filled('q')) {
            $alumnos = Alumno::with([
                'inscripciones' => fn($q) => $q
                    ->where('activo', true)
                    ->with('grupo.grado.nivel', 'ciclo'),
            ])
            ->where(function ($q) use ($busqueda) {
                $q->where('nombre',     'like', "%{$busqueda}%")
                  ->orWhere('ap_paterno', 'like', "%{$busqueda}%")
                  ->orWhere('matricula',  'like', "%{$busqueda}%")
                  ->orWhere('curp',       'like', "%{$busqueda}%");
            })
            ->where('estado', 'activo')
            ->limit(10)
            ->get();
        }

        return view('cobros.index', compact('alumnos', 'busqueda'));
    }

    // ══════════════════════════════════════════════════════════
    // GET /cobros/alumno/{alumnoId}
    // Pantalla de cobro: cargos pendientes del alumno
    // ══════════════════════════════════════════════════════════
    public function alumno(int $alumnoId)
    {
        $alumno = Alumno::with([
            'familia',
            'inscripciones' => fn($q) => $q
                ->where('activo', true)
                ->with('grupo.grado.nivel', 'ciclo')
                ->orderByDesc('id'),
        ])->findOrFail($alumnoId);

        $inscripcionActual = $alumno->inscripciones->first();

        // Cargos pendientes y parciales de todas sus inscripciones
        $hoy = now();

        $cargos = Cargo::with([
            'concepto',
            'detallesPagosVigentes',
            'inscripcion.ciclo',
            'asignacion.plan.politicasDescuentoActivas',
            'asignacion.plan.politicasRecargo',
        ])
        ->whereHas('inscripcion', fn($q) => $q->where('alumno_id', $alumnoId))
        ->whereIn('estado', ['pendiente', 'parcial'])
        ->withSum('detallesPagosVigentes as total_abonado', 'monto_abonado')
        ->orderBy('fecha_vencimiento')
        ->get()
        ->map(function ($cargo) use ($hoy) {
            $abonado    = (float) ($cargo->total_abonado ?? 0);
            $pendiente  = max(0, round((float) $cargo->monto_original - $abonado, 2));
            $vencido    = $hoy->gt($cargo->fecha_vencimiento);

            $cargo->abonado     = $abonado;
            $cargo->pendiente   = $pendiente;
            $cargo->vencido     = $vencido;
            $cargo->dias_atraso = $vencido
                ? $cargo->fecha_vencimiento->diffInDays($hoy)
                : 0;

            // ── Calcular recargo / descuento según política del plan ──
            $descuento    = 0.0;
            $recargo      = 0.0;
            $mesesRetraso = 0;

            if ($pendiente > 0 && $cargo->asignacion?->plan) {
                $plan = $cargo->asignacion->plan;

                if ($vencido) {
                    $mesesRetraso = (int) $cargo->fecha_vencimiento->diffInMonths($hoy) + 1;
                    $pr = $plan->politicasRecargo->firstWhere('activo', true);
                    if ($pr) {
                        $recargo = $pr->calcular($pendiente, $mesesRetraso);
                    }
                } else {
                    $pd = $plan->politicasDescuentoActivas->first(fn($p) => $p->aplicaHoy());
                    if ($pd) {
                        $descuento = $pd->calcular($pendiente);
                    }
                }
            }

            $cargo->recargo_calc      = $recargo;
            $cargo->descuento_calc    = $descuento;
            $cargo->meses_retraso     = $mesesRetraso;
            $cargo->monto_a_pagar_hoy = max(0, $pendiente - $descuento + $recargo);

            return $cargo;
        });

        // Conceptos para cargo único en el momento
        $conceptos = ConceptoCobro::where('activo', true)
            ->whereIn('tipo', ['cargo_unico', 'cargo_recurrente', 'inscripcion'])
            ->orderBy('tipo')->orderBy('nombre')
            ->get();

        return view('cobros.alumno', compact(
            'alumno', 'inscripcionActual', 'cargos', 'conceptos'
        ));
    }

    // ══════════════════════════════════════════════════════════
    // GET /cobros/buscar-alumno  (AJAX)
    // Búsqueda rápida para autocomplete
    // ══════════════════════════════════════════════════════════
    public function buscarAlumno(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $alumnos = Alumno::with([
            'inscripciones' => fn($q) => $q
                ->where('activo', true)
                ->with('grupo.grado.nivel')
                ->orderByDesc('id'),
        ])
        ->where(function ($query) use ($q) {
            $query->where('nombre',     'like', "%{$q}%")
                  ->orWhere('ap_paterno', 'like', "%{$q}%")
                  ->orWhere('matricula',  'like', "%{$q}%");
        })
        ->where('estado', 'activo')
        ->limit(8)
        ->get()
        ->map(fn($a) => [
            'id'         => $a->id,
            'nombre'     => "{$a->nombre} {$a->ap_paterno} {$a->ap_materno}",
            'matricula'  => $a->matricula,
            'grupo'      => $a->inscripciones->first()?->grupo?->grado?->nivel?->nombre . ' '
                          . ($a->inscripciones->first()?->grupo?->grado?->nombre ?? '') . '° '
                          . ($a->inscripciones->first()?->grupo?->nombre ?? ''),
            'url'        => route('cobros.alumno', $a->id),
        ]);

        return response()->json($alumnos);
    }

    // ══════════════════════════════════════════════════════════
    // POST /cobros/registrar
    // Procesa el pago: crea pago + pago_detalle(s)
    // ══════════════════════════════════════════════════════════
    public function registrar(Request $request)
    {
        $request->validate([
            'alumno_id'              => ['required', 'integer', 'exists:alumno,id'],
            'forma_pago'             => ['required', 'in:efectivo,transferencia,tarjeta,cheque'],
            'referencia'             => ['nullable', 'string', 'max:100'],
            'fecha_pago'             => ['required', 'date'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.tipo'           => ['required', 'in:cargo,nuevo'],
            // Para cargos existentes
            'items.*.cargo_id'       => ['required_if:items.*.tipo,cargo', 'integer'],
            'items.*.monto_abonado'  => ['required', 'numeric', 'min:0.01'],
            'items.*.descuento_beca' => ['nullable', 'numeric', 'min:0'],
            'items.*.descuento_otros'=> ['nullable', 'numeric', 'min:0'],
            'items.*.recargo'        => ['nullable', 'numeric', 'min:0'],
            // Para cargo nuevo en el momento
            'items.*.concepto_id'   => ['required_if:items.*.tipo,nuevo', 'integer'],
            'items.*.inscripcion_id'=> ['required_if:items.*.tipo,nuevo', 'integer'],
        ], [
            'items.required'             => 'Debe seleccionar al menos un concepto a cobrar.',
            'items.*.monto_abonado.min'  => 'El monto a cobrar debe ser mayor a cero.',
            'forma_pago.required'        => 'Selecciona la forma de pago.',
        ]);

        DB::beginTransaction();

        try {
            $cajeroId   = auth()->id();
            $montoTotal = 0;
            $detalles   = [];

            foreach ($request->items as $item) {
                $abonado    = (float) $item['monto_abonado'];
                $descBeca   = (float) ($item['descuento_beca']   ?? 0);
                $descOtros  = (float) ($item['descuento_otros']  ?? 0);
                $recargo    = (float) ($item['recargo']          ?? 0);
                $montoFinal = round($abonado - $descBeca - $descOtros + $recargo, 2);

                if ($montoFinal <= 0) {
                    throw new \Exception("El monto final de un concepto no puede ser cero o negativo.");
                }

                if ($item['tipo'] === 'nuevo') {
                    // Crear cargo nuevo en el momento
                    $cargo = Cargo::create([
                        'inscripcion_id'  => $item['inscripcion_id'],
                        'concepto_id'     => $item['concepto_id'],
                        'generado_por'    => $cajeroId,
                        'monto_original'  => $abonado,
                        'fecha_vencimiento' => $request->fecha_pago,
                        'estado'          => 'pagado',
                        'periodo'         => now()->format('Y-m'),
                    ]);
                } else {
                    $cargo = Cargo::findOrFail($item['cargo_id']);
                }

                $detalles[] = [
                    'cargo'      => $cargo,
                    'abonado'    => $abonado,
                    'descBeca'   => $descBeca,
                    'descOtros'  => $descOtros,
                    'recargo'    => $recargo,
                    'montoFinal' => $montoFinal,
                ];

                $montoTotal += $montoFinal;
            }

            // Generar folio
            $folio = $this->generarFolio();

            // Crear encabezado de pago
            $pago = Pago::create([
                'cajero_id'    => $cajeroId,
                'monto_total'  => $montoTotal,
                'fecha_pago'   => $request->fecha_pago,
                'forma_pago'   => $request->forma_pago,
                'referencia'   => $request->referencia ?? null,
                'folio_recibo' => $folio,
                'estado'       => 'vigente',
            ]);

            // Crear detalles y actualizar estado de cargos
            foreach ($detalles as $d) {
                PagoDetalle::create([
                    'pago_id'         => $pago->id,
                    'cargo_id'        => $d['cargo']->id,
                    'descuento_beca'  => $d['descBeca'],
                    'descuento_otros' => $d['descOtros'],
                    'recargo_aplicado'=> $d['recargo'],
                    'monto_abonado'   => $d['abonado'],
                    'monto_final'     => $d['montoFinal'],
                ]);

                // Actualizar estado del cargo
                if ($d['cargo']->estado !== 'pagado') {
                    $totalAbonado = PagoDetalle::where('cargo_id', $d['cargo']->id)
                        ->whereHas('pago', fn($q) => $q->where('estado', 'vigente'))
                        ->sum('monto_abonado');

                    $nuevoEstado = $totalAbonado >= $d['cargo']->monto_original
                        ? 'pagado'
                        : 'parcial';

                    $d['cargo']->update(['estado' => $nuevoEstado]);
                }
            }

            Auditoria::registrar('pago', $pago->id, 'insert', null, [
                'folio'       => $folio,
                'alumno_id'   => $request->alumno_id,
                'monto_total' => $montoTotal,
                'items'       => count($detalles),
            ]);

            DB::commit();

            return redirect()
                ->route('cobros.recibo', $pago->id)
                ->with('success', "Pago registrado. Folio: {$folio}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════
    // GET /cobros/recibo/{pagoId}
    // Recibo de pago generado
    // ══════════════════════════════════════════════════════════
    public function recibo(int $pagoId)
    {
        $pago = Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cajero',
        ])->findOrFail($pagoId);

        // Obtener el alumno del primer detalle
        $alumno = $pago->detalles->first()?->cargo?->inscripcion?->alumno;

        return view('cobros.recibo', compact('pago', 'alumno'));
    }

    // ══════════════════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════════════════
    private function generarFolio(): string
    {
        $prefijo = 'R' . now()->format('Ymd');
        $ultimo  = Pago::where('folio_recibo', 'like', "{$prefijo}%")
            ->orderByDesc('folio_recibo')
            ->value('folio_recibo');
        $siguiente = $ultimo ? (int) substr($ultimo, -4) + 1 : 1;

        return $prefijo . '-' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}
