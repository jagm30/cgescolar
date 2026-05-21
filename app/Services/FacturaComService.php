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

    // ── Helpers ──────────────────────────────────────────

    /**
     * Determina si la respuesta de factura.com es un error.
     *
     * La API usa 'status' o 'response' con valor 'error' para indicar falla.
     * Si el HTTP es 2xx y ninguno de esos campos dice 'error', se considera éxito.
     */
    private function esError(\Illuminate\Http\Client\Response $response, ?array $json): bool
    {
        if ($response->failed()) {
            return true;
        }

        $status = strtolower($json['status'] ?? $json['response'] ?? '');

        return $status === 'error';
    }

    /** Convierte el campo de error de factura.com a texto limpio (sin HTML). */
    private function extraerMensaje(mixed $raw): string
    {
        $texto = is_array($raw) ? implode(' | ', $raw) : (string) $raw;

        return trim(strip_tags($texto));
    }

    // ── Clientes ─────────────────────────────────────────

    /**
     * Registra un receptor en factura.com y devuelve su UID.
     *
     * El UID es requerido en el nodo Receptor de cada CFDI 4.0.
     *
     * @throws \RuntimeException Si la API devuelve error o no devuelve UID
     */
    public function crearCliente(
        string $rfc,
        string $razonSocial,
        string $codigoPostal,
        string $regimenFiscal,
        string $email
    ): string {
        $response = Http::withHeaders($this->headers())
            ->post($this->url('/api/v1/clients/create'), [
                'rfc'    => strtoupper($rfc),
                'razons' => strtoupper($razonSocial),
                'codpos' => $codigoPostal,
                'regimen' => $regimenFiscal,
                'email'  => $email,
                'pais'   => 'MEX',
            ]);

        $json = $response->json();

        if ($this->esError($response, $json)) {
            $raw     = $json['message'] ?? $json['error'] ?? $response->body();
            $mensaje = $this->extraerMensaje($raw);
            throw new \RuntimeException("Error al registrar cliente en factura.com: {$mensaje}");
        }

        $uid = $json['Data']['UID'] ?? $json['data']['UID'] ?? null;

        if (! $uid) {
            throw new \RuntimeException('factura.com no devolvió el UID del cliente.');
        }

        return $uid;
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

        if ($this->esError($response, $json)) {
            $raw     = $json['message'] ?? $json['error'] ?? $json['response'] ?? $response->body();
            $mensaje = $this->extraerMensaje($raw);

            throw new \RuntimeException("factura.com: {$mensaje}", $response->status());
        }

        // factura.com puede devolver UID/UUID en el root o dentro de 'data'/'Data'.
        // Normalizamos todo al root para que el controlador siempre encuentre los campos.
        $data = $json['data'] ?? $json['Data'] ?? [];
        $merged = is_array($data) ? array_merge($json, $data) : $json;

        // Normalizar UID y UUID a claves canónicas en caso de variación de mayúsculas.
        if (! isset($merged['UID']) && isset($merged['uid'])) {
            $merged['UID'] = $merged['uid'];
        }
        if (! isset($merged['UUID']) && isset($merged['uuid'])) {
            $merged['UUID'] = $merged['uuid'];
        }

        return $merged;
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

        if ($this->esError($response, $json)) {
            $raw     = $json['message'] ?? $json['error'] ?? $response->body();
            $mensaje = $this->extraerMensaje($raw);
            throw new \RuntimeException("Error al cancelar CFDI: {$mensaje}", $response->status());
        }
    }

    /**
     * Verifica la conexión con factura.com y devuelve las series disponibles.
     *
     * Llama a GET /api/v1/series para listar las series configuradas en la cuenta.
     * Útil para confirmar que las credenciales son válidas y que la serie configurada existe.
     *
     * @return array  Arreglo de series: [['id' => int, 'nombre' => string], ...]
     *
     * @throws \RuntimeException Si las credenciales son inválidas o la API falla
     */
    public function listarSeries(): array
    {
        $response = Http::withHeaders($this->headers())
            ->get($this->url('/api/v1/series'));

        $json = $response->json();

        if ($this->esError($response, $json)) {
            $raw     = $json['message'] ?? $json['error'] ?? $response->body();
            $mensaje = $this->extraerMensaje($raw);
            throw new \RuntimeException("No se pudo conectar con factura.com: {$mensaje}");
        }

        // La API devuelve un arreglo de series u objeto con clave 'data'
        $lista = $json['data'] ?? $json['Data'] ?? $json;

        if (! is_array($lista)) {
            return [];
        }

        // Normalizar a [['id' => ..., 'nombre' => ...], ...]
        // Los campos varían entre versiones de la API de factura.com — se prueban todas las variantes conocidas.
        return collect($lista)->map(fn ($s) => [
            'id'     => $s['SerieID']   ?? $s['serieID']   ?? $s['serie_id'] ?? $s['id'] ?? $s['Id'] ?? null,
            'nombre' => $s['Serie']     ?? $s['serie']      ?? $s['Name']    ?? $s['name'] ?? $s['Nombre'] ?? $s['nombre'] ?? null,
            'folio'  => $s['Folio']     ?? $s['folio']      ?? $s['FolioActual'] ?? null,
            '_raw'   => $s,  // campo de diagnóstico: se pasa al frontend para mostrar claves reales
        ])->filter(fn ($s) => $s['id'] !== null)->values()->toArray();
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
