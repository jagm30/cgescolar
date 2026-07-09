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
            background-color: #e74c3c;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
        }

        .data-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #c0392b;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            Aviso de modificación de perfil (Seguridad)
        </div>
        <p>Hola Administrador,</p>
        <p>Se ha modificado información sensible (rol o contraseña) de un usuario existente en el sistema.</p>

        <div class="data-box">
            <p><strong>Usuario afectado:</strong> {{ $nombreUsuario }} ({{ $emailUsuario }})</p>
            <p><strong>Rol tras el cambio:</strong> <span style="text-transform: capitalize;">{{ $rolActual }}</span>
            </p>
            <p><strong>Cambios detectados:</strong> <strong style="color: #e74c3c;">{{ $cambiosRealizados }}</strong></p>
            <p><strong>Modificado por:</strong> {{ $quienModifico }}</p>
        </div>

        <p><em>Este es un aviso automático de auditoría. Si desconoces este cambio o parece sospechoso, revisa la
                bitácora del sistema inmediatamente.</em></p>
    </div>
</body>

</html>
