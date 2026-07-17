<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos Inscritos — {{ $ciclo->nombre }}</title>

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

        /* ── Sección nivel ── */
        .nivel-header {
            background: #1e4d7b;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 8px;
            margin-top: 14px;
            margin-bottom: 0;
            border-radius: 3px 3px 0 0;
        }

        /* ── Tabla ── */
        .tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .tabla th {
            background: #eaf3fb;
            color: #1e4d7b;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 8px;
            border: 1px solid #d0dce8;
            text-align: left;
        }
        .tabla td {
            padding: 4px 8px;
            border: 1px solid #e0e7ef;
            font-size: 11px;
        }
        .tabla tr:nth-child(even) td { background: #f7fafd; }
        .td-num { text-align: center; width: 100px; }

        /* ── Fila subtotal nivel ── */
        .fila-subtotal td {
            background: #d6eaf8;
            color: #1e4d7b;
            font-weight: bold;
            border-top: 2px solid #aed6f1;
        }

        /* ── Gran total ── */
        .tabla-total {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tabla-total td {
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .total-label {
            color: #1e4d7b;
            text-align: right;
            width: 80%;
        }
        .total-value {
            background: #1e4d7b;
            color: #fff;
            text-align: center;
            border-radius: 3px;
            width: 20%;
        }

        /* ── Pie de página ── */
        .footer {
            margin-top: 24px;
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
                <div class="report-title">Reporte de Alumnos Inscritos</div>
                <div class="report-meta">Ciclo escolar: {{ $ciclo->nombre }}</div>
                <div class="report-meta">Fecha: {{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- ── Secciones por nivel ── --}}
    @foreach ($datos as $seccion)
        <div class="nivel-header">{{ $seccion['nivel'] }}</div>

        <table class="tabla">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th class="td-num">Alumnos inscritos</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($seccion['filas'] as $fila)
                    <tr>
                        <td>{{ $fila['grupo'] }}</td>
                        <td class="td-num">{{ $fila['total'] }}</td>
                    </tr>
                @endforeach
                <tr class="fila-subtotal">
                    <td>Total {{ $seccion['nivel'] }}</td>
                    <td class="td-num">{{ $seccion['total'] }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    {{-- ── Gran total ── --}}
    <table class="tabla-total">
        <tr>
            <td class="total-label">TOTAL GENERAL DE ALUMNOS INSCRITOS</td>
            <td class="total-value">{{ $granTotal }}</td>
        </tr>
    </table>

    <div class="footer">
        Generado el {{ now()->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') }}
        &nbsp;|&nbsp; {{ $setting->nombre_escuela ?? '' }}
    </div>

</body>
</html>
