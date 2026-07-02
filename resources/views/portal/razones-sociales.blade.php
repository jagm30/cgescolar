@extends('layouts.master')

@section('page_title', 'Razones sociales')
@section('page_subtitle', 'Datos fiscales de la familia')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Razones sociales</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        .rs-form-panel { background:#f8fafc; border:1px solid #e4eaf0; border-radius:8px; padding:18px 18px 10px; }
        .rs-form-panel .form-group { margin-bottom:12px; }
        .rs-form-panel label { font-size:12px; font-weight:600; color:#5a6a7a; margin-bottom:4px; }
        .rs-form-panel .form-control { border-radius:5px; border-color:#d0dae5; font-size:13px; }
        .rs-alerta { display:none; margin-top:8px; }
        .rs-contacto-badge {
            font-size:11px; font-weight:600; color:#3c8dbc;
            background:#e8f0fb; border:1px solid #c5d9f5;
            border-radius:999px; padding:2px 8px; display:inline-flex;
            align-items:center; gap:4px;
        }
        .rs-contacto-badge.es-mio { background:#e8f8f0; color:#00875a; border-color:#b3e8d0; }
    </style>
@endpush

@php
$regimenOpciones = [
    '601' => '601 – General de Ley Personas Morales',
    '603' => '603 – Personas Morales con Fines no Lucrativos',
    '605' => '605 – Sueldos y Salarios e Ingresos Asimilados',
    '606' => '606 – Arrendamiento',
    '608' => '608 – Demás ingresos',
    '612' => '612 – Personas Físicas con Actividades Empresariales y Profesionales',
    '616' => '616 – Sin obligaciones fiscales',
    '621' => '621 – Incorporación Fiscal',
    '626' => '626 – Régimen Simplificado de Confianza (RESICO)',
];
$cfdiOpciones = [
    'D10'  => 'D10 – Pagos por servicios educativos',
    'G03'  => 'G03 – Gastos en general',
    'D01'  => 'D01 – Honorarios médicos y hospitalarios',
    'D08'  => 'D08 – Transportación escolar obligatoria',
    'I04'  => 'I04 – Equipo de cómputo y accesorios',
    'S01'  => 'S01 – Sin efectos fiscales',
    'CP01' => 'CP01 – Pagos',
];
// Cuántas razones sociales tiene el contacto logueado (para el límite del botón Agregar)
$misCantidad = $razonesSociales->where('contacto_id', $miContactoId)->count();
@endphp

@section('content')

<div class="portal-card" id="card-listado">
    <div class="portal-card-header">
        <h4 class="portal-card-title"><i class="fa fa-building-o"></i> Datos fiscales de la familia</h4>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="portal-pill portal-pill-ok" id="badge-total">{{ $razonesSociales->count() }} activa(s)</span>
            <button class="btn btn-primary btn-xs" id="btn-mostrar-form-nuevo"
                @if($misCantidad >= 3) disabled title="Ya tienes 3 razones sociales (máximo permitido)" @endif>
                <i class="fa fa-plus"></i> Agregar mi RFC
            </button>
        </div>
    </div>

    {{-- ── Formulario nuevo ── --}}
    <div id="form-nuevo-rs" style="display:none;padding:16px;border-bottom:1px solid #eef2f6;">
        <div class="rs-form-panel">
            <h5 style="margin:0 0 14px;color:#172b3a;font-weight:700;font-size:14px;">
                <i class="fa fa-plus-circle" style="color:#3c8dbc;margin-right:6px;"></i>Nueva razón social
            </h5>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>RFC <span style="color:#e74c3c;">*</span></label>
                        <input type="text" id="nuevo-rfc" class="form-control" maxlength="13"
                               placeholder="XAXX010101000" style="text-transform:uppercase;">
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="form-group">
                        <label>Razón social <span style="color:#e74c3c;">*</span></label>
                        <input type="text" id="nuevo-razon-social" class="form-control" maxlength="300">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <div class="form-group">
                        <label>Régimen fiscal <span style="color:#e74c3c;">*</span></label>
                        <select id="nuevo-regimen" class="form-control">
                            <option value="">-- Seleccionar --</option>
                            @foreach($regimenOpciones as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>CP fiscal <span style="color:#e74c3c;">*</span></label>
                        <input type="text" id="nuevo-cp" class="form-control" maxlength="5" placeholder="00000">
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-group">
                        <label>Uso CFDI predeterminado <span style="color:#e74c3c;">*</span></label>
                        <select id="nuevo-uso-cfdi" class="form-control">
                            <option value="">-- Seleccionar --</option>
                            @foreach($cfdiOpciones as $val => $lbl)
                                <option value="{{ $val }}" @if($val === 'D10') selected @endif>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-top:4px;">
                <label style="font-size:12px;font-weight:600;color:#5a6a7a;margin:0;cursor:pointer;">
                    <input type="checkbox" id="nuevo-principal" style="margin-right:5px;">
                    Marcar como principal
                </label>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-default btn-sm" id="btn-cancelar-nuevo">Cancelar</button>
                    <button class="btn btn-primary btn-sm" id="btn-guardar-nuevo">
                        <i class="fa fa-save"></i> Guardar RFC
                    </button>
                </div>
            </div>
            <div class="alert rs-alerta" id="nuevo-alerta"></div>
        </div>
    </div>

    {{-- ── Listado ── --}}
    <div id="rs-lista">
        @forelse ($razonesSociales as $rs)
            @php $esMia = $rs->contacto_id === $miContactoId; @endphp
            <div class="rs-item" data-id="{{ $rs->id }}" data-mio="{{ $esMia ? '1' : '0' }}" style="border-bottom:1px solid #f0f3f7;">

                {{-- Vista normal --}}
                <div class="rs-vista" style="padding:16px;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <div>
                            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:4px;">
                                <h4 class="portal-student-name rs-razon-social-texto" style="margin:0;">
                                    {{ $rs->razon_social }}
                                </h4>
                                @if($rs->es_principal)
                                    <span class="portal-pill portal-pill-ok badge-principal" style="font-size:10px;">
                                        <i class="fa fa-star"></i> Principal
                                    </span>
                                @endif
                                <span class="badge-principal-placeholder"></span>
                            </div>
                            {{-- Badge de quién es la razón social --}}
                            <span class="rs-contacto-badge {{ $esMia ? 'es-mio' : '' }}">
                                <i class="fa fa-user"></i>
                                {{ trim($rs->contacto->nombre . ' ' . $rs->contacto->ap_paterno) }}
                                @if($esMia) <em style="font-weight:400;">(yo)</em> @endif
                            </span>
                        </div>
                        @if($esMia)
                            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:flex-start;">
                                @if(!$rs->es_principal)
                                    <button class="btn btn-default btn-xs btn-principal-rs" title="Marcar como principal">
                                        <i class="fa fa-star-o"></i> Principal
                                    </button>
                                @endif
                                <button class="btn btn-info btn-xs btn-editar-rs">
                                    <i class="fa fa-pencil"></i> Editar
                                </button>
                                <button class="btn btn-danger btn-xs btn-eliminar-rs">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="row" style="margin-top:12px;">
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>RFC</strong><br>
                                <span class="rs-rfc-texto">{{ $rs->rfc }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>Régimen</strong><br>
                                <span class="rs-regimen-texto">{{ $regimenOpciones[$rs->regimen_fiscal] ?? $rs->regimen_fiscal }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>Uso CFDI</strong><br>
                                <span class="rs-uso-texto">{{ $cfdiOpciones[$rs->uso_cfdi_default] ?? $rs->uso_cfdi_default }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>CP fiscal</strong><br>
                                <span class="rs-cp-texto">{{ $rs->domicilio_fiscal }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Formulario de edición — solo para los propios --}}
                @if($esMia)
                <div class="rs-form-editar" style="display:none;padding:0 16px 16px;">
                    <div class="rs-form-panel">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label>Razón social <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" class="form-control editar-razon-social"
                                           value="{{ $rs->razon_social }}" maxlength="300">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label>Régimen fiscal <span style="color:#e74c3c;">*</span></label>
                                    <select class="form-control editar-regimen">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($regimenOpciones as $val => $lbl)
                                            <option value="{{ $val }}" @if($rs->regimen_fiscal === $val) selected @endif>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>CP fiscal <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" class="form-control editar-cp"
                                           value="{{ $rs->domicilio_fiscal }}" maxlength="5">
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label>Uso CFDI <span style="color:#e74c3c;">*</span></label>
                                    <select class="form-control editar-uso-cfdi">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($cfdiOpciones as $val => $lbl)
                                            <option value="{{ $val }}" @if($rs->uso_cfdi_default === $val) selected @endif>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                            <label style="font-size:12px;font-weight:600;color:#5a6a7a;margin:0;cursor:pointer;">
                                <input type="checkbox" class="editar-principal"
                                       @if($rs->es_principal) checked @endif style="margin-right:5px;">
                                Marcar como principal
                            </label>
                            <div style="display:flex;gap:8px;">
                                <button class="btn btn-default btn-sm btn-cancelar-editar">Cancelar</button>
                                <button class="btn btn-primary btn-sm btn-guardar-editar">
                                    <i class="fa fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                        <div class="alert rs-alerta editar-alerta"></div>
                    </div>
                </div>
                @endif

            </div>
        @empty
            <div style="padding:16px;" id="rs-vacio">
                <div class="portal-empty">
                    <i class="fa fa-building-o" style="font-size:34px;margin-bottom:10px;"></i>
                    <div>No hay razones sociales activas registradas en la familia.</div>
                    <div style="margin-top:8px;font-size:12px;">
                        Usa el botón <strong>Agregar mi RFC</strong> para registrar tus datos fiscales.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {

    var miContactoId  = {{ $miContactoId ?? 'null' }};
    var regimenOpciones = @json($regimenOpciones);
    var cfdiOpciones    = @json($cfdiOpciones);

    // ── Mostrar / cancelar formulario nuevo ───────────────
    $('#btn-mostrar-form-nuevo').on('click', function () {
        $('#form-nuevo-rs').slideDown(150);
        $(this).prop('disabled', true);
        $('#nuevo-rfc').focus();
    });

    $('#btn-cancelar-nuevo').on('click', cerrarFormNuevo);

    function cerrarFormNuevo() {
        $('#form-nuevo-rs').slideUp(150);
        var misCant = $('#rs-lista .rs-item[data-mio="1"]').length;
        $('#btn-mostrar-form-nuevo').prop('disabled', misCant >= 3);
        limpiarFormNuevo();
    }

    function limpiarFormNuevo() {
        $('#nuevo-rfc, #nuevo-razon-social, #nuevo-cp').val('');
        $('#nuevo-regimen').val('');
        $('#nuevo-uso-cfdi').val('D10');
        $('#nuevo-principal').prop('checked', false);
        $('#nuevo-alerta').hide();
        $('#btn-guardar-nuevo').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar RFC');
    }

    // ── Guardar nuevo ─────────────────────────────────────
    $('#btn-guardar-nuevo').on('click', function () {
        var btn = $(this);
        var datos = {
            rfc:              $('#nuevo-rfc').val().trim().toUpperCase(),
            razon_social:     $('#nuevo-razon-social').val().trim(),
            regimen_fiscal:   $('#nuevo-regimen').val(),
            domicilio_fiscal: $('#nuevo-cp').val().trim(),
            uso_cfdi_default: $('#nuevo-uso-cfdi').val(),
            es_principal:     $('#nuevo-principal').is(':checked') ? 1 : 0,
            _token:           '{{ csrf_token() }}',
        };

        if (!datos.rfc)              { alerta('#nuevo-alerta', 'El RFC es obligatorio.', 'danger'); return; }
        if (!datos.razon_social)     { alerta('#nuevo-alerta', 'La razón social es obligatoria.', 'danger'); return; }
        if (!datos.regimen_fiscal)   { alerta('#nuevo-alerta', 'Selecciona el régimen fiscal.', 'danger'); return; }
        if (!/^\d{5}$/.test(datos.domicilio_fiscal)) { alerta('#nuevo-alerta', 'El código postal debe tener 5 dígitos.', 'danger'); return; }
        if (!datos.uso_cfdi_default) { alerta('#nuevo-alerta', 'Selecciona el uso de CFDI.', 'danger'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.post('{{ route("portal.razones-sociales.store") }}', datos)
            .done(function (resp) {
                cerrarFormNuevo();
                agregarItemAlListado(resp.razon_social);
                toast(resp.mensaje, 'success');
            })
            .fail(function (xhr) {
                var msg = xhr.responseJSON?.mensaje
                    || xhr.responseJSON?.message
                    || primerError(xhr.responseJSON?.errors)
                    || 'Error al guardar.';
                alerta('#nuevo-alerta', msg, 'danger');
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar RFC');
            });
    });

    // ── Abrir / cerrar edición ────────────────────────────
    $(document).on('click', '.btn-editar-rs', function () {
        var $item = $(this).closest('.rs-item');
        $item.find('.rs-vista').hide();
        $item.find('.rs-form-editar').show();
    });

    $(document).on('click', '.btn-cancelar-editar', function () {
        var $item = $(this).closest('.rs-item');
        $item.find('.rs-form-editar').hide();
        $item.find('.editar-alerta').hide();
        $item.find('.rs-vista').show();
        $item.find('.btn-guardar-editar').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
    });

    // ── Guardar edición ───────────────────────────────────
    $(document).on('click', '.btn-guardar-editar', function () {
        var btn     = $(this);
        var $item   = btn.closest('.rs-item');
        var rsId    = $item.data('id');
        var $alerta = $item.find('.editar-alerta');

        var datos = {
            razon_social:     $item.find('.editar-razon-social').val().trim(),
            regimen_fiscal:   $item.find('.editar-regimen').val(),
            domicilio_fiscal: $item.find('.editar-cp').val().trim(),
            uso_cfdi_default: $item.find('.editar-uso-cfdi').val(),
            es_principal:     $item.find('.editar-principal').is(':checked') ? 1 : 0,
            _token:           '{{ csrf_token() }}',
            _method:          'PUT',
        };

        if (!datos.razon_social)     { alerta($alerta, 'La razón social es obligatoria.', 'danger'); return; }
        if (!datos.regimen_fiscal)   { alerta($alerta, 'Selecciona el régimen fiscal.', 'danger'); return; }
        if (!/^\d{5}$/.test(datos.domicilio_fiscal)) { alerta($alerta, 'El código postal debe tener 5 dígitos.', 'danger'); return; }
        if (!datos.uso_cfdi_default) { alerta($alerta, 'Selecciona el uso de CFDI.', 'danger'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.post('/portal/razones-sociales/' + rsId, datos)
            .done(function (resp) {
                refrescarVistaItem($item, resp.razon_social);
                toast(resp.mensaje, 'success');
            })
            .fail(function (xhr) {
                var msg = xhr.responseJSON?.mensaje || xhr.responseJSON?.message || 'Error al actualizar.';
                alerta($alerta, msg, 'danger');
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            });
    });

    // ── Eliminar ──────────────────────────────────────────
    $(document).on('click', '.btn-eliminar-rs', function () {
        var btn   = $(this);
        var $item = btn.closest('.rs-item');
        var rsId  = $item.data('id');
        var rfc   = $item.find('.rs-rfc-texto').text().trim();

        if (!confirm('¿Eliminar el RFC ' + rfc + '?\nEsta acción no se puede deshacer.')) return;
        btn.prop('disabled', true);

        $.post('/portal/razones-sociales/' + rsId, { _token: '{{ csrf_token() }}', _method: 'DELETE' })
            .done(function (resp) {
                $item.fadeOut(300, function () {
                    $(this).remove();
                    actualizarBadgeTotal(-1);
                    actualizarBtnAgregar();
                    if ($('#rs-lista .rs-item').length === 0) {
                        $('#rs-lista').html(htmlVacio());
                    }
                });
                toast(resp.mensaje, 'success');
            })
            .fail(function () {
                btn.prop('disabled', false);
                toast('No se pudo eliminar.', 'danger');
            });
    });

    // ── Marcar como principal ─────────────────────────────
    $(document).on('click', '.btn-principal-rs', function () {
        var btn   = $(this);
        var $item = btn.closest('.rs-item');
        var rsId  = $item.data('id');

        btn.prop('disabled', true);

        $.post('/portal/razones-sociales/' + rsId + '/principal', { _token: '{{ csrf_token() }}' })
            .done(function (resp) {
                // Quitar estrellas de todos los items de mis propios
                $('.rs-item[data-mio="1"] .badge-principal').remove();
                $('.rs-item[data-mio="1"] .badge-principal-placeholder').html('');
                $('.btn-principal-rs').prop('disabled', false).show();
                // Marcar este item
                $item.find('.badge-principal-placeholder').html(
                    '<span class="portal-pill portal-pill-ok badge-principal" style="font-size:10px;"><i class="fa fa-star"></i> Principal</span>'
                );
                btn.hide();
                toast(resp.mensaje, 'success');
            })
            .fail(function () {
                btn.prop('disabled', false);
                toast('No se pudo actualizar.', 'danger');
            });
    });

    // ── Helpers ───────────────────────────────────────────
    function alerta(selector, msg, tipo) {
        var $el = typeof selector === 'string' ? $(selector) : selector;
        $el.removeClass('alert-success alert-danger alert-warning')
           .addClass('alert alert-' + tipo).text(msg).show();
    }

    function toast(msg, tipo) {
        var bg = tipo === 'success' ? '#00875a' : '#b91c1c';
        var $t = $('<div>').css({
            position:'fixed', bottom:'24px', right:'24px', background:bg,
            color:'#fff', padding:'10px 18px', borderRadius:'7px',
            zIndex:9999, fontSize:'13px', boxShadow:'0 4px 14px rgba(0,0,0,.18)', maxWidth:'320px',
        }).text(msg).appendTo('body');
        setTimeout(function () { $t.fadeOut(400, function () { $t.remove(); }); }, 3000);
    }

    function primerError(errors) {
        if (!errors) return null;
        var vals = Object.values(errors);
        return vals.length ? vals[0][0] : null;
    }

    function actualizarBadgeTotal(delta) {
        var $b = $('#badge-total');
        var n  = parseInt($b.text()) + delta;
        $b.text(n + ' activa(s)');
    }

    function actualizarBtnAgregar() {
        var misCant = $('#rs-lista .rs-item[data-mio="1"]').length;
        $('#btn-mostrar-form-nuevo').prop('disabled', misCant >= 3);
    }

    function htmlVacio() {
        return '<div style="padding:16px;" id="rs-vacio">'
             + '<div class="portal-empty">'
             + '<i class="fa fa-building-o" style="font-size:34px;margin-bottom:10px;"></i>'
             + '<div>No hay razones sociales activas registradas en la familia.</div>'
             + '</div></div>';
    }

    function optsHtml(opciones, valorActual) {
        return Object.entries(opciones).map(function (kv) {
            return '<option value="' + kv[0] + '"' + (kv[0] === valorActual ? ' selected' : '') + '>' + kv[1] + '</option>';
        }).join('');
    }

    function agregarItemAlListado(rs) {
        $('#rs-vacio').remove();

        var principal = rs.es_principal
            ? '<span class="portal-pill portal-pill-ok badge-principal" style="font-size:10px;"><i class="fa fa-star"></i> Principal</span>'
            : '';
        var btnPrincipal = rs.es_principal
            ? ''
            : '<button class="btn btn-default btn-xs btn-principal-rs" title="Marcar como principal"><i class="fa fa-star-o"></i> Principal</button>';

        var nombreContacto = rs.contacto
            ? (rs.contacto.nombre + ' ' + (rs.contacto.ap_paterno || '')).trim()
            : 'Yo';

        var html = '<div class="rs-item" data-id="' + rs.id + '" data-mio="1" style="border-bottom:1px solid #f0f3f7;">'
            + '<div class="rs-vista" style="padding:16px;">'
            + '<div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;">'
            + '<div>'
            + '<div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:4px;">'
            + '<h4 class="portal-student-name rs-razon-social-texto" style="margin:0;">' + rs.razon_social + '</h4>'
            + principal
            + '<span class="badge-principal-placeholder"></span>'
            + '</div>'
            + '<span class="rs-contacto-badge es-mio"><i class="fa fa-user"></i> ' + nombreContacto + ' <em style="font-weight:400;">(yo)</em></span>'
            + '</div>'
            + '<div style="display:flex;gap:6px;flex-wrap:wrap;align-items:flex-start;">'
            + btnPrincipal
            + '<button class="btn btn-info btn-xs btn-editar-rs"><i class="fa fa-pencil"></i> Editar</button>'
            + '<button class="btn btn-danger btn-xs btn-eliminar-rs"><i class="fa fa-trash"></i></button>'
            + '</div></div>'
            + '<div class="row" style="margin-top:12px;">'
            + '<div class="col-sm-6 col-md-3"><div class="portal-meta"><strong>RFC</strong><br><span class="rs-rfc-texto">' + rs.rfc + '</span></div></div>'
            + '<div class="col-sm-6 col-md-3"><div class="portal-meta"><strong>Régimen</strong><br><span class="rs-regimen-texto">' + (regimenOpciones[rs.regimen_fiscal] || rs.regimen_fiscal) + '</span></div></div>'
            + '<div class="col-sm-6 col-md-3"><div class="portal-meta"><strong>Uso CFDI</strong><br><span class="rs-uso-texto">' + (cfdiOpciones[rs.uso_cfdi_default] || rs.uso_cfdi_default) + '</span></div></div>'
            + '<div class="col-sm-6 col-md-3"><div class="portal-meta"><strong>CP fiscal</strong><br><span class="rs-cp-texto">' + rs.domicilio_fiscal + '</span></div></div>'
            + '</div></div>'
            // Formulario de edición
            + '<div class="rs-form-editar" style="display:none;padding:0 16px 16px;">'
            + '<div class="rs-form-panel">'
            + '<div class="row"><div class="col-sm-12"><div class="form-group"><label>Razón social *</label>'
            + '<input type="text" class="form-control editar-razon-social" value="' + rs.razon_social + '" maxlength="300"></div></div></div>'
            + '<div class="row">'
            + '<div class="col-sm-5"><div class="form-group"><label>Régimen fiscal *</label>'
            + '<select class="form-control editar-regimen"><option value="">-- Seleccionar --</option>' + optsHtml(regimenOpciones, rs.regimen_fiscal) + '</select></div></div>'
            + '<div class="col-sm-2"><div class="form-group"><label>CP fiscal *</label>'
            + '<input type="text" class="form-control editar-cp" value="' + rs.domicilio_fiscal + '" maxlength="5"></div></div>'
            + '<div class="col-sm-5"><div class="form-group"><label>Uso CFDI *</label>'
            + '<select class="form-control editar-uso-cfdi"><option value="">-- Seleccionar --</option>' + optsHtml(cfdiOpciones, rs.uso_cfdi_default) + '</select></div></div>'
            + '</div>'
            + '<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">'
            + '<label style="font-size:12px;font-weight:600;color:#5a6a7a;margin:0;cursor:pointer;">'
            + '<input type="checkbox" class="editar-principal"' + (rs.es_principal ? ' checked' : '') + ' style="margin-right:5px;"> Marcar como principal</label>'
            + '<div style="display:flex;gap:8px;">'
            + '<button class="btn btn-default btn-sm btn-cancelar-editar">Cancelar</button>'
            + '<button class="btn btn-primary btn-sm btn-guardar-editar"><i class="fa fa-save"></i> Guardar</button>'
            + '</div></div>'
            + '<div class="alert rs-alerta editar-alerta"></div>'
            + '</div></div></div>';

        $('#rs-lista').append(html);
        actualizarBadgeTotal(1);
        actualizarBtnAgregar();
    }

    function refrescarVistaItem($item, rs) {
        $item.find('.rs-razon-social-texto').text(rs.razon_social);
        $item.find('.rs-regimen-texto').text(regimenOpciones[rs.regimen_fiscal] || rs.regimen_fiscal);
        $item.find('.rs-uso-texto').text(cfdiOpciones[rs.uso_cfdi_default] || rs.uso_cfdi_default);
        $item.find('.rs-cp-texto').text(rs.domicilio_fiscal);
        $item.find('.editar-razon-social').val(rs.razon_social);
        $item.find('.editar-regimen').val(rs.regimen_fiscal);
        $item.find('.editar-cp').val(rs.domicilio_fiscal);
        $item.find('.editar-uso-cfdi').val(rs.uso_cfdi_default);
        $item.find('.editar-principal').prop('checked', rs.es_principal == 1);
        $item.find('.rs-form-editar').hide();
        $item.find('.editar-alerta').hide();
        $item.find('.rs-vista').show();
        $item.find('.btn-guardar-editar').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
    }

});
</script>
@endpush
