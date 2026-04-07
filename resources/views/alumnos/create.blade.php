@extends('layouts.master')

@section('page_title', 'Registrar alumno')
@section('page_subtitle', 'Nuevo ingreso')

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
@endsection

@push('styles')
<style>
/* ── Wizard steps ─────────────────────────────────────── */
.wizard-steps {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
    border-bottom: 2px solid #e8e8e8;
}
.wizard-steps li {
    flex: 1;
    text-align: center;
    padding: 14px 8px;
    font-size: 13px;
    color: #aaa;
    cursor: default;
    position: relative;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    transition: all .2s;
}
.wizard-steps li .step-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #888;
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 6px;
    transition: all .2s;
}
.wizard-steps li .step-label { display: block; font-size: 12px; }
.wizard-steps li.active {
    color: #3c8dbc;
    border-bottom-color: #3c8dbc;
}
.wizard-steps li.active .step-num {
    background: #3c8dbc;
    color: #fff;
}
.wizard-steps li.done {
    color: #00a65a;
    border-bottom-color: #00a65a;
}
.wizard-steps li.done .step-num {
    background: #00a65a;
    color: #fff;
}

/* ── Paneles del wizard ───────────────────────────────── */
.wizard-panel { display: none; }
.wizard-panel.active { display: block; }

/* ── Resumen paso 4 ──────────────────────────────────── */
.resumen-item {
    display: flex;
    justify-content: space-between;
    padding: 7px 0;
    border-bottom: 1px solid #f4f4f4;
    font-size: 13px;
}
.resumen-item:last-child { border-bottom: none; }
.resumen-item .lbl { color: #888; }
.resumen-item .val { font-weight: 500; color: #333; }

/* ── Foto preview ────────────────────────────────────── */
#foto-preview-wrap {
    width: 100px; height: 100px;
    border: 2px dashed #ccc;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; overflow: hidden; position: relative;
}
#foto-preview-wrap img {
    width: 100%; height: 100%; object-fit: cover;
}
#foto-preview-wrap .placeholder {
    text-align: center; color: #ccc;
}
</style>
@endpush

@section('content')

<form method="POST"
      action="{{ route('alumnos.store') }}"
      enctype="multipart/form-data"
      id="form-alumno">
@csrf

