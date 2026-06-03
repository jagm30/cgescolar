<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Cfdi;
use App\Models\ConfigFiscal;
use App\Models\Pago;
use App\Models\RazonSocialContacto;
use App\Services\FacturaComService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    // ─────────────────────────────────────────────────────────────────────────
    // POST /cfdis/emitir/{pago}
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Emite un CFDI 4.0 para el pago indicado usando la API de factura.com.
     */
    public function emitir(Request $request, int $pagoId, FacturaComService $factura)
    {
        $request->validate([
            'razon_social_id' => ['nullable', 'exists:razon_social_contacto,id'],
            'uso_cfdi'        => ['required', 'string', 'max:10'],
        ]);

        $pago = Pago::with([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'cfdis' => fn ($q) => $q->where('estado', 'vigente'),
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
            return $this->respuestaError('Falta el ID de serie de factura.com. Ve a Configuración → Datos del Emisor e ingresa el ID numérico de la serie (Catálogos → Series en tu panel de factura.com).');
        }

        // Determinar datos del receptor
        $razonSocialId = $request->filled('razon_social_id')
            ? (int) $request->razon_social_id
            : null;

        $receptor = $razonSocialId
            ? $this->receptorDesdeRazonSocial(
                RazonSocialContacto::with('contacto')->findOrFail($razonSocialId),
                $factura
              )
            : $this->receptorPublicoGeneral($config, $factura);

        DB::beginTransaction();
        try {
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

            DB::commit();

            return $this->respuestaExito(
                redirectRoute: 'pagos.show',
                jsonData: ['cfdi' => $cfdi],
                mensaje: "CFDI emitido correctamente. Folio: {$folio}",
                routeParams: [$pago->id]
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            // Si el error es que el receptor no existe en el catálogo de factura.com,
            // el UID almacenado pertenece a otra cuenta (p.ej. después de cambiar credenciales).
            // Limpiamos el UID obsoleto para que el siguiente intento lo recree.
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

            return $this->respuestaError('Error al emitir CFDI: '.$e->getMessage());
        }
    }

    /**
     * Detecta si el error de factura.com indica que el receptor tiene datos inválidos/desactualizados.
     * En ese caso conviene limpiar el UID en caché y recrear el cliente.
     */
    private function esErrorReceptorInvalido(string $mensaje): bool
    {
        $m = strtolower($mensaje);

        return str_contains($m, 'receptor') && (
            str_contains($m, 'catálogo')
            || str_contains($m, 'catalogo')
            || str_contains($m, 'no se encuentra')
            || str_contains($m, 'not found')
            || str_contains($m, 'código postal')
            || str_contains($m, 'codigo postal')
            || str_contains($m, 'domicilio fiscal')
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /cfdis/{cfdi}/cancelar
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cancela un CFDI ante el SAT vía factura.com.
     */
    public function cancelar(Request $request, int $cfdiId, FacturaComService $factura)
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
                redirectRoute: 'pagos.show',
                jsonData: ['ok' => true],
                mensaje: "CFDI {$cfdi->folio} cancelado correctamente.",
                routeParams: [$cfdi->pago_id]
            );
        } catch (\Throwable $e) {
            return $this->respuestaError('Error al cancelar CFDI: '.$e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /cfdis/{cfdi}/descargar/{formato}
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Descarga el PDF o XML de un CFDI desde factura.com y lo envía al navegador.
     */
    public function descargar(int $cfdiId, string $formato, FacturaComService $factura)
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
            return back()->with('error', 'Error al descargar: '.$e->getMessage());
        }

        $nombre   = ($cfdi->folio ?? $cfdi->uuid_sat ?? "CFDI-{$cfdiId}") . ".{$formato}";
        $mimeType = $formato === 'pdf' ? 'application/pdf' : 'application/xml';

        return response($contenido, 200, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$nombre}\"",
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────────────

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
            $cp = config('factura.cp_expedicion');
            if (! $cp) {
                throw new \RuntimeException(
                    'Configure FACTURA_CP_EXPEDICION en .env con el código postal del lugar de expedición.'
                );
            }

            $email = config('factura.email_contacto')
                ?: throw new \RuntimeException(
                    'Configure FACTURA_EMAIL_CONTACTO en .env para emitir a Público en General.'
                );

            $uid = $factura->crearCliente(
                'XAXX010101000',
                'PUBLICO EN GENERAL',
                $cp,
                '616',
                $email,
            );

            $config->update(['publico_general_uid' => $uid]);
            $config->publico_general_uid = $uid;
        }

        return [
            'UID'            => $config->publico_general_uid,
            'RegimenFiscalR' => '616',
        ];
    }

    /**
     * Construye el payload JSON para la API de factura.com (CFDI 4.0).
     */
    private function construirPayload(
        Pago $pago,
        ConfigFiscal $config,
        array $receptor,
        string $folio,
        string $usoCfdi,
        bool $esPublicoGeneral = false
    ): array {
        $conceptos = $pago->detalles->map(function ($detalle) {
            $concepto = $detalle->cargo?->concepto;
            $alumno   = $detalle->cargo?->inscripcion?->alumno;

            // Usa la etiqueta del cargo: "Colegiatura Agosto 2026" (o solo el nombre si es pago único)
            $descripcion = $detalle->cargo?->etiqueta ?? 'Servicio educativo';
            if ($alumno) {
                $descripcion .= ' — '.trim("{$alumno->nombre} {$alumno->ap_paterno} {$alumno->ap_materno}");
            }

            $monto = round((float) $detalle->monto_abonado, 2);

            return [
                'ClaveProdServ' => $concepto?->clave_sat ?? self::CLAVE_PROD_SERV_DEFAULT,
                'Cantidad'      => 1,
                'ClaveUnidad'   => 'E48',          // Unidad de servicio
                'Unidad'        => 'Servicio',
                'ValorUnitario' => $monto,
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
                'Periodicidad' => '04',                          // Mensual
                'Meses'        => now()->format('m'),            // Mes actual (01–12)
                'Año'          => (string) now()->year,
            ];
        }

        return $payload;
    }
}
