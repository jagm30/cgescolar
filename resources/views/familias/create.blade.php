@extends('layouts.master')

@section('page_title', 'Nueva familia')
@section('page_subtitle', 'Registro de familia')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li class="active">Nueva familia</li>
@endsection

@push('styles')
    <style>
        /* ══ PANELES ══ */
        .fam-panel {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .fam-panel-header {
            background: #f4f6f8;
            border-bottom: 2px solid #e0e6ed;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fam-panel-title {
            font-size: 12px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .fam-panel-body {
            padding: 14px 16px;
        }

        .fam-panel-body .form-group {
            margin-bottom: 10px;
        }

        .fam-panel-body label {
            font-size: 12px;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .fam-panel-body .form-control {
            border-radius: 6px !important;
            border: 1px solid #d0dbe6;
            box-shadow: none;
            height: 32px;
            font-size: 13px;
            padding: 4px 10px;
            color: #1a2634;
        }

        .fam-panel-body textarea.form-control { height: auto; }

        .fam-panel-body .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60,141,188,.12);
        }

        .fam-panel-body .help-block {
            font-size: 11px;
            margin-top: 3px;
        }

        /* ══ CONTACTO ITEM ══ */
        .contacto-item {
            border: 1px solid #e0e7ef;
            border-left: 3px solid #3c8dbc;
            border-radius: 6px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .contacto-item-header {
            background: #f4f6f8;
            padding: 7px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e6ed;
        }

        .contacto-item-header strong {
            font-size: 12px;
            font-weight: 700;
            color: #3c8dbc;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .contacto-item-body {
            padding: 12px;
        }

        .contacto-item-body .form-group {
            margin-bottom: 8px;
        }

        .contacto-item-body label {
            font-size: 11px;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .contacto-item-body .form-control {
            border-radius: 6px !important;
            border: 1px solid #d0dbe6;
            box-shadow: none;
            height: 30px;
            font-size: 12px;
            padding: 3px 9px;
            color: #1a2634;
        }

        .contacto-item-body .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60,141,188,.12);
        }

        /* ══ PANEL LATERAL ══ */
        .fam-side-panel {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .fam-side-header {
            background: #f4f6f8;
            border-bottom: 2px solid #e0e6ed;
            padding: 10px 16px;
        }

        .fam-side-header h4 {
            margin: 0;
            font-size: 12px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .fam-side-body {
            padding: 14px 16px;
            font-size: 12px;
            color: #6b7a8d;
            line-height: 1.7;
        }

        .fam-side-body p { margin-bottom: 6px; }
        .fam-side-body ul { padding-left: 16px; margin: 0; }
        .fam-side-body strong { color: #1a2634; }
    </style>
@endpush

@section('content')

    {{-- ══ ENCABEZADO ══ --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-home text-blue"></i> Nueva familia
        </h4>
        <a href="{{ route('familias.index') }}" class="btn btn-default btn-sm btn-flat"
           style="border-radius:20px;flex-shrink:0;">
            <i class="fa fa-arrow-left"></i> Cancelar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('familias.store') }}" id="form-familia">
        @csrf

        <div class="row">

            {{-- ── Columna principal ── --}}
            <div class="col-md-8">

                {{-- Datos de la familia --}}
                <div class="fam-panel">
                    <div class="fam-panel-header">
                        <h4 class="fam-panel-title">
                            <i class="fa fa-home" style="color:#3c8dbc;"></i> Datos de la familia
                        </h4>
                    </div>
                    <div class="fam-panel-body">

                        <div class="form-group {{ $errors->has('apellido_familia') ? 'has-error' : '' }}">
                            <label for="apellido_familia">
                                Nombre de la familia <span class="text-red">*</span>
                            </label>
                            <input type="text" name="apellido_familia" id="apellido_familia"
                                   class="form-control" maxlength="200"
                                   placeholder="Ej: Familia López García"
                                   value="{{ old('apellido_familia') }}">
                            <span class="help-block text-muted">
                                Generalmente el apellido paterno del padre o madre principal.
                            </span>
                            @error('apellido_familia')
                                <span class="help-block text-red">
                                    <i class="fa fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones"
                                      class="form-control" rows="2" maxlength="1000"
                                      placeholder="Notas adicionales sobre la familia (opcional)">{{ old('observaciones') }}</textarea>
                            @error('observaciones')
                                <span class="help-block text-red">
                                    <i class="fa fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Contactos --}}
                <div class="fam-panel">
                    <div class="fam-panel-header">
                        <h4 class="fam-panel-title">
                            <i class="fa fa-phone" style="color:#3c8dbc;"></i> Contactos familiares
                        </h4>
                        <button type="button" class="btn btn-success btn-xs btn-flat"
                                id="btn-agregar-contacto" style="border-radius:20px;">
                            <i class="fa fa-plus"></i> Agregar contacto
                        </button>
                    </div>
                    <div class="fam-panel-body">

                        <p style="font-size:12px;color:#6b7a8d;margin-bottom:12px;">
                            <i class="fa fa-info-circle"></i>
                            Agrega al menos un contacto. El primero será el contacto principal.
                            Máximo 3 contactos por familia.
                        </p>

                        @error('contactos')
                            <div class="alert alert-danger" style="border-radius:6px;font-size:13px;">
                                <i class="fa fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror

                        <div id="contenedor-contactos"></div>

                    </div>
                </div>

            </div>

            {{-- ── Columna lateral ── --}}
            <div class="col-md-4">

                {{-- Acciones --}}
                <div class="fam-side-panel">
                    <div class="fam-side-body" style="padding:14px 16px;">
                        <button type="submit" class="btn btn-success btn-block btn-flat"
                                id="btn-guardar" style="border-radius:20px;margin-bottom:8px;">
                            <i class="fa fa-save"></i> Registrar familia
                        </button>
                        <a href="{{ route('familias.index') }}"
                           class="btn btn-default btn-block btn-flat" style="border-radius:20px;">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </div>

                {{-- Info --}}
                <div class="fam-side-panel">
                    <div class="fam-side-header">
                        <h4><i class="fa fa-info-circle text-blue"></i> ¿Qué es una familia?</h4>
                    </div>
                    <div class="fam-side-body">
                        <p>Una <strong>familia</strong> agrupa alumnos hermanos y sus contactos compartidos.</p>
                        <p>Al inscribir alumnos hermanos, se vinculan a la misma familia para:</p>
                        <ul>
                            <li>Compartir contactos familiares</li>
                            <li>Aplicar becas por hermanos</li>
                            <li>Estado de cuenta unificado</li>
                            <li>Acceso al portal familiar</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </form>

    {{-- Template de contacto --}}
    <script type="text/template" id="tpl-contacto">
        <div class="contacto-item" data-index="__IDX__">
            <div class="contacto-item-header">
                <strong><i class="fa fa-user"></i> Contacto #<span class="num-contacto">__NUM__</span></strong>
                <button type="button" class="btn btn-danger btn-xs btn-flat btn-eliminar-contacto"
                        style="border-radius:20px;">
                    <i class="fa fa-trash"></i> Eliminar
                </button>
            </div>
            <div class="contacto-item-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nombre(s) <span class="text-red">*</span></label>
                            <input type="text" name="contactos[__IDX__][nombre]"
                                   class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido paterno</label>
                            <input type="text" name="contactos[__IDX__][ap_paterno]"
                                   class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido materno</label>
                            <input type="text" name="contactos[__IDX__][ap_materno]"
                                   class="form-control" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tel. celular <span class="text-red">*</span></label>
                            <input type="tel" name="contactos[__IDX__][telefono_celular]"
                                   class="form-control" maxlength="20" placeholder="10 dígitos">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tel. trabajo</label>
                            <input type="tel" name="contactos[__IDX__][telefono_trabajo]"
                                   class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" name="contactos[__IDX__][email]"
                                   class="form-control" maxlength="200">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CURP</label>
                            <input type="text" name="contactos[__IDX__][curp]"
                                   class="form-control" maxlength="18"
                                   style="text-transform:uppercase;">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Acceso al portal</label>
                            <div style="margin-top:6px;">
                                <label style="font-weight:400;text-transform:none;letter-spacing:0;font-size:12px;">
                                    <input type="hidden" name="contactos[__IDX__][tiene_acceso_portal]" value="0">
                                    <input type="checkbox" name="contactos[__IDX__][tiene_acceso_portal]" value="1">
                                    Habilitar acceso al portal familiar
                                </label>
                            </div>
                            <small class="text-muted" style="font-size:11px;">
                                El usuario se crea desde la sección de usuarios.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

@endsection

@push('scripts')
<script>
$(function() {

    var numContactos  = 0;
    var MAX_CONTACTOS = 3;

    // ── Agregar primer contacto automáticamente ──
    agregarContacto();

    $('#btn-agregar-contacto').on('click', function() {
        if (numContactos >= MAX_CONTACTOS) {
            alert('Máximo ' + MAX_CONTACTOS + ' contactos por familia.');
            return;
        }
        agregarContacto();
    });

    function agregarContacto() {
        if (numContactos >= MAX_CONTACTOS) return;

        var idx = numContactos;
        var num = numContactos + 1;

        var tpl = $('#tpl-contacto').html()
            .replace(/__IDX__/g, idx)
            .replace(/__NUM__/g, num);

        $('#contenedor-contactos').append(tpl);
        numContactos++;
        actualizarBtnAgregar();
    }

    $(document).on('click', '.btn-eliminar-contacto', function() {
        if (numContactos <= 1) {
            alert('Debe haber al menos un contacto familiar.');
            return;
        }
        $(this).closest('.contacto-item').remove();
        numContactos--;
        actualizarBtnAgregar();
        renumerar();
    });

    function actualizarBtnAgregar() {
        $('#btn-agregar-contacto').prop('disabled', numContactos >= MAX_CONTACTOS);
    }

    function renumerar() {
        $('.contacto-item').each(function(i) {
            $(this).find('.num-contacto').text(i + 1);
        });
    }

    $('#form-familia').on('submit', function(e) {
        var ok = true;

        if (!$('#apellido_familia').val().trim()) {
            $('#apellido_familia').closest('.form-group').addClass('has-error');
            if (!$('#apellido_familia').closest('.form-group').find('.val-msg').length) {
                $('#apellido_familia').closest('.form-group')
                    .append('<span class="help-block val-msg text-red"><i class="fa fa-exclamation-circle"></i> El nombre de la familia es obligatorio.</span>');
            }
            ok = false;
        }

        var hayContactoValido = false;
        $('.contacto-item').each(function() {
            var nombre = $(this).find('input[name$="[nombre]"]').val().trim();
            var tel    = $(this).find('input[name$="[telefono_celular]"]').val().trim();
            if (nombre && tel) hayContactoValido = true;
            if (nombre && !tel) {
                $(this).find('input[name$="[telefono_celular]"]')
                    .closest('.form-group').addClass('has-error');
                ok = false;
            }
        });

        if (!hayContactoValido) {
            ok = false;
            alert('Agrega al menos un contacto con nombre y teléfono.');
        }

        if (!ok) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.has-error').first().offset().top - 80
            }, 300);
            return false;
        }

        $('#btn-guardar').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Registrando...');
    });

    $(document).on('input change', '.has-error input, .has-error select', function() {
        $(this).closest('.form-group').removeClass('has-error').find('.val-msg').remove();
    });

    $('#apellido_familia').on('input', function() {
        $(this).closest('.form-group').removeClass('has-error').find('.val-msg').remove();
    });

});
</script>
@endpush
