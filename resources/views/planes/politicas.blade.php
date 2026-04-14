@extends('layouts.master')

@section('page_title', 'Políticas del plan')
@section('page_subtitle', $plan->nombre)

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
    <li><a href="{{ route('planes.show', $plan->id) }}">{{ $plan->nombre }}</a></li>
    <li class="active">Políticas</li>
@endsection

@push('styles')
<style>
/* ── Cabecera del plan ─────────────────────── */
.plan-header {
    background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
    border-radius: 4px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
    color: #fff;
}
.plan-header-nombre { font-size: 20px; font-weight: 700; line-height: 1.2; }
.plan-header-sub    { font-size: 12px; opacity: .75; margin-top: 4px; }
.plan-badge {
    background: rgba(255,255,255,.2);
    color: #fff;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

/* ── Secciones ─────────────────────────────── */
.seccion-titulo {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .07em;
    font-weight: 700;
    color: #999;
    margin: 0 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.seccion-titulo::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #eee;
}

/* ── Tarjetas de política ──────────────────── */
.politica-card {
    border: 1px solid #e0e0e0;
    border-left: 4px solid #3c8dbc;
    border-radius: 6px;
    padding: 14px 16px;
    margin-bottom: 10px;
    background: #fff;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow .15s;
}
.politica-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.politica-card.recargo { border-left-color: #e74c3c; }
.politica-card.inactiva { opacity: .55; }

.politica-icono {
    width: 42px; height: 42px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.politica-nombre { font-size: 15px; font-weight: 700; color: #1a1a1a; }
.politica-detalle { font-size: 12px; color: #999; margin-top: 3px; }

.valor-badge {
    font-size: 20px;
    font-weight: 700;
    margin-left: auto;
    flex-shrink: 0;
    text-align: right;
    min-width: 90px;
}
.valor-badge small {
    display: block;
    font-size: 10px;
    font-weight: 400;
    color: #aaa;
    margin-top: 1px;
}

/* ── Formularios inline ───────────────────── */
.form-card {
    border: 2px dashed #d0dde8;
    border-radius: 8px;
    padding: 18px;
    background: #f8fbff;
    margin-bottom: 14px;
}
.form-card-titulo {
    font-size: 13px;
    font-weight: 700;
    color: #2c6fad;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.form-card.recargo-form { border-color: #f5c6c6; background: #fff8f8; }
.form-card.recargo-form .form-card-titulo { color: #c0392b; }

/* ── Tipo selector (radio visual) ─────────── */
.tipo-opt {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #666;
    transition: all .15s;
    margin-right: 8px;
    user-select: none;
}
.tipo-opt input { display: none; }
.tipo-opt.activo-desc { border-color: #3c8dbc; background: #e8f0fb; color: #1e4d7b; }
.tipo-opt.activo-rec  { border-color: #e74c3c; background: #fdecea; color: #c0392b; }

/* ── Simulador ─────────────────────────────── */
.simulador {
    background: #f8f8f8;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    padding: 12px 16px;
    margin-top: 14px;
    font-size: 13px;
}
.simulador-titulo {
    font-size: 11px; text-transform: uppercase;
    letter-spacing: .05em; color: #aaa; margin-bottom: 8px;
}
.simulador-fila {
    display: flex; justify-content: space-between;
    padding: 4px 0; border-bottom: 1px solid #f0f0f0;
}
.simulador-fila:last-child { border-bottom: none; font-weight: 700; font-size: 14px; }
</style>
@endpush

@section('content')

{{-- Alertas --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- Cabecera del plan ──────────────────────── --}}
<div class="plan-header">
    <div style="width:48px;height:48px;border-radius:12px;flex-shrink:0;
                background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
        <i class="fa fa-file-text-o" style="font-size:22px;color:rgba(255,255,255,.9);"></i>
    </div>
    <div style="flex:1;">
        <div class="plan-header-nombre">{{ $plan->nombre }}</div>
        <div class="plan-header-sub" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
            <span class="plan-badge">
                <i class="fa fa-refresh"></i>
                {{ ucfirst($plan->periodicidad) }}
            </span>
            <span class="plan-badge">
                <i class="fa fa-calendar"></i>
                {{ $plan->fecha_inicio->format('d/m/Y') }} — {{ $plan->fecha_fin->format('d/m/Y') }}
            </span>
            @if($plan->activo)
                <span class="plan-badge" style="background:rgba(39,174,96,.4);">
                    <i class="fa fa-circle"></i> Activo
                </span>
            @else
                <span class="plan-badge" style="background:rgba(0,0,0,.3);">
                    <i class="fa fa-circle-o"></i> Inactivo
                </span>
            @endif
        </div>
    </div>
    <a href="{{ route('planes.index') }}"
       class="btn btn-xs btn-flat"
       style="color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.1);">
        <i class="fa fa-arrow-left"></i> Volver
    </a>
</div>

{{-- Alerta AJAX global --}}
<div id="alerta-global" style="display:none;" class="alert alert-dismissible">
    <button type="button" class="close" onclick="$('#alerta-global').hide()">&times;</button>
    <span id="alerta-global-msg"></span>
</div>

<div class="row">

{{-- ════════════════════════════════════════════════════
     DESCUENTOS
════════════════════════════════════════════════════ --}}
<div class="col-md-7">

    {{-- ── DESCUENTOS ── --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-tag" style="color:#3c8dbc;"></i>
                Descuentos
                <span class="badge bg-blue" style="margin-left:6px;">
                    {{ $plan->politicasDescuento->count() }}
                </span>
            </h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-primary btn-xs btn-flat"
                        id="btn-nuevo-descuento">
                    <i class="fa fa-plus"></i> Nuevo descuento
                </button>
            </div>
        </div>
        <div class="box-body">

            {{-- Formulario nuevo descuento (oculto) --}}
            <div id="form-nuevo-descuento" style="display:none;">
                <div class="form-card" id="form-desc-card">
                    <div class="form-card-titulo">
                        <i class="fa fa-tag"></i>
                        <span id="form-desc-titulo">Nuevo descuento</span>
                    </div>
                    <form id="form-descuento" autocomplete="off">
                        <input type="hidden" id="desc-id" value="">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:600;">
                                        Nombre del descuento <span class="text-red">*</span>
                                    </label>
                                    <input type="text" id="desc-nombre" class="form-control"
                                           placeholder="Ej: Pronto pago, Hermanos, Pago anual"
                                           maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:600;">
                                        Día límite para aplicar
                                    </label>
                                    <div class="input-group">
                                        <input type="number" id="desc-dia" class="form-control"
                                               placeholder="Ej: 5 (día 5 de cada mes)"
                                               min="1" max="31">
                                        <span class="input-group-addon">de cada mes</span>
                                    </div>
                                    <span style="font-size:11px;color:#aaa;">
                                        Dejar vacío si aplica siempre
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:8px;">
                                    Tipo de descuento <span class="text-red">*</span>
                                </label>
                                <label class="tipo-opt" id="opt-desc-pct">
                                    <input type="radio" name="desc_tipo" value="porcentaje">
                                    <i class="fa fa-percent" style="font-size:14px;"></i>
                                    Porcentaje
                                </label>
                                <label class="tipo-opt" id="opt-desc-monto">
                                    <input type="radio" name="desc_tipo" value="monto_fijo">
                                    <i class="fa fa-dollar" style="font-size:14px;"></i>
                                    Monto fijo
                                </label>
                            </div>
                        </div>

                        <div class="row" style="margin-top:14px;">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:600;" id="lbl-desc-valor">
                                        Valor <span class="text-red">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="prefix-desc">%</span>
                                        <input type="number" id="desc-valor" class="form-control"
                                               placeholder="0.00" min="0.01" step="0.01">
                                        <span class="input-group-addon" id="suffix-desc"
                                              style="display:none;">de descuento</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" style="padding-top:20px;">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="desc-activo" checked>
                                    Activo
                                </label>
                            </div>
                        </div>

                        {{-- Simulador --}}
                        <div class="simulador" id="simulador-desc" style="display:none;">
                            <div class="simulador-titulo">
                                <i class="fa fa-calculator"></i> Simulación — monto de referencia
                                <input type="number" id="sim-desc-base" class="form-control input-sm"
                                       style="width:100px;display:inline-block;margin-left:8px;"
                                       value="1000" min="1" placeholder="Monto">
                            </div>
                            <div class="simulador-fila">
                                <span>Monto original</span>
                                <span id="sim-desc-original">$1,000.00</span>
                            </div>
                            <div class="simulador-fila" style="color:#3c8dbc;">
                                <span>Descuento aplicado</span>
                                <span id="sim-desc-descuento">—</span>
                            </div>
                            <div class="simulador-fila" style="color:#27ae60;">
                                <span>Monto a pagar</span>
                                <span id="sim-desc-final">—</span>
                            </div>
                        </div>

                        <div style="margin-top:14px;text-align:right;">
                            <button type="button" class="btn btn-default btn-sm"
                                    id="btn-cancelar-desc">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm"
                                    id="btn-guardar-desc">
                                <i class="fa fa-save"></i> Guardar descuento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Lista de descuentos --}}
            <div class="seccion-titulo">
                <i class="fa fa-list"></i> Descuentos configurados
            </div>

            <div id="lista-descuentos">
                @forelse($plan->politicasDescuento as $desc)
                <div class="politica-card {{ !$desc->activo ? 'inactiva' : '' }}"
                     data-id="{{ $desc->id }}"
                     data-nombre="{{ $desc->nombre }}"
                     data-tipo="{{ $desc->tipo_valor }}"
                     data-valor="{{ $desc->valor }}"
                     data-dia="{{ $desc->dia_limite }}"
                     data-activo="{{ $desc->activo ? '1' : '0' }}">

                    <div class="politica-icono" style="background:#e8f0fb;">
                        <i class="fa fa-tag" style="color:#3c8dbc;font-size:18px;"></i>
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div class="politica-nombre">{{ $desc->nombre }}</div>
                        <div class="politica-detalle">
                            @if($desc->dia_limite)
                                <span style="background:#f0f0f0;padding:1px 7px;border-radius:8px;font-size:10px;">
                                    <i class="fa fa-calendar"></i>
                                    Hasta el día {{ $desc->dia_limite }} de cada mes
                                </span>
                            @else
                                <span style="color:#bbb;font-size:11px;">Sin límite de fecha</span>
                            @endif
                            &nbsp;
                            @if(!$desc->activo)
                                <span style="background:#f5f5f5;color:#999;padding:1px 7px;border-radius:8px;font-size:10px;">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="valor-badge" style="color:#3c8dbc;">
                        @if($desc->tipo_valor === 'porcentaje')
                            {{ $desc->valor }}%
                        @else
                            ${{ number_format($desc->valor, 2) }}
                        @endif
                        <small>{{ $desc->tipo_valor === 'porcentaje' ? 'porcentaje' : 'monto fijo' }}</small>
                    </div>

                    <div style="flex-shrink:0;display:flex;gap:4px;margin-left:10px;">
                        <button type="button" class="btn btn-default btn-xs btn-flat btn-editar-desc"
                                data-id="{{ $desc->id }}" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs btn-flat btn-eliminar-desc"
                                data-id="{{ $desc->id }}"
                                data-nombre="{{ $desc->nombre }}"
                                title="Eliminar">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:30px 0;color:#ccc;" id="empty-descuentos">
                    <i class="fa fa-tag" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                    Sin descuentos configurados.<br>
                    <small>Usa el botón "Nuevo descuento" para agregar uno.</small>
                </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- ── POLÍTICA DE RECARGO ── --}}
    <div class="box" style="border-top:3px solid #e74c3c;">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-exclamation-triangle" style="color:#e74c3c;"></i>
                Política de recargo por mora
            </h3>
            <div class="box-tools pull-right">
                @if(!$plan->politicaRecargo)
                <button type="button" class="btn btn-danger btn-xs btn-flat"
                        id="btn-nuevo-recargo">
                    <i class="fa fa-plus"></i> Configurar recargo
                </button>
                @endif
            </div>
        </div>
        <div class="box-body">

            <div class="alert alert-warning" style="font-size:12px;margin-bottom:14px;">
                <i class="fa fa-info-circle"></i>
                <strong>Un solo recargo activo por plan.</strong>
                El recargo se aplica automáticamente cuando el pago se realiza después del día límite configurado.
            </div>

            {{-- Formulario de recargo (oculto) --}}
            <div id="form-recargo-wrap" style="{{ $plan->politicaRecargo ? 'display:none;' : ($plan->politicasDescuento->count() ? 'display:none;' : 'display:none;') }}">
                <div class="form-card recargo-form">
                    <div class="form-card-titulo">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span id="form-rec-titulo">Configurar recargo por mora</span>
                    </div>
                    <form id="form-recargo" autocomplete="off">
                        <input type="hidden" id="rec-id" value="">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:600;">
                                        Día límite de pago sin recargo <span class="text-red">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon"
                                              style="background:#fdecea;border-color:#f5c6c6;">
                                            <i class="fa fa-calendar" style="color:#e74c3c;"></i>
                                        </span>
                                        <input type="number" id="rec-dia" class="form-control"
                                               placeholder="Ej: 10"
                                               min="1" max="31">
                                        <span class="input-group-addon">de cada mes</span>
                                    </div>
                                    <span style="font-size:11px;color:#aaa;">
                                        Pagos después de este día generan recargo
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:600;">
                                        Tope máximo de recargo
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon">$</span>
                                        <input type="number" id="rec-tope" class="form-control"
                                               placeholder="Sin límite" min="0" step="0.01">
                                    </div>
                                    <span style="font-size:11px;color:#aaa;">
                                        Dejar vacío = sin tope máximo
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:8px;">
                                    Tipo de recargo <span class="text-red">*</span>
                                </label>
                                <label class="tipo-opt" id="opt-rec-pct">
                                    <input type="radio" name="rec_tipo" value="porcentaje">
                                    <i class="fa fa-percent" style="font-size:14px;"></i>
                                    Porcentaje sobre el monto
                                </label>
                                <label class="tipo-opt" id="opt-rec-monto">
                                    <input type="radio" name="rec_tipo" value="monto_fijo">
                                    <i class="fa fa-dollar" style="font-size:14px;"></i>
                                    Monto fijo
                                </label>
                            </div>
                        </div>

                        <div class="row" style="margin-top:14px;">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:600;" id="lbl-rec-valor">
                                        Valor del recargo <span class="text-red">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-addon" id="prefix-rec"
                                              style="background:#fdecea;border-color:#f5c6c6;color:#e74c3c;">%</span>
                                        <input type="number" id="rec-valor" class="form-control"
                                               placeholder="0.00" min="0.01" step="0.01">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7" style="padding-top:20px;">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="rec-activo" checked>
                                    Activo
                                </label>
                                <label class="checkbox-inline" style="margin-left:14px;">
                                    <input type="checkbox" id="rec-acumular">
                                    Acumular mensualmente
                                </label>
                                <div style="font-size:11px;color:#aaa;margin-top:4px;">
                                    Si está marcado, el recargo se multiplica por los meses de retraso
                                    (ej. 2 meses atrasado = 2× recargo).
                                </div>
                            </div>
                        </div>

                        {{-- Simulador de recargo --}}
                        <div class="simulador" id="simulador-rec" style="display:none;border-color:#f5c6c6;background:#fff8f8;">
                            <div class="simulador-titulo">
                                <i class="fa fa-calculator"></i> Simulación — monto de referencia
                                <input type="number" id="sim-rec-base" class="form-control input-sm"
                                       style="width:100px;display:inline-block;margin-left:8px;"
                                       value="1000" min="1" placeholder="Monto">
                                <span id="sim-rec-meses-wrap" style="display:none;">
                                    &nbsp;·&nbsp; Meses de retraso:
                                    <input type="number" id="sim-rec-meses" class="form-control input-sm"
                                           style="width:60px;display:inline-block;margin-left:4px;"
                                           value="1" min="1" max="24">
                                </span>
                            </div>
                            <div class="simulador-fila">
                                <span>Monto original</span>
                                <span id="sim-rec-original">$1,000.00</span>
                            </div>
                            <div class="simulador-fila" style="color:#e74c3c;">
                                <span>Recargo por mora <span id="sim-rec-meses-label" style="display:none;font-size:11px;"></span></span>
                                <span id="sim-rec-recargo">—</span>
                            </div>
                            <div class="simulador-fila" style="color:#c0392b;">
                                <span>Total a pagar con recargo</span>
                                <span id="sim-rec-total">—</span>
                            </div>
                        </div>

                        <div style="margin-top:14px;text-align:right;">
                            <button type="button" class="btn btn-default btn-sm"
                                    id="btn-cancelar-rec">Cancelar</button>
                            <button type="submit" class="btn btn-danger btn-sm"
                                    id="btn-guardar-rec">
                                <i class="fa fa-save"></i> Guardar recargo
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tarjeta de recargo activo --}}
            <div id="tarjeta-recargo">
                @if($plan->politicaRecargo)
                @php $rec = $plan->politicaRecargo; @endphp
                <div class="politica-card recargo {{ !$rec->activo ? 'inactiva' : '' }}"
                     id="rec-card-{{ $rec->id }}"
                     data-id="{{ $rec->id }}"
                     data-dia="{{ $rec->dia_limite_pago }}"
                     data-tipo="{{ $rec->tipo_recargo }}"
                     data-valor="{{ $rec->valor }}"
                     data-tope="{{ $rec->tope_maximo }}"
                     data-activo="{{ $rec->activo ? '1' : '0' }}"
                     data-acumular="{{ $rec->acumular_mensual ? '1' : '0' }}">

                    <div class="politica-icono" style="background:#fdecea;">
                        <i class="fa fa-exclamation-triangle" style="color:#e74c3c;font-size:17px;"></i>
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div class="politica-nombre" style="color:#c0392b;">
                            Recargo por mora
                        </div>
                        <div class="politica-detalle">
                            <span style="background:#fdecea;color:#c0392b;padding:1px 8px;border-radius:8px;font-size:11px;font-weight:600;">
                                <i class="fa fa-calendar"></i>
                                Aplica después del día {{ $rec->dia_limite_pago }}
                            </span>
                            @if($rec->acumular_mensual)
                                <span style="background:#fff3cd;color:#856404;padding:1px 8px;border-radius:8px;font-size:11px;margin-left:8px;">
                                    <i class="fa fa-stack-overflow"></i> Acumula mensual
                                </span>
                            @endif
                            @if($rec->tope_maximo)
                                <span style="color:#aaa;font-size:11px;margin-left:8px;">
                                    · Tope: ${{ number_format($rec->tope_maximo, 2) }}
                                </span>
                            @endif
                            @if(!$rec->activo)
                                <span style="background:#f5f5f5;color:#999;padding:1px 7px;border-radius:8px;font-size:10px;margin-left:4px;">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="valor-badge" style="color:#e74c3c;">
                        @if($rec->tipo_recargo === 'porcentaje')
                            +{{ $rec->valor }}%
                        @else
                            +${{ number_format($rec->valor, 2) }}
                        @endif
                        <small>{{ $rec->tipo_recargo === 'porcentaje' ? 'sobre el saldo' : 'monto fijo' }}</small>
                    </div>

                    <div style="flex-shrink:0;display:flex;gap:4px;margin-left:10px;">
                        <button type="button" class="btn btn-default btn-xs btn-flat"
                                id="btn-editar-rec" title="Editar recargo">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs btn-flat"
                                id="btn-eliminar-rec"
                                data-id="{{ $rec->id }}" title="Eliminar recargo">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                @else
                <div style="text-align:center;padding:30px 0;color:#ccc;" id="empty-recargo">
                    <i class="fa fa-exclamation-triangle" style="font-size:28px;display:block;margin-bottom:8px;color:#f5c6c6;"></i>
                    Sin política de recargo configurada.<br>
                    <small>Usa el botón "Configurar recargo" para agregar una.</small>
                </div>
                @endif
            </div>

        </div>
    </div>

</div>{{-- /col-md-7 --}}

{{-- ════════════════════════════════════════════════════
     COLUMNA LATERAL — RESUMEN Y AYUDA
════════════════════════════════════════════════════ --}}
<div class="col-md-5">

    {{-- Resumen del plan --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size:13px;">
                <i class="fa fa-info-circle" style="color:#3c8dbc;"></i>
                Resumen de políticas
            </h3>
        </div>
        <div class="box-body no-padding">
            <table class="table" style="font-size:13px;margin:0;">
                <tr>
                    <th style="color:#aaa;font-weight:400;padding:10px 16px;width:55%;">Plan</th>
                    <td style="padding:10px 16px;font-weight:600;">{{ $plan->nombre }}</td>
                </tr>
                <tr>
                    <th style="color:#aaa;font-weight:400;padding:10px 16px;">Periodicidad</th>
                    <td style="padding:10px 16px;">{{ ucfirst($plan->periodicidad) }}</td>
                </tr>
                <tr>
                    <th style="color:#aaa;font-weight:400;padding:10px 16px;">Vigencia</th>
                    <td style="padding:10px 16px;font-size:12px;">
                        {{ $plan->fecha_inicio->format('d/m/Y') }}<br>
                        {{ $plan->fecha_fin->format('d/m/Y') }}
                    </td>
                </tr>
                <tr>
                    <th style="color:#aaa;font-weight:400;padding:10px 16px;">Descuentos</th>
                    <td style="padding:10px 16px;">
                        <span class="badge bg-blue">{{ $plan->politicasDescuento->count() }}</span>
                        @if($plan->politicasDescuento->where('activo', true)->count() < $plan->politicasDescuento->count())
                            <small style="color:#aaa;">
                                ({{ $plan->politicasDescuento->where('activo', true)->count() }} activos)
                            </small>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th style="color:#aaa;font-weight:400;padding:10px 16px;">Recargo</th>
                    <td style="padding:10px 16px;">
                        @if($plan->politicaRecargo)
                            <span style="color:#e74c3c;font-weight:600;">
                                <i class="fa fa-check-circle"></i> Configurado
                            </span>
                        @else
                            <span style="color:#ccc;">Sin configurar</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Guía de uso --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size:13px;">
                <i class="fa fa-question-circle" style="color:#f39c12;"></i>
                ¿Cómo funcionan las políticas?
            </h3>
        </div>
        <div class="box-body" style="font-size:12px;line-height:1.8;color:#555;">

            <p style="margin:0 0 10px;">
                <strong style="color:#3c8dbc;"><i class="fa fa-tag"></i> Descuentos</strong><br>
                Se aplican al momento del cobro si se cumplen las condiciones.
                Puedes configurar varios descuentos activos simultáneamente.
            </p>

            <div style="background:#f0f7ff;border-left:3px solid #3c8dbc;padding:8px 12px;border-radius:0 4px 4px 0;margin-bottom:12px;">
                <strong>Día límite:</strong> si lo configuras, el descuento solo aplica
                si el pago se realiza <em>antes</em> de ese día del mes.<br>
                <strong>Sin día límite:</strong> el descuento aplica siempre que el cajero lo seleccione.
            </div>

            <p style="margin:0 0 10px;">
                <strong style="color:#e74c3c;"><i class="fa fa-exclamation-triangle"></i> Recargo por mora</strong><br>
                Se aplica cuando el pago se realiza <em>después</em> del día límite configurado.
                Solo puede haber <strong>una política de recargo activa</strong> por plan.
            </p>

            <div style="background:#fff8f8;border-left:3px solid #e74c3c;padding:8px 12px;border-radius:0 4px 4px 0;">
                <strong>Tope máximo:</strong> si lo configuras, el recargo no superará
                ese monto aunque el porcentaje resulte mayor.<br>
                <strong>Sin tope:</strong> el recargo se calcula sobre el total pendiente sin límite.
            </div>

        </div>
    </div>

    {{-- Simulador independiente --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size:13px;">
                <i class="fa fa-calculator" style="color:#9c27b0;"></i>
                Calculadora rápida
            </h3>
        </div>
        <div class="box-body" style="font-size:13px;">
            <div class="form-group">
                <label style="font-size:12px;">Monto de referencia</label>
                <div class="input-group">
                    <span class="input-group-addon">$</span>
                    <input type="number" id="calc-base" class="form-control"
                           value="1000" min="1" step="0.01">
                </div>
            </div>

            @if($plan->politicasDescuento->where('activo', true)->count())
            <div style="margin-bottom:14px;">
                <div style="font-size:11px;text-transform:uppercase;color:#aaa;margin-bottom:6px;letter-spacing:.05em;">
                    Con descuento
                </div>
                @foreach($plan->politicasDescuento->where('activo', true) as $d)
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #f5f5f5;font-size:12px;">
                    <span>{{ $d->nombre }}</span>
                    <span style="color:#3c8dbc;font-weight:600;" id="calc-desc-{{ $d->id }}"
                          data-tipo="{{ $d->tipo_valor }}" data-valor="{{ $d->valor }}">
                        —
                    </span>
                </div>
                @endforeach
            </div>
            @endif

            @if($plan->politicaRecargo)
            @php $rec = $plan->politicaRecargo; @endphp
            <div>
                <div style="font-size:11px;text-transform:uppercase;color:#aaa;margin-bottom:6px;letter-spacing:.05em;">
                    Con recargo por mora (día {{ $rec->dia_limite_pago }})
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12px;">
                    <span>Recargo</span>
                    <span style="color:#e74c3c;font-weight:600;" id="calc-rec"
                          data-tipo="{{ $rec->tipo_recargo }}" data-valor="{{ $rec->valor }}"
                          data-tope="{{ $rec->tope_maximo }}">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;font-weight:700;border-top:2px solid #eee;margin-top:4px;">
                    <span>Total con recargo</span>
                    <span id="calc-rec-total" style="color:#c0392b;">—</span>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>{{-- /col-md-5 --}}

</div>{{-- /row --}}
@endsection

@push('scripts')
<script>
$(function() {

    var PLAN_ID = {{ $plan->id }};

    // ══════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════
    function mostrarAlerta(msg, tipo) {
        $('#alerta-global-msg').text(msg);
        $('#alerta-global')
            .removeClass('alert-success alert-danger alert-warning')
            .addClass('alert-' + tipo)
            .show();
        $('html,body').animate({ scrollTop: 0 }, 200);
        if (tipo === 'success') setTimeout(function() { $('#alerta-global').hide(); }, 4000);
    }

    function fmt(n) {
        return '$' + parseFloat(n).toLocaleString('es-MX', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function calcularDescuento(tipo, valor, base) {
        if (tipo === 'porcentaje') return base * (valor / 100);
        return parseFloat(valor);
    }

    // ══════════════════════════════════════════════════
    // TIPOS — radio visual
    // ══════════════════════════════════════════════════
    function setupTipo(prefixId, opt1Id, opt2Id, onCambio) {
        $(document).on('change', 'input[name="' + prefixId + '"]', function() {
            var val = $(this).val();
            $('#opt-' + opt1Id).removeClass('activo-desc activo-rec');
            $('#opt-' + opt2Id).removeClass('activo-desc activo-rec');
            $(this).closest('label').addClass(prefixId === 'desc_tipo' ? 'activo-desc' : 'activo-rec');
            if (onCambio) onCambio(val);
        });
    }

    // Tipo descuento
    setupTipo('desc_tipo', 'desc-pct', 'desc-monto', function(val) {
        if (val === 'porcentaje') {
            $('#prefix-desc').text('%');
            $('#suffix-desc').hide();
        } else {
            $('#prefix-desc').text('$');
            $('#suffix-desc').show().text('pesos');
        }
        actualizarSimuladorDesc();
        mostrarSimuladores();
    });

    // Tipo recargo
    setupTipo('rec_tipo', 'rec-pct', 'rec-monto', function(val) {
        $('#prefix-rec').text(val === 'porcentaje' ? '%' : '$');
        actualizarSimuladorRec();
        mostrarSimuladores();
    });

    function mostrarSimuladores() {
        var tipoDesc = $('input[name="desc_tipo"]:checked').val();
        var tipoRec  = $('input[name="rec_tipo"]:checked').val();
        $('#simulador-desc').toggle(!!tipoDesc);
        $('#simulador-rec').toggle(!!tipoRec);
    }

    // ══════════════════════════════════════════════════
    // SIMULADORES
    // ══════════════════════════════════════════════════
    function actualizarSimuladorDesc() {
        var base  = parseFloat($('#sim-desc-base').val()) || 0;
        var tipo  = $('input[name="desc_tipo"]:checked').val();
        var valor = parseFloat($('#desc-valor').val()) || 0;
        if (!tipo || !valor) return;
        var desc  = calcularDescuento(tipo, valor, base);
        var final = Math.max(0, base - desc);
        $('#sim-desc-original').text(fmt(base));
        $('#sim-desc-descuento').text('-' + fmt(desc));
        $('#sim-desc-final').text(fmt(final));
    }

    function actualizarSimuladorRec() {
        var base     = parseFloat($('#sim-rec-base').val()) || 0;
        var tipo     = $('input[name="rec_tipo"]:checked').val();
        var valor    = parseFloat($('#rec-valor').val()) || 0;
        var tope     = parseFloat($('#rec-tope').val()) || 0;
        var acumular = $('#rec-acumular').is(':checked');
        var meses    = acumular ? (parseInt($('#sim-rec-meses').val()) || 1) : 1;
        if (!tipo || !valor) return;
        var recPorMes = calcularDescuento(tipo, valor, base);
        var rec       = Math.round(recPorMes * meses * 100) / 100;
        if (tope > 0) rec = Math.min(rec, tope);
        var total = base + rec;
        $('#sim-rec-original').text(fmt(base));
        $('#sim-rec-recargo').text('+' + fmt(rec));
        $('#sim-rec-total').text(fmt(total));

        // Etiqueta de meses en el simulador
        if (acumular && meses > 1) {
            $('#sim-rec-meses-label').show().text('(× ' + meses + ' meses)');
        } else {
            $('#sim-rec-meses-label').hide();
        }
    }

    // Mostrar / ocultar el selector de meses según acumular_mensual
    $('#rec-acumular').on('change', function() {
        $('#sim-rec-meses-wrap').toggle($(this).is(':checked'));
        actualizarSimuladorRec();
    });

    $(document).on('input', '#sim-desc-base, #desc-valor', actualizarSimuladorDesc);
    $(document).on('input', '#sim-rec-base, #rec-valor, #rec-tope, #sim-rec-meses', actualizarSimuladorRec);

    // ══════════════════════════════════════════════════
    // CALCULADORA RÁPIDA
    // ══════════════════════════════════════════════════
    function actualizarCalculadora() {
        var base = parseFloat($('#calc-base').val()) || 0;

        $('[id^="calc-desc-"]').each(function() {
            var tipo  = $(this).data('tipo');
            var valor = parseFloat($(this).data('valor')) || 0;
            var desc  = calcularDescuento(tipo, valor, base);
            $(this).text(fmt(base - desc) + ' (-' + fmt(desc) + ')');
        });

        var $rec = $('#calc-rec');
        if ($rec.length) {
            var tipo  = $rec.data('tipo');
            var valor = parseFloat($rec.data('valor')) || 0;
            var tope  = parseFloat($rec.data('tope')) || 0;
            var rec   = calcularDescuento(tipo, valor, base);
            if (tope > 0) rec = Math.min(rec, tope);
            $rec.text('+' + fmt(rec));
            $('#calc-rec-total').text(fmt(base + rec));
        }
    }

    $('#calc-base').on('input', actualizarCalculadora);
    actualizarCalculadora();

    // ══════════════════════════════════════════════════
    // FORMULARIO DESCUENTO — abrir/cerrar
    // ══════════════════════════════════════════════════
    $('#btn-nuevo-descuento').on('click', function() {
        limpiarFormDesc();
        $('#form-desc-titulo').text('Nuevo descuento');
        $('#desc-id').val('');
        $('#form-nuevo-descuento').slideDown(200);
        $('#desc-nombre').focus();
    });

    $('#btn-cancelar-desc').on('click', function() {
        $('#form-nuevo-descuento').slideUp(200);
        limpiarFormDesc();
    });

    function limpiarFormDesc() {
        $('#desc-id, #desc-nombre, #desc-dia, #desc-valor').val('');
        $('#desc-activo').prop('checked', true);
        $('input[name="desc_tipo"]').prop('checked', false);
        $('.tipo-opt').removeClass('activo-desc activo-rec');
        $('#simulador-desc').hide();
    }

    // ── Editar descuento ─────────────────────────────
    $(document).on('click', '.btn-editar-desc', function() {
        var $card = $(this).closest('.politica-card');
        $('#desc-id').val($card.data('id'));
        $('#desc-nombre').val($card.data('nombre'));
        $('#desc-dia').val($card.data('dia') || '');
        $('#desc-valor').val($card.data('valor'));
        $('#desc-activo').prop('checked', $card.data('activo') == '1');

        var tipo = $card.data('tipo');
        $('input[name="desc_tipo"][value="' + tipo + '"]').prop('checked', true).trigger('change');

        $('#form-desc-titulo').text('Editar descuento');
        $('#form-nuevo-descuento').slideDown(200);
        $('html,body').animate({ scrollTop: $('#form-nuevo-descuento').offset().top - 80 }, 200);
        $('#desc-nombre').focus();
    });

    // ── Submit descuento ─────────────────────────────
    $('#form-descuento').on('submit', function(e) {
        e.preventDefault();

        var id     = $('#desc-id').val();
        var nombre = $('#desc-nombre').val().trim();
        var tipo   = $('input[name="desc_tipo"]:checked').val();
        var valor  = $('#desc-valor').val();

        if (!nombre) { mostrarAlerta('El nombre del descuento es obligatorio.', 'danger'); return; }
        if (!tipo)   { mostrarAlerta('Selecciona el tipo de descuento.', 'danger'); return; }
        if (!valor || parseFloat(valor) <= 0) { mostrarAlerta('El valor debe ser mayor a cero.', 'danger'); return; }
        if (tipo === 'porcentaje' && parseFloat(valor) > 100) { mostrarAlerta('El porcentaje no puede superar 100.', 'danger'); return; }

        var url    = id
            ? '/planes/' + PLAN_ID + '/politicas/descuento/' + id
            : '/planes/' + PLAN_ID + '/politicas/descuento';
        var method = id ? 'PUT' : 'POST';

        var datos = {
            nombre:     nombre,
            tipo_valor: tipo,
            valor:      valor,
            dia_limite: $('#desc-dia').val() || null,
            activo:     $('#desc-activo').is(':checked') ? 1 : 0,
        };
        if (method === 'PUT') datos._method = 'PUT';
        var $btn = $('#btn-guardar-desc');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: url, method: 'POST', data: JSON.stringify(datos),
            success: function(res) {
                mostrarAlerta(res.message || 'Guardado.', 'success');
                setTimeout(function() { location.reload(); }, 900);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar descuento');
                var msg = xhr.responseJSON?.message || 'Error al guardar.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' · ');
                mostrarAlerta(msg, 'danger');
            }
        });
    });

    // ── Eliminar descuento ───────────────────────────
    $(document).on('click', '.btn-eliminar-desc', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        if (!confirm('¿Eliminar el descuento "' + nombre + '"?')) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/planes/' + PLAN_ID + '/politicas/descuento/' + id,
            method: 'POST',
            data: JSON.stringify({ _method: 'DELETE' }),
            success: function(res) {
                $btn.closest('.politica-card').fadeOut(250, function() {
                    $(this).remove();
                    if (!$('.politica-card').length) $('#empty-descuentos').show();
                });
                mostrarAlerta(res.message, 'success');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                mostrarAlerta(xhr.responseJSON?.message || 'Error al eliminar.', 'danger');
            }
        });
    });

    // ══════════════════════════════════════════════════
    // FORMULARIO RECARGO — abrir/cerrar
    // ══════════════════════════════════════════════════
    $('#btn-nuevo-recargo').on('click', function() {
        limpiarFormRec();
        $('#form-rec-titulo').text('Configurar recargo por mora');
        $('#rec-id').val('');
        $('#form-recargo-wrap').slideDown(200);
        $('#rec-dia').focus();
    });

    $('#btn-cancelar-rec').on('click', function() {
        $('#form-recargo-wrap').slideUp(200);
        limpiarFormRec();
    });

    function limpiarFormRec() {
        $('#rec-id, #rec-dia, #rec-valor, #rec-tope').val('');
        $('#rec-activo').prop('checked', true);
        $('input[name="rec_tipo"]').prop('checked', false);
        $('.tipo-opt').removeClass('activo-desc activo-rec');
        $('#simulador-rec').hide();
    }

    // ── Editar recargo ───────────────────────────────
    $('#btn-editar-rec').on('click', function() {
        var $card = $('.politica-card.recargo');
        $('#rec-id').val($card.data('id'));
        $('#rec-dia').val($card.data('dia'));
        $('#rec-valor').val($card.data('valor'));
        $('#rec-tope').val($card.data('tope') || '');
        $('#rec-activo').prop('checked', $card.data('activo') == '1');

        var acumular = $card.data('acumular') == '1';
        $('#rec-acumular').prop('checked', acumular);
        $('#sim-rec-meses-wrap').toggle(acumular);

        var tipo = $card.data('tipo');
        $('input[name="rec_tipo"][value="' + tipo + '"]').prop('checked', true).trigger('change');

        $('#form-rec-titulo').text('Editar recargo por mora');
        $('#form-recargo-wrap').slideDown(200);
        $('html,body').animate({ scrollTop: $('#form-recargo-wrap').offset().top - 80 }, 200);
    });

    // ── Submit recargo ───────────────────────────────
    $('#form-recargo').on('submit', function(e) {
        e.preventDefault();

        var id    = $('#rec-id').val();
        var dia   = $('#rec-dia').val();
        var tipo  = $('input[name="rec_tipo"]:checked').val();
        var valor = $('#rec-valor').val();

        if (!dia)   { mostrarAlerta('El día límite es obligatorio.', 'danger'); return; }
        if (!tipo)  { mostrarAlerta('Selecciona el tipo de recargo.', 'danger'); return; }
        if (!valor || parseFloat(valor) <= 0) { mostrarAlerta('El valor debe ser mayor a cero.', 'danger'); return; }
        if (tipo === 'porcentaje' && parseFloat(valor) > 100) { mostrarAlerta('El porcentaje no puede superar 100.', 'danger'); return; }

        var url    = id
            ? '/planes/' + PLAN_ID + '/politicas/recargo/' + id
            : '/planes/' + PLAN_ID + '/politicas/recargo';

        var datos = {
            dia_limite_pago:  dia,
            tipo_recargo:     tipo,
            valor:            valor,
            tope_maximo:      $('#rec-tope').val() || null,
            activo:           $('#rec-activo').is(':checked') ? 1 : 0,
            acumular_mensual: $('#rec-acumular').is(':checked') ? 1 : 0,
        };
        if (id) datos._method = 'PUT';
        var $btn = $('#btn-guardar-rec');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: url, method: 'POST', data: JSON.stringify(datos),
            success: function(res) {
                mostrarAlerta(res.message || 'Guardado.', 'success');
                setTimeout(function() { location.reload(); }, 900);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar recargo');
                var msg = xhr.responseJSON?.message || 'Error al guardar.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' · ');
                mostrarAlerta(msg, 'danger');
            }
        });
    });

    // ── Eliminar recargo ─────────────────────────────
    $(document).on('click', '#btn-eliminar-rec', function() {
        var id = $(this).data('id');
        if (!confirm('¿Eliminar la política de recargo?')) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/planes/' + PLAN_ID + '/politicas/recargo/' + id,
            method: 'POST',
            data: JSON.stringify({ _method: 'DELETE' }),
            success: function(res) {
                $('.politica-card.recargo').fadeOut(250, function() {
                    $(this).remove();
                    $('#btn-nuevo-recargo').show();
                    $('#empty-recargo').show();
                });
                mostrarAlerta(res.message, 'success');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                mostrarAlerta(xhr.responseJSON?.message || 'Error al eliminar.', 'danger');
            }
        });
    });

});
</script>
@endpush