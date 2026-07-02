@extends('layouts.master')

@section('page_title', 'Editar alumno')
@section('page_subtitle', $alumno->nombre . ' ' . $alumno->ap_paterno . ' — ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li><a href="{{ route('alumnos.show', $alumno->id) }}">{{ $alumno->ap_paterno }}</a></li>
    <li class="active">Editar</li>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single { height:34px !important; border:1px solid #d2d6de !important; border-radius:4px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:32px !important; padding-left:12px !important; color:#555 !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:32px !important; }
    </style>
<style>
/* ══ Wizard nav ═══════════════════════════════════════════ */
.wizard-step-trigger {
    background: #fff; border: 1px solid #e0e7ef !important;
    border-top: 3px solid #d0dbe6 !important; border-radius: 6px !important;
    padding: 14px 16px !important; min-height: 78px;
    display: flex; align-items: center; gap: 12px;
    text-align: left; white-space: normal;
    transition: border-color .15s, box-shadow .15s;
    box-shadow: 0 1px 4px rgba(0,0,0,.04); width: 100%;
}
.wizard-step-trigger:hover { border-color: #b0c8e0 !important; box-shadow: 0 2px 8px rgba(60,141,188,.10); }
.wizard-step-trigger.is-active { border-top-color: #3c8dbc !important; border-color: #c8dff0 !important; background: #f7fbff !important; box-shadow: 0 2px 10px rgba(60,141,188,.12); }
.wizard-step-trigger.is-active .wizard-step-badge { background: #3c8dbc !important; color: #fff !important; }
.wizard-step-trigger.is-complete { border-top-color: #00a65a !important; border-color: #c3e6cb !important; }
.wizard-step-trigger.is-complete .wizard-step-badge { background: #00a65a !important; color: #fff !important; }
.wizard-step-badge {
    width: 34px; height: 34px; border-radius: 50%; background: #f0f3f7; color: #3c8dbc;
    font-weight: 700; font-size: 14px; display: inline-flex; align-items: center;
    justify-content: center; flex-shrink: 0; transition: background .15s, color .15s;
}
.wizard-step-label { font-size: 13px; font-weight: 700; color: #1a2634; line-height: 1.2; display: block; }
.wizard-step-desc  { font-size: 11px; color: #8a9ab0; margin-top: 3px; text-transform: uppercase; letter-spacing: .04em; display: block; }
/* ══ Barra de progreso ════════════════════════════════════ */
.wizard-progress-wrap { background: #edf1f5; border-radius: 999px; height: 6px; overflow: hidden; margin-bottom: 20px; }
.wizard-progress-fill { background: linear-gradient(90deg,#3c8dbc,#2c6fad); height: 6px; transition: width .25s ease; }
/* ══ Sidebar ══════════════════════════════════════════════ */
.sidebar-panel { border-radius: 8px; border: 1px solid #e0e7ef; box-shadow: 0 2px 10px rgba(0,0,0,.05); overflow: hidden; background: #fff; margin-bottom: 16px; }
.sidebar-header { background: linear-gradient(135deg,#1e4d7b 0%,#3c8dbc 100%); padding: 14px 18px; color: #fff; font-size: 13px; font-weight: 700; }
.sidebar-body { padding: 16px 18px; }
.wizard-summary-item { padding: 9px 0; border-bottom: 1px solid #f0f3f7; font-size: 13px; color: #4a5568; display: flex; align-items: center; gap: 10px; }
.wizard-summary-item:last-child { border-bottom: none; }
.wizard-summary-item.is-active { color: #3c8dbc; font-weight: 700; }
.wizard-summary-item.is-active .step-dot { background: #3c8dbc; }
.step-dot { width: 22px; height: 22px; border-radius: 50%; background: #e8ecf0; color: #fff; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
/* ══ Inscripción actual (solo lectura) ════════════════════ */
.ins-actual-card {
    background: #f0f7ff; border: 1px solid #b8d4ec; border-left: 4px solid #3c8dbc;
    border-radius: 6px; padding: 14px 16px; margin-bottom: 18px;
    display: flex; align-items: center; gap: 14px;
}
.ins-actual-badge { width: 46px; height: 46px; border-radius: 10px; background: #3c8dbc; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ins-actual-titulo { font-size: 14px; font-weight: 700; color: #1e4d7b; }
.ins-actual-sub    { font-size: 12px; color: #5b8db8; margin-top: 3px; }
/* ══ Foto preview ═════════════════════════════════════════ */
#foto-preview-wrap {
    width: 120px; height: 120px; border: 2px dashed #ccc; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; overflow: hidden; background: #fafafa; transition: border-color .2s;
}
#foto-preview-wrap:hover { border-color: #3c8dbc; }
/* ══ Responsive ═══════════════════════════════════════════ */
@media (max-width: 768px) {
    #wizard-steps-nav .col-sm-6, #wizard-steps-nav .col-lg-3 { width: 25%; float: left; padding: 4px; }
    .wizard-step-trigger { padding: 8px !important; min-height: auto !important; }
    .wizard-step-desc { display: none !important; }
    .wizard-step-badge { width: 28px !important; height: 28px !important; font-size: 12px; }
    .wizard-step-trigger.is-active { transform: scale(1.04); }
}
</style>
@endpush

@php
    $pasosWizard = [
        1 => ['titulo' => 'Datos personales', 'descripcion' => 'Nombre, fechas y CURP'],
        2 => ['titulo' => 'Foto y estado',    'descripcion' => 'Foto, estado y baja'],
        3 => ['titulo' => 'Inscripción',       'descripcion' => 'Ciclo, nivel y grupo'],
        4 => ['titulo' => 'Contactos',         'descripcion' => 'Responsables y autorizados'],
    ];

    // Inscripción regular activa actual (tipo=regular, activo=true, prioriza ciclo activo)
    $inscActual = $inscripciones
        ->where('activo', true)
        ->filter(fn($i) => $i->tipo?->value === 'regular')
        ->sortByDesc('id')
        ->first()
        ?? $inscripciones->where('activo', true)->sortByDesc('id')->first();

    // Valores precargados para el paso 3 (old() tiene prioridad)
    $cicloIdActual = old('ciclo_id', $inscActual?->ciclo_id ?? $inscActual?->grupo?->ciclo_id ?? '');
    $nivelActual   = old('nivel_id', $inscActual?->grupo?->grado?->nivel_id   ?? '');
    $grupoActual   = old('grupo_id', $inscActual?->grupo_id                   ?? '');
@endphp

@section('content')

<form method="POST"
      action="{{ route('alumnos.update', $alumno->id) }}"
      enctype="multipart/form-data"
      id="form-editar-alumno"
      novalidate>
@csrf
@method('PUT')
<input type="hidden" name="familia_id" id="input-familia-id" value="{{ $alumno->familia_id }}">

{{-- ══ BARRA DE PROGRESO + NAV WIZARD ══ --}}
<div class="wizard-progress-wrap">
    <div id="wizard-progress-bar" class="wizard-progress-fill" style="width:25%;"></div>
</div>

<div class="row" id="wizard-steps-nav" style="margin-bottom:20px;">
    @foreach($pasosWizard as $numero => $paso)
    <div class="col-sm-6 col-lg-3" style="margin-bottom:10px;">
        <button type="button" class="wizard-step-trigger"
                data-step="{{ $numero }}"
                onclick="wizardIr({{ $numero }}); return false;">
            <span class="wizard-step-badge">{{ $numero }}</span>
            <span>
                <span class="wizard-step-label">Paso {{ $numero }}: {{ $paso['titulo'] }}</span>
                <span class="wizard-step-desc">{{ $paso['descripcion'] }}</span>
            </span>
        </button>
    </div>
    @endforeach
</div>

<div class="row">

    {{-- ══ COLUMNA IZQUIERDA — pasos ══ --}}
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
                                placeholder="Ej: Juan Carlos" value="{{ old('nombre', $alumno->nombre) }}"
                                maxlength="100">
                            @error('nombre')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
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
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
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
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="genero">Género</label>
                            <select name="genero" id="genero" class="form-control">
                                <option value="">-- Seleccionar --</option>
                                <option value="M" {{ old('genero', $alumno->genero) === 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('genero', $alumno->genero) === 'F' ? 'selected' : '' }}>Femenino</option>
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
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
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
                                placeholder="18 caracteres" value="{{ old('curp', $alumno->curp) }}"
                                maxlength="18" style="text-transform:uppercase">
                            @error('curp')
                                <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
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

                {{-- Domicilio --}}
                <hr style="margin:10px 0 12px;">
                <p style="font-size:11px;font-weight:700;color:#6b7a8d;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">
                    <i class="fa fa-map-marker" style="color:#3c8dbc;"></i> Domicilio
                    <span style="font-weight:400;color:#b0bec5;text-transform:none;font-size:10px;"> — Opcional</span>
                </p>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group {{ $errors->has('calle') ? 'has-error' : '' }}">
                            <label for="calle">Calle y número</label>
                            <input type="text" name="calle" id="calle" class="form-control"
                                placeholder="Ej: Av. Reforma 123 Int. 4"
                                value="{{ old('calle', $alumno->calle) }}" maxlength="200">
                            @error('calle') <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('colonia') ? 'has-error' : '' }}">
                            <label for="colonia">Colonia</label>
                            <input type="text" name="colonia" id="colonia" class="form-control"
                                placeholder="Colonia" value="{{ old('colonia', $alumno->colonia) }}" maxlength="200">
                            @error('colonia') <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('codigo_postal') ? 'has-error' : '' }}">
                            <label for="codigo_postal">C.P.</label>
                            <input type="text" name="codigo_postal" id="codigo_postal" class="form-control"
                                placeholder="00000" value="{{ old('codigo_postal', $alumno->codigo_postal) }}" maxlength="10">
                            @error('codigo_postal') <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('ciudad') ? 'has-error' : '' }}">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" name="ciudad" id="ciudad" class="form-control"
                                placeholder="Ciudad" value="{{ old('ciudad', $alumno->ciudad) }}" maxlength="100">
                            @error('ciudad') <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group {{ $errors->has('estado_residencia') ? 'has-error' : '' }}">
                            <label for="estado_residencia">Estado</label>
                            <input type="text" name="estado_residencia" id="estado_residencia" class="form-control"
                                placeholder="Estado" value="{{ old('estado_residencia', $alumno->estado_residencia) }}" maxlength="100">
                            @error('estado_residencia') <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group {{ $errors->has('religion') ? 'has-error' : '' }}">
                            <label for="religion">Religión</label>
                            <input type="text" name="religion" id="religion" class="form-control"
                                placeholder="Ej: Católica" value="{{ old('religion', $alumno->religion) }}" maxlength="100">
                            @error('religion') <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>
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
                                <div id="foto-preview-wrap" onclick="document.getElementById('foto').click()">
                                    @if($alumno->foto_url)
                                        <img src="{{ asset('storage/' . $alumno->foto_url) }}" alt="Foto"
                                            style="width:100%;height:100%;object-fit:cover;">
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
                                    <label class="btn btn-default btn-sm btn-flat" for="foto"
                                        style="margin:0;cursor:pointer;">
                                        <i class="fa fa-camera"></i>
                                        {{ $alumno->foto_url ? 'Cambiar foto' : 'Seleccionar' }}
                                    </label>
                                </span>
                                <input type="text" id="foto-nombre" class="form-control input-sm"
                                    placeholder="Sin cambios" readonly>
                            </div>
                            <span class="help-block" style="font-size:11px;">JPG, PNG o WEBP · Máx. 2 MB.</span>
                            @error('foto')
                                <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('estado') ? 'has-error' : '' }}">
                            <label for="estado">Estado <span class="text-red">*</span></label>
                            <select name="estado" id="estado" class="form-control">
                                <option value="activo" {{ old('estado', $alumno->estado) === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="baja_temporal" {{ old('estado', $alumno->estado) === 'baja_temporal' ? 'selected' : '' }}>Baja temporal</option>
                                <option value="baja_definitiva" {{ old('estado', $alumno->estado) === 'baja_definitiva' ? 'selected' : '' }}>Baja definitiva</option>
                                <option value="egresado" {{ old('estado', $alumno->estado) === 'egresado' ? 'selected' : '' }}>Egresado</option>
                            </select>
                            @error('estado')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group {{ $errors->has('fecha_baja') ? 'has-error' : '' }}"
                            id="bloque-fecha-baja"
                            style="{{ in_array(old('estado', $alumno->estado), ['baja_temporal', 'baja_definitiva']) ? '' : 'display:none;' }}">
                            <label for="fecha_baja">Fecha de baja</label>
                            <input type="date" name="fecha_baja" id="fecha_baja" class="form-control"
                                value="{{ old('fecha_baja', $alumno->fecha_baja?->format('Y-m-d')) }}">
                            @error('fecha_baja')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}"
                            id="bloque-observaciones"
                            style="{{ (old('estado', $alumno->estado) !== $alumno->estado || old('observaciones', $alumno->observaciones)) ? '' : 'display:none;' }}">
                            <label for="observaciones">
                                <i class="fa fa-comment-o"></i> Observaciones del cambio de estado
                            </label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3"
                                maxlength="1000"
                                placeholder="Ej: Solicitud de baja por cambio de domicilio, egreso anticipado, etc.">{{ old('observaciones', $alumno->observaciones) }}</textarea>
                            @error('observaciones')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                            <span class="help-block" style="font-size:11px;color:#8a9ab0;">
                                Indica el motivo o cualquier nota relacionada con este cambio de estado.
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Información del registro</label>
                            <table class="table table-condensed"
                                style="font-size:12px;margin:0;background:#fafafa;border:1px solid #f0f0f0;border-radius:4px;">
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
                                    <td style="padding:6px 10px;font-size:11px;">
                                        {{ $alumno->created_at?->format('d/m/Y') }}</td>
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
                    @php
                        $esAnticActual = $inscActual->tipo?->value === 'anticipada';
                        $cicloNombreActual = $inscActual->ciclo?->nombre ?? $inscActual->grupo?->ciclo?->nombre ?? '—';
                    @endphp
                    <div class="ins-actual-card"
                         style="{{ $esAnticActual ? 'background:#fffbf0;border-color:#fcd97d;border-left-color:#f39c12;' : '' }}">
                        <div class="ins-actual-badge"
                             style="{{ $esAnticActual ? 'background:#f39c12;' : '' }}">
                            <i class="fa {{ $esAnticActual ? 'fa-calendar-plus-o' : 'fa-graduation-cap' }}"
                               style="color:#fff;font-size:20px;"></i>
                        </div>
                        <div style="flex:1;">
                            <div class="ins-actual-titulo"
                                 style="{{ $esAnticActual ? 'color:#b45309;' : '' }}">
                                {{ $esAnticActual ? 'Inscripción anticipada activa' : 'Inscripción actual' }}
                            </div>
                            <div class="ins-actual-sub">
                                {{ $cicloNombreActual }}
                                &nbsp;·&nbsp;
                                {{ $inscActual->grupo?->grado?->nivel?->nombre ?? '—' }}
                                @if($inscActual->grupo)
                                    &nbsp;·&nbsp;
                                    {{ $inscActual->grupo->grado?->numero ?? '' }}°
                                    Grupo {{ $inscActual->grupo->nombre }}
                                @else
                                    &nbsp;·&nbsp; <em style="color:#b0bec5;">Sin grupo asignado</em>
                                @endif
                            </div>
                        </div>
                        <span style="background:{{ $esAnticActual ? '#f39c12' : '#3c8dbc' }};color:#fff;font-size:10px;padding:2px 10px;border-radius:10px;font-weight:600;">
                            {{ $esAnticActual ? 'ANTICIPADA' : 'ACTIVA' }}
                        </span>
                    </div>
                @else
                    <div class="alert alert-info" style="font-size:12px;">
                        <i class="fa fa-info-circle"></i>
                        Este alumno no tiene inscripción activa. Selecciona un ciclo para inscribirlo (el grupo es opcional).
                    </div>
                @endif

                {{-- Selectores editables --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                            <label for="ciclo_id">Ciclo escolar</label>
                            <select name="ciclo_id" id="ciclo_id" class="form-control">
                                <option value="">-- Sin cambio de ciclo --</option>
                                @foreach($ciclosDisponibles as $ciclo)
                                    <option value="{{ $ciclo->id }}"
                                        {{ $cicloIdActual == $ciclo->id ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }}
                                        @if($ciclo->estado === 'activo') (Activo) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('ciclo_id')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('nivel_id') ? 'has-error' : '' }}">
                            <label for="nivel_id">Nivel educativo</label>
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
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('grupo_id') ? 'has-error' : '' }}">
                            <label for="grupo_id">
                                Grado y grupo
                                <small class="text-muted" style="font-weight:400;">(opcional)</small>
                            </label>
                            <select name="grupo_id" id="grupo_id" class="form-control">
                                <option value="">-- Sin grupo asignado --</option>
                                @if ($grupoActual && $inscActual?->grupo)
                                    <option value="{{ $grupoActual }}" selected>
                                        {{ $inscActual->grupo->grado->numero ?? '' }}°
                                        {{ $inscActual->grupo->nombre }}
                                    </option>
                                @endif
                            </select>
                            @error('grupo_id')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                    {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div id="grupos-cargando" style="display:none;color:#999;font-size:12px;margin-top:-10px;">
                    <i class="fa fa-spinner fa-spin"></i> Cargando grupos disponibles...
                </div>

                {{-- ── HISTORIAL DE INSCRIPCIONES ── --}}
                @if($inscripciones->count())
                <div style="margin-top:28px;">
                    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;
                               color:#6b7a8d;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                        <i class="fa fa-history" style="color:#3c8dbc;"></i>
                        Historial de inscripciones
                        <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                                     padding:2px 8px;border-radius:10px;">{{ $inscripciones->count() }}</span>
                    </p>
                    <div style="border:1px solid #e4eaf0;border-radius:8px;overflow:hidden;">
                        <table class="table" style="margin:0;font-size:12px;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th style="color:#6b7a8d;font-weight:600;padding:9px 14px;border-bottom:1px solid #e8ecf0;">Ciclo</th>
                                    <th style="color:#6b7a8d;font-weight:600;padding:9px 14px;border-bottom:1px solid #e8ecf0;">Nivel</th>
                                    <th style="color:#6b7a8d;font-weight:600;padding:9px 14px;border-bottom:1px solid #e8ecf0;">Grupo</th>
                                    <th style="color:#6b7a8d;font-weight:600;padding:9px 14px;border-bottom:1px solid #e8ecf0;">Fecha</th>
                                    <th style="color:#6b7a8d;font-weight:600;padding:9px 14px;border-bottom:1px solid #e8ecf0;">Tipo</th>
                                    <th style="color:#6b7a8d;font-weight:600;padding:9px 14px;border-bottom:1px solid #e8ecf0;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inscripciones->sortByDesc('id') as $insc)
                                    @php
                                        $esActiva  = $insc->activo;
                                        $esAntic   = $insc->tipo?->value === 'anticipada';
                                        $cicloNom  = $insc->ciclo?->nombre ?? $insc->grupo?->ciclo?->nombre ?? '—';
                                        $nivelNom  = $insc->grupo?->grado?->nivel?->nombre ?? '—';
                                        $grupoNom  = $insc->grupo
                                                     ? ($insc->grupo->grado?->numero . '° ' . $insc->grupo->nombre)
                                                     : null;
                                    @endphp
                                    <tr style="{{ $esActiva ? 'background:#f7fbff;' : '' }}">
                                        <td style="padding:9px 14px;font-weight:{{ $esActiva ? '700' : '400' }};color:#1a2634;">
                                            {{ $cicloNom }}
                                        </td>
                                        <td style="padding:9px 14px;color:#4a5568;">{{ $nivelNom }}</td>
                                        <td style="padding:9px 14px;color:#4a5568;">
                                            @if($grupoNom)
                                                {{ $grupoNom }}
                                            @else
                                                <em style="color:#b0bec5;">Sin grupo</em>
                                            @endif
                                        </td>
                                        <td style="padding:9px 14px;color:#8a9ab0;">
                                            {{ $insc->fecha?->format('d/m/Y') ?? '—' }}
                                        </td>
                                        <td style="padding:9px 14px;">
                                            @if($esAntic)
                                                <span style="background:#fff3cd;color:#856404;font-size:10px;
                                                             font-weight:700;padding:2px 8px;border-radius:8px;">
                                                    Anticipada
                                                </span>
                                            @else
                                                <span style="background:#f0f3f7;color:#6b7a8d;font-size:10px;
                                                             font-weight:600;padding:2px 8px;border-radius:8px;">
                                                    Regular
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding:9px 14px;">
                                            @if($esActiva)
                                                <span style="background:#e8f8f0;color:#00875a;font-size:10px;
                                                             font-weight:700;padding:2px 8px;border-radius:8px;">
                                                    Activa
                                                </span>
                                            @else
                                                <span style="background:#f0f3f7;color:#b0bec5;font-size:10px;
                                                             font-weight:600;padding:2px 8px;border-radius:8px;">
                                                    Inactiva
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

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

                {{-- ── Cambiar familia ── --}}
                <div class="panel panel-default" style="margin-bottom:16px;border-color:#d2d6de;">
                    <div class="panel-heading" id="toggle-cambiar-familia"
                         style="cursor:pointer;padding:10px 14px;background:#f5f5f5;display:flex;justify-content:space-between;align-items:center;">
                        <span>
                            <i class="fa fa-users" style="margin-right:6px;color:#777;"></i>
                            <span style="font-size:12px;color:#777;">Familia asignada:</span>
                            <strong id="label-familia-actual" style="margin-left:4px;">
                                {{ $alumno->familia?->apellido_familia ?? '—' }}
                            </strong>
                        </span>
                        <span style="font-size:12px;color:#3c8dbc;">
                            <i class="fa fa-pencil"></i> Cambiar familia
                            <i class="fa fa-chevron-down" id="ico-toggle-familia" style="margin-left:4px;"></i>
                        </span>
                    </div>
                    <div class="panel-body" id="panel-cambiar-familia" style="display:none;padding:14px;">
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group" style="margin-bottom:8px;">
                                    <label style="font-size:12px;">Selecciona la familia</label>
                                    <select id="select-familia" style="width:100%;">
                                        @foreach($familias as $fam)
                                            <option value="{{ $fam->id }}"
                                                {{ $fam->id == $alumno->familia_id ? 'selected' : '' }}>
                                                {{ $fam->apellido_familia }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4" style="display:flex;align-items:flex-end;padding-bottom:8px;">
                                <button type="button" class="btn btn-warning btn-block btn-sm" id="btn-aplicar-familia">
                                    <i class="fa fa-check"></i> Aplicar
                                </button>
                            </div>
                        </div>
                        <p style="font-size:11px;color:#888;margin:4px 0 0;">
                            <i class="fa fa-info-circle"></i>
                            Cambiar la familia actualiza el vínculo del alumno y los nuevos contactos que agregues quedarán asociados a ella.
                            Guarda el formulario para confirmar.
                        </p>
                    </div>
                </div>

                {{-- Contactos existentes --}}
                @forelse($alumno->contactos as $contacto)
                    <div class="panel panel-default ctc-panel" style="margin-bottom:10px;"
                        data-id="{{ $contacto->id }}">
                        <div class="panel-heading" style="padding:8px 12px;background:#f5f5f5;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <strong style="font-size:13px;">
                                    <span class="ctc-titulo">{{ $contacto->nombre }}
                                        {{ $contacto->ap_paterno }}</span>
                                    @if($contacto->pivot->orden == 1)
                                        <span class="label label-primary"
                                            style="font-size:10px;margin-left:4px;">Principal</span>
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
                                            @foreach(['padre' => 'Padre', 'madre' => 'Madre', 'abuelo' => 'Abuelo/a', 'tio' => 'Tío/a', 'otro' => 'Otro'] as $val => $lbl)
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
                                            <option value="padre" {{ $contacto->pivot->tipo === 'padre' ? 'selected' : '' }}>Padre/Madre</option>
                                            <option value="tutor" {{ $contacto->pivot->tipo === 'tutor' ? 'selected' : '' }}>Tutor</option>
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
                                            {{ $contacto->pivot->tiene_acceso_portal ? 'checked' : '' }}>
                                        Acceso al portal
                                    </label>
                                    @if($contacto->usuario_id && $contacto->usuario?->activo)
                                        <span class="label label-success" style="margin-left:6px;font-size:10px;">
                                            <i class="fa fa-check"></i> Usuario activo
                                        </span>
                                    @elseif($contacto->tiene_acceso_portal && !$contacto->usuario_id)
                                        <span class="label label-warning" style="margin-left:6px;font-size:10px;">
                                            <i class="fa fa-clock-o"></i> Pendiente de usuario
                                        </span>
                                    @elseif($contacto->usuario_id && !$contacto->usuario?->activo)
                                        <span class="label label-default" style="margin-left:6px;font-size:10px;">
                                            <i class="fa fa-ban"></i> Usuario deshabilitado
                                        </span>
                                    @endif
                                </div>
                            </div>
                            {{-- Datos adicionales del contacto --}}
                            <div style="margin-top:8px;">
                                <a href="#ctc-extra-{{ $contacto->id }}" data-toggle="collapse"
                                   style="font-size:11px;color:#3c8dbc;display:inline-block;margin-bottom:6px;">
                                    <i class="fa fa-plus-circle"></i> Datos adicionales
                                    <span style="color:#b0bec5;">(opcional)</span>
                                </a>
                                <div id="ctc-extra-{{ $contacto->id }}" class="{{ $contacto->telefono_2 || $contacto->fecha_nacimiento || $contacto->lugar_trabajo || $contacto->profesion ? 'collapse in' : 'collapse' }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-size:12px;">Teléfono 2</label>
                                                <input type="tel" class="form-control input-sm ctc-telefono2"
                                                    value="{{ $contacto->telefono_2 }}" maxlength="20">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-size:12px;">Fecha de nacimiento</label>
                                                <input type="date" class="form-control input-sm ctc-fecha-nacimiento"
                                                    value="{{ $contacto->fecha_nacimiento?->format('Y-m-d') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-size:12px;">Nivel de estudios</label>
                                                <select class="form-control input-sm ctc-nivel-estudios">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach(['Sin estudios','Primaria','Secundaria','Preparatoria','Técnico','Licenciatura','Posgrado','Otro'] as $nivel)
                                                        <option value="{{ $nivel }}" {{ $contacto->nivel_estudios === $nivel ? 'selected' : '' }}>{{ $nivel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label style="font-size:12px;">Profesión</label>
                                                <input type="text" class="form-control input-sm ctc-profesion"
                                                    value="{{ $contacto->profesion }}" maxlength="100">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label style="font-size:12px;">Lugar de trabajo</label>
                                                <input type="text" class="form-control input-sm ctc-lugar-trabajo"
                                                    value="{{ $contacto->lugar_trabajo }}" maxlength="200">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label style="font-size:12px;">Puesto</label>
                                                <input type="text" class="form-control input-sm ctc-puesto"
                                                    value="{{ $contacto->puesto }}" maxlength="100">
                                            </div>
                                        </div>
                                        <div class="col-md-2" style="padding-top:22px;">
                                            <label class="checkbox-inline" style="font-size:12px;">
                                                <input type="checkbox" class="ctc-vive"
                                                    {{ $contacto->vive !== false ? 'checked' : '' }}>
                                                Vive
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid #f0f0f0;">
                                <div class="ctc-foto-preview" style="width:52px;height:52px;border-radius:50%;border:2px solid #ddd;overflow:hidden;background:#f5f5f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    @if($contacto->foto_url)
                                        <img src="{{ asset('storage/' . $contacto->foto_url) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                                    @else
                                        <i class="fa fa-user" style="font-size:22px;color:#ccc;"></i>
                                    @endif
                                </div>
                                <div>
                                    <label style="font-size:11px;color:#888;margin-bottom:4px;display:block;">Foto del contacto</label>
                                    <div class="input-group" style="width:230px;">
                                        <span class="input-group-btn">
                                            <label class="btn btn-default btn-xs btn-flat" for="foto-ctc-{{ $contacto->id }}" style="margin:0;cursor:pointer;">
                                                <i class="fa fa-camera"></i> {{ $contacto->foto_url ? 'Cambiar' : 'Subir foto' }}
                                            </label>
                                        </span>
                                        <input type="text" class="form-control input-xs ctc-foto-nombre" placeholder="Sin foto" readonly style="font-size:11px;">
                                    </div>
                                    <input type="file" id="foto-ctc-{{ $contacto->id }}"
                                        name="fotos_contacto[{{ $contacto->id }}]"
                                        class="ctc-foto-input" data-id="{{ $contacto->id }}"
                                        accept="image/jpeg,image/png,image/webp" style="display:none">
                                    <small class="text-muted" style="font-size:10px;">JPG, PNG o WEBP · Máx. 2 MB.</small>
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
                                    <input type="text" id="nctc-nombre" class="form-control input-sm"
                                        maxlength="100" placeholder="Nombre(s)">
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
                                    <input type="tel" id="nctc-telefono" class="form-control input-sm"
                                        maxlength="10" placeholder="10 dígitos">
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

                        {{-- Datos adicionales del nuevo contacto --}}
                        <p style="font-size:11px;font-weight:700;color:#6b7a8d;text-transform:uppercase;letter-spacing:.04em;margin:8px 0 6px;">
                            <i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Datos adicionales
                            <span style="font-weight:400;color:#b0bec5;text-transform:none;"> — Opcional</span>
                        </p>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Teléfono 2</label>
                                    <input type="tel" id="nctc-telefono2" class="form-control input-sm" maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Fecha de nacimiento</label>
                                    <input type="date" id="nctc-fecha-nacimiento" class="form-control input-sm">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Nivel de estudios</label>
                                    <select id="nctc-nivel-estudios" class="form-control input-sm">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="Sin estudios">Sin estudios</option>
                                        <option value="Primaria">Primaria</option>
                                        <option value="Secundaria">Secundaria</option>
                                        <option value="Preparatoria">Preparatoria / Bachillerato</option>
                                        <option value="Técnico">Técnico / Carrera técnica</option>
                                        <option value="Licenciatura">Licenciatura / Universidad</option>
                                        <option value="Posgrado">Posgrado (Maestría / Doctorado)</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Profesión</label>
                                    <input type="text" id="nctc-profesion" class="form-control input-sm" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label style="font-size:12px;">Lugar de trabajo</label>
                                    <input type="text" id="nctc-lugar-trabajo" class="form-control input-sm" maxlength="200">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px;">Puesto</label>
                                    <input type="text" id="nctc-puesto" class="form-control input-sm" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-3" style="padding-top:22px;">
                                <label class="checkbox-inline" style="font-size:12px;">
                                    <input type="checkbox" id="nctc-vive" checked> Vive
                                </label>
                            </div>
                        </div>

                        <div class="row" style="margin-top:4px;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-size:12px;">Foto del contacto <small class="text-muted">(opcional)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <label class="btn btn-default btn-sm btn-flat" for="nctc-foto" style="margin:0;cursor:pointer;">
                                                <i class="fa fa-camera"></i> Seleccionar
                                            </label>
                                        </span>
                                        <input type="text" id="nctc-foto-nombre" class="form-control input-sm" placeholder="Sin foto" readonly>
                                    </div>
                                    <input type="file" id="nctc-foto" accept="image/jpeg,image/png,image/webp" style="display:none">
                                    <span class="help-block" style="font-size:11px;">JPG, PNG o WEBP · Máx. 2 MB.</span>
                                </div>
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

    {{-- ══ COLUMNA DERECHA — nav + acciones ══ --}}
    <div class="col-md-4">

        {{-- Navegación del wizard --}}
        <div class="sidebar-panel">
            <div class="sidebar-header">
                <i class="fa fa-list-ol" style="margin-right:6px;"></i> Progreso de edición
            </div>
            <div class="sidebar-body">
                <p style="font-size:12px;color:#8a9ab0;margin-bottom:14px;" id="wizard-step-description">
                    Paso 1 de 4: edita los datos personales del alumno.
                </p>

                <div style="margin-bottom:16px;">
                    @foreach($pasosWizard as $numero => $paso)
                    <div class="wizard-summary-item" data-step="{{ $numero }}">
                        <span class="step-dot">{{ $numero }}</span>
                        <span>
                            <span style="display:block;font-weight:600;font-size:12px;">{{ $paso['titulo'] }}</span>
                            <span style="display:block;font-size:11px;color:#8a9ab0;">{{ $paso['descripcion'] }}</span>
                        </span>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-default btn-block" id="btn-paso-anterior"
                        onclick="wizardIr(wizardPasoActual() - 1); return false;"
                        style="display:none;margin-bottom:6px;">
                    <i class="fa fa-arrow-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-primary btn-block" id="btn-paso-siguiente"
                        onclick="wizardIr(wizardPasoActual() + 1); return false;"
                        style="margin-bottom:6px;">
                    Siguiente <i class="fa fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn btn-success btn-block btn-lg" id="btn-guardar"
                        style="margin-bottom:6px;">
                    <i class="fa fa-save"></i> Guardar cambios
                </button>
                <a href="{{ route('alumnos.show', $alumno->id) }}"
                   class="btn btn-default btn-block">
                    <i class="fa fa-times"></i> Cancelar
                </a>
            </div>
        </div>

        {{-- Errores de validación --}}
        @if($errors->any())
        <div class="sidebar-panel" style="border-color:#f5c6cb;">
            <div class="sidebar-header" style="background:linear-gradient(135deg,#c0392b 0%,#e74c3c 100%);">
                <i class="fa fa-exclamation-triangle" style="margin-right:6px;"></i> Corrige los errores
            </div>
            <div class="sidebar-body">
                <ul style="padding-left:18px;margin:0;">
                    @foreach($errors->all() as $error)
                    <li style="color:#a94442;font-size:12px;margin-bottom:4px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        {{-- Resumen alumno --}}
        <div class="sidebar-panel">
            <div class="sidebar-header" style="background:linear-gradient(135deg,#2d3748 0%,#4a5568 100%);">
                <i class="fa fa-info-circle" style="margin-right:6px;"></i> Resumen del alumno
            </div>
            <div class="sidebar-body" style="padding:0;">
                <table class="table" style="font-size:12px;margin:0;">
                    <tr>
                        <th style="color:#999;font-weight:400;padding:8px 14px;width:45%;">Matrícula</th>
                        <td style="padding:8px 14px;"><code>{{ $alumno->matricula }}</code></td>
                    </tr>
                    <tr>
                        <th style="color:#999;font-weight:400;padding:8px 14px;">Familia</th>
                        <td style="padding:8px 14px;" id="sidebar-familia-nombre">{{ $alumno->familia?->apellido_familia ?? '—' }}</td>
                    </tr>
                    @if($inscActual)
                    <tr>
                        <th style="color:#999;font-weight:400;padding:8px 14px;">Ciclo</th>
                        <td style="padding:8px 14px;">{{ $inscActual->grupo?->ciclo?->nombre ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th style="color:#999;font-weight:400;padding:8px 14px;">Grupo actual</th>
                        <td style="padding:8px 14px;font-weight:600;">
                            {{ $inscActual->grupo?->grado?->numero ?? '' }}°
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function() {

            // ══════════════════════════════════════════════════
            // WIZARD
            // ══════════════════════════════════════════════════
            var TOTAL_PASOS = 4;
            var pasoActual = 1;

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
                        .toggleClass('is-active', s === pasoActual)
                        .toggleClass('is-complete', s < pasoActual);
                });

                $('.wizard-summary-item').each(function() {
                    $(this).toggleClass('is-active', Number($(this).data('step')) === pasoActual);
                });

                $('#wizard-progress-bar').css('width', (pasoActual / TOTAL_PASOS * 100) + '%');
                $('#wizard-step-description').text(PASOS_DESC[pasoActual]);
                $('#btn-paso-anterior').toggle(pasoActual > 1);
                $('#btn-paso-siguiente').toggle(pasoActual < TOTAL_PASOS);

                if (pasoActual === 3) {
                    var ci = $('#ciclo_id').val();
                    var ni = $('#nivel_id').val();
                    // Siempre recargar al visitar el paso 3 para mostrar capacidades actualizadas
                    if (ci && ni) {
                        cargarGrupos(ci, ni, GRUPO_ACTUAL);
                    }
                }

                $('html,body').animate({
                    scrollTop: $('#wizard-steps-nav').offset().top - 80
                }, 200);
            };

            window.wizardPasoActual = function() {
                return pasoActual;
            };

            function pasoConError() {
                var $e = $('.has-error').first();
                if (!$e.length) return null;
                var $panel = $e.closest('.wizard-step-panel');
                return $panel.length ? Number($panel.data('step')) : null;
            }

            // ══════════════════════════════════════════════════
            // PASO 3 — valores actuales inyectados desde PHP
            // ══════════════════════════════════════════════════
            var CICLO_ACTUAL = '{{ $cicloIdActual }}';
            var NIVEL_ACTUAL = '{{ $nivelActual }}';
            var GRUPO_ACTUAL = '{{ $grupoActual }}';

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
                    if (!$('#nombre').val().trim()) {
                        marcarError('#nombre', 'El nombre es obligatorio.');
                        ok = false;
                    } else marcarOk('#nombre');
                    if (!$('#ap_paterno').val().trim()) {
                        marcarError('#ap_paterno', 'El apellido paterno es obligatorio.');
                        ok = false;
                    } else marcarOk('#ap_paterno');
                    if (!$('#fecha_nacimiento').val()) {
                        marcarError('#fecha_nacimiento', 'La fecha de nacimiento es obligatoria.');
                        ok = false;
                    } else marcarOk('#fecha_nacimiento');
                    if (!$('#fecha_inscripcion').val()) {
                        marcarError('#fecha_inscripcion', 'La fecha de inscripción es obligatoria.');
                        ok = false;
                    } else marcarOk('#fecha_inscripcion');
                }

                if (paso === 2) {
                    if (!$('#estado').val()) {
                        marcarError('#estado', 'El estado es obligatorio.');
                        ok = false;
                    } else marcarOk('#estado');
                }

                // Paso 3: ningún campo de inscripción es obligatorio (grupo puede quedar sin asignar)

                return ok;
            }

            // ══════════════════════════════════════════════════
            // PASO 3 — CARGA DINÁMICA DE GRUPOS
            // ══════════════════════════════════════════════════
            $('#ciclo_id, #nivel_id').on('change', function() {
                var ci = $('#ciclo_id').val();
                var ni = $('#nivel_id').val();
                if (ci && ni) {
                    cargarGrupos(ci, ni, null);
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
                    success: function(response) {
                var grupos = Array.isArray(response) ? response : (response.data || []);
                        var opciones = '';

                        if (!grupos.length) {
                            opciones = '<option value="">Sin grupos para esta selección</option>';
                        } else {
                            opciones = '<option value="">-- Seleccionar grupo --</option>';
                            grupos.forEach(function(g) {
                                var capacidad = g.cupo_maximo ?
                                    g.alumnos_inscritos + '/' + g.cupo_maximo :
                                    g.alumnos_inscritos + ' inscritos';
                                var lleno = (g.cupo_maximo && g.alumnos_inscritos >= g.cupo_maximo) ? ' [LLENO]' : '';
                                var sel = (preseleccionar && g.id == preseleccionar) ? ' selected' : '';
                                var gradoNombre = (g.grado && g.grado.numero) ? g.grado.numero + '° ' : '';
                                opciones += '<option value="' + g.id + '"' + sel + '>' +
                                    gradoNombre + g.nombre +
                                    ' (' + capacidad + ')' + lleno +
                                    '</option>';
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
            // ESTADO → FECHA BAJA + OBSERVACIONES
            // ══════════════════════════════════════════════════
            var ESTADO_ORIGINAL = '{{ $alumno->estado }}';

            $('#estado').on('change', function() {
                var v = $(this).val();
                if (v === 'baja_temporal' || v === 'baja_definitiva') {
                    $('#bloque-fecha-baja').show();
                    if (!$('#fecha_baja').val()) $('#fecha_baja').val("{{ now()->format('Y-m-d') }}");
                } else {
                    $('#bloque-fecha-baja').hide();
                    $('#fecha_baja').val('');
                }
                if (v !== ESTADO_ORIGINAL) {
                    $('#bloque-observaciones').show();
                    $('#observaciones').focus();
                } else {
                    $('#bloque-observaciones').hide();
                }
            });

            // ══════════════════════════════════════════════════
            // FOTO PREVIEW (alumno)
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
                        .html('<img src="' + e.target.result +
                            '" style="width:100%;height:100%;object-fit:cover;">');
                };
                r.readAsDataURL(f);
            });

            // ══════════════════════════════════════════════════
            // FOTO DE CONTACTOS — solo preview (se guarda al pulsar "Guardar")
            // ══════════════════════════════════════════════════
            $(document).on('change', '.ctc-foto-input', function() {
                var f = this.files[0];
                var $panel = $(this).closest('.ctc-panel');
                var $preview = $panel.find('.ctc-foto-preview');
                var $nombre = $panel.find('.ctc-foto-nombre');

                if (!f) return;

                if (f.size > MAX_FOTO) {
                    this.value = '';
                    $nombre.val('');
                    alert('El archivo supera 2 MB.');
                    return;
                }

                $nombre.val(f.name);
                alertaCtc('Foto seleccionada. Se guardará al hacer clic en "Guardar cambios".', 'info');

                var reader = new FileReader();
                reader.onload = function(e) {
                    $preview.html('<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">');
                };
                reader.readAsDataURL(f);
            });

            // Foto del nuevo contacto — solo preview y validar tamaño
            $('#nctc-foto').on('change', function() {
                var f = this.files[0];
                if (!f) { $('#nctc-foto-nombre').val(''); return; }
                if (f.size > MAX_FOTO) {
                    this.value = '';
                    $('#nctc-foto-nombre').val('');
                    alert('El archivo supera 2 MB.');
                    return;
                }
                $('#nctc-foto-nombre').val(f.name);
            });

            // ══════════════════════════════════════════════════
            // SUBMIT — guardar contactos vía AJAX y luego enviar el formulario
            // Las fotos de contacto se incluyen directamente en el submit del form
            // ══════════════════════════════════════════════════
            $('#form-editar-alumno').on('submit', function(e) {
                var $form = $(this);
                var $paneles = $('.ctc-panel');

                $('#btn-guardar').prop('disabled', true)
                    .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

                if ($paneles.length === 0) return; // sin contactos, enviar directo

                e.preventDefault();

                var peticiones = $paneles.map(function() {
                    var $panel = $(this);
                    var id = $panel.data('id');

                    // Guardar texto de cada contacto (JSON PUT)
                    // Las fotos van incluidas directamente en el submit del formulario principal
                    return $.ajax({
                        url: '/familias/contactos/' + id,
                        method: 'PUT',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            alumno_id:           ALUMNO_ID,
                            nombre:              $panel.find('.ctc-nombre').val().trim(),
                            ap_paterno:          $panel.find('.ctc-ap-paterno').val().trim(),
                            ap_materno:          $panel.find('.ctc-ap-materno').val().trim(),
                            telefono_celular:    $panel.find('.ctc-telefono').val().trim(),
                            email:               $panel.find('.ctc-email').val().trim(),
                            // Datos adicionales
                            telefono_2:          $panel.find('.ctc-telefono2').val().trim(),
                            fecha_nacimiento:    $panel.find('.ctc-fecha-nacimiento').val() || null,
                            lugar_trabajo:       $panel.find('.ctc-lugar-trabajo').val().trim(),
                            puesto:              $panel.find('.ctc-puesto').val().trim(),
                            nivel_estudios:      $panel.find('.ctc-nivel-estudios').val(),
                            profesion:           $panel.find('.ctc-profesion').val().trim(),
                            vive:                $panel.find('.ctc-vive').is(':checked'),
                            // Permisos y pivot — independientes por alumno
                            parentesco:          $panel.find('.ctc-parentesco').val(),
                            tipo:                $panel.find('.ctc-tipo').val(),
                            orden:               parseInt($panel.find('.ctc-orden').val()),
                            autorizado_recoger:  $panel.find('.ctc-recoger').is(':checked'),
                            es_responsable_pago: $panel.find('.ctc-pago').is(':checked'),
                            tiene_acceso_portal: $panel.find('.ctc-portal').is(':checked'),
                        }),
                    });
                }).get();

                $.when.apply($, peticiones).always(function() {
                    $form[0].submit();
                });
            });

            // ══════════════════════════════════════════════════
            // CONTACTOS — GUARDAR EXISTENTE (AJAX)
            // ══════════════════════════════════════════════════
            $(document).on('click', '.btn-ctc-guardar', function() {
                var $panel = $(this).closest('.ctc-panel');
                var id = $panel.data('id');
                var $btn = $(this);
                var orig = $btn.html();

                if (!$panel.find('.ctc-nombre').val().trim()) { alertaCtc('El nombre es obligatorio.', 'danger'); return; }
                if (!$panel.find('.ctc-telefono').val().trim()) { alertaCtc('El teléfono es obligatorio.', 'danger'); return; }

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

                // 1. Guardar datos de texto (JSON PUT)
                $.ajax({
                    url: '/familias/contactos/' + id,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        alumno_id:           ALUMNO_ID,
                        nombre:              $panel.find('.ctc-nombre').val().trim(),
                        ap_paterno:          $panel.find('.ctc-ap-paterno').val().trim(),
                        ap_materno:          $panel.find('.ctc-ap-materno').val().trim(),
                        telefono_celular:    $panel.find('.ctc-telefono').val().trim(),
                        email:               $panel.find('.ctc-email').val().trim(),
                        // Datos adicionales
                        telefono_2:          $panel.find('.ctc-telefono2').val().trim(),
                        fecha_nacimiento:    $panel.find('.ctc-fecha-nacimiento').val() || null,
                        lugar_trabajo:       $panel.find('.ctc-lugar-trabajo').val().trim(),
                        puesto:              $panel.find('.ctc-puesto').val().trim(),
                        nivel_estudios:      $panel.find('.ctc-nivel-estudios').val(),
                        profesion:           $panel.find('.ctc-profesion').val().trim(),
                        vive:                $panel.find('.ctc-vive').is(':checked'),
                        // Permisos y pivot
                        parentesco:          $panel.find('.ctc-parentesco').val(),
                        tipo:                $panel.find('.ctc-tipo').val(),
                        orden:               parseInt($panel.find('.ctc-orden').val()),
                        autorizado_recoger:  $panel.find('.ctc-recoger').is(':checked'),
                        es_responsable_pago: $panel.find('.ctc-pago').is(':checked'),
                        tiene_acceso_portal: $panel.find('.ctc-portal').is(':checked'),
                    }),
                })
                .done(function() {
                    $panel.find('.ctc-titulo').text(
                        $panel.find('.ctc-nombre').val().trim() + ' ' +
                        $panel.find('.ctc-ap-paterno').val().trim()
                    );
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Guardado');
                    alertaCtc('Contacto guardado. La foto se guardará con "Guardar cambios".', 'success');
                    setTimeout(function() { $btn.html(orig); }, 2500);
                })
                .fail(function(xhr) {
                    $btn.prop('disabled', false).html(orig);
                    alertaCtc(xhr.responseJSON?.message || 'Error al guardar.', 'danger');
                });
            });

            // ══════════════════════════════════════════════════
            // CONTACTOS — ELIMINAR (AJAX)
            // ══════════════════════════════════════════════════
            $(document).on('click', '.btn-ctc-eliminar', function() {
                var $panel = $(this).closest('.ctc-panel');
                var id = $panel.data('id');
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

            // ══════════════════════════════════════════════════
            // CAMBIAR FAMILIA
            // ══════════════════════════════════════════════════
            $('#select-familia').select2({ width: '100%', language: { noResults: function() { return 'Sin resultados'; } } });

            $('#toggle-cambiar-familia').on('click', function () {
                $('#panel-cambiar-familia').slideToggle(200);
                $('#ico-toggle-familia').toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('#btn-aplicar-familia').on('click', function () {
                var id    = $('#select-familia').val();
                var texto = $('#select-familia option:selected').text().trim();
                if (!id) return;

                FAMILIA_ID = parseInt(id);
                $('#input-familia-id').val(id);
                $('#label-familia-actual').text(texto);
                $('#sidebar-familia-nombre').text(texto);
                $('#panel-cambiar-familia').slideUp(200);
                $('#ico-toggle-familia').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                alertaCtc('Familia cambiada a "' + texto + '". Guarda el formulario para confirmar el cambio.', 'warning');
            });

            $('#btn-cancelar-ctc').on('click', function() {
                limpiarCtc();
                $('#form-nuevo-ctc').hide();
                $('#ico-toggle-ctc').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            });

            $('#btn-guardar-ctc').on('click', function() {
                var $btn = $(this);

                var datos = {
                    alumno_id: ALUMNO_ID,
                    familia_id: FAMILIA_ID,
                    nombre: $('#nctc-nombre').val().trim(),
                    ap_paterno: $('#nctc-ap-paterno').val().trim(),
                    ap_materno: $('#nctc-ap-materno').val().trim(),
                    telefono_celular: $('#nctc-telefono').val().trim(),
                    email: $('#nctc-email').val().trim(),
                    curp: $('#nctc-curp').val().trim().toUpperCase(),
                    parentesco: $('#nctc-parentesco').val(),
                    tipo: $('#nctc-tipo').val(),
                    orden: parseInt($('#nctc-orden').val()),
                    autorizado_recoger: $('#nctc-recoger').is(':checked'),
                    es_responsable_pago: $('#nctc-pago').is(':checked'),
                    tiene_acceso_portal: $('#nctc-portal').is(':checked'),
                };

                if (!datos.nombre) { alertaCtc('El nombre es obligatorio.', 'danger'); $('#nctc-nombre').focus(); return; }
                if (!datos.telefono_celular) { alertaCtc('El teléfono es obligatorio.', 'danger'); $('#nctc-telefono').focus(); return; }
                if (!datos.parentesco) { alertaCtc('El parentesco es obligatorio.', 'danger'); $('#nctc-parentesco').focus(); return; }
                if (!datos.tipo) { alertaCtc('El tipo es obligatorio.', 'danger'); $('#nctc-tipo').focus(); return; }

                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

                // Construir FormData con campos explícitos (misma técnica que el formulario de registro)
                var fd = new FormData();
                fd.append('alumno_id',           String(ALUMNO_ID));
                fd.append('familia_id',           FAMILIA_ID !== null && FAMILIA_ID !== undefined ? String(FAMILIA_ID) : '');
                fd.append('nombre',               $('#nctc-nombre').val().trim());
                fd.append('ap_paterno',           $('#nctc-ap-paterno').val().trim());
                fd.append('ap_materno',           $('#nctc-ap-materno').val().trim());
                fd.append('telefono_celular',     $('#nctc-telefono').val().trim());
                fd.append('email',                $('#nctc-email').val().trim());
                fd.append('curp',                 $('#nctc-curp').val().trim().toUpperCase());
                // Datos adicionales
                fd.append('telefono_2',           $('#nctc-telefono2').val().trim());
                fd.append('fecha_nacimiento',     $('#nctc-fecha-nacimiento').val());
                fd.append('lugar_trabajo',        $('#nctc-lugar-trabajo').val().trim());
                fd.append('puesto',               $('#nctc-puesto').val().trim());
                fd.append('nivel_estudios',       $('#nctc-nivel-estudios').val());
                fd.append('profesion',            $('#nctc-profesion').val().trim());
                fd.append('vive',                 $('#nctc-vive').is(':checked') ? '1' : '0');
                // Permisos y pivot
                fd.append('parentesco',           $('#nctc-parentesco').val());
                fd.append('tipo',                 $('#nctc-tipo').val());
                fd.append('orden',                $('#nctc-orden').val());
                fd.append('autorizado_recoger',   $('#nctc-recoger').is(':checked') ? '1' : '0');
                fd.append('es_responsable_pago',  $('#nctc-pago').is(':checked')    ? '1' : '0');
                fd.append('tiene_acceso_portal',  $('#nctc-portal').is(':checked')  ? '1' : '0');
                var fotoNueva = document.getElementById('nctc-foto');
                if (fotoNueva && fotoNueva.files[0]) {
                    fd.append('foto', fotoNueva.files[0]);
                }

                $.ajax({
                    url: '/familias/contactos',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                })
                .done(function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');

                    var c = res.contacto, piv = res.pivot;

                    var pOpts = [['padre','Padre'],['madre','Madre'],['abuelo','Abuelo/a'],['tio','Tío/a'],['otro','Otro']]
                        .map(function(p) {
                            return '<option value="' + p[0] + '"' + (datos.parentesco === p[0] ? ' selected' : '') + '>' + p[1] + '</option>';
                        }).join('');
                    var tOpts =
                        '<option value="padre"' + (datos.tipo === 'padre' ? ' selected' : '') + '>Padre/Madre</option>' +
                        '<option value="tutor"' + (datos.tipo === 'tutor' ? ' selected' : '') + '>Tutor</option>' +
                        '<option value="tercero_autorizado"' + (datos.tipo === 'tercero_autorizado' ? ' selected' : '') + '>Tercero autorizado</option>';
                    var oOpts =
                        '<option value="1"' + (datos.orden === 1 ? ' selected' : '') + '>1 — Principal</option>' +
                        '<option value="2"' + (datos.orden === 2 ? ' selected' : '') + '>2 — Secundario</option>' +
                        '<option value="3"' + (datos.orden === 3 ? ' selected' : '') + '>3 — Tercero</option>';

                    var nivelOpts = ['', 'Sin estudios', 'Primaria', 'Secundaria', 'Preparatoria', 'Técnico', 'Licenciatura', 'Posgrado', 'Otro']
                        .map(function(n) {
                            return '<option value="' + n + '"' + (datos.nivel_estudios === n ? ' selected' : '') + '>' + (n || '-- Seleccionar --') + '</option>';
                        }).join('');

                    var html =
                        '<div class="panel panel-default ctc-panel" style="margin-bottom:10px;" data-id="' + c.id + '">' +
                        '<div class="panel-heading" style="padding:8px 12px;background:#f5f5f5;">' +
                        '<div style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<strong style="font-size:13px;"><span class="ctc-titulo">' + datos.nombre + ' ' + datos.ap_paterno + '</span></strong>' +
                        '<div>' +
                        '<button type="button" class="btn btn-success btn-xs btn-ctc-guardar"><i class="fa fa-save"></i> Guardar</button>' +
                        '<button type="button" class="btn btn-danger btn-xs btn-ctc-eliminar" style="margin-left:4px;"><i class="fa fa-trash"></i></button>' +
                        '</div></div></div>' +
                        '<div class="panel-body" style="padding:12px;">' +
                        '<div class="row">' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label><input type="text" class="form-control input-sm ctc-nombre" value="' + datos.nombre + '" maxlength="100"></div></div>' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Apellido paterno</label><input type="text" class="form-control input-sm ctc-ap-paterno" value="' + (datos.ap_paterno || '') + '" maxlength="100"></div></div>' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Apellido materno</label><input type="text" class="form-control input-sm ctc-ap-materno" value="' + (datos.ap_materno || '') + '" maxlength="100"></div></div>' +
                        '</div>' +
                        '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Teléfono</label><input type="tel" class="form-control input-sm ctc-telefono" value="' + (datos.telefono_celular || '') + '"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Correo</label><input type="email" class="form-control input-sm ctc-email" value="' + (datos.email || '') + '"></div></div>' +
                        '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Parentesco</label><select class="form-control input-sm ctc-parentesco">' + pOpts + '</select></div></div>' +
                        '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Tipo</label><select class="form-control input-sm ctc-tipo">' + tOpts + '</select></div></div>' +
                        '<div class="col-md-2"><div class="form-group"><label style="font-size:12px;">Orden</label><select class="form-control input-sm ctc-orden">' + oOpts + '</select></div></div>' +
                        '</div>' +
                        '<div class="row"><div class="col-md-12">' +
                        '<label class="checkbox-inline"><input type="checkbox" class="ctc-recoger"' + (piv.autorizado_recoger ? ' checked' : '') + '>  Autorizado recoger</label>' +
                        '<label class="checkbox-inline" style="margin-left:12px;"><input type="checkbox" class="ctc-pago"' + (piv.es_responsable_pago ? ' checked' : '') + '>  Resp. pagos</label>' +
                        '<label class="checkbox-inline" style="margin-left:12px;"><input type="checkbox" class="ctc-portal"' + (piv.tiene_acceso_portal ? ' checked' : '') + '>  Portal</label>' +
                        '</div></div>' +
                        // Datos adicionales colapsables
                        '<div style="margin-top:6px;">' +
                        '<a href="#ctc-extra-' + c.id + '" data-toggle="collapse" style="font-size:11px;color:#3c8dbc;display:inline-block;margin-bottom:6px;">' +
                        '<i class="fa fa-plus-circle"></i> Datos adicionales <span style="color:#b0bec5;">(opcional)</span></a>' +
                        '<div id="ctc-extra-' + c.id + '" class="collapse">' +
                        '<div class="row">' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Teléfono 2</label><input type="tel" class="form-control input-sm ctc-telefono2" value="' + (datos.telefono_2 || '') + '" maxlength="20"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Fecha nacimiento</label><input type="date" class="form-control input-sm ctc-fecha-nacimiento" value="' + (datos.fecha_nacimiento || '') + '"></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Nivel de estudios</label><select class="form-control input-sm ctc-nivel-estudios">' + nivelOpts + '</select></div></div>' +
                        '<div class="col-md-3"><div class="form-group"><label style="font-size:12px;">Profesión</label><input type="text" class="form-control input-sm ctc-profesion" value="' + (datos.profesion || '') + '" maxlength="100"></div></div>' +
                        '</div>' +
                        '<div class="row">' +
                        '<div class="col-md-5"><div class="form-group"><label style="font-size:12px;">Lugar de trabajo</label><input type="text" class="form-control input-sm ctc-lugar-trabajo" value="' + (datos.lugar_trabajo || '') + '" maxlength="200"></div></div>' +
                        '<div class="col-md-4"><div class="form-group"><label style="font-size:12px;">Puesto</label><input type="text" class="form-control input-sm ctc-puesto" value="' + (datos.puesto || '') + '" maxlength="100"></div></div>' +
                        '<div class="col-md-3" style="padding-top:22px;"><label class="checkbox-inline" style="font-size:12px;"><input type="checkbox" class="ctc-vive" checked>  Vive</label></div>' +
                        '</div>' +
                        '</div></div>' +
                        '<div style="display:flex;align-items:center;gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid #f0f0f0;">' +
                        '<div class="ctc-foto-preview" style="width:52px;height:52px;border-radius:50%;border:2px solid #ddd;overflow:hidden;background:#f5f5f5;display:flex;align-items:center;justify-content:center;flex-shrink:0;">' +
                        (c.foto_url ? '<img src="/storage/' + c.foto_url + '" style="width:100%;height:100%;object-fit:cover;" alt="">' : '<i class="fa fa-user" style="font-size:22px;color:#ccc;"></i>') +
                        '</div>' +
                        '<div>' +
                        '<label style="font-size:11px;color:#888;margin-bottom:4px;display:block;">Foto del contacto</label>' +
                        '<div class="input-group" style="width:230px;">' +
                        '<span class="input-group-btn"><label class="btn btn-default btn-xs btn-flat" for="foto-ctc-' + c.id + '" style="margin:0;cursor:pointer;"><i class="fa fa-camera"></i> ' + (c.foto_url ? 'Cambiar' : 'Subir foto') + '</label></span>' +
                        '<input type="text" class="form-control input-xs ctc-foto-nombre" placeholder="Sin foto" readonly style="font-size:11px;">' +
                        '</div>' +
                        '<input type="file" id="foto-ctc-' + c.id + '" name="fotos_contacto[' + c.id + ']" class="ctc-foto-input" data-id="' + c.id + '" accept="image/jpeg,image/png,image/webp" style="display:none">' +
                        '<small class="text-muted" style="font-size:10px;">JPG, PNG o WEBP · Máx. 2 MB.</small>' +
                        '</div>' +
                        '</div>' +
                        '</div></div>';

                    $('.panel.panel-success').before(html);
                    $('.alert.alert-warning').remove();
                    limpiarCtc();
                    $('#form-nuevo-ctc').hide();
                    $('#ico-toggle-ctc').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    alertaCtc(res.message || 'Contacto agregado.', 'success');
                })
                .fail(function(xhr) {
                    $btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');
                    var msg = xhr.responseJSON?.message || 'Error al agregar el contacto.';
                    if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    alertaCtc(msg, 'danger');
                });
            });

            // ══════════════════════════════════════════════════
            // HELPERS
            // ══════════════════════════════════════════════════
            function limpiarCtc() {
                $('#nctc-nombre,#nctc-ap-paterno,#nctc-ap-materno,#nctc-telefono,#nctc-email,#nctc-curp').val('');
                $('#nctc-telefono2,#nctc-fecha-nacimiento,#nctc-lugar-trabajo,#nctc-puesto,#nctc-profesion').val('');
                $('#nctc-nivel-estudios').val('');
                $('#nctc-parentesco,#nctc-tipo').val('');
                $('#nctc-orden').val('1');
                $('#nctc-recoger,#nctc-pago,#nctc-portal').prop('checked', false);
                $('#nctc-vive').prop('checked', true); // vive=true por defecto
                $('#nctc-foto').val('');
                $('#nctc-foto-nombre').val('');
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
