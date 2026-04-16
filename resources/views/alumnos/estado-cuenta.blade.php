@extends('layouts.master')

@section('page_title', 'Estado de cuenta')
@section('page_subtitle', $alumno->nombre . ' ' . $alumno->ap_paterno . ' · ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li><a href="{{ route('alumnos.show', $alumno->id) }}">{{ $alumno->ap_paterno }}</a></li>
    <li class="active">Estado de cuenta</li>
@endsection

@push('styles')
<style>
.resumen-card {
    border-radius: 4px;
    padding: 16px 20px;
    color: #fff;
    margin-bottom: 0;
}
.resumen-card .monto {
    font-size: 26px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
}
.resumen-card .label-monto {
    font-size: 11px;
    opacity: .85;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.cargo-row { transition: background .12s; }
.cargo-row:hover { background: #f8f8f8; }
.cargo-row td { vertical-align: middle !important; font-size: 13px; }
.badge-estado {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
    white-space: nowrap;
}
.estado-pagado    { background:#dff0d8; color:#3c763d; }
.estado-pendiente { background:#fcf8e3; color:#8a6d3b; }
.estado-vencido   { background:#f2dede; color:#a94442; }
.estado-parcial   { background:#d9edf7; color:#31708f; }
.estado-condonado { background:#f5f5f5; color:#777; }

.pagos-detalle { background:#fafffe; border-top:1px dashed #e0e0e0; }
.pagos-detalle td { font-size:12px !important; color:#555; }

/* Filtros de pestaña */
.filtro-tabs { border-bottom:2px solid #e8e8e8; margin-bottom:16px; display:flex; gap:0; }
.filtro-tab {
    padding:8px 18px; font-size:13px; cursor:pointer;
    border:none; background:none; color:#888;
    border-bottom:3px solid transparent; margin-bottom:-2px;
    transition: all .15s;
}
.filtro-tab:hover { color:#3c8dbc; }
.filtro-tab.activo { color:#3c8dbc; border-bottom-color:#3c8dbc; font-weight:600; }
</style>
@endpush

@section('content')

{{-- ── Encabezado alumno ── --}}
<div class="box box-default" style="margin-bottom:16px;">
    <div class="box-body" style="padding:14px 20px;">
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="flex-shrink:0;">
                @if($alumno->foto_url)
                    <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                         style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #e8e8e8;">
                @else
                    <div style="width:56px;height:56px;border-radius:50%;background:#e8e8e8;
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-user" style="font-size:24px;color:#aaa;"></i>
                    </div>
                @endif
            </div>
            <div style="flex:1;">
                <h4 style="margin:0 0 4px; font-size:16px;">
                    {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                </h4>
                <div style="font-size:12px; color:#888; display:flex; gap:16px; flex-wrap:wrap;">
                    <span><i class="fa fa-id-badge"></i> <code>{{ $alumno->matricula }}</code></span>
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
                </div>
            </div>
            <div style="flex-shrink:0;">
                <a href="{{ route('alumnos.show', $alumno->id) }}"
                   class="btn btn-default btn-sm btn-flat">
                    <i class="fa fa-arrow-left"></i> Volver al alumno
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Tarjetas de resumen ── --}}
<div class="row" style="margin-bottom:16px;">
    <div class="col-md-3 col-sm-6">
        <div class="resumen-card" style="background:#3c8dbc;">
            <div class="monto">${{ number_format($resumen['total_cargado'], 2) }}</div>
            <div class="label-monto"><i class="fa fa-file-text-o"></i> Total cargado</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="resumen-card" style="background:#00a65a;">
            <div class="monto">${{ number_format($resumen['total_pagado'], 2) }}</div>
            <div class="label-monto"><i class="fa fa-check-circle"></i> Total pagado</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="resumen-card" style="background:{{ $resumen['saldo_pendiente'] > 0 ? '#f39c12' : '#00a65a' }};">
            <div class="monto">${{ number_format($resumen['saldo_pendiente'], 2) }}</div>
            <div class="label-monto"><i class="fa fa-clock-o"></i> Saldo pendiente</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="resumen-card" style="background:{{ $resumen['total_vencido'] > 0 ? '#dd4b39' : '#00a65a' }};">
            <div class="monto">${{ number_format($resumen['total_vencido'], 2) }}</div>
            <div class="label-monto"><i class="fa fa-exclamation-circle"></i> Vencido</div>
        </div>
    </div>
</div>

<div class="row">

    {{-- ── Columna principal: cargos ── --}}
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list-alt"></i> Cargos
                </h3>
                <div class="box-tools pull-right">
                    {{-- Filtro por ciclo --}}
                    @if($ciclosAlumno->count() > 1)
                    <form method="GET" style="display:inline-flex; gap:6px; align-items:center;">
                        <select name="ciclo_id" class="form-control input-sm"
                                onchange="this.form.submit()" style="width:160px;">
                            <option value="">Todos los ciclos</option>
                            @foreach($ciclosAlumno as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ request('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
            </div>

            <div class="box-body" style="padding:0 0 8px;">

                {{-- Filtros de estado --}}
                <div style="padding:12px 16px 0;">
                    <div class="filtro-tabs">
                        <button class="filtro-tab activo" data-filtro="todos">
                            Todos
                            <span class="badge" style="background:#aaa;">{{ $resumen['total_cargos'] }}</span>
                        </button>
                        <button class="filtro-tab" data-filtro="pendiente">
                            Pendientes
                            @if($resumen['cargos_pendientes'] > 0)
                            <span class="badge" style="background:#f39c12;">{{ $resumen['cargos_pendientes'] }}</span>
                            @endif
                        </button>
                        <button class="filtro-tab" data-filtro="vencido">
                            Vencidos
                            @if($resumen['cargos_vencidos'] > 0)
                            <span class="badge" style="background:#dd4b39;">{{ $resumen['cargos_vencidos'] }}</span>
                            @endif
                        </button>
                        <button class="filtro-tab" data-filtro="pagado">
                            Pagados
                        </button>
                        <button class="filtro-tab" data-filtro="parcial">
                            Parciales
                        </button>
                    </div>
                </div>

                {{-- Tabla de cargos --}}
                <table class="table" style="margin:0;">
                    <thead>
                        <tr style="background:#f9f9f9;">
                            <th style="width:3%;padding:8px 16px;"></th>
                            <th style="width:19%;">Concepto</th>
                            <th style="width:9%;">Periodo</th>
                            <th style="width:12%;">Vencimiento</th>
                            <th style="width:11%;text-align:right;">Monto</th>
                            <th style="width:11%;text-align:right;">Pagado</th>
                            <th style="width:11%;text-align:right;">Pendiente</th>
                            <th style="width:13%;text-align:right;">Recargo / Dto.</th>
                            <th style="width:11%;text-align:center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-cargos">

                    @forelse($cargos as $cargo)
                    @php
                        $saldoAbonado   = (float) ($cargo->total_abonado ?? 0);
                        $saldoPendiente = max(0, (float) $cargo->monto_original - $saldoAbonado);
                        $hoy            = now();
                        $vencido        = $hoy->isAfter($cargo->fecha_vencimiento);

                        // Valores calculados en el controlador (beca + recargo / descuento)
                        $becaDescuentoCalc = (float) ($cargo->beca_descuento_calc ?? 0);
                        $becaPorcentaje    = $cargo->beca_porcentaje ?? null;
                        $descuentoCalc     = (float) ($cargo->descuento_calc ?? 0);
                        $recargoCalc       = (float) ($cargo->recargo_calc   ?? 0);
                        $mesesRetraso      = (int)   ($cargo->meses_retraso  ?? 0);
                        $aPagarHoy         = (float) ($cargo->monto_a_pagar_hoy ?? $saldoPendiente);
                        $tieneAjuste       = ($becaDescuentoCalc > 0 || $descuentoCalc > 0 || $recargoCalc > 0)
                                             && !in_array($cargo->estado, ['pagado', 'condonado']);

                        $estadoReal = match($cargo->estado) {
                            'pagado'    => 'pagado',
                            'condonado' => 'condonado',
                            'parcial'   => $vencido ? 'vencido' : 'parcial',
                            default     => $vencido ? 'vencido' : 'pendiente',
                        };

                        $badgeClass = match($estadoReal) {
                            'pagado'    => 'estado-pagado',
                            'parcial'   => 'estado-parcial',
                            'vencido'   => 'estado-vencido',
                            'condonado' => 'estado-condonado',
                            default     => 'estado-pendiente',
                        };

                        $badgeLabel = match($estadoReal) {
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

                        <td style="padding:10px 10px 10px 16px; color:#aaa; text-align:center;">
                            @if($tienePagos)
                            <i class="fa fa-chevron-right toggle-icon-{{ $cargo->id }}"
                               style="font-size:10px; transition:transform .2s;"></i>
                            @endif
                        </td>
                        <td style="padding:10px 8px;">
                            <strong style="font-size:13px;">{{ $cargo->concepto->nombre }}</strong>
                            <br>
                            <small class="text-muted">{{ ucfirst($cargo->concepto->tipo) }}</small>
                            @if($becaPorcentaje !== null && !in_array($cargo->estado, ['pagado','condonado']))
                            <br>
                            <span style="display:inline-block;margin-top:3px;padding:1px 7px;
                                         border-radius:8px;background:#dff0d8;color:#3c763d;
                                         font-size:10px;font-weight:600;">
                                <i class="fa fa-graduation-cap"></i> {{ number_format($becaPorcentaje, 0) }}% beca
                            </span>
                            @elseif($becaDescuentoCalc > 0 && !in_array($cargo->estado, ['pagado','condonado']))
                            <br>
                            <span style="display:inline-block;margin-top:3px;padding:1px 7px;
                                         border-radius:8px;background:#dff0d8;color:#3c763d;
                                         font-size:10px;font-weight:600;">
                                <i class="fa fa-graduation-cap"></i> beca
                            </span>
                            @endif
                        </td>
                        <td style="padding:10px 8px;">
                            <code style="font-size:12px;">{{ $cargo->periodo }}</code>
                        </td>
                        <td style="padding:10px 8px;">
                            <span style="{{ $vencido && $estadoReal !== 'pagado' ? 'color:#a94442;font-weight:600;' : '' }}">
                                {{ $cargo->fecha_vencimiento->format('d/m/Y') }}
                            </span>
                            @if($vencido && !in_array($estadoReal, ['pagado','condonado']))
                            <br>
                            <small style="color:#a94442;">
                                {{ $cargo->fecha_vencimiento->diffForHumans() }}
                            </small>
                            @endif
                        </td>
                        <td style="padding:10px 8px; text-align:right;">
                            ${{ number_format($cargo->monto_original, 2) }}
                        </td>
                        <td style="padding:10px 8px; text-align:right; color:#00a65a;">
                            @if($saldoAbonado > 0)
                                ${{ number_format($saldoAbonado, 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        {{-- Pendiente base --}}
                        <td style="padding:10px 8px; text-align:right;
                                   {{ $saldoPendiente > 0 ? 'color:#a94442;font-weight:600;' : '' }}">
                            @if($saldoPendiente > 0)
                                ${{ number_format($saldoPendiente, 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Ajustes: beca + recargo/descuento del plan --}}
                        <td style="padding:10px 8px; text-align:right;">
                            @if($tieneAjuste)
                                @if($becaDescuentoCalc > 0)
                                    <span style="color:#3c763d; font-weight:600;">
                                        -${{ number_format($becaDescuentoCalc, 2) }}
                                    </span>
                                    <br>
                                    <small style="color:#3c763d; font-size:10px;">
                                        <i class="fa fa-graduation-cap"></i>
                                        beca{{ $becaPorcentaje !== null ? ' '.number_format($becaPorcentaje, 0).'%' : '' }}
                                    </small>
                                @endif
                                @if($recargoCalc > 0)
                                    @if($becaDescuentoCalc > 0)<br>@endif
                                    <span style="color:#a94442; font-weight:600;">
                                        +${{ number_format($recargoCalc, 2) }}
                                    </span>
                                    <br>
                                    <small style="color:#a94442; font-size:10px;">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        recargo
                                        @if($mesesRetraso > 1)
                                            × {{ $mesesRetraso }} meses
                                        @endif
                                    </small>
                                @elseif($descuentoCalc > 0)
                                    @if($becaDescuentoCalc > 0)<br>@endif
                                    <span style="color:#00a65a; font-weight:600;">
                                        -${{ number_format($descuentoCalc, 2) }}
                                    </span>
                                    <br>
                                    <small style="color:#00a65a; font-size:10px;">
                                        <i class="fa fa-tag"></i> dto. pronto pago
                                    </small>
                                @endif
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>

                        <td style="padding:10px 8px; text-align:center;">
                            <span class="badge-estado {{ $badgeClass }}">{{ $badgeLabel }}</span>
                        </td>
                    </tr>

                    {{-- Detalle de pagos (colapsado) --}}
                    @if($tienePagos)
                    <tr class="pagos-detalle" id="pagos-{{ $cargo->id }}" style="display:none;">
                        <td colspan="9" style="padding:0 16px 12px 40px;">
                            <table style="width:100%; margin-top:8px;">
                                <thead>
                                    <tr style="color:#999; font-size:11px; text-transform:uppercase;">
                                        <th style="padding:4px 8px; font-weight:500;">Folio</th>
                                        <th style="padding:4px 8px; font-weight:500;">Fecha</th>
                                        <th style="padding:4px 8px; font-weight:500;">Forma pago</th>
                                        <th style="padding:4px 8px; font-weight:500;">Referencia</th>
                                        <th style="padding:4px 8px; font-weight:500; text-align:right;">Descuento beca</th>
                                        <th style="padding:4px 8px; font-weight:500; text-align:right;">Recargo</th>
                                        <th style="padding:4px 8px; font-weight:500; text-align:right;">Abonado</th>
                                        <th style="padding:4px 8px; font-weight:500; text-align:center;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($cargo->detallesPagosVigentes as $detalle)
                                @php $pago = $detalle->pago; @endphp
                                <tr style="border-top:1px solid #f0f0f0;">
                                    <td style="padding:5px 8px;">
                                        <code style="font-size:11px;">{{ $pago->folio_recibo ?? '—' }}</code>
                                    </td>
                                    <td style="padding:5px 8px;">
                                        {{ $pago->fecha_pago ? \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') : '—' }}
                                    </td>
                                    <td style="padding:5px 8px;">{{ ucfirst($pago->forma_pago ?? '') }}</td>
                                    <td style="padding:5px 8px; color:#999;">{{ $pago->referencia ?? '—' }}</td>
                                    <td style="padding:5px 8px; text-align:right; color:#00a65a;">
                                        @if($detalle->descuento_beca > 0)
                                            -${{ number_format($detalle->descuento_beca, 2) }}
                                        @else
                                            <span style="color:#ccc;">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:5px 8px; text-align:right;
                                               {{ $detalle->recargo_aplicado > 0 ? 'color:#a94442;' : '' }}">
                                        @if($detalle->recargo_aplicado > 0)
                                            +${{ number_format($detalle->recargo_aplicado, 2) }}
                                        @else
                                            <span style="color:#ccc;">—</span>
                                        @endif
                                    </td>
                                    <td style="padding:5px 8px; text-align:right; font-weight:600;">
                                        ${{ number_format($detalle->monto_abonado, 2) }}
                                    </td>
                                    <td style="padding:5px 8px; text-align:center;">
                                        <span class="badge-estado {{ $pago->estado === 'vigente' ? 'estado-pagado' : 'estado-condonado' }}"
                                              style="font-size:10px;">
                                            {{ ucfirst($pago->estado ?? '') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endif

                    @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding:40px; color:#aaa;">
                            <i class="fa fa-inbox fa-3x" style="display:block;margin-bottom:10px;"></i>
                            <strong>Sin cargos registrados</strong>
                            @if(request('ciclo_id'))
                            <br><small>para el ciclo seleccionado</small>
                            @endif
                        </td>
                    </tr>
                    @endforelse

                    </tbody>
                </table>

            </div>
        </div>
    </div>

    {{-- ── Columna lateral: resumen por ciclo y beca ── --}}
    <div class="col-md-3">

        {{-- Estado general --}}
        <div class="box {{ $resumen['saldo_pendiente'] > 0 ? 'box-warning' : 'box-success' }}">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size:13px;">
                    <i class="fa fa-dollar"></i> Balance
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table" style="font-size:12px; margin:0;">
                    <tr>
                        <td style="color:#888; padding:8px 14px;">Total cargado</td>
                        <td style="text-align:right; padding:8px 14px;">
                            ${{ number_format($resumen['total_cargado'], 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#888; padding:8px 14px;">Total pagado</td>
                        <td style="text-align:right; padding:8px 14px; color:#00a65a;">
                            ${{ number_format($resumen['total_pagado'], 2) }}
                        </td>
                    </tr>
                    @if($resumen['total_condonado'] > 0)
                    <tr>
                        <td style="color:#888; padding:8px 14px;">Condonado</td>
                        <td style="text-align:right; padding:8px 14px; color:#777;">
                            ${{ number_format($resumen['total_condonado'], 2) }}
                        </td>
                    </tr>
                    @endif
                    <tr style="background:#f9f9f9; font-weight:600;">
                        <td style="padding:10px 14px;">Saldo pendiente</td>
                        <td style="text-align:right; padding:10px 14px;
                                   color:{{ $resumen['saldo_pendiente'] > 0 ? '#a94442' : '#00a65a' }};">
                            ${{ number_format($resumen['saldo_pendiente'], 2) }}
                        </td>
                    </tr>
                    @if($resumen['total_becas'] > 0)
                    <tr>
                        <td style="color:#3c763d; padding:8px 14px; font-size:11px;">
                            <i class="fa fa-graduation-cap"></i> − Descuento becas
                        </td>
                        <td style="text-align:right; padding:8px 14px; color:#3c763d; font-size:11px;">
                            -${{ number_format($resumen['total_becas'], 2) }}
                        </td>
                    </tr>
                    @endif
                    @if($resumen['total_recargos'] > 0)
                    <tr>
                        <td style="color:#a94442; padding:8px 14px; font-size:11px;">
                            <i class="fa fa-exclamation-triangle"></i> + Recargos aplicados
                        </td>
                        <td style="text-align:right; padding:8px 14px; color:#a94442; font-size:11px;">
                            +${{ number_format($resumen['total_recargos'], 2) }}
                        </td>
                    </tr>
                    @endif
                    @if($resumen['total_descuentos'] > 0)
                    <tr>
                        <td style="color:#00a65a; padding:8px 14px; font-size:11px;">
                            <i class="fa fa-tag"></i> − Descuentos pronto pago
                        </td>
                        <td style="text-align:right; padding:8px 14px; color:#00a65a; font-size:11px;">
                            -${{ number_format($resumen['total_descuentos'], 2) }}
                        </td>
                    </tr>
                    @endif
                    @if($resumen['total_becas'] > 0 || $resumen['total_recargos'] > 0 || $resumen['total_descuentos'] > 0)
                    <tr style="background:#fff8e1; font-weight:700; border-top:2px solid #f39c12;">
                        <td style="padding:10px 14px; color:#8a6d3b;">
                            <i class="fa fa-calculator"></i> A pagar hoy
                        </td>
                        <td style="text-align:right; padding:10px 14px;
                                   color:{{ $resumen['total_a_pagar_hoy'] > 0 ? '#a94442' : '#00a65a' }};">
                            ${{ number_format($resumen['total_a_pagar_hoy'], 2) }}
                        </td>
                    </tr>
                    @endif
                    @if($resumen['total_vencido'] > 0)
                    <tr>
                        <td style="color:#a94442; padding:8px 14px; font-size:11px;">
                            <i class="fa fa-exclamation-triangle"></i> De los cuales vencidos
                        </td>
                        <td style="text-align:right; padding:8px 14px; color:#a94442; font-size:11px;">
                            ${{ number_format($resumen['total_vencido'], 2) }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Becas activas --}}
        @if($becas->count())
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size:13px;">
                    <i class="fa fa-tag"></i> Becas activas
                </h3>
            </div>
            <div class="box-body" style="padding:10px 14px;">
                @foreach($becas as $beca)
                <div style="margin-bottom:8px; padding-bottom:8px;
                            border-bottom:1px solid #f4f4f4;">
                    <strong style="font-size:13px;">{{ $beca->catalogoBeca->nombre }}</strong>
                    <br>
                    <small class="text-muted">
                        {{ $beca->concepto->nombre }}
                        ·
                        @if($beca->catalogoBeca->tipo === 'porcentaje')
                            {{ $beca->catalogoBeca->valor }}%
                        @else
                            ${{ number_format($beca->catalogoBeca->valor, 2) }}
                        @endif
                    </small>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Accesos rápidos --}}
        <div class="box box-default">
            <div class="box-body" style="padding:12px;">
                @can('caja')
                <a href="{{ route('pagos.create') }}?alumno_id={{ $alumno->id }}"
                   class="btn btn-success btn-block btn-sm btn-flat">
                    <i class="fa fa-plus"></i> Registrar pago
                </a>
                @endcan
                <a href="{{ route('alumnos.show', $alumno->id) }}"
                   class="btn btn-default btn-block btn-sm btn-flat" style="margin-top:4px;">
                    <i class="fa fa-user"></i> Ficha del alumno
                </a>
                @if($alumno->familia)
                <a href="{{ route('familias.show', $alumno->familia->id) }}"
                   class="btn btn-default btn-block btn-sm btn-flat" style="margin-top:4px;">
                    <i class="fa fa-home"></i> Ficha de familia
                </a>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Toggle pagos del cargo ─────────────────────────────
function togglePagos(cargoId) {
    var fila   = document.getElementById('pagos-' + cargoId);
    var icon   = document.querySelector('.toggle-icon-' + cargoId);
    var visible = fila.style.display !== 'none';
    fila.style.display = visible ? 'none' : 'table-row';
    if (icon) {
        icon.style.transform = visible ? '' : 'rotate(90deg)';
    }
}

// ── Filtro por estado ──────────────────────────────────
$(function() {
    $('.filtro-tab').on('click', function() {
        $('.filtro-tab').removeClass('activo');
        $(this).addClass('activo');

        var filtro = $(this).data('filtro');

        $('#tabla-cargos tr.cargo-row').each(function() {
            var estado = $(this).data('estado');
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
