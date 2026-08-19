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
    | Sandbox:    https://sandbox.factura.com/api   ← incluir /api en sandbox
    | Producción: https://api.factura.com           ← sin /api (ya está en el hostname)
    |
    | Los endpoints del servicio usan rutas como /v1/series, /v4/cfdi40/create, etc.
    | El segmento /api está embebido en la URL base de sandbox pero NO en producción.
    |
    */

    'url'        => env('FACTURA_URL', 'https://api.factura.com'),
    'api_key'    => env('FACTURA_API_KEY'),
    'secret_key' => env('FACTURA_SECRET_KEY'),
    'plugin'     => env('FACTURA_PLUGIN'),

    /*
    | Código postal del lugar de expedición de la institución educativa.
    | Requerido por el SAT para CFDI 4.0.
    */
    'cp_expedicion' => env('FACTURA_CP_EXPEDICION'),

    /*
    | Email de contacto usado al registrar clientes sin email capturado.
    | Requerido por factura.com para crear el receptor en su sistema.
    */
    'email_contacto' => env('FACTURA_EMAIL_CONTACTO'),

];
