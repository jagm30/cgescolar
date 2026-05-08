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
    'api_key'    => env('FACTURA_API_KEY', 'JDJ5JDEwJFZXY3d6S0NyRXN2Mm5hY2lEV3kybE9EZlFxeTZTa3hHVjdvM1NRNmlBYThJMVN2OFlZYjV5'),
    'secret_key' => env('FACTURA_SECRET_KEY', 'JDJ5JDEwJGFWNVpwNjhwTE9mc1JpM09Qb2V1VWVhcUR6dXlXOC5OQk1lR3NBN0VLcXk2cklnaVJHcGwu'),
    'plugin'     => env('FACTURA_PLUGIN', '9d4a2b33-13c3-4a2f-8d0d-e1c7d2a6f534'),

    /*
    | Código postal del lugar de expedición de la institución educativa.
    | Requerido por el SAT para CFDI 4.0.
    */
    'cp_expedicion' => env('FACTURA_CP_EXPEDICION', '29070'),

    /*
    | Email de contacto usado al registrar clientes sin email capturado.
    | Requerido por factura.com para crear el receptor en su sistema.
    */
    'email_contacto' => env('FACTURA_EMAIL_CONTACTO', 'josegijon30@hotmail.com'),

];
