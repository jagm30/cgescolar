@extends('layouts.master')

@section('page_title', 'Editar familia')
@section('page_subtitle', 'Actualizar datos de la familia')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li><a href="{{ route('familias.show', $familia->id) }}">Familia {{ $familia->apellido_familia }}</a></li>
    <li class="active">Editar</li>
@endsection

@push('styles')
    <style>
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
            gap: 10px;
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
        .fam-panel-body .form-group { margin-bottom: 10px; }
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
        .fam-panel-body .help-block { font-size: 11px; margin-top: 3px; }
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
    </style>
@endpush

@section('content')

    {{-- ══ ENCABEZADO ══ --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-home text-blue"></i> Editar — Familia {{ $familia->apellido_familia }}
        </h4>
        <a href="{{ route('familias.show', $familia->id) }}" class="btn btn-default btn-sm btn-flat"
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

    <form method="POST" action="{{ route('familias.update', $familia->id) }}" id="form-familia">
        @csrf
        @method('PUT')

        <div class="row">

            {{-- ── Columna principal ── --}}
            <div class="col-md-8">

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
                                   value="{{ old('apellido_familia', $familia->apellido_familia) }}">
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
                                      placeholder="Notas adicionales (opcional)">{{ old('observaciones', $familia->observaciones) }}</textarea>
                            @error('observaciones')
                                <span class="help-block text-red">
                                    <i class="fa fa-exclamation-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Estado</label>
                            <div style="margin-top:4px;">
                                <label style="font-weight:400;text-transform:none;letter-spacing:0;font-size:13px;">
                                    <input type="hidden" name="activo" value="0">
                                    <input type="checkbox" name="activo" value="1"
                                           {{ old('activo', $familia->activo) ? 'checked' : '' }}>
                                    Familia activa
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- ── Columna lateral ── --}}
            <div class="col-md-4">

                <div class="fam-side-panel">
                    <div class="fam-side-body" style="padding:14px 16px;">
                        <button type="submit" class="btn btn-primary btn-block btn-flat"
                                id="btn-guardar" style="border-radius:20px;margin-bottom:8px;">
                            <i class="fa fa-save"></i> Guardar cambios
                        </button>
                        <a href="{{ route('familias.show', $familia->id) }}"
                           class="btn btn-default btn-block btn-flat" style="border-radius:20px;">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </div>

                <div class="fam-side-panel">
                    <div class="fam-side-header">
                        <h4><i class="fa fa-info-circle text-blue"></i> Información</h4>
                    </div>
                    <div class="fam-side-body">
                        <p>Desde aquí puedes actualizar el nombre y las observaciones de la familia.</p>
                        <p>Para gestionar los <strong>contactos familiares</strong> ve a la ficha de la familia.</p>
                    </div>
                </div>

            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
$(function() {
    $('#form-familia').on('submit', function(e) {
        if (!$('#apellido_familia').val().trim()) {
            var $grp = $('#apellido_familia').closest('.form-group');
            $grp.addClass('has-error');
            if (!$grp.find('.val-msg').length) {
                $grp.append('<span class="help-block val-msg text-red"><i class="fa fa-exclamation-circle"></i> El nombre de la familia es obligatorio.</span>');
            }
            e.preventDefault();
            return false;
        }
        $('#btn-guardar').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
    });

    $('#apellido_familia').on('input', function() {
        $(this).closest('.form-group').removeClass('has-error').find('.val-msg').remove();
    });
});
</script>
@endpush
