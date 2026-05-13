<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Credenciales de Acceso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }

        .titulo {
            text-align: center;
            color: #2c3e50;
            border-bottom: 2px solid #3c8dbc;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .tarjeta {
            border: 1px solid #d2d6de;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            page-break-inside: avoid;
        }

        .header-tarjeta {
            background-color: #3c8dbc;
            color: white;
            margin: -20px -20px 15px -20px;
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }

        .etiqueta {
            font-weight: bold;
            color: #64748b;
            font-size: 14px;
        }

        .dato {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .password {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2 class="titulo">Nuevas Credenciales de Acceso</h2>

    @foreach ($credenciales as $cred)
        <div class="tarjeta">
            <div class="header-tarjeta">NUEVO USUARIO: ROL PADRE</div>
            <div class="dato"><span class="etiqueta">Nombre:</span> {{ $cred['nombre'] }}</div>
            <div class="dato"><span class="etiqueta">Correo Electrónico:</span> {{ $cred['email'] }}</div>
            <div class="dato"><span class="etiqueta">Contraseña:</span> <span
                    class="password">{{ $cred['password'] }}</span></div>
        </div>
    @endforeach
</body>

</html>
