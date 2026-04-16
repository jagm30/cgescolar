{{--
    Partial: _razon_social.blade.php
    Variables requeridas: $familia (con contactos.razonesSociales cargados)

    Catálogos SAT incluidos inline para no depender de tablas externas.
--}}

@php
$regimenesFiscales = [
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

$usosCfdi = [
    'D10' => 'D10 – Pagos por servicios educativos (colegiaturas)',
    'G03' => 'G03 – Gastos en general',
    'D01' => 'D01 – Honorarios médicos, dentales y gastos hospitalarios',
    'D08' => 'D08 – Gastos de transportación escolar obligatoria',
    'I04' => 'I04 – Equipo de cómputo y accesorios',
    'S01' => 'S01 – Sin efectos fiscales',
    'CP01'=> 'CP01 – Pagos',
];
@endphp

<div class="box box-default" id="box-facturacion">
    <div class="box-header with-border"
         style="background:linear-gradient(135deg,#37474f,#546e7a);border-radius:3px 3px 0 0;">
        <h3 class="box-title" style="color:#fff;font-size:15px;">
            <i class="fa fa-file-text-o"></i>
            Datos de facturación
            <span style="background:rgba(255,255,255,.2);color:#fff;border-radius:10px;
                          padding:1px 8px;font-size:12px;margin-left:6px;" id="badge-total-rfc">
                {{ $familia->contactos->sum(fn($c) => $c->razonesSociales->count()) }}
            </span>
        </h3>
        <div class="box-tools pull-right">
            @can('administrador', 'caja')
            <button type="button" id="btn-nueva-rs"
                    class="btn btn-xs btn-flat"
                    style="color:#fff;border:1px solid rgba(255,255,255,.5);background:rgba(255,255,255,.15);">
                <i class="fa fa-plus"></i> Agregar RFC
            </button>
            @endcan
        </div>
    </div>

    {{-- Alerta AJAX --}}
    <div id="rs-alerta" style="display:none;margin:10px 16px 0;" class="alert alert-dismissible">
        <button type="button" class="close" onclick="$('#rs-alerta').hide()">&times;</button>
        <span id="rs-alerta-msg"></span>
    </div>

    {{-- ── Formulario nueva razón social ── --}}
    <div id="form-nueva-rs" style="display:none;border-bottom:2px solid #eceff1;">
        <div style="padding:16px;background:#f5f7f8;">
            <h4 style="margin:0 0 14px;font-size:13px;color:#37474f;font-weight:700;">
                <i class="fa fa-plus-circle"></i> Nueva razón social
            </h4>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:12px;">Contacto <span class="text-red">*</span></label>
                        <select id="nrs-contacto" class="form-control input-sm">
                            <option value="">-- Seleccionar --</option>
                            @foreach($familia->contactos->sortBy('pivot.orden') as $ctc)
                                @if($ctc->razonesSociales->count() < 3)
                                <option value="{{ $ctc->id }}">
                                    {{ trim("{$ctc->nombre} {$ctc->ap_paterno}") }}
                                    ({{ $ctc->razonesSociales->count() }}/3 RFC)
                                </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label style="font-size:12px;">RFC <span class="text-red">*</span></label>
                        <input type="text" id="nrs-rfc" class="form-control input-sm"
                               maxlength="13" placeholder="Ej: AAAA000000AA0"
                               style="text-transform:uppercase">
                        <div style="font-size:10px;color:#aaa;margin-top:2px;">
                            Persona física: 13 chars · Moral: 12
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label style="font-size:12px;">Razón social <span class="text-red">*</span></label>
                        <input type="text" id="nrs-razon-social" class="form-control input-sm"
                               maxlength="300" placeholder="Nombre completo como aparece en el SAT">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label style="font-size:12px;">Régimen fiscal <span class="text-red">*</span></label>
                        <select id="nrs-regimen" class="form-control input-sm">
                            <option value="">-- Seleccionar --</option>
                            @foreach($regimenesFiscales as $codigo => $nombre)
                            <option value="{{ $codigo }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label style="font-size:12px;">CP fiscal <span class="text-red">*</span></label>
                        <input type="text" id="nrs-cp" class="form-control input-sm"
                               maxlength="5" placeholder="00000"
                               style="letter-spacing:.1em;">
                        <div style="font-size:10px;color:#aaa;margin-top:2px;">5 dígitos</div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label style="font-size:12px;">Uso CFDI predeterminado <span class="text-red">*</span></label>
                        <select id="nrs-uso-cfdi" class="form-control input-sm">
                            <option value="">-- Seleccionar --</option>
                            @foreach($usosCfdi as $codigo => $nombre)
                            <option value="{{ $codigo }}" {{ $codigo === 'D10' ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="checkbox-inline" style="font-size:12px;">
                        <input type="checkbox" id="nrs-principal">
                        Marcar como RFC principal del contacto
                    </label>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-default btn-sm" id="btn-cancelar-rs">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-guardar-rs">
                        <i class="fa fa-save"></i> Guardar RFC
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Lista de razones sociales agrupadas por contacto ── --}}
    <div class="box-body" style="padding:16px;" id="contenedor-rs">

        @php
            $contactosConRs = $familia->contactos->filter(fn($c) => $c->razonesSociales->count() > 0);
        @endphp

        @forelse($contactosConRs as $ctc)
        <div style="margin-bottom:18px;">
            {{-- Cabecera del contacto --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;
                        padding-bottom:8px;border-bottom:2px solid #eceff1;">
                <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;
                            background:#546e7a;
                            display:flex;align-items:center;justify-content:center;">
                    <i class="fa fa-user" style="color:#fff;font-size:13px;"></i>
                </div>
                <div>
                    <strong style="font-size:13px;color:#1a1a1a;">
                        {{ trim("{$ctc->nombre} {$ctc->ap_paterno} {$ctc->ap_materno}") }}
                    </strong>
                    <small style="color:#aaa;margin-left:6px;">
                        {{ $ctc->razonesSociales->count() }}/3 RFC registrado(s)
                    </small>
                </div>
            </div>

            {{-- RFCs del contacto --}}
            @foreach($ctc->razonesSociales as $rs)
            <div class="rs-card" id="rs-card-{{ $rs->id }}"
                 style="border:1px solid #e0e0e0;border-left:4px solid {{ $rs->es_principal ? '#546e7a' : '#ccc' }};
                        border-radius:6px;padding:12px 16px;margin-bottom:8px;
                        background:{{ $rs->es_principal ? '#f5f7f8' : '#fff' }};">

                {{-- Vista normal --}}
                <div class="rs-vista" id="rs-vista-{{ $rs->id }}">
                    <div style="display:flex;align-items:flex-start;gap:12px;">
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span style="font-family:monospace;font-size:15px;font-weight:700;
                                             color:#1a1a1a;letter-spacing:.05em;">
                                    {{ $rs->rfc }}
                                </span>
                                @if($rs->es_principal)
                                <span style="background:#546e7a;color:#fff;font-size:10px;font-weight:700;
                                             padding:2px 9px;border-radius:10px;letter-spacing:.03em;">
                                    PRINCIPAL
                                </span>
                                @endif
                            </div>
                            <div style="font-size:13px;color:#333;margin-top:4px;">
                                {{ $rs->razon_social }}
                            </div>
                            <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
                                <span style="background:#f0f0f0;color:#555;font-size:11px;
                                             padding:2px 8px;border-radius:8px;">
                                    <i class="fa fa-building-o"></i>
                                    {{ $rs->regimen_fiscal }}
                                    — {{ $regimenesFiscales[$rs->regimen_fiscal] ?? $rs->regimen_fiscal }}
                                </span>
                                <span style="background:#f0f0f0;color:#555;font-size:11px;
                                             padding:2px 8px;border-radius:8px;">
                                    <i class="fa fa-map-marker"></i>
                                    CP {{ $rs->domicilio_fiscal }}
                                </span>
                                <span style="background:#e3f2fd;color:#1565c0;font-size:11px;
                                             padding:2px 8px;border-radius:8px;">
                                    <i class="fa fa-file-text-o"></i>
                                    {{ $rs->uso_cfdi_default }}
                                    — {{ $usosCfdi[$rs->uso_cfdi_default] ?? $rs->uso_cfdi_default }}
                                </span>
                            </div>
                        </div>

                        {{-- Botones --}}
                        @can('administrador', 'caja')
                        <div style="flex-shrink:0;display:flex;gap:4px;flex-direction:column;align-items:flex-end;">
                            @if(!$rs->es_principal)
                            <button type="button"
                                    class="btn btn-default btn-xs btn-flat btn-rs-principal"
                                    data-id="{{ $rs->id }}" title="Marcar como principal"
                                    style="font-size:10px;">
                                <i class="fa fa-star-o"></i> Principal
                            </button>
                            @endif
                            <div style="display:flex;gap:4px;">
                                <button type="button"
                                        class="btn btn-default btn-xs btn-flat btn-rs-editar"
                                        data-id="{{ $rs->id }}" title="Editar">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-danger btn-xs btn-flat btn-rs-eliminar"
                                        data-id="{{ $rs->id }}"
                                        data-rfc="{{ $rs->rfc }}" title="Desactivar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endcan
                    </div>
                </div>

                {{-- Panel de edición inline --}}
                <div class="rs-form-editar" id="rs-editar-{{ $rs->id }}" style="display:none;">
                    <div style="font-size:12px;color:#888;text-transform:uppercase;
                                letter-spacing:.05em;margin-bottom:10px;">
                        <i class="fa fa-pencil"></i> Editando RFC <strong>{{ $rs->rfc }}</strong>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label style="font-size:12px;">Razón social <span class="text-red">*</span></label>
                                <input type="text" class="form-control input-sm ers-razon-social"
                                       value="{{ $rs->razon_social }}" maxlength="300">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label style="font-size:12px;">Régimen fiscal <span class="text-red">*</span></label>
                                <select class="form-control input-sm ers-regimen">
                                    @foreach($regimenesFiscales as $codigo => $nombre)
                                    <option value="{{ $codigo }}"
                                        {{ $rs->regimen_fiscal === $codigo ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label style="font-size:12px;">CP fiscal <span class="text-red">*</span></label>
                                <input type="text" class="form-control input-sm ers-cp"
                                       value="{{ $rs->domicilio_fiscal }}" maxlength="5"
                                       style="letter-spacing:.1em;">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label style="font-size:12px;">Uso CFDI <span class="text-red">*</span></label>
                                <select class="form-control input-sm ers-uso-cfdi">
                                    @foreach($usosCfdi as $codigo => $nombre)
                                    <option value="{{ $codigo }}"
                                        {{ $rs->uso_cfdi_default === $codigo ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <label class="checkbox-inline" style="font-size:12px;margin:0;">
                            <input type="checkbox" class="ers-principal"
                                {{ $rs->es_principal ? 'checked' : '' }}>
                            RFC principal
                        </label>
                        <div>
                            <button type="button" class="btn btn-default btn-xs btn-rs-cancelar-editar"
                                    data-id="{{ $rs->id }}">Cancelar</button>
                            <button type="button" class="btn btn-success btn-xs btn-rs-guardar-editar"
                                    data-id="{{ $rs->id }}">
                                <i class="fa fa-save"></i> Guardar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach

        </div>
        @empty
        <div id="rs-vacio" style="padding:30px;text-align:center;color:#ccc;">
            <i class="fa fa-file-text-o" style="font-size:32px;display:block;margin-bottom:8px;"></i>
            <strong style="color:#bbb;">Sin razones sociales registradas</strong>
            <p style="font-size:13px;margin-top:4px;">
                Agrega los datos fiscales de los contactos para poder emitir facturas.
            </p>
        </div>
        @endforelse

    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    function mostrarRsAlerta(msg, tipo) {
        $('#rs-alerta-msg').text(msg);
        $('#rs-alerta')
            .removeClass('alert-success alert-danger alert-warning')
            .addClass('alert-' + tipo)
            .show();
        if (tipo === 'success') setTimeout(function () { $('#rs-alerta').hide(); }, 4000);
    }

    // ── Toggle formulario nuevo ─────────────────────────
    $('#btn-nueva-rs').on('click', function () {
        var abierto = $('#form-nueva-rs').is(':visible');
        $('#form-nueva-rs').toggle(!abierto);
        if (!abierto) {
            $('#nrs-contacto').focus();
            $('#rs-vacio').hide();
        }
    });

    $('#btn-cancelar-rs').on('click', function () {
        $('#form-nueva-rs').hide();
        limpiarFormNuevo();
    });

    function limpiarFormNuevo() {
        $('#nrs-contacto').val('');
        $('#nrs-rfc,#nrs-razon-social,#nrs-cp').val('');
        $('#nrs-regimen,#nrs-uso-cfdi').val('');
        $('#nrs-uso-cfdi option[value="D10"]').prop('selected', true);
        $('#nrs-principal').prop('checked', false);
    }

    // ── Guardar nueva razón social ──────────────────────
    $('#btn-guardar-rs').on('click', function () {
        var btn = $(this);
        var rfc = $('#nrs-rfc').val().trim().toUpperCase();

        var datos = {
            contacto_id:      $('#nrs-contacto').val(),
            rfc:              rfc,
            razon_social:     $('#nrs-razon-social').val().trim(),
            regimen_fiscal:   $('#nrs-regimen').val(),
            domicilio_fiscal: $('#nrs-cp').val().trim(),
            uso_cfdi_default: $('#nrs-uso-cfdi').val(),
            es_principal:     $('#nrs-principal').is(':checked'),
        };

        if (!datos.contacto_id) { mostrarRsAlerta('Selecciona el contacto.', 'danger'); return; }
        if (!datos.rfc)         { mostrarRsAlerta('El RFC es obligatorio.', 'danger'); return; }
        if (!datos.razon_social){ mostrarRsAlerta('La razón social es obligatoria.', 'danger'); return; }
        if (!datos.regimen_fiscal){ mostrarRsAlerta('Selecciona el régimen fiscal.', 'danger'); return; }
        if (datos.domicilio_fiscal.length !== 5 || !/^\d{5}$/.test(datos.domicilio_fiscal)) {
            mostrarRsAlerta('El código postal debe tener exactamente 5 dígitos.', 'danger'); return;
        }
        if (!datos.uso_cfdi_default){ mostrarRsAlerta('Selecciona el uso de CFDI.', 'danger'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url:         '{{ route("familias.razon-social.store") }}',
            method:      'POST',
            contentType: 'application/json',
            data:        JSON.stringify(datos),
            success: function (res) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar RFC');
                limpiarFormNuevo();
                $('#form-nueva-rs').hide();
                mostrarRsAlerta(res.message || 'RFC registrado.', 'success');
                setTimeout(function () { location.reload(); }, 1200);
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar RFC');
                var msg = xhr.responseJSON?.message || 'Error al guardar.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                mostrarRsAlerta(msg, 'danger');
            }
        });
    });

    // ── Editar inline ───────────────────────────────────
    $(document).on('click', '.btn-rs-editar', function () {
        var id = $(this).data('id');
        $('.rs-form-editar').not('#rs-editar-' + id).hide();
        $('.rs-vista').show();
        $('#rs-vista-' + id).hide();
        $('#rs-editar-' + id).show();
    });

    $(document).on('click', '.btn-rs-cancelar-editar', function () {
        var id = $(this).data('id');
        $('#rs-editar-' + id).hide();
        $('#rs-vista-' + id).show();
    });

    $(document).on('click', '.btn-rs-guardar-editar', function () {
        var id    = $(this).data('id');
        var panel = $('#rs-editar-' + id);
        var btn   = $(this);
        var orig  = btn.html();

        var cp = panel.find('.ers-cp').val().trim();
        if (cp.length !== 5 || !/^\d{5}$/.test(cp)) {
            mostrarRsAlerta('El código postal debe tener exactamente 5 dígitos.', 'danger'); return;
        }

        var datos = {
            razon_social:     panel.find('.ers-razon-social').val().trim(),
            regimen_fiscal:   panel.find('.ers-regimen').val(),
            domicilio_fiscal: cp,
            uso_cfdi_default: panel.find('.ers-uso-cfdi').val(),
            es_principal:     panel.find('.ers-principal').is(':checked'),
        };

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url:         '{{ url("familias/razon-social") }}/' + id,
            method:      'PUT',
            contentType: 'application/json',
            data:        JSON.stringify(datos),
            success: function (res) {
                btn.prop('disabled', false).html(orig);
                mostrarRsAlerta(res.message || 'RFC actualizado.', 'success');
                setTimeout(function () { location.reload(); }, 1200);
            },
            error: function (xhr) {
                btn.prop('disabled', false).html(orig);
                mostrarRsAlerta(xhr.responseJSON?.message || 'Error al guardar.', 'danger');
            }
        });
    });

    // ── Marcar como principal ───────────────────────────
    $(document).on('click', '.btn-rs-principal', function () {
        var id  = $(this).data('id');
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url:    '{{ url("familias/razon-social") }}/' + id + '/principal',
            method: 'POST',
            success: function (res) {
                mostrarRsAlerta(res.message || 'RFC marcado como principal.', 'success');
                setTimeout(function () { location.reload(); }, 1000);
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-star-o"></i> Principal');
                mostrarRsAlerta(xhr.responseJSON?.message || 'Error.', 'danger');
            }
        });
    });

    // ── Desactivar (eliminar lógico) ────────────────────
    $(document).on('click', '.btn-rs-eliminar', function () {
        var id  = $(this).data('id');
        var rfc = $(this).data('rfc');

        if (!confirm('¿Desactivar el RFC ' + rfc + '?\nPodrás reactivarlo si es necesario.')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url:    '{{ url("familias/razon-social") }}/' + id,
            method: 'DELETE',
            success: function (res) {
                mostrarRsAlerta(res.message || 'RFC desactivado.', 'success');
                $('#rs-card-' + id).fadeOut(300, function () { $(this).remove(); });
            },
            error: function (xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                mostrarRsAlerta(xhr.responseJSON?.message || 'Error al desactivar.', 'danger');
            }
        });
    });

    // RFC en mayúsculas al escribir
    $(document).on('input', '#nrs-rfc', function () {
        this.value = this.value.toUpperCase();
    });

    // Solo dígitos en CP
    $(document).on('input', '#nrs-cp, .ers-cp', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 5);
    });

}());
</script>
@endpush
