@extends('layouts.master')

@section('page_title', 'Estado de cuenta')
@section('page_subtitle', $alumno->ap_paterno . ' ' . $alumno->ap_materno . ', ' . $alumno->nombre . ' · ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li><a href="{{ route('alumnos.show', $alumno->id) }}">{{ $alumno->ap_paterno }}</a></li>
    <li class="active">Estado de cuenta</li>
@endsection

@push('styles')
<style>
/* ════════════════════════════════════════════
   HERO
════════════════════════════════════════════ */
.ec-hero {
    background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
    border-radius: 8px;
    padding: 20px 28px;
    margin-bottom: 22px;
    display: flex; align-items: center; gap: 18px; flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(60,141,188,.25);
}
.ec-hero-foto {
    width: 68px; height: 68px; border-radius: 50%;
    object-fit: cover; border: 3px solid rgba(255,255,255,.5); flex-shrink: 0;
}
.ec-hero-placeholder {
    width: 68px; height: 68px; border-radius: 50%;
    background: rgba(255,255,255,.18); border: 3px solid rgba(255,255,255,.3);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ec-hero-nombre    { font-size: 11px; color: rgba(255,255,255,.7); margin-bottom: 3px; }
.ec-hero-apellidos { font-size: 20px; font-weight: 800; color: #fff; line-height: 1.1; }
.ec-hero-meta      { font-size: 11px; color: rgba(255,255,255,.65); margin-top: 5px;
                     display: flex; gap: 14px; flex-wrap: wrap; }
.ec-hero-matricula {
    background: rgba(0,0,0,.25); color: rgba(255,255,255,.9);
    font-family: monospace; font-size: 11px;
    padding: 1px 9px; border-radius: 10px; letter-spacing: .07em;
}

/* Stats del hero */
.ec-stats { display: flex; gap: 0; margin-left: auto; flex-shrink: 0; }
.ec-stat  {
    text-align: center; padding: 0 20px;
    border-left: 1px solid rgba(255,255,255,.18);
}
.ec-stat:first-child { border-left: none; }
.ec-stat-num { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; }
.ec-stat-lbl { font-size: 10px; color: rgba(255,255,255,.6); margin-top: 2px;
               text-transform: uppercase; letter-spacing: .05em; }

/* ════════════════════════════════════════════
   SECCIÓN TÍTULOS
════════════════════════════════════════════ */
.sec-title {
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7a8d;
    margin: 0 0 14px;
    display: flex; align-items: center; gap: 8px;
}
.sec-title::after { content: ''; flex: 1; height: 1px; background: #e8ecf0; }

/* ════════════════════════════════════════════
   FILTRO TABS
════════════════════════════════════════════ */
.ec-tabs {
    display: flex; gap: 4px; padding: 14px 16px 0;
    border-bottom: 2px solid #e8ecf0; margin-bottom: 0;
    flex-wrap: wrap;
}
.ec-tab {
    padding: 7px 14px; font-size: 12px; font-weight: 600;
    border: none; background: none; color: #8a9ab0;
    border-bottom: 2px solid transparent; margin-bottom: -2px;
    border-radius: 4px 4px 0 0; cursor: pointer;
    transition: color .15s, border-color .15s;
    display: flex; align-items: center; gap: 6px;
}
.ec-tab:hover { color: #3c8dbc; }
.ec-tab.activo { color: #3c8dbc; border-bottom-color: #3c8dbc; background: #f0f7ff; }
.ec-tab .ec-badge {
    font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px;
    background: #e8ecf0; color: #6b7a8d;
}
.ec-tab.activo .ec-badge { background: #d0e8fb; color: #2c6fad; }
.ec-tab .ec-badge.rojo   { background: #fdecea; color: #b91c1c; }
.ec-tab .ec-badge.naranja { background: #fff8e1; color: #b45309; }

/* ════════════════════════════════════════════
   TABLA DE CARGOS
════════════════════════════════════════════ */
.ec-table { width: 100%; border-collapse: collapse; margin: 0; }
.ec-table thead th {
    background: #f4f6f8; color: #6b7a8d;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; padding: 9px 12px;
    border-bottom: 2px solid #e4eaf0; white-space: nowrap;
}
.ec-table tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
.ec-table tbody tr:last-child { border-bottom: none; }
.ec-table tbody tr.cargo-row:hover td { background: #f5f9ff !important; }
.ec-table td { padding: 10px 12px; vertical-align: middle; font-size: 13px; }

/* Badges de estado */
.ec-estado {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 9px;
    border-radius: 10px; white-space: nowrap;
}
.ec-pagado    { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
.ec-pendiente { background: #fff8e6; color: #b45309; border: 1px solid #fcd97d; }
.ec-vencido   { background: #fdecea; color: #b91c1c; border: 1px solid #fca5a5; }
.ec-parcial   { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
.ec-condonado { background: #f0f3f7; color: #6b7a8d; border: 1px solid #dde4eb; }

/* Detalle de pagos */
.ec-pagos-detalle { background: #f8fafc !important; }
.ec-pagos-detalle td { font-size: 12px !important; color: #4a5568; }
.ec-pagos-inner { border-top: 1px dashed #dde4eb; }

/* ════════════════════════════════════════════
   SIDEBAR — INFO CARD
════════════════════════════════════════════ */
.info-card {
    border: 1px solid #e4eaf0; border-radius: 10px; background: #fff;
    margin-bottom: 16px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.info-card-header {
    padding: 11px 16px; border-bottom: 1px solid #f0f3f7;
    display: flex; align-items: center; justify-content: space-between;
    background: #f8fafc;
}
.info-card-title { font-size: 11px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .07em; color: #6b7a8d; }
.balance-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 16px; border-bottom: 1px solid #f5f7fa; font-size: 13px;
}
.balance-row:last-child { border-bottom: none; }
.balance-row-label { color: #8a9ab0; font-size: 12px; }
.balance-row-value { font-weight: 700; color: #1a2634; }

.accion-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; border-bottom: 1px solid #f4f6f8;
    text-decoration: none; color: #333; font-size: 13px; font-weight: 500;
    transition: background .12s;
}
.accion-btn:hover { background: #f0f7ff; text-decoration: none; color: #3c8dbc; }
.accion-btn:last-child { border-bottom: none; }
.accion-icon { width: 32px; height: 32px; border-radius: 8px;
               display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
</style>
@endpush

@section('content')

@php
    $estado        = $alumno->estado;
    $estadoBadge   = [
        'activo'          => ['bg'=>'#e8f8f0','color'=>'#00875a','borde'=>'#b3e8d0','txt'=>'Activo'],
        'baja_temporal'   => ['bg'=>'#fff8e6','color'=>'#b45309','borde'=>'#fcd97d','txt'=>'Baja temporal'],
        'baja_definitiva' => ['bg'=>'#fdecea','color'=>'#b91c1c','borde'=>'#fca5a5','txt'=>'Baja definitiva'],
        'egresado'        => ['bg'=>'#f3e8fd','color'=>'#6b21a8','borde'=>'#d8b4fe','txt'=>'Egresado'],
    ][$estado] ?? ['bg'=>'#f0f3f7','color'=>'#555','borde'=>'#dde4eb','txt'=>ucfirst($estado)];
@endphp

{{-- ══ HERO ══ --}}
<div class="ec-hero">
    @if($alumno->foto_url)
        <img src="{{ asset('storage/'.$alumno->foto_url) }}" class="ec-hero-foto" alt="Foto">
    @else
        <div class="ec-hero-placeholder">
            <i class="fa fa-user" style="font-size:30px;color:rgba(255,255,255,.5);"></i>
        </div>
    @endif

    <div>
        <div class="ec-hero-nombre">{{ $alumno->nombre }}</div>
        <div class="ec-hero-apellidos">{{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}</div>
        <div class="ec-hero-meta">
            <span class="ec-hero-matricula">{{ $alumno->matricula }}</span>
            @if($inscripcionActual)
            <span>
                <i class="fa fa-graduation-cap"></i>
                {{ $inscripcionActual->grupo->grado->nivel->nombre ?? '' }}
                · {{ $inscripcionActual->grupo->grado->nombre }}°
                {{ $inscripcionActual->grupo->nombre }}
            </span>
            <span>
                <i class="fa fa-calendar"></i>
                {{ $inscripcionActual->ciclo->nombre ?? '' }}
            </span>
            @endif
            <span style="background:{{ $estadoBadge['bg'] }};color:{{ $estadoBadge['color'] }};
                         border:1px solid {{ $estadoBadge['borde'] }};
                         font-size:10px;font-weight:700;padding:1px 9px;border-radius:10px;">
                {{ $estadoBadge['txt'] }}
            </span>
        </div>
    </div>

    <div class="ec-stats">
        <div class="ec-stat">
            <div class="ec-stat-num">${{ number_format($resumen['total_cargado'], 0) }}</div>
            <div class="ec-stat-lbl">Cargado</div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-num" style="color:#a8e6cf;">${{ number_format($resumen['total_pagado'], 0) }}</div>
            <div class="ec-stat-lbl">Pagado</div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-num" style="color:{{ $resumen['saldo_pendiente'] > 0 ? '#ffcdd2' : '#a8e6cf' }};">
                ${{ number_format($resumen['saldo_pendiente'], 0) }}
            </div>
            <div class="ec-stat-lbl">Pendiente</div>
        </div>
        @if($resumen['total_vencido'] > 0)
        <div class="ec-stat">
            <div class="ec-stat-num" style="color:#ef9a9a;">${{ number_format($resumen['total_vencido'], 0) }}</div>
            <div class="ec-stat-lbl">Vencido</div>
        </div>
        @endif
        <div class="ec-stat" style="align-self:center;">
            <a href="{{ route('alumnos.show', $alumno->id) }}"
               class="btn btn-sm btn-flat"
               style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;">
                <i class="fa fa-arrow-left"></i> Alumno
            </a>
        </div>
    </div>
</div>

<div class="row">

{{-- ════════════════════════════════════════════════════
     COLUMNA PRINCIPAL — cargos (col-md-9)
════════════════════════════════════════════════════ --}}
<div class="col-md-9">

    <div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
                box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;margin-bottom:20px;">

        {{-- Cabecera con selector de ciclo --}}
        <div style="padding:14px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;
                    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <p class="sec-title" style="margin:0;flex:1;">
                <i class="fa fa-list-alt" style="color:#3c8dbc;"></i>
                Cargos
                <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                             padding:2px 9px;border-radius:10px;">{{ $resumen['total_cargos'] }}</span>
            </p>
            @if($ciclos->count() > 1)
            <form method="GET" style="display:flex;align-items:center;gap:6px;">
                <i class="fa fa-calendar" style="color:#8a9ab0;font-size:13px;"></i>
                <select name="ciclo_id" class="form-control input-sm"
                        onchange="this.form.submit()"
                        style="border-radius:6px;border-color:#dde4eb;font-size:12px;min-width:150px;">
                    <option value="">Todos los ciclos</option>
                    @foreach($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}"
                            {{ request('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                            {{ $ciclo->nombre }}
                        </option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        {{-- Tabs de filtro --}}
        <div class="ec-tabs">
            <button class="ec-tab activo" data-filtro="todos">
                Todos
                <span class="ec-badge">{{ $resumen['total_cargos'] }}</span>
            </button>
            <button class="ec-tab" data-filtro="pendiente">
                Pendientes
                @if($resumen['cargos_pendientes'] > 0)
                <span class="ec-badge naranja">{{ $resumen['cargos_pendientes'] }}</span>
                @endif
            </button>
            <button class="ec-tab" data-filtro="vencido">
                Vencidos
                @if($resumen['cargos_vencidos'] > 0)
                <span class="ec-badge rojo">{{ $resumen['cargos_vencidos'] }}</span>
                @endif
            </button>
            <button class="ec-tab" data-filtro="pagado">Pagados</button>
            <button class="ec-tab" data-filtro="parcial">Parciales</button>
        </div>

        {{-- Tabla --}}
        <div style="overflow-x:auto;">
            <table class="ec-table">
                <thead>
                    <tr>
                        <th style="width:30px;"></th>
                        <th>Concepto</th>
                        <th>Periodo</th>
                        <th>Vencimiento</th>
                        <th style="text-align:right;">Monto</th>
                        <th style="text-align:right;">Pagado</th>
                        <th style="text-align:right;">Pendiente</th>
                        <th style="text-align:right;">Recargo / Dto.</th>
                        <th style="text-align:center;">Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-cargos">

                @forelse($cargos as $cargo)
                @php
                    $saldoAbonado   = (float) ($cargo->total_abonado ?? 0);
                    $saldoPendiente = max(0, (float) $cargo->monto_original - $saldoAbonado);
                    $hoy            = now();
                    $vencido        = $hoy->isAfter($cargo->fecha_vencimiento);
                    $descuentoCalc  = (float) ($cargo->descuento_calc ?? 0);
                    $recargoCalc    = (float) ($cargo->recargo_calc   ?? 0);
                    $mesesRetraso   = (int)   ($cargo->meses_retraso  ?? 0);
                    $tieneAjuste    = ($descuentoCalc > 0 || $recargoCalc > 0)
                                      && !in_array($cargo->estado, ['pagado', 'condonado']);
                    $estadoReal = match($cargo->estado) {
                        'pagado'    => 'pagado',
                        'condonado' => 'condonado',
                        'parcial'   => $vencido ? 'vencido' : 'parcial',
                        default     => $vencido ? 'vencido' : 'pendiente',
                    };
                    $estadoClass = match($estadoReal) {
                        'pagado'    => 'ec-pagado',
                        'parcial'   => 'ec-parcial',
                        'vencido'   => 'ec-vencido',
                        'condonado' => 'ec-condonado',
                        default     => 'ec-pendiente',
                    };
                    $estadoLabel = match($estadoReal) {
                        'pagado'    => 'Pagado',
                        'parcial'   => 'Parcial',
                        'vencido'   => 'Vencido',
                        'condonado' => 'Condonado',
                        default     => 'Pendiente',
                    };
                    $tienePagos = $cargo->detallesPagosVigentes->count() > 0;
                @endphp

                <tr class="cargo-row"
                    data-estado="{{ $estadoReal }}"
                    data-cargo-id="{{ $cargo->id }}"
                    style="cursor:{{ $tienePagos ? 'pointer' : 'default' }};"
                    @if($tienePagos) onclick="togglePagos({{ $cargo->id }})" @endif>

                    <td style="text-align:center;color:#b0bec5;padding:10px 8px;">
                        @if($tienePagos)
                        <i class="fa fa-chevron-right toggle-icon-{{ $cargo->id }}"
                           style="font-size:10px;transition:transform .2s;"></i>
                        @endif
                    </td>

                    <td>
                        <div style="font-weight:700;color:#1a2634;">{{ $cargo->concepto->nombre }}</div>
                        <div style="font-size:11px;color:#8a9ab0;margin-top:2px;">{{ ucfirst($cargo->concepto->tipo) }}</div>
                    </td>

                    <td>
                        <code style="font-size:11px;background:#f0f3f7;padding:2px 7px;border-radius:4px;color:#4a5568;">
                            {{ $cargo->periodo }}
                        </code>
                    </td>

                    <td>
                        <span style="{{ $vencido && !in_array($estadoReal,['pagado','condonado']) ? 'color:#b91c1c;font-weight:600;' : 'color:#4a5568;' }}">
                            {{ $cargo->fecha_vencimiento->format('d/m/Y') }}
                        </span>
                        @if($vencido && !in_array($estadoReal, ['pagado','condonado']))
                        <div style="font-size:10px;color:#b91c1c;margin-top:2px;">
                            {{ $cargo->fecha_vencimiento->diffForHumans() }}
                        </div>
                        @endif
                    </td>

                    <td style="text-align:right;font-weight:600;color:#1a2634;">
                        ${{ number_format($cargo->monto_original, 2) }}
                    </td>

                    <td style="text-align:right;">
                        @if($saldoAbonado > 0)
                            <span style="color:#00875a;font-weight:600;">${{ number_format($saldoAbonado, 2) }}</span>
                        @else
                            <span style="color:#dde4eb;">—</span>
                        @endif
                    </td>

                    <td style="text-align:right;">
                        @if($saldoPendiente > 0)
                            <span style="color:#b91c1c;font-weight:700;">${{ number_format($saldoPendiente, 2) }}</span>
                        @else
                            <span style="color:#dde4eb;">—</span>
                        @endif
                    </td>

                    <td style="text-align:right;">
                        @if($tieneAjuste)
                            @if($recargoCalc > 0)
                                <span style="color:#b91c1c;font-weight:700;">
                                    +${{ number_format($recargoCalc, 2) }}
                                </span>
                                <div style="font-size:10px;color:#b91c1c;margin-top:2px;">
                                    <i class="fa fa-exclamation-triangle"></i> recargo
                                    @if($mesesRetraso > 1) × {{ $mesesRetraso }} meses @endif
                                </div>
                            @elseif($descuentoCalc > 0)
                                <span style="color:#00875a;font-weight:700;">
                                    -${{ number_format($descuentoCalc, 2) }}
                                </span>
                                <div style="font-size:10px;color:#00875a;margin-top:2px;">
                                    <i class="fa fa-tag"></i> pronto pago
                                </div>
                            @endif
                        @else
                            <span style="color:#dde4eb;">—</span>
                        @endif
                    </td>

                    <td style="text-align:center;">
                        <span class="ec-estado {{ $estadoClass }}">
                            <i class="fa fa-circle" style="font-size:6px;"></i>
                            {{ $estadoLabel }}
                        </span>
                    </td>
                </tr>

                {{-- Detalle de pagos colapsado --}}
                @if($tienePagos)
                <tr class="ec-pagos-detalle" id="pagos-{{ $cargo->id }}" style="display:none;">
                    <td colspan="9" style="padding:0;">
                        <div class="ec-pagos-inner" style="padding:10px 16px 14px 46px;">
                            <table style="width:100%;">
                                <thead>
                                    <tr style="color:#8a9ab0;">
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Folio</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Fecha</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Forma pago</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">Referencia</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;text-align:right;">Dto. beca</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;text-align:right;">Recargo</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;text-align:right;">Abonado</th>
                                        <th style="padding:4px 8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;text-align:center;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($cargo->detallesPagosVigentes as $detalle)
                                @php $pago = $detalle->pago; @endphp
                                <tr style="border-top:1px solid #edf1f5;">
                                    <td style="padding:6px 8px;">
                                        <code style="font-size:11px;background:#f0f3f7;padding:1px 6px;border-radius:3px;">
                                            {{ $pago->folio_recibo ?? '—' }}
                                        </code>
                                    </td>
                                    <td style="padding:6px 8px;color:#4a5568;">
                                        {{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td style="padding:6px 8px;color:#4a5568;">{{ ucfirst($pago->forma_pago ?? '') }}</td>
                                    <td style="padding:6px 8px;color:#8a9ab0;">{{ $pago->referencia ?? '—' }}</td>
                                    <td style="padding:6px 8px;text-align:right;">
                                        @if($detalle->descuento_beca > 0)
                                            <span style="color:#00875a;font-weight:600;">-${{ number_format($detalle->descuento_beca, 2) }}</span>
                                        @else <span style="color:#dde4eb;">—</span> @endif
                                    </td>
                                    <td style="padding:6px 8px;text-align:right;">
                                        @if($detalle->recargo_aplicado > 0)
                                            <span style="color:#b91c1c;font-weight:600;">+${{ number_format($detalle->recargo_aplicado, 2) }}</span>
                                        @else <span style="color:#dde4eb;">—</span> @endif
                                    </td>
                                    <td style="padding:6px 8px;text-align:right;font-weight:700;color:#1a2634;">
                                        ${{ number_format($detalle->monto_abonado, 2) }}
                                    </td>
                                    <td style="padding:6px 8px;text-align:center;">
                                        <span class="ec-estado {{ $pago->estado === 'vigente' ? 'ec-pagado' : 'ec-condonado' }}"
                                              style="font-size:10px;padding:2px 7px;">
                                            {{ ucfirst($pago->estado ?? '') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                @endif

                @empty
                <tr>
                    <td colspan="9" style="padding:56px 20px;text-align:center;">
                        <i class="fa fa-inbox" style="font-size:42px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
                        <p style="color:#b0bec5;margin:0;font-weight:600;">Sin cargos registrados</p>
                        @if(request('ciclo_id'))
                        <p style="color:#b0bec5;margin:4px 0 0;font-size:12px;">para el ciclo seleccionado</p>
                        @endif
                    </td>
                </tr>
                @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>{{-- /col-md-9 --}}

{{-- ════════════════════════════════════════════════════
     COLUMNA LATERAL (col-md-3)
════════════════════════════════════════════════════ --}}
<div class="col-md-3">

    {{-- Balance --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-calculator" style="margin-right:5px;color:#3c8dbc;"></i>Balance
            </span>
            @if($resumen['saldo_pendiente'] > 0)
            <span style="background:#fdecea;color:#b91c1c;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">
                Deuda
            </span>
            @else
            <span style="background:#e8f8f0;color:#00875a;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">
                <i class="fa fa-check"></i> Al corriente
            </span>
            @endif
        </div>

        <div class="balance-row">
            <span class="balance-row-label">Total cargado</span>
            <span class="balance-row-value">${{ number_format($resumen['total_cargado'], 2) }}</span>
        </div>
        <div class="balance-row">
            <span class="balance-row-label">Total pagado</span>
            <span class="balance-row-value" style="color:#00875a;">${{ number_format($resumen['total_pagado'], 2) }}</span>
        </div>
        @if($resumen['total_condonado'] > 0)
        <div class="balance-row">
            <span class="balance-row-label">Condonado</span>
            <span class="balance-row-value" style="color:#6b7a8d;">${{ number_format($resumen['total_condonado'], 2) }}</span>
        </div>
        @endif
        <div class="balance-row" style="background:#f8fafc;">
            <span class="balance-row-label" style="font-weight:700;color:#1a2634;">Saldo pendiente</span>
            <span class="balance-row-value" style="color:{{ $resumen['saldo_pendiente'] > 0 ? '#b91c1c' : '#00875a' }};font-size:15px;">
                ${{ number_format($resumen['saldo_pendiente'], 2) }}
            </span>
        </div>
        @if($resumen['total_recargos'] > 0)
        <div class="balance-row" style="border-top:1px dashed #f0f3f7;">
            <span class="balance-row-label" style="color:#b91c1c;font-size:11px;">
                <i class="fa fa-exclamation-triangle"></i> + Recargos
            </span>
            <span style="color:#b91c1c;font-weight:700;font-size:12px;">
                +${{ number_format($resumen['total_recargos'], 2) }}
            </span>
        </div>
        @endif
        @if($resumen['total_descuentos'] > 0)
        <div class="balance-row">
            <span class="balance-row-label" style="color:#00875a;font-size:11px;">
                <i class="fa fa-tag"></i> − Pronto pago
            </span>
            <span style="color:#00875a;font-weight:700;font-size:12px;">
                -${{ number_format($resumen['total_descuentos'], 2) }}
            </span>
        </div>
        @endif
        @if($resumen['total_recargos'] > 0 || $resumen['total_descuentos'] > 0)
        <div class="balance-row" style="background:#fff8e1;border-top:2px solid #f39c12;">
            <span class="balance-row-label" style="color:#b45309;font-weight:700;">
                <i class="fa fa-calculator"></i> A pagar hoy
            </span>
            <span style="color:{{ $resumen['total_a_pagar_hoy'] > 0 ? '#b91c1c' : '#00875a' }};font-weight:800;font-size:15px;">
                ${{ number_format($resumen['total_a_pagar_hoy'], 2) }}
            </span>
        </div>
        @endif
        @if($resumen['total_vencido'] > 0)
        <div class="balance-row" style="background:#fdecea;">
            <span class="balance-row-label" style="color:#b91c1c;font-size:11px;">
                <i class="fa fa-exclamation-circle"></i> De los cuales vencidos
            </span>
            <span style="color:#b91c1c;font-weight:700;font-size:12px;">
                ${{ number_format($resumen['total_vencido'], 2) }}
            </span>
        </div>
        @endif
    </div>

    {{-- Becas activas --}}
    @if($becas->count())
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-star" style="margin-right:5px;color:#f39c12;"></i>Becas activas
            </span>
            <span style="background:#fff8e1;color:#b45309;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">{{ $becas->count() }}</span>
        </div>
        @foreach($becas as $beca)
        <div style="padding:10px 16px;border-bottom:1px solid #f5f7fa;display:flex;align-items:center;gap:10px;">
            <div style="width:30px;height:30px;border-radius:8px;background:#fff8e1;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa fa-percent" style="color:#f39c12;font-size:12px;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:700;color:#1a2634;">{{ $beca->catalogoBeca->nombre }}</div>
                <div style="font-size:11px;color:#8a9ab0;margin-top:1px;">{{ $beca->concepto->nombre }}</div>
            </div>
            <span style="background:#fff3cd;color:#856404;font-size:11px;font-weight:700;
                         padding:2px 8px;border-radius:8px;white-space:nowrap;flex-shrink:0;">
                @if($beca->catalogoBeca->tipo === 'porcentaje')
                    {{ $beca->catalogoBeca->valor }}%
                @else
                    ${{ number_format($beca->catalogoBeca->valor, 2) }}
                @endif
            </span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Acciones rápidas --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-bolt" style="margin-right:5px;color:#f39c12;"></i>Acciones
            </span>
        </div>
        <div>
            @can('caja')
            <a href="{{ route('pagos.create') }}?alumno_id={{ $alumno->id }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f8f0;">
                    <i class="fa fa-plus" style="color:#00a65a;font-size:13px;"></i>
                </div>
                Registrar pago
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endcan
            <a href="{{ route('alumnos.show', $alumno->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f0fb;">
                    <i class="fa fa-user" style="color:#3c8dbc;font-size:13px;"></i>
                </div>
                Ficha del alumno
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @if($alumno->familia)
            <a href="{{ route('familias.show', $alumno->familia_id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f5e9;">
                    <i class="fa fa-home" style="color:#4caf50;font-size:13px;"></i>
                </div>
                Ficha de familia
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endif
            <a href="{{ route('alumnos.index') }}" class="accion-btn">
                <div class="accion-icon" style="background:#f0f3f7;">
                    <i class="fa fa-arrow-left" style="color:#6b7a8d;font-size:13px;"></i>
                </div>
                Volver a alumnos
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
        </div>
    </div>

</div>{{-- /col-md-3 --}}

</div>{{-- /row --}}
@endsection

@push('scripts')
<script>
function togglePagos(cargoId) {
    var fila    = document.getElementById('pagos-' + cargoId);
    var icon    = document.querySelector('.toggle-icon-' + cargoId);
    var visible = fila.style.display !== 'none';
    fila.style.display = visible ? 'none' : 'table-row';
    if (icon) icon.style.transform = visible ? '' : 'rotate(90deg)';
}

$(function() {
    $('.ec-tab').on('click', function() {
        $('.ec-tab').removeClass('activo');
        $(this).addClass('activo');
        var filtro = $(this).data('filtro');

        $('#tabla-cargos tr.cargo-row').each(function() {
            var estado  = $(this).data('estado');
            var cargoId = $(this).data('cargo-id');
            var detalle = $('#pagos-' + cargoId);

            if (filtro === 'todos' || estado === filtro) {
                $(this).show();
            } else {
                $(this).hide();
                if (detalle.length) detalle.hide();
            }
        });
    });
});
</script>
@endpush
