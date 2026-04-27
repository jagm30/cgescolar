<?php

return [

    /*
    |--------------------------------------------------------------------------
    | factura.com — Configuración de API
    |--------------------------------------------------------------------------
    |
    | Credenciales y parámetros para la integración con factura.com (CFDI 4.0).
    | Obtén tu API Key y Secret Key en:
    |   Configuraciones → API → Datos de acceso
    |
    | Sandbox:    https://sandbox.factura.com
    | Producción: https://api.factura.com
    |
    */

    'url'        => env('FACTURA_URL', 'https://sandbox.factura.com'),
    'api_key'    => env('FACTURA_API_KEY', ''),
    'secret_key' => env('FACTURA_SECRET_KEY', ''),
    'plugin'     => env('FACTURA_PLUGIN', '9d4a2b33-13c3-4a2f-8d0d-e1c7d2a6f534'),

    /*
    | Código postal del lugar de expedición de la institución educativa.
    | Requerido por el SAT para CFDI 4.0.
    */
    'cp_expedicion' => env('FACTURA_CP_EXPEDICION', ''),

];
