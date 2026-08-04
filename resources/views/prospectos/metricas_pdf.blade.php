<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>

        @page { margin: 10mm 12mm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── ENCABEZADO ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 8px;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 270px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub { color: #777; font-size: 16px; margin-top: 2px; text-transform: uppercase; }
        .report-title { text-align: right; }
        .report-title-main {
            color: #1e4d7b;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title-sub { color: #666; font-size: 16px; margin-top: 3px; line-height: 1.6; }

        /* ── TÍTULO DE SECCIÓN ── */
        .section-title {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            margin-bottom: 5px;
        }
        .section-title td {
            background: #1e4d7b;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 10px;
            letter-spacing: .06em;
        }

        /* ── KPIs ── */
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 8px; }
        .kpi-cell {
            border: 1.5px solid #c8d8e8;
            border-radius: 5px;
            padding: 12px 8px;
            text-align: center;
            background: #f8fbff;
        }
        .kpi-num   { font-size: 28px; font-weight: 700; color: #1e4d7b; line-height: 1; }
        .kpi-label { font-size: 14px; color: #666; text-transform: uppercase; letter-spacing: .3px; margin-top: 5px; }

        /* ── GRÁFICAS ── */
        .ch-title {
            font-size: 16px;
            font-weight: 700;
            color: #5a6a7a;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 5px;
            text-align: center;
        }
        .ch-img {
            border: 1px solid #dde5ef;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }

        /* ── TABLAS DE DATOS ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 18px;
        }
        .data-table th {
            background: #f2f5f9;
            color: #1e4d7b;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 9px;
            border: 1px solid #dde4eb;
            text-align: left;
        }
        .data-table th.dark {
            background: #1e4d7b;
            color: #fff;
        }
        .data-table td {
            padding: 6px 9px;
            border: 1px solid #dde4eb;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) td { background: #f5f8fc; }
        .data-table tfoot td {
            background: #e6edf7;
            font-weight: 700;
            border-top: 2px solid #1e4d7b;
            padding: 6px 9px;
        }

        /* ── BADGE ETAPA ── */
        .be {
            display: inline;
            padding: 2px 7px;
            border-radius: 9px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .e-prospecto     { background:#dbeafe; color:#1e40af; }
        .e-cita          { background:#cffafe; color:#0e7490; }
        .e-visita        { background:#d1fae5; color:#065f46; }
        .e-documentacion { background:#fef9c3; color:#854d0e; }
        .e-aceptado      { background:#bbf7d0; color:#14532d; }
        .e-en_espera     { background:#fde68a; color:#78350f; }
        .e-no_aceptado   { background:#fecaca; color:#7f1d1d; }
        .e-inscrito      { background:#e0e7ef; color:#1e3a5f; }
        .e-no_concretado { background:#fee2e2; color:#991b1b; }

        /* ── 2 COLUMNAS ── */
        .two-col { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .col-l   { width: 50%; vertical-align: top; padding-right: 6px; }
        .col-r   { width: 50%; vertical-align: top; padding-left: 6px; }

        /* ── TEXTO ── */
        .tr { text-align: right; }
        .tc { text-align: center; }
        .tm { color: #8a9ab0; }
        .tb { font-weight: 700; }

        /* ── PIE FIJO ── */
        .pie {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            font-size: 14px;
            color: #bbb;
        }

        /* ── SALTOS ── */
        .pb { page-break-before: always; }

    </style>
</head>
<body>

@php
    $etiquetasEtapa = [
        'prospecto'     => 'Prospecto',    'cita'          => 'Cita',
        'visita'        => 'Visita',       'documentacion' => 'Documentación',
        'aceptado'      => 'Aceptado',     'en_espera'     => 'En espera',
        'no_aceptado'   => 'No aceptado',  'inscrito'      => 'Inscrito',
        'no_concretado' => 'No concretado',
    ];
    $etiquetasCanal = [
        'referido'       => 'Referido',       'redes'  => 'Redes sociales',
        'visita_directa' => 'Visita directa', 'web'    => 'Sitio web',
        'otro'           => 'Otro',
    ];
    $esc           = \App\Models\Setting::find(1);
    $nombreEscuela = $esc?->nombre_escuela ?? config('app.school_name');
    $total         = $datos['total_prospectos'];
@endphp

{{-- PIE FIJO (aparece en todas las páginas) --}}
<div class="pie">
    {{ $nombreEscuela }} &mdash; Informe de Admisiones &mdash; {{ $ciclo?->nombre }} &mdash;
    Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->nombre ?? '—' }}
</div>

{{-- ═══════════════════════════════════
     PÁGINA 1 — KPIs + Gráficas
     ═══════════════════════════════════ --}}

{{-- Encabezado --}}
<table class="header">
    <tr>
        <td style="width:28%;">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="school-logo" alt="Logo">
            @else
                <div style="width:240px;height:240px;background:#e0e0e0;text-align:center;line-height:240px;color:#888;font-size:14px;">LOGO</div>
            @endif
        </td>
        <td style="width:40%; padding-left:10px;">
            <div class="school-name">{{ $nombreEscuela }}</div>
            <div class="school-sub">Informe de Admisiones</div>
        </td>
        <td class="report-title" style="width:33%;">
            <div class="report-title-main">Informe de Admisiones</div>
            <div class="report-title-sub">
                <b>Ciclo:</b> {{ $ciclo?->nombre ?? 'N/A' }}<br>
                <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}<br>
                <b>Por:</b> {{ auth()->user()->nombre ?? '—' }}
            </div>
        </td>
    </tr>
</table>

{{-- KPIs --}}
<table class="section-title"><tr><td>Indicadores generales</td></tr></table>
<table class="kpi-table">
    <tr>
        <td class="kpi-cell">
            <div class="kpi-num">{{ $datos['total_prospectos'] }}</div>
            <div class="kpi-label">Total prospectos</div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-num">{{ $datos['total_inscritos'] }}</div>
            <div class="kpi-label">Inscritos</div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-num">{{ $datos['tasa_conversion'] }}</div>
            <div class="kpi-label">Tasa de conversión</div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-num">{{ $porResponsable->count() }}</div>
            <div class="kpi-label">Responsables activos</div>
        </td>
        <td class="kpi-cell">
            <div class="kpi-num">{{ $porSeguimientoUsuario->sum() }}</div>
            <div class="kpi-label">Total seguimientos</div>
        </td>
    </tr>
</table>

{{-- Gráficas --}}
@if ($chartEtapa || $chartCanal || $chartResponsable)
<table class="section-title"><tr><td>Distribución visual</td></tr></table>

@if ($chartEtapa || $chartCanal)
<table style="width:100%; border-collapse:collapse; margin-bottom:14px;">
    <tr>
        @if ($chartEtapa)
        <td style="width:42%; text-align:center; vertical-align:top; padding-right:10px;">
            <div class="ch-title">Por etapa</div>
            <img class="ch-img" src="{{ $chartEtapa }}" style="width:367px; height:419px;">
        </td>
        @endif
        @if ($chartCanal)
        <td style="width:58%; text-align:center; vertical-align:top; padding-left:10px;">
            <div class="ch-title">Por canal de contacto</div>
            <img class="ch-img" src="{{ $chartCanal }}" style="width:450px; height:260px;">
        </td>
        @endif
    </tr>
</table>
@endif

@if ($chartResponsable)
<table class="section-title"><tr><td>Seguimientos por usuario</td></tr></table>
<div style="text-align:center; margin-top:8px;">
    <img class="ch-img" src="{{ $chartResponsable }}" style="width:675px; height:360px;">
</div>
@endif

@endif

{{-- ═══════════════════════════════════
     PÁGINA 2 — Tablas resumen
     ═══════════════════════════════════ --}}
<div class="pb"></div>

<table class="header">
    <tr>
        <td style="width:28%;">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="school-logo" alt="Logo">
            @else
                <div style="width:240px;height:240px;background:#e0e0e0;text-align:center;line-height:240px;color:#888;font-size:14px;">LOGO</div>
            @endif
        </td>
        <td style="width:40%; padding-left:10px;">
            <div class="school-name">{{ $nombreEscuela }}</div>
            <div class="school-sub">Resumen estadístico</div>
        </td>
        <td class="report-title" style="width:33%;">
            <div class="report-title-main">Resumen estadístico</div>
            <div class="report-title-sub"><b>Ciclo:</b> {{ $ciclo?->nombre ?? 'N/A' }}</div>
        </td>
    </tr>
</table>

<table class="section-title"><tr><td>Prospectos por etapa y canal</td></tr></table>
<table class="two-col">
    <tr>
        <td class="col-l">
            <table class="data-table">
                <thead>
                    <tr><th colspan="3" class="dark">Por etapa</th></tr>
                    <tr><th>Etapa</th><th class="tr">Total</th><th class="tr">%</th></tr>
                </thead>
                <tbody>
                    @foreach ($datos['por_etapa'] as $etapa => $cnt)
                    <tr>
                        <td>{{ $etiquetasEtapa[$etapa] ?? ucfirst($etapa) }}</td>
                        <td class="tr tb">{{ $cnt }}</td>
                        <td class="tr tm">{{ $total > 0 ? round($cnt / $total * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td class="tb">Total</td><td class="tr tb">{{ $total }}</td><td class="tr">100%</td></tr>
                </tfoot>
            </table>
        </td>
        <td class="col-r">
            <table class="data-table">
                <thead>
                    <tr><th colspan="3" class="dark">Por canal de contacto</th></tr>
                    <tr><th>Canal</th><th class="tr">Total</th><th class="tr">%</th></tr>
                </thead>
                <tbody>
                    @foreach ($datos['por_canal'] as $canal => $cnt)
                    <tr>
                        <td>{{ $etiquetasCanal[$canal] ?? ($canal ? ucfirst($canal) : 'Sin canal') }}</td>
                        <td class="tr tb">{{ $cnt }}</td>
                        <td class="tr tm">{{ $total > 0 ? round($cnt / $total * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td class="tb">Total</td><td class="tr tb">{{ $total }}</td><td class="tr">100%</td></tr>
                </tfoot>
            </table>
        </td>
    </tr>
</table>

<table class="section-title"><tr><td>Actividad del equipo de admisiones</td></tr></table>
<table class="two-col">
    <tr>
        <td class="col-l">
            <table class="data-table">
                <thead>
                    <tr><th colspan="3" class="dark">Atención por responsable</th></tr>
                    <tr><th>Responsable</th><th class="tr">Prospectos</th><th class="tr">%</th></tr>
                </thead>
                <tbody>
                    @forelse ($porResponsable as $nombre => $cnt)
                    <tr>
                        <td>{{ $nombre }}</td>
                        <td class="tr tb">{{ $cnt }}</td>
                        <td class="tr tm">{{ $total > 0 ? round($cnt / $total * 100, 1) : 0 }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="tc tm">Sin datos</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr><td class="tb">Total</td><td class="tr tb">{{ $total }}</td><td class="tr">100%</td></tr>
                </tfoot>
            </table>
        </td>
        <td class="col-r">
            <table class="data-table">
                <thead>
                    <tr><th colspan="2" class="dark">Seguimientos por usuario</th></tr>
                    <tr><th>Usuario</th><th class="tr">Acciones registradas</th></tr>
                </thead>
                <tbody>
                    @forelse ($porSeguimientoUsuario as $nombre => $cnt)
                    <tr>
                        <td>{{ $nombre }}</td>
                        <td class="tr tb">{{ $cnt }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="tc tm">Sin datos</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr><td class="tb">Total</td><td class="tr tb">{{ $porSeguimientoUsuario->sum() }}</td></tr>
                </tfoot>
            </table>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════
     PÁGINA 3+ — Listado completo
     ═══════════════════════════════════ --}}
<div class="pb"></div>

<table class="header">
    <tr>
        <td style="width:28%;">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="school-logo" alt="Logo">
            @else
                <div style="width:240px;height:240px;background:#e0e0e0;text-align:center;line-height:240px;color:#888;font-size:14px;">LOGO</div>
            @endif
        </td>
        <td style="width:40%; padding-left:10px;">
            <div class="school-name">{{ $nombreEscuela }}</div>
            <div class="school-sub">Listado completo de prospectos</div>
        </td>
        <td class="report-title" style="width:33%;">
            <div class="report-title-main">Listado de prospectos</div>
            <div class="report-title-sub">
                <b>Ciclo:</b> {{ $ciclo?->nombre ?? 'N/A' }}<br>
                <b>Total:</b> {{ $total }} prospectos
            </div>
        </td>
    </tr>
</table>

<table class="section-title"><tr><td>Listado completo de prospectos — {{ $ciclo?->nombre }}</td></tr></table>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%;"  class="tc dark">#</th>
            <th style="width:25%;" class="dark">Nombre</th>
            <th style="width:14%;" class="dark">Nivel / Grado</th>
            <th style="width:12%;" class="dark">Canal</th>
            <th style="width:14%;" class="dark">Etapa</th>
            <th style="width:19%;" class="dark">Responsable</th>
            <th style="width:12%;" class="tc dark">1er contacto</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($prospectos as $i => $p)
        <tr>
            <td class="tc tm">{{ $i + 1 }}</td>
            <td class="tb">{{ $p->nombre_completo }}</td>
            <td>
                {{ $p->nivelInteres?->nombre ?? '—' }}
                @if ($p->gradoInteres) / {{ $p->gradoInteres->numero }}° @endif
            </td>
            <td>{{ $etiquetasCanal[$p->canal_contacto] ?? ($p->canal_contacto ? ucfirst($p->canal_contacto) : '—') }}</td>
            <td><span class="be e-{{ $p->etapa }}">{{ $etiquetasEtapa[$p->etapa] ?? $p->etapa }}</span></td>
            <td>{{ $p->responsable?->nombre ?? '—' }}</td>
            <td class="tc">{{ $p->fecha_primer_contacto?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="tc tm">Sin prospectos para este ciclo.</td></tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
