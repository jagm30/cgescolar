@extends('layouts.master')

@section('page_title', 'Cobrar')
@section('page_subtitle', $alumno->nombre . ' ' . $alumno->ap_paterno . ' · ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('cobros.index') }}">Cobros</a></li>
    <li class="active">{{ $alumno->ap_paterno }}</li>
@endsection

@push('styles')
<style>
/* ── Alumno cabecera ──────────────────────── */
.alumno-header {
    background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
    border-radius: 4px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 18px;
}
.alumno-header-nombre {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}
.alumno-header-sub {
    font-size: 12px;
    color: rgba(255,255,255,.75);
    margin-top: 4px;
}

/* ── Cargos ───────────────────────────────── */
.cargo-item {
    border: 2px solid #e8e8e8;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
    overflow: hidden;
}
.cargo-item.seleccionado {
    border-color: #3c8dbc;
    box-shadow: 0 2px 10px rgba(60,141,188,.18);
}
.cargo-item.vencido-card { border-left: 4px solid #e74c3c; }
.cargo-check { cursor: pointer; }
.cargo-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    cursor: pointer;
    user-select: none;
}
.cargo-concepto { font-size: 15px; font-weight: 700; color: #1a1a1a; }
.cargo-meta { font-size: 12px; color: #999; margin-top: 3px; }
.cargo-montos {
    margin-left: auto;
    text-align: right;
    flex-shrink: 0;
}
.cargo-monto-orig { font-size: 13px; color: #aaa; text-decoration: line-through; }
.cargo-monto-pend { font-size: 18px; font-weight: 700; color: #e74c3c; }
.cargo-monto-pend.ok { color: #3c8dbc; }

/* Panel de detalle del cargo (oculto por defecto) */
.cargo-detalle {
    display: none;
    padding: 12px 16px 16px;
    background: #f8fbff;
    border-top: 1px solid #e8f0f8;
}
.cargo-item.seleccionado .cargo-detalle { display: block; }

/* Campo monto editable */
.monto-input {
    font-size: 20px;
    font-weight: 700;
    height: 46px;
    text-align: right;
    border-radius: 6px;
    border: 2px solid #3c8dbc;
    padding-right: 12px;
}
.monto-input:focus { border-color: #1e6fa8; box-shadow: none; }

/* ── Nuevo concepto ───────────────────────── */
.nuevo-concepto-panel {
    border: 2px dashed #d0dde8;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 18px;
    background: #fafcff;
}

/* ── Resumen ──────────────────────────────── */
.resumen-box {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    position: sticky;
    top: 20px;
}
.resumen-header {
    background: linear-gradient(135deg, #1e4d7b, #3c8dbc);
    padding: 14px 18px;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
}
.resumen-linea {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 18px;
    border-bottom: 1px solid #f4f4f4;
    font-size: 13px;
}
.resumen-linea:last-child { border-bottom: none; }
.resumen-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    background: #f0f7ff;
    border-top: 2px solid #3c8dbc;
}
.resumen-total-label { font-size: 14px; font-weight: 700; color: #1a1a1a; }
.resumen-total-monto { font-size: 26px; font-weight: 700; color: #3c8dbc; }

#btn-cobrar {
    font-size: 16px;
    height: 50px;
    letter-spacing: .02em;
}
.btn-billete {
    border-color: #b2dfdb;
    color: #00695c;
    background: #e0f2f1;
    transition: background .12s, border-color .12s;
}
.btn-billete:hover, .btn-billete.activo {
    background: #27ae60;
    border-color: #27ae60;
    color: #fff;
}
</style>
@endpush

@section('content')

@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong><i class="fa fa-exclamation-triangle"></i> Errores de validación:</strong>
    <ul style="margin:6px 0 0 16px;padding:0;">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ── Cabecera del alumno ── --}}
<div class="alumno-header">
    <div style="width:56px;height:56px;border-radius:50%;flex-shrink:0;
                background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.5);
                display:flex;align-items:center;justify-content:center;overflow:hidden;">
        @if($alumno->foto_url)
            <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                 style="width:100%;height:100%;object-fit:cover;">
        @else
            <i class="fa fa-user" style="color:rgba(255,255,255,.8);font-size:24px;"></i>
        @endif
    </div>
    <div style="flex:1;">
        <div class="alumno-header-nombre">
            {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
        </div>
        <div class="alumno-header-sub">
            <code style="background:rgba(0,0,0,.2);color:rgba(255,255,255,.9);
                          padding:1px 7px;border-radius:10px;font-size:11px;">
                {{ $alumno->matricula }}
            </code>
            @if($inscripcionActual)
                &nbsp;·&nbsp;
                {{ $inscripcionActual->grupo?->grado?->nivel?->nombre ?? '' }}
                {{ $inscripcionActual->grupo?->grado?->nombre }}°
                {{ $inscripcionActual->grupo?->nombre ?? 'Sin grupo' }}
                &nbsp;·&nbsp;
                {{ $inscripcionActual->ciclo->nombre ?? '' }}
            @elseif($inscripcionParaCobro)
                &nbsp;·&nbsp;
                <span style="background:rgba(255,193,7,.25);color:rgba(255,255,255,.9);
                              padding:1px 8px;border-radius:10px;font-size:11px;">
                    <i class="fa fa-exclamation-triangle"></i>
                    Sin inscripción activa &mdash; último ciclo: {{ $inscripcionParaCobro->ciclo?->nombre ?? 'desconocido' }}
                </span>
            @endif
        </div>
    </div>
    <a href="{{ route('cobros.index') }}" class="btn btn-xs btn-flat"
       style="color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.1);">
        <i class="fa fa-arrow-left"></i> Buscar otro
    </a>
</div>

<form method="POST" action="{{ route('cobros.registrar') }}" id="form-cobro">
@csrf
<input type="hidden" name="alumno_id" value="{{ $alumno->id }}">

<div class="row">

{{-- ════════════════════════════ COLUMNA PRINCIPAL ════════════════════════════ --}}
<div class="col-md-8">

    {{-- ── Cargos pendientes ── --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-list-alt"></i>
                Cargos pendientes
                @if($cargos->count())
                    <span class="badge bg-blue" style="margin-left:6px;">{{ $cargos->count() }}</span>
                @endif
            </h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-default btn-xs btn-flat" id="btn-sel-todos">
                    Seleccionar todos
                </button>
            </div>
        </div>
        <div class="box-body" style="padding:12px;">

            @forelse($cargos as $i => $cargo)
            @php
                $tieneRecargo   = $cargo->recargo_calc        > 0;
                $tieneDescuento = $cargo->descuento_calc      > 0;
                $tieneBeca      = ($cargo->beca_descuento_calc ?? 0) > 0;
                $tieneAjuste    = $tieneRecargo || $tieneDescuento || $tieneBeca;
                $becaDescuento  = (float) ($cargo->beca_descuento_calc ?? 0);
                $becaPorcentaje = $cargo->beca_porcentaje ?? null;
            @endphp
            <div class="cargo-item {{ $cargo->vencido ? 'vencido-card' : '' }}"
                 id="cargo-card-{{ $cargo->id }}"
                 data-cargo-id="{{ $cargo->id }}"
                 data-pendiente="{{ $cargo->pendiente }}"
                 data-beca="{{ $becaDescuento }}"
                 data-recargo="{{ $cargo->recargo_calc }}"
                 data-descuento="{{ $cargo->descuento_calc }}"
                 data-pagar-hoy="{{ $cargo->monto_a_pagar_hoy }}"
                 data-descuento-tipo="{{ $cargo->descuento_tipo ?? '' }}"
                 data-descuento-valor="{{ $cargo->descuento_valor ?? 0 }}">

                {{-- Cabecera del cargo (clickeable para seleccionar) --}}
                <div class="cargo-header" onclick="toggleCargo({{ $cargo->id }}, {{ $cargo->monto_a_pagar_hoy }})">

                    {{-- Checkbox visual --}}
                    <div style="width:22px;height:22px;border-radius:50%;border:2px solid #ccc;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;
                                background:#fff;transition:all .15s;"
                         id="chk-visual-{{ $cargo->id }}">
                    </div>

                    {{-- Ícono concepto --}}
                    <div style="width:40px;height:40px;border-radius:8px;flex-shrink:0;
                                background:{{ $cargo->vencido ? '#fdecea' : '#e8f0fb' }};
                                display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-file-text-o"
                           style="font-size:17px;color:{{ $cargo->vencido ? '#e74c3c' : '#3c8dbc' }};"></i>
                    </div>

                    {{-- Datos --}}
                    <div style="flex:1;min-width:0;">
                        <div class="cargo-concepto">{{ $cargo->concepto->nombre }}</div>
                        <div class="cargo-meta">
                            <span style="background:#f0f0f0;padding:1px 7px;border-radius:8px;font-size:10px;">
                                {{ $cargo->inscripcion->ciclo->nombre ?? '' }}
                            </span>
                            @if($cargo->asignacion?->plan)
                            &nbsp;
                            <span style="background:#e8f0fb;color:#2c6fad;padding:1px 7px;border-radius:8px;font-size:10px;">
                                <i class="fa fa-list" style="font-size:9px;"></i>
                                {{ $cargo->asignacion->plan->nombre }}
                            </span>
                            @endif
                            @if($cargo->periodo_label)
                            &nbsp;
                            <span style="background:#e8f0fb;color:#2c6fad;padding:1px 7px;border-radius:8px;font-size:10px;">
                                {{ $cargo->periodo_label }}
                            </span>
                            @endif
                            &nbsp;·&nbsp;
                            Vence: {{ $cargo->fecha_vencimiento->format('d/m/Y') }}
                            @if($cargo->vencido)
                                <span style="color:#e74c3c;font-weight:600;margin-left:4px;">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    {{ number_format($cargo->dias_atraso,0) }} días de atraso
                                    @if($cargo->meses_retraso > 1)
                                        ({{ $cargo->meses_retraso }} meses)
                                    @endif
                                </span>
                            @endif
                        </div>
                        {{-- Badge beca --}}
                        @if($tieneBeca)
                        <div style="margin-top:4px;">
                            <span style="display:inline-block;background:#dff0d8;color:#3c763d;
                                         font-size:11px;font-weight:600;border-radius:10px;
                                         padding:2px 8px;border:1px solid #b2dfb2;">
                                <i class="fa fa-graduation-cap"></i>
                                Beca{{ $becaPorcentaje !== null ? ' '.$becaPorcentaje.'%' : '' }}: -${{ number_format($becaDescuento, 2) }}
                            </span>
                        </div>
                        @endif
                        {{-- Badges de recargo / descuento de política --}}
                        @if($tieneRecargo)
                        <div style="margin-top:4px;">
                            <span style="display:inline-block;background:#fdecea;color:#e74c3c;
                                         font-size:11px;font-weight:600;border-radius:10px;
                                         padding:2px 8px;border:1px solid #f5c6cb;">
                                <i class="fa fa-exclamation-triangle"></i>
                                Recargo por mora: +${{ number_format($cargo->recargo_calc, 2) }}
                                @if($cargo->meses_retraso > 1)
                                    × {{ $cargo->meses_retraso }} meses
                                @endif
                            </span>
                        </div>
                        @elseif($tieneDescuento)
                        <div style="margin-top:4px;">
                            <span style="display:inline-block;background:#eafaf1;color:#27ae60;
                                         font-size:11px;font-weight:600;border-radius:10px;
                                         padding:2px 8px;border:1px solid #c3e6cb;">
                                <i class="fa fa-tag"></i>
                                Dto. pronto pago: -${{ number_format($cargo->descuento_calc, 2) }}
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Montos --}}
                    <div class="cargo-montos">
                        @if($cargo->abonado > 0)
                        <div class="cargo-monto-orig">
                            ${{ number_format($cargo->monto_original, 2) }}
                        </div>
                        @endif
                        @if($tieneAjuste)
                            <div style="font-size:12px;color:#aaa;text-decoration:line-through;">
                                ${{ number_format($cargo->pendiente, 2) }}
                            </div>
                            <div class="cargo-monto-pend"
                                 style="color:{{ $tieneRecargo ? '#e74c3c' : '#27ae60' }};">
                                ${{ number_format($cargo->monto_a_pagar_hoy, 2) }}
                            </div>
                            <div style="font-size:10px;color:{{ $tieneRecargo ? '#e74c3c' : '#27ae60' }};">
                                A pagar hoy
                            </div>
                            @if($tieneBeca)
                            <div style="font-size:10px;color:#3c763d;">
                                <i class="fa fa-graduation-cap"></i>
                                beca{{ $becaPorcentaje !== null ? ' '.number_format($becaPorcentaje,0).'%' : '' }}
                            </div>
                            @endif
                        @else
                            <div class="cargo-monto-pend {{ $cargo->abonado > 0 ? '' : 'ok' }}">
                                ${{ number_format($cargo->pendiente, 2) }}
                            </div>
                            <div style="font-size:10px;color:#bbb;">Pendiente</div>
                        @endif
                    </div>
                </div>

                {{-- Panel de detalle (visible solo cuando está seleccionado) --}}
                <div class="cargo-detalle">
                    <input type="hidden" name="items[{{ $i }}][tipo]" value="cargo">
                    <input type="hidden" name="items[{{ $i }}][cargo_id]" value="{{ $cargo->id }}">

                    @if($tieneBeca)
                    <div style="margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:12px;
                                background:#dff0d8;color:#2d6a2d;border:1px solid #b2dfb2;">
                        <i class="fa fa-graduation-cap"></i>
                        <strong>Descuento por beca aplicado:</strong>
                        Se aplica automáticamente un descuento de <strong>-${{ number_format($becaDescuento, 2) }}</strong>
                        @if($becaPorcentaje !== null)({{ number_format($becaPorcentaje, 0) }}% del saldo)@endif
                        según la beca asignada.
                    </div>
                    @endif
                    @if($tieneRecargo || $tieneDescuento)
                    <div style="margin-bottom:10px;padding:8px 12px;border-radius:6px;font-size:12px;
                                {{ $tieneRecargo
                                    ? 'background:#fdecea;color:#c0392b;border:1px solid #f5c6cb;'
                                    : 'background:#eafaf1;color:#1e8449;border:1px solid #c3e6cb;' }}">
                        @if($tieneRecargo)
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Recargo por mora aplicado:</strong>
                            Cargo vencido hace {{ $cargo->dias_atraso }} días ({{ $cargo->meses_retraso }} {{ $cargo->meses_retraso === 1 ? 'mes' : 'meses' }}).
                            Se añade automáticamente un recargo de <strong>+${{ number_format($cargo->recargo_calc, 2) }}</strong>
                            según la política del plan de pagos.
                        @else
                            <i class="fa fa-tag"></i>
                            <strong>Descuento de pronto pago:</strong>
                            Se aplica automáticamente un descuento de <strong>-${{ number_format($cargo->descuento_calc, 2) }}</strong>
                            según la política del plan de pagos vigente hoy.
                        @endif
                    </div>
                    @endif

                    {{-- Valores de beca y pronto pago: ocultos para el form, visibles como etiqueta --}}
                    <input type="hidden" name="items[{{ $i }}][descuento_beca]"        value="{{ $becaDescuento }}">
                    <input type="hidden" name="items[{{ $i }}][descuento_pronto_pago]" value="{{ $cargo->descuento_calc }}">

                    <div class="row">
                        {{-- Monto a abonar --}}
                        <div class="col-md-3">
                            <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:4px;">
                                Monto a cobrar
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon" style="background:#3c8dbc;color:#fff;font-weight:700;border:2px solid #3c8dbc;border-right:none;">$</span>
                                <input type="number"
                                       class="form-control monto-input item-monto"
                                       value="{{ $cargo->monto_a_pagar_hoy }}"
                                       min="0.01"
                                       step="0.01"
                                       data-idx="{{ $i }}">
                            </div>
                            {{-- Campo oculto: mantiene el saldo base para el seguimiento del estado del cargo --}}
                            <input type="hidden"
                                   name="items[{{ $i }}][monto_abonado]"
                                   class="item-monto-oculto"
                                   value="{{ $cargo->pendiente }}">
                            <div style="font-size:11px;color:#aaa;margin-top:4px;">
                                Saldo pendiente: ${{ number_format($cargo->pendiente, 2) }}
                            </div>
                        </div>

                        {{-- Etiqueta: Descuento beca --}}
                        <div class="col-md-2">
                            <div style="font-size:12px;font-weight:600;margin-bottom:6px;
                                        color:{{ $tieneBeca ? '#3c763d' : '#aaa' }};">
                                <i class="fa fa-graduation-cap"></i> Desc. beca
                            </div>
                            <div style="font-size:16px;font-weight:700;
                                        color:{{ $tieneBeca ? '#3c763d' : '#ccc' }};">
                                {{ $tieneBeca ? '-$'.number_format($becaDescuento, 2) : '—' }}
                            </div>
                            @if($tieneBeca)
                            <div style="font-size:10px;color:#3c763d;margin-top:3px;">Auto</div>
                            @endif
                        </div>

                        {{-- Etiqueta: Descuento pronto pago --}}
                        <div class="col-md-2">
                            <div style="font-size:12px;font-weight:600;margin-bottom:6px;
                                        color:{{ $tieneDescuento ? '#27ae60' : '#aaa' }};">
                                <i class="fa fa-tag"></i> Pronto pago
                            </div>
                            <div id="label-pronto-pago-{{ $i }}"
                                 style="font-size:16px;font-weight:700;
                                        color:{{ $tieneDescuento ? '#27ae60' : '#ccc' }};">
                                {{ $tieneDescuento ? '-$'.number_format($cargo->descuento_calc, 2) : '—' }}
                            </div>
                            @if($tieneDescuento)
                            <div style="font-size:10px;color:#27ae60;margin-top:3px;">Auto</div>
                            @endif
                        </div>

                        {{-- Descuento extra (manual) --}}
                        <div class="col-md-2">
                            <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:4px;">
                                Desc. extra
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon" style="font-size:11px;">$</span>
                                <input type="number"
                                       name="items[{{ $i }}][descuento_otros]"
                                       class="form-control input-sm item-desc"
                                       value="0" min="0" step="0.01"
                                       data-idx="{{ $i }}">
                            </div>
                        </div>

                        {{-- Recargo --}}
                        <div class="col-md-1">
                            <label style="font-size:12px;{{ $tieneRecargo ? 'color:#e74c3c;' : 'color:#555;' }}font-weight:600;display:block;margin-bottom:4px;">
                                Recargo
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon"
                                      style="font-size:10px;padding:6px 4px;{{ $tieneRecargo ? 'background:#fdecea;color:#e74c3c;border-color:#f5c6cb;' : '' }}">$</span>
                                <input type="number"
                                       name="items[{{ $i }}][recargo]"
                                       class="form-control input-sm item-desc"
                                       value="{{ $cargo->recargo_calc }}" min="0" step="0.01"
                                       data-idx="{{ $i }}"
                                       style="{{ $tieneRecargo ? 'border-color:#f5c6cb;color:#c0392b;font-weight:600;' : '' }}">
                            </div>
                            @if($tieneRecargo)
                            <div style="font-size:10px;color:#e74c3c;margin-top:3px;">
                                Mora auto
                            </div>
                            @endif
                        </div>

                        {{-- Total del ítem --}}
                        <div class="col-md-2" style="text-align:right;padding-top:20px;">
                            <div style="font-size:11px;color:#aaa;margin-bottom:2px;">Total ítem</div>
                            <div style="font-size:18px;font-weight:700;color:{{ $tieneRecargo ? '#e74c3c' : ($tieneAjuste ? '#27ae60' : '#3c8dbc') }};"
                                 id="total-item-{{ $i }}">
                                ${{ number_format($cargo->monto_a_pagar_hoy, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @empty
            <div style="text-align:center;padding:40px 0;color:#ccc;">
                <i class="fa fa-check-circle" style="font-size:36px;color:#27ae60;display:block;margin-bottom:10px;"></i>
                <strong style="color:#27ae60;font-size:15px;">Al corriente</strong>
                <p style="font-size:13px;margin-top:6px;">Este alumno no tiene cargos pendientes.</p>
            </div>
            @endforelse

        </div>
    </div>

    {{-- ── Cargo único en el momento ── --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-plus-circle" style="color:#27ae60;"></i>
                Agregar concepto en este momento
            </h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-success btn-xs btn-flat" id="btn-agregar-nuevo">
                    <i class="fa fa-plus"></i> Agregar
                </button>
            </div>
        </div>
        <div class="box-body" id="contenedor-nuevos">
            @if(! $inscripcionActual && $inscripcionParaCobro)
            <div class="alert alert-warning" style="font-size:12px;margin-bottom:10px;padding:8px 12px;">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Sin inscripción activa.</strong>
                El nuevo concepto se asociará al ciclo más reciente:
                <strong>{{ $inscripcionParaCobro->ciclo?->nombre ?? 'desconocido' }}</strong>.
            </div>
            @endif
            <p class="text-muted" style="font-size:13px;margin:0;" id="txt-sin-nuevos">
                <i class="fa fa-info-circle"></i>
                Usa este panel para cobrar un concepto que no está en los cargos pendientes
                (ej. papelería, evento, taller).
            </p>
        </div>
    </div>

</div>{{-- /col-md-8 --}}

{{-- ════════════════════════════ COLUMNA RESUMEN ════════════════════════════ --}}
<div class="col-md-4">
    <div class="resumen-box">

        <div class="resumen-header">
            <i class="fa fa-shopping-cart"></i>
            Resumen del cobro
        </div>

        {{-- Líneas de resumen --}}
        <div id="resumen-items">
            <div class="resumen-linea" style="color:#aaa;font-size:12px;justify-content:center;">
                Sin conceptos seleccionados
            </div>
        </div>

        {{-- Total --}}
        <div class="resumen-total">
            <div class="resumen-total-label">Total a cobrar</div>
            <div class="resumen-total-monto" id="resumen-total">$0.00</div>
        </div>

        {{-- Forma de pago --}}
        <div style="padding:14px 18px;border-top:1px solid #f0f0f0;">
            <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:8px;">
                Forma de pago <span class="text-red">*</span>
            </label>
            <div class="row" style="margin:0;gap:0;">
                @foreach(['efectivo'=>['fa-money','#27ae60'],'transferencia'=>['fa-exchange','#3c8dbc'],'tarjeta'=>['fa-credit-card','#9c27b0'],'cheque'=>['fa-bank','#607d8b']] as $forma=>[$icon,$color])
                <div class="col-xs-6" style="padding:3px;">
                    <label style="display:block;margin:0;">
                        <input type="radio" name="forma_pago" value="{{ $forma }}"
                               class="forma-radio" style="display:none;">
                        <div class="forma-btn" data-forma="{{ $forma }}" style="
                            border:2px solid #e0e0e0;border-radius:6px;padding:8px 4px;
                            text-align:center;cursor:pointer;transition:all .15s;
                            font-size:11px;color:#666;
                        ">
                            <i class="fa {{ $icon }}" style="font-size:16px;display:block;margin-bottom:2px;color:#bbb;"></i>
                            {{ ucfirst($forma) }}
                        </div>
                    </label>
                </div>
                @endforeach
            </div>

            {{-- Referencia --}}
            <div id="campo-referencia" style="display:none;margin-top:10px;">
                <label style="font-size:12px;color:#555;font-weight:600;">Referencia / Folio bancario</label>
                <input type="text" name="referencia" class="form-control input-sm"
                       placeholder="Número de transferencia...">
            </div>

            {{-- Efectivo: ¿con cuánto paga? --}}
            <div id="bloque-efectivo" style="display:none;margin-top:12px;">
                <label style="font-size:12px;color:#27ae60;font-weight:700;display:block;margin-bottom:6px;">
                    <i class="fa fa-money"></i> ¿Con cuánto paga el cliente?
                </label>
                <div class="input-group">
                    <span class="input-group-addon"
                          style="background:#27ae60;color:#fff;font-weight:700;font-size:15px;
                                 border:2px solid #27ae60;border-right:none;">$</span>
                    <input type="number" id="monto-entregado"
                           class="form-control"
                           placeholder="0.00" min="0" step="0.01"
                           style="font-size:18px;font-weight:700;text-align:right;
                                  border:2px solid #27ae60;border-left:none;height:42px;">
                </div>
                <div style="display:flex;gap:4px;margin-top:6px;flex-wrap:wrap;">
                    @foreach([50,100,200,500,1000] as $bill)
                    <button type="button" class="btn btn-xs btn-default btn-billete"
                            data-monto="{{ $bill }}"
                            style="font-size:11px;border-radius:10px;padding:2px 8px;">
                        ${{ $bill }}
                    </button>
                    @endforeach
                </div>

                {{-- Cambio a devolver --}}
                <div id="bloque-cambio" style="display:none;margin-top:10px;padding:12px 14px;
                     border-radius:8px;background:#e8f8f0;border:2px solid #00a65a;text-align:center;">
                    <div style="font-size:10px;color:#00875a;text-transform:uppercase;
                                letter-spacing:.06em;font-weight:700;margin-bottom:4px;">
                        <i class="fa fa-arrow-left"></i> Cambio a devolver
                    </div>
                    <div id="monto-cambio"
                         style="font-size:30px;font-weight:800;color:#00875a;line-height:1;">
                        $0.00
                    </div>
                </div>

                {{-- Monto insuficiente --}}
                <div id="bloque-faltante" style="display:none;margin-top:10px;padding:10px 14px;
                     border-radius:8px;background:#fdecea;border:2px solid #dd4b39;text-align:center;">
                    <div style="font-size:10px;color:#b91c1c;text-transform:uppercase;
                                letter-spacing:.06em;font-weight:700;margin-bottom:4px;">
                        <i class="fa fa-exclamation-triangle"></i> Monto insuficiente
                    </div>
                    <div id="monto-faltante"
                         style="font-size:20px;font-weight:800;color:#b91c1c;">
                        $0.00 de falta
                    </div>
                </div>
            </div>
        </div>

        {{-- Fecha --}}
        <div style="padding:0 18px 14px;">
            <label style="font-size:12px;color:#555;font-weight:600;">Fecha de pago</label>
            <input type="date" name="fecha_pago" class="form-control"
                   value="{{ now()->format('Y-m-d') }}" required>
        </div>

        {{-- Botón cobrar --}}
        <div style="padding:0 18px 18px;">
            <button type="submit" class="btn btn-success btn-block btn-flat" id="btn-cobrar"
                    disabled>
                <i class="fa fa-check-circle"></i>
                Registrar cobro
            </button>
            <a href="{{ route('cobros.index') }}"
               class="btn btn-default btn-block btn-flat btn-sm" style="margin-top:6px;">
                Cancelar
            </a>
        </div>

    </div>
</div>

</div>{{-- /row --}}
</form>

{{-- Template para nuevo concepto --}}
<script type="text/template" id="tpl-nuevo">
<div class="nuevo-concepto-panel" id="nuevo-panel-__IDX__">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <strong style="font-size:13px;color:#27ae60;">
            <i class="fa fa-plus-circle"></i> Nuevo concepto
        </strong>
        <button type="button" class="btn btn-danger btn-xs" onclick="quitarNuevo(__IDX__)">
            <i class="fa fa-times"></i>
        </button>
    </div>
    <input type="hidden" name="items[__IDX__][tipo]" value="nuevo">
    <input type="hidden" name="items[__IDX__][inscripcion_id]" value="{{ $inscripcionParaCobro?->id }}">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label style="font-size:12px;">Concepto <span class="text-red">*</span></label>
                <select name="items[__IDX__][concepto_id]" class="form-control input-sm nuevo-concepto-sel">
                    <option value="">-- Selecciona --</option>
                    @foreach($conceptos as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label style="font-size:12px;">Monto <span class="text-red">*</span></label>
                <div class="input-group">
                    <span class="input-group-addon" style="background:#27ae60;color:#fff;border:2px solid #27ae60;border-right:none;">$</span>
                    <input type="number" name="items[__IDX__][monto_abonado]"
                           class="form-control item-monto" placeholder="0.00"
                           min="0.01" step="0.01" data-idx="__IDX__">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label style="font-size:12px;">Desc. extra</label>
                <div class="input-group">
                    <span class="input-group-addon" style="font-size:11px;">$</span>
                    <input type="number" name="items[__IDX__][descuento_otros]"
                           class="form-control input-sm item-desc" value="0" min="0" step="0.01" data-idx="__IDX__">
                </div>
            </div>
        </div>
        <div class="col-md-3" style="padding-top:20px;text-align:right;">
            <div style="font-size:11px;color:#aaa;">Total ítem</div>
            <div style="font-size:18px;font-weight:700;color:#27ae60;" id="total-item-__IDX__">
                $0.00
            </div>
        </div>
    </div>
    <input type="hidden" name="items[__IDX__][descuento_beca]" value="0">
    <input type="hidden" name="items[__IDX__][recargo]" value="0">
</div>
</script>

@endsection

@push('scripts')
<script>
$(function() {

    var cargosSeleccionados = {};  // { cargoId: { idx, monto, desc_beca, desc_otros, recargo } }
    var nuevosItems        = {};   // { idx: { monto, desc } }
    var nuevoIdx           = {{ $cargos->count() }};

    // ══════════════════════════════════════════════════
    // TOGGLE CARGO
    // ══════════════════════════════════════════════════
    window.toggleCargo = function(cargoId, pendiente) {
        var $card    = $('#cargo-card-' + cargoId);
        var $chk     = $('#chk-visual-' + cargoId);
        var selected = $card.hasClass('seleccionado');

        if (selected) {
            $card.removeClass('seleccionado');
            $chk.css({ background: '#fff', borderColor: '#ccc' }).html('');
            delete cargosSeleccionados[cargoId];
        } else {
            $card.addClass('seleccionado');
            $chk.css({ background: '#3c8dbc', borderColor: '#3c8dbc' })
                .html('<i class="fa fa-check" style="color:#fff;font-size:12px;"></i>');
            cargosSeleccionados[cargoId] = { pendiente: pendiente };
        }

        actualizarResumen();
        actualizarBtnCobrar();
    };

    // NO usamos disabled — en su lugar controlamos via reindexado en submit

    // ── Seleccionar todos ─────────────────────────────
    $('#btn-sel-todos').on('click', function() {
        $('.cargo-item').each(function() {
            var id       = $(this).data('cargo-id');
            var pagarHoy = $(this).data('pagar-hoy');
            if (!$(this).hasClass('seleccionado')) {
                toggleCargo(id, pagarHoy);
            }
        });
    });

    // ── Recalcular total de ítem al cambiar inputs ────
    $(document).on('input', '.item-monto, .item-desc', function() {
        var idx = $(this).data('idx');
        // Si el cajero modificó el monto visible o el recargo, sincronizar el campo base oculto
        if ($(this).hasClass('item-monto') || $(this).closest('.cargo-detalle').length) {
            sincronizarBaseDesdeVisible(idx);
        }
        recalcularItem(idx);
        actualizarResumen();
    });

    /**
     * Recalcula el campo oculto monto_abonado (base para el estado del cargo) a partir
     * del monto visible ingresado por el cajero. Fórmula: base = visible + beca + pp - recargo
     * Esto garantiza que al restar los descuentos y sumar el recargo en el backend
     * se obtenga exactamente el monto visible como monto_final.
     */
    function sincronizarBaseDesdeVisible(idx) {
        var $det = $('[data-idx="' + idx + '"].item-monto').closest('.cargo-detalle');
        if (!$det.length) return; // solo aplica a cargos existentes, no a nuevos conceptos
        var visible = parseFloat($det.find('.item-monto').val()) || 0;
        var dBeca   = parseFloat($det.find('input[name*="[descuento_beca]"]').val())        || 0;
        var dPP     = parseFloat($det.find('input[name*="[descuento_pronto_pago]"]').val()) || 0;
        var recarg  = parseFloat($det.find('input[name*="[recargo]"]').val())                || 0;
        $det.find('.item-monto-oculto').val(Math.max(0.01, visible + dBeca + dPP - recarg).toFixed(2));
    }

    function recalcularItem(idx) {
        var $row   = $('[data-idx="' + idx + '"]').closest('.cargo-detalle, .nuevo-concepto-panel');
        var monto  = parseFloat($row.find('input[name*="[monto_abonado]"]').val()) || 0;

        // Recalcular pronto pago proporcional al monto ingresado (solo cargos existentes)
        var $card = $row.closest('.cargo-item');
        if ($card.length) {
            var tipo  = $card.data('descuento-tipo');
            var valor = parseFloat($card.data('descuento-valor')) || 0;
            var nuevoPP = 0;
            if (tipo === 'porcentaje' && valor > 0) {
                nuevoPP = Math.round(monto * valor / 100 * 100) / 100;
            } else if (tipo === 'monto_fijo' && valor > 0) {
                nuevoPP = Math.min(valor, monto);
            }
            $row.find('input[name*="[descuento_pronto_pago]"]').val(nuevoPP.toFixed(2));
            var $lbl = $('#label-pronto-pago-' + idx);
            if ($lbl.length) {
                $lbl.text(nuevoPP > 0 ? '-$' + nuevoPP.toFixed(2) : '—');
            }
        }

        var dBeca       = parseFloat($row.find('input[name*="[descuento_beca]"]').val()) || 0;
        var dProntoPago = parseFloat($row.find('input[name*="[descuento_pronto_pago]"]').val()) || 0;
        var dOtros      = parseFloat($row.find('input[name*="[descuento_otros]"]').val()) || 0;
        var recarg      = parseFloat($row.find('input[name*="[recargo]"]').val()) || 0;
        var total       = Math.max(0, monto - dBeca - dProntoPago - dOtros + recarg);
        $('#total-item-' + idx).text('$' + total.toFixed(2));
        return total;
    }

    // ══════════════════════════════════════════════════
    // NUEVO CONCEPTO
    // ══════════════════════════════════════════════════
    $('#btn-agregar-nuevo').on('click', function() {
        if (!{{ $inscripcionParaCobro ? 'true' : 'false' }}) {
            alert('El alumno no tiene ninguna inscripción registrada. No se puede agregar un concepto nuevo.');
            return;
        }
        $('#txt-sin-nuevos').hide();
        var idx = nuevoIdx++;
        var tpl = $('#tpl-nuevo').html().replace(/__IDX__/g, idx);
        $('#contenedor-nuevos').append(tpl);
        nuevosItems[idx] = true;
        actualizarBtnCobrar();
    });

    window.quitarNuevo = function(idx) {
        $('#nuevo-panel-' + idx).remove();
        delete nuevosItems[idx];
        actualizarResumen();
        actualizarBtnCobrar();
        if (!Object.keys(nuevosItems).length) $('#txt-sin-nuevos').show();
    };

    // ══════════════════════════════════════════════════
    // FORMA DE PAGO
    // ══════════════════════════════════════════════════
    $(document).on('click', '.forma-btn', function() {
        var forma = $(this).data('forma');
        $('.forma-btn').css({
            background: '#fff', borderColor: '#e0e0e0', color: '#666'
        }).find('i').css('color', '#bbb');

        $(this).css({
            background: '#e8f4ff', borderColor: '#3c8dbc', color: '#1e4d7b'
        }).find('i').css('color', '#3c8dbc');

        $(this).closest('label').find('.forma-radio').prop('checked', true);

        $('#campo-referencia').toggle(forma === 'transferencia' || forma === 'cheque');

        if (forma === 'efectivo') {
            $('#bloque-efectivo').show();
            sugerirMonto();
            calcularCambio();
        } else {
            $('#bloque-efectivo').hide();
            $('#bloque-cambio, #bloque-faltante').hide();
        }

        actualizarBtnCobrar();
    });

    // ── Botones de billete ────────────────────────────
    $(document).on('click', '.btn-billete', function() {
        var monto = parseFloat($(this).data('monto'));
        $('#monto-entregado').val(monto.toFixed(2));
        $('.btn-billete').removeClass('activo');
        $(this).addClass('activo');
        calcularCambio();
    });

    // ── Cambio al escribir en el campo ───────────────
    $('#monto-entregado').on('input', function() {
        $('.btn-billete').removeClass('activo');
        calcularCambio();
    });

    // ══════════════════════════════════════════════════
    // CÁLCULO DE CAMBIO (efectivo)
    // ══════════════════════════════════════════════════
    function obtenerTotal() {
        return parseFloat($('#resumen-total').text().replace(/[$,]/g, '')) || 0;
    }

    function sugerirMonto() {
        var total = obtenerTotal();
        if (total <= 0 || $('#monto-entregado').val()) return;
        var billetes = [20, 50, 100, 200, 500, 1000];
        var sugerido = billetes.find(function(b) { return b >= total; }) || total;
        $('#monto-entregado').val(sugerido.toFixed(2));
    }

    function calcularCambio() {
        var total     = obtenerTotal();
        var entregado = parseFloat($('#monto-entregado').val()) || 0;

        $('#bloque-cambio').hide();
        $('#bloque-faltante').hide();

        if (entregado <= 0 || total <= 0) return;

        var diff = entregado - total;
        if (diff >= 0) {
            $('#monto-cambio').text('$' + diff.toFixed(2));
            $('#bloque-cambio').show();
        } else {
            $('#monto-faltante').text('$' + Math.abs(diff).toFixed(2) + ' de falta');
            $('#bloque-faltante').show();
        }
    }

    // ══════════════════════════════════════════════════
    // RESUMEN Y BOTÓN
    // ══════════════════════════════════════════════════
    function actualizarResumen() {
        var $contenedor = $('#resumen-items');
        $contenedor.empty();
        var totalGlobal = 0;

        // Cargos seleccionados
        $('.cargo-item.seleccionado').each(function() {
            var idx    = $(this).find('.item-monto').data('idx');
            var total  = recalcularItem(idx);
            var nombre = $(this).find('.cargo-concepto').text().trim();
            totalGlobal += total;
            $contenedor.append(
                '<div class="resumen-linea">' +
                '<span style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
                    nombre + '</span>' +
                '<strong>$' + total.toFixed(2) + '</strong>' +
                '</div>'
            );
        });

        // Nuevos conceptos
        $('.nuevo-concepto-panel').each(function() {
            var idx    = $(this).attr('id').replace('nuevo-panel-', '');
            var total  = recalcularItem(idx);
            var nombre = $(this).find('.nuevo-concepto-sel option:selected').text() || 'Nuevo concepto';
            if (total > 0) {
                totalGlobal += total;
                $contenedor.append(
                    '<div class="resumen-linea">' +
                    '<span style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
                        nombre + '</span>' +
                    '<strong>$' + total.toFixed(2) + '</strong>' +
                    '</div>'
                );
            }
        });

        if (!$contenedor.children().length) {
            $contenedor.html('<div class="resumen-linea" style="color:#aaa;font-size:12px;justify-content:center;">Sin conceptos seleccionados</div>');
        }

        $('#resumen-total').text('$' + totalGlobal.toFixed(2));

        // Recalcular cambio si la forma de pago es efectivo
        if ($('input[name="forma_pago"]:checked').val() === 'efectivo') {
            if (!$('#monto-entregado').val()) sugerirMonto();
            calcularCambio();
        }
    }

    function actualizarBtnCobrar() {
        var hayItems     = $('.cargo-item.seleccionado').length > 0 || $('.nuevo-concepto-panel').length > 0;
        var hayFormaPago = $('input[name="forma_pago"]:checked').length > 0;
        $('#btn-cobrar').prop('disabled', !(hayItems && hayFormaPago));
    }

    // También actualizar al cambiar selects de nuevo concepto
    $(document).on('change', '.nuevo-concepto-sel', function() {
        actualizarResumen();
    });

    // ══════════════════════════════════════════════════
    // SUBMIT — reindexar items y validar
    // ══════════════════════════════════════════════════
    $('#form-cobro').on('submit', function(e) {
        var hayItems = $('.cargo-item.seleccionado').length > 0 ||
                       $('.nuevo-concepto-panel').length > 0;

        if (!hayItems) {
            e.preventDefault();
            alert('Selecciona al menos un concepto para cobrar.');
            return false;
        }

        var formaPago = $('input[name="forma_pago"]:checked').val();
        if (!formaPago) {
            e.preventDefault();
            alert('Selecciona la forma de pago.');
            return false;
        }

        // Validar nuevos
        var ok = true;
        $('.nuevo-concepto-panel').each(function() {
            var monto = parseFloat($(this).find('input[name*="[monto_abonado]"]').val());
            var conc  = $(this).find('.nuevo-concepto-sel').val();
            if (!conc || !monto || monto <= 0) {
                alert('Completa el concepto y monto de los cargos nuevos agregados.');
                ok = false; return false;
            }
        });
        if (!ok) { e.preventDefault(); return false; }

        // ── Reindexar: solo enviar items seleccionados ──
        // Recolectar datos de cargos seleccionados
        var itemsData = [];

        $('.cargo-item.seleccionado').each(function() {
            var $d = $(this).find('.cargo-detalle');
            itemsData.push({
                tipo:                  'cargo',
                cargo_id:              $d.find('input[name*="[cargo_id]"]').val(),
                monto_abonado:         $d.find('input[name*="[monto_abonado]"]').val() || '0',
                descuento_beca:        $d.find('input[name*="[descuento_beca]"]').val() || '0',
                descuento_pronto_pago: $d.find('input[name*="[descuento_pronto_pago]"]').val() || '0',
                descuento_otros:       $d.find('input[name*="[descuento_otros]"]').val() || '0',
                recargo:               $d.find('input[name*="[recargo]"]').val() || '0',
            });
        });

        // Nuevos conceptos
        $('.nuevo-concepto-panel').each(function() {
            itemsData.push({
                tipo:                  'nuevo',
                concepto_id:           $(this).find('select[name*="[concepto_id]"]').val(),
                inscripcion_id:        $(this).find('input[name*="[inscripcion_id]"]').val(),
                monto_abonado:         $(this).find('input[name*="[monto_abonado]"]').val() || '0',
                descuento_beca:        '0',
                descuento_pronto_pago: '0',
                descuento_otros:       $(this).find('input[name*="[descuento_otros]"]').val() || '0',
                recargo:               '0',
            });
        });

        // Eliminar todos los inputs de items existentes del form
        $(this).find('input[name^="items["], select[name^="items["]').remove();

        // Agregar inputs limpios reindexados
        var $form = $(this);
        itemsData.forEach(function(item, idx) {
            $.each(item, function(key, val) {
                $('<input>').attr({
                    type:  'hidden',
                    name:  'items[' + idx + '][' + key + ']',
                    value: val
                }).appendTo($form);
            });
        });

        $('#btn-cobrar').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Registrando...');
    });

});
</script>
@endpush