<div class="box box-primary">

    {{-- ── Indicador de pasos ── --}}
    <div class="box-header with-border" style="padding-bottom:0;">
        <ul class="wizard-steps" id="wizard-steps">
            <li class="active" data-step="1">
                <span class="step-num">1</span>
                <span class="step-label"><i class="fa fa-user"></i> Datos personales</span>
            </li>
            <li data-step="2">
                <span class="step-num">2</span>
                <span class="step-label"><i class="fa fa-graduation-cap"></i> Inscripción</span>
            </li>
            <li data-step="3">
                <span class="step-num">3</span>
                <span class="step-label"><i class="fa fa-home"></i> Familia</span>
            </li>
            <li data-step="4">
                <span class="step-num">4</span>
                <span class="step-label"><i class="fa fa-phone"></i> Contactos</span>
            </li>
        </ul>
    </div>

    <div class="box-body">

        {{-- ════════════════════════════════════════
             PASO 1 — Datos personales
        ════════════════════════════════════════ --}}
        <div class="wizard-panel active" id="paso-1">

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><i class="fa fa-exclamation-triangle"></i> Corrige los siguientes errores:</strong>
                <ul style="margin:6px 0 0; padding-left:18px;">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="row">

                {{-- Foto --}}
                <div class="col-md-2" style="text-align:center;">
                    <label style="display:block; margin-bottom:6px; font-size:12px; color:#666;">Foto</label>
                    <div id="foto-preview-wrap" onclick="$('#foto').click()">
                        @if(old('foto'))
                            <img src="#" id="foto-preview-img" alt="preview">
                        @else
                            <div class="placeholder">
                                <i class="fa fa-camera fa-2x"></i>
                                <div style="font-size:11px; margin-top:4px;">Click para subir</div>
                            </div>
                        @endif
                    </div>
                    <input type="file" name="foto" id="foto"
                           accept="image/jpeg,image/png,image/webp"
                           style="display:none">
                    <small class="text-muted" style="font-size:11px;">JPG PNG WEBP · Máx 2MB</small>
                    @error('foto')
                        <span class="help-block text-red" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Nombre y apellidos --}}
                <div class="col-md-10">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                <label for="nombre">Nombre(s) <span class="text-red">*</span></label>
                                <input type="text" name="nombre" id="nombre"
                                       class="form-control" placeholder="Ej: Juan Carlos"
                                       value="{{ old('nombre') }}" maxlength="100">
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
                                <input type="text" name="ap_paterno" id="ap_paterno"
                                       class="form-control" placeholder="Ej: López"
                                       value="{{ old('ap_paterno') }}" maxlength="100">
                                @error('ap_paterno')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('ap_materno') ? 'has-error' : '' }}">
                                <label for="ap_materno">Apellido materno</label>
                                <input type="text" name="ap_materno" id="ap_materno"
                                       class="form-control" placeholder="Ej: García"
                                       value="{{ old('ap_materno') }}" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:8px;">
                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('fecha_nacimiento') ? 'has-error' : '' }}">
                        <label for="fecha_nacimiento">Fecha de nacimiento <span class="text-red">*</span></label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                               class="form-control"
                               value="{{ old('fecha_nacimiento') }}"
                               max="{{ now()->subYears(2)->format('Y-m-d') }}">
                        @error('fecha_nacimiento')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group {{ $errors->has('genero') ? 'has-error' : '' }}">
                        <label for="genero">Género</label>
                        <select name="genero" id="genero" class="form-control">
                            <option value="">--</option>
                            <option value="M"    {{ old('genero') === 'M'    ? 'selected' : '' }}>Masculino</option>
                            <option value="F"    {{ old('genero') === 'F'    ? 'selected' : '' }}>Femenino</option>
                            <option value="Otro" {{ old('genero') === 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group {{ $errors->has('curp') ? 'has-error' : '' }}">
                        <label for="curp">
                            CURP
                            <span id="curp-chars-lbl" class="text-muted" style="font-weight:400; font-size:11px;">
                                (<span id="curp-chars">{{ strlen(old('curp','')) }}</span>/18)
                            </span>
                        </label>
                        <input type="text" name="curp" id="curp"
                               class="form-control" placeholder="18 caracteres"
                               value="{{ old('curp') }}" maxlength="18"
                               style="text-transform:uppercase">
                        @error('curp')
                            <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}">
                        <label for="observaciones">Observaciones</label>
                        <input type="text" name="observaciones" id="observaciones"
                               class="form-control" placeholder="Notas adicionales (opcional)"
                               value="{{ old('observaciones') }}" maxlength="500">
                    </div>
                </div>
            </div>

        </div>{{-- /paso-1 --}}

        {{-- ════════════════════════════════════════
             PASO 2 — Inscripción
        ════════════════════════════════════════ --}}
        <div class="wizard-panel" id="paso-2">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('fecha_inscripcion') ? 'has-error' : '' }}">
                        <label for="fecha_inscripcion">Fecha de inscripción <span class="text-red">*</span></label>
                        <input type="date" name="fecha_inscripcion" id="fecha_inscripcion"
                               class="form-control"
                               value="{{ old('fecha_inscripcion', now()->format('Y-m-d')) }}">
                        @error('fecha_inscripcion')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                        <label for="ciclo_id">Ciclo escolar <span class="text-red">*</span></label>
                        <select name="ciclo_id" id="ciclo_id" class="form-control">
                            <option value="">-- Seleccionar ciclo --</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ old('ciclo_id', $cicloId) == $ciclo->id ? 'selected' : '' }}>
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
                        <label for="nivel_id">Nivel <span class="text-red">*</span></label>
                        <select name="nivel_id" id="nivel_id" class="form-control">
                            <option value="">-- Seleccionar nivel --</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}"
                                    {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>
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
                        <label for="grupo_id">Grupo <span class="text-red">*</span></label>
                        <select name="grupo_id" id="grupo_id" class="form-control">
                            <option value="">-- Primero selecciona nivel --</option>
                        </select>
                        @error('grupo_id')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Prospecto (colapsado) --}}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group {{ $errors->has('prospecto_id') ? 'has-error' : '' }}">
                        <label for="prospecto_id">
                            ID de prospecto
                            <small class="text-muted">(opcional)</small>
                        </label>
                        <input type="number" name="prospecto_id" id="prospecto_id"
                               class="form-control" placeholder="Si viene de admisiones"
                               value="{{ old('prospecto_id') }}" min="1">
                        <span class="help-block" style="color:#999;">
                            Si se especifica, el prospecto cambia a "inscrito" automáticamente.
                        </span>
                    </div>
                </div>
            </div>

        </div>{{-- /paso-2 --}}

        {{-- ════════════════════════════════════════
             PASO 3 — Familia
        ════════════════════════════════════════ --}}
        <div class="wizard-panel" id="paso-3">

            <div class="form-group">
                <label>¿El alumno tiene hermanos inscritos?</label>
                <div style="margin-top:6px;">
                    <label class="radio-inline">
                        <input type="radio" name="tipo_familia" value="nueva"
                            {{ old('tipo_familia', 'nueva') === 'nueva' ? 'checked' : '' }}>
                        No, es familia nueva
                    </label>
                    <label class="radio-inline" style="margin-left:24px;">
                        <input type="radio" name="tipo_familia" value="existente"
                            {{ old('tipo_familia') === 'existente' ? 'checked' : '' }}>
                        Sí, vincular a familia existente
                    </label>
                </div>
            </div>

            {{-- Nueva familia --}}
            <div id="bloque-familia-nueva">
                <div class="form-group {{ $errors->has('apellido_familia') ? 'has-error' : '' }}">
                    <label for="apellido_familia">Nombre de la familia <span class="text-red">*</span></label>
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="apellido_familia" id="apellido_familia"
                                   class="form-control" placeholder="Ej: Familia López García"
                                   value="{{ old('apellido_familia') }}" maxlength="200">
                            @error('apellido_familia')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Familia existente --}}
            <div id="bloque-familia-existente" style="display:none;">
                <div class="form-group {{ $errors->has('familia_id') ? 'has-error' : '' }}">
                    <label for="familia_id">Seleccionar familia <span class="text-red">*</span></label>
                    <div class="row">
                        <div class="col-md-5">
                            <select name="familia_id" id="familia_id" class="form-control">
                                <option value="">-- Buscar familia --</option>
                                @foreach($familias as $familia)
                                    <option value="{{ $familia->id }}"
                                        {{ old('familia_id') == $familia->id ? 'selected' : '' }}>
                                        {{ $familia->apellido_familia }}
                                        ({{ $familia->alumnos_count ?? 0 }} alumno(s))
                                    </option>
                                @endforeach
                            </select>
                            @error('familia_id')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /paso-3 --}}

        {{-- ════════════════════════════════════════
             PASO 4 — Contactos + Resumen
        ════════════════════════════════════════ --}}
        <div class="wizard-panel" id="paso-4">

            <div class="row">

                {{-- Contactos --}}
                <div class="col-md-8">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <strong><i class="fa fa-phone"></i> Contactos familiares</strong>
                        <button type="button" class="btn btn-success btn-xs" id="btn-agregar-contacto">
                            <i class="fa fa-plus"></i> Agregar contacto
                        </button>
                    </div>
                    <p class="text-muted" style="font-size:12px; margin-bottom:12px;">
                        <i class="fa fa-info-circle"></i>
                        Mínimo 1, máximo 3. El primero será el contacto principal.
                    </p>
                    <div id="contenedor-contactos"></div>
                    @error('contactos')
                        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Resumen antes de guardar --}}
                <div class="col-md-4">
                    <div class="box box-default" style="margin-bottom:0;">
                        <div class="box-header with-border" style="padding:10px 14px;">
                            <h4 class="box-title" style="font-size:13px;">
                                <i class="fa fa-eye"></i> Resumen del registro
                            </h4>
                        </div>
                        <div class="box-body" style="padding:10px 14px;">
                            <div class="resumen-item">
                                <span class="lbl">Alumno</span>
                                <span class="val" id="rsm-nombre">—</span>
                            </div>
                            <div class="resumen-item">
                                <span class="lbl">Fecha nac.</span>
                                <span class="val" id="rsm-fnac">—</span>
                            </div>
                            <div class="resumen-item">
                                <span class="lbl">CURP</span>
                                <span class="val" id="rsm-curp" style="font-size:11px;">—</span>
                            </div>
                            <div class="resumen-item">
                                <span class="lbl">Ciclo</span>
                                <span class="val" id="rsm-ciclo">—</span>
                            </div>
                            <div class="resumen-item">
                                <span class="lbl">Grupo</span>
                                <span class="val" id="rsm-grupo">—</span>
                            </div>
                            <div class="resumen-item">
                                <span class="lbl">Familia</span>
                                <span class="val" id="rsm-familia">—</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>{{-- /paso-4 --}}

    </div>{{-- /.box-body --}}

    {{-- ── Botones de navegación ── --}}
    <div class="box-footer clearfix">
        <button type="button" class="btn btn-default" id="btn-anterior" style="display:none;">
            <i class="fa fa-arrow-left"></i> Anterior
        </button>
        <button type="button" class="btn btn-primary pull-right" id="btn-siguiente" onclick="return false;">
            Siguiente <i class="fa fa-arrow-right"></i>
        </button>
        <button type="submit" class="btn btn-success pull-right" id="btn-guardar" style="display:none;">
            <i class="fa fa-save"></i> Registrar alumno
        </button>
    </div>

