<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Credenciales de Acceso</title>
    <style>
        /* Tamaño media carta (half-letter) */
        @page {
            size: 5.5in 8.5in portrait;
            margin: 25px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
        }

        .card {
            border: 2px solid #3c8dbc;
            border-radius: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
            overflow: hidden;
        }

        .card-header {
            background-color: #3c8dbc;
            color: #ffffff;
            text-align: center;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .card-body {
            padding: 25px;
            background-color: #fcfcfc;
        }

        .data-row {
            margin-bottom: 15px;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 10px;
        }

        .data-label {
            font-size: 12px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: block;
        }

        .data-value {
            font-size: 16px;
            font-weight: bold;
            color: #222;
        }

        .password-box {
            background-color: #eaf3fb;
            border: 1px dashed #3c8dbc;
            padding: 8px 12px;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: #c0392b;
            display: inline-block;
        }

        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    @foreach ($credenciales as $cred)
        <div class="card">
            <div class="card-header">
                Portal Escolar - Datos de Acceso
            </div>
            <div class="card-body">
                <div class="data-row">
                    <span class="data-label">Nombre del Usuario</span>
                    <span class="data-value">{{ $cred['nombre'] }}</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Nivel de Acceso (Rol)</span>
                    <span class="data-value"
                        style="text-transform: capitalize;">{{ $cred['rol'] ?? 'Padre de Familia' }}</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Correo Electrónico (Usuario)</span>
                    <span class="data-value">{{ $cred['email'] }}</span>
                </div>

                <div class="data-row" style="border: none;">
                    <span class="data-label">Contraseña Asignada</span>
                    <span class="password-box">{{ $cred['password'] }}</span>
                </div>
            </div>
        </div>
        <div class="footer-note">
            Por favor, guarde este documento en un lugar seguro. No comparta su contraseña.
        </div>
    @endforeach
</body>

</html>
