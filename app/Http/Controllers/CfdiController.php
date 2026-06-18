<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Cfdi;
use App\Models\ConfigFiscal;
use App\Models\ContactoFamiliar;
use App\Models\Pago;
use App\Models\RazonSocialContacto;
use App\Models\Setting;
use App\Services\FacturaComService;
use App\Traits\RespondsWithJson;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CfdiController extends Controller
{
    use RespondsWithJson;

    /** Mapeo de forma_pago del sistema → clave SAT */
    private const FORMAS_PAGO_SAT = [
        'efectivo'        => '01',
        'cheque'          => '02',
        'transferencia'   => '03',
        'tarjeta_credito' => '04',
        'tarjeta_debito'  => '28',
    ];

    /** Clave SAT por defecto para servicios educativos */
    private const CLAVE_PROD_SERV_DEFAULT = '86101500';

    /** GET /facturas — Lista todas las facturas electrónicas (individuales y globales). */
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 25);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 25;
        }

        $base = Cfdi::query()
            ->when($request->filled('folio'),       fn($q) => $q->where('folio', 'like', "%{$request->folio}%"))
            ->when($request->filled('tipo'),        fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('estado'),      fn($q) => $q->where('estado', $request->estado))
            ->when($request->filled('fecha_desde'), fn($q) => $q->whereDate('fecha_timbrado', '>=', $request->fecha_desde))
            ->when($request->filled('fecha_hasta'), fn($q) => $q->whereDate('fecha_timbrado', '<=', $request->fecha_hasta));

        $resumen = [
            'total'      => (clone $base)->count(),
            'vigentes'   => (clone $base)->where('estado', 'vigente')->count(),
            'cancelados' => (clone $base)->where('estado', 'cancelado')->count(),
            'globales'   => (clone $base)->where('tipo', 'global')->where('estado', 'vigente')->count(),
        ];

        $cfdis = (clone $base)
            ->with(['razonSocial', 'pago'])
            ->withSum('pagos', 'monto_total')
            ->withCount('pagos')
            ->orderByDesc('fecha_timbrado')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('facturas.index', [
            'cfdis'        => $cfdis,
            'resumen'      => $resumen,
            'perPage'      => $perPage,
            'configFiscal' => ConfigFiscal::first(),
        ]);
    }

    /** POST /cfdis/emitir/{pago} — Emite un CFDI 4.0 para el pago indicado. */
    public function emitir(Request $request, int $pagoId, FacturaComService $factura): RedirectResponse|JsonResponse
    {
        $request->validate([
            'razon_social_id' => ['nullable', 'exists:razon_social_contacto,id'],
            'uso_cfdi'        => ['required', 'string', 'max:10'],
        ]);

        $pago = Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis' => fn($q) => $q->where('estado', 'vigente'),
        ])->findOrFail($pagoId);

        if ($pago->estado === 'anulado') {
            return $this->respuestaError('No se puede facturar un pago anulado.');
        }

        if ($pago->cfdis->isNotEmpty()) {
            return $this->respuestaError('Este pago ya tiene un CFDI vigente.');
        }

        $config = ConfigFiscal::first();
        if (! $config) {
            return $this->respuestaError('No hay configuración fiscal registrada. Configure el emisor primero.');
        }

        if (! $config->serie_id) {
            return $this->respuestaError(
                'Falta el ID de serie de factura.com. Ve a Configuración → Datos del Emisor e ingresa el ID '.
                'numérico de la serie (Catálogos → Series en tu panel de factura.com).'
            );
        }

        $razonSocialId = $request->filled('razon_social_id') ? (int) $request->razon_social_id : null;

        $receptor = $razonSocialId
            ? $this->receptorDesdeRazonSocial(RazonSocialContacto::with('contacto')->findOrFail($razonSocialId), $factura)
            : $this->receptorPublicoGeneral($config, $factura);

        try {
            $resultado = DB::transaction(function () use ($pago, $config, $receptor, $request, $razonSocialId, $factura): array {
                $folio   = $config->siguienteFolio();
                $payload = $this->construirPayload($pago, $config, $receptor, $folio, $request->uso_cfdi, $razonSocialId === null);

                $respuesta = $factura->emitir($payload);

                $cfdi = Cfdi::create([
                    'pago_id'          => $pago->id,
                    'config_fiscal_id' => $config->id,
                    'razon_social_id'  => $razonSocialId,
                    'uso_cfdi'         => $request->uso_cfdi,
                    'uuid_sat'         => $respuesta['UUID'] ?? $respuesta['Uuid'] ?? null,
                    'factura_uid'      => $respuesta['UID'] ?? null,
                    'folio'            => $folio,
                    'fecha_timbrado'   => now(),
                    'estado'           => 'vigente',
                ]);

                Auditoria::registrar('cfdi', $cfdi->id, 'insert', null, [
                    'pago_id'     => $pago->id,
                    'folio'       => $folio,
                    'factura_uid' => $cfdi->factura_uid,
                    'uuid_sat'    => $cfdi->uuid_sat,
                ]);

                return ['cfdi' => $cfdi, 'folio' => $folio];
            });
        } catch (\Throwable $e) {
            // Si el receptor está desactualizado en factura.com, limpiamos el UID en caché
            // para que el siguiente intento lo recree automáticamente.
            if ($this->esErrorReceptorInvalido($e->getMessage())) {
                if ($razonSocialId === null) {
                    $config->update(['publico_general_uid' => null]);
                } else {
                    RazonSocialContacto::where('id', $razonSocialId)->update(['factura_uid' => null]);
                }

                return $this->respuestaError(
                    'El cliente receptor estaba desactualizado en factura.com y fue eliminado del caché. '.
                    'Vuelve a intentar emitir el CFDI — en este segundo intento se registrará automáticamente.'
                );
            }

            return $this->respuestaError('Error al emitir CFDI: ' . $e->getMessage());
        }

        return $this->respuestaExito(
            redirectRoute: 'pagos.show',
            jsonData: ['cfdi' => $resultado['cfdi']],
            mensaje: "CFDI emitido correctamente. Folio: {$resultado['folio']}",
            routeParams: [$pago->id]
        );
    }

    /** POST /cfdis/{cfdi}/cancelar — Cancela un CFDI ante el SAT vía factura.com. */
    public function cancelar(Request $request, int $cfdiId, FacturaComService $factura): RedirectResponse|JsonResponse
    {
        $request->validate([
            'motivo' => ['required', 'string', 'in:01,02,03,04'],
        ]);

        $cfdi = Cfdi::with('pago')->findOrFail($cfdiId);

        if ($cfdi->estado === 'cancelado') {
            return $this->respuestaError('Este CFDI ya está cancelado.');
        }

        if (! $cfdi->factura_uid) {
            return $this->respuestaError('No se puede cancelar: UID de factura.com no disponible.');
        }

        try {
            $factura->cancelar($cfdi->factura_uid, $request->motivo);

            $anterior = $cfdi->toArray();
            $cfdi->update(['estado' => 'cancelado']);

            Auditoria::registrar('cfdi', $cfdi->id, 'cancelacion', $anterior, $cfdi->fresh()->toArray());

            return $this->respuestaExito(
                redirectRoute: $cfdi->pago_id ? 'pagos.show' : 'facturas.index',
                jsonData: ['ok' => true],
                mensaje: "CFDI {$cfdi->folio} cancelado correctamente.",
                routeParams: $cfdi->pago_id ? [$cfdi->pago_id] : []
            );
        } catch (\Throwable $e) {
            return $this->respuestaError('Error al cancelar CFDI: ' . $e->getMessage());
        }
    }

    /**
     * GET /cfdis/{cfdi}/form-correo (AJAX)
     * Devuelve los contactos con email del pago para el modal de envío.
     * El contacto del receptor del CFDI viene marcado como predeterminado.
     */
    public function formCorreo(int $cfdiId): JsonResponse
    {
        $cfdi = Cfdi::with([
            'razonSocial.contacto',
            'pago.detalles.cargo.inscripcion.alumno',
        ])->findOrFail($cfdiId);

        $emailDefecto = $cfdi->razonSocial?->contacto?->email;

        $alumnoIds = $cfdi->pago->detalles
            ->map(fn($d) => $d->cargo?->inscripcion?->alumno_id)
            ->filter()
            ->unique()
            ->values();

        $contactos = ContactoFamiliar::query()
            ->whereHas('alumnos', fn($q) => $q->whereIn('alumno.id', $alumnoIds))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get()
            ->map(fn($c) => [
                'nombre'     => $c->nombre_completo,
                'email'      => $c->email,
                'es_defecto' => $emailDefecto && $c->email === $emailDefecto,
            ])
            ->sortByDesc('es_defecto')
            ->values();

        return response()->json([
            'cfdi_id'       => $cfdi->id,
            'folio'         => $cfdi->folio,
            'email_defecto' => $emailDefecto,
            'contactos'     => $contactos,
        ]);
    }

    /**
     * POST /cfdis/{cfdi}/enviar-correo
     * Descarga el PDF y XML de factura.com y los envía como adjuntos al correo indicado.
     */
    public function enviarCorreo(Request $request, int $cfdiId, FacturaComService $factura): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $cfdi = Cfdi::with('pago')->findOrFail($cfdiId);

        if (! $cfdi->factura_uid) {
            return $this->respuestaError('No se puede enviar: el CFDI no tiene UID de factura.com.');
        }

        try {
            $pdf     = $factura->descargar($cfdi->factura_uid, 'pdf');
            $xml     = $factura->descargar($cfdi->factura_uid, 'xml');
            $folio       = $cfdi->folio ?? "CFDI-{$cfdiId}";
            $setting     = Setting::find(1);
            $escuela     = $setting?->nombre_escuela ?? config('app.name');
            $logoRuta    = $setting?->logo_ruta ?? 'logo-escuela.png';
            $logoPath    = public_path('imgs_escuela/reportes/' . $logoRuta);

            Mail::send(
                'emails.cfdi',
                ['folio' => $folio, 'nombreEscuela' => $escuela, 'logoPath' => $logoPath],
                function ($msg) use ($request, $folio, $escuela, $pdf, $xml) {
                    $msg->to($request->email)
                        ->subject("Factura electrónica {$folio} — {$escuela}")
                        ->attachData($pdf, "{$folio}.pdf", ['mime' => 'application/pdf'])
                        ->attachData($xml, "{$folio}.xml", ['mime' => 'application/xml']);
                }
            );

            return $this->respuestaExito(
                redirectRoute: 'pagos.show',
                jsonData: ['ok' => true],
                mensaje: "Factura {$folio} enviada a {$request->email}.",
                routeParams: [$cfdi->pago_id]
            );
        } catch (\Throwable $e) {
            return $this->respuestaError('Error al enviar la factura: ' . $e->getMessage());
        }
    }

    /** GET /cfdis/{cfdi}/descargar/{formato} — Descarga el PDF o XML desde factura.com. */
    public function descargar(int $cfdiId, string $formato, FacturaComService $factura): RedirectResponse|Response
    {
        if (! in_array($formato, ['pdf', 'xml'], true)) {
            abort(404);
        }

        $cfdi = Cfdi::findOrFail($cfdiId);

        if (! $cfdi->factura_uid) {
            return back()->with('error', 'No se puede descargar: CFDI sin UID de factura.com.');
        }

        try {
            $contenido = $factura->descargar($cfdi->factura_uid, $formato);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al descargar: ' . $e->getMessage());
        }

        $nombre   = ($cfdi->folio ?? $cfdi->uuid_sat ?? "CFDI-{$cfdiId}") . ".{$formato}";
        $mimeType = $formato === 'pdf' ? 'application/pdf' : 'application/xml';

        return response($contenido, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ]);
    }

    /**
     * GET /cfdis/preview-global (AJAX)
     * Resumen de pagos sin CFDI vigente en el rango indicado, para previsualizarlo
     * antes de timbrar la factura global.
     */
    public function previewGlobal(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
        ]);

        $pagos = $this->pagosParaFacturaGlobal($request->fecha_desde, $request->fecha_hasta);

        $resumenConceptos = $pagos
            ->flatMap(fn(Pago $p) => $p->detalles)
            ->groupBy(fn($d) => $d->cargo?->concepto?->nombre ?? 'Servicio educativo')
            ->map(fn($detalles, $nombre) => [
                'nombre' => $nombre,
                'monto'  => round($detalles->sum('monto_abonado'), 2),
            ])
            ->values();

        return response()->json([
            'pagos_count'       => $pagos->count(),
            'monto_total'       => round($pagos->sum('monto_total'), 2),
            'resumen_conceptos' => $resumenConceptos,
        ]);
    }

    /**
     * POST /cfdis/emitir-global
     * Emite una factura global (CFDI 4.0 a Público en General) agrupando
     * todos los pagos vigentes sin CFDI en el rango de fechas indicado.
     */
    public function emitirGlobal(Request $request, FacturaComService $factura): RedirectResponse|JsonResponse
    {
        $request->validate([
            'fecha_desde'  => ['required', 'date'],
            'fecha_hasta'  => ['required', 'date', 'after_or_equal:fecha_desde'],
            'periodicidad' => ['required', 'string', 'in:01,02,03,04'],
        ]);

        $config = ConfigFiscal::first();
        if (! $config) {
            return $this->respuestaError('No hay configuración fiscal registrada. Configure el emisor primero.');
        }

        if (! $config->serie_id) {
            return $this->respuestaError('Falta el ID de serie de factura.com. Ve a Configuración → Datos del Emisor.');
        }

        $pagos = $this->pagosParaFacturaGlobal($request->fecha_desde, $request->fecha_hasta);

        if ($pagos->isEmpty()) {
            return $this->respuestaError('No hay pagos sin factura en el período seleccionado.');
        }

        $receptor  = $this->receptorPublicoGeneral($config, $factura);
        $conceptos = $this->construirConceptosGlobal($pagos);

        try {
            $resultado = DB::transaction(function () use ($request, $config, $receptor, $conceptos, $pagos, $factura): array {
                $folio = $config->siguienteFolio();
                $mes   = Carbon::parse($request->fecha_desde)->format('m');
                $anio  = Carbon::parse($request->fecha_desde)->year;

                $payload = [
                    'TipoDocumento'     => 'factura',
                    'Serie'             => $config->serie_id ?? $config->serie,
                    'Folio'             => (string) $config->folio_actual,
                    'UsoCFDI'           => 'S01',
                    'FormaPago'         => '99', // No identificado (agrupa varios métodos)
                    'MetodoPago'        => 'PUE',
                    'Moneda'            => 'MXN',
                    'LugarExpedicion'   => config('factura.cp_expedicion'),
                    'Receptor'          => $receptor,
                    'Conceptos'         => $conceptos,
                    'InformacionGlobal' => [
                        'Periodicidad' => $request->periodicidad,
                        'Meses'        => $mes,
                        'Año'          => (string) $anio,
                    ],
                    'EnviarCorreo'      => false,
                    'Draft'             => false,
                ];

                $respuesta = $factura->emitir($payload);

                $cfdi = Cfdi::create([
                    'pago_id'          => null,
                    'config_fiscal_id' => $config->id,
                    'razon_social_id'  => null,
                    'tipo'             => 'global',
                    'periodicidad'     => $request->periodicidad,
                    'fecha_desde'      => $request->fecha_desde,
                    'fecha_hasta'      => $request->fecha_hasta,
                    'uso_cfdi'         => 'S01',
                    'uuid_sat'         => $respuesta['UUID'] ?? $respuesta['Uuid'] ?? null,
                    'factura_uid'      => $respuesta['UID'] ?? null,
                    'folio'            => $folio,
                    'fecha_timbrado'   => now(),
                    'estado'           => 'vigente',
                ]);

                $cfdi->pagos()->attach($pagos->pluck('id'));

                Auditoria::registrar('cfdi', $cfdi->id, 'insert', null, [
                    'tipo'        => 'global',
                    'folio'       => $folio,
                    'fecha_desde' => $request->fecha_desde,
                    'fecha_hasta' => $request->fecha_hasta,
                    'pagos_count' => $pagos->count(),
                    'monto_total' => $pagos->sum('monto_total'),
                    'factura_uid' => $cfdi->factura_uid,
                    'uuid_sat'    => $cfdi->uuid_sat,
                ]);

                return ['cfdi' => $cfdi, 'folio' => $folio];
            });
        } catch (\Throwable $e) {
            if ($this->esErrorReceptorInvalido($e->getMessage())) {
                $config->update(['publico_general_uid' => null]);

                return $this->respuestaError(
                    'El cliente "Público en General" estaba desactualizado en factura.com y fue eliminado del caché. '.
                    'Vuelve a intentar — en este segundo intento se registrará automáticamente.'
                );
            }

            return $this->respuestaError('Error al emitir factura global: ' . $e->getMessage());
        }

        return $this->respuestaExito(
            redirectRoute: 'pagos.index',
            jsonData: ['cfdi' => $resultado['cfdi']],
            mensaje: "Factura global emitida. Folio: {$resultado['folio']} · {$pagos->count()} pagos agrupados.",
        );
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    private function receptorDesdeRazonSocial(RazonSocialContacto $rs, FacturaComService $factura): array
    {
        if (! $rs->factura_uid) {
            $email = $rs->contacto?->email
                ?? config('factura.email_contacto')
                ?: throw new \RuntimeException(
                    'El contacto no tiene email y FACTURA_EMAIL_CONTACTO no está configurado en .env.'
                );

            $uid = $factura->crearCliente(
                $rs->rfc,
                $rs->razon_social,
                $rs->domicilio_fiscal,
                $rs->regimen_fiscal,
                $email,
            );

            $rs->update(['factura_uid' => $uid]);
            $rs->factura_uid = $uid;
        }

        return [
            'UID'            => $rs->factura_uid,
            'RegimenFiscalR' => $rs->regimen_fiscal,
        ];
    }

    private function receptorPublicoGeneral(ConfigFiscal $config, FacturaComService $factura): array
    {
        if (! $config->publico_general_uid) {
            $cp = config('factura.cp_expedicion')
                ?: throw new \RuntimeException(
                    'Configure FACTURA_CP_EXPEDICION en .env con el código postal del lugar de expedición.'
                );

            $email = config('factura.email_contacto')
                ?: throw new \RuntimeException(
                    'Configure FACTURA_EMAIL_CONTACTO en .env para emitir a Público en General.'
                );

            $uid = $factura->crearCliente('XAXX010101000', 'PUBLICO EN GENERAL', $cp, '616', $email);

            $config->update(['publico_general_uid' => $uid]);
            $config->publico_general_uid = $uid;
        }

        return [
            'UID'            => $config->publico_general_uid,
            'RegimenFiscalR' => '616',
        ];
    }

    /** Construye el payload JSON para la API de factura.com (CFDI 4.0 individual). */
    private function construirPayload(
        Pago         $pago,
        ConfigFiscal $config,
        array        $receptor,
        string       $folio,
        string       $usoCfdi,
        bool         $esPublicoGeneral = false,
    ): array {
        $conceptos = $pago->detalles->map(function ($detalle) {
            $alumno      = $detalle->cargo?->inscripcion?->alumno;
            $descripcion = $detalle->cargo?->etiqueta ?? 'Servicio educativo';

            if ($alumno) {
                $descripcion .= ' — ' . trim("{$alumno->nombre} {$alumno->ap_paterno} {$alumno->ap_materno}");
            }

            return [
                'ClaveProdServ' => $detalle->cargo?->concepto?->clave_sat ?? self::CLAVE_PROD_SERV_DEFAULT,
                'Cantidad'      => 1,
                'ClaveUnidad'   => 'E48',
                'Unidad'        => 'Servicio',
                'ValorUnitario' => round((float) $detalle->monto_abonado, 2),
                'Descripcion'   => mb_substr($descripcion, 0, 1000),
                'Impuestos'     => ['Traslados' => [], 'Retenidos' => []],
            ];
        })->values()->toArray();

        $payload = [
            'TipoDocumento'   => 'factura',
            'Serie'           => $config->serie_id ?? $config->serie,
            'Folio'           => (string) $config->folio_actual,
            'UsoCFDI'         => $usoCfdi,
            'FormaPago'       => self::FORMAS_PAGO_SAT[$pago->forma_pago] ?? '99',
            'MetodoPago'      => 'PUE',
            'Moneda'          => 'MXN',
            'LugarExpedicion' => config('factura.cp_expedicion'),
            'Receptor'        => $receptor,
            'Conceptos'       => $conceptos,
            'EnviarCorreo'    => false,
            'Draft'           => false,
        ];

        if ($esPublicoGeneral) {
            $payload['InformacionGlobal'] = [
                'Periodicidad' => '04',                // Mensual
                'Meses'        => now()->format('m'),  // Mes actual (01–12)
                'Año'          => (string) now()->year,
            ];
        }

        return $payload;
    }

    /** Pagos vigentes sin CFDI vigente dentro del rango de fechas dado. */
    private function pagosParaFacturaGlobal(string $fechaDesde, string $fechaHasta): Collection
    {
        return Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
        ])
            ->where('estado', 'vigente')
            ->whereBetween('fecha_pago', [$fechaDesde, $fechaHasta])
            ->whereDoesntHave('cfdis', fn($q) => $q->where('estado', 'vigente'))
            ->get();
    }

    /**
     * Construye los conceptos del CFDI global agrupando los detalles de todos
     * los pagos por concepto de cobro y sumando sus montos.
     *
     * @param Collection<int, Pago> $pagos
     */
    private function construirConceptosGlobal(Collection $pagos): array
    {
        return $pagos
            ->flatMap(fn(Pago $p) => $p->detalles)
            ->groupBy(fn($d) => $d->cargo?->concepto?->id ?? 0)
            ->map(function ($detalles) {
                $concepto = $detalles->first()->cargo?->concepto;

                return [
                    'ClaveProdServ' => $concepto?->clave_sat ?? self::CLAVE_PROD_SERV_DEFAULT,
                    'Cantidad'      => 1,
                    'ClaveUnidad'   => 'E48',
                    'Unidad'        => 'Servicio',
                    'ValorUnitario' => round($detalles->sum('monto_abonado'), 2),
                    'Descripcion'   => mb_substr($concepto?->nombre ?? 'Servicio educativo', 0, 1000),
                    'Impuestos'     => ['Traslados' => [], 'Retenidos' => []],
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Detecta si el error de factura.com indica que el receptor tiene datos inválidos.
     * En ese caso conviene limpiar el UID en caché y recrear el cliente en el siguiente intento.
     */
    private function esErrorReceptorInvalido(string $mensaje): bool
    {
        $m = strtolower($mensaje);

        return str_contains($m, 'receptor') && (
            str_contains($m, 'catálogo')    ||
            str_contains($m, 'catalogo')    ||
            str_contains($m, 'no se encuentra') ||
            str_contains($m, 'not found')   ||
            str_contains($m, 'código postal') ||
            str_contains($m, 'codigo postal') ||
            str_contains($m, 'domicilio fiscal')
        );
    }
}