</div>{{-- /.box --}}

</form>

{{-- ── Template oculto de contacto ── --}}
<div id="template-contacto" style="display:none;">
    <div class="contacto-item panel panel-default" data-index="__INDEX__" style="margin-bottom:10px;">
        <div class="panel-heading" style="padding:8px 12px; background:#f5f5f5;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong style="font-size:13px;">Contacto #<span class="num-contacto">__NUM__</span></strong>
                <button type="button" class="btn btn-danger btn-xs btn-eliminar-contacto">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="panel-body" style="padding:12px;">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nombre(s) <span class="text-red">*</span></label>
                        <input type="text" name="contactos[__INDEX__][nombre]"
                               class="form-control inp-nombre-contacto" maxlength="100">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Apellido paterno</label>
                        <input type="text" name="contactos[__INDEX__][ap_paterno]"
                               class="form-control" maxlength="100">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Apellido materno</label>
                        <input type="text" name="contactos[__INDEX__][ap_materno]"
                               class="form-control" maxlength="100">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono celular <span class="text-red">*</span></label>
                        <input type="tel" name="contactos[__INDEX__][telefono_celular]"
                               class="form-control inp-tel-contacto" maxlength="20"
                               placeholder="10 dígitos">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="contactos[__INDEX__][email]"
                               class="form-control inp-email-contacto" maxlength="200">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>CURP</label>
                        <input type="text" name="contactos[__INDEX__][curp]"
                               class="form-control" maxlength="18"
                               style="text-transform:uppercase">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Parentesco <span class="text-red">*</span></label>
                        <select name="contactos[__INDEX__][parentesco]" class="form-control">
                            <option value="">--</option>
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
                        <label>Tipo <span class="text-red">*</span></label>
                        <select name="contactos[__INDEX__][tipo]" class="form-control">
                            <option value="">--</option>
                            <option value="padre">Padre/Madre</option>
                            <option value="tutor">Tutor</option>
                            <option value="tercero_autorizado">Tercero autorizado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Orden</label>
                        <select name="contactos[__INDEX__][orden]" class="form-control">
                            <option value="1" __ORDEN1__>1 — Principal</option>
                            <option value="2" __ORDEN2__>2 — Secundario</option>
                            <option value="3" __ORDEN3__>3 — Tercero</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4" style="padding-top:24px;">
                    <div class="checkbox" style="margin:4px 0;">
                        <label>
                            <input type="hidden"   name="contactos[__INDEX__][autorizado_recoger]" value="0">
                            <input type="checkbox" name="contactos[__INDEX__][autorizado_recoger]" value="1" __RECOGER__>
                            Autorizado para recoger
                        </label>
                    </div>
                    <div class="checkbox" style="margin:4px 0;">
                        <label>
                            <input type="hidden"   name="contactos[__INDEX__][es_responsable_pago]" value="0">
                            <input type="checkbox" name="contactos[__INDEX__][es_responsable_pago]" value="1" __PAGO__>
                            Responsable de pagos
                        </label>
                    </div>
                    <div class="checkbox" style="margin:4px 0;">
                        <label>
                            <input type="hidden"   name="contactos[__INDEX__][tiene_acceso_portal]" value="0">
                            <input type="checkbox" name="contactos[__INDEX__][tiene_acceso_portal]" value="1">
                            Acceso al portal
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════
// WIZARD — script completamente reescrito
// Sin $(document).ready — se ejecuta al final del DOM
// ═══════════════════════════════════════════════════════

