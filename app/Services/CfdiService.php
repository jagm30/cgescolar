<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Cfdi;
use App\Models\ConfigFiscal;
use App\Models\Pago;
use App\Models\RazonSocialContacto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CfdiService
{
    private const FORMAS_PAGO_SAT = [
        'efectivo' => '01',
        'cheque' => '02',
        'transferencia' => '03',
        'deposito' => '03',
        'tarjeta_credito' => '04',
        'tarjeta_debito' => '28',
    ];

    private const CLAVE_PROD_SERV_DEFAULT = '86101500';

    public function __construct(private FacturaComService $factura) {}

    /**
     * Emite un CFDI 4.0 individual para el pago indicado.
     * Limpia la caché del receptor si el error proviene de datos inválidos en factura.com,
     * de modo que el siguiente intento lo recree automáticamente.
     *
     * @return array{cfdi: Cfdi, folio: string}
     *
     * @throws \RuntimeException
     */
    public function emitirParaPago(
        Pago $pago,
        ?int $razonSocialId,
        string $usoCfdi,
        Carbon $fechaEmision,
    ): array {
        $config = ConfigFiscal::first()
            ?? throw new \RuntimeException('No hay configuración fiscal registrada. Configure el emisor primero.');

        if (! $config->serie_id) {
            throw new \RuntimeException(
                'Falta el ID de serie de factura.com. Ve a Configuración → Datos del Emisor e ingresa el ID numérico de la serie.'
            );
        }

        $pago->loadMissing([
            'detalles.cargo.concepto',
            'detalles.cargo.inscripcion.alumno',
            'detalles.cargo.inscripcion.ciclo',
            'detalles.cargo.inscripcion.grupo.grado.nivel',
        ]);

        $rs = $razonSocialId ? RazonSocialContacto::with('contacto')->findOrFail($razonSocialId) : null;
        $receptor = $rs
            ? $this->receptorDesdeRazonSocial($rs)
            : $this->receptorPublicoGeneral($config);

        try {
            return DB::transaction(function () use ($pago, $config, $receptor, $razonSocialId, $usoCfdi, $fechaEmision): array {
                $folio = $config->siguienteFolio();
                $payload = $this->construirPayload($pago, $config, $receptor, $folio, $usoCfdi, $razonSocialId === null, $fechaEmision);

                $respuesta = $this->factura->emitir($payload);

                $cfdi = Cfdi::create([
                    'pago_id' => $pago->id,
                    'config_fiscal_id' => $config->id,
                    'razon_social_id' => $razonSocialId,
                    'uso_cfdi' => $usoCfdi,
                    'uuid_sat' => $respuesta['UUID'] ?? $respuesta['Uuid'] ?? null,
                    'factura_uid' => $respuesta['UID'] ?? null,
                    'folio' => $folio,
                    'fecha_timbrado' => $fechaEmision,
                    'estado' => 'vigente',
                ]);

                Auditoria::registrar('cfdi', $cfdi->id, 'insert', null, [
                    'pago_id' => $pago->id,
                    'folio' => $folio,
                    'factura_uid' => $cfdi->factura_uid,
                    'uuid_sat' => $cfdi->uuid_sat,
                ]);

                return ['cfdi' => $cfdi, 'folio' => $folio];
            });
        } catch (\Throwable $e) {
            // El Uso CFDI no es compatible con el régimen fiscal del receptor: es un error de
            // datos, no de caché — reintentar con el mismo régimen/uso fallará siempre igual.
            if ($this->esErrorUsoCfdiIncompatible($e->getMessage())) {
                throw new \RuntimeException(
                    'El Uso CFDI "'.$usoCfdi.'" no es compatible con el régimen fiscal registrado para este receptor. '.
                    'Corrige el régimen fiscal o elige otro Uso de CFDI (p. ej. G03) en los datos de facturación del contacto — reintentar no lo resolverá.'
                );
            }

            if ($this->esErrorReceptorInvalido($e->getMessage())) {
                // Limpiar UID en caché para que el siguiente intento lo recree
                $razonSocialId === null
                    ? $config->update(['publico_general_uid' => null])
                    : $rs?->update(['factura_uid' => null]);

                throw new \RuntimeException(
                    'El cliente receptor estaba desactualizado en factura.com y fue eliminado del caché. '.
                    'Vuelve a intentar emitir el CFDI — en este segundo intento se registrará automáticamente.'
                );
            }

            throw new \RuntimeException('Error al emitir CFDI: '.$e->getMessage(), 0, $e);
        }
    }

    /** @return array{UID: string, RegimenFiscalR: string} */
    public function receptorDesdeRazonSocial(RazonSocialContacto $rs): array
    {
        if (! $rs->factura_uid) {
            $email = $rs->contacto?->email
                ?? config('factura.email_contacto')
                ?: throw new \RuntimeException(
                    'El contacto no tiene email y FACTURA_EMAIL_CONTACTO no está configurado en .env.'
                );

            $uid = $this->factura->crearCliente(
                $rs->rfc, $rs->razon_social, $rs->domicilio_fiscal, $rs->regimen_fiscal, $email
            );

            $rs->update(['factura_uid' => $uid]);
            $rs->factura_uid = $uid;
        }

        return ['UID' => $rs->factura_uid, 'RegimenFiscalR' => $rs->regimen_fiscal];
    }

    /** @return array{UID: string, RegimenFiscalR: string} */
    public function receptorPublicoGeneral(ConfigFiscal $config): array
    {
        if (! $config->publico_general_uid) {
            $cp = config('factura.cp_expedicion')
                ?: throw new \RuntimeException('Configure FACTURA_CP_EXPEDICION en .env con el CP del lugar de expedición.');

            $email = config('factura.email_contacto')
                ?: throw new \RuntimeException('Configure FACTURA_EMAIL_CONTACTO en .env para emitir a Público en General.');

            $uid = $this->factura->crearCliente('XAXX010101000', 'PUBLICO EN GENERAL', $cp, '616', $email);

            $config->update(['publico_general_uid' => $uid]);
            $config->publico_general_uid = $uid;
        }

        return ['UID' => $config->publico_general_uid, 'RegimenFiscalR' => '616'];
    }

    /**
     * Detecta si el error de factura.com indica que el Uso CFDI es incompatible con el
     * régimen fiscal del receptor (CFDI40161/CFDI40154). Es un error de datos permanente:
     * recrear el cliente en factura.com con el mismo régimen/uso fallará de nuevo.
     */
    public function esErrorUsoCfdiIncompatible(string $mensaje): bool
    {
        $m = strtolower($mensaje);

        return str_contains($m, 'cfdi40161') || str_contains($m, 'cfdi40154') || str_contains($m, 'usocfdi');
    }

    public function esErrorReceptorInvalido(string $mensaje): bool
    {
        $m = strtolower($mensaje);

        // CFDI40145: el "Nombre" (razón social) registrado en factura.com para este RFC
        // no coincide con el que valida el SAT — hay que recrear el cliente con el nombre correcto.
        if (str_contains($m, 'cfdi40145') || str_contains($m, 'nombre del receptor')) {
            return true;
        }

        return str_contains($m, 'receptor') && (
            str_contains($m, 'catálogo') ||
            str_contains($m, 'catalogo') ||
            str_contains($m, 'no se encuentra') ||
            str_contains($m, 'not found') ||
            str_contains($m, 'código postal') ||
            str_contains($m, 'codigo postal') ||
            str_contains($m, 'domicilio fiscal')
        );
    }

    private function construirPayload(
        Pago $pago,
        ConfigFiscal $config,
        array $receptor,
        string $folio,
        string $usoCfdi,
        bool $esPublicoGeneral,
        Carbon $fechaEmision,
    ): array {
        $conceptos = $pago->detalles->map(function ($detalle) {
            $alumno = $detalle->cargo?->inscripcion?->alumno;
            $ciclo = $detalle->cargo?->inscripcion?->ciclo;
            $nivel = $detalle->cargo?->inscripcion?->grupo?->grado?->nivel;
            $descripcion = $detalle->cargo?->etiqueta ?? 'Servicio educativo';

            if ($ciclo) {
                $descripcion .= ' — Ciclo Escolar: '.$ciclo->nombre;
            }

            if ($alumno) {
                $descripcion .= ' — '.trim("{$alumno->nombre} {$alumno->ap_paterno} {$alumno->ap_materno}");
            }

            if ($alumno?->curp) {
                $descripcion .= ' — CURP: '.$alumno->curp;
            }

            if ($nivel) {
                $descripcion .= ' — '.$nivel->nombre;
            }

            if ($nivel?->revoe) {
                $descripcion .= ' — RVOE: '.$nivel->revoe;
            }

            return [
                'ClaveProdServ' => $detalle->cargo?->concepto?->clave_sat ?? self::CLAVE_PROD_SERV_DEFAULT,
                'Cantidad' => 1,
                'ClaveUnidad' => 'E48',
                'Unidad' => 'Servicio',
                'ValorUnitario' => round((float) $detalle->monto_final, 2),
                'Descripcion' => mb_substr($descripcion, 0, 1000),
                'Impuestos' => ['Traslados' => [], 'Retenidos' => []],
            ];
        })->values()->toArray();

        $payload = [
            'TipoDocumento' => 'factura',
            'Serie' => $config->serie_id ?? $config->serie,
            'Folio' => (string) $config->folio_actual,
            'Fecha' => $fechaEmision->format('Y-m-d\TH:i:s'),
            'UsoCFDI' => $usoCfdi,
            'FormaPago' => self::FORMAS_PAGO_SAT[$pago->forma_pago] ?? '99',
            'MetodoPago' => 'PUE',
            'Moneda' => 'MXN',
            'LugarExpedicion' => config('factura.cp_expedicion'),
            'Receptor' => $receptor,
            'Conceptos' => $conceptos,
            'EnviarCorreo' => false,
            'Draft' => false,
        ];

        if ($esPublicoGeneral) {
            $payload['InformacionGlobal'] = [
                'Periodicidad' => '04',
                'Meses' => $fechaEmision->format('m'),
                'Año' => (string) $fechaEmision->year,
            ];
        }

        return $payload;
    }
}
