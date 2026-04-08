@extends('layouts.master')

@section('page_title', 'Registrar alumno')
@section('page_subtitle', 'Nuevo ingreso')

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
@endsection

@push('styles')
    <style>
        .wizard-step-trigger.is-active {
            border-color: #3c8dbc;
            background: #f7fbfe;
            box-shadow: inset 0 0 0 1px rgba(60, 141, 188, 0.15);
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

        @media (max-width: 768px) {

            #wizard-steps-nav .col-sm-6,
            #wizard-steps-nav .col-lg-3 {
                width: 25%;
                float: left;
                padding: 4px;
            }

            .wizard-step-trigger {
                padding: 6px !important;
                min-height: auto !important;
                text-align: center !important;
                font-size: 11px;
            }

            .wizard-step-trigger span:last-child {
                display: none;
                /* oculta descripción */
            }

            .wizard-step-badge {
                width: 26px !important;
                height: 26px !important;
                font-size: 12px;
                margin: 0 auto 4px;
            }
        }

        @media (max-width: 768px) {
            .wizard-step-trigger {
                opacity: 0.5;
            }

            .wizard-step-trigger.is-active {
                opacity: 1;
                transform: scale(1.05);
            }
        }
    </style>
@endpush

@section('content')
    @php
        $pasosWizard = [
            1 => ['titulo' => 'Datos personales', 'descripcion' => 'Información básica del alumno'],
            2 => ['titulo' => 'Inscripción', 'descripcion' => 'Ciclo escolar, nivel y grupo'],
            3 => ['titulo' => 'Familia', 'descripcion' => 'Vinculación familiar y admisiones'],
            4 => ['titulo' => 'Contactos familiares', 'descripcion' => 'Responsables y autorizados'],
        ];
        $alumnoPrecargado = $datosPrecargados['alumno'] ?? [];
        $contactosPrecargados = old('contactos', $datosPrecargados['contactos'] ?? []);
        $prospectoIdInicial = old('prospecto_id', $alumnoPrecargado['prospecto_id'] ?? '');
    @endphp

    <form method="POST" action="{{ route('alumnos.store') }}" enctype="multipart/form-data" id="form-alumno" novalidate>
        @csrf

        <div class="box box-primary">
            <div class="box-body">
                <div style="background:#f4f4f4;border-radius:999px;height:8px;overflow:hidden;margin-bottom:18px;">
                    <div id="wizard-progress-bar" style="background:#3c8dbc;height:8px;width:25%;transition:width .2s ease;">
                    </div>
                    <input type="file" name="foto" id="foto"
                           accept="image/jpeg,image/png,image/webp"
                           style="display:none">
                    <small class="text-muted" style="font-size:11px;">JPG PNG WEBP · Máx 2MB</small>
                    @error('foto')
                        <span class="help-block text-red" style="font-size:11px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row" id="wizard-steps-nav">
                    @foreach ($pasosWizard as $numero => $paso)
                        <div class="col-sm-6 col-lg-3" style="margin-bottom:12px;">
                            <button type="button" class="btn btn-default btn-block text-left wizard-step-trigger"
                                data-step="{{ $numero }}"
                                onclick="window.alumnoWizardIrPaso({{ $numero }}); return false;"
                                style="min-height:78px;white-space:normal;border-width:2px;">
                                <div style="display:flex;align-items:flex-start;gap:10px;">
                                    <span class="wizard-step-badge"
                                        style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#f4f4f4;color:#3c8dbc;font-weight:700;flex-shrink:0;">{{ $numero }}</span>
                                    <span>
                                        <span style="display:block;font-weight:700;color:#222;">Paso {{ $numero }}:
                                            {{ $paso['titulo'] }}</span>
                                        <span class="text-muted"
                                            style="display:block;font-size:12px;margin-top:4px;">{{ $paso['descripcion'] }}</span>
                                    </span>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row">

            {{-- ----------------------------------------------
         COLUMNA IZQUIERDA - Datos del alumno
    ---------------------------------------------- --}}

            <div class="col-md-8">

                {{-- -- Datos personales -- --}}
                <div class="box box-primary wizard-step-panel" data-step="1">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-user"></i> Paso 1: Datos personales
                        </h3>
                    </div>
                    <div class="box-body">

                        {{-- Nombre completo --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                    <label for="nombre">Nombre(s) <span class="text-red">*</span></label>
                                    <input type="text" name="nombre" id="nombre" class="form-control"
                                        placeholder="Ej: Juan Carlos"
                                        value="{{ old('nombre', $alumnoPrecargado['nombre'] ?? '') }}" maxlength="100">
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
                                        placeholder="Ej: López"
                                        value="{{ old('ap_paterno', $alumnoPrecargado['ap_paterno'] ?? '') }}"
                                        maxlength="100">
                                    @error('ap_paterno')
                                        <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('ap_materno') ? 'has-error' : '' }}">
                                    <label for="ap_materno">Apellido materno</label>
                                    <input type="text" name="ap_materno" id="ap_materno" class="form-control"
                                        placeholder="Ej: García"
                                        value="{{ old('ap_materno', $alumnoPrecargado['ap_materno'] ?? '') }}"
                                        maxlength="100">
                                    @error('ap_materno')
                                        <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('fecha_nacimiento') ? 'has-error' : '' }}">
                                    <label for="fecha_nacimiento">Fecha de nacimiento <span
                                            class="text-red">*</span></label>
                                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control"
                                        value="{{ old('fecha_nacimiento', $alumnoPrecargado['fecha_nacimiento'] ?? '') }}"
                                        max="{{ now()->subYears(2)->format('Y-m-d') }}">
                                    @error('fecha_nacimiento')
                                        <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('genero') ? 'has-error' : '' }}">
                                    <label for="genero">Género</label>
                                    <select name="genero" id="genero" class="form-control">
                                        <option value="">-- Seleccionar --</option>
                                        <option value="M"
                                            {{ old('genero', $alumnoPrecargado['genero'] ?? '') === 'M' ? 'selected' : '' }}>
                                            Masculino</option>
                                        <option value="F"
                                            {{ old('genero', $alumnoPrecargado['genero'] ?? '') === 'F' ? 'selected' : '' }}>
                                            Femenino</option>
                                        <option value="Otro"
                                            {{ old('genero', $alumnoPrecargado['genero'] ?? '') === 'Otro' ? 'selected' : '' }}>
                                            Otro</option>
                                    </select>
                                    @error('genero')
                                        <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('fecha_inscripcion') ? 'has-error' : '' }}">
                                    <label for="fecha_inscripcion">Fecha de inscripción <span
                                            class="text-red">*</span></label>
                                    <input type="date" name="fecha_inscripcion" id="fecha_inscripcion"
                                        class="form-control"
                                        value="{{ old('fecha_inscripcion', $alumnoPrecargado['fecha_inscripcion'] ?? now()->format('Y-m-d')) }}">
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
                                    <label for="curp">CURP</label>
                                    <input type="text" name="curp" id="curp" class="form-control"
                                        placeholder="18 caracteres"
                                        value="{{ old('curp', $alumnoPrecargado['curp'] ?? '') }}" maxlength="18"
                                        style="text-transform:uppercase">
                                    <span class="help-block" id="curp-contador" style="color:#999;">
                                        <span id="curp-chars">0</span>/18 caracteres
                                    </span>
                                    @error('curp')
                                        <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                                    <label>Foto del alumno</label>
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                            <label class="btn btn-default btn-flat" for="foto"
                                                style="margin:0;cursor:pointer;">
                                                <i class="fa fa-camera"></i> Seleccionar
                                            </label>
                                        </span>
                                        <input type="text" id="foto-nombre" class="form-control"
                                            placeholder="Sin archivo" readonly>
                                    </div>
                                    <input type="file" name="foto" id="foto"
                                        accept="image/jpeg,image/png,image/webp" style="display:none">
                                    <span class="help-block">JPG, PNG o WEBP. Máx. 2 MB.</span>
                                    @error('foto')
                                        <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}">
                            <label for="observaciones">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="2"
                                placeholder="Notas adicionales sobre el alumno (opcional)" maxlength="1000">{{ old('observaciones', $alumnoPrecargado['observaciones'] ?? '') }}</textarea>
                        </div>

                    </div>{{-- /.box-body --}}
                </div>{{-- /.box --}}

                {{-- -- Inscripción -- --}}
                <div class="box box-primary wizard-step-panel" data-step="2" style="display:none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-graduation-cap"></i> Paso 2: Inscripción
                        </h3>
                    </div>
                    <div class="box-body">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                                    <label for="ciclo_id">Ciclo escolar <span class="text-red">*</span></label>
                                    <select name="ciclo_id" id="ciclo_id" class="form-control">
                                        <option value="">-- Seleccionar ciclo --</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}"
                                                {{ old('ciclo_id', $alumnoPrecargado['ciclo_id'] ?? $cicloId) == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                                @if ($ciclo->estado === 'activo')
                                                    (Activo)
                                                @endif
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
                                    <label for="nivel_id">Nivel <span class="text-red">*</span></label>
                                    <select name="nivel_id" id="nivel_id" class="form-control">
                                        <option value="">-- Seleccionar nivel --</option>
                                        @foreach ($niveles as $nivel)
                                            <option value="{{ $nivel->id }}"
                                                {{ old('nivel_id', $alumnoPrecargado['nivel_id'] ?? '') == $nivel->id ? 'selected' : '' }}>
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
                                    <label for="grupo_id">Grupo <span class="text-red">*</span></label>
                                    <select name="grupo_id" id="grupo_id" class="form-control">
                                        <option value="">-- Primero selecciona nivel --</option>
                                    </select>
                                    @error('grupo_id')
                                        <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                            {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>{{-- /.box-body --}}
                </div>{{-- /.box --}}

                {{-- -- Contactos familiares -- --}}
                <div class="box box-primary wizard-step-panel" data-step="4" style="display:none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-phone"></i> Paso 4: Contactos familiares
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-sm" id="btn-agregar-contacto">
                                <i class="fa fa-plus"></i> Agregar contacto
                            </button>
                        </div>
                    </div>
                    <div class="box-body">

                        <p class="text-muted" style="margin-bottom:12px;">
                            <i class="fa fa-info-circle"></i>
                            Agrega al menos un contacto. Máximo 3. El primero será el contacto principal.
                        </p>

                        <div id="contenedor-contactos">
                            {{-- El primer contacto se genera automáticamente --}}
                        </div>

                        @error('contactos')
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-circle"></i> {{ $message }}
                            </div>
                        @enderror

                    </div>{{-- /.box-body --}}
                </div>{{-- /.box --}}

                {{-- -- Familia -- --}}
                <div class="box box-warning wizard-step-panel" data-step="3" style="display:none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-home"></i> Paso 3: Familia
                        </h3>
                    </div>
                    <div class="box-body">

                        <div class="form-group">
                            <label>¿El alumno tiene hermanos inscritos?</label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="tipo_familia" value="nueva"
                                        {{ old('tipo_familia', 'nueva') === 'nueva' ? 'checked' : '' }}>
                                    No, es familia nueva
                                </label>
                            </div>
                            <div style="margin-top:6px;">
                                <label class="radio-inline">
                                    <input type="radio" name="tipo_familia" value="existente"
                                        {{ old('tipo_familia') === 'existente' ? 'checked' : '' }}>
                                    Si, vincular a familia existente
                                </label>
                            </div>
                        </div>

                        <div id="bloque-familia-nueva">
                            <div class="form-group {{ $errors->has('apellido_familia') ? 'has-error' : '' }}">
                                <label for="apellido_familia">Nombre de la familia <span class="text-red">*</span></label>
                                <input type="text" name="apellido_familia" id="apellido_familia" class="form-control"
                                    placeholder="Ej: Familia Lopez Garcia"
                                    value="{{ old('apellido_familia', $datosPrecargados['apellido_familia'] ?? '') }}"
                                    maxlength="200">
                                @error('apellido_familia')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div id="bloque-familia-existente" style="display:none;">
                            <div class="form-group {{ $errors->has('familia_id') ? 'has-error' : '' }}">
                                <label for="familia_id">Seleccionar familia <span class="text-red">*</span></label>
                                <select name="familia_id" id="familia_id" class="form-control">
                                    <option value="">-- Buscar familia --</option>
                                    @foreach ($familias as $familia)
                                        <option value="{{ $familia->id }}"
                                            {{ old('familia_id') == $familia->id ? 'selected' : '' }}>
                                            {{ $familia->apellido_familia }}
                                            ({{ $familia->alumnos_count ?? 0 }} alumno(s))
                                        </option>
                                    @endforeach
                                </select>
                                @error('familia_id')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i>
                                        {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                    </div>{{-- /.box-body --}}
                </div>{{-- /.box --}}

                {{-- -- Prospecto de admisiones -- --}}
                <div class="box box-default collapsed-box wizard-step-panel" data-step="3" style="display:none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-user-plus"></i> Complemento de admisiones
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="form-group {{ $errors->has('prospecto_id') ? 'has-error' : '' }}">
                            <label for="prospecto_id">Numero de prospecto</label>
                            <input type="number" name="prospecto_id" id="prospecto_id" class="form-control"
                                placeholder="ID del prospecto en admisiones" value="{{ $prospectoIdInicial }}"
                                min="1">
                            <span class="help-block">
                                Opcional. Si se especifica, el prospecto cambia a "inscrito" automaticamente.
                            </span>
                        </div>
                    </div>
                </div>
            </div>{{-- /.col-md-8 --}}

            {{-- ----------------------------------------------
         COLUMNA DERECHA, Familia y acciones
    ---------------------------------------------- --}}
            <div class="col-md-4">



                {{-- -- Navegación del wizard -- --}}
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list-ol"></i> Progreso del registro
                        </h3>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" id="wizard-step-description" style="margin-bottom:15px;">
                            Paso 1 de 4: completa los datos personales del alumno.
                        </p>

                        <ul class="list-unstyled" style="margin:0 0 15px;">
                            @foreach ($pasosWizard as $numero => $paso)
                                <li class="wizard-summary-item" data-step="{{ $numero }}"
                                    style="padding:8px 0;border-bottom:1px solid #f0f0f0;">
                                    <strong>Paso {{ $numero }}</strong><br>
                                    <span class="text-muted">{{ $paso['titulo'] }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <button type="button" class="btn btn-default btn-block" id="btn-paso-anterior"
                            onclick="window.alumnoWizardIrPaso(window.alumnoWizardPasoActual() - 1); return false;">
                            <i class="fa fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn-primary btn-block" id="btn-paso-siguiente"
                            onclick="window.alumnoWizardIrPaso(window.alumnoWizardPasoActual() + 1); return false;">
                            Siguiente <i class="fa fa-arrow-right"></i>
                        </button>
                        <button type="submit" class="btn btn-success btn-block btn-lg" id="btn-guardar"
                            style="display:none;">
                            <i class="fa fa-save"></i> Registrar alumno
                        </button>
                        <a href="{{ route('alumnos.index') }}" class="btn btn-default btn-block">
                            <i class="fa fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>

                @if (session('error'))
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-times-circle"></i> Error al guardar
                            </h3>
                        </div>
                        <div class="box-body">
                            <p style="margin:0; color:#a94442;">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{-- -- Indicador de errores -- --}}
                @if ($errors->any())
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">
                                <i class="fa fa-exclamation-triangle"></i> Corrige los siguientes errores
                            </h3>
                        </div>
                        <div class="box-body">
                            <ul style="padding-left:18px; margin:0;">
                                @foreach ($errors->all() as $error)
                                    <li style="color:#a94442; font-size:12px;">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

            </div>{{-- /.col-md-4 --}}

        </div>{{-- /.row --}}
    </form>

    {{-- -- Template oculto para contacto -- --}}
    <div id="template-contacto" style="display:none;">
        <div class="contacto-item panel panel-default" data-index="__INDEX__">
            <div class="panel-heading">
                <h4 class="panel-title" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="contacto-titulo">Contacto #<span class="num-contacto">__NUM__</span></span>
                    <button type="button" class="btn btn-danger btn-xs btn-eliminar-contacto">
                        <i class="fa fa-trash"></i> Eliminar
                    </button>
                </h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Nombre(s) <span class="text-red">*</span></label>
                            <input type="text" name="contactos[__INDEX__][nombre]" class="form-control"
                                placeholder="Nombre(s)" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido paterno</label>
                            <input type="text" name="contactos[__INDEX__][ap_paterno]" class="form-control"
                                placeholder="Apellido paterno" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Apellido materno</label>
                            <input type="text" name="contactos[__INDEX__][ap_materno]" class="form-control"
                                placeholder="Apellido materno" maxlength="100">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Teléfono celular <span class="text-red">*</span></label>
                            <input type="tel" name="contactos[__INDEX__][telefono_celular]" class="form-control"
                                placeholder="10 dígitos" maxlength="10">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" name="contactos[__INDEX__][email]" class="form-control"
                                placeholder="correo@ejemplo.com" maxlength="200">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CURP</label>
                            <input type="text" name="contactos[__INDEX__][curp]" class="form-control"
                                placeholder="18 caracteres" maxlength="18" style="text-transform:uppercase">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Parentesco <span class="text-red">*</span></label>
                            <select name="contactos[__INDEX__][parentesco]" class="form-control">
                                <option value="">-- Seleccionar --</option>
                                <option value="padre">Padre</option>
                                <option value="madre">Madre</option>
                                <option value="abuelo">Abuelo/a</option>
                                <option value="tio">Tío/a</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo <span class="text-red">*</span></label>
                            <select name="contactos[__INDEX__][tipo]" class="form-control">
                                <option value="">-- Seleccionar --</option>
                                <option value="padre">Padre/Madre</option>
                                <option value="tutor">Tutor</option>
                                <option value="tercero_autorizado">Tercero autorizado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Orden</label>
                            <select name="contactos[__INDEX__][orden]" class="form-control">
                                <option value="1" __ORDEN1__>1 - Principal</option>
                                <option value="2" __ORDEN2__>2 - Secundario</option>
                                <option value="3" __ORDEN3__>3 - Tercero</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="contactos[__INDEX__][autorizado_recoger]" value="0">
                                <input type="checkbox" name="contactos[__INDEX__][autorizado_recoger]" value="1"
                                    __RECOGER__>
                                Autorizado para recoger
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="contactos[__INDEX__][es_responsable_pago]" value="0">
                                <input type="checkbox" name="contactos[__INDEX__][es_responsable_pago]" value="1"
                                    __PAGO__>
                                Responsable de pagos
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="contactos[__INDEX__][tiene_acceso_portal]" value="0">
                                <input type="checkbox" name="contactos[__INDEX__][tiene_acceso_portal]" value="1">
                                Acceso al portal
                            </label>
                        </div>
                    </div>
                </div>
            </div>{{-- /.panel-body --}}
        </div>{{-- /.contacto-item --}}
    </div>{{-- /#template-contacto --}}

@endsection

@push('scripts')
    <script>
        var MAX_CONTACTOS = 3;
        var MAX_FOTO_BYTES = 2 * 1024 * 1024; // 2 MB
        var TOTAL_PASOS = 4;
        var PASOS_DESCRIPCION = {
            1: 'Paso 1 de 4: completa los datos personales del alumno.',
            2: 'Paso 2 de 4: selecciona la información de inscripción.',
            3: 'Paso 3 de 4: define la familia y el vínculo con admisiones.',
            4: 'Paso 4 de 4: captura los contactos familiares.',
        };
        var numContactos = 0;
        var pasoActual = 1;
        var contactosIniciales = @json($contactosPrecargados);

        $(document).ready(function() {
            if (contactosIniciales.length) {
                contactosIniciales.slice(0, MAX_CONTACTOS).forEach(function(contacto) {
                    agregarContacto(contacto);
                });
            } else {
                agregarContacto();
            }

            $('#curp').trigger('input');


            window.alumnoWizardIrPaso(obtenerPasoConError() || 1, false);
        });

        window.alumnoWizardIrPaso = function(paso, hacerScroll) {
            var porcentaje;

            if (typeof hacerScroll === 'undefined') {
                hacerScroll = true;
            }

            if (paso > pasoActual) {
                if (!validarPaso(pasoActual)) {
                    alert('Completa correctamente los campos del paso ' + pasoActual + ' antes de continuar.');
                    return;

                }
            }
            pasoActual = Math.min(Math.max(paso, 1), TOTAL_PASOS);

            $('.wizard-step-panel').hide();
            $('.wizard-step-panel[data-step="' + pasoActual + '"]').show();

            $('.wizard-step-trigger').each(function() {
                var step = Number($(this).data('step'));
                $(this)
                    .toggleClass('is-active', step === pasoActual)
                    .toggleClass('is-complete', step < pasoActual);
            });

            $('.wizard-summary-item').each(function() {
                var step = Number($(this).data('step'));
                $(this).toggleClass('is-active', step === pasoActual);
            });

            porcentaje = (pasoActual / TOTAL_PASOS) * 100;
            $('#wizard-progress-bar').css('width', porcentaje + '%');
            $('#wizard-step-description').text(PASOS_DESCRIPCION[pasoActual]);
            $('#btn-paso-anterior').toggle(pasoActual > 1);
            $('#btn-paso-siguiente').toggle(pasoActual < TOTAL_PASOS);
            $('#btn-guardar').toggle(pasoActual === TOTAL_PASOS);

            if (hacerScroll) {
                $('html, body').animate({
                    scrollTop: $('#wizard-steps-nav').offset().top - 80
                }, 200);
            }
        };

        window.alumnoWizardPasoActual = function() {
            return pasoActual;
        };

        function obtenerPasoConError() {
            var $primerError = $('.has-error').first();
            var $panel;

            if (!$primerError.length) {
                return null;
            }

            $panel = $primerError.closest('.wizard-step-panel');

            if (!$panel.length) {
                return null;
            }

            return Number($panel.data('step'));
        }

        $('#foto').on('change', function() {
            var archivo = this.files[0];

            if (!archivo) {
                $('#foto-nombre').val('');
                return;
            }

            if (archivo.size > MAX_FOTO_BYTES) {
                this.value = '';
                $('#foto-nombre').val('');
                alert('El archivo pesa ' + (archivo.size / 1024 / 1024).toFixed(2) +
                    ' MB.\nEl máximo permitido es 2 MB.');
                return;
            }

            $('#foto-nombre').val(archivo.name);
        });

        $('#curp').on('input', function() {
            $(this).val($(this).val().toUpperCase());
            $('#curp-chars').text($(this).val().length);
        });

        $('input[name="tipo_familia"]').on('change', function() {
            var tipoSeleccionado = $('input[name="tipo_familia"]:checked').val();

            if (tipoSeleccionado === 'existente') {
                $('#bloque-familia-nueva').hide();
                $('#bloque-familia-existente').show();
                $('#apellido_familia').prop('disabled', true).val('');
                $('#familia_id').prop('disabled', false);
            } else {
                $('#bloque-familia-nueva').show();
                $('#bloque-familia-existente').hide();
                $('#apellido_familia').prop('disabled', false);
                $('#familia_id').prop('disabled', true).val('');
            }
        });

        $('input[name="tipo_familia"]:checked').trigger('change');

        $('#ciclo_id, #nivel_id').on('change', function() {
            cargarGrupos();
        });

        function cargarGrupos() {
            var cicloId = $('#ciclo_id').val();
            var nivelId = $('#nivel_id').val();
            var grupoActual = '{{ old('grupo_id') }}';

            if (!cicloId || !nivelId) {
                $('#grupo_id').html('<option value="">-- Primero selecciona ciclo y nivel --</option>');
                return;
            }

            $.ajax({
                url: '/grupos',
                method: 'GET',
                data: {
                    ciclo_id: cicloId,
                    nivel_id: nivelId
                },
                success: function(response) {
                    var opciones = '<option value="">-- Seleccionar grupo --</option>';

                    if (!response.length) {
                        opciones = '<option value="">Sin grupos disponibles</option>';
                    } else {
                        response.forEach(function(grupo) {
                            var disponibles = grupo.cupo_maximo ?
                                grupo.alumnos_inscritos + '/' + grupo.cupo_maximo :
                                grupo.alumnos_inscritos + ' inscritos';
                            var sel = grupo.id == grupoActual ? 'selected' : '';
                            var lleno = grupo.cupo_maximo && grupo.alumnos_inscritos >= grupo
                                .cupo_maximo ?
                                ' [LLENO]' : '';
                            opciones += '<option value="' + grupo.id + '" ' + sel + '>' + grupo.grado
                                .nombre + ' ' + grupo.nombre + ' (' + disponibles + ')' + lleno +
                                '</option>';
                        });
                    }

                    $('#grupo_id').html(opciones);
                },
                error: function() {
                    $('#grupo_id').html('<option value="">Error al cargar grupos</option>');
                }
            });
        }

        if ($('#ciclo_id').val() && $('#nivel_id').val()) {
            cargarGrupos();
        }

        $('#btn-agregar-contacto').on('click', function() {
            if (numContactos >= MAX_CONTACTOS) {
                alert('El máximo de contactos permitidos es ' + MAX_CONTACTOS + '.');
                return;
            }

            agregarContacto();
        });

        function agregarContacto(contactoInicial) {
            var index;
            var num;
            var template;
            var $contacto;

            contactoInicial = contactoInicial || {};

            if (numContactos >= MAX_CONTACTOS) {
                return;
            }

            index = numContactos;
            num = numContactos + 1;
            template = $('#template-contacto').html();

            template = template.replace(/__INDEX__/g, index);
            template = template.replace(/__NUM__/g, num);
            template = template.replace('__ORDEN1__', num === 1 ? 'selected' : '');
            template = template.replace('__ORDEN2__', num === 2 ? 'selected' : '');
            template = template.replace('__ORDEN3__', num === 3 ? 'selected' : '');
            template = template.replace('__RECOGER__', num === 1 ? 'checked' : '');
            template = template.replace('__PAGO__', num === 1 ? 'checked' : '');

            $contacto = $(template);

            Object.entries(contactoInicial).forEach(function(entry) {
                var campo = entry[0];
                var valor = entry[1];
                var $campo = $contacto.find('[name="contactos[' + index + '][' + campo + ']"]');

                if (!$campo.length) {
                    return;
                }

                if ($campo.attr('type') === 'checkbox') {
                    $campo.prop('checked', Boolean(Number(valor)) || valor === true || valor === '1');
                    return;
                }

                $campo.val(valor || '');
            });

            if (!Object.prototype.hasOwnProperty.call(contactoInicial, 'orden')) {
                $contacto.find('[name="contactos[' + index + '][orden]"]').val(String(num));
            }

            $('#contenedor-contactos').append($contacto);
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
            renumerarContactos();
        });

        function actualizarBtnAgregar() {
            $('#btn-agregar-contacto').prop('disabled', numContactos >= MAX_CONTACTOS);
        }

        function renumerarContactos() {
            $('.contacto-item').each(function(i) {
                $(this).find('.num-contacto').text(i + 1);
            });
        }

        function marcarError(selector, mensaje) {
            var $grupo = $(selector).closest('.form-group');

            $grupo.addClass('has-error').removeClass('has-success');

            if (!$grupo.find('.help-block.val-msg').length) {
                $grupo.append('<span class="help-block val-msg"></span>');
            }

            $grupo.find('.help-block.val-msg')
                .html('<i class="fa fa-exclamation-circle"></i> ' + mensaje)
                .show();
        }

        function marcarOk(selector) {
            var $grupo = $(selector).closest('.form-group');
            $grupo.removeClass('has-error').addClass('has-success');
            $grupo.find('.help-block.val-msg').hide();
        }


        function limpiarEstado(selector) {
            var $grupo = $(selector).closest('.form-group');
            $grupo.removeClass('has-error has-success');
            $grupo.find('.help-block.val-msg').hide();
        }

        function validarCampo(selector, fn) {
            var resultado = fn($(selector).val());

            if (resultado) {
                marcarError(selector, resultado);
                return false;
            }

            marcarOk(selector);
            return true;
        }

        var reglas = {
            '#nombre': function(v) {
                if (!v.trim()) return 'El nombre es obligatorio.';
                if (v.trim().length < 2) return 'Mínimo 2 caracteres.';
                return null;
            },
            '#ap_paterno': function(v) {
                if (!v.trim()) return 'El apellido paterno es obligatorio.';
                if (v.trim().length < 2) return 'Mínimo 2 caracteres.';
                return null;
            },
            '#fecha_nacimiento': function(v) {
                var hoy;
                var fecha;
                var anios;

                if (!v) return 'La fecha de nacimiento es obligatoria.';
                hoy = new Date();
                fecha = new Date(v);
                anios = (hoy - fecha) / (1000 * 60 * 60 * 24 * 365);
                if (anios < 2) return 'El alumno debe tener al menos 2 años.';
                if (anios > 25) return 'Verifica la fecha de nacimiento.';
                return null;
            },
            '#fecha_inscripcion': function(v) {
                if (!v) return 'La fecha de inscripción es obligatoria.';
                return null;
            },
            '#ciclo_id': function(v) {
                if (!v) return 'Debe seleccionar el ciclo escolar.';
                return null;
            },
            '#nivel_id': function(v) {
                if (!v) return 'Debe seleccionar el nivel.';
                return null;
            },
            '#grupo_id': function(v) {
                if (!v) return 'Debe seleccionar el grupo.';
                return null;
            },
            '#curp': function(v) {
                if (!v) return null;
                if (v.length !== 18) return 'La CURP debe tener exactamente 18 caracteres.';
                if (!/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9]{2}$/.test(v)) {
                    return 'El formato de la CURP no es válido.';
                }
                return null;
            }
        };

        Object.keys(reglas).forEach(function(selector) {
            $(document).on('blur', selector, function() {
                validarCampo(selector, reglas[selector]);
            });

            $(document).on('input change', selector, function() {
                if ($(selector).closest('.form-group').hasClass('has-error')) {
                    validarCampo(selector, reglas[selector]);
                }
            });
        });

        $('input[name="tipo_familia"]').on('change', function() {
            limpiarEstado('#apellido_familia');
            limpiarEstado('#familia_id');
        });

        $(document).on('blur', '#apellido_familia', function() {
            if ($('input[name="tipo_familia"]:checked').val() !== 'nueva') {
                return;
            }

            if (!$(this).val().trim()) {
                marcarError('#apellido_familia', 'El nombre de la familia es obligatorio.');
            } else {
                marcarOk('#apellido_familia');
            }
        });

        $(document).on('change', '#familia_id', function() {
            if ($('input[name="tipo_familia"]:checked').val() !== 'existente') {
                return;
            }

            if (!$(this).val()) {
                marcarError('#familia_id', 'Debe seleccionar la familia.');
            } else {
                marcarOk('#familia_id');
            }
        });

        $(document).on('blur', '.contacto-item input[name$="[nombre]"]', function() {
            var $input = $(this);
            var $grupo = $input.closest('.form-group');

            if (!$input.val().trim()) {
                $grupo.addClass('has-error');
                if (!$grupo.find('.help-block.val-msg').length) {
                    $grupo.append('<span class="help-block val-msg"></span>');
                }
                $grupo.find('.help-block.val-msg')
                    .html('<i class="fa fa-exclamation-circle"></i> El nombre del contacto es obligatorio.')
                    .show();
            } else {
                $grupo.removeClass('has-error').addClass('has-success');
                $grupo.find('.help-block.val-msg').hide();
            }
        });

        $(document).on('blur', '.contacto-item input[name$="[telefono_celular]"]', function() {
            var $input = $(this);
            var $grupo = $input.closest('.form-group');
            var telefono = $input.val().trim();
            var error = null;

            if (!telefono) {
                error = 'El teléfono es obligatorio.';
            } else if (!/^[0-9]{10}$/.test(telefono.replace(/\s|-/g, ''))) {
                error = 'Debe ser un número de 10 dígitos.';
            }

            if (error) {
                $grupo.addClass('has-error').removeClass('has-success');
                if (!$grupo.find('.help-block.val-msg').length) {
                    $grupo.append('<span class="help-block val-msg"></span>');
                }
                $grupo.find('.help-block.val-msg')
                    .html('<i class="fa fa-exclamation-circle"></i> ' + error)
                    .show();
            } else {
                $grupo.removeClass('has-error').addClass('has-success');
                $grupo.find('.help-block.val-msg').hide();
            }
        });

        $(document).on('blur', '.contacto-item input[name$="[email]"]', function() {
            var $input = $(this);
            var $grupo = $input.closest('.form-group');
            var email = $input.val().trim();

            if (!email) {
                $grupo.removeClass('has-error has-success');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $grupo.addClass('has-error').removeClass('has-success');
                if (!$grupo.find('.help-block.val-msg').length) {
                    $grupo.append('<span class="help-block val-msg"></span>');
                }
                $grupo.find('.help-block.val-msg')
                    .html('<i class="fa fa-exclamation-circle"></i> El formato del correo no es válido.')
                    .show();
            } else {
                $grupo.removeClass('has-error').addClass('has-success');
                $grupo.find('.help-block.val-msg').hide();
            }
        });

        $("#form-alumno").on('submit', function() {
            var tipoFamilia = $("input[name='tipo_familia']:checked").val();

            if (tipoFamilia === 'existente') {
                $("#apellido_familia").prop('disabled', true).val('');
                $("#familia_id").prop('disabled', false);
            } else {
                $("#apellido_familia").prop('disabled', false);
                $("#familia_id").prop('disabled', true).val('');
            }

            $("#btn-guardar").prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
        });

        function validarPaso(paso) {
            var valido = true;

            if (paso === 1) {
                valido &= validarCampo('#nombre', reglas['#nombre']);
                valido &= validarCampo('#ap_paterno', reglas['#ap_paterno']);
                valido &= validarCampo('#fecha_nacimiento', reglas['#fecha_nacimiento']);
                valido &= validarCampo('#genero', function(v) {
                    return v ? null : 'Debe seleccionar el género.';
                });
                valido &= validarCampo('#fecha_inscripcion', reglas['#fecha_inscripcion']);
                valido &= validarCampo('#curp', reglas['#curp']);
            }
            if (paso === 2) {
                valido &= validarCampo('#ciclo_id', reglas['#ciclo_id']);
                valido &= validarCampo('#nivel_id', reglas['#nivel_id']);
                valido &= validarCampo('#grupo_id', reglas['#grupo_id']);
            }
            if (paso === 3) {
                var tipo = $("input[name='tipo_familia']:checked").val();

                if (tipo === 'nueva') {
                    if (!$('#apellido_familia').val().trim()) {
                        marcarError('#apellido_familia', 'El nombre de la familia es obligatorio.');
                        valido = false;
                    } else {
                        marcarOk('#apellido_familia');
                    }
                } else {
                    if (!$('#familia_id').val()) {
                        marcarError('#familia_id', 'Debe seleccionar la familia.');
                        valido = false;
                    } else {
                        marcarOk('#familia_id');
                    }
                }
            }
            if (paso === 4) {
                $('.contacto-item').each(function() {
                    var nombre = $(this).find('input[name$="[nombre]"]').val().trim();
                    var telefono = $(this).find('input[name$="[telefono_celular]"]').val().trim();

                    if (!nombre || !telefono) {
                        valido = false;
                    }
                });
            }

            return !!valido;
        }
    </script>
@endpush