var TOTAL_PASOS   = 4;
var MAX_CONTACTOS = 3;
var MAX_FOTO_MB   = 2 * 1024 * 1024;
var pasoActual    = 1;
var numContactos  = 0;

// ── Inicializar cuando el DOM esté listo ─────────────────
function initWizard() {
    if (typeof jQuery === 'undefined') {
        setTimeout(initWizard, 50);
        return;
    }

    agregarContacto();
    actualizarWizard();

    // Si hubo error de servidor, saltar al paso correcto
    @if($errors->any())
    (function(){
        var ep4 = {{ $errors->hasAny(['contactos']) ? 'true' : 'false' }};
        var ep3 = {{ $errors->hasAny(['familia_id','apellido_familia']) ? 'true' : 'false' }};
        var ep2 = {{ $errors->hasAny(['ciclo_id','nivel_id','grupo_id','fecha_inscripcion']) ? 'true' : 'false' }};
        if      (ep4) irAPaso(4);
        else if (ep3) irAPaso(3);
        else if (ep2) irAPaso(2);
        else          irAPaso(1);
    })();
    @endif

    if (jQuery('#ciclo_id').val() && jQuery('#nivel_id').val()) {
        cargarGrupos();
    }

    // Familia: mostrar/ocultar al cargar
    toggleFamilia();
}

