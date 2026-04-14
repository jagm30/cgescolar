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
                {{ $inscripcionActual->grupo->grado->nivel->nombre ?? '' }}
                {{ $inscripcionActual->grupo->grado->nombre }}°
                {{ $inscripcionActual->grupo->nombre }}
                &nbsp;·&nbsp;
                {{ $inscripcionActual->ciclo->nombre ?? '' }}
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
                $tieneRecargo   = $cargo->recargo_calc   > 0;
                $tieneDescuento = $cargo->descuento_calc > 0;
                $tieneAjuste    = $tieneRecargo || $tieneDescuento;
            @endphp
            <div class="cargo-item {{ $cargo->vencido ? 'vencido-card' : '' }}"
                 id="cargo-card-{{ $cargo->id }}"
                 data-cargo-id="{{ $cargo->id }}"
                 data-pendiente="{{ $cargo->pendiente }}"
                 data-recargo="{{ $cargo->recargo_calc }}"
                 data-descuento="{{ $cargo->descuento_calc }}"
                 data-pagar-hoy="{{ $cargo->monto_a_pagar_hoy }}">

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
                            &nbsp;
                            <code style="font-size:11px;">{{ $cargo->periodo }}</code>
                            &nbsp;·&nbsp;
                            Vence: {{ $cargo->fecha_vencimiento->format('d/m/Y') }}
                            @if($cargo->vencido)
                                <span style="color:#e74c3c;font-weight:600;margin-left:4px;">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    {{ $cargo->dias_atraso }} días de atraso
                                    @if($cargo->meses_retraso > 1)
                                        ({{ $cargo->meses_retraso }} meses)
                                    @endif
                                </span>
                            @endif
                        </div>
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

                    @if($tieneAjuste)
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

                    <div class="row">
                        {{-- Monto a abonar --}}
                        <div class="col-md-4">
                            <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:4px;">
                                Monto a cobrar
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon" style="background:#3c8dbc;color:#fff;font-weight:700;border:2px solid #3c8dbc;border-right:none;">$</span>
                                <input type="number"
                                       name="items[{{ $i }}][monto_abonado]"
                                       class="form-control monto-input item-monto"
                                       value="{{ $cargo->pendiente }}"
                                       min="0.01"
                                       max="{{ $cargo->pendiente }}"
                                       step="0.01"
                                       data-idx="{{ $i }}">
                            </div>
                            <div style="font-size:11px;color:#aaa;margin-top:4px;">
                                Saldo base: ${{ number_format($cargo->pendiente, 2) }}
                            </div>
                        </div>

                        {{-- Descuento beca --}}
                        <div class="col-md-2">
                            <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:4px;">
                                Desc. beca
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon" style="font-size:11px;">$</span>
                                <input type="number"
                                       name="items[{{ $i }}][descuento_beca]"
                                       class="form-control input-sm item-desc"
                                       value="0" min="0" step="0.01"
                                       data-idx="{{ $i }}">
                            </div>
                        </div>

                        {{-- Descuento extra --}}
                        <div class="col-md-2">
                            <label style="font-size:12px;color:#555;font-weight:600;display:block;margin-bottom:4px;">
                                Desc. extra
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon" style="font-size:11px;">$</span>
                                <input type="number"
                                       name="items[{{ $i }}][descuento_otros]"
                                       class="form-control input-sm item-desc"
                                       value="{{ $cargo->descuento_calc }}" min="0" step="0.01"
                                       data-idx="{{ $i }}">
                            </div>
                            @if($tieneDescuento)
                            <div style="font-size:10px;color:#27ae60;margin-top:3px;">
                                Pronto pago auto
                            </div>
                            @endif
                        </div>

                        {{-- Recargo --}}
                        <div class="col-md-2">
                            <label style="font-size:12px;{{ $tieneRecargo ? 'color:#e74c3c;' : 'color:#555;' }}font-weight:600;display:block;margin-bottom:4px;">
                                Recargo
                            </label>
                            <div class="input-group">
                                <span class="input-group-addon"
                                      style="font-size:11px;{{ $tieneRecargo ? 'background:#fdecea;color:#e74c3c;border-color:#f5c6cb;' : '' }}">$</span>
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
                            <div style="font-size:18px;font-weight:700;color:{{ $tieneRecargo ? '#e74c3c' : '#27ae60' }};"
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
    <input type="hidden" name="items[__IDX__][inscripcion_id]" value="{{ $inscripcionActual?->id }}">
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
        var idx   = $(this).data('idx');
        recalcularItem(idx);
        actualizarResumen();
    });

    function recalcularItem(idx) {
        var $row   = $('[data-idx="' + idx + '"]').closest('.cargo-detalle, .nuevo-concepto-panel');
        var monto  = parseFloat($row.find('input[name*="[monto_abonado]"]').val()) || 0;
        var dBeca  = parseFloat($row.find('input[name*="[descuento_beca]"]').val()) || 0;
        var dOtros = parseFloat($row.find('input[name*="[descuento_otros]"]').val()) || 0;
        var recarg = parseFloat($row.find('input[name*="[recargo]"]').val()) || 0;
        var total  = Math.max(0, monto - dBeca - dOtros + recarg);
        $('#total-item-' + idx).text('$' + total.toFixed(2));
        return total;
    }

    // ══════════════════════════════════════════════════
    // NUEVO CONCEPTO
    // ══════════════════════════════════════════════════
    $('#btn-agregar-nuevo').on('click', function() {
        if (!{{ $inscripcionActual ? 'true' : 'false' }}) {
            alert('El alumno no tiene inscripción activa. No se puede agregar un concepto nuevo.');
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
        actualizarBtnCobrar();
    });

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
                tipo:           'cargo',
                cargo_id:       $d.find('input[name*="[cargo_id]"]').val(),
                monto_abonado:  $d.find('input[name*="[monto_abonado]"]').val() || '0',
                descuento_beca: $d.find('input[name*="[descuento_beca]"]').val() || '0',
                descuento_otros:$d.find('input[name*="[descuento_otros]"]').val() || '0',
                recargo:        $d.find('input[name*="[recargo]"]').val() || '0',
            });
        });

        // Nuevos conceptos
        $('.nuevo-concepto-panel').each(function() {
            itemsData.push({
                tipo:            'nuevo',
                concepto_id:     $(this).find('select[name*="[concepto_id]"]').val(),
                inscripcion_id:  $(this).find('input[name*="[inscripcion_id]"]').val(),
                monto_abonado:   $(this).find('input[name*="[monto_abonado]"]').val() || '0',
                descuento_beca:  '0',
                descuento_otros: $(this).find('input[name*="[descuento_otros]"]').val() || '0',
                recargo:         '0',
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
