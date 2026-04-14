@extends('layouts.master')

@section('page_title', 'Nueva familia')
@section('page_subtitle', 'Registro de familia')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li class="active">Nueva familia</li>
@endsection

@section('content')

<form method="POST"
      action="{{ route('familias.store') }}"
      id="form-familia">
@csrf

<div class="row">

    {{-- ── Columna principal ── --}}
    <div class="col-md-8">

        {{-- Datos de la familia --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-home"></i> Datos de la familia
                </h3>
            </div>
            <div class="box-body">

                <div class="form-group {{ $errors->has('apellido_familia') ? 'has-error' : '' }}">
                    <label for="apellido_familia">
                        Nombre de la familia <span class="text-red">*</span>
                    </label>
                    <input type="text"
                           name="apellido_familia"
                           id="apellido_familia"
                           class="form-control"
                           placeholder="Ej: Familia López García"
                           value="{{ old('apellido_familia') }}"
                           maxlength="200">
                    <span class="help-block" style="font-size:11px;">
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
                    <textarea name="observaciones"
                              id="observaciones"
                              class="form-control"
                              rows="3"
                              maxlength="1000"
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
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-phone"></i> Contactos familiares
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-success btn-xs" id="btn-agregar-contacto">
                        <i class="fa fa-plus"></i> Agregar contacto
                    </button>
                </div>
            </div>
            <div class="box-body">

                <p class="text-muted" style="font-size:12px; margin-bottom:12px;">
                    <i class="fa fa-info-circle"></i>
                    Agrega al menos un contacto. El primero será el contacto principal.
                    Máximo 3 contactos por familia.
                </p>

                @error('contactos')
                    <div class="alert alert-danger">
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
        <div class="box box-primary">
            <div class="box-body">
                <button type="submit" class="btn btn-success btn-block" id="btn-guardar">
                    <i class="fa fa-save"></i> Registrar familia
                </button>
                <a href="{{ route('familias.index') }}"
                   class="btn btn-default btn-block" style="margin-top:6px;">
                    <i class="fa fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>

        {{-- Información --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size:13px;">
                    <i class="fa fa-info-circle"></i> ¿Qué es una familia?
                </h3>
            </div>
            <div class="box-body" style="font-size:12px; color:#666; line-height:1.7;">
                <p>Una <strong>familia</strong> agrupa alumnos hermanos y sus contactos compartidos.</p>
                <p>Al inscribir alumnos hermanos, se vinculan a la misma familia para:</p>
                <ul style="padding-left:16px; margin:0;">
                    <li>Compartir contactos familiares</li>
                    <li>Aplicar becas por hermanos</li>
                    <li>Estado de cuenta unificado</li>
                    <li>Acceso al portal familiar</li>
                </ul>
            </div>
        </div>

        {{-- Errores --}}
        @if($errors->any())
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Errores
                </h3>
            </div>
            <div class="box-body">
                <ul style="padding-left:18px; margin:0;">
                    @foreach($errors->all() as $error)
                        <li style="color:#a94442; font-size:12px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

    </div>

</div>{{-- /.row --}}
</form>

{{-- Template de contacto --}}
<script type="text/template" id="tpl-contacto">
<div class="contacto-item panel panel-default" data-index="__IDX__" style="margin-bottom:10px;">
    <div class="panel-heading" style="padding:8px 12px; background:#f5f5f5;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <strong style="font-size:13px;">
                Contacto #<span class="num-contacto">__NUM__</span>
            </strong>
            <button type="button" class="btn btn-danger btn-xs btn-eliminar-contacto">
                <i class="fa fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
    <div class="panel-body" style="padding:12px;">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>
                    <input type="text" name="contactos[__IDX__][nombre]"
                           class="form-control input-sm" maxlength="100">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Apellido paterno</label>
                    <input type="text" name="contactos[__IDX__][ap_paterno]"
                           class="form-control input-sm" maxlength="100">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Apellido materno</label>
                    <input type="text" name="contactos[__IDX__][ap_materno]"
                           class="form-control input-sm" maxlength="100">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Teléfono celular <span class="text-red">*</span></label>
                    <input type="tel" name="contactos[__IDX__][telefono_celular]"
                           class="form-control input-sm" maxlength="20"
                           placeholder="10 dígitos">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Teléfono trabajo</label>
                    <input type="tel" name="contactos[__IDX__][telefono_trabajo]"
                           class="form-control input-sm" maxlength="20">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Correo electrónico</label>
                    <input type="email" name="contactos[__IDX__][email]"
                           class="form-control input-sm" maxlength="200">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">CURP</label>
                    <input type="text" name="contactos[__IDX__][curp]"
                           class="form-control input-sm" maxlength="18"
                           style="text-transform:uppercase">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label style="font-size:12px;">Acceso al portal</label>
                    <div style="margin-top:6px;">
                        <label class="checkbox-inline">
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
        {{-- Nota: parentesco, tipo, orden y permisos de recoger/pagos
             se configuran en la inscripción del alumno (tabla alumno_contacto) --}}
    </div>
</div>
</script>

@endsection

@push('scripts')
<script>
$(function() {

    var numContactos  = 0;
    var MAX_CONTACTOS = 3;

    // ── Agregar primer contacto automáticamente ───────────
    agregarContacto();

    // ── Botón agregar ─────────────────────────────────────
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

    // ── Eliminar contacto ─────────────────────────────────
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

    // ── Validación antes de enviar ────────────────────────
    $('#form-familia').on('submit', function(e) {
        var ok = true;

        // Nombre de la familia
        if (!$('#apellido_familia').val().trim()) {
            $('#apellido_familia').closest('.form-group').addClass('has-error');
            if (!$('#apellido_familia').closest('.form-group').find('.val-msg').length) {
                $('#apellido_familia').closest('.form-group')
                    .append('<span class="help-block val-msg text-red"><i class="fa fa-exclamation-circle"></i> El nombre de la familia es obligatorio.</span>');
            }
            ok = false;
        }

        // Contactos
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

        // Envío — deshabilitar botón
        $('#btn-guardar').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Registrando...');
    });

    // ── Limpiar errores al escribir ───────────────────────
    $(document).on('input change', '.has-error input, .has-error select', function() {
        $(this).closest('.form-group')
            .removeClass('has-error')
            .find('.val-msg').remove();
    });

    $('#apellido_familia').on('input', function() {
        $(this).closest('.form-group')
            .removeClass('has-error')
            .find('.val-msg').remove();
    });

});
</script>
@endpush
