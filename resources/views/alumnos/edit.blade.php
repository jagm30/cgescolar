@extends('layouts.master')

@section('page_title', 'Editar alumno')
@section('page_subtitle', $alumno->nombre . ' ' . $alumno->ap_paterno . ' — ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li><a href="{{ route('alumnos.show', $alumno->id) }}">{{ $alumno->ap_paterno }}</a></li>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong><i class="fa fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
    <ul style="margin:6px 0 0 18px;">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row">

    <div class="col-md-9">
        <form method="POST"
              action="{{ route('alumnos.update', $alumno->id) }}"
              enctype="multipart/form-data"
              id="form-editar-alumno">
        @csrf
        @method('PUT')

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active" id="tab-li-1">
                    <a href="#tab-datos" data-toggle="tab">
                        <i class="fa fa-user"></i> Datos personales
                        @if($errors->hasAny(['nombre','ap_paterno','fecha_nacimiento','curp']))
                            <span class="label label-danger">!</span>
                        @endif
                    </a>
                </li>
                <li id="tab-li-2">
                    <a href="#tab-foto" data-toggle="tab">
                        <i class="fa fa-camera"></i> Foto y estado
                        @if($errors->hasAny(['foto','estado','fecha_baja']))
                            <span class="label label-danger">!</span>
                        @endif
                    </a>
                </li>
                <li id="tab-li-3">
                    <a href="#tab-contactos" data-toggle="tab">
                        <i class="fa fa-phone"></i> Contactos
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- TAB 1: Datos personales --}}
                <div class="tab-pane active" id="tab-datos">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                <label for="nombre">Nombre(s) <span class="text-red">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control"
                                       value="{{ old('nombre', $alumno->nombre) }}" maxlength="100">
                                @error('nombre')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('ap_paterno') ? 'has-error' : '' }}">
                                <label for="ap_paterno">Apellido paterno <span class="text-red">*</span></label>
                                <input type="text" name="ap_paterno" id="ap_paterno" class="form-control"
                                       value="{{ old('ap_paterno', $alumno->ap_paterno) }}" maxlength="100">
                                @error('ap_paterno')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ap_materno">Apellido materno</label>
                                <input type="text" name="ap_materno" id="ap_materno" class="form-control"
                                       value="{{ old('ap_materno', $alumno->ap_materno) }}" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('fecha_nacimiento') ? 'has-error' : '' }}">
                                <label for="fecha_nacimiento">Fecha de nacimiento <span class="text-red">*</span></label>
                                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control"
                                       value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento?->format('Y-m-d')) }}"
                                       max="{{ now()->subYears(2)->format('Y-m-d') }}">
                                @error('fecha_nacimiento')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="genero">Género</label>
                                <select name="genero" id="genero" class="form-control">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="M"    {{ old('genero', $alumno->genero) === 'M'    ? 'selected' : '' }}>Masculino</option>
                                    <option value="F"    {{ old('genero', $alumno->genero) === 'F'    ? 'selected' : '' }}>Femenino</option>
                                    <option value="Otro" {{ old('genero', $alumno->genero) === 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('curp') ? 'has-error' : '' }}">
                                <label for="curp">CURP
                                    <small class="text-muted" id="curp-lbl">
                                        (<span id="curp-chars">{{ strlen(old('curp', $alumno->curp ?? '')) }}</span>/18)
                                    </small>
                                </label>
                                <input type="text" name="curp" id="curp" class="form-control"
                                       placeholder="18 caracteres"
                                       value="{{ old('curp', $alumno->curp) }}"
                                       maxlength="18" style="text-transform:uppercase">
                                @error('curp')
                                    <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-control"
                                  rows="2" maxlength="1000">{{ old('observaciones', $alumno->observaciones) }}</textarea>
                    </div>
                    <div style="text-align:right;margin-top:8px;">
                        <button type="button" class="btn btn-primary btn-sm"
                                onclick="$('#tab-li-2 a').tab('show')">
                            Siguiente <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- TAB 2: Foto y estado --}}
                <div class="tab-pane" id="tab-foto">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                                <label>Foto del alumno</label>
                                <div style="margin-bottom:10px;">
                                    <div id="foto-preview-wrap"
                                         onclick="document.getElementById('foto').click()"
                                         style="width:130px;height:130px;border:2px dashed #ccc;border-radius:6px;
                                                display:flex;align-items:center;justify-content:center;
                                                cursor:pointer;overflow:hidden;background:#fafafa;">
                                        @if($alumno->foto_url)
                                            <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                                                 alt="Foto" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <div style="text-align:center;color:#ccc;">
                                                <i class="fa fa-camera" style="font-size:32px;"></i>
                                                <div style="font-size:11px;margin-top:6px;">Sin foto</div>
                                            </div>
                                        @endif
                                    </div>
                                    <small class="text-muted" style="display:block;margin-top:4px;font-size:11px;">
                                        <i class="fa fa-hand-pointer-o"></i> Click para cambiar
                                    </small>
                                </div>
                                <input type="file" name="foto" id="foto"
                                       accept="image/jpeg,image/png,image/webp" style="display:none">
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <label class="btn btn-default btn-sm btn-flat"
                                               for="foto" style="margin:0;cursor:pointer;">
                                            <i class="fa fa-camera"></i>
                                            {{ $alumno->foto_url ? 'Cambiar' : 'Seleccionar' }}
                                        </label>
                                    </span>
                                    <input type="text" id="foto-nombre" class="form-control input-sm"
                                           placeholder="Sin cambios" readonly>
                                </div>
                                <span class="help-block" style="font-size:11px;">
                                    JPG, PNG o WEBP · Máx. 2 MB.
                                </span>
                                @error('foto')
                                    <span class="help-block text-red">
                                        <i class="fa fa-exclamation-circle"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('estado') ? 'has-error' : '' }}">
                                <label for="estado">Estado <span class="text-red">*</span></label>
                                <select name="estado" id="estado" class="form-control">
                                    <option value="activo"          {{ old('estado', $alumno->estado) === 'activo'          ? 'selected' : '' }}>Activo</option>
                                    <option value="baja_temporal"   {{ old('estado', $alumno->estado) === 'baja_temporal'   ? 'selected' : '' }}>Baja temporal</option>
                                    <option value="baja_definitiva" {{ old('estado', $alumno->estado) === 'baja_definitiva' ? 'selected' : '' }}>Baja definitiva</option>
                                    <option value="egresado"        {{ old('estado', $alumno->estado) === 'egresado'        ? 'selected' : '' }}>Egresado</option>
                                </select>
                                @error('estado')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group {{ $errors->has('fecha_baja') ? 'has-error' : '' }}"
                                 id="bloque-fecha-baja"
                                 style="{{ in_array(old('estado', $alumno->estado), ['baja_temporal','baja_definitiva']) ? '' : 'display:none;' }}">
                                <label for="fecha_baja">Fecha de baja</label>
                                <input type="date" name="fecha_baja" id="fecha_baja" class="form-control"
                                       value="{{ old('fecha_baja', $alumno->fecha_baja?->format('Y-m-d')) }}">
                                @error('fecha_baja')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-top:16px;">
                                <label>Matrícula</label>
                                <input type="text" class="form-control"
                                       value="{{ $alumno->matricula }}" disabled>
                                <span class="help-block" style="font-size:11px;">No se puede modificar.</span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:8px;">
                        <button type="button" class="btn btn-default btn-sm"
                                onclick="$('#tab-li-1 a').tab('show')">
                            <i class="fa fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-primary btn-sm"
                                onclick="$('#tab-li-3 a').tab('show')">
                            Siguiente <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- TAB 3: Contactos editables --}}
                <div class="tab-pane" id="tab-contactos">
                    <div id="ctc-alerta" style="display:none;" class="alert alert-dismissible">
                        <button type="button" class="close"
                                onclick="this.parentElement.style.display='none'">&times;</button>
                        <span id="ctc-alerta-msg"></span>
                    </div>

                    @forelse($alumno->contactos as $contacto)
                    <div class="panel panel-default ctc-panel" style="margin-bottom:10px;"
                         data-id="{{ $contacto->id }}">
                        <div class="panel-heading" style="padding:8px 12px;background:#f5f5f5;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <strong style="font-size:13px;">
                                    <span class="ctc-titulo">{{ $contacto->nombre }} {{ $contacto->ap_paterno }}</span>
                                    @if($contacto->pivot->orden == 1)
                                        <span class="label label-primary" style="font-size:10px;margin-left:4px;">Principal</span>
                                    @endif
                                </strong>
                                <div>
                                    <button type="button" class="btn btn-success btn-xs btn-ctc-guardar">
                                        <i class="fa fa-save"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-danger btn-xs btn-ctc-eliminar"
                                            style="margin-left:4px;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body" style="padding:12px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>
                                        <input type="text" class="form-control input-sm ctc-nombre"
                                               value="{{ $contacto->nombre }}" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Apellido paterno</label>
                                        <input type="text" class="form-control input-sm ctc-ap-paterno"
                                               value="{{ $contacto->ap_paterno }}" maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Apellido materno</label>
                                        <input type="text" class="form-control input-sm ctc-ap-materno"
                                               value="{{ $contacto->ap_materno }}" maxlength="100">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Teléfono <span class="text-red">*</span></label>
                                        <input type="tel" class="form-control input-sm ctc-telefono"
                                               value="{{ $contacto->telefono_celular }}" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Correo</label>
                                        <input type="email" class="form-control input-sm ctc-email"
                                               value="{{ $contacto->email }}" maxlength="200">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Parentesco</label>
                                        <select class="form-control input-sm ctc-parentesco">
                                            @foreach(['padre'=>'Padre','madre'=>'Madre','abuelo'=>'Abuelo/a','tio'=>'Tío/a','otro'=>'Otro'] as $val => $lbl)
                                                <option value="{{ $val }}"
                                                    {{ $contacto->pivot->parentesco === $val ? 'selected' : '' }}>
                                                    {{ $lbl }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Tipo</label>
                                        <select class="form-control input-sm ctc-tipo">
                                            <option value="padre"               {{ $contacto->pivot->tipo === 'padre'               ? 'selected' : '' }}>Padre/Madre</option>
                                            <option value="tutor"               {{ $contacto->pivot->tipo === 'tutor'               ? 'selected' : '' }}>Tutor</option>
                                            <option value="tercero_autorizado"  {{ $contacto->pivot->tipo === 'tercero_autorizado'  ? 'selected' : '' }}>Tercero autorizado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Orden</label>
                                        <select class="form-control input-sm ctc-orden">
                                            <option value="1" {{ $contacto->pivot->orden == 1 ? 'selected' : '' }}>1 — Principal</option>
                                            <option value="2" {{ $contacto->pivot->orden == 2 ? 'selected' : '' }}>2 — Secundario</option>
                                            <option value="3" {{ $contacto->pivot->orden == 3 ? 'selected' : '' }}>3 — Tercero</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" class="ctc-recoger"
                                            {{ $contacto->pivot->autorizado_recoger ? 'checked' : '' }}>
                                        Autorizado para recoger
                                    </label>
                                    <label class="checkbox-inline" style="margin-left:16px;">
                                        <input type="checkbox" class="ctc-pago"
                                            {{ $contacto->pivot->es_responsable_pago ? 'checked' : '' }}>
                                        Responsable de pagos
                                    </label>
                                    <label class="checkbox-inline" style="margin-left:16px;">
                                        <input type="checkbox" class="ctc-portal"
                                            {{ $contacto->tiene_acceso_portal ? 'checked' : '' }}>
                                        Acceso al portal
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Sin contactos registrados.
                        </div>
                    @endforelse

                    {{-- ── Formulario nuevo contacto ── --}}
                    <div class="panel panel-success" style="margin-top:16px;">
                        <div class="panel-heading" style="padding:8px 12px;cursor:pointer; background-color: #00a65a;"
                             onclick="(function(){
                                 var f=document.getElementById('form-nuevo-ctc');
                                 var i=document.getElementById('ico-toggle-nuevo-ctc');
                                 var v=f.style.display!=='none';
                                 f.style.display=v?'none':'block';
                                 i.className=v?'fa fa-chevron-down':'fa fa-chevron-up';
                             })()">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <strong style="font-size:13px;color:#fff;">
                                    <i class="fa fa-plus-circle"></i> Agregar nuevo contacto
                                </strong>
                                <i class="fa fa-chevron-down" id="ico-toggle-nuevo-ctc" style="color:#fff;"></i>
                            </div>
                        </div>
                        <div class="panel-body" id="form-nuevo-ctc" style="display:none;padding:12px;">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>
                                        <input type="text" id="nctc-nombre" class="form-control input-sm"
                                               maxlength="100" placeholder="Nombre(s)">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Apellido paterno</label>
                                        <input type="text" id="nctc-ap-paterno" class="form-control input-sm"
                                               maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Apellido materno</label>
                                        <input type="text" id="nctc-ap-materno" class="form-control input-sm"
                                               maxlength="100">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Teléfono <span class="text-red">*</span></label>
                                        <input type="tel" id="nctc-telefono" class="form-control input-sm"
                                               maxlength="20" placeholder="10 dígitos">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Correo</label>
                                        <input type="email" id="nctc-email" class="form-control input-sm"
                                               maxlength="200">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label style="font-size:12px;">CURP</label>
                                        <input type="text" id="nctc-curp" class="form-control input-sm"
                                               maxlength="18" style="text-transform:uppercase">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Parentesco <span class="text-red">*</span></label>
                                        <select id="nctc-parentesco" class="form-control input-sm">
                                            <option value="">-- Seleccionar --</option>
                                            <option value="padre">Padre</option>
                                            <option value="madre">Madre</option>
                                            <option value="abuelo">Abuelo/a</option>
                                            <option value="tio">Tío/a</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Tipo <span class="text-red">*</span></label>
                                        <select id="nctc-tipo" class="form-control input-sm">
                                            <option value="">-- Seleccionar --</option>
                                            <option value="padre">Padre/Madre</option>
                                            <option value="tutor">Tutor</option>
                                            <option value="tercero_autorizado">Tercero autorizado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label style="font-size:12px;">Orden</label>
                                        <select id="nctc-orden" class="form-control input-sm">
                                            <option value="1">1 — Principal</option>
                                            <option value="2">2 — Secundario</option>
                                            <option value="3">3 — Tercero</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-top:22px;">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="nctc-recoger"> Autorizado recoger
                                    </label>
                                    <label class="checkbox-inline" style="margin-left:8px;">
                                        <input type="checkbox" id="nctc-pago"> Resp. pagos
                                    </label>
                                    <label class="checkbox-inline" style="margin-left:8px;">
                                        <input type="checkbox" id="nctc-portal"> Portal
                                    </label>
                                </div>
                            </div>

                            <div style="text-align:right;margin-top:4px;">
                                <button type="button" class="btn btn-default btn-sm" id="btn-cancelar-nuevo-ctc">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="btn-guardar-nuevo-ctc">
                                    <i class="fa fa-plus"></i> Agregar contacto
                                </button>
                            </div>

                        </div>
                    </div>

                    <div style="margin-top:8px;">
                        <button type="button" class="btn btn-default btn-sm"
                                onclick="$('#tab-li-2 a').tab('show')">
                            <i class="fa fa-arrow-left"></i> Anterior
                        </button>
                    </div>
                </div>

            </div>{{-- /.tab-content --}}
        </div>{{-- /.nav-tabs-custom --}}
        </form>
    </div>

    {{-- Columna lateral --}}
    <div class="col-md-3">
        <div class="box box-primary">
            <div class="box-body">
                <button type="submit" form="form-editar-alumno"
                        class="btn btn-success btn-block" id="btn-guardar">
                    <i class="fa fa-save"></i> Guardar cambios
                </button>
                <a href="{{ route('alumnos.show', $alumno->id) }}"
                   class="btn btn-default btn-block" style="margin-top:6px;">
                    <i class="fa fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size:13px;">
                    <i class="fa fa-info-circle"></i> Resumen
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table" style="font-size:12px;margin:0;">
                    <tr>
                        <th style="color:#999;font-weight:400;width:45%;">Matrícula</th>
                        <td><code>{{ $alumno->matricula }}</code></td>
                    </tr>
                    <tr>
                        <th style="color:#999;font-weight:400;">Familia</th>
                        <td>{{ $alumno->familia?->apellido_familia ?? '—' }}</td>
                    </tr>
                    @if($alumno->inscripciones->isNotEmpty())
                    @php $ins = $alumno->inscripciones->first(); @endphp
                    <tr>
                        <th style="color:#999;font-weight:400;">Grupo</th>
                        <td>{{ $ins->grupo->grado->nombre }}° {{ $ins->grupo->nombre }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(function() {

    var MAX_FOTO_MB = 2 * 1024 * 1024;
    var ALUMNO_ID   = {{ $alumno->id }};
    var FAMILIA_ID  = {{ $alumno->familia_id ?? 'null' }};

    // ── CURP ─────────────────────────────────────────────
    $('#curp').on('input', function() {
        this.value = this.value.toUpperCase();
        var len = this.value.length;
        $('#curp-chars').text(len);
        $('#curp-lbl').css('color', len === 18 ? '#00a65a' : len > 0 ? '#f39c12' : '#999');
    });

    // ── Estado → fecha baja ───────────────────────────────
    $('#estado').on('change', function() {
        var v = $(this).val();
        if (v === 'baja_temporal' || v === 'baja_definitiva') {
            $('#bloque-fecha-baja').show();
            if (!$('#fecha_baja').val()) $('#fecha_baja').val("{{ now()->format('Y-m-d') }}");
        } else {
            $('#bloque-fecha-baja').hide();
            $('#fecha_baja').val('');
        }
    });

    // ── Foto preview ──────────────────────────────────────
    $('#foto').on('change', function() {
        var archivo = this.files[0];
        if (!archivo) return;
        if (archivo.size > MAX_FOTO_MB) {
            this.value = '';
            $('#foto-nombre').val('');
            alert('Archivo demasiado grande. Máximo 2 MB.');
            return;
        }
        $('#foto-nombre').val(archivo.name);
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#foto-preview-wrap')
                .css('border', '2px solid #00a65a')
                .html('<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">');
        };
        reader.readAsDataURL(archivo);
    });

    // ── Guardar alumno ────────────────────────────────────
    $('#form-editar-alumno').on('submit', function() {
        $('#btn-guardar').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
    });

    // ── Eliminar contacto (AJAX) ──────────────────────────
    $(document).on('click', '.btn-ctc-eliminar', function() {
        var panel  = $(this).closest('.ctc-panel');
        var id     = panel.data('id');
        var nombre = panel.find('.ctc-titulo').text().trim();
        var total  = $('.ctc-panel').length;

        if (total <= 1) {
            mostrarAlerta('Debe haber al menos un contacto familiar.', 'danger');
            return;
        }

        if (!confirm('¿Eliminar el contacto "' + nombre + '"? Esta acción no se puede deshacer.')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/familias/contactos/' + id,
            method: 'DELETE',
            success: function(res) {
                panel.fadeOut(300, function() { $(this).remove(); });
                mostrarAlerta(res.message || 'Contacto eliminado.', 'success');
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                mostrarAlerta(xhr.responseJSON?.message || 'Error al eliminar.', 'danger');
            }
        });
    });

    // ── Guardar contacto existente (AJAX) ─────────────────
    $(document).on('click', '.btn-ctc-guardar', function() {
        var panel = $(this).closest('.ctc-panel');
        var id    = panel.data('id');
        var btn   = $(this);
        var orig  = btn.html();

        var datos = {
            nombre:              panel.find('.ctc-nombre').val().trim(),
            ap_paterno:          panel.find('.ctc-ap-paterno').val().trim(),
            ap_materno:          panel.find('.ctc-ap-materno').val().trim(),
            telefono_celular:    panel.find('.ctc-telefono').val().trim(),
            email:               panel.find('.ctc-email').val().trim(),
            parentesco:          panel.find('.ctc-parentesco').val(),
            tipo:                panel.find('.ctc-tipo').val(),
            orden:               parseInt(panel.find('.ctc-orden').val()),
            autorizado_recoger:  panel.find('.ctc-recoger').is(':checked'),
            es_responsable_pago: panel.find('.ctc-pago').is(':checked'),
            tiene_acceso_portal: panel.find('.ctc-portal').is(':checked'),
        };

        if (!datos.nombre)           { alert('El nombre es obligatorio.');   return; }
        if (!datos.telefono_celular) { alert('El teléfono es obligatorio.'); return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/familias/contactos/' + id,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function(res) {
                panel.find('.ctc-titulo').text(datos.nombre + ' ' + datos.ap_paterno);
                btn.prop('disabled', false).html('<i class="fa fa-check"></i> Guardado');
                mostrarAlerta(res.message || 'Contacto guardado.', 'success');
                setTimeout(function(){ btn.html(orig); }, 2500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(orig);
                mostrarAlerta(xhr.responseJSON?.message || 'Error al guardar.', 'danger');
            }
        });
    });

    // ── Nuevo contacto: toggle ────────────────────────────
    // El heading usa onclick inline; aquí manejamos cancelar y guardar
    $('#btn-cancelar-nuevo-ctc').on('click', function() {
        limpiarNuevoCtc();
        $('#form-nuevo-ctc').hide();
        $('#ico-toggle-nuevo-ctc').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    // ── Nuevo contacto: guardar ───────────────────────────
    $('#btn-guardar-nuevo-ctc').on('click', function() {
        var btn = $(this);

        var datos = {
            alumno_id:           ALUMNO_ID,
            familia_id:          FAMILIA_ID,
            nombre:              $('#nctc-nombre').val().trim(),
            ap_paterno:          $('#nctc-ap-paterno').val().trim(),
            ap_materno:          $('#nctc-ap-materno').val().trim(),
            telefono_celular:    $('#nctc-telefono').val().trim(),
            email:               $('#nctc-email').val().trim(),
            curp:                $('#nctc-curp').val().trim().toUpperCase(),
            parentesco:          $('#nctc-parentesco').val(),
            tipo:                $('#nctc-tipo').val(),
            orden:               parseInt($('#nctc-orden').val()),
            autorizado_recoger:  $('#nctc-recoger').is(':checked'),
            es_responsable_pago: $('#nctc-pago').is(':checked'),
            tiene_acceso_portal: $('#nctc-portal').is(':checked'),
        };

        if (!datos.nombre)           { mostrarAlerta('El nombre es obligatorio.', 'danger');      $('#nctc-nombre').focus();      return; }
        if (!datos.telefono_celular) { mostrarAlerta('El teléfono es obligatorio.', 'danger');    $('#nctc-telefono').focus();    return; }
        if (!datos.parentesco)       { mostrarAlerta('El parentesco es obligatorio.', 'danger');  $('#nctc-parentesco').focus();  return; }
        if (!datos.tipo)             { mostrarAlerta('El tipo es obligatorio.', 'danger');        $('#nctc-tipo').focus();        return; }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '/familias/contactos',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');

                var c     = res.contacto;
                var pivot = res.pivot;

                // Construir panel del nuevo contacto
                var parentescos = {padre:'Padre',madre:'Madre',abuelo:'Abuelo/a',tio:'Tío/a',otro:'Otro'};
                var selOpts = $.map(parentescos, function(lbl, val) {
                    return '<option value="' + val + '"' + (val === datos.parentesco ? ' selected' : '') + '>' + lbl + '</option>';
                }).join('');
                var tipoOpts =
                    '<option value="padre"'              + (datos.tipo==='padre'             ?' selected':'') + '>Padre/Madre</option>' +
                    '<option value="tutor"'              + (datos.tipo==='tutor'             ?' selected':'') + '>Tutor</option>' +
                    '<option value="tercero_autorizado"' + (datos.tipo==='tercero_autorizado'?' selected':'') + '>Tercero autorizado</option>';
                var ordenOpts =
                    '<option value="1"' + (datos.orden===1?' selected':'') + '>1 — Principal</option>' +
                    '<option value="2"' + (datos.orden===2?' selected':'') + '>2 — Secundario</option>' +
                    '<option value="3"' + (datos.orden===3?' selected':'') + '>3 — Tercero</option>';

                var html =
                '<div class="panel panel-default ctc-panel" style="margin-bottom:10px;" data-id="' + c.id + '">' +
                  '<div class="panel-heading" style="padding:8px 12px;background:#f5f5f5;">' +
                    '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                      '<strong style="font-size:13px;"><span class="ctc-titulo">' + datos.nombre + ' ' + datos.ap_paterno + '</span></strong>' +
                      '<div>' +
                      '<button type="button" class="btn btn-success btn-xs btn-ctc-guardar"><i class="fa fa-save"></i> Guardar</button>' +
                      '<button type="button" class="btn btn-danger btn-xs btn-ctc-eliminar" style="margin-left:4px;"><i class="fa fa-trash"></i></button>' +
                      '</div>' +
                    '</div>' +
                  '</div>' +
                  '<div class="panel-body" style="padding:12px;">' +
                    '<div class="row">' +
                      '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>' +
                        '<input type="text" class="form-control input-sm ctc-nombre" value="' + datos.nombre + '" maxlength="100"></div></div>' +
                      '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Apellido paterno</label>' +
                        '<input type="text" class="form-control input-sm ctc-ap-paterno" value="' + (datos.ap_paterno||'') + '" maxlength="100"></div></div>' +
                      '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Apellido materno</label>' +
                        '<input type="text" class="form-control input-sm ctc-ap-materno" value="' + (datos.ap_materno||'') + '" maxlength="100"></div></div>' +
                    '</div>' +
                    '<div class="row">' +
                      '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Teléfono <span class="text-red">*</span></label>' +
                        '<input type="tel" class="form-control input-sm ctc-telefono" value="' + (datos.telefono_celular||'') + '" maxlength="20"></div></div>' +
                      '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Correo</label>' +
                        '<input type="email" class="form-control input-sm ctc-email" value="' + (datos.email||'') + '" maxlength="200"></div></div>' +
                      '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Parentesco</label>' +
                        '<select class="form-control input-sm ctc-parentesco">' + selOpts + '</select></div></div>' +
                      '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Tipo</label>' +
                        '<select class="form-control input-sm ctc-tipo">' + tipoOpts + '</select></div></div>' +
                      '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Orden</label>' +
                        '<select class="form-control input-sm ctc-orden">' + ordenOpts + '</select></div></div>' +
                    '</div>' +
                    '<div class="row"><div class="col-md-12">' +
                      '<label class="checkbox-inline"><input type="checkbox" class="ctc-recoger"' + (pivot.autorizado_recoger?' checked':'') + '> Autorizado para recoger</label>' +
                      '<label class="checkbox-inline" style="margin-left:16px;"><input type="checkbox" class="ctc-pago"' + (pivot.es_responsable_pago?' checked':'') + '> Responsable de pagos</label>' +
                      '<label class="checkbox-inline" style="margin-left:16px;"><input type="checkbox" class="ctc-portal"' + (c.tiene_acceso_portal?' checked':'') + '> Acceso al portal</label>' +
                    '</div></div>' +
                  '</div>' +
                '</div>';

                // Insertar antes del panel verde
                $('.panel.panel-success').before(html);
                // Quitar alerta de sin contactos si existía
                $('.alert.alert-warning').remove();

                limpiarNuevoCtc();
                $('#form-nuevo-ctc').hide();
                $('#ico-toggle-nuevo-ctc').removeClass('fa-chevron-up').addClass('fa-chevron-down');

                mostrarAlerta(res.message || 'Contacto agregado.', 'success');
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');
                var msg = 'Error al agregar el contacto.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors)  msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    else if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                }
                mostrarAlerta(msg, 'danger');
            }
        });
    });

    // ── Helpers ───────────────────────────────────────────
    function limpiarNuevoCtc() {
        $('#nctc-nombre, #nctc-ap-paterno, #nctc-ap-materno, #nctc-telefono, #nctc-email, #nctc-curp').val('');
        $('#nctc-parentesco, #nctc-tipo').val('');
        $('#nctc-orden').val('1');
        $('#nctc-recoger, #nctc-pago, #nctc-portal').prop('checked', false);
    }

    function mostrarAlerta(msg, tipo) {
        $('#ctc-alerta-msg').text(msg);
        $('#ctc-alerta').removeClass('alert-success alert-danger alert-warning')
            .addClass('alert-' + tipo).show();
        if (tipo === 'success') setTimeout(function(){ $('#ctc-alerta').hide(); }, 4000);
    }

}); // fin $(function)
</script>
@endpush
