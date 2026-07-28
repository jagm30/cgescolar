<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cumpleañeros de {{ $nombreMes }}</title>

    <style>
        @page { margin: 10mm 12mm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 6px;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 90px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub { color: #777; font-size: 10px; margin-top: 2px; }
        .report-title {
            color: #1e4d7b;
            font-size: 13px;
            font-weight: bold;
            text-align: right;
        }
        .report-meta { color: #555; font-size: 10px; text-align: right; }

        /* ── Tabla ── */
        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .tabla th {
            background: #1e4d7b;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #16396b;
            text-align: left;
        }
        .tabla td {
            padding: 5px 8px;
            border: 1px solid #e0e7ef;
            font-size: 11px;
            vertical-align: middle;
        }
        .tabla tr:nth-child(even) td { background: #f7fafd; }

        .td-dia {
            text-align: center;
            width: 32px;
            font-size: 18px;
            font-weight: bold;
            color: #1e4d7b;
        }
        .td-edad  { text-align: center; width: 50px; }
        .td-grupo { width: 120px; }
        .td-nivel { width: 100px; color: #555; font-size: 10px; }

        /* ── Pastel de cumpleaños en la columna día ── */
        .cake { font-size: 14px; display: block; }

        /* ── Subtítulo de filtros ── */
        .filtros-bar {
            background: #eaf3fb;
            border: 1px solid #d6eaf8;
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 10px;
            color: #2980b9;
            margin-bottom: 10px;
        }

        /* ── Foto ── */
        .td-foto { width: 48px; text-align: center; vertical-align: middle; padding: 3px; }
        .foto-alumno {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d0dce8;
            display: block;
            margin: 0 auto;
        }
        .foto-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #eaf3fb;
            border: 2px solid #d0dce8;
            display: block;
            margin: 0 auto;
            line-height: 42px;
            text-align: center;
            font-size: 16px;
            color: #aac4e0;
        }

        /* ── Totalizador ── */
        .total-row td {
            background: #1e4d7b;
            color: #fff;
            font-weight: bold;
            padding: 5px 8px;
        }

        /* ── Vacío ── */
        .vacio {
            text-align: center;
            padding: 30px;
            color: #aaa;
            font-size: 12px;
        }

        /* ── Pie ── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #d0dce8;
            padding-top: 5px;
            color: #999;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Encabezado institucional ── --}}
    <table class="header">
        <tr>
            @if ($base64)
                <td style="width:100px;">
                    <img src="{{ $base64 }}" class="school-logo" alt="Logo">
                </td>
            @endif
            <td style="padding-left:10px;">
                <div class="school-name">{{ $setting->nombre_escuela ?? 'Escuela' }}</div>
                <div class="school-sub">Sistema de Gestión Escolar</div>
            </td>
            <td style="text-align:right;">
                <div class="report-title">Lista de Cumpleañeros — {{ $nombreMes }}</div>
                <div class="report-meta">Ciclo escolar: {{ $ciclo->nombre }}</div>
                <div class="report-meta">Fecha de emisión: {{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Barra de filtros activos ── --}}
    @php
        $etiquetasFiltros = [];
        if (!empty($filtros['estado']))    $etiquetasFiltros[] = 'Estado: ' . $filtros['estado'];
        if (!empty($filtros['nivel_id']))  $etiquetasFiltros[] = 'Nivel ID: ' . $filtros['nivel_id'];
        if (!empty($filtros['grupo_id']))  $etiquetasFiltros[] = 'Grupo ID: ' . $filtros['grupo_id'];
        if (!empty($filtros['buscar']))    $etiquetasFiltros[] = 'Búsqueda: "' . $filtros['buscar'] . '"';
    @endphp
    @if (count($etiquetasFiltros))
        <div class="filtros-bar">
            Filtros aplicados: {{ implode(' &nbsp;|&nbsp; ', $etiquetasFiltros) }}
        </div>
    @endif

    {{-- ── Tabla de cumpleañeros ── --}}
    @if ($alumnos->isEmpty())
        <div class="vacio">No hay alumnos con cumpleaños en {{ $nombreMes }} con los filtros seleccionados.</div>
    @else
        <table class="tabla">
            <thead>
                <tr>
                    <th class="td-foto"></th>
                    <th style="width:110px;">Cumpleaños</th>
                    <th>Nombre</th>
                    <th class="td-grupo">Grupo</th>
                    <th class="td-nivel">Nivel</th>
                    <th class="td-edad">Años que cumple</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($alumnos as $alumno)
                    @php
                        $inscripcion  = $alumno->inscripciones->first();
                        $grupo        = $inscripcion?->grupo;
                        $nivel        = $grupo?->grado?->nivel;
                        $foto         = $fotosPorAlumno[$alumno->id] ?? null;
                        $fnac         = $alumno->fecha_nacimiento;
                        $fechaCumple  = $fnac ? $fnac->day . ' de ' . ucfirst($fnac->translatedFormat('F')) : '—';
                        $edad         = $fnac ? (now()->year - $fnac->year) : null;
                    @endphp
                    <tr>
                        <td class="td-foto">
                            @if ($foto)
                                <img src="{{ $foto }}" class="foto-alumno" alt="">
                            @else
                                <span class="foto-placeholder">&#128100;</span>
                            @endif
                        </td>
                        <td style="width:110px;font-weight:600;color:#1e4d7b;">{{ $fechaCumple }}</td>
                        <td>
                            <strong>{{ trim("{$alumno->ap_paterno} {$alumno->ap_materno}") }}</strong>,
                            {{ $alumno->nombre }}
                        </td>
                        <td class="td-grupo">
                            {{ $grupo ? $grupo->grado->numero . '° ' . $grupo->nombre : '—' }}
                        </td>
                        <td class="td-nivel">{{ $nivel?->nombre ?? '—' }}</td>
                        <td class="td-edad">{{ $edad !== null ? $edad . ' años' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;">Total cumpleañeros en {{ $nombreMes }}:</td>
                    <td style="text-align:center;">{{ $alumnos->count() }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        Reporte generado el {{ now()->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') }} &nbsp;|&nbsp; {{ $setting->nombre_escuela ?? '' }}
    </div>

</body>
</html>
