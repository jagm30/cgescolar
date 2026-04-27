<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Cliente HTTP para la API de factura.com (CFDI 4.0).
 *
 * Documentación oficial: https://factura.com/apidocs
 */
class FacturaComService
{
    // ── Autenticación ────────────────────────────────────

    private function headers(): array
    {
        return [
            'F-Api-Key'    => config('factura.api_key'),
            'F-Secret-Key' => config('factura.secret_key'),
            'F-PLUGIN'     => config('factura.plugin'),
            'Content-Type' => 'application/json',
        ];
    }

    private function url(string $path): string
    {
        return rtrim(config('factura.url'), '/') . '/' . ltrim($path, '/');
    }

    // ── Operaciones CFDI ─────────────────────────────────

    /**
     * Emite un CFDI 4.0.
     *
     * @param  array  $payload  Estructura del CFDI según la API de factura.com
     * @return array            Datos de la respuesta (contiene 'UID', etc.)
     *
     * @throws \RuntimeException Si la API devuelve error
     */
    public function emitir(array $payload): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->url('/api/v4/cfdi40/create'), $payload);

        $json = $response->json();

        if ($response->failed() || ($json['status'] ?? '') !== 'success') {
            $mensaje = $json['message']
                ?? $json['error']
                ?? $json['response']
                ?? $response->body();

            throw new \RuntimeException("factura.com: {$mensaje}", $response->status());
        }

        return $json['data'] ?? $json;
    }

    /**
     * Cancela un CFDI previamente emitido.
     *
     * Motivos SAT válidos:
     *  - 01: Comprobante emitido con errores con relación
     *  - 02: Comprobante emitido con errores sin relación
     *  - 03: No se llevó a cabo la operación
     *  - 04: Operación nominativa relacionada en la factura global
     *
     * @throws \RuntimeException Si la cancelación falla
     */
    public function cancelar(string $uid, string $motivo = '02'): void
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->url("/api/v4/cfdi40/{$uid}/cancel"), [
                'motivo' => $motivo,
            ]);

        $json = $response->json();

        if ($response->failed() || ($json['status'] ?? '') !== 'success') {
            $mensaje = $json['message'] ?? $json['error'] ?? $response->body();
            throw new \RuntimeException("Error al cancelar CFDI: {$mensaje}", $response->status());
        }
    }

    /**
     * Descarga un CFDI en formato PDF o XML.
     *
     * @param  string  $uid      UID interno de factura.com
     * @param  string  $formato  'pdf' o 'xml'
     * @return string            Contenido binario del archivo
     *
     * @throws \RuntimeException Si la descarga falla
     */
    public function descargar(string $uid, string $formato): string
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->url("/api/v4/cfdi40/{$uid}/{$formato}"));

        if ($response->failed()) {
            throw new \RuntimeException("No se pudo descargar el {$formato} del CFDI.");
        }

        // factura.com puede devolver JSON con campo 'data' en base64
        // o bien el binario directo con Content-Type correcto
        $json = $response->json();
        if (is_array($json) && isset($json['data'])) {
            return base64_decode($json['data']);
        }

        return $response->body();
    }
}
