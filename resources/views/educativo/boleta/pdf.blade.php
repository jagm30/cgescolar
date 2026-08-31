<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Boleta — {{ $alumno->ap_paterno }} {{ $alumno->nombre }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 20px;
        }
        * { box-sizing: border-box; }
    </style>
</head>
<body>
    @include('educativo.boleta._contenido')
</body>
</html>
