@extends('layouts.master')

@section('page_title', 'Corte del día')
@section('page_subtitle', \Carbon\Carbon::parse($fecha)->translatedFormat('l, d \d\e F \d\e Y'))

@section('breadcrumb')
    <li><a href="{{ route('pagos.index') }}">Historial de pagos</a></li>
    <li class="active">Corte del día</li>
@endsection

@push('styles')
<style>
/* ════ HERO ════ */
.corte-hero {
    background: linear-gradient(135deg, #1a4f7a 0%, #2471a3 100%);
    border-radius: 8px; padding: 18px 24px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 0; flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(36,113,163,.22);
}
.corte-stat { text-align:center; padding: 0 22px; border-left:1px solid rgba(255,255,255,.18); }
.corte-stat:first-child { border-left:none; padding-left:0; }
.corte-stat-num { font-size:24px; font-weight:800; color:#fff; line-height:1; }
.corte-stat-lbl { font-size:10px; color:rgba(255,255,255,.6); margin-top:3px;
                  text-transform:uppercase; letter-spacing:.06em; }

/* ════ FORMA PAGO CARDS ════ */
.fp-cards { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.fp-card {
    flex:1; min-width:140px;
    border:1px solid #e4eaf0; border-radius:10px; background:#fff;
    padding:14px 18px; box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.fp-card-icon { width:36px; height:36px; border-radius:9px;
                display:flex; align-items:center; justify-content:center; margin-bottom:10px; }
.fp-card-total { font-size:20px; font-weight:800; color:#1a2634; line-height:1; }
.fp-card-lbl   { font-size:11px; color:#8a9ab0; margin-top:3px; }
.fp-card-qty   { font-size:11px; color:#8a9ab0; margin-top:6px; }

/* ════ TABLA ════ */
.corte-table { width:100%; border-collapse:collapse; }
.corte-table thead th {
    background:#f4f6f8; color:#6b7a8d;
    font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
    padding:8px 12px; border-bottom:2px solid #e4eaf0; white-space:nowrap;
}
.corte-table tbody tr { border-bottom:1px solid #f0f3f7; }
.corte-table tbody tr:hover td { background:#f5f9ff; }
.corte-table td { padding:9px 12px; vertical-align:middle; font-size:13px; }
.corte-table tfoot td {
    padding:10px 12px; font-size:13px;
    border-top:2px solid #e4eaf0; background:#f8fafc; font-weight:700;
}

/* ════ CAJERO SECTION (admin) ════ */
.cajero-section { margin-bottom:24px; }
.cajero-header {
    padding:10px 16px; background:#f0f7ff; border:1px solid #d0e8fb;
    border-radius:8px 8px 0 0; display:flex; align-items:center; gap:10px;
}

/* ════ IMPRESIÓN ════ */
@media print {
    .main-header, .main-sidebar, .content-header, .breadcrumb,
    .no-print, .main-footer { display:none !important; }
    .content-wrapper { margin-left:0 !important; padding:0 !important; }
    .print-header { display:block !important; }
    body { background:#fff !important; font-size:12px; }
    .corte-hero, .fp-cards { display:none !important; }
    .corte-table thead th { background:#eee !important; -webkit-print-color-adjust:exact; }
    .corte-table tbody tr:hover td { background:transparent !important; }
    a { color:inherit !important; text-decoration:none !important; }
    .cajero-header { background:#eee !important; -webkit-print-color-adjust:exact; }
}
.print-header { display:none; }
</style>
@endpush

@section('content')

{{-- ══ CABECERA IMPRESIÓN ══ --}}
<div class="print-header" style="text-align:center;margin-bottom:20px;border-bottom:2px solid #333;padding-bottom:12px;">
    <h2 style="margin:0;font-size:18px;">CORTE DEL DÍA</h2>
    <p style="margin:4px 0 0;font-size:13px;color:#555;">
        {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l, d \d\e F \d\e Y') }}
        @if(!$esAdmin) &nbsp;·&nbsp; Cajero: {{ auth()->user()->nombre }} @endif
    </p>
    <p style="margin:2px 0 0;font-size:11px;color:#888;">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- ══ NAVEGACIÓN DE FECHA ══ --}}
<div class="no-print" style="display:flex;align-items:center;justify-content:space-between;
                               flex-wrap:wrap;gap:10px;margin-bottom:18px;">
    <div style="display:flex;align-items:center;gap:8px;">
        <a href="{{ route('pagos.corte') }}?fecha={{ \Carbon\Carbon::parse($fecha)->subDay()->toDateString() }}{{ $esAdmin && request('cajero_id') ? '&cajero_id='.request('cajero_id') : '' }}"
           class="btn btn-sm btn-default btn-flat" style="border-radius:6px;">
            <i class="fa fa-chevron-left"></i>
        </a>
        <form method="GET" action="{{ route('pagos.corte') }}" style="display:flex;gap:6px;align-items:center;">
            <input type="date" name="fecha" value="{{ $fecha }}"
                   class="form-control input-sm"
                   onchange="this.form.submit()"
                   style="border-radius:6px;border-color:#dde4eb;width:160px;">
            @if($esAdmin && request('cajero_id'))
                <input type="hidden" name="cajero_id" value="{{ request('cajero_id') }}">
            @endif
        </form>
        <a href="{{ route('pagos.corte') }}?fecha={{ \Carbon\Carbon::parse($fecha)->addDay()->toDateString() }}{{ $esAdmin && request('cajero_id') ? '&cajero_id='.request('cajero_id') : '' }}"
           class="btn btn-sm btn-default btn-flat"
           style="border-radius:6px;{{ \Carbon\Carbon::parse($fecha)->isToday() ? 'opacity:.4;pointer-events:none;' : '' }}">
            <i class="fa fa-chevron-right"></i>
        </a>
        @if(!\Carbon\Carbon::parse($fecha)->isToday())
        <a href="{{ route('pagos.corte') }}{{ $esAdmin && request('cajero_id') ? '?cajero_id='.request('cajero_id') : '' }}"
           class="btn btn-sm btn-default btn-flat" style="border-radius:6px;font-size:11px;">
            Hoy
        </a>
        @endif
    </div>

    <div style="display:flex;gap:8px;align-items:center;">
        @if($esAdmin && $cajeros)
        <form method="GET" action="{{ route('pagos.corte') }}" style="display:flex;gap:6px;align-items:center;">
            <input type="hidden" name="fecha" value="{{ $fecha }}">
            <select name="cajero_id" class="form-control input-sm"
                    onchange="this.form.submit()"
                    style="border-radius:6px;border-color:#dde4eb;min-width:180px;">
                <option value="">Todos los cajeros</option>
                @foreach($cajeros as $c)
                <option value="{{ $c->id }}" {{ request('cajero_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->nombre }}
                </option>
                @endforeach
            </select>
        </form>
        @endif
        <button onclick="window.print()" class="btn btn-sm btn-default btn-flat" style="border-radius:6px;">
            <i class="fa fa-print"></i> Imprimir
        </button>
        <a href="{{ route('pagos.corte.pdf') }}?fecha={{ $fecha }}{{ $esAdmin && request('cajero_id') ? '&cajero_id='.request('cajero_id') : '' }}"
           target="_blank"
           class="btn btn-sm btn-danger btn-flat" style="border-radius:6px;">
            <i class="fa fa-file-pdf-o"></i> PDF
        </a>
        <a href="{{ route('pagos.index') }}?fecha_desde={{ $fecha }}&fecha_hasta={{ $fecha }}"
           class="btn btn-sm btn-default btn-flat" style="border-radius:6px;">
            <i class="fa fa-list"></i> Ver en historial
        </a>
    </div>
</div>

{{-- ══ HERO STATS ══ --}}
<div class="corte-hero no-print">
    <div class="corte-stat">
        <div class="corte-stat-num">${{ number_format($resumen['total_cobrado'], 2) }}</div>
        <div class="corte-stat-lbl">Total cobrado</div>
    </div>
    <div class="corte-stat">
        <div class="corte-stat-num">{{ $resumen['total_pagos'] }}</div>
        <div class="corte-stat-lbl">Recibos vigentes</div>
    </div>
    <div class="corte-stat">
        <div class="corte-stat-num">{{ $resumen['total_cargos'] }}</div>
        <div class="corte-stat-lbl">Conceptos</div>
    </div>
    @if($resumen['total_anulados'] > 0)
    <div class="corte-stat">
        <div class="corte-stat-num" style="color:#ffcdd2;">{{ $resumen['total_anulados'] }}</div>
        <div class="corte-stat-lbl">Anulados</div>
    </div>
    @endif
    <div style="margin-left:auto;">
        <div style="font-size:11px;color:rgba(255,255,255,.55);">
            @if(!$esAdmin) {{ auth()->user()->nombre }} @else Todos los cajeros @endif
        </div>
        <div style="font-size:14px;font-weight:700;color:#fff;margin-top:2px;">
            {{ \Carbon\Carbon::parse($fecha)->translatedFormat('d \d\e F, Y') }}
        </div>
    </div>
</div>

@if($resumen['total_pagos'] === 0)
{{-- Sin pagos --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            padding:60px 20px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.04);">
    <i class="fa fa-inbox" style="font-size:48px;color:#dde4ea;display:block;margin-bottom:14px;"></i>
    <p style="color:#b0bec5;font-size:15px;font-weight:600;margin:0;">Sin pagos registrados</p>
    <p style="color:#b0bec5;font-size:12px;margin:6px 0 0;">
        No hay pagos vigentes para el {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
    </p>
</div>
@else

{{-- ══ DESGLOSE POR FORMA DE PAGO ══ --}}
@php
    $formaConfig = [
        'efectivo'      => ['icon'=>'fa-money',       'bg'=>'#e8f8f0','color'=>'#00875a','label'=>'Efectivo'],
        'transferencia' => ['icon'=>'fa-exchange',    'bg'=>'#e8f0fb','color'=>'#3c8dbc','label'=>'Transferencia'],
        'tarjeta'       => ['icon'=>'fa-credit-card', 'bg'=>'#f3e8fd','color'=>'#7c3aed','label'=>'Tarjeta'],
        'cheque'        => ['icon'=>'fa-file-text-o', 'bg'=>'#fff8e1','color'=>'#b45309','label'=>'Cheque'],
    ];
@endphp
<div class="fp-cards no-print">
    @foreach($formaConfig as $forma => $cfg)
    @if(isset($resumen['por_forma_pago'][$forma]))
    @php $fp = $resumen['por_forma_pago'][$forma]; @endphp
    <div class="fp-card">
        <div class="fp-card-icon" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
            <i class="fa {{ $cfg['icon'] }}" style="font-size:15px;"></i>
        </div>
        <div class="fp-card-total">${{ number_format($fp['total'], 2) }}</div>
        <div class="fp-card-lbl">{{ $cfg['label'] }}</div>
        <div class="fp-card-qty">{{ $fp['cantidad'] }} recibo{{ $fp['cantidad'] != 1 ? 's' : '' }}</div>
    </div>
    @endif
    @endforeach
</div>

@if($esAdmin && $porCajero && $porCajero->count() > 1)
{{-- ══ RESUMEN POR CAJERO (admin) ══ --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;margin-bottom:20px;">
    <div style="padding:11px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6b7a8d;">
            <i class="fa fa-users" style="color:#3c8dbc;margin-right:5px;"></i>Desglose por cajero
        </span>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="padding:8px 16px;font-size:11px;font-weight:700;text-transform:uppercase;
                           letter-spacing:.05em;color:#6b7a8d;background:#f4f6f8;border-bottom:2px solid #e4eaf0;">Cajero</th>
                <th style="padding:8px 16px;text-align:center;font-size:11px;font-weight:700;text-transform:uppercase;
                           letter-spacing:.05em;color:#6b7a8d;background:#f4f6f8;border-bottom:2px solid #e4eaf0;">Recibos</th>
                <th style="padding:8px 16px;text-align:right;font-size:11px;font-weight:700;text-transform:uppercase;
                           letter-spacing:.05em;color:#6b7a8d;background:#f4f6f8;border-bottom:2px solid #e4eaf0;">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($porCajero as $grupo)
        <tr style="border-bottom:1px solid #f0f3f7;">
            <td style="padding:9px 16px;font-size:13px;">
                <div style="font-weight:600;color:#1a2634;">{{ $grupo['cajero']?->nombre ?? '—' }}</div>
            </td>
            <td style="padding:9px 16px;text-align:center;color:#4a5568;">{{ $grupo['cantidad'] }}</td>
            <td style="padding:9px 16px;text-align:right;font-weight:700;color:#1a2634;">
                ${{ number_format($grupo['total'], 2) }}
            </td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;border-top:2px solid #e4eaf0;">
                <td style="padding:9px 16px;font-weight:700;color:#1a2634;">Total</td>
                <td style="padding:9px 16px;text-align:center;font-weight:700;color:#1a2634;">
                    {{ $resumen['total_pagos'] }}
                </td>
                <td style="padding:9px 16px;text-align:right;font-weight:800;font-size:15px;color:#1a2634;">
                    ${{ number_format($resumen['total_cobrado'], 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

{{-- ══ TABLA DE PAGOS ══ --}}
@php
    $grupos = $esAdmin && $porCajero && $porCajero->count() > 0
        ? $porCajero
        : collect([['cajero' => auth()->user(), 'pagos' => $pagos, 'cantidad' => $pagos->count(), 'total' => $resumen['total_cobrado']]]);
@endphp

@foreach($grupos as $grupo)

@if($esAdmin && $porCajero && $porCajero->count() > 1)
<div class="cajero-section">
    <div class="cajero-header">
        <span style="width:30px;height:30px;border-radius:8px;background:#d0e8fb;
                     display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa fa-user" style="color:#3c8dbc;font-size:13px;"></i>
        </span>
        <span style="font-weight:700;color:#1a2634;font-size:13px;">
            {{ $grupo['cajero']?->nombre ?? '—' }}
        </span>
        <span style="margin-left:8px;background:#d0e8fb;color:#2c6fad;font-size:11px;font-weight:700;
                     padding:2px 8px;border-radius:8px;">
            {{ $grupo['cantidad'] }} recibo{{ $grupo['cantidad'] != 1 ? 's' : '' }}
        </span>
        <span style="margin-left:auto;font-weight:800;color:#1a2634;">
            ${{ number_format($grupo['total'], 2) }}
        </span>
    </div>
@endif

<div style="border:1px solid #e4eaf0;
            border-radius:{{ ($esAdmin && $porCajero && $porCajero->count() > 1) ? '0 0 10px 10px' : '10px' }};
            background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.04);
            overflow:hidden;margin-bottom:{{ ($esAdmin && $porCajero && $porCajero->count() > 1) ? '24px' : '20px' }};">
    <div style="overflow-x:auto;">
        <table class="corte-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Alumno(s)</th>
                    <th>Forma de pago</th>
                    <th>Referencia</th>
                    <th style="text-align:center;">Conceptos</th>
                    <th style="text-align:right;">Monto</th>
                    <th class="no-print" style="text-align:center;width:60px;"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($grupo['pagos'] as $pago)
            @php
                $alumnos = $pago->detalles
                    ->map(fn($d) => $d->cargo?->inscripcion?->alumno)
                    ->filter()->unique('id')->values();
                $fi = $formaConfig[$pago->forma_pago] ?? ['icon'=>'fa-question','bg'=>'#f0f3f7','color'=>'#6b7a8d','label'=>ucfirst($pago->forma_pago)];
            @endphp
            <tr>
                <td>
                    <code style="font-size:12px;background:#f0f3f7;padding:2px 7px;border-radius:4px;color:#1a2634;font-weight:700;">
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
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span style="width:24px;height:24px;border-radius:6px;
                                     background:{{ $fi['bg'] }};color:{{ $fi['color'] }};
                                     display:inline-flex;align-items:center;justify-content:center;font-size:11px;">
                            <i class="fa {{ $fi['icon'] }}"></i>
                        </span>
                        <span style="font-size:12px;color:#4a5568;">{{ $fi['label'] }}</span>
                    </span>
                </td>
                <td style="font-size:12px;color:#8a9ab0;">{{ $pago->referencia ?? '—' }}</td>
                <td style="text-align:center;">
                    <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                                 padding:2px 8px;border-radius:8px;">
                        {{ $pago->detalles->count() }}
                    </span>
                </td>
                <td style="text-align:right;font-weight:700;color:#1a2634;font-size:14px;">
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
                    <td colspan="4"></td>
                    <td style="text-align:center;color:#6b7a8d;font-size:12px;">
                        {{ $grupo['pagos']->sum(fn($p) => $p->detalles->count()) }} conceptos
                    </td>
                    <td style="text-align:right;font-size:15px;font-weight:800;color:#1a2634;">
                        ${{ number_format($grupo['total'], 2) }}
                    </td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if($esAdmin && $porCajero && $porCajero->count() > 1)
</div>{{-- /cajero-section --}}
@endif

@endforeach

@endif{{-- /total_pagos > 0 --}}

@endsection
