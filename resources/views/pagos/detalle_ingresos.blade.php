@extends('layouts.master')

@section('page_title', 'Detalle de ingresos')
@section('page_subtitle', 'Reporte de ingresos por concepto y período')

@section('breadcrumb')
    <li><a href="{{ route('pagos.index') }}">Historial de pagos</a></li>
    <li class="active">Detalle de ingresos</li>
@endsection

@push('styles')
<style>
/* ════ HERO ════ */
.di-hero {
    background: linear-gradient(135deg, #1a5a3a 0%, #27ae60 100%);
    border-radius: 8px; padding: 18px 24px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 0; flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(39,174,96,.22);
}
.di-stat { text-align:center; padding: 0 22px; border-left:1px solid rgba(255,255,255,.18); }
.di-stat:first-child { border-left:none; padding-left:0; }
.di-stat-num { font-size:24px; font-weight:800; color:#fff; line-height:1; }
.di-stat-lbl { font-size:10px; color:rgba(255,255,255,.65); margin-top:3px;
               text-transform:uppercase; letter-spacing:.06em; }

/* ════ PANEL ════ */
.di-panel {
    border:1px solid #e4eaf0; border-radius:10px; background:#fff;
    box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; margin-bottom:20px;
}
.di-panel-header {
    padding:11px 16px; background:#f8fafc; border-bottom:1px solid #e8ecf0;
    display:flex; align-items:center; gap:8px;
}
.di-panel-title {
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:#6b7a8d;
}

/* ════ FILTRO LABEL ════ */
.di-flabel {
    font-size:11px; font-weight:700; color:#6b7a8d;
    text-transform:uppercase; letter-spacing:.04em;
    display:block; margin-bottom:4px;
}

/* ════ TABLA ════ */
.di-table { width:100%; border-collapse:collapse; }
.di-table thead th {
    background:#f4f6f8; color:#6b7a8d;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
    padding:8px 14px; border-bottom:2px solid #e4eaf0; white-space:nowrap;
}
.di-table tbody tr { border-bottom:1px solid #f0f3f7; }
.di-table tbody tr:hover td { background:#f5fbf8; }
.di-table td { padding:9px 14px; vertical-align:middle; font-size:13px; }
.di-table tfoot td {
    padding:10px 14px; font-size:13px;
    border-top:2px solid #e4eaf0; background:#f8fafc; font-weight:700;
}

/* ════ BADGES ════ */
.tipo-badge {
    display:inline-block; font-size:10px; font-weight:700; padding:2px 7px;
    border-radius:6px; letter-spacing:.03em;
}
.periodo-badge {
    display:inline-block; font-size:11px; font-weight:600; padding:2px 8px;
    border-radius:6px; background:#f0f7ff; color:#2c6fad; white-space:nowrap;
}

/* ════ IMPRESIÓN ════ */
@media print {
    .main-header, .main-sidebar, .content-header, .breadcrumb,
    .no-print, .main-footer { display:none !important; }
    .content-wrapper { margin-left:0 !important; padding:0 !important; }
    .print-header { display:block !important; }
    body { background:#fff !important; font-size:12px; }
    .di-hero { display:none !important; }
    .di-table thead th { background:#eee !important; -webkit-print-color-adjust:exact; }
    .di-table tbody tr:hover td { background:transparent !important; }
    a { color:inherit !important; text-decoration:none !important; }
}
.print-header { display:none; }
</style>
@endpush

@section('content')

@php
    $filtroConcepto = $conceptos->firstWhere('id', request('concepto_id'));
    $filtroNivel    = $niveles->firstWhere('id', request('nivel_id'));
    $filtroPeriodo  = request('periodo')
        ? \Carbon\Carbon::createFromFormat('Y-m', request('periodo'))->locale('es')
        : null;
@endphp

{{-- ══ CABECERA IMPRESIÓN ══ --}}
<div class="print-header" style="text-align:center;margin-bottom:20px;border-bottom:2px solid #333;padding-bottom:12px;">
    <h2 style="margin:0;font-size:18px;">DETALLE DE INGRESOS</h2>
    <p style="margin:4px 0 0;font-size:13px;color:#555;">
        Del {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
        al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        @if($filtroConcepto) &nbsp;·&nbsp; Concepto: {{ $filtroConcepto->nombre }} @endif
        @if($filtroNivel)    &nbsp;·&nbsp; Nivel: {{ $filtroNivel->nombre }} @endif
        @if($filtroPeriodo)  &nbsp;·&nbsp; Período: {{ ucfirst($filtroPeriodo->monthName) }} {{ $filtroPeriodo->year }} @endif
    </p>
    <p style="margin:2px 0 0;font-size:11px;color:#888;">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- ══ FILTROS ══ --}}
<div class="no-print di-panel" style="margin-bottom:20px;">
    <div class="di-panel-header">
        <i class="fa fa-filter" style="color:#27ae60;font-size:13px;"></i>
        <span class="di-panel-title">Filtros del reporte</span>
    </div>
    <div style="padding:16px;">
        <form method="GET" action="{{ route('pagos.detalle-ingresos') }}">

            {{-- Fila 1: fechas + concepto + nivel --}}
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="di-flabel">Fecha inicial</label>
                        <input type="date" name="fecha_desde" value="{{ $fechaDesde }}"
                               class="form-control input-sm" style="border-radius:6px;border-color:#dde4eb;">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="di-flabel">Fecha final</label>
                        <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}"
                               class="form-control input-sm" style="border-radius:6px;border-color:#dde4eb;">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="di-flabel">Concepto de pago</label>
                        <select name="concepto_id" class="form-control input-sm" style="border-radius:6px;border-color:#dde4eb;">
                            <option value="">Todos los conceptos</option>
                            @foreach($conceptos as $concepto)
                                <option value="{{ $concepto->id }}"
                                    {{ request('concepto_id') == $concepto->id ? 'selected' : '' }}>
                                    {{ $concepto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="di-flabel">Nivel educativo</label>
                        <select name="nivel_id" class="form-control input-sm" style="border-radius:6px;border-color:#dde4eb;">
                            <option value="">Todos los niveles</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}"
                                    {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Fila 2: período + forma de pago --}}
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="di-flabel">
                            Período del cargo
                            <span style="font-weight:400;color:#9eb1c8;">(mes de la colegiatura)</span>
                        </label>
                        <input type="month" name="periodo" value="{{ request('periodo') }}"
                               class="form-control input-sm"
                               style="border-radius:6px;border-color:#dde4eb;">
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="di-flabel">Forma de pago</label>
                        <select name="forma_pago" class="form-control input-sm" style="border-radius:6px;border-color:#dde4eb;">
                            <option value="">Todas las formas</option>
                            <option value="efectivo"      {{ request('forma_pago') === 'efectivo'      ? 'selected' : '' }}>Efectivo</option>
                            <option value="transferencia" {{ request('forma_pago') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                            <option value="tarjeta"       {{ request('forma_pago') === 'tarjeta'       ? 'selected' : '' }}>Tarjeta</option>
                            <option value="cheque"        {{ request('forma_pago') === 'cheque'        ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6" style="display:flex;align-items:flex-end;padding-bottom:1px;">
                    <div style="display:flex;gap:8px;align-items:center;width:100%;">
                        <button type="submit" class="btn btn-sm btn-success btn-flat" style="border-radius:6px;">
                            <i class="fa fa-search"></i> Generar reporte
                        </button>
                        <a href="{{ route('pagos.detalle-ingresos') }}" class="btn btn-sm btn-default btn-flat" style="border-radius:6px;">
                            <i class="fa fa-times"></i> Limpiar
                        </a>
                        <div style="margin-left:auto;">
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-default btn-flat" style="border-radius:6px;">
                                <i class="fa fa-print"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- ══ FILTROS ACTIVOS ══ --}}
@if($filtroConcepto || $filtroNivel || $filtroPeriodo || request('forma_pago'))
<div class="no-print" style="margin-bottom:14px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
    <span style="font-size:11px;color:#8a9ab0;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Filtrando por:</span>
    @if($filtroConcepto)
    <span style="background:#e8f8f0;color:#00875a;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">
        <i class="fa fa-tag" style="margin-right:4px;"></i>{{ $filtroConcepto->nombre }}
    </span>
    @endif
    @if($filtroNivel)
    <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">
        <i class="fa fa-graduation-cap" style="margin-right:4px;"></i>{{ $filtroNivel->nombre }}
    </span>
    @endif
    @if($filtroPeriodo)
    <span style="background:#f0f7ff;color:#2c6fad;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">
        <i class="fa fa-calendar" style="margin-right:4px;"></i>{{ ucfirst($filtroPeriodo->monthName) }} {{ $filtroPeriodo->year }}
    </span>
    @endif
    @if(request('forma_pago'))
    <span style="background:#fff8e1;color:#b45309;font-size:11px;font-weight:700;padding:3px 10px;border-radius:10px;">
        <i class="fa fa-credit-card" style="margin-right:4px;"></i>{{ ucfirst(request('forma_pago')) }}
    </span>
    @endif
</div>
@endif

{{-- ══ HERO STATS ══ --}}
<div class="di-hero no-print">
    <div class="di-stat">
        <div class="di-stat-num">${{ number_format($resumen['total_cobrado'], 2) }}</div>
        <div class="di-stat-lbl">Total ingresado</div>
    </div>
    <div class="di-stat">
        <div class="di-stat-num">{{ $resumen['total_pagos'] }}</div>
        <div class="di-stat-lbl">Recibos vigentes</div>
    </div>
    <div class="di-stat">
        <div class="di-stat-num">{{ $resumen['total_conceptos'] }}</div>
        <div class="di-stat-lbl">Conceptos / períodos</div>
    </div>
    <div style="margin-left:auto;text-align:right;">
        <div style="font-size:11px;color:rgba(255,255,255,.55);">Rango de fechas de pago</div>
        <div style="font-size:13px;font-weight:700;color:#fff;margin-top:2px;">
            {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }}
            &ndash;
            {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
        </div>
    </div>
</div>

@if($resumen['total_cobrado'] == 0)
{{-- Sin datos --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            padding:60px 20px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.04);">
    <i class="fa fa-inbox" style="font-size:48px;color:#dde4ea;display:block;margin-bottom:14px;"></i>
    <p style="color:#b0bec5;font-size:15px;font-weight:600;margin:0;">Sin ingresos en el período</p>
    <p style="color:#b0bec5;font-size:12px;margin:6px 0 0;">
        No se encontraron pagos vigentes con los filtros seleccionados.
    </p>
</div>

@else

{{-- ══ RESUMEN POR CONCEPTO + PERÍODO ══ --}}
<div class="di-panel">
    <div class="di-panel-header">
        <i class="fa fa-pie-chart" style="color:#27ae60;font-size:13px;"></i>
        <span class="di-panel-title">Resumen por concepto y período de cargo</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="di-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Tipo</th>
                    <th>Período del cargo</th>
                    <th style="text-align:center;">Cargos cobrados</th>
                    <th style="text-align:right;">Total ingresado</th>
                    <th style="text-align:right;">% del total</th>
                </tr>
            </thead>
            <tbody>
            @php
                $tipoConfig = [
                    'colegiatura'      => ['label'=>'Colegiatura',   'bg'=>'#e8f0fb','color'=>'#3c8dbc'],
                    'inscripcion'      => ['label'=>'Inscripción',   'bg'=>'#e8f8f0','color'=>'#00875a'],
                    'cargo_unico'      => ['label'=>'Cargo único',   'bg'=>'#fff8e1','color'=>'#b45309'],
                    'cargo_recurrente' => ['label'=>'Recurrente',    'bg'=>'#f3e8fd','color'=>'#7c3aed'],
                ];
            @endphp
            @foreach($porConcepto as $fila)
            @php
                $tc  = $tipoConfig[$fila['concepto']->tipo] ?? ['label'=>ucfirst($fila['concepto']->tipo),'bg'=>'#f0f3f7','color'=>'#6b7a8d'];
                $pct = $resumen['total_cobrado'] > 0
                    ? round($fila['total'] / $resumen['total_cobrado'] * 100, 1) : 0;
            @endphp
            <tr>
                <td>
                    <div style="font-weight:600;color:#1a2634;">{{ $fila['concepto']->nombre }}</div>
                    @if($fila['concepto']->descripcion)
                    <div style="font-size:11px;color:#8a9ab0;">{{ $fila['concepto']->descripcion }}</div>
                    @endif
                </td>
                <td>
                    <span class="tipo-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">
                        {{ $tc['label'] }}
                    </span>
                </td>
                <td>
                    @if($fila['periodo'])
                        <span class="periodo-badge">{{ $fila['periodo_label'] }}</span>
                    @else
                        <span style="font-size:12px;color:#b0bec5;">Sin período</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                                 padding:2px 8px;border-radius:8px;">
                        {{ $fila['cantidad'] }}
                    </span>
                </td>
                <td style="text-align:right;font-weight:700;color:#1a2634;font-size:14px;">
                    ${{ number_format($fila['total'], 2) }}
                </td>
                <td style="text-align:right;">
                    <div style="display:flex;align-items:center;gap:8px;justify-content:flex-end;">
                        <div style="flex:1;max-width:80px;height:6px;background:#f0f3f7;border-radius:3px;overflow:hidden;">
                            <div style="width:{{ $pct }}%;height:100%;background:#27ae60;border-radius:3px;"></div>
                        </div>
                        <span style="font-size:12px;color:#6b7a8d;min-width:36px;text-align:right;">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="font-weight:700;color:#1a2634;">Total</td>
                    <td style="text-align:center;font-weight:700;color:#1a2634;">
                        {{ $porConcepto->sum('cantidad') }}
                    </td>
                    <td style="text-align:right;font-size:15px;font-weight:800;color:#1a2634;">
                        ${{ number_format($resumen['total_cobrado'], 2) }}
                    </td>
                    <td style="text-align:right;font-weight:700;color:#1a2634;">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- ══ DETALLE DE PAGOS ══ --}}
<div class="di-panel">
    <div class="di-panel-header">
        <i class="fa fa-list" style="color:#27ae60;font-size:13px;"></i>
        <span class="di-panel-title">Detalle de recibos ({{ $pagosUnicos->count() }})</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="di-table">
            <thead>
                <tr>
                    <th>Fecha pago</th>
                    <th>Folio</th>
                    <th>Alumno(s)</th>
                    <th>Concepto / Período</th>
                    <th>Forma de pago</th>
                    <th style="text-align:right;">Monto</th>
                    <th class="no-print" style="text-align:center;width:50px;"></th>
                </tr>
            </thead>
            <tbody>
            @php
                $formaConfig = [
                    'efectivo'      => ['icon'=>'fa-money',       'bg'=>'#e8f8f0','color'=>'#00875a','label'=>'Efectivo'],
                    'transferencia' => ['icon'=>'fa-exchange',    'bg'=>'#e8f0fb','color'=>'#3c8dbc','label'=>'Transferencia'],
                    'tarjeta'       => ['icon'=>'fa-credit-card', 'bg'=>'#f3e8fd','color'=>'#7c3aed','label'=>'Tarjeta'],
                    'cheque'        => ['icon'=>'fa-file-text-o', 'bg'=>'#fff8e1','color'=>'#b45309','label'=>'Cheque'],
                ];
            @endphp
            @foreach($pagosUnicos as $pago)
            @php
                $alumnos = $pago->detalles
                    ->map(fn($d) => $d->cargo?->inscripcion?->alumno)
                    ->filter()->unique('id')->values();
                $fi = $formaConfig[$pago->forma_pago] ?? ['icon'=>'fa-question','bg'=>'#f0f3f7','color'=>'#6b7a8d','label'=>ucfirst($pago->forma_pago)];
            @endphp
            <tr>
                <td style="white-space:nowrap;font-size:12px;color:#4a5568;">
                    {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                </td>
                <td>
                    <code style="font-size:11px;background:#f0f3f7;padding:2px 6px;border-radius:4px;color:#1a2634;font-weight:700;">
                        {{ $pago->folio_recibo }}
                    </code>
                </td>
                <td>
                    @if($alumnos->isEmpty())
                        <span style="color:#b0bec5;">—</span>
                    @else
                        <div style="font-size:12px;font-weight:600;color:#1a2634;">
                            {{ $alumnos->first()->ap_paterno }} {{ $alumnos->first()->ap_materno }},
                            {{ $alumnos->first()->nombre }}
                        </div>
                        @if($alumnos->count() > 1)
                        <div style="font-size:11px;color:#8a9ab0;">+{{ $alumnos->count() - 1 }} más</div>
                        @endif
                    @endif
                </td>
                <td>
                    @foreach($pago->detalles->unique(fn($d) => ($d->cargo?->concepto_id ?? 0).':'.($d->cargo?->periodo ?? '')) as $det)
                    <div style="font-size:11px;color:#4a5568;line-height:1.7;">
                        {{ $det->cargo?->concepto?->nombre ?? '—' }}
                        @if($det->cargo?->periodo)
                            <span class="periodo-badge" style="font-size:10px;padding:1px 5px;">
                                {{ $det->cargo->periodo_label }}
                            </span>
                        @endif
                    </div>
                    @endforeach
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;">
                        <span style="width:22px;height:22px;border-radius:5px;
                                     background:{{ $fi['bg'] }};color:{{ $fi['color'] }};
                                     display:inline-flex;align-items:center;justify-content:center;font-size:10px;">
                            <i class="fa {{ $fi['icon'] }}"></i>
                        </span>
                        <span style="font-size:12px;color:#4a5568;">{{ $fi['label'] }}</span>
                    </span>
                </td>
                <td style="text-align:right;font-weight:700;color:#1a2634;font-size:13px;">
                    ${{ number_format($pago->monto_total, 2) }}
                </td>
                <td class="no-print" style="text-align:center;">
                    <a href="{{ route('pagos.show', $pago->id) }}"
                       class="btn btn-xs btn-default btn-flat" style="border-radius:5px;" title="Ver recibo">
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="font-weight:700;color:#1a2634;">Total</td>
                    <td style="text-align:right;font-size:15px;font-weight:800;color:#1a2634;">
                        ${{ number_format($resumen['total_cobrado'], 2) }}
                    </td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endif{{-- /total_cobrado > 0 --}}

@endsection