// Ejecutar cuando el DOM esté completamente listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWizard);
} else {
    initWizard();
}

// ═══════════════════════════════════════════════════════
// NAVEGACIÓN
// ═══════════════════════════════════════════════════════

function actualizarWizard() {
    // Mostrar solo el panel activo
    document.querySelectorAll('.wizard-panel').forEach(function(p) {
        p.style.display = 'none';
    });
    var panelActivo = document.getElementById('paso-' + pasoActual);
    if (panelActivo) panelActivo.style.display = 'block';

    // Actualizar indicadores de paso
    document.querySelectorAll('#wizard-steps li').forEach(function(li) {
        var s = parseInt(li.getAttribute('data-step'));
        li.classList.remove('active', 'done');
        var numEl = li.querySelector('.step-num');
        if (s === pasoActual) {
            li.classList.add('active');
            numEl.textContent = s;
        } else if (s < pasoActual) {
            li.classList.add('done');
            numEl.innerHTML = '<i class="fa fa-check"></i>';
        } else {
            numEl.textContent = s;
        }
    });

    // Botones
    var btnAnt = document.getElementById('btn-anterior');
    var btnSig = document.getElementById('btn-siguiente');
    var btnGrd = document.getElementById('btn-guardar');
    if (btnAnt) btnAnt.style.display = pasoActual > 1 ? 'inline-block' : 'none';
    if (btnSig) btnSig.style.display = pasoActual < TOTAL_PASOS ? 'inline-block' : 'none';
    if (btnGrd) btnGrd.style.display = pasoActual === TOTAL_PASOS ? 'inline-block' : 'none';

    if (pasoActual === 4) actualizarResumen();
}

