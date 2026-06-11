@extends('layouts.master')

@section('page_title', 'Dashboard Caja')
@section('page_subtitle', now()->isoFormat('dddd D [de] MMMM [de] YYYY'))

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@push('styles')
<style>
/* ── KPI Cards ─────────────────────────────── */
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 14px; margin-bottom: 22px; }
.kpi-card {
    background: #fff; border: 1px solid #e4eaf0; border-radius: 10px;
    padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; flex-direction: column; gap: 4px;
}
.kpi-label  { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #8a9ab0; }
.kpi-value  { font-size: 26px; font-weight: 800; line-height: 1.1; color: #1a2634; }
.kpi-sub    { font-size: 11px; color: #b0bec5; margin-top: 2px; }
.kpi-icon   {
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; margin-bottom: 6px;
}

/* ── Section cards ────────────────────────── */
.dash-card {
    background: #fff; border: 1px solid #e4eaf0; border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 20px;
}
.dash-card-header {
    padding: 11px 16px; background: #f8fafc; border-bottom: 1px solid #e8ecf0;
    display: flex; align-items: center; gap: 8px;
}
.dash-card-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7a8d;
}

/* ── Table ────────────────────────────────── */
.dt { width: 100%; border-collapse: collapse; }
.dt thead th {
    background: #f4f6f8; color: #6b7a8d; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    padding: 8px 12px; border-bottom: 2px solid #e4eaf0; white-space: nowrap;
}
.dt tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
.dt tbody tr:last-child { border-bottom: none; }
.dt tbody tr:hover td { background: #f8fbff; }
.dt td { padding: 9px 12px; font-size: 13px; vertical-align: middle; }

/* ── Forma pago pills ─────────────────────── */
.fp-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 10px; white-space: nowrap;
}
</style>
@endpush

@section('content')

{{-- ══ SALUDO ══ --}}
<div style="margin-bottom:20px;">
    <h2 style="font-size:20px;font-weight:800;color:#1a2634;margin:0;">
        Hola, {{ auth()->user()->nombre }} <span style="font-weight:400;color:#8a9ab0;">— Caja</span>
    </h2>
    <p style="font-size:13px;color:#b0bec5;margin:4px 0 0;">
        Resumen de operaciones al día de hoy
    </p>
</div>

{{-- ══ KPIs PRINCIPALES ══ --}}
<div class="kpi-grid">

    {{-- Cobrado hoy --}}
    <div class="kpi-card" style="border-left:4px solid #27a05a;">
        <div class="kpi-icon" style="background:#e8f8f0;color:#00875a;">
            <i class="fa fa-dollar"></i>
        </div>
        <div class="kpi-label">Cobrado hoy</div>
        <div class="kpi-value" style="color:#00875a;">${{ number_format($cobradoHoy, 2) }}</div>
        <div class="kpi-sub">
            {{ $pagosHoy }} pago(s) &nbsp;·&nbsp;
            Ayer: ${{ number_format($cobradoAyer, 2) }}
        </div>
    </div>

    {{-- Cobrado este mes --}}
    <div class="kpi-card" style="border-left:4px solid #3c8dbc;">
        <div class="kpi-icon" style="background:#e8f0fb;color:#2980b9;">
            <i class="fa fa-calendar"></i>
        </div>
        <div class="kpi-label">Cobrado {{ now()->isoFormat('MMMM') }}</div>
        <div class="kpi-value" style="color:#2980b9;">${{ number_format($cobradoMes, 2) }}</div>
        <div class="kpi-sub">Mes {{ now()->format('m/Y') }}</div>
    </div>

    {{-- Cargos vencidos --}}
    <div class="kpi-card" style="border-left:4px solid #e74c3c;">
        <div class="kpi-icon" style="background:#fdecea;color:#c0392b;">
            <i class="fa fa-exclamation-circle"></i>
        </div>
        <div class="kpi-label">Cargos vencidos</div>
        <div class="kpi-value" style="color:#c0392b;">{{ number_format($cargosVencidos) }}</div>
        <div class="kpi-sub">${{ number_format($montoVencido, 2) }} pendiente</div>
    </div>

    {{-- Cargos pendientes --}}
    <div class="kpi-card" style="border-left:4px solid #f39c12;">
        <div class="kpi-icon" style="background:#fff8e1;color:#b45309;">
            <i class="fa fa-clock-o"></i>
        </div>
        <div class="kpi-label">Cargos pendientes</div>
        <div class="kpi-value" style="color:#b45309;">{{ number_format($cargosPendientes) }}</div>
        <div class="kpi-sub">${{ number_format($montoPendiente, 2) }} por cobrar</div>
    </div>

    {{-- Facturas del mes --}}
    <div class="kpi-card" style="border-left:4px solid #7b2d8b;">
        <div class="kpi-icon" style="background:#f3e8fd;color:#7b2d8b;">
            <i class="fa fa-file-text-o"></i>
        </div>
        <div class="kpi-label">CFDIs {{ now()->isoFormat('MMMM') }}</div>
        <div class="kpi-value" style="color:#7b2d8b;">{{ number_format($cfdisMes) }}</div>
        <div class="kpi-sub">{{ $cfdisGlobalesMes }} global(es) &nbsp;·&nbsp; {{ $cfdisMes - $cfdisGlobalesMes }} individual(es)</div>
    </div>

    {{-- Sin factura hoy --}}
    <div class="kpi-card" style="border-left:4px solid {{ $pagosSinFacturaHoy > 0 ? '#e67e22' : '#27a05a' }};">
        <div class="kpi-icon" style="background:{{ $pagosSinFacturaHoy > 0 ? '#fff3e0' : '#e8f8f0' }};color:{{ $pagosSinFacturaHoy > 0 ? '#c0392b' : '#00875a' }};">
            <i class="fa fa-{{ $pagosSinFacturaHoy > 0 ? 'warning' : 'check-circle' }}"></i>
        </div>
        <div class="kpi-label">Sin factura hoy</div>
        <div class="kpi-value" style="color:{{ $pagosSinFacturaHoy > 0 ? '#e67e22' : '#00875a' }};">
            {{ $pagosSinFacturaHoy }}
        </div>
        <div class="kpi-sub">
            @if($pagosSinFacturaHoy > 0)
                <a href="{{ route('pagos.index', ['fecha_desde' => now()->toDateString(), 'fecha_hasta' => now()->toDateString()]) }}"
                   style="color:#e67e22;">Ver pagos →</a>
            @else
                Todos facturados
            @endif
        </div>
    </div>

</div>

<div class="row">

{{-- ══ COLUMNA IZQUIERDA ══ --}}
<div class="col-md-8">

    {{-- Pagos del día --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-list" style="color:#27a05a;"></i>
            <span class="dash-card-title">Pagos de hoy</span>
            <span style="background:#e8f5ee;color:#27a05a;font-size:11px;font-weight:700;
                         padding:2px 8px;border-radius:10px;margin-left:2px;">
                {{ $ultimosPagos->count() }}
            </span>
            <a href="{{ route('pagos.index', ['fecha_desde' => now()->toDateString(), 'fecha_hasta' => now()->toDateString()]) }}"
               style="margin-left:auto;font-size:11px;color:#3c8dbc;text-decoration:none;">
                Ver todos <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        @if($ultimosPagos->isEmpty())
        <div style="padding:40px;text-align:center;color:#b0bec5;">
            <i class="fa fa-inbox" style="font-size:36px;display:block;margin-bottom:10px;"></i>
            Sin cobros registrados hoy
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Alumno(s)</th>
                        <th>Forma</th>
                        <th style="text-align:right;">Monto</th>
                        <th style="text-align:center;">CFDI</th>
                        <th style="text-align:center;width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($ultimosPagos as $pago)
                @php
                    $alumnos = $pago->detalles
                        ->map(fn($d) => $d->cargo?->inscripcion?->alumno)
                        ->filter()->unique('id')->values();
                    $formaIconos = [
                        'efectivo'      => ['icon'=>'fa-money',       'bg'=>'#e8f8f0','color'=>'#00875a'],
                        'transferencia' => ['icon'=>'fa-exchange',    'bg'=>'#e8f0fb','color'=>'#3c8dbc'],
                        'tarjeta'       => ['icon'=>'fa-credit-card', 'bg'=>'#f3e8fd','color'=>'#7c3aed'],
                        'cheque'        => ['icon'=>'fa-file-text-o', 'bg'=>'#fff8e1','color'=>'#b45309'],
                    ];
                    $fi = $formaIconos[$pago->forma_pago] ?? ['icon'=>'fa-question','bg'=>'#f0f3f7','color'=>'#6b7a8d'];
                    $cfdi = $pago->cfdis->first();
                @endphp
                <tr>
                    <td>
                        <code style="font-size:11px;background:#f0f3f7;padding:2px 7px;
                                     border-radius:4px;font-weight:700;color:#1a2634;">
                            {{ $pago->folio_recibo }}
                        </code>
                    </td>
                    <td>
                        @if($alumnos->isEmpty())
                            <span style="color:#b0bec5;">—</span>
                        @else
                            <div style="font-weight:600;color:#1a2634;font-size:12px;">
                                {{ $alumnos->first()->ap_paterno }}, {{ $alumnos->first()->nombre }}
                            </div>
                            @if($alumnos->count() > 1)
                                <div style="font-size:11px;color:#b0bec5;">+{{ $alumnos->count() - 1 }} más</div>
                            @endif
                        @endif
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;">
                            <span style="width:24px;height:24px;border-radius:6px;background:{{ $fi['bg'] }};
                                         color:{{ $fi['color'] }};display:inline-flex;align-items:center;
                                         justify-content:center;font-size:11px;">
                                <i class="fa {{ $fi['icon'] }}"></i>
                            </span>
                            <span style="font-size:11px;color:#4a5568;">{{ ucfirst($pago->forma_pago) }}</span>
                        </span>
                    </td>
                    <td style="text-align:right;font-weight:700;color:#1a2634;">
                        ${{ number_format($pago->monto_total, 2) }}
                    </td>
                    <td style="text-align:center;">
                        @if($cfdi)
                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;
                                         font-weight:700;background:#e8f5ee;color:#00875a;
                                         padding:2px 7px;border-radius:8px;border:1px solid #b3e8d0;">
                                <i class="fa fa-check" style="font-size:9px;"></i> {{ $cfdi->folio }}
                            </span>
                        @else
                            <span style="font-size:10px;color:#b0bec5;">Sin CFDI</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ route('pagos.show', $pago->id) }}"
                           class="btn btn-xs btn-default btn-flat" style="border-radius:5px;"
                           title="Ver detalle"><i class="fa fa-eye"></i></a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Top deudores --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-exclamation-triangle" style="color:#e74c3c;"></i>
            <span class="dash-card-title">Cargos vencidos más antiguos</span>
            <a href="{{ route('reportes.deudores') }}"
               style="margin-left:auto;font-size:11px;color:#3c8dbc;text-decoration:none;">
                Reporte completo <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        @if($topDeudores->isEmpty())
        <div style="padding:32px;text-align:center;color:#b0bec5;">
            <i class="fa fa-check-circle" style="font-size:30px;color:#b3e8d0;display:block;margin-bottom:8px;"></i>
            Sin cargos vencidos
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Concepto</th>
                        <th>Vencimiento</th>
                        <th style="text-align:right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($topDeudores as $cargo)
                <tr>
                    <td>
                        @php $alumno = $cargo->inscripcion?->alumno; @endphp
                        @if($alumno)
                            <a href="{{ route('alumnos.show', $alumno->id) }}"
                               style="font-weight:600;color:#1a2634;font-size:12px;text-decoration:none;">
                                {{ $alumno->ap_paterno }}, {{ $alumno->nombre }}
                            </a>
                            <div style="font-size:11px;color:#b0bec5;">{{ $alumno->matricula }}</div>
                        @else
                            <span style="color:#b0bec5;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#4a5568;">
                        {{ $cargo->concepto?->nombre ?? '—' }}
                        @if($cargo->periodo)
                            <code style="font-size:10px;background:#f0f3f7;padding:1px 5px;border-radius:3px;">
                                {{ $cargo->periodo }}
                            </code>
                        @endif
                    </td>
                    <td>
                        @php $dias = now()->diffInDays($cargo->fecha_vencimiento, false) * -1; @endphp
                        <span style="font-size:12px;color:#b91c1c;font-weight:600;">
                            {{ $cargo->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                        <div style="font-size:10px;color:#fca5a5;">
                            hace {{ abs((int)$dias) }} día(s)
                        </div>
                    </td>
                    <td style="text-align:right;font-weight:700;color:#b91c1c;">
                        ${{ number_format($cargo->monto_original, 2) }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>{{-- /col-md-8 --}}

{{-- ══ COLUMNA DERECHA ══ --}}
<div class="col-md-4">

    {{-- Desglose por forma de pago --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-pie-chart" style="color:#3c8dbc;"></i>
            <span class="dash-card-title">Desglose hoy</span>
        </div>
        <div style="padding:14px 16px;">
        @php
            $formasConfig = [
                'efectivo'      => ['label'=>'Efectivo',      'icon'=>'fa-money',       'color'=>'#00875a','bg'=>'#e8f8f0'],
                'transferencia' => ['label'=>'Transferencia', 'icon'=>'fa-exchange',    'color'=>'#2980b9','bg'=>'#e8f0fb'],
                'tarjeta'       => ['label'=>'Tarjeta',       'icon'=>'fa-credit-card', 'color'=>'#7c3aed','bg'=>'#f3e8fd'],
                'cheque'        => ['label'=>'Cheque',        'icon'=>'fa-file-text-o', 'color'=>'#b45309','bg'=>'#fff8e1'],
            ];
        @endphp
        @forelse($formasConfig as $forma => $cfg)
        @php $fp = $porFormaPago->get($forma); @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:9px 0;
                    border-bottom:1px solid #f4f6f8;
                    {{ $loop->last ? 'border-bottom:none;' : '' }}">
            <span style="width:32px;height:32px;border-radius:8px;background:{{ $cfg['bg'] }};
                         color:{{ $cfg['color'] }};display:flex;align-items:center;
                         justify-content:center;font-size:13px;flex-shrink:0;">
                <i class="fa {{ $cfg['icon'] }}"></i>
            </span>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:700;color:#1a2634;">{{ $cfg['label'] }}</div>
                <div style="font-size:11px;color:#b0bec5;">{{ $fp ? $fp->cantidad . ' pago(s)' : '—' }}</div>
            </div>
            <div style="text-align:right;font-weight:700;font-size:14px;
                        color:{{ $fp ? $cfg['color'] : '#dde4eb' }};">
                {{ $fp ? '$'.number_format($fp->total, 2) : '—' }}
            </div>
        </div>
        @empty
        @endforelse

        {{-- Total --}}
        <div style="display:flex;justify-content:space-between;align-items:center;
                    margin-top:12px;padding-top:12px;border-top:2px solid #e4eaf0;">
            <span style="font-size:12px;font-weight:700;color:#1a2634;">Total cobrado hoy</span>
            <span style="font-size:18px;font-weight:800;color:#00875a;">
                ${{ number_format($cobradoHoy, 2) }}
            </span>
        </div>
        </div>
    </div>

    {{-- Accesos rápidos --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-bolt" style="color:#f39c12;"></i>
            <span class="dash-card-title">Acciones rápidas</span>
        </div>
        <div>
            @php
                $acciones = [
                    ['route' => 'cobros.index',     'icon' => 'fa-shopping-cart', 'label' => 'Registrar cobro',     'color' => '#27a05a', 'bg' => '#e8f8f0'],
                    ['route' => 'pagos.corte',       'icon' => 'fa-print',         'label' => 'Corte del día',       'color' => '#2980b9', 'bg' => '#e8f0fb'],
                    ['route' => 'facturas.index',    'icon' => 'fa-file-text-o',   'label' => 'Facturas CFDI',       'color' => '#7b2d8b', 'bg' => '#f3e8fd'],
                    ['route' => 'reportes.deudores', 'icon' => 'fa-users',         'label' => 'Reporte deudores',    'color' => '#c0392b', 'bg' => '#fdecea'],
                    ['route' => 'pagos.index',       'icon' => 'fa-history',       'label' => 'Historial de pagos',  'color' => '#6b7a8d', 'bg' => '#f0f3f7'],
                    ['route' => 'cargos.index',      'icon' => 'fa-file-text',     'label' => 'Ver cargos',          'color' => '#b45309', 'bg' => '#fff8e1'],
                ];
            @endphp
            @foreach($acciones as $acc)
            <a href="{{ route($acc['route']) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;
                      border-bottom:1px solid #f4f6f8;text-decoration:none;color:#1a2634;
                      font-size:13px;transition:background .12s;"
               onmouseover="this.style.background='#f8fbff'" onmouseout="this.style.background=''">
                <span style="width:30px;height:30px;border-radius:7px;background:{{ $acc['bg'] }};
                             color:{{ $acc['color'] }};display:flex;align-items:center;
                             justify-content:center;font-size:12px;flex-shrink:0;">
                    <i class="fa {{ $acc['icon'] }}"></i>
                </span>
                <span style="font-weight:500;">{{ $acc['label'] }}</span>
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endforeach
        </div>
    </div>

</div>{{-- /col-md-4 --}}

</div>{{-- /row --}}

@endsection
