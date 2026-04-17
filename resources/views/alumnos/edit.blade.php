@extends('layouts.master')

@section('page_title', 'Editar alumno')
@section('page_subtitle', $alumno->nombre . ' ' . $alumno->ap_paterno . ' — ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li><a href="{{ route('alumnos.show', $alumno->id) }}">{{ $alumno->ap_paterno }}</a></li>
    <li class="active">Editar</li>
@endsection

@push('styles')
<style>
    /* ── Wizard (idéntico al create) ─────────────────── */
    .wizard-step-trigger.is-active {
        border-color: #3c8dbc;
        background: #f7fbfe;
        box-shadow: inset 0 0 0 1px rgba(60,141,188,.15);
    }
    .wizard-step-trigger.is-active .wizard-step-badge {
        background: #3c8dbc !important;
        color: #fff !important;
    }
    .wizard-step-trigger.is-complete {
        border-color: #00a65a;
    }
    .wizard-step-trigger.is-complete .wizard-step-badge {
        background: #00a65a !important;
        color: #fff !important;
    }
    .wizard-summary-item.is-active {
        color: #3c8dbc;
        font-weight: 700;
    }
    .wizard-summary-item.is-active .text-muted {
        color: #3c8dbc;
    }

    /* ── Inscripción actual (solo lectura) ─────────── */
    .ins-actual-card {
        background: #f0f7ff;
        border: 1px solid #b8d4ec;
        border-left: 4px solid #3c8dbc;
        border-radius: 6px;
        padding: 14px 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .ins-actual-badge {
        width: 46px; height: 46px; border-radius: 10px;
        background: #3c8dbc; display: flex; align-items: center;
        justify-content: center; flex-shrink: 0;
    }
    .ins-actual-titulo { font-size: 14px; font-weight: 700; color: #1e4d7b; }
    .ins-actual-sub    { font-size: 12px; color: #5b8db8; margin-top: 3px; }

    /* ── Foto preview ──────────────────────────────── */
    #foto-preview-wrap {
        width: 120px; height: 120px; border: 2px dashed #ccc; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; overflow: hidden; background: #fafafa;
        transition: border-color .2s;
    }
    #foto-preview-wrap:hover { border-color: #3c8dbc; }

    @media (max-width: 768px) {
        #wizard-steps-nav .col-sm-6,
        #wizard-steps-nav .col-lg-3 { width: 25%; float: left; padding: 4px; }
        .wizard-step-trigger {
            padding: 6px !important; min-height: auto !important;
            text-align: center !important; font-size: 11px;
        }
        .wizard-step-trigger span:last-child { display: none; }
        .wizard-step-badge { width: 26px !important; height: 26px !important; font-size: 12px; margin: 0 auto 4px; }
        .wizard-step-trigger { opacity: .5; }
        .wizard-step-trigger.is-active { opacity: 1; transform: scale(1.05); }
    }
</style>
@endpush

@section('content')

@php
    $pasosWizard = [
        1 => ['titulo' => 'Datos personales', 'descripcion' => 'Nombre, fechas y CURP'],
        2 => ['titulo' => 'Foto y estado',    'descripcion' => 'Foto, estado y baja'],
        3 => ['titulo' => 'Inscripción',       'descripcion' => 'Ciclo, nivel y grupo'],
        4 => ['titulo' => 'Contactos',         'descripcion' => 'Responsables y autorizados'],
    ];

    // Inscripción activa actual
    $inscActual = $inscripciones->where('estado', 'activo')->first()
                  ?? $inscripciones->sortByDesc('id')->first();

    // Valores precargados para el paso 3 (old() tiene prioridad)
    $cicloIdActual  = old('ciclo_id',  $inscActual?->grupo?->ciclo_id              ?? '');
    $nivelActual  = old('nivel_id',  $inscActual?->grupo?->grado?->nivel_id      ?? '');
    $grupoActual  = old('grupo_id',  $inscActual?->grupo_id                      ?? '');
@endphp

<form method="POST"
      action="{{ route('alumnos.update', $alumno->id) }}"
      enctype="multipart/form-data"
      id="form-editar-alumno"
      novalidate>
@csrf
@method('PUT')

{{-- ══════════════════════════════════
     BARRA DE PROGRESO + NAV WIZARD
══════════════════════════════════ --}}
<div class="box box-primary">
    <div class="box-body">
        <div style="background:#f4f4f4;border-radius:999px;height:8px;overflow:hidden;margin-bottom:18px;">
            <div id="wizard-progress-bar"
                 style="background:#3c8dbc;height:8px;width:25%;transition:width .2s ease;"></div>
        </div>

        <div class="row" id="wizard-steps-nav">
            @foreach($pasosWizard as $numero => $paso)
            <div class="col-sm-6 col-lg-3" style="margin-bottom:12px;">
                <button type="button"
                        class="btn btn-default btn-block text-left wizard-step-trigger"
                        data-step="{{ $numero }}"
                        onclick="wizardIr({{ $numero }}); return false;"
                        style="min-height:78px;white-space:normal;border-width:2px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <span class="wizard-step-badge"
                              style="display:inline-flex;align-items:center;justify-content:center;
                                     width:32px;height:32px;border-radius:50%;
                                     background:#f4f4f4;color:#3c8dbc;font-weight:700;flex-shrink:0;">
                            {{ $numero }}
                        </span>
                        <span>
                            <span style="display:block;font-weight:700;color:#222;">
                                Paso {{ $numero }}: {{ $paso['titulo'] }}
                            </span>
                            <span class="text-muted" style="display:block;font-size:12px;margin-top:4px;">
                                {{ $paso['descripcion'] }}
                            </span>
                        </span>
                    </div>
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row">

{{-- ══════════════════════════════════
     COLUMNA IZQUIERDA — pasos
══════════════════════════════════ --}}
<div class="col-md-8">

    {{-- ────────────── PASO 1: Datos personales ────────────── --}}
    <div class="box box-primary wizard-step-panel" data-step="1">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-user"></i> Paso 1: Datos personales
            </h3>
        </div>
        <div class="box-body">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                        <label for="nombre">Nombre(s) <span class="text-red">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control"
                               placeholder="Ej: Juan Carlos"
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
                    <div class="form-group {{ $errors->has('fecha_inscripcion') ? 'has-error' : '' }}">
                        <label for="fecha_inscripcion">Fecha de inscripción <span class="text-red">*</span></label>
                        <input type="date" name="fecha_inscripcion" id="fecha_inscripcion" class="form-control"
                               value="{{ old('fecha_inscripcion', $alumno->fecha_inscripcion?->format('Y-m-d')) }}">
                        @error('fecha_inscripcion')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group {{ $errors->has('curp') ? 'has-error' : '' }}">
                        <label for="curp">
                            CURP
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Matrícula</label>
                        <input type="text" class="form-control" value="{{ $alumno->matricula }}" disabled>
                        <span class="help-block" style="font-size:11px;">No se puede modificar.</span>
                    </div>
                </div>
            </div>

            <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}">
                <label for="observaciones">Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control"
                          rows="2" maxlength="1000"
                          placeholder="Notas adicionales sobre el alumno (opcional)">{{ old('observaciones', $alumno->observaciones) }}</textarea>
            </div>

        </div>{{-- /.box-body --}}
    </div>{{-- /paso 1 --}}

    {{-- ────────────── PASO 2: Foto y estado ────────────── --}}
    <div class="box box-primary wizard-step-panel" data-step="2" style="display:none;">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-camera"></i> Paso 2: Foto y estado
            </h3>
        </div>
        <div class="box-body">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                        <label>Foto del alumno</label>
                        <div style="margin-bottom:10px;">
                            <div id="foto-preview-wrap"
                                 onclick="document.getElementById('foto').click()">
                                @if($alumno->foto_url)
                                    <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                                         alt="Foto" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <div style="text-align:center;color:#ccc;">
                                        <i class="fa fa-camera" style="font-size:28px;"></i>
                                        <div style="font-size:11px;margin-top:4px;">Sin foto</div>
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
                                    {{ $alumno->foto_url ? 'Cambiar foto' : 'Seleccionar' }}
                                </label>
                            </span>
                            <input type="text" id="foto-nombre" class="form-control input-sm"
                                   placeholder="Sin cambios" readonly>
                        </div>
                        <span class="help-block" style="font-size:11px;">JPG, PNG o WEBP · Máx. 2 MB.</span>
                        @error('foto')
                            <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
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
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Información del registro</label>
                        <table class="table table-condensed" style="font-size:12px;margin:0;background:#fafafa;border:1px solid #f0f0f0;border-radius:4px;">
                            <tr>
                                <th style="color:#999;font-weight:400;padding:6px 10px;">Matrícula</th>
                                <td style="padding:6px 10px;"><code>{{ $alumno->matricula }}</code></td>
                            </tr>
                            <tr>
                                <th style="color:#999;font-weight:400;padding:6px 10px;">Familia</th>
                                <td style="padding:6px 10px;">{{ $alumno->familia?->apellido_familia ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th style="color:#999;font-weight:400;padding:6px 10px;">Registrado</th>
                                <td style="padding:6px 10px;font-size:11px;">{{ $alumno->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>{{-- /.box-body --}}
    </div>{{-- /paso 2 --}}

    {{-- ────────────── PASO 3: Inscripción ────────────── --}}
    <div class="box box-primary wizard-step-panel" data-step="3" style="display:none;">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-graduation-cap"></i> Paso 3: Inscripción
            </h3>
        </div>
        <div class="box-body">

            {{-- Tarjeta inscripción activa (informativa) --}}
            @if($inscActual)
            <div class="ins-actual-card">
                <div class="ins-actual-badge">
                    <i class="fa fa-graduation-cap" style="color:#fff;font-size:20px;"></i>
                </div>
                <div style="flex:1;">
                    <div class="ins-actual-titulo">Inscripción actual</div>
                    <div class="ins-actual-sub">
                        {{ $inscActual->grupo?->ciclo?->nombre ?? '—' }}
                        &nbsp;·&nbsp;
                        {{ $inscActual->grupo?->grado?->nivel?->nombre ?? '—' }}
                        &nbsp;·&nbsp;
                        {{ $inscActual->grupo?->grado?->nombre ?? '' }}
                        {{ $inscActual->grupo?->nombre ?? '—' }}
                    </div>
                </div>
                <span style="background:#3c8dbc;color:#fff;font-size:10px;padding:2px 10px;border-radius:10px;font-weight:600;">
                    {{ strtoupper($inscActual->estado ?? 'activo') }}
                </span>
            </div>
            @else
            <div class="alert alert-info" style="font-size:12px;">
                <i class="fa fa-info-circle"></i>
                Este alumno no tiene inscripción activa. Selecciona un ciclo, nivel y grupo para inscribirlo.
            </div>
            @endif

            {{-- Selectores editables --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                        <label for="ciclo_id">Ciclo escolar <span class="text-red">*</span></label>
                        <select name="ciclo_id" id="ciclo_id" class="form-control">
                            <option value="">-- Seleccionar ciclo --</option>
                            @foreach($ciclosDisponibles as $ciclo)
                            <option value="{{ $ciclo->id }}"
                                {{ $cicloIdActual == $ciclo->id ? 'selected' : '' }}>
                                {{ $ciclo->nombre }}
                                @if($ciclo->estado === 'activo') (Activo) @endif
                            </option>
                            @endforeach
                        </select>
                        @error('ciclo_id')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('nivel_id') ? 'has-error' : '' }}">
                        <label for="nivel_id">Nivel educativo <span class="text-red">*</span></label>
                        <select name="nivel_id" id="nivel_id" class="form-control">
                            <option value="">-- Seleccionar nivel --</option>
                            @foreach($niveles as $nivel)
                            <option value="{{ $nivel->id }}"
                                {{ $nivelActual == $nivel->id ? 'selected' : '' }}>
                                {{ $nivel->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('nivel_id')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('grupo_id') ? 'has-error' : '' }}">
                        <label for="grupo_id">Grado y grupo <span class="text-red">*</span></label>
                        <select name="grupo_id" id="grupo_id" class="form-control">
                            <option value="">-- Primero selecciona ciclo y nivel --</option>
                        </select>
                        @error('grupo_id')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div id="grupos-cargando" style="display:none;color:#999;font-size:12px;margin-top:-10px;">
                <i class="fa fa-spinner fa-spin"></i> Cargando grupos disponibles...
            </div>

        </div>{{-- /.box-body --}}
    </div>{{-- /paso 3 --}}

    {{-- ────────────── PASO 4: Contactos ────────────── --}}
    <div class="box box-primary wizard-step-panel" data-step="4" style="display:none;">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-phone"></i> Paso 4: Contactos familiares
            </h3>
        </div>
        <div class="box-body">

            <div id="ctc-alerta" style="display:none;" class="alert alert-dismissible">
                <button type="button" class="close"
                        onclick="this.parentElement.style.display='none'">&times;</button>
                <span id="ctc-alerta-msg"></span>
            </div>

            {{-- Contactos existentes --}}
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
                                    <option value="padre"              {{ $contacto->pivot->tipo === 'padre'              ? 'selected' : '' }}>Padre/Madre</option>
                                    <option value="tutor"              {{ $contacto->pivot->tipo === 'tutor'              ? 'selected' : '' }}>Tutor</option>
                                    <option value="tercero_autorizado" {{ $contacto->pivot->tipo === 'tercero_autorizado' ? 'selected' : '' }}>Tercero autorizado</option>
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

            {{-- Formulario nuevo contacto --}}
            <div class="panel panel-success" style="margin-top:16px;">
                <div class="panel-heading" style="padding:8px 12px;cursor:pointer;background:#00a65a;"
                     onclick="(function(){
                         var f=document.getElementById('form-nuevo-ctc');
                         var i=document.getElementById('ico-toggle-ctc');
                         var v=f.style.display!=='none';
                         f.style.display=v?'none':'block';
                         i.className=v?'fa fa-chevron-down':'fa fa-chevron-up';
                     })()">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <strong style="font-size:13px;color:#fff;">
                            <i class="fa fa-plus-circle"></i> Agregar nuevo contacto
                        </strong>
                        <i class="fa fa-chevron-down" id="ico-toggle-ctc" style="color:#fff;"></i>
                    </div>
                </div>
                <div class="panel-body" id="form-nuevo-ctc" style="display:none;padding:12px;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>
                                <input type="text" id="nctc-nombre" class="form-control input-sm" maxlength="100" placeholder="Nombre(s)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Apellido paterno</label>
                                <input type="text" id="nctc-ap-paterno" class="form-control input-sm" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Apellido materno</label>
                                <input type="text" id="nctc-ap-materno" class="form-control input-sm" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Teléfono celular <span class="text-red">*</span></label>
                                <input type="tel" id="nctc-telefono" class="form-control input-sm" maxlength="10" placeholder="10 dígitos">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Correo electrónico</label>
                                <input type="email" id="nctc-email" class="form-control input-sm" maxlength="200">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">CURP</label>
                                <input type="text" id="nctc-curp" class="form-control input-sm" maxlength="18" style="text-transform:uppercase">
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
                        <button type="button" class="btn btn-default btn-sm" id="btn-cancelar-ctc">Cancelar</button>
                        <button type="button" class="btn btn-success btn-sm" id="btn-guardar-ctc">
                            <i class="fa fa-plus"></i> Agregar contacto
                        </button>
                    </div>
                </div>
            </div>

        </div>{{-- /.box-body --}}
    </div>{{-- /paso 4 --}}

</div>{{-- /.col-md-8 --}}

{{-- ══════════════════════════════════
     COLUMNA DERECHA — nav + acciones
══════════════════════════════════ --}}
<div class="col-md-4">

    {{-- Navegación del wizard --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-list-ol"></i> Progreso de edición
            </h3>
        </div>
        <div class="box-body">
            <p class="text-muted" id="wizard-step-description" style="margin-bottom:15px;">
                Paso 1 de 4: edita los datos personales del alumno.
            </p>

            <ul class="list-unstyled" style="margin:0 0 15px;">
                @foreach($pasosWizard as $numero => $paso)
                <li class="wizard-summary-item" data-step="{{ $numero }}"
                    style="padding:8px 0;border-bottom:1px solid #f0f0f0;">
                    <strong>Paso {{ $numero }}</strong><br>
                    <span class="text-muted">{{ $paso['titulo'] }}</span>
                </li>
                @endforeach
            </ul>

            <button type="button" class="btn btn-default btn-block" id="btn-paso-anterior"
                    onclick="wizardIr(wizardPasoActual() - 1); return false;"
                    style="display:none;">
                <i class="fa fa-arrow-left"></i> Anterior
            </button>
            <button type="button" class="btn btn-primary btn-block" id="btn-paso-siguiente"
                    onclick="wizardIr(wizardPasoActual() + 1); return false;">
                Siguiente <i class="fa fa-arrow-right"></i>
            </button>
            <button type="submit" class="btn btn-success btn-block btn-lg" id="btn-guardar">
                <i class="fa fa-save"></i> Guardar cambios
            </button>
            <a href="{{ route('alumnos.show', $alumno->id) }}"
               class="btn btn-default btn-block" style="margin-top:4px;">
                <i class="fa fa-times"></i> Cancelar
            </a>
        </div>
    </div>

    {{-- Errores de validación --}}
    @if($errors->any())
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-exclamation-triangle"></i> Corrige los errores
            </h3>
        </div>
        <div class="box-body">
            <ul style="padding-left:18px;margin:0;">
                @foreach($errors->all() as $error)
                <li style="color:#a94442;font-size:12px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Resumen alumno --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size:13px;">
                <i class="fa fa-info-circle"></i> Resumen
            </h3>
        </div>
        <div class="box-body no-padding">
            <table class="table" style="font-size:12px;margin:0;">
                <tr>
                    <th style="color:#999;font-weight:400;padding:8px 14px;width:45%;">Matrícula</th>
                    <td style="padding:8px 14px;"><code>{{ $alumno->matricula }}</code></td>
                </tr>
                <tr>
                    <th style="color:#999;font-weight:400;padding:8px 14px;">Familia</th>
                    <td style="padding:8px 14px;">{{ $alumno->familia?->apellido_familia ?? '—' }}</td>
                </tr>
                @if($inscActual)
                <tr>
                    <th style="color:#999;font-weight:400;padding:8px 14px;">Ciclo</th>
                    <td style="padding:8px 14px;">{{ $inscActual->grupo?->cicloEscolar?->nombre ?? '—' }}</td>
                </tr>
                <tr>
                    <th style="color:#999;font-weight:400;padding:8px 14px;">Grupo actual</th>
                    <td style="padding:8px 14px;font-weight:600;">
                        {{ $inscActual->grupo?->grado?->nombre ?? '' }}°
                        {{ $inscActual->grupo?->nombre ?? '—' }}
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

</div>{{-- /.col-md-4 --}}

</div>{{-- /.row --}}
</form>

@endsection

@push('scripts')
<script>
$(function() {

    // ══════════════════════════════════════════════════
    // WIZARD
    // ══════════════════════════════════════════════════
    var TOTAL_PASOS = 4;
    var pasoActual  = 1;

    var PASOS_DESC = {
        1: 'Paso 1 de 4: edita los datos personales del alumno.',
        2: 'Paso 2 de 4: actualiza la foto y el estado.',
        3: 'Paso 3 de 4: cambia el ciclo, nivel y grupo.',
        4: 'Paso 4 de 4: administra los contactos familiares.',
    };

    window.wizardIr = function(paso, validar) {
        if (typeof validar === 'undefined') validar = true;
        if (validar && paso > pasoActual && !validarPaso(pasoActual)) {
            alert('Corrige los campos requeridos del paso ' + pasoActual + ' antes de continuar.');
            return;
        }

        pasoActual = Math.min(Math.max(paso, 1), TOTAL_PASOS);

        $('.wizard-step-panel').hide();
        $('.wizard-step-panel[data-step="' + pasoActual + '"]').show();

        $('.wizard-step-trigger').each(function() {
            var s = Number($(this).data('step'));
            $(this)
                .toggleClass('is-active',   s === pasoActual)
                .toggleClass('is-complete', s < pasoActual);
        });

        $('.wizard-summary-item').each(function() {
            $(this).toggleClass('is-active', Number($(this).data('step')) === pasoActual);
        });

        $('#wizard-progress-bar').css('width', (pasoActual / TOTAL_PASOS * 100) + '%');
        $('#wizard-step-description').text(PASOS_DESC[pasoActual]);
        $('#btn-paso-anterior').toggle(pasoActual > 1);
        $('#btn-paso-siguiente').toggle(pasoActual < TOTAL_PASOS);

        // Al entrar al paso 3 cargar grupos si ya hay ciclo+nivel
        // Solo preselecciona GRUPO_ACTUAL la primera vez (grupos aún vacíos)
        if (pasoActual === 3) {
            var ci = $('#ciclo_id').val();
            var ni = $('#nivel_id').val();
            var gruposYaCargados = $('#grupo_id option').length > 1;
            if (ci && ni && !gruposYaCargados) {
                cargarGrupos(ci, ni, GRUPO_ACTUAL);
            }
        }

        $('html,body').animate({ scrollTop: $('#wizard-steps-nav').offset().top - 80 }, 200);
    };

    window.wizardPasoActual = function() { return pasoActual; };

    // Detectar paso con error de validación del servidor
    function pasoConError() {
        var $e = $('.has-error').first();
        if (!$e.length) return null;
        var $panel = $e.closest('.wizard-step-panel');
        return $panel.length ? Number($panel.data('step')) : null;
    }

    // ══════════════════════════════════════════════════
    // PASO 3 — valores actuales inyectados desde PHP
    // (deben estar antes de wizardIr para que estén
    //  disponibles si hay error de validación en paso 3)
    // ══════════════════════════════════════════════════
    var CICLO_ACTUAL = '{{ $cicloIdActual }}';
    var NIVEL_ACTUAL = '{{ $nivelActual }}';
    var GRUPO_ACTUAL = '{{ $grupoActual }}';

    // Iniciar en el paso con error o en el 1
    wizardIr(pasoConError() || 1, false);

    // ══════════════════════════════════════════════════
    // VALIDACIÓN POR PASO
    // ══════════════════════════════════════════════════
    function marcarError(sel, msg) {
        var $g = $(sel).closest('.form-group');
        $g.addClass('has-error').removeClass('has-success');
        if (!$g.find('.help-block.val-msg').length) $g.append('<span class="help-block val-msg"></span>');
        $g.find('.help-block.val-msg').html('<i class="fa fa-exclamation-circle"></i> ' + msg).show();
    }

    function marcarOk(sel) {
        var $g = $(sel).closest('.form-group');
        $g.removeClass('has-error').addClass('has-success');
        $g.find('.help-block.val-msg').hide();
    }

    function validarPaso(paso) {
        var ok = true;

        if (paso === 1) {
            if (!$('#nombre').val().trim())        { marcarError('#nombre',          'El nombre es obligatorio.');           ok = false; } else marcarOk('#nombre');
            if (!$('#ap_paterno').val().trim())    { marcarError('#ap_paterno',      'El apellido paterno es obligatorio.'); ok = false; } else marcarOk('#ap_paterno');
            if (!$('#fecha_nacimiento').val())     { marcarError('#fecha_nacimiento','La fecha de nacimiento es obligatoria.'); ok = false; } else marcarOk('#fecha_nacimiento');
            if (!$('#fecha_inscripcion').val())    { marcarError('#fecha_inscripcion','La fecha de inscripción es obligatoria.'); ok = false; } else marcarOk('#fecha_inscripcion');
        }

        if (paso === 2) {
            if (!$('#estado').val()) { marcarError('#estado', 'El estado es obligatorio.'); ok = false; } else marcarOk('#estado');
        }

        if (paso === 3) {
            if (!$('#ciclo_id').val())  { marcarError('#ciclo_id',  'Selecciona el ciclo escolar.'); ok = false; } else marcarOk('#ciclo_id');
            if (!$('#nivel_id').val())  { marcarError('#nivel_id',  'Selecciona el nivel.');         ok = false; } else marcarOk('#nivel_id');
            if (!$('#grupo_id').val())  { marcarError('#grupo_id',  'Selecciona el grupo.');         ok = false; } else marcarOk('#grupo_id');
        }

        return ok;
    }

    // ══════════════════════════════════════════════════
    // PASO 3 — CARGA DINÁMICA DE GRUPOS
    // ══════════════════════════════════════════════════

    // Cuando cambia ciclo o nivel → recargar grupos
    $('#ciclo_id, #nivel_id').on('change', function() {
        var ci = $('#ciclo_id').val();
        var ni = $('#nivel_id').val();
        if (ci && ni) {
            cargarGrupos(ci, ni, null); // null → no pre-seleccionar al cambiar manualmente
        } else {
            $('#grupo_id').html('<option value="">-- Primero selecciona ciclo y nivel --</option>');
        }
    });

    function cargarGrupos(cicloId, nivelId, preseleccionar) {
        $('#grupos-cargando').show();
        $('#grupo_id').prop('disabled', true)
                      .html('<option value="">Cargando...</option>');

        $.ajax({
            url: '/grupos',
            method: 'GET',
            data: { ciclo_id: cicloId, nivel_id: nivelId },
            success: function(grupos) {
                var opciones = '';

                if (!grupos.length) {
                    opciones = '<option value="">Sin grupos para esta selección</option>';
                } else {
                    opciones = '<option value="">-- Seleccionar grupo --</option>';
                    grupos.forEach(function(g) {
                        var capacidad = g.cupo_maximo
                            ? g.alumnos_inscritos + '/' + g.cupo_maximo
                            : g.alumnos_inscritos + ' inscritos';
                        var lleno = (g.cupo_maximo && g.alumnos_inscritos >= g.cupo_maximo) ? ' [LLENO]' : '';
                        var sel   = (preseleccionar && g.id == preseleccionar) ? ' selected' : '';

                        // Construir etiqueta: "Grado Nombre (capacidad)"
                        var gradoNombre = (g.grado && g.grado.nombre) ? g.grado.nombre + '° ' : '';
                        opciones += '<option value="' + g.id + '"' + sel + '>'
                                  + gradoNombre + g.nombre
                                  + ' (' + capacidad + ')' + lleno
                                  + '</option>';
                    });
                }

                $('#grupo_id').prop('disabled', false).html(opciones);
                $('#grupos-cargando').hide();
            },
            error: function() {
                $('#grupo_id').prop('disabled', false)
                              .html('<option value="">Error al cargar grupos</option>');
                $('#grupos-cargando').hide();
            }
        });
    }

    // ══════════════════════════════════════════════════
    // CURP
    // ══════════════════════════════════════════════════
    $('#curp').on('input', function() {
        this.value = this.value.toUpperCase();
        var len = this.value.length;
        $('#curp-chars').text(len);
        $('#curp-lbl').css('color', len === 18 ? '#00a65a' : len > 0 ? '#f39c12' : '#999');
    });

    // ══════════════════════════════════════════════════
    // ESTADO → FECHA BAJA
    // ══════════════════════════════════════════════════
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

    // ══════════════════════════════════════════════════
    // FOTO PREVIEW
    // ══════════════════════════════════════════════════
    var MAX_FOTO = 2 * 1024 * 1024;
    $('#foto').on('change', function() {
        var f = this.files[0];
        if (!f) return;
        if (f.size > MAX_FOTO) {
            this.value = '';
            $('#foto-nombre').val('');
            alert('El archivo supera 2 MB.');
            return;
        }
        $('#foto-nombre').val(f.name);
        var r = new FileReader();
        r.onload = function(e) {
            $('#foto-preview-wrap')
                .css('border', '2px solid #00a65a')
                .html('<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">');
        };
        r.readAsDataURL(f);
    });

    // ══════════════════════════════════════════════════
    // SUBMIT — deshabilitar botón
    // ══════════════════════════════════════════════════
    $('#form-editar-alumno').on('submit', function() {
        $('#btn-guardar').prop('disabled', true)
            .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
    });

    // ══════════════════════════════════════════════════
    // CONTACTOS — GUARDAR EXISTENTE (AJAX)
    // ══════════════════════════════════════════════════
    $(document).on('click', '.btn-ctc-guardar', function() {
        var $panel = $(this).closest('.ctc-panel');
        var id     = $panel.data('id');
        var $btn   = $(this);
        var orig   = $btn.html();

        var datos = {
            nombre:              $panel.find('.ctc-nombre').val().trim(),
            ap_paterno:          $panel.find('.ctc-ap-paterno').val().trim(),
            ap_materno:          $panel.find('.ctc-ap-materno').val().trim(),
            telefono_celular:    $panel.find('.ctc-telefono').val().trim(),
            email:               $panel.find('.ctc-email').val().trim(),
            parentesco:          $panel.find('.ctc-parentesco').val(),
            tipo:                $panel.find('.ctc-tipo').val(),
            orden:               parseInt($panel.find('.ctc-orden').val()),
            autorizado_recoger:  $panel.find('.ctc-recoger').is(':checked'),
            es_responsable_pago: $panel.find('.ctc-pago').is(':checked'),
            tiene_acceso_portal: $panel.find('.ctc-portal').is(':checked'),
        };

        if (!datos.nombre)           { alertaCtc('El nombre es obligatorio.', 'danger');   return; }
        if (!datos.telefono_celular) { alertaCtc('El teléfono es obligatorio.', 'danger'); return; }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/familias/contactos/' + id,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function(res) {
                $panel.find('.ctc-titulo').text(datos.nombre + ' ' + datos.ap_paterno);
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Guardado');
                alertaCtc(res.message || 'Contacto guardado.', 'success');
                setTimeout(function() { $btn.html(orig); }, 2500);
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(orig);
                alertaCtc(xhr.responseJSON?.message || 'Error al guardar.', 'danger');
            }
        });
    });

    // ══════════════════════════════════════════════════
    // CONTACTOS — ELIMINAR (AJAX)
    // ══════════════════════════════════════════════════
    $(document).on('click', '.btn-ctc-eliminar', function() {
        var $panel = $(this).closest('.ctc-panel');
        var id     = $panel.data('id');
        var nombre = $panel.find('.ctc-titulo').text().trim();

        if ($('.ctc-panel').length <= 1) {
            alertaCtc('Debe haber al menos un contacto familiar.', 'danger');
            return;
        }
        if (!confirm('¿Eliminar el contacto "' + nombre + '"?')) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/familias/contactos/' + id,
            method: 'DELETE',
            success: function(res) {
                $panel.fadeOut(300, function() { $(this).remove(); });
                alertaCtc(res.message || 'Contacto eliminado.', 'success');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                alertaCtc(xhr.responseJSON?.message || 'Error al eliminar.', 'danger');
            }
        });
    });

    // ══════════════════════════════════════════════════
    // CONTACTOS — AGREGAR NUEVO (AJAX)
    // ══════════════════════════════════════════════════
    var ALUMNO_ID = {{ $alumno->id }};
    var FAMILIA_ID = {{ $alumno->familia_id ?? 'null' }};

    $('#btn-cancelar-ctc').on('click', function() {
        limpiarCtc();
        $('#form-nuevo-ctc').hide();
        $('#ico-toggle-ctc').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    $('#btn-guardar-ctc').on('click', function() {
        var $btn = $(this);

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

        if (!datos.nombre)           { alertaCtc('El nombre es obligatorio.', 'danger');      $('#nctc-nombre').focus();     return; }
        if (!datos.telefono_celular) { alertaCtc('El teléfono es obligatorio.', 'danger');    $('#nctc-telefono').focus();   return; }
        if (!datos.parentesco)       { alertaCtc('El parentesco es obligatorio.', 'danger');  $('#nctc-parentesco').focus(); return; }
        if (!datos.tipo)             { alertaCtc('El tipo es obligatorio.', 'danger');        $('#nctc-tipo').focus();       return; }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '/familias/contactos',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');

                var c = res.contacto, piv = res.pivot;

                // Opciones selects
                var pOpts = [['padre','Padre'],['madre','Madre'],['abuelo','Abuelo/a'],['tio','Tío/a'],['otro','Otro']]
                    .map(function(p){ return '<option value="'+p[0]+'"'+(datos.parentesco===p[0]?' selected':'')+'>'+p[1]+'</option>'; }).join('');
                var tOpts =
                    '<option value="padre"'+(datos.tipo==='padre'?' selected':'')+'>Padre/Madre</option>'+
                    '<option value="tutor"'+(datos.tipo==='tutor'?' selected':'')+'>Tutor</option>'+
                    '<option value="tercero_autorizado"'+(datos.tipo==='tercero_autorizado'?' selected':'')+'>Tercero autorizado</option>';
                var oOpts =
                    '<option value="1"'+(datos.orden===1?' selected':'')+'>1 — Principal</option>'+
                    '<option value="2"'+(datos.orden===2?' selected':'')+'>2 — Secundario</option>'+
                    '<option value="3"'+(datos.orden===3?' selected':'')+'>3 — Tercero</option>';

                var html =
                  '<div class="panel panel-default ctc-panel" style="margin-bottom:10px;" data-id="'+c.id+'">' +
                    '<div class="panel-heading" style="padding:8px 12px;background:#f5f5f5;">' +
                      '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<strong style="font-size:13px;"><span class="ctc-titulo">'+datos.nombre+' '+datos.ap_paterno+'</span></strong>' +
                        '<div>'+
                          '<button type="button" class="btn btn-success btn-xs btn-ctc-guardar"><i class="fa fa-save"></i> Guardar</button>'+
                          '<button type="button" class="btn btn-danger btn-xs btn-ctc-eliminar" style="margin-left:4px;"><i class="fa fa-trash"></i></button>'+
                        '</div>' +
                      '</div>' +
                    '</div>' +
                    '<div class="panel-body" style="padding:12px;">' +
                      '<div class="row">' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label><input type="text" class="form-control input-sm ctc-nombre" value="'+datos.nombre+'" maxlength="100"></div></div>' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Apellido paterno</label><input type="text" class="form-control input-sm ctc-ap-paterno" value="'+(datos.ap_paterno||'')+'" maxlength="100"></div></div>' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Apellido materno</label><input type="text" class="form-control input-sm ctc-ap-materno" value="'+(datos.ap_materno||'')+'" maxlength="100"></div></div>' +
                      '</div>' +
                      '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Teléfono</label><input type="tel" class="form-control input-sm ctc-telefono" value="'+(datos.telefono_celular||'')+'"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Correo</label><input type="email" class="form-control input-sm ctc-email" value="'+(datos.email||'')+'"></div></div>' +
                        '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Parentesco</label><select class="form-control input-sm ctc-parentesco">'+pOpts+'</select></div></div>' +
                        '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Tipo</label><select class="form-control input-sm ctc-tipo">'+tOpts+'</select></div></div>' +
                        '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Orden</label><select class="form-control input-sm ctc-orden">'+oOpts+'</select></div></div>' +
                      '</div>' +
                      '<div class="row"><div class="col-md-12">'+
                        '<label class="checkbox-inline"><input type="checkbox" class="ctc-recoger"'+(piv.autorizado_recoger?' checked':'')+'>  Autorizado recoger</label>'+
                        '<label class="checkbox-inline" style="margin-left:12px;"><input type="checkbox" class="ctc-pago"'+(piv.es_responsable_pago?' checked':'')+'>  Resp. pagos</label>'+
                        '<label class="checkbox-inline" style="margin-left:12px;"><input type="checkbox" class="ctc-portal"'+(c.tiene_acceso_portal?' checked':'')+'>  Portal</label>'+
                      '</div></div>' +
                    '</div>' +
                  '</div>';

                $('.panel.panel-success').before(html);
                $('.alert.alert-warning').remove();
                limpiarCtc();
                $('#form-nuevo-ctc').hide();
                $('#ico-toggle-ctc').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                alertaCtc(res.message || 'Contacto agregado.', 'success');
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');
                var msg = xhr.responseJSON?.message || 'Error al agregar el contacto.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                alertaCtc(msg, 'danger');
            }
        });
    });

    // ══════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════
    function limpiarCtc() {
        $('#nctc-nombre,#nctc-ap-paterno,#nctc-ap-materno,#nctc-telefono,#nctc-email,#nctc-curp').val('');
        $('#nctc-parentesco,#nctc-tipo').val('');
        $('#nctc-orden').val('1');
        $('#nctc-recoger,#nctc-pago,#nctc-portal').prop('checked', false);
    }

    function alertaCtc(msg, tipo) {
        $('#ctc-alerta-msg').text(msg);
        $('#ctc-alerta').removeClass('alert-success alert-danger alert-warning')
                        .addClass('alert-' + tipo).show();
        if (tipo === 'success') setTimeout(function() { $('#ctc-alerta').hide(); }, 4000);
    }

});
</script>
@endpush
