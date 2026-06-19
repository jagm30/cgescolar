<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';

        $inscripciones = $grupo->inscripciones->sortBy(fn($i) => $i->alumno->ap_paterno);

        $estadoColor = [
            'pagado'          => '#16a34a',
            'pendiente'       => '#dc2626',
            'parcial'         => '#d97706',
            'vencido'         => '#b91c1c',
            'parcial_vencido' => '#b45309',
            'condonado'       => '#6b7280',
        ];
        $estadoLabel = [
            'pagado'          => 'Pagado',
            'pendiente'       => 'Pendiente',
            'parcial'         => 'Parcial',
            'vencido'         => 'Vencido',
            'parcial_vencido' => 'Parcial/Vencido',
            'condonado'       => 'Condonado',
        ];
    @endphp

    <title>Reporte de Pagos — {{ $grupo->nombre }}</title>

    <style>
        @page { margin: 8mm 10mm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 6px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 100px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub { color: #777; font-size: 10px; margin-top: 2px; text-transform: uppercase; }
        .report-title { text-align: right; }
        .report-title-main { color: #1e4d7b; font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .report-title-sub { color: #666; font-size: 10px; margin-top: 3px; }

        /* ── Info del grupo ── */
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
        .info-val { font-size: 12px; font-weight: bold; color: #1a2634; }
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

        /* ── Tabla principal ── */
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
            font-size: 10px;
        }
        .main-table tbody tr:nth-child(even) td { background: #f9fafb; }

        /* ── Tabla de cargos (interna) ── */
        .cargos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .cargos-table td {
            padding: 2px 5px;
            font-size: 9px;
            border: none;
        }
        .cargo-concepto { color: #374151; }
        .cargo-monto { text-align: right; font-family: monospace; white-space: nowrap; }

        /* ── Badges de estado ── */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* ── Montos ── */
        .monto { text-align: right; font-family: monospace; white-space: nowrap; }
        .monto-rojo { color: #dc2626; }
        .monto-verde { color: #16a34a; }

        /* ── Número de fila ── */
        .num-td { text-align: center; color: #94a3b8; font-size: 9px; }

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
        .text-right  { text-align: right; }
        .bold        { font-weight: bold; }
    </style>
</head>
<body>

    {{-- ── Encabezado ── --}}
    <table class="header">
        <tr>
            <td style="width:18%;">
                @if (file_exists(public_path('imgs_escuela/reportes/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/reportes/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @else
                    <div style="width:100px;height:100px;background:#e0e0e0;text-align:center;line-height:100px;color:#888;font-size:9px;">LOGO</div>
                @endif
            </td>
            <td style="width:46%; padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Reporte de Estado de Pagos por Grupo</div>
            </td>
            <td class="report-title" style="width:36%;">
                <div class="report-title-main">Estado de Pagos</div>
                <div class="report-title-sub">
                    Ciclo: {{ $grupo->ciclo->nombre }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Datos del grupo ── --}}
    @php
        $totalAlumnos  = $inscripciones->count();
        $totalCargado  = $inscripciones->sum(fn($i) => $i->cargos->sum('monto_original'));
        $totalPagado   = $inscripciones->sum(fn($i) => $i->cargos->sum(fn($c) => $c->monto_cubierto));
        $totalPendiente = max(0, $totalCargado - $totalPagado);
    @endphp

    <table class="info-box">
        <tr>
            <td style="width:20%;">
                <span class="info-lbl">Nivel</span>
                <span class="info-val">{{ $grupo->grado->nivel->nombre }}</span>
            </td>
            <td style="width:20%;">
                <span class="info-lbl">Grado y Grupo</span>
                <span class="info-badge">{{ $grupo->grado->numero }}° {{ $grupo->nombre }}</span>
            </td>
            <td style="width:20%;">
                <span class="info-lbl">Total alumnos</span>
                <span class="info-val">{{ $totalAlumnos }}</span>
            </td>
            <td style="width:20%; text-align:right;">
                <span class="info-lbl">Total cargado</span>
                <span class="info-val">${{ number_format($totalCargado, 2) }}</span>
            </td>
            <td style="width:20%; text-align:right;">
                <span class="info-lbl">Total pagado</span>
                <span class="info-val monto-verde">${{ number_format($totalPagado, 2) }}</span>
            </td>
            <td style="width:20%; text-align:right;">
                <span class="info-lbl">Saldo pendiente</span>
                <span class="info-val monto-rojo">${{ number_format($totalPendiente, 2) }}</span>
            </td>
        </tr>
    </table>

    {{-- ── Tabla de alumnos y sus cargos ── --}}
    <table class="main-table">
        <thead>
            <tr>
                <th style="width:24px;">#</th>
                <th style="width:70px;">Matrícula</th>
                <th>Nombre del alumno</th>
                <th>Concepto / Período</th>
                <th style="width:90px;" class="text-right">Monto original</th>
                <th style="width:90px;" class="text-right">Pagado</th>
                <th style="width:90px;" class="text-right">Pendiente</th>
                <th style="width:80px;" class="text-center">Estado</th>
                <th style="width:72px;" class="text-center">Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripciones as $index => $ins)
                @php
                    $cargos = $ins->cargos->sortBy('fecha_vencimiento');
                    $filas  = $cargos->count();
                @endphp

                @if ($filas === 0)
                    <tr>
                        <td class="num-td">{{ $index + 1 }}</td>
                        <td style="text-align:center;font-family:monospace;font-size:9px;color:#4a5568;">
                            {{ $ins->alumno->matricula ?? '—' }}
                        </td>
                        <td class="bold" style="text-transform:uppercase;">
                            {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }},
                            {{ $ins->alumno->nombre }}
                        </td>
                        <td colspan="6" style="color:#94a3b8;font-style:italic;">Sin cargos registrados</td>
                    </tr>
                @else
                    @foreach ($cargos as $ci => $cargo)
                        @php
                            $cubierto  = $cargo->monto_cubierto;
                            $pendiente = max(0, (float) $cargo->monto_original - $cubierto);
                            $estado    = $cargo->estado_real;
                            $color     = $estadoColor[$estado] ?? '#6b7280';
                            $label     = $estadoLabel[$estado] ?? $estado;
                        @endphp
                        <tr>
                            @if ($ci === 0)
                                <td class="num-td" rowspan="{{ $filas }}">{{ $index + 1 }}</td>
                                <td style="text-align:center;font-family:monospace;font-size:9px;color:#4a5568;" rowspan="{{ $filas }}">
                                    {{ $ins->alumno->matricula ?? '—' }}
                                </td>
                                <td class="bold" style="text-transform:uppercase;" rowspan="{{ $filas }}">
                                    {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }},
                                    {{ $ins->alumno->nombre }}
                                </td>
                            @endif
                            <td class="cargo-concepto">{{ $cargo->etiqueta }}</td>
                            <td class="monto">${{ number_format($cargo->monto_original, 2) }}</td>
                            <td class="monto monto-verde">${{ number_format($cubierto, 2) }}</td>
                            <td class="monto {{ $pendiente > 0 ? 'monto-rojo' : '' }}">${{ number_format($pendiente, 2) }}</td>
                            <td class="text-center">
                                <span class="badge" style="background:{{ $color }}1a;color:{{ $color }};border:1px solid {{ $color }}44;">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="text-center" style="font-size:9px;color:#4a5568;">
                                {{ $cargo->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="color:#94a3b8;padding:16px;">
                        No hay alumnos activos en este grupo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── Pie de página ── --}}
    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }} &mdash; Este documento no tiene validez oficial fuera de la institución.
    </div>

</body>
</html>
