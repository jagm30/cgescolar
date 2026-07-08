<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
        }

        .container {
            border: 1px solid #e0e7ef;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
        }

        .header {
            background-color: #f39c12;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
        }

        .data-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #3c8dbc;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            Aviso de actualización de credenciales
        </div>
        <p>Hola Administrador,</p>
        <p>El sistema ha registrado un cambio en el correo electrónico de un usuario con perfil de
            <strong>{{ $tipoPerfil }}</strong> que cuenta con acceso al sistema.
        </p>

        <div class="data-box">
            <p><strong>Usuario:</strong> {{ $nombreUsuario }}</p>
            <p><strong>Correo anterior:</strong> <del>{{ $correoAnterior ?? 'Ninguno' }}</del></p>
            <p><strong>Correo nuevo:</strong> <strong style="color: #00a65a;">{{ $correoNuevo }}</strong></p>
            <p><strong>Modificado por:</strong> {{ $quienModifico }}</p>
        </div>

        <p><em>Este es un aviso automático de auditoría de seguridad. Si el usuario requiere asistencia con sus
                credenciales, puedes gestionarlas desde tu panel de administración.</em></p>
    </div>
</body>

</html>
