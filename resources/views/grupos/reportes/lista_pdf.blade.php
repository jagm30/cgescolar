<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    {{-- Obtenemos la configuración de la escuela al inicio --}}
    @php
        $escuelaInfo = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta = $escuelaInfo->logo_ruta ?? 'logo-escuela.png';
    @endphp

    <title>Lista de Asistencia - {{ $grupo->nombre }}</title>
    <style>
        /* Estilos seguros para DomPDF: Sin Flexbox, sin sombras complejas */
        @page {
            margin: 1.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Encabezado: Usamos una tabla para posicionar el logo y el texto */
        .header-table {
            width: 100%;
            background-color: #f7f9fc;
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 15px;
        }

        .school-logo {
            width: 90px;
            height: auto;
            display: block;
        }

        .school-name {
            font-size: 17px;
            font-weight: bold;
            margin: 0;
            color: #1a2634;
        }

        .report-title {
            font-size: 12px;
            margin-top: 5px;
            color: #5a6a7a;
            font-weight: bold;
        }

        .print-date {
            font-size: 10px;
            color: #9aa;
            text-align: right;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
        }

        .label {
            color: #8a9ab0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .value {
            color: #1a2634;
            font-weight: bold;
            font-size: 11px;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .main-table th {
            background-color: #f4f6f8;
            color: #6b7a8d;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px;
            border: 1px solid #e0e6ed;
            text-align: left;
        }

        .main-table td {
            padding: 7px 10px;
            border: 1px solid #e0e6ed;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .check-col {
            width: 30px;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #95a5a6;
        }
    </style>
</head>

<body>
    {{-- ENCABEZADO ACTUALIZADO CON DATOS DINÁMICOS --}}
    <table class="header-table">
        <tr>
            <td width="100">
                {{-- Ahora busca el logo definido en la base de datos --}}
                @if (file_exists(public_path('imgs_escuela/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @elseif (file_exists(public_path('imgs_escuela/reportes/logo-escuela.png')))
                    {{-- Fallback por si no ha subido logo nuevo pero existe el anterior --}}
                    <img src="{{ public_path('imgs_escuela/reportes/logo-escuela.png') }}" class="school-logo"
                        alt="Logo">
                @endif
            </td>
            <td>
                {{-- Nombre dinámico de la escuela --}}
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="report-title">LISTA DE ASISTENCIA - CICLO {{ $grupo->ciclo->nombre }}</div>
            </td>
            <td class="print-date">
                {{ date('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <div class="content">
        <table class="info-table">
            <tr>
                <td width="33%">
                    <span class="label">Nivel</span><br>
                    <span class="value">{{ $grupo->grado->nivel->nombre }}</span>
                </td>
                <td width="33%" class="text-center">
                    <span class="label">Grado y Grupo</span><br>
                    <span class="value"
                        style="background:#e8f0fb; color:#3c8dbc; padding: 2px 8px; border-radius:4px;">
                        {{ $grupo->grado->nombre }} {{ $grupo->nombre }}
                    </span>
                </td>
                <td width="33%" style="text-align: right;">
                    <span class="label">Ciclo Escolar</span><br>
                    <span class="value">{{ $grupo->ciclo->nombre }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Docente</span><br>
                    <span class="value">{{ $grupo->docente ?? 'SIN DOCENTE ASIGNADO' }}</span>
                </td>
                <td style="text-align: right;">
                    <span class="label">Total Alumnos Inscritos</span><br>
                    <span class="value">{{ $grupo->inscripciones->count() }}</span>
                </td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr>
                    <th width="20">#</th>
                    <th width="80">Matrícula</th>
                    <th>Nombre del Alumno (A. Paterno / A. Materno / Nombres)</th>
                    <th class="check-col">1</th>
                    <th class="check-col">2</th>
                    <th class="check-col">3</th>
                    <th class="check-col">4</th>
                    <th class="check-col">5</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grupo->inscripciones as $index => $ins)
                    <tr>
                        <td class="text-center" style="background-color: #fdfdfd; font-weight: bold;">
                            {{ $index + 1 }}</td>
                        <td class="text-center" style="font-family: monospace;">{{ $ins->alumno->matricula }}</td>
                        <td>
                            <span style="font-weight: bold; text-transform: uppercase;">
                                {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }}
                                {{ $ins->alumno->nombre }}
                            </span>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generado automáticamente por el Sistema de CGESCOLAR - {{ date('Y') }}
    </div>
</body>

</html>