function irAPaso(n) {
    pasoActual = n;
    actualizarWizard();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Botones — usando addEventListener directo sin jQuery
document.addEventListener('click', function(e) {
    // Botón Siguiente
    if (e.target.id === 'btn-siguiente' || e.target.closest('#btn-siguiente')) {
        e.preventDefault();
        e.stopPropagation();
        if (validarPasoActual()) {
            if (pasoActual < TOTAL_PASOS) irAPaso(pasoActual + 1);
        }
        return false;
    }

    // Botón Anterior
    if (e.target.id === 'btn-anterior' || e.target.closest('#btn-anterior')) {
        e.preventDefault();
        e.stopPropagation();
        if (pasoActual > 1) irAPaso(pasoActual - 1);
        return false;
    }

    // Steps clickeables (solo hacia atrás)
    var stepLi = e.target.closest('#wizard-steps li');
    if (stepLi) {
        var paso = parseInt(stepLi.getAttribute('data-step'));
        if (paso < pasoActual) irAPaso(paso);
    }

    // Eliminar contacto
    if (e.target.closest('.btn-eliminar-contacto')) {
        e.preventDefault();
        if (numContactos <= 1) {
            alert('Debe haber al menos un contacto familiar.');
            return;
        }
        e.target.closest('.contacto-item').remove();
        numContactos--;
        document.getElementById('btn-agregar-contacto').disabled = false;
        renumerarContactos();
    }

    // Agregar contacto
    if (e.target.id === 'btn-agregar-contacto' || e.target.closest('#btn-agregar-contacto')) {
        e.preventDefault();
        if (numContactos >= MAX_CONTACTOS) {
            alert('Máximo ' + MAX_CONTACTOS + ' contactos.');
            return;
        }
        agregarContacto();
    }
});

// ═══════════════════════════════════════════════════════
// VALIDACIÓN POR PASO
// ═══════════════════════════════════════════════════════

function validarPasoActual() {
    if (pasoActual === 1) return validarPaso1();
    if (pasoActual === 2) return validarPaso2();
    if (pasoActual === 3) return validarPaso3();
    return true;
}

function validarPaso1() {
    var ok = true;
    ok = validarCampo('nombre', function(v) {
        if (!v.trim()) return 'El nombre es obligatorio.';
        if (v.trim().length < 2) return 'Mínimo 2 caracteres.';
        return null;
    }) & ok;
    ok = validarCampo('ap_paterno', function(v) {
        if (!v.trim()) return 'El apellido paterno es obligatorio.';
        return null;
    }) & ok;
    ok = validarCampo('fecha_nacimiento', function(v) {
        if (!v) return 'La fecha de nacimiento es obligatoria.';
        var años = (new Date() - new Date(v)) / (1000*60*60*24*365);
        if (años < 2)  return 'El alumno debe tener al menos 2 años.';
        if (años > 25) return 'Verifica la fecha de nacimiento.';
        return null;
    }) & ok;
    var curp = document.getElementById('curp').value.trim();
    if (curp) {
        ok = validarCampo('curp', function(v) {
            if (v.length !== 18) return 'La CURP debe tener exactamente 18 caracteres.';
            return null;
        }) & ok;
    }
    if (!ok) {
        var primero = document.querySelector('#paso-1 .has-error');
        if (primero) primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return !!ok;
}

function validarPaso2() {
    var ok = true;
    ok = validarCampo('ciclo_id',         function(v){ return !v ? 'Selecciona el ciclo escolar.' : null; }) & ok;
    ok = validarCampo('nivel_id',         function(v){ return !v ? 'Selecciona el nivel.'         : null; }) & ok;
    ok = validarCampo('grupo_id',         function(v){ return !v ? 'Selecciona el grupo.'          : null; }) & ok;
    ok = validarCampo('fecha_inscripcion',function(v){ return !v ? 'La fecha de inscripción es obligatoria.' : null; }) & ok;
    return !!ok;
}

function validarPaso3() {
    var tipo = document.querySelector('input[name="tipo_familia"]:checked');
    tipo = tipo ? tipo.value : 'nueva';
    if (tipo === 'nueva') {
        return validarCampo('apellido_familia', function(v){
            return !v.trim() ? 'El nombre de la familia es obligatorio.' : null;
        });
    }
    return validarCampo('familia_id', function(v){
        return !v ? 'Debes seleccionar una familia.' : null;
    });
}

// ── Helper: marcar error/ok en un campo ──────────────────
function validarCampo(id, fn) {
    var el    = document.getElementById(id);
    if (!el) return true;
    var grupo = el.closest('.form-group');
    if (!grupo) return true;

    // Limpiar estado anterior
    grupo.classList.remove('has-error', 'has-success');
    var viejo = grupo.querySelector('.help-block.val-msg');
    if (viejo) viejo.remove();

    var error = fn(el.value || '');
    if (error) {
        grupo.classList.add('has-error');
        var span = document.createElement('span');
        span.className = 'help-block val-msg';
        span.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + error;
        grupo.appendChild(span);
        return false;
    }
    grupo.classList.add('has-success');
    return true;
}

// Limpiar error al escribir
document.addEventListener('input', function(e) {
    var grupo = e.target.closest('.form-group');
    if (grupo && grupo.classList.contains('has-error')) {
        grupo.classList.remove('has-error', 'has-success');
        var msg = grupo.querySelector('.val-msg');
        if (msg) msg.remove();
    }
});
document.addEventListener('change', function(e) {
    var grupo = e.target.closest('.form-group');
    if (grupo && grupo.classList.contains('has-error')) {
        grupo.classList.remove('has-error', 'has-success');
        var msg = grupo.querySelector('.val-msg');
        if (msg) msg.remove();
    }
});

// ═══════════════════════════════════════════════════════
// FOTO — preview
// ═══════════════════════════════════════════════════════

document.addEventListener('change', function(e) {
    if (e.target.id !== 'foto') return;
    var archivo = e.target.files[0];
    if (!archivo) return;
    if (archivo.size > MAX_FOTO_MB) {
        e.target.value = '';
        alert('El archivo pesa ' + (archivo.size/1024/1024).toFixed(2) + ' MB. Máximo: 2 MB.');
        return;
    }
    var reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('foto-preview-wrap').innerHTML =
            '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;" alt="preview">';
    };
    reader.readAsDataURL(archivo);
});

// Click en preview abre selector
document.addEventListener('click', function(e) {
    if (e.target.closest('#foto-preview-wrap') && e.target.id !== 'foto') {
        document.getElementById('foto').click();
    }
});

// ═══════════════════════════════════════════════════════
// CURP — contador
// ═══════════════════════════════════════════════════════

document.addEventListener('input', function(e) {
    if (e.target.id !== 'curp') return;
    e.target.value = e.target.value.toUpperCase();
    var len = e.target.value.length;
    var chars = document.getElementById('curp-chars');
    var lbl   = document.getElementById('curp-chars-lbl');
    if (chars) chars.textContent = len;
    if (lbl) lbl.style.color = len === 18 ? '#00a65a' : len > 0 ? '#f39c12' : '#999';
});

// ═══════════════════════════════════════════════════════
// INSCRIPCIÓN — grupos dinámicos
// ═══════════════════════════════════════════════════════

document.addEventListener('change', function(e) {
    if (e.target.id === 'ciclo_id' || e.target.id === 'nivel_id') {
        cargarGrupos();
    }
});

function cargarGrupos() {
    var cicloId = document.getElementById('ciclo_id').value;
    var nivelId = document.getElementById('nivel_id').value;
    var grupoEl = document.getElementById('grupo_id');
    var grupoViejo = '{{ old("grupo_id") }}';

    if (!cicloId || !nivelId) {
        grupoEl.innerHTML = '<option value="">-- Primero selecciona ciclo y nivel --</option>';
        return;
    }

    grupoEl.innerHTML = '<option value="">Cargando...</option>';
    grupoEl.disabled  = true;

    jQuery.ajax({
        url: '/grupos',
        method: 'GET',
        data: { ciclo_id: cicloId, nivel_id: nivelId },
        success: function(resp) {
            var html = '<option value="">-- Seleccionar grupo --</option>';
            if (!resp.length) {
                html = '<option value="">Sin grupos disponibles</option>';
            } else {
                resp.forEach(function(g) {
                    var cap   = g.cupo_maximo ? g.alumnos_inscritos+'/'+g.cupo_maximo : g.alumnos_inscritos+' inscritos';
                    var lleno = g.cupo_maximo && g.alumnos_inscritos >= g.cupo_maximo ? ' [LLENO]' : '';
                    var sel   = String(g.id) === String(grupoViejo) ? 'selected' : '';
                    html += '<option value="'+g.id+'" '+sel+'>'+g.grado.nombre+'° '+g.nombre+' ('+cap+')'+lleno+'</option>';
                });
            }
            grupoEl.innerHTML = html;
            grupoEl.disabled  = false;
        },
        error: function() {
            grupoEl.innerHTML = '<option value="">Error al cargar grupos</option>';
            grupoEl.disabled  = false;
        }
    });
}

// ═══════════════════════════════════════════════════════
// FAMILIA
// ═══════════════════════════════════════════════════════

function toggleFamilia() {
    var radios = document.querySelectorAll('input[name="tipo_familia"]');
    var tipo   = 'nueva';
    radios.forEach(function(r){ if (r.checked) tipo = r.value; });

    var nueva    = document.getElementById('bloque-familia-nueva');
    var existente = document.getElementById('bloque-familia-existente');
    if (!nueva || !existente) return;

    if (tipo === 'nueva') {
        nueva.style.display     = 'block';
        existente.style.display = 'none';
    } else {
        nueva.style.display     = 'none';
        existente.style.display = 'block';
    }
}

document.addEventListener('change', function(e) {
    if (e.target.name === 'tipo_familia') toggleFamilia();
});

// ═══════════════════════════════════════════════════════
// CONTACTOS
// ═══════════════════════════════════════════════════════

function agregarContacto() {
    if (numContactos >= MAX_CONTACTOS) return;

    var idx = numContactos;
    var num = numContactos + 1;
    var tpl = document.getElementById('template-contacto').innerHTML;

    tpl = tpl.replace(/__INDEX__/g, idx)
             .replace(/__NUM__/g,   num)
             .replace('__ORDEN1__', num === 1 ? 'selected' : '')
             .replace('__ORDEN2__', num === 2 ? 'selected' : '')
             .replace('__ORDEN3__', num === 3 ? 'selected' : '')
             .replace('__RECOGER__', num === 1 ? 'checked' : '')
             .replace('__PAGO__',    num === 1 ? 'checked' : '');

    var contenedor = document.getElementById('contenedor-contactos');
    var div = document.createElement('div');
    div.innerHTML = tpl;
    contenedor.appendChild(div.firstElementChild);

    numContactos++;
    var btnAgregar = document.getElementById('btn-agregar-contacto');
    if (btnAgregar) btnAgregar.disabled = numContactos >= MAX_CONTACTOS;
}

function renumerarContactos() {
    var items = document.querySelectorAll('.contacto-item');
    items.forEach(function(item, i) {
        var numEl = item.querySelector('.num-contacto');
        if (numEl) numEl.textContent = i + 1;
    });
}

// Validación blur en contactos
document.addEventListener('blur', function(e) {
    if (e.target.classList.contains('inp-nombre-contacto')) {
        var g = e.target.closest('.form-group');
        g.classList.remove('has-error','has-success');
        var m = g.querySelector('.val-msg'); if (m) m.remove();
        if (!e.target.value.trim()) {
            g.classList.add('has-error');
            g.insertAdjacentHTML('beforeend','<span class="help-block val-msg"><i class="fa fa-exclamation-circle"></i> Obligatorio.</span>');
        } else { g.classList.add('has-success'); }
    }

    if (e.target.classList.contains('inp-tel-contacto')) {
        var g = e.target.closest('.form-group');
        var tel = e.target.value.trim().replace(/[\s-]/g,'');
        g.classList.remove('has-error','has-success');
        var m = g.querySelector('.val-msg'); if (m) m.remove();
        if (!tel) {
            g.classList.add('has-error');
            g.insertAdjacentHTML('beforeend','<span class="help-block val-msg"><i class="fa fa-exclamation-circle"></i> El teléfono es obligatorio.</span>');
        } else if (!/^\d{10}$/.test(tel)) {
            g.classList.add('has-error');
            g.insertAdjacentHTML('beforeend','<span class="help-block val-msg"><i class="fa fa-exclamation-circle"></i> Deben ser 10 dígitos.</span>');
        } else { g.classList.add('has-success'); }
    }

    if (e.target.classList.contains('inp-email-contacto')) {
        var g = e.target.closest('.form-group');
        var email = e.target.value.trim();
        g.classList.remove('has-error','has-success');
        var m = g.querySelector('.val-msg'); if (m) m.remove();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            g.classList.add('has-error');
            g.insertAdjacentHTML('beforeend','<span class="help-block val-msg"><i class="fa fa-exclamation-circle"></i> Correo inválido.</span>');
        } else if (email) { g.classList.add('has-success'); }
    }
}, true); // true = capture phase para que funcione en elementos dinámicos

