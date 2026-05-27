<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            margin: 0 auto;
            border-top: 4px solid #3c8dbc;
        }

        .data-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .label {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .value {
            color: #1e293b;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 style="color: #2c3e50; margin-top: 0;">¡Hola, {{ $datosUsuario['nombre'] }}!</h2>
        <p style="color: #475569; line-height: 1.5;">
            Se ha generado o actualizado tu acceso al <b>Portal Escolar</b>. A continuación, te proporcionamos tus datos
            de ingreso de forma segura.
        </p>

        <div class="data-box">
            <div class="label">Tu Rol / Nivel de Acceso</div>
            <div class="value" style="text-transform: capitalize;">{{ $datosUsuario['rol'] }}</div>

            <div class="label">Correo Electrónico (Usuario)</div>
            <div class="value">{{ $datosUsuario['email'] }}</div>

            <div class="label">Contraseña</div>
            <div class="value" style="color: #c0392b; font-family: monospace; font-size: 18px;">
                {{ $datosUsuario['password'] }}</div>
        </div>

        <p style="color: #475569; font-size: 14px;">Te recomendamos guardar esta información en un lugar seguro.</p>

        <div class="footer">
            Atentamente,<br>
            <b>Administración Escolar</b>
        </div>
    </div>
</body>

</html>
