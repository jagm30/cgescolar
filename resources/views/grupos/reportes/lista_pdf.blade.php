<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';
        $totalAlumnos  = $grupo->inscripciones->count();
    @endphp

    <title>Lista de Asistencia — {{ $grupo->nombre }}</title>

    <style>
        @page { margin: 8mm 10mm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado institucional ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 6px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 64px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub {
            color: #777;
            font-size: 10px;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .report-title { text-align: right; }
        .report-title-main {
            color: #1e4d7b;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title-sub { color: #666; font-size: 10px; margin-top: 3px; }

        /* ── Caja de datos del grupo ── */
        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #dde4eb;
        }
        .info-box td {
            padding: 7px 14px;
            border-right: 1px solid #dde4eb;
            vertical-align: middle;
        }
        .info-box td:last-child { border-right: none; }
        .info-lbl {
            font-size: 9px;
            font-weight: bold;
            color: #8a9ab0;
            text-transform: uppercase;
            letter-spacing: .05em;
            display: block;
            margin-bottom: 2px;
        }
        .info-val {
            font-size: 12px;
            font-weight: bold;
            color: #1a2634;
        }
        .info-badge {
            display: inline-block;
            background: #e8f0fb;
            color: #2e6da4;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 4px;
            border: 1px solid #b3d0f0;
        }
        .info-count {
            display: inline-block;
            background: #fdecea;
            color: #b91c1c;
            font-size: 14px;
            font-weight: bold;
            padding: 1px 10px;
            border-radius: 4px;
            border: 1px solid #fca5a5;
        }

        /* ── Tabla principal de alumnos ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .main-table thead th {
            background: #1e4d7b;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #1a4570;
            letter-spacing: .04em;
        }
        .main-table tbody td {
            padding: 5px 8px;
            border: 1px solid #e0e6ed;
            vertical-align: middle;
            font-size: 11px;
        }
        .main-table tbody tr:nth-child(even) td {
            background: #f9fafb;
        }

        /* Columnas de asistencia */
        .check-th {
            text-align: center;
            width: 28px;
            background: #2e6da4;
        }
        .check-td {
            text-align: center;
            width: 28px;
        }

        /* Número de fila */
        .num-td {
            text-align: center;
            width: 24px;
            color: #94a3b8;
            font-size: 10px;
        }

        /* ── Área de firmas ── */
        .firma-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28px;
        }
        .firma-table td {
            width: 33%;
            text-align: center;
            font-size: 10px;
            color: #555;
            padding-top: 14px;
            border-top: 1px solid #999;
        }
        .firma-table .firma-lbl {
            font-size: 9px;
            color: #8a9ab0;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* ── Pie ── */
        .pie {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
            font-size: 9px;
            color: #bbb;
        }

        .text-center { text-align: center; }
    </style>
</head>
<body>

    {{-- ── Encabezado institucional ── --}}
    <table class="header">
        <tr>
            <td style="width:14%;">
                @if (file_exists(public_path('imgs_escuela/reportes/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/reportes/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @else
                    <div style="width:64px;height:64px;background:#e0e0e0;text-align:center;line-height:64px;color:#888;font-size:9px;">LOGO</div>
                @endif
            </td>
            <td style="width:50%; padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Lista de Asistencia</div>
            </td>
            <td class="report-title" style="width:36%;">
                <div class="report-title-main">Lista de Asistencia</div>
                <div class="report-title-sub">
                    Ciclo: {{ $grupo->ciclo->nombre }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Datos del grupo ── --}}
    <table class="info-box">
        <tr>
            <td style="width:22%;">
                <span class="info-lbl">Nivel</span>
                <span class="info-val">{{ $grupo->grado->nivel->nombre }}</span>
            </td>
            <td style="width:22%;">
                <span class="info-lbl">Grado y Grupo</span>
                <span class="info-badge">{{ $grupo->grado->nombre }} {{ $grupo->nombre }}</span>
            </td>
            <td style="width:30%;">
                <span class="info-lbl">Docente</span>
                <span class="info-val">{{ $grupo->docente ?? 'Sin docente asignado' }}</span>
            </td>
            <td style="width:14%;">
                <span class="info-lbl">Ciclo escolar</span>
                <span class="info-val">{{ $grupo->ciclo->nombre }}</span>
            </td>
            <td style="width:12%; text-align:center;">
                <span class="info-lbl">Total alumnos</span>
                <span class="info-count">{{ $totalAlumnos }}</span>
            </td>
        </tr>
    </table>

    {{-- ── Tabla de alumnos ── --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width:24px; text-align:center;">#</th>
                <th style="width:75px;">Matrícula</th>
                <th style="width:auto;">Nombre del alumno (Ap. Paterno / Ap. Materno / Nombre(s))</th>
                <th class="check-th">1</th>
                <th class="check-th">2</th>
                <th class="check-th">3</th>
                <th class="check-th">4</th>
                <th class="check-th">5</th>
                <th class="check-th">6</th>
                <th class="check-th">7</th>
                <th class="check-th">8</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grupo->inscripciones->sortBy(fn($i) => $i->alumno->ap_paterno) as $index => $ins)
            <tr>
                <td class="num-td">{{ $index + 1 }}</td>
                <td style="text-align:center;font-family:monospace;font-size:10px;color:#4a5568;">
                    {{ $ins->alumno->matricula ?? '—' }}
                </td>
                <td style="font-weight:bold;text-transform:uppercase;">
                    {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }},
                    {{ $ins->alumno->nombre }}
                </td>
                <td class="check-td"></td>
                <td class="check-td"></td>
                <td class="check-td"></td>
                <td class="check-td"></td>
                <td class="check-td"></td>
                <td class="check-td"></td>
                <td class="check-td"></td>
                <td class="check-td"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Firmas ── --}}
    <table class="firma-table">
        <tr>
            <td>
                <span class="firma-lbl">Elaboró</span>
            </td>
            <td>
                <span class="firma-lbl">Docente responsable</span>
            </td>
            <td>
                <span class="firma-lbl">Vo. Bo. Dirección</span>
            </td>
        </tr>
    </table>

    {{-- ── Pie de página ── --}}
    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }} &mdash; Este documento no tiene validez oficial fuera de la institución.
    </div>

</body>
</html>