// ═══════════════════════════════════════════════════════
// RESUMEN (paso 4)
// ═══════════════════════════════════════════════════════

function actualizarResumen() {
    var nombre = [
        document.getElementById('nombre')?.value,
        document.getElementById('ap_paterno')?.value,
        document.getElementById('ap_materno')?.value
    ].filter(Boolean).join(' ');

    setText('rsm-nombre', nombre || '—');
    setText('rsm-fnac',   document.getElementById('fecha_nacimiento')?.value || '—');
    setText('rsm-curp',   document.getElementById('curp')?.value || '—');

    var cicloSel = document.getElementById('ciclo_id');
    setText('rsm-ciclo', cicloSel?.options[cicloSel.selectedIndex]?.text || '—');

    var grupoSel = document.getElementById('grupo_id');
    setText('rsm-grupo', grupoSel?.options[grupoSel.selectedIndex]?.text || '—');

    var tipo = document.querySelector('input[name="tipo_familia"]:checked');
    tipo = tipo ? tipo.value : 'nueva';
    var familia = tipo === 'nueva'
        ? (document.getElementById('apellido_familia')?.value || '(Nueva)')
        : (function(){
            var s = document.getElementById('familia_id');
            return s?.options[s.selectedIndex]?.text || '—';
          })();
    setText('rsm-familia', familia);
}

