<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';

        $etiquetasEstado = [
            'pendiente' => 'Pendientes',
            'vencido'   => 'Vencidos',
            'parcial'   => 'Parciales',
        ];
    @endphp

    <title>Reporte de Deudores</title>

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
            color: #1e4d7b;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub { color: #777; font-size: 10px; margin-top: 2px; text-transform: uppercase; }
        .report-title { text-align: right; }
        .report-title-main { color: #1e4d7b; font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .report-title-sub  { color: #666; font-size: 10px; margin-top: 3px; }

        /* ── Caja de resumen ── */
        .resumen-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #e0dde8;
            border-radius: 4px;
        }
        .resumen-box td {
            padding: 8px 14px;
            text-align: center;
            border-right: 1px solid #e0dde8;
        }
        .resumen-box td:last-child { border-right: none; }
        .resumen-num { font-size: 18px; font-weight: bold; line-height: 1; }
        .resumen-lbl { font-size: 9px; color: #666; text-transform: uppercase; margin-top: 3px; letter-spacing: .05em; }

        /* ── Filtros aplicados ── */
        .filtros {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
            padding: 4px 8px;
            background: #f8fafc;
            border: 1px solid #e4eaf0;
            border-radius: 3px;
        }

        /* ── Tabla de deudores ── */
        table.deu-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.deu-table thead th {
            background: #1e4d7b;
            color: #fff;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #1e4d7b;
        }
        table.deu-table tbody td {
            padding: 5px 8px;
            border: 1px solid #e4eaf0;
            font-size: 11px;
            vertical-align: middle;
        }
        table.deu-table tbody tr:nth-child(even) td { background: #f9fafb; }
        table.deu-table tfoot td {
            padding: 6px 8px;
            border: 1px solid #d0dde8;
            background: #fdf5f5;
            font-weight: bold;
            font-size: 12px;
        }

        /* ── Badges de estado ── */
        .badge-pendiente { color: #b45309; font-weight: bold; }
        .badge-vencido   { color: #b91c1c; font-weight: bold; }
        .badge-parcial   { color: #475569; font-weight: bold; }

        /* ── Firma ── */
        .firma-table {
            width: 100%;
            margin-top: 24px;
        }
        .firma-table td {
            width: 33%;
            text-align: center;
            font-size: 10px;
            color: #555;
            padding-top: 18px;
            border-top: 1px solid #aaa;
        }

        /* ── Pie ── */
        .pie {
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 6px;
            margin-top: 14px;
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
            <td style="width: 15%;">
                @if (file_exists(public_path('imgs_escuela/reportes/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/reportes/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @else
                    <div style="width:64px;height:64px;background:#e0e0e0;text-align:center;line-height:64px;color:#888;font-size:9px;">LOGO</div>
                @endif
            </td>
            <td style="width: 50%; padding-left: 10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Reporte de Adeudos</div>
            </td>
            <td class="report-title" style="width: 35%;">
                <div class="report-title-main">Reporte de Deudores</div>
                <div class="report-title-sub">
                    Ciclo: {{ $ciclo?->nombre ?? '—' }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Resumen general ── --}}
    <table class="resumen-box">
        <tr>
            <td>
                <div class="resumen-num text-red">{{ $resumen['total_deudores'] }}</div>
                <div class="resumen-lbl">Alumnos deudores</div>
            </td>
            <td>
                <div class="resumen-num" style="color:#b45309;">{{ $resumen['total_pendientes'] }}</div>
                <div class="resumen-lbl">Cargos pendientes</div>
            </td>
            <td>
                <div class="resumen-num text-red">{{ $resumen['total_vencidos'] }}</div>
                <div class="resumen-lbl">Cargos vencidos</div>
            </td>
            <td>
                <div class="resumen-num" style="color:#475569;">{{ $resumen['total_parciales'] }}</div>
                <div class="resumen-lbl">Cargos parciales</div>
            </td>
            <td>
                <div class="resumen-num text-red">${{ number_format($resumen['gran_total'], 2) }}</div>
                <div class="resumen-lbl">Total adeudado</div>
            </td>
        </tr>
    </table>

    {{-- ── Filtros aplicados ── --}}
    <div class="filtros">
        <b>Filtros aplicados:</b>
        Estados incluidos:
        @foreach($estados as $e)
            {{ $etiquetasEstado[$e] ?? $e }}{{ !$loop->last ? ',' : '' }}
        @endforeach
    </div>

    {{-- ── Tabla de deudores ── --}}
    @if($deudores->isEmpty())
        <div style="text-align:center;padding:30px;color:#aaa;font-size:13px;">
            No hay alumnos con adeudos para los filtros seleccionados.
        </div>
    @else
    <table class="deu-table">
        <thead>
            <tr>
                <th style="width:4%; text-align:center;">#</th>
                <th style="width:30%; text-align:left;">Alumno</th>
                <th style="width:13%; text-align:left;">Matrícula</th>
                <th style="width:17%; text-align:left;">Grupo / Nivel</th>
                <th style="width:8%; text-align:center;">Pend.</th>
                <th style="width:8%; text-align:center;">Venc.</th>
                <th style="width:8%; text-align:center;">Parc.</th>
                <th style="width:12%; text-align:right;">Total adeudo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deudores as $i => $d)
            <tr>
                <td class="text-center" style="color:#94a3b8;">{{ $i + 1 }}</td>
                <td style="font-weight:bold;">
                    {{ $d['alumno']->ap_paterno }} {{ $d['alumno']->ap_materno }},
                    {{ $d['alumno']->nombre }}
                </td>
                <td>{{ $d['alumno']->matricula ?? '—' }}</td>
                <td>
                    @if($d['grupo'])
                        {{ $d['grupo']->nombre }}
                        @if($d['nivel'])
                            / {{ $d['nivel']->nombre }}
                        @endif
                    @else
                        —
                    @endif
                </td>
                <td class="text-center">
                    @if($d['pendientes'] > 0)
                        <span class="badge-pendiente">{{ $d['pendientes'] }}</span>
                    @else
                        <span style="color:#ccc;">—</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($d['vencidos'] > 0)
                        <span class="badge-vencido">{{ $d['vencidos'] }}</span>
                    @else
                        <span style="color:#ccc;">—</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($d['parciales'] > 0)
                        <span class="badge-parcial">{{ $d['parciales'] }}</span>
                    @else
                        <span style="color:#ccc;">—</span>
                    @endif
                </td>
                <td class="text-right text-red" style="font-weight:bold;">
                    ${{ number_format($d['total_adeudo'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right" style="color:#64748b;">Gran total adeudado:</td>
                <td class="text-right text-red">${{ number_format($resumen['gran_total'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- ── Firma ── --}}
    <table class="firma-table">
        <tr>
            <td>Elaboró</td>
            <td>Revisó</td>
            <td>
                Fecha de corte<br>
                <span style="font-size:9px;color:#999;">{{ now()->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }} &mdash; Este reporte no tiene validez fiscal.
    </div>

</body>
</html>
