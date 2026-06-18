<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';
    @endphp

    <title>Adeudos Detallados</title>

    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 8mm 10mm;
        }

        /* ── Encabezado institucional ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 64px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b; font-size: 17px; font-weight: bold;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .school-sub { color: #777; font-size: 10px; margin-top: 2px; text-transform: uppercase; }
        .report-title { text-align: right; }
        .report-title-main { color: #1e4d7b; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .report-title-sub  { color: #666; font-size: 10px; margin-top: 3px; }

        /* ── Caja de resumen global ── */
        .resumen-box {
            width: 100%; border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #e0dde8;
        }
        .resumen-box td {
            padding: 8px 16px; text-align: center;
            border-right: 1px solid #e0dde8;
        }
        .resumen-box td:last-child { border-right: none; }
        .resumen-num { font-size: 18px; font-weight: bold; line-height: 1; }
        .resumen-lbl { font-size: 9px; color: #666; text-transform: uppercase; margin-top: 3px; }

        /* ── Bloque por alumno ── */
        .alumno-bloque {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .alumno-header {
            background: #1e4d7b;
            color: #fff;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
        }
        .alumno-header-right {
            float: right;
            font-size: 12px;
        }

        /* ── Tabla de cargos del alumno ── */
        table.cargos-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.cargos-table th {
            background: #f0f4f9;
            color: #1e4d7b;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #d0dde8;
        }
        table.cargos-table td {
            padding: 4px 8px;
            border: 1px solid #e4eaf0;
            font-size: 11px;
            vertical-align: middle;
        }
        table.cargos-table tr:nth-child(even) td { background: #f9fafb; }
        table.cargos-table tfoot td {
            background: #f0f4f9;
            font-weight: bold;
            padding: 4px 8px;
            border: 1px solid #d0dde8;
        }

        /* ── Badges de estado ── */
        .est-pendiente { color: #b45309; font-weight: bold; }
        .est-vencido   { color: #b91c1c; font-weight: bold; }
        .est-parcial   { color: #475569; font-weight: bold; }

        /* ── Pie ── */
        .pie {
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 6px;
            margin-top: 16px;
            font-size: 9px;
            color: #bbb;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-red    { color: #b91c1c; }
    </style>
</head>
<body>

    {{-- ── Encabezado institucional ── --}}
    <table class="header">
        <tr>
            <td style="width:15%;">
                @if (file_exists(public_path('imgs_escuela/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @elseif (file_exists(public_path('imgs_escuela/reportes/logo-escuela.png')))
                    <img src="{{ public_path('imgs_escuela/reportes/logo-escuela.png') }}" class="school-logo" alt="Logo">
                @else
                    <div style="width:64px;height:64px;background:#e0e0e0;text-align:center;line-height:64px;color:#888;font-size:9px;">LOGO</div>
                @endif
            </td>
            <td style="width:50%; padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Detalle de Adeudos por Alumno</div>
            </td>
            <td class="report-title" style="width:35%;">
                <div class="report-title-main">Adeudos Detallados</div>
                <div class="report-title-sub">
                    Ciclo: {{ $ciclo?->nombre ?? '—' }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Resumen global ── --}}
    <table class="resumen-box">
        <tr>
            <td>
                <div class="resumen-num text-red">{{ $resumen['total_deudores'] }}</div>
                <div class="resumen-lbl">Alumnos deudores</div>
            </td>
            <td>
                <div class="resumen-num text-red">${{ number_format($resumen['gran_total'], 2) }}</div>
                <div class="resumen-lbl">Total adeudado</div>
            </td>
        </tr>
    </table>

    {{-- ── Detalle por alumno ── --}}
    @forelse($deudores as $d)
    <div class="alumno-bloque">

        {{-- Cabecera del alumno ── --}}
        <div class="alumno-header">
            {{ $d['alumno']->ap_paterno }} {{ $d['alumno']->ap_materno }}, {{ $d['alumno']->nombre }}
            &nbsp;&mdash;&nbsp;
            Mat. {{ $d['alumno']->matricula ?? '—' }}
            @if($d['grupo'])
                &nbsp;&mdash;&nbsp;{{ $d['grupo']->nombre }}
                @if($d['nivel']) / {{ $d['nivel']->nombre }} @endif
            @endif
            <span class="alumno-header-right">
                Total: ${{ number_format($d['total_adeudo'], 2) }}
            </span>
        </div>

        {{-- Tabla de cargos ── --}}
        <table class="cargos-table">
            <thead>
                <tr>
                    <th style="width:28%; text-align:left;">Concepto</th>
                    <th style="width:16%; text-align:center;">Periodo</th>
                    <th style="width:14%; text-align:center;">Vencimiento</th>
                    <th style="width:10%; text-align:center;">Estado</th>
                    <th style="width:12%; text-align:right;">Cargo</th>
                    <th style="width:10%; text-align:right;">Abonado</th>
                    <th style="width:10%; text-align:right;">Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['cargos'] as $cargo)
                <tr>
                    <td style="font-weight:600;">{{ $cargo['concepto'] }}</td>
                    <td class="text-center">{{ $cargo['periodo_label'] }}</td>
                    <td class="text-center">{{ $cargo['fecha_vencimiento']->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @if($cargo['estado'] === 'vencido')
                            <span class="est-vencido">Vencido</span>
                        @elseif($cargo['estado'] === 'parcial')
                            <span class="est-parcial">Parcial</span>
                        @else
                            <span class="est-pendiente">Pendiente</span>
                        @endif
                    </td>
                    <td class="text-right">${{ number_format($cargo['monto_original'], 2) }}</td>
                    <td class="text-right" style="color:#27ae60;">
                        {{ $cargo['saldo_abonado'] > 0 ? '$'.number_format($cargo['saldo_abonado'], 2) : '—' }}
                    </td>
                    <td class="text-right text-red" style="font-weight:bold;">
                        ${{ number_format($cargo['saldo_pendiente'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right" style="color:#64748b;">
                        Total pendiente:
                    </td>
                    <td class="text-right text-red">
                        ${{ number_format($d['total_adeudo'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

    </div>
    @empty
        <div style="text-align:center;padding:30px;color:#aaa;">
            No hay adeudos para los filtros seleccionados.
        </div>
    @endforelse

    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }} &mdash; Este reporte no tiene validez fiscal.
    </div>

</body>
</html>