function setText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
}

// ═══════════════════════════════════════════════════════
// SUBMIT — escuchar el evento submit del FORM, no el click
// del botón. Así capturamos el envío sin importar si el
// botón estaba oculto o visible cuando se registró el listener.
// ═══════════════════════════════════════════════════════

document.getElementById('form-alumno').addEventListener('submit', function(e) {
    // Solo validar si estamos en el paso 4 (cuando btn-guardar es visible)
    if (pasoActual !== TOTAL_PASOS) {
        e.preventDefault();
        return false;
    }

    var items = document.querySelectorAll('#contenedor-contactos .contacto-item');

    if (items.length === 0) {
        e.preventDefault();
        alert('Debes agregar al menos un contacto familiar.');
        return false;
    }

    var hayError = false;
    items.forEach(function(item) {
        var nombreEl = item.querySelector('.inp-nombre-contacto');
        var telEl    = item.querySelector('.inp-tel-contacto');
        var nVal = nombreEl ? nombreEl.value.trim() : '';
        var tVal = telEl    ? telEl.value.trim()    : '';

        if (!nVal && nombreEl) {
            hayError = true;
            var g = nombreEl.closest('.form-group');
            g.classList.add('has-error');
            if (!g.querySelector('.val-msg')) {
                g.insertAdjacentHTML('beforeend',
                    '<span class="help-block val-msg"><i class="fa fa-exclamation-circle"></i> El nombre es obligatorio.</span>');
            }
        }
        if (!tVal && telEl) {
            hayError = true;
            var g = telEl.closest('.form-group');
            g.classList.add('has-error');
            if (!g.querySelector('.val-msg')) {
                g.insertAdjacentHTML('beforeend',
                    '<span class="help-block val-msg"><i class="fa fa-exclamation-circle"></i> El teléfono es obligatorio.</span>');
            }
        }
    });

    if (hayError) {
        e.preventDefault();
        var primerError = document.querySelector('#contenedor-contactos .has-error');
        if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    // Todo OK — deshabilitar botón para evitar doble envío
    var btnGuardar = document.getElementById('btn-guardar');
    if (btnGuardar) {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Registrando...';
    }
});
</script>
@endpush
