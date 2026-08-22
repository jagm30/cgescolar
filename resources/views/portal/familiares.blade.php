@extends('layouts.master')

@section('page_title', 'Familiares')
@section('page_subtitle', 'Contactos de mi familia')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Familiares</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Hero ── */
        .fm-hero {
            background: linear-gradient(135deg, #134e4a 0%, #0f766e 100%);
            border-radius: 14px;
            padding: 20px 18px;
            margin-bottom: 16px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .fm-hero-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,.18);
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .fm-hero-sub { font-size: 13px; color: rgba(255,255,255,.78); margin: 0; line-height: 1.4; }

        /* ── Info banner ── */
        .fm-info {
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 10px;
            font-size: 13px; color: #134e4a; line-height: 1.5;
        }
        .fm-info i { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

        /* ── Botón agregar ── */
        .fm-btn-agregar {
            width: 100%; padding: 16px;
            border-radius: 12px; border: 2px dashed #99f6e4;
            background: #f0fdfa; color: #0f766e;
            font-size: 16px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-bottom: 14px;
            transition: background .15s, border-color .15s;
        }
        .fm-btn-agregar:hover:not(:disabled) { background: #ccfbf1; border-color: #0f766e; }
        .fm-btn-agregar:disabled { opacity: .5; cursor: not-allowed; border-style: solid; }
        .fm-btn-agregar i { font-size: 20px; }

        /* ── Panel formulario ── */
        .fm-form-panel {
            background: #f0fdfa; border: 1px solid #99f6e4;
            border-radius: 12px; padding: 18px 16px 14px; margin-bottom: 14px;
        }
        .fm-form-titulo {
            font-size: 15px; font-weight: 800; color: #134e4a;
            margin: 0 0 16px; display: flex; align-items: center; gap: 8px;
        }
        .fm-form-group { margin-bottom: 14px; }
        .fm-form-label {
            display: block; font-size: 13px; font-weight: 700;
            color: #4a5568; margin-bottom: 6px;
        }
        .fm-requerido { color: #b91c1c; margin-left: 2px; }
        .fm-control {
            width: 100%; height: 46px; padding: 0 14px;
            border: 1.5px solid #d0dae5; border-radius: 10px;
            font-size: 15px; color: #1a2634; background: #fff;
            box-sizing: border-box; transition: border-color .15s; appearance: none;
        }
        .fm-control:focus { outline: none; border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15,118,110,.12); }
        .fm-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .fm-form-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .fm-btn {
            padding: 13px; border-radius: 10px; border: none;
            font-size: 14px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            min-height: 44px; transition: opacity .15s;
        }
        .fm-btn:disabled { opacity: .6; cursor: not-allowed; }
        .fm-btn-cancel { background: #f0f3f7; color: #4a5568; }
        .fm-btn-cancel:hover:not(:disabled) { background: #e4eaf0; }
        .fm-btn-save { background: #0f766e; color: #fff; }
        .fm-btn-save:hover:not(:disabled) { background: #134e4a; }
        .fm-alerta {
            display: none; margin-top: 12px; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; font-weight: 600;
        }
        .fm-alerta-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* ── Tarjeta de contacto ── */
        .fm-card {
            background: #fff; border: 1px solid #e4eaf0;
            border-radius: 14px; margin-bottom: 12px;
            overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .fm-card-header {
            display: flex; align-items: center; gap: 14px;
            padding: 16px 16px 14px;
        }
        .fm-avatar {
            width: 50px; height: 50px; border-radius: 50%;
            background: #ccfbf1; color: #0f766e;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 800; flex-shrink: 0; text-transform: uppercase;
        }
        .fm-card-nombre {
            font-size: 17px; font-weight: 800; color: #1a2634;
            margin: 0 0 6px; line-height: 1.2;
        }
        .fm-card-badges { display: flex; flex-wrap: wrap; gap: 5px; }
        .fm-badge {
            font-size: 11px; font-weight: 700; padding: 3px 9px;
            border-radius: 20px; display: inline-flex; align-items: center; gap: 4px;
        }
        .fm-badge-yo         { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .fm-badge-portal     { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .fm-badge-parentesco { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }

        /* ── Datos de contacto ── */
        .fm-card-datos {
            border-top: 1px solid #f0f3f7;
            display: grid; grid-template-columns: 1fr 1fr; gap: 0;
        }
        .fm-dato { padding: 10px 16px; border-right: 1px solid #f0f3f7; }
        .fm-dato:nth-child(even) { border-right: none; }
        .fm-dato-label {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em;
            color: #9aa5b4; margin-bottom: 3px;
        }
        .fm-dato-valor { font-size: 12px; font-weight: 600; color: #1a2634; line-height: 1.3; word-break: break-word; }
        .fm-dato-vacio { color: #c0c9d5 !important; font-style: italic; }

        /* ── Alumnos vinculados ── */
        .fm-alumnos { border-top: 1px solid #f0f3f7; padding: 10px 16px 12px; }
        .fm-alumnos-label {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .05em;
            color: #9aa5b4; margin-bottom: 8px;
        }
        .fm-alumno-chip {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 600;
            padding: 4px 10px; border-radius: 20px;
            background: #f0fdfa; color: #0f766e;
            border: 1px solid #99f6e4; margin: 2px;
        }

        /* ── Vacío ── */
        .fm-empty {
            background: #fff; border: 1px solid #e4eaf0;
            border-radius: 14px; text-align: center;
            padding: 48px 20px; color: #9aa5b4;
        }
        .fm-empty i { font-size: 48px; color: #d1d9e0; display: block; margin-bottom: 14px; }
        .fm-empty p { font-size: 15px; margin: 0 0 6px; }
        .fm-empty small { font-size: 13px; }

        /* ── Botón regresar ── */
        .fm-nav-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 11px 18px; border-radius: 8px; font-size: 14px; font-weight: 600;
            text-decoration: none !important; border: 1px solid #dde3ea;
            background: #fff; color: #4a5568; transition: background .15s;
            margin-top: 6px; margin-bottom: 20px;
        }
        .fm-nav-btn:hover { background: #f0f3f7; color: #1a2634; }

        /* ── Responsive ── */
        @media (max-width: 420px) {
            .fm-grid-2   { grid-template-columns: 1fr; }
            .fm-form-btns { grid-template-columns: 1fr; }
        }
        @media (max-width: 360px) {
            .fm-card-datos { grid-template-columns: 1fr; }
            .fm-dato { border-right: none; border-bottom: 1px solid #f0f3f7; }
            .fm-dato:last-child { border-bottom: none; }
        }
    </style>
@endpush

@php
    $totalContactos = $contactos->count();
@endphp

@section('content')

    {{-- ── Hero ── --}}
    <div class="fm-hero">
        <div class="fm-hero-icon"><i class="fa fa-users"></i></div>
        <div>
            <div class="fm-hero-sub">
                Consulta y agrega los contactos familiares asociados a la cuenta.
            </div>
        </div>
    </div>

    {{-- ── Info ── --}}
    <div class="fm-info">
        <i class="fa fa-info-circle"></i>
        <span>
            Puedes registrar hasta <strong>3 familiares</strong> por familia.
            Para modificar datos de un familiar existente, comunícate con la escuela.
        </span>
    </div>

    {{-- ── Botón agregar ── --}}
    <button id="btn-mostrar-form-nuevo" class="fm-btn-agregar"
        @if($totalContactos >= 3) disabled title="Tu familia ya tiene 3 contactos registrados (máximo permitido)" @endif>
        <i class="fa fa-user-plus"></i>
        @if($totalContactos >= 3) Ya tienes 3 familiares registrados @else Agregar familiar @endif
    </button>

    {{-- ── Formulario nuevo ── --}}
    <div id="form-nuevo-fm" style="display:none; margin-bottom:14px;">
        <div class="fm-form-panel">
            <div class="fm-form-titulo">
                <i class="fa fa-user-plus"></i> Nuevo familiar
            </div>

            <div class="fm-grid-2">
                <div class="fm-form-group">
                    <label class="fm-form-label">
                        Nombre <span class="fm-requerido">*</span>
                    </label>
                    <input type="text" id="nuevo-nombre" class="fm-control" maxlength="100" placeholder="Ej. María">
                </div>
                <div class="fm-form-group">
                    <label class="fm-form-label">Apellido paterno</label>
                    <input type="text" id="nuevo-ap-paterno" class="fm-control" maxlength="100">
                </div>
            </div>

            <div class="fm-grid-2">
                <div class="fm-form-group">
                    <label class="fm-form-label">Apellido materno</label>
                    <input type="text" id="nuevo-ap-materno" class="fm-control" maxlength="100">
                </div>
                <div class="fm-form-group">
                    <label class="fm-form-label">
                        Parentesco <span class="fm-requerido">*</span>
                    </label>
                    <select id="nuevo-parentesco" class="fm-control">
                        <option value="">-- Seleccionar --</option>
                        <option value="padre">Padre</option>
                        <option value="madre">Madre</option>
                        <option value="abuelo">Abuelo/a</option>
                        <option value="tio">Tío/a</option>
                        <option value="hermano">Hermano/a</option>
                        <option value="tutor">Tutor/a</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
            </div>

            <div class="fm-grid-2">
                <div class="fm-form-group">
                    <label class="fm-form-label">Teléfono celular</label>
                    <input type="text" id="nuevo-telefono" class="fm-control" maxlength="20" inputmode="tel">
                </div>
                <div class="fm-form-group">
                    <label class="fm-form-label">Correo electrónico</label>
                    <input type="email" id="nuevo-email" class="fm-control" maxlength="150">
                </div>
            </div>

            <div class="fm-form-btns">
                <button type="button" class="fm-btn fm-btn-cancel" id="btn-cancelar-nuevo">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="button" class="fm-btn fm-btn-save" id="btn-guardar-nuevo">
                    <i class="fa fa-save"></i> Guardar
                </button>
            </div>
            <div class="fm-alerta" id="nuevo-alerta"></div>
        </div>
    </div>

    {{-- ── Lista de contactos ── --}}
    <div id="fm-lista">
        @forelse ($contactos as $cf)
            @php
                $esMio     = $cf->id === $miContactoId;
                $iniciales = strtoupper(mb_substr($cf->nombre ?? '', 0, 1) . mb_substr($cf->ap_paterno ?? '', 0, 1));
                $parentescos = $cf->alumnoContactos->pluck('parentesco')->filter()->unique()->values();
            @endphp
            <div class="fm-card" data-id="{{ $cf->id }}">

                <div class="fm-card-header">
                    <div class="fm-avatar">{{ $iniciales ?: '?' }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="fm-card-nombre">{{ $cf->nombre_completo }}</div>
                        <div class="fm-card-badges">
                            @foreach($parentescos as $p)
                                <span class="fm-badge fm-badge-parentesco">
                                    <i class="fa fa-heart-o"></i> {{ ucfirst($p) }}
                                </span>
                            @endforeach
                            @if($esMio)
                                <span class="fm-badge fm-badge-yo">
                                    <i class="fa fa-user"></i> Yo
                                </span>
                            @endif
                            @if($cf->tiene_acceso_portal)
                                <span class="fm-badge fm-badge-portal">
                                    <i class="fa fa-globe"></i> Acceso portal
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="fm-card-datos">
                    <div class="fm-dato">
                        <div class="fm-dato-label">Teléfono</div>
                        <div class="fm-dato-valor {{ $cf->telefono_celular ? '' : 'fm-dato-vacio' }}">
                            {{ $cf->telefono_celular ?: 'Sin teléfono' }}
                        </div>
                    </div>
                    <div class="fm-dato">
                        <div class="fm-dato-label">Correo</div>
                        <div class="fm-dato-valor {{ $cf->email ? '' : 'fm-dato-vacio' }}">
                            {{ $cf->email ?: 'Sin correo' }}
                        </div>
                    </div>
                </div>

                @if($cf->alumnoContactos->count())
                    <div class="fm-alumnos">
                        <div class="fm-alumnos-label">Alumnos vinculados</div>
                        @foreach($cf->alumnoContactos as $ac)
                            <span class="fm-alumno-chip">
                                <i class="fa fa-graduation-cap"></i>
                                {{ $ac->alumno?->nombre_completo ?? '—' }}
                            </span>
                        @endforeach
                    </div>
                @endif

            </div>
        @empty
            <div class="fm-empty" id="fm-vacio">
                <i class="fa fa-users"></i>
                <p>No hay contactos registrados.</p>
                <small>Usa el botón de arriba para agregar familiares.</small>
            </div>
        @endforelse
    </div>

    {{-- ── Regresar ── --}}
    <a href="{{ route('portal.dashboard') }}" class="fm-nav-btn">
        <i class="fa fa-arrow-left"></i> Regresar al inicio
    </a>

@endsection

@push('scripts')
<script>
$(function () {

    var miContactoId = {{ $miContactoId ?? 'null' }};

    // ── Mostrar / cancelar formulario ────────────────────────
    $('#btn-mostrar-form-nuevo').on('click', function () {
        $('#form-nuevo-fm').slideDown(150);
        $(this).hide();
        $('#nuevo-nombre').focus();
    });

    $('#btn-cancelar-nuevo').on('click', cerrarFormNuevo);

    function cerrarFormNuevo() {
        $('#form-nuevo-fm').slideUp(150);
        limpiarFormNuevo();
        actualizarBtnAgregar();
    }

    function limpiarFormNuevo() {
        $('#nuevo-nombre, #nuevo-ap-paterno, #nuevo-ap-materno, #nuevo-telefono, #nuevo-email').val('');
        $('#nuevo-parentesco').val('');
        ocultarAlerta('#nuevo-alerta');
        $('#btn-guardar-nuevo').prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
    }

    // ── Guardar nuevo familiar ────────────────────────────────
    $('#btn-guardar-nuevo').on('click', function () {
        var btn   = $(this);
        var datos = {
            nombre:           $('#nuevo-nombre').val().trim(),
            ap_paterno:       $('#nuevo-ap-paterno').val().trim(),
            ap_materno:       $('#nuevo-ap-materno').val().trim(),
            parentesco:       $('#nuevo-parentesco').val(),
            telefono_celular: $('#nuevo-telefono').val().trim(),
            email:            $('#nuevo-email').val().trim(),
            _token:           '{{ csrf_token() }}',
        };

        if (!datos.nombre)      { mostrarAlerta('#nuevo-alerta', 'El nombre es obligatorio.'); return; }
        if (!datos.parentesco)  { mostrarAlerta('#nuevo-alerta', 'Selecciona el parentesco.'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.post('{{ route("portal.familiares.store") }}', datos)
            .done(function (resp) {
                cerrarFormNuevo();
                agregarCardAlListado(resp.contacto);
                actualizarBtnAgregar();
                toast(resp.mensaje, 'success');
            })
            .fail(function (xhr) {
                var msg = xhr.responseJSON?.mensaje
                    || xhr.responseJSON?.message
                    || primerError(xhr.responseJSON?.errors)
                    || 'Error al guardar.';
                mostrarAlerta('#nuevo-alerta', msg);
                btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar');
            });
    });

    // ── Helpers ───────────────────────────────────────────────
    function actualizarBtnAgregar() {
        var total = $('#fm-lista .fm-card').length;
        var $btn  = $('#btn-mostrar-form-nuevo');
        if (total >= 3) {
            $btn.prop('disabled', true)
                .html('<i class="fa fa-check-circle"></i> Ya tienes 3 familiares registrados')
                .show();
        } else {
            $btn.prop('disabled', false)
                .html('<i class="fa fa-user-plus"></i> Agregar familiar')
                .show();
        }
    }

    function agregarCardAlListado(cf) {
        $('#fm-vacio').remove();

        var ini = ((cf.nombre || '').charAt(0) + (cf.ap_paterno || '').charAt(0)).toUpperCase();
        var nombre = [cf.nombre, cf.ap_paterno, cf.ap_materno].filter(Boolean).join(' ');

        var html =
            '<div class="fm-card" data-id="' + cf.id + '">'
          + '<div class="fm-card-header">'
          + '<div class="fm-avatar">' + esc(ini || '?') + '</div>'
          + '<div style="flex:1;min-width:0;">'
          + '<div class="fm-card-nombre">' + esc(nombre) + '</div>'
          + '<div class="fm-card-badges"></div>'
          + '</div></div>'
          + '<div class="fm-card-datos">'
          + '<div class="fm-dato"><div class="fm-dato-label">Teléfono</div>'
          + '<div class="fm-dato-valor' + (cf.telefono_celular ? '' : ' fm-dato-vacio') + '">'
          + esc(cf.telefono_celular || 'Sin teléfono') + '</div></div>'
          + '<div class="fm-dato"><div class="fm-dato-label">Correo</div>'
          + '<div class="fm-dato-valor' + (cf.email ? '' : ' fm-dato-vacio') + '">'
          + esc(cf.email || 'Sin correo') + '</div></div>'
          + '</div>'
          + '</div>';

        $('#fm-lista').append(html);
    }

    function esc(str) {
        return $('<div>').text(String(str)).html();
    }

    function mostrarAlerta(selector, msg) {
        var $el = typeof selector === 'string' ? $(selector) : selector;
        $el.addClass('fm-alerta-danger').text(msg).show();
    }

    function ocultarAlerta(selector) {
        var $el = typeof selector === 'string' ? $(selector) : selector;
        $el.removeClass('fm-alerta-danger').text('').hide();
    }

    function primerError(errors) {
        if (!errors) return null;
        var vals = Object.values(errors);
        return vals.length ? vals[0][0] : null;
    }

    function toast(msg, tipo) {
        var bg = tipo === 'success' ? '#065f46' : '#b91c1c';
        var $t = $('<div>').css({
            position: 'fixed', bottom: '24px', left: '50%', transform: 'translateX(-50%)',
            background: bg, color: '#fff', padding: '12px 20px', borderRadius: '10px',
            zIndex: 9999, fontSize: '14px', fontWeight: '600',
            boxShadow: '0 4px 16px rgba(0,0,0,.2)', maxWidth: '90vw', textAlign: 'center',
        }).text(msg).appendTo('body');
        setTimeout(function () { $t.fadeOut(400, function () { $t.remove(); }); }, 3000);
    }

});
</script>
@endpush
