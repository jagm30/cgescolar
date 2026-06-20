<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';

        $tipoConfig = [
            'colegiatura'      => ['label' => 'Colegiatura', 'bg' => '#e8f0fb', 'color' => '#3c8dbc'],
            'inscripcion'      => ['label' => 'Inscripción', 'bg' => '#e8f8f0', 'color' => '#00875a'],
            'cargo_unico'      => ['label' => 'Cargo único', 'bg' => '#fff8e1', 'color' => '#b45309'],
            'cargo_recurrente' => ['label' => 'Recurrente',  'bg' => '#f3e8fd', 'color' => '#7c3aed'],
        ];

        $formaLabels = [
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'tarjeta'       => 'Tarjeta',
            'cheque'        => 'Cheque',
        ];
    @endphp

    <title>Detalle de Ingresos — {{ $fechaDesde }} al {{ $fechaHasta }}</title>

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
        .report-title-sub  { color: #555; font-size: 10px; margin-top: 3px; }

        /* ── Resumen de estadísticas ── */
        .resumen-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background: #f0f4f9;
            border: 1px solid #ccd6e8;
        }
        .resumen-box td {
            padding: 8px 14px;
            text-align: center;
            border-right: 1px solid #ccd6e8;
        }
        .resumen-box td:last-child { border-right: none; }
        .resumen-num { font-size: 18px; font-weight: bold; color: #1e4d7b; line-height: 1; }
        .resumen-lbl { font-size: 9px; color: #666; text-transform: uppercase; margin-top: 3px; letter-spacing: .05em; }

        /* ── Filtros activos ── */
        .filtros-box {
            background: #f8fafc;
            border: 1px solid #dde4eb;
            border-radius: 3px;
            padding: 5px 10px;
            margin-bottom: 10px;
            font-size: 10px;
            color: #555;
        }

        /* ── Secciones ── */
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

        /* ── Tabla resumen por concepto ── */
        table.concepto-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.concepto-table th {
            background: #f4f6f9;
            color: #1e4d7b;
            padding: 4px 8px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #d0dde8;
        }
        table.concepto-table td {
            padding: 5px 8px;
            border: 1px solid #e4eaf0;
            font-size: 11px;
            vertical-align: middle;
        }
        table.concepto-table tfoot td {
            background: #f0f4f9;
            font-weight: bold;
            border: 1px solid #d0dde8;
            padding: 5px 8px;
        }
        table.concepto-table tr:nth-child(even) td { background: #f9fafb; }

        /* ── Tabla detalle de recibos ── */
        table.recibos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.recibos-table th {
            background: #f4f6f9;
            color: #1e4d7b;
            padding: 4px 7px;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #d0dde8;
            white-space: nowrap;
        }
        table.recibos-table td {
            padding: 4px 7px;
            border-bottom: 1px solid #eef0f3;
            font-size: 11px;
            vertical-align: top;
        }
        table.recibos-table tr:nth-child(even) td { background: #f9fafb; }
        table.recibos-table tfoot td {
            background: #f0f4f9;
            font-weight: bold;
            font-size: 12px;
            padding: 5px 7px;
            border-top: 2px solid #1e4d7b;
        }

        /* ── Tipo badge ── */
        .tipo-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
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
                <div class="school-sub">Reporte de Detalle de Ingresos</div>
            </td>
            <td class="report-title" style="width: 35%;">
                <div class="report-title-main">DETALLE DE INGRESOS</div>
                <div class="report-title-sub">
                    Del {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
                    al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                </div>
                <div class="report-title-sub" style="color:#999;margin-top:2px;">
                    Generado el {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Filtros activos ── --}}
    @if($filtroConcepto || $filtroNivel || $filtroPeriodo || $filtroForma)
    <div class="filtros-box">
        <strong>Filtros aplicados:</strong>
        @if($filtroConcepto) &nbsp; Concepto: <strong>{{ $filtroConcepto->nombre }}</strong> @endif
        @if($filtroNivel)    &nbsp;·&nbsp; Nivel: <strong>{{ $filtroNivel->nombre }}</strong> @endif
        @if($filtroPeriodo)  &nbsp;·&nbsp; Período: <strong>{{ ucfirst($filtroPeriodo->monthName) }} {{ $filtroPeriodo->year }}</strong> @endif
        @if($filtroForma)    &nbsp;·&nbsp; Forma de pago: <strong>{{ $formaLabels[$filtroForma] ?? ucfirst($filtroForma) }}</strong> @endif
    </div>
    @endif

    {{-- ── Resumen de estadísticas ── --}}
    <table class="resumen-box">
        <tr>
            <td>
                <div class="resumen-num">${{ number_format($resumen['total_cobrado'], 2) }}</div>
                <div class="resumen-lbl">Total ingresado</div>
            </td>
            <td>
                <div class="resumen-num">{{ $resumen['total_pagos'] }}</div>
                <div class="resumen-lbl">Recibos vigentes</div>
            </td>
            <td>
                <div class="resumen-num">{{ $resumen['total_conceptos'] }}</div>
                <div class="resumen-lbl">Conceptos / períodos</div>
            </td>
        </tr>
    </table>

    @if($resumen['total_cobrado'] == 0)

    <div style="text-align:center;padding:40px 0;color:#b0bec5;border:1px solid #e4eaf0;">
        Sin ingresos en el período seleccionado.
    </div>

    @else

    {{-- ── Resumen por concepto y período ── --}}
    <div class="section-title">Resumen por Concepto y Período de Cargo</div>
    <table class="concepto-table">
        <thead>
            <tr>
                <th style="text-align: left; width: 30%;">Concepto</th>
                <th style="text-align: left; width: 14%;">Tipo</th>
                <th style="text-align: left; width: 18%;">Período del cargo</th>
                <th class="text-center" style="width: 12%;">Cargos cobrados</th>
                <th class="text-right" style="width: 16%;">Total ingresado</th>
                <th class="text-right" style="width: 10%;">% del total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porConcepto as $fila)
            @php
                $tc  = $tipoConfig[$fila['concepto']->tipo] ?? ['label' => ucfirst($fila['concepto']->tipo), 'bg' => '#f0f3f7', 'color' => '#6b7a8d'];
                $pct = $resumen['total_cobrado'] > 0
                    ? round($fila['total'] / $resumen['total_cobrado'] * 100, 1) : 0;
            @endphp
            <tr>
                <td>
                    <strong>{{ $fila['concepto']->nombre }}</strong>
                    @if($fila['concepto']->descripcion)
                        <br><span style="font-size:10px;color:#8a9ab0;">{{ $fila['concepto']->descripcion }}</span>
                    @endif
                </td>
                <td>
                    <span class="tipo-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                        {{ $tc['label'] }}
                    </span>
                </td>
                <td>
                    @if($fila['periodo'])
                        <strong>{{ $fila['periodo_label'] }}</strong>
                    @else
                        <span style="color:#b0bec5;">Sin período</span>
                    @endif
                </td>
                <td class="text-center">{{ $fila['cantidad'] }}</td>
                <td class="text-right"><strong>${{ number_format($fila['total'], 2) }}</strong></td>
                <td class="text-right">{{ $pct }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total</td>
                <td class="text-center">{{ $porConcepto->sum('cantidad') }}</td>
                <td class="text-right">${{ number_format($resumen['total_cobrado'], 2) }}</td>
                <td class="text-right">100%</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Detalle de recibos ── --}}
    <div class="section-title">Detalle de Recibos ({{ $resumen['total_pagos'] }})</div>
    <table class="recibos-table">
        <thead>
            <tr>
                <th style="text-align: left; width: 12%;">Fecha</th>
                <th style="text-align: left; width: 18%;">Folio</th>
                <th style="text-align: left; width: 28%;">Alumno</th>
                <th style="text-align: left; width: 24%;">Concepto / Período</th>
                <th style="text-align: left; width: 10%;">Forma</th>
                <th class="text-right" style="width: 13%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagosUnicos as $pago)
            @php
                $alumnos = $pago->detalles
                    ->map(fn ($d) => $d->cargo?->inscripcion?->alumno)
                    ->filter()->unique('id')->values();
                $alumno = $alumnos->first();
                $extra  = $alumnos->count() - 1;
            @endphp
            <tr>
                <td style="white-space: nowrap;">
                    {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                </td>
                <td style="font-weight: bold;">{{ $pago->folio_recibo }}</td>
                <td>
                    @if($alumno)
                        {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
                        @if($extra > 0)
                            <span style="color:#888;font-size:10px;">(+{{ $extra }} más)</span>
                        @endif
                    @else
                        <span style="color:#bbb;">—</span>
                    @endif
                </td>
                <td>
                    @foreach($pago->detalles->unique(fn ($d) => ($d->cargo?->concepto_id ?? 0).':'.($d->cargo?->periodo ?? '')) as $det)
                    <div style="font-size:10px;line-height:1.6;color:#4a5568;">
                        {{ $det->cargo?->concepto?->nombre ?? '—' }}
                        @if($det->cargo?->periodo)
                            &nbsp;<strong>{{ $det->cargo->periodo_label }}</strong>
                        @endif
                    </div>
                    @endforeach
                </td>
                <td>{{ $formaLabels[$pago->forma_pago] ?? ucfirst($pago->forma_pago) }}</td>
                <td class="text-right" style="font-weight: bold;">
                    ${{ number_format($pago->monto_total, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Total</td>
                <td class="text-right">${{ number_format($resumen['total_cobrado'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @endif{{-- /total_cobrado > 0 --}}

    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }}
    </div>

</body>
</html>
