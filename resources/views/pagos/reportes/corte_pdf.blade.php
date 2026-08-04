<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';

        $formaLabels = [
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta'       => 'Tarjeta',
            'cheque'        => 'Cheque',
        ];
    @endphp

    <title>Corte del día {{ $fecha }}</title>

    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 8mm 10mm;
        }

        /* ── Encabezado ── */
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
        .report-title-main { color: #1e4d7b; font-size: 16px; font-weight: bold; }
        .report-title-fecha { color: #555; font-size: 11px; margin-top: 3px; }

        /* ── Resumen general ── */
        .resumen-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f0f4f9;
            border: 1px solid #ccd6e8;
            border-radius: 4px;
        }
        .resumen-box td {
            padding: 8px 14px;
            text-align: center;
            border-right: 1px solid #ccd6e8;
        }
        .resumen-box td:last-child { border-right: none; }
        .resumen-num { font-size: 18px; font-weight: bold; color: #1e4d7b; line-height: 1; }
        .resumen-lbl { font-size: 9px; color: #666; text-transform: uppercase; margin-top: 3px; letter-spacing: .05em; }

        /* ── Desglose por forma de pago ── */
        .section-title {
            background: #1e4d7b;
            color: #fff;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            border-radius: 3px;
        }

        table.fp-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.fp-table th {
            background: #f4f6f9;
            color: #1e4d7b;
            padding: 4px 8px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #d0dde8;
        }
        table.fp-table td {
            padding: 4px 8px;
            border: 1px solid #e4eaf0;
            font-size: 11px;
        }
        table.fp-table tfoot td {
            background: #f0f4f9;
            font-weight: bold;
            border: 1px solid #d0dde8;
            padding: 4px 8px;
        }

        /* ── Tabla de pagos ── */
        table.pagos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.pagos-table th {
            background: #f4f6f9;
            color: #1e4d7b;
            padding: 4px 7px;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #d0dde8;
            white-space: nowrap;
        }
        table.pagos-table td {
            padding: 4px 7px;
            border-bottom: 1px solid #eef0f3;
            font-size: 11px;
            vertical-align: middle;
        }
        table.pagos-table tr:nth-child(even) td { background: #f9fafb; }
        table.pagos-table tfoot td {
            background: #f0f4f9;
            font-weight: bold;
            font-size: 12px;
            padding: 5px 7px;
            border-top: 2px solid #1e4d7b;
        }

        /* ── Cajero header ── */
        .cajero-header {
            background: #e8f0fb;
            border: 1px solid #ccd8f0;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #1e4d7b;
            margin-bottom: 0;
            border-radius: 4px 4px 0 0;
        }

        /* ── Firma ── */
        .firma-table {
            width: 100%;
            margin-top: 20px;
        }
        .firma-table td {
            width: 33%;
            text-align: center;
            font-size: 10px;
            color: #555;
            padding-top: 20px;
            border-top: 1px solid #aaa;
        }

        /* ── Pie ── */
        .pie {
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 6px;
            margin-top: 16px;
            font-size: 9px;
            color: #aaa;
        }

        .text-right  { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    {{-- ── Encabezado ── --}}
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
                <div class="school-sub">Reporte de Corte del Día</div>
            </td>
            <td class="report-title" style="width: 35%;">
                <div class="report-title-main">CORTE DEL DÍA</div>
                <div class="report-title-fecha">
                    {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l, d \d\e F \d\e Y') }}
                </div>
                @if(!$esAdmin)
                <div style="font-size:10px;color:#888;margin-top:2px;">
                    Cajero: {{ auth()->user()->nombre }}
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── Resumen general ── --}}
    <table class="resumen-box">
        <tr>
            <td>
                <div class="resumen-num">${{ number_format($resumen['total_cobrado'], 2) }}</div>
                <div class="resumen-lbl">Total cobrado</div>
            </td>
            <td>
                <div class="resumen-num">{{ $resumen['total_pagos'] }}</div>
                <div class="resumen-lbl">Recibos vigentes</div>
            </td>
            <td>
                <div class="resumen-num">{{ $resumen['total_cargos'] }}</div>
                <div class="resumen-lbl">Conceptos pagados</div>
            </td>
            @if($resumen['total_anulados'] > 0)
            <td>
                <div class="resumen-num" style="color:#e74c3c;">{{ $resumen['total_anulados'] }}</div>
                <div class="resumen-lbl">Anulados</div>
            </td>
            @endif
        </tr>
    </table>

    {{-- ── Desglose por forma de pago ── --}}
    <div class="section-title">Desglose por Forma de Pago</div>
    <table class="fp-table">
        <thead>
            <tr>
                <th style="text-align:left;">Forma de Pago</th>
                <th class="text-center">Recibos</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumen['por_forma_pago'] as $forma => $datos)
            <tr>
                <td>{{ $formaLabels[$forma] ?? ucfirst($forma) }}</td>
                <td class="text-center">{{ $datos['cantidad'] }}</td>
                <td class="text-right">${{ number_format($datos['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="text-center">{{ $resumen['total_pagos'] }}</td>
                <td class="text-right">${{ number_format($resumen['total_cobrado'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Detalle de pagos (agrupado por cajero si admin) ── --}}
    <div class="section-title">Detalle de Pagos</div>

    @foreach($porCajero as $grupo)

        @if($esAdmin && $porCajero->count() > 1)
        <div class="cajero-header">
            Cajero: {{ $grupo['cajero']?->nombre ?? '—' }}
            &nbsp;&mdash;&nbsp;
            {{ $grupo['cantidad'] }} recibo{{ $grupo['cantidad'] != 1 ? 's' : '' }}
            &nbsp;&mdash;&nbsp;
            ${{ number_format($grupo['total'], 2) }}
        </div>
        @endif

        <table class="pagos-table" style="{{ ($esAdmin && $porCajero->count() > 1) ? 'border-radius:0 0 4px 4px;' : '' }}">
            <thead>
                <tr>
                    <th style="text-align:left; width:18%;">Folio</th>
                    <th style="text-align:left; width:35%;">Alumno</th>
                    <th style="width:16%;">Forma de Pago</th>
                    <th style="width:15%;">Conceptos</th>
                    <th class="text-right; width:16%;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grupo['pagos'] as $pago)
                @php
                    $alumno = $pago->detalles->map(fn($d) => $d->cargo?->inscripcion?->alumno)
                        ->filter()->unique('id')->first();
                    $extra  = $pago->detalles->map(fn($d) => $d->cargo?->inscripcion?->alumno)
                        ->filter()->unique('id')->count() - 1;
                @endphp
                <tr>
                    <td style="font-weight:bold;">{{ $pago->folio_recibo }}</td>
                    <td>
                        @if($alumno)
                            {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
                            @if($extra > 0) <span style="color:#888;">(+{{ $extra }})</span> @endif
                        @else
                            <span style="color:#bbb;">—</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $formaLabels[$pago->forma_pago] ?? ucfirst($pago->forma_pago) }}</td>
                    <td class="text-center">{{ $pago->detalles->count() }}</td>
                    <td class="text-right" style="font-weight:bold;">${{ number_format($pago->monto_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="text-center">{{ $grupo['pagos']->sum(fn($p) => $p->detalles->count()) }}</td>
                    <td class="text-right">${{ number_format($grupo['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

    @endforeach

    {{-- ── Firma ── --}}
    <table class="firma-table">
        <tr>
            <td>
                Firma / Sello del Cajero<br>
                <span style="font-size:9px;color:#999;">
                    @if(!$esAdmin) {{ auth()->user()->nombre }} @else &nbsp; @endif
                </span>
            </td>
            <td>
                Vo. Bo. Supervisión
            </td>
            <td>
                Fecha y Hora de Cierre<br>
                <span style="font-size:9px;color:#999;">{{ now()->format('d/m/Y H:i') }}</span>
            </td>
        </tr>
    </table>

    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }}
    </div>

</body>
</html>
