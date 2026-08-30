<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo  = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta ?? 'logo-escuela.png';

        $inscripciones = $grupo->inscripciones;

        $estadoColor = [
            'pagado'         => '#16a34a',
            'pendiente'      => '#dc2626',
            'parcial'        => '#d97706',
            'vencido'        => '#b91c1c',
            'parcial_vencido'=> '#b45309',
            'condonado'      => '#6b7280',
        ];
        $estadoLabel = [
            'pagado'         => 'Pagado',
            'pendiente'      => 'Pendiente',
            'parcial'        => 'Parcial',
            'vencido'        => 'Vencido',
            'parcial_vencido'=> 'Parcial/Venc.',
            'condonado'      => 'Condonado',
        ];

        // Totales globales
        $totalCargado    = 0;
        $totalBeca       = 0;
        $totalProntoPago = 0;
        $totalCondonado  = 0;
        $totalAbonado    = 0;

        foreach ($inscripciones as $ins) {
            foreach ($ins->cargos as $c) {
                $totalCargado    += (float) $c->monto_original;
                $totalBeca       += $c->detallesPagosVigentes->sum('descuento_beca');
                $totalProntoPago += $c->detallesPagosVigentes->sum('descuento_pronto_pago');
                $totalCondonado  += $c->condonacionDetalles->sum('monto_aplicado');
                $totalAbonado    += $c->saldo_abonado;
            }
        }

        $totalPendiente = max(0, $totalCargado - $totalAbonado);
    @endphp

    <title>Reporte de Pagos — {{ $grupo->nombre }}</title>

    <style>
        @page { size: letter landscape; margin: 8mm 10mm; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #1a2634;
            margin: 0; padding: 0;
        }

        /* ── Encabezado ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 6px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 80px; height: auto; display: block; }
        .school-name { color: #1e4d7b; font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .school-sub  { color: #777; font-size: 9px; margin-top: 2px; text-transform: uppercase; }
        .report-title { text-align: right; }
        .report-title-main { color: #1e4d7b; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .report-title-sub  { color: #666; font-size: 9px; margin-top: 3px; }

        /* ── Info del grupo ── */
        .info-box { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #dde4eb; }
        .info-box td { padding: 6px 12px; border-right: 1px solid #dde4eb; vertical-align: middle; }
        .info-box td:last-child { border-right: none; }
        .info-lbl { font-size: 8px; font-weight: bold; color: #8a9ab0; text-transform: uppercase; letter-spacing: .05em; display: block; margin-bottom: 2px; }
        .info-val { font-size: 11px; font-weight: bold; color: #1a2634; }
        .info-badge { display: inline-block; background: #e8f0fb; color: #2e6da4; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; border: 1px solid #b3d0f0; }
        .monto-verde { color: #16a34a; }
        .monto-rojo  { color: #dc2626; }

        /* ── Tabla principal ── */
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; table-layout: fixed; }

        .main-table thead th {
            background: #1e4d7b;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 5px;
            border: 1px solid #1a4570;
            letter-spacing: .03em;
            text-align: center;
        }
        .main-table thead th.left { text-align: left; }

        /* ── Fila cabecera de alumno ── */
        .alumno-header td {
            background: #1e4d7b;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #1a4570;
        }
        .alumno-header .num-cell {
            text-align: center;
            width: 22px;
            background: #163d63;
        }
        .alumno-header .name-cell {
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .alumno-header .mat-cell {
            text-align: right;
            font-family: monospace;
            font-size: 8px;
            opacity: .85;
            white-space: nowrap;
        }

        /* ── Filas de cargo ── */
        .cargo-row td {
            padding: 4px 5px;
            border: 1px solid #e0e6ed;
            vertical-align: middle;
            font-size: 9px;
        }
        .cargo-row:nth-child(odd) td  { background: #f9fafb; }
        .cargo-row:nth-child(even) td { background: #ffffff; }

        /* ── Subtotal del alumno ── */
        .subtotal-row td {
            padding: 4px 5px;
            border: 1px solid #c9d5e0;
            background: #edf3fa;
            font-size: 8.5px;
            font-weight: bold;
        }

        /* ── Fila de totales globales ── */
        .total-row td {
            padding: 5px 5px;
            border: 2px solid #1e4d7b;
            background: #1e4d7b;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
        }

        /* ── Alineaciones ── */
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .mono { font-family: monospace; }

        /* ── Badges de estado ── */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* ── Valores cero: gris ── */
        .cero { color: #cbd5e1; }

        /* ── Pie de página ── */
        .pie {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 4px;
            font-size: 8px;
            color: #bbb;
        }
    </style>
</head>
<body>

    {{-- ── Encabezado ── --}}
    <table class="header">
        <tr>
            <td style="width:14%;">
                @if (file_exists(public_path('imgs_escuela/reportes/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/reportes/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @else
                    <div style="width:80px;height:60px;background:#e0e0e0;text-align:center;line-height:60px;color:#888;font-size:8px;">LOGO</div>
                @endif
            </td>
            <td style="width:48%; padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Estado de Pagos por Grupo</div>
            </td>
            <td class="report-title" style="width:38%;">
                <div class="report-title-main">Reporte de Pagos</div>
                <div class="report-title-sub">
                    Ciclo: {{ $grupo->ciclo->nombre }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }} hrs
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Datos del grupo ── --}}
    <table class="info-box">
        <tr>
            <td style="width:18%;">
                <span class="info-lbl">Nivel</span>
                <span class="info-val">{{ $grupo->grado->nivel->nombre }}</span>
            </td>
            <td style="width:18%;">
                <span class="info-lbl">Grado y Grupo</span>
                <span class="info-badge">{{ $grupo->grado->numero }}° {{ $grupo->nombre }}</span>
            </td>
            <td style="width:12%;">
                <span class="info-lbl">Alumnos</span>
                <span class="info-val">{{ $inscripciones->count() }}</span>
            </td>
            <td style="width:13%; text-align:right;">
                <span class="info-lbl">Total cargado</span>
                <span class="info-val">${{ number_format($totalCargado, 2) }}</span>
            </td>
            <td style="width:13%; text-align:right;">
                <span class="info-lbl">Total abonado</span>
                <span class="info-val monto-verde">${{ number_format($totalAbonado, 2) }}</span>
            </td>
            <td style="width:13%; text-align:right;">
                <span class="info-lbl">Saldo pendiente</span>
                <span class="info-val monto-rojo">${{ number_format($totalPendiente, 2) }}</span>
            </td>
            <td style="width:13%; text-align:right;">
                <span class="info-lbl">Total condonado</span>
                <span class="info-val" style="color:#6b7280;">${{ number_format($totalCondonado, 2) }}</span>
            </td>
        </tr>
    </table>

    {{-- ── Tabla principal ── --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width:22px;"  class="left">#</th>
                <th style="width:24%;"   class="left">Concepto / Período</th>
                <th style="width:9%;">   Monto original</th>
                <th style="width:8%;">   Beca</th>
                <th style="width:8%;">   Dto. PP</th>
                <th style="width:8%;">   Condonado</th>
                <th style="width:9%;">   Abonado</th>
                <th style="width:9%;">   Pendiente</th>
                <th style="width:9%;">   Estado</th>
                <th style="width:9%;">   Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripciones as $index => $ins)
                @php
                    $cargos = $ins->cargos->sortBy('fecha_vencimiento');

                    $subtotalOriginal  = $cargos->sum('monto_original');
                    $subtotalBeca      = $cargos->sum(fn($c) => $c->detallesPagosVigentes->sum('descuento_beca'));
                    $subtotalProntoPago= $cargos->sum(fn($c) => $c->detallesPagosVigentes->sum('descuento_pronto_pago'));
                    $subtotalCondonado = $cargos->sum(fn($c) => $c->condonacionDetalles->sum('monto_aplicado'));
                    $subtotalAbonado   = $cargos->sum(fn($c) => $c->saldo_abonado);
                    $subtotalPendiente = max(0, $subtotalOriginal - $subtotalAbonado);
                @endphp

                {{-- ── Fila-cabecera del alumno (sin rowspan) ── --}}
                <tr class="alumno-header">
                    <td class="num-cell">{{ $index + 1 }}</td>
                    <td class="name-cell" colspan="7">
                        {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }}, {{ $ins->alumno->nombre }}
                    </td>
                    <td class="mat-cell" colspan="2">
                        Matrícula: {{ $ins->alumno->matricula ?? '—' }}
                    </td>
                </tr>

                @if ($cargos->isEmpty())
                    <tr class="cargo-row">
                        <td></td>
                        <td colspan="9" style="color:#94a3b8; font-style:italic;">
                            Sin cargos registrados en este ciclo.
                        </td>
                    </tr>
                @else
                    @foreach ($cargos as $cargo)
                        @php
                            $beca       = $cargo->detallesPagosVigentes->sum('descuento_beca');
                            $prontoPago = $cargo->detallesPagosVigentes->sum('descuento_pronto_pago');
                            $condonado  = $cargo->condonacionDetalles->sum('monto_aplicado');
                            $abonado    = $cargo->saldo_abonado;
                            $pendiente  = max(0, (float) $cargo->monto_original - $abonado);
                            $estado     = $cargo->estado_real;
                            $color      = $estadoColor[$estado] ?? '#6b7280';
                            $label      = $estadoLabel[$estado] ?? $estado;
                        @endphp
                        <tr class="cargo-row">
                            <td></td>
                            <td class="text-left" style="padding-left:10px;">{{ $cargo->etiqueta }}</td>
                            <td class="text-right mono">${{ number_format($cargo->monto_original, 2) }}</td>
                            <td class="text-right mono {{ $beca > 0 ? 'monto-verde' : 'cero' }}">
                                {{ $beca > 0 ? '$'.number_format($beca, 2) : '—' }}
                            </td>
                            <td class="text-right mono {{ $prontoPago > 0 ? 'monto-verde' : 'cero' }}">
                                {{ $prontoPago > 0 ? '$'.number_format($prontoPago, 2) : '—' }}
                            </td>
                            <td class="text-right mono {{ $condonado > 0 ? '' : 'cero' }}" style="{{ $condonado > 0 ? 'color:#6b7280;' : '' }}">
                                {{ $condonado > 0 ? '$'.number_format($condonado, 2) : '—' }}
                            </td>
                            <td class="text-right mono monto-verde">
                                {{ $abonado > 0 ? '$'.number_format($abonado, 2) : '—' }}
                            </td>
                            <td class="text-right mono {{ $pendiente > 0 ? 'monto-rojo' : 'monto-verde' }}">
                                ${{ number_format($pendiente, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge"
                                    style="background:{{ $color }}22; color:{{ $color }}; border:1px solid {{ $color }}55;">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="text-center" style="color:#4a5568;">
                                {{ $cargo->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- ── Subtotal del alumno ── --}}
                    <tr class="subtotal-row">
                        <td></td>
                        <td class="text-right" style="color:#475569; padding-right:6px;">Subtotal alumno</td>
                        <td class="text-right mono">${{ number_format($subtotalOriginal, 2) }}</td>
                        <td class="text-right mono monto-verde">
                            {{ $subtotalBeca > 0 ? '$'.number_format($subtotalBeca, 2) : '—' }}
                        </td>
                        <td class="text-right mono monto-verde">
                            {{ $subtotalProntoPago > 0 ? '$'.number_format($subtotalProntoPago, 2) : '—' }}
                        </td>
                        <td class="text-right mono" style="color:#6b7280;">
                            {{ $subtotalCondonado > 0 ? '$'.number_format($subtotalCondonado, 2) : '—' }}
                        </td>
                        <td class="text-right mono monto-verde">${{ number_format($subtotalAbonado, 2) }}</td>
                        <td class="text-right mono {{ $subtotalPendiente > 0 ? 'monto-rojo' : 'monto-verde' }}">
                            ${{ number_format($subtotalPendiente, 2) }}
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="10" class="text-center" style="color:#94a3b8; padding:16px;">
                        No hay alumnos activos en este grupo.
                    </td>
                </tr>
            @endforelse

            {{-- ── Totales globales ── --}}
            @if ($inscripciones->isNotEmpty())
                <tr class="total-row">
                    <td colspan="2" class="text-right" style="letter-spacing:.04em;">
                        TOTALES DEL GRUPO
                    </td>
                    <td class="text-right mono">${{ number_format($totalCargado, 2) }}</td>
                    <td class="text-right mono">
                        {{ $totalBeca > 0 ? '$'.number_format($totalBeca, 2) : '—' }}
                    </td>
                    <td class="text-right mono">
                        {{ $totalProntoPago > 0 ? '$'.number_format($totalProntoPago, 2) : '—' }}
                    </td>
                    <td class="text-right mono">
                        {{ $totalCondonado > 0 ? '$'.number_format($totalCondonado, 2) : '—' }}
                    </td>
                    <td class="text-right mono">${{ number_format($totalAbonado, 2) }}</td>
                    <td class="text-right mono">${{ number_format($totalPendiente, 2) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- ── Leyenda de columnas ── --}}
    <div style="font-size:7.5px; color:#94a3b8; margin-top:4px; border-top:1px dashed #e2e8f0; padding-top:4px;">
        <strong style="color:#64748b;">Glosario:</strong>
        <strong>Beca</strong> = descuento por beca aplicado al pago &nbsp;·&nbsp;
        <strong>Dto. PP</strong> = descuento por pronto pago &nbsp;·&nbsp;
        <strong>Condonado</strong> = monto condonado (cancelado) &nbsp;·&nbsp;
        <strong>Abonado</strong> = monto efectivamente cubierto (incluye beca y descuentos) &nbsp;·&nbsp;
        <strong>Pendiente</strong> = saldo restante por cubrir
    </div>

    {{-- ── Pie de página ── --}}
    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }} &mdash; Este documento no tiene validez oficial fuera de la institución.
    </div>

</body>
</html>
