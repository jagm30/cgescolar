@extends('layouts.master')

@section('page_title', 'Registrar alumno')
@section('page_subtitle', 'Nuevo ingreso')

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
@endsection

@section('content')

<form method="POST"
      action="{{ route('alumnos.store') }}"
      enctype="multipart/form-data"
      id="form-alumno">
@csrf

<div class="row">

    {{-- ══════════════════════════════════════════════
         COLUMNA IZQUIERDA — Datos del alumno
    ══════════════════════════════════════════════ --}}
    <div class="col-md-8">

        {{-- ── Datos personales ── --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user"></i> Datos personales
                </h3>
            </div>
            <div class="box-body">

                {{-- Nombre completo --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                            <label for="nombre">Nombre(s) <span class="text-red">*</span></label>
                            <input type="text"
                                   name="nombre"
                                   id="nombre"
                                   class="form-control"
                                   placeholder="Ej: Juan Carlos"
                                   value="{{ old('nombre') }}"
                                   maxlength="100">
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
                            <input type="text"
                                   name="ap_paterno"
                                   id="ap_paterno"
                                   class="form-control"
                                   placeholder="Ej: López"
                                   value="{{ old('ap_paterno') }}"
                                   maxlength="100">
                            @error('ap_paterno')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('ap_materno') ? 'has-error' : '' }}">
                            <label for="ap_materno">Apellido materno</label>
                            <input type="text"
                                   name="ap_materno"
                                   id="ap_materno"
                                   class="form-control"
                                   placeholder="Ej: García"
                                   value="{{ old('ap_materno') }}"
                                   maxlength="100">
                            @error('ap_materno')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('fecha_nacimiento') ? 'has-error' : '' }}">
                            <label for="fecha_nacimiento">Fecha de nacimiento <span class="text-red">*</span></label>
                            <input type="date"
                                   name="fecha_nacimiento"
                                   id="fecha_nacimiento"
                                   class="form-control"
                                   value="{{ old('fecha_nacimiento') }}"
                                   max="{{ now()->subYears(2)->format('Y-m-d') }}">
                            @error('fecha_nacimiento')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('genero') ? 'has-error' : '' }}">
                            <label for="genero">Género</label>
                            <select name="genero" id="genero" class="form-control">
                                <option value="">-- Seleccionar --</option>
                                <option value="M"    {{ old('genero') === 'M'    ? 'selected' : '' }}>Masculino</option>
                                <option value="F"    {{ old('genero') === 'F'    ? 'selected' : '' }}>Femenino</option>
                                <option value="Otro" {{ old('genero') === 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('genero')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('fecha_inscripcion') ? 'has-error' : '' }}">
                            <label for="fecha_inscripcion">Fecha de inscripción <span class="text-red">*</span></label>
                            <input type="date"
                                   name="fecha_inscripcion"
                                   id="fecha_inscripcion"
                                   class="form-control"
                                   value="{{ old('fecha_inscripcion', now()->format('Y-m-d')) }}">
                            @error('fecha_inscripcion')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('curp') ? 'has-error' : '' }}">
                            <label for="curp">CURP</label>
                            <input type="text"
                                   name="curp"
                                   id="curp"
                                   class="form-control"
                                   placeholder="18 caracteres"
                                   value="{{ old('curp') }}"
                                   maxlength="18"
                                   style="text-transform:uppercase">
                            <span class="help-block" id="curp-contador" style="color:#999;">
                                <span id="curp-chars">0</span>/18 caracteres
                            </span>
                            @error('curp')
                                <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                            <label>Foto del alumno</label>
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <label class="btn btn-default btn-flat" for="foto" style="margin:0;cursor:pointer;">
                                        <i class="fa fa-camera"></i> Seleccionar
                                    </label>
                                </span>
                                <input type="text" id="foto-nombre" class="form-control" placeholder="Sin archivo" readonly>
                            </div>
                            <input type="file" name="foto" id="foto"
                                   accept="image/jpeg,image/png,image/webp"
                                   style="display:none">
                            <span class="help-block">JPG, PNG o WEBP. Máx. 2 MB.</span>
                            @error('foto')
                                <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones"
                              id="observaciones"
                              class="form-control"
                              rows="2"
                              placeholder="Notas adicionales sobre el alumno (opcional)"
                              maxlength="1000">{{ old('observaciones') }}</textarea>
                </div>

            </div>{{-- /.box-body --}}
        </div>{{-- /.box --}}

        {{-- ── Inscripción ── --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-graduation-cap"></i> Inscripción
                </h3>
            </div>
            <div class="box-body">

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

            </div>{{-- /.box-body --}}
        </div>{{-- /.box --}}

        {{-- ── Contactos familiares ── --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-phone"></i> Contactos familiares
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

    </div>{{-- /.col-md-8 --}}

    {{-- ══════════════════════════════════════════════
         COLUMNA DERECHA — Familia y acciones
    ══════════════════════════════════════════════ --}}
    <div class="col-md-4">

        {{-- ── Familia ── --}}
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-home"></i> Familia
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
                            Sí, vincular a familia existente
                        </label>
                    </div>
                </div>

                {{-- Nueva familia --}}
                <div id="bloque-familia-nueva">
                    <div class="form-group {{ $errors->has('apellido_familia') ? 'has-error' : '' }}">
                        <label for="apellido_familia">Nombre de la familia <span class="text-red">*</span></label>
                        <input type="text"
                               name="apellido_familia"
                               id="apellido_familia"
                               class="form-control"
                               placeholder="Ej: Familia López García"
                               value="{{ old('apellido_familia') }}"
                               maxlength="200">
                        @error('apellido_familia')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Familia existente --}}
                <div id="bloque-familia-existente" style="display:none;">
                    <div class="form-group {{ $errors->has('familia_id') ? 'has-error' : '' }}">
                        <label for="familia_id">Seleccionar familia <span class="text-red">*</span></label>
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

            </div>{{-- /.box-body --}}
        </div>{{-- /.box --}}

        {{-- ── Prospecto de admisiones ── --}}
        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user-plus"></i> ¿Viene de admisiones?
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group {{ $errors->has('prospecto_id') ? 'has-error' : '' }}">
                    <label for="prospecto_id">Número de prospecto</label>
                    <input type="number"
                           name="prospecto_id"
                           id="prospecto_id"
                           class="form-control"
                           placeholder="ID del prospecto en admisiones"
                           value="{{ old('prospecto_id') }}"
                           min="1">
                    <span class="help-block">
                        Opcional. Si se especifica, el prospecto cambia a "inscrito" automáticamente.
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Botones de acción ── --}}
        <div class="box box-default">
            <div class="box-body">
                <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-guardar">
                    <i class="fa fa-save"></i> Registrar alumno
                </button>
                <a href="{{ route('alumnos.index') }}" class="btn btn-default btn-block">
                    <i class="fa fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>

        {{-- ── Indicador de errores ── --}}
        @if($errors->any())
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Corrige los siguientes errores
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

    </div>{{-- /.col-md-4 --}}

</div>{{-- /.row --}}
</form>

{{-- ── Template oculto para contacto ── --}}
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
                        <input type="text" name="contactos[__INDEX__][nombre]"
                               class="form-control" placeholder="Nombre(s)" maxlength="100">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Apellido paterno</label>
                        <input type="text" name="contactos[__INDEX__][ap_paterno]"
                               class="form-control" placeholder="Apellido paterno" maxlength="100">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Apellido materno</label>
                        <input type="text" name="contactos[__INDEX__][ap_materno]"
                               class="form-control" placeholder="Apellido materno" maxlength="100">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Teléfono celular <span class="text-red">*</span></label>
                        <input type="tel" name="contactos[__INDEX__][telefono_celular]"
                               class="form-control" placeholder="10 dígitos" maxlength="20">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="contactos[__INDEX__][email]"
                               class="form-control" placeholder="correo@ejemplo.com" maxlength="200">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>CURP</label>
                        <input type="text" name="contactos[__INDEX__][curp]"
                               class="form-control" placeholder="18 caracteres"
                               maxlength="18" style="text-transform:uppercase">
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
                            <option value="1" __ORDEN1__>1 — Principal</option>
                            <option value="2" __ORDEN2__>2 — Secundario</option>
                            <option value="3" __ORDEN3__>3 — Tercero</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="checkbox">
                        <label>
                            <input type="hidden"  name="contactos[__INDEX__][autorizado_recoger]" value="0">
                            <input type="checkbox" name="contactos[__INDEX__][autorizado_recoger]" value="1" __RECOGER__>
                            Autorizado para recoger
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="checkbox">
                        <label>
                            <input type="hidden"  name="contactos[__INDEX__][es_responsable_pago]" value="0">
                            <input type="checkbox" name="contactos[__INDEX__][es_responsable_pago]" value="1" __PAGO__>
                            Responsable de pagos
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="checkbox">
                        <label>
                            <input type="hidden"  name="contactos[__INDEX__][tiene_acceso_portal]" value="0">
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
const MAX_CONTACTOS  = 3;
const MAX_FOTO_BYTES = 2 * 1024 * 1024; // 2 MB
let   numContactos   = 0;

// ── Al cargar la página ──────────────────────────────────
$(document).ready(function () {
    // Agregar el primer contacto automáticamente
    agregarContacto();

    // Si hubo error de validación y hay datos de old() en el servidor,
    // los campos se repoblan por el input[name] automáticamente con Laravel
});

// ── Foto ─────────────────────────────────────────────────
$('#foto').on('change', function () {
    const archivo = this.files[0];
    if (!archivo) { $('#foto-nombre').val(''); return; }

    if (archivo.size > MAX_FOTO_BYTES) {
        this.value = '';
        $('#foto-nombre').val('');
        alert('El archivo pesa ' + (archivo.size / 1024 / 1024).toFixed(2) + ' MB.\nEl máximo permitido es 2 MB.');
        return;
    }
    $('#foto-nombre').val(archivo.name);
});

// ── CURP contador y mayúsculas ────────────────────────────
$('#curp').on('input', function () {
    $(this).val($(this).val().toUpperCase());
    $('#curp-chars').text($(this).val().length);
});

// ── Familia: mostrar/ocultar bloques ─────────────────────
$('input[name="tipo_familia"]').on('change', function () {
    if ($(this).val() === 'existente') {
        $('#bloque-familia-nueva').hide();
        $('#bloque-familia-existente').show();
        $('#apellido_familia').prop('required', false).val('');
        $('#familia_id').prop('required', true);
    } else {
        $('#bloque-familia-nueva').show();
        $('#bloque-familia-existente').hide();
        $('#apellido_familia').prop('required', true);
        $('#familia_id').prop('required', false).val('');
    }
}).trigger('change');

// ── Inscripción: cargar grupos al cambiar ciclo o nivel ───
$('#ciclo_id, #nivel_id').on('change', function () {
    cargarGrupos();
});

function cargarGrupos() {
    const cicloId = $('#ciclo_id').val();
    const nivelId = $('#nivel_id').val();
    const grupoActual = '{{ old('grupo_id') }}';

    if (!cicloId || !nivelId) {
        $('#grupo_id').html('<option value="">-- Primero selecciona ciclo y nivel --</option>');
        return;
    }

    $.ajax({
        url: '/grupos',
        method: 'GET',
        data: { ciclo_id: cicloId, nivel_id: nivelId },
        success: function (response) {
            let opciones = '<option value="">-- Seleccionar grupo --</option>';

            if (!response.length) {
                opciones = '<option value="">Sin grupos disponibles</option>';
            } else {
                response.forEach(function (grupo) {
                    const disponibles = grupo.cupo_maximo
                        ? grupo.alumnos_inscritos + '/' + grupo.cupo_maximo
                        : grupo.alumnos_inscritos + ' inscritos';
                    const sel = grupo.id == grupoActual ? 'selected' : '';
                    const lleno = grupo.cupo_maximo && grupo.alumnos_inscritos >= grupo.cupo_maximo
                        ? ' [LLENO]' : '';
                    opciones += `<option value="${grupo.id}" ${sel}>${grupo.grado.nombre}° ${grupo.nombre} (${disponibles})${lleno}</option>`;
                });
            }

            $('#grupo_id').html(opciones);
        },
        error: function () {
            $('#grupo_id').html('<option value="">Error al cargar grupos</option>');
        }
    });
}

// Cargar grupos al entrar (si ya hay ciclo y nivel seleccionados por old())
if ($('#ciclo_id').val() && $('#nivel_id').val()) {
    cargarGrupos();
}

// ── Contactos: agregar ───────────────────────────────────
$('#btn-agregar-contacto').on('click', function () {
    if (numContactos >= MAX_CONTACTOS) {
        alert('El máximo de contactos permitidos es ' + MAX_CONTACTOS + '.');
        return;
    }
    agregarContacto();
});

function agregarContacto() {
    if (numContactos >= MAX_CONTACTOS) return;

    const index  = numContactos;
    const num    = numContactos + 1;
    let template = $('#template-contacto').html();

    // Reemplazar placeholders
    template = template.replace(/__INDEX__/g, index);
    template = template.replace(/__NUM__/g, num);
    // Orden por defecto según posición
    template = template.replace('__ORDEN1__', num === 1 ? 'selected' : '');
    template = template.replace('__ORDEN2__', num === 2 ? 'selected' : '');
    template = template.replace('__ORDEN3__', num === 3 ? 'selected' : '');
    // Primer contacto: autorizado recoger y responsable de pagos por defecto
    template = template.replace('__RECOGER__', num === 1 ? 'checked' : '');
    template = template.replace('__PAGO__',    num === 1 ? 'checked' : '');

    $('#contenedor-contactos').append(template);
    numContactos++;
    actualizarBtnAgregar();
}

// ── Contactos: eliminar ──────────────────────────────────
$(document).on('click', '.btn-eliminar-contacto', function () {
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
    $('.contacto-item').each(function (i) {
        $(this).find('.num-contacto').text(i + 1);
    });
}

// =======================================================
// VALIDACIÓN EN TIEMPO REAL
// Cada campo muestra error en cuanto el usuario lo deja
// (evento blur) sin necesidad de enviar el formulario.
// =======================================================

// ── Helpers de validación ────────────────────────────────

function marcarError(selector, mensaje) {
    const $grupo = $(selector).closest('.form-group');
    $grupo.addClass('has-error').removeClass('has-success');
    // Crear o actualizar el span de error
    if (!$grupo.find('.help-block.val-msg').length) {
        $grupo.append('<span class="help-block val-msg"></span>');
    }
    $grupo.find('.help-block.val-msg')
        .html('<i class="fa fa-exclamation-circle"></i> ' + mensaje)
        .show();
}

function marcarOk(selector) {
    const $grupo = $(selector).closest('.form-group');
    $grupo.removeClass('has-error').addClass('has-success');
    $grupo.find('.help-block.val-msg').hide();
}

function limpiarEstado(selector) {
    const $grupo = $(selector).closest('.form-group');
    $grupo.removeClass('has-error has-success');
    $grupo.find('.help-block.val-msg').hide();
}

function validarCampo(selector, fn) {
    const resultado = fn($(selector).val());
    if (resultado) {
        marcarError(selector, resultado);
        return false;
    }
    marcarOk(selector);
    return true;
}

// ── Reglas de validación por campo ───────────────────────

const reglas = {
    '#nombre': v => {
        if (!v.trim()) return 'El nombre es obligatorio.';
        if (v.trim().length < 2) return 'Mínimo 2 caracteres.';
        return null;
    },
    '#ap_paterno': v => {
        if (!v.trim()) return 'El apellido paterno es obligatorio.';
        if (v.trim().length < 2) return 'Mínimo 2 caracteres.';
        return null;
    },
    '#fecha_nacimiento': v => {
        if (!v) return 'La fecha de nacimiento es obligatoria.';
        const hoy   = new Date();
        const fecha = new Date(v);
        const años  = (hoy - fecha) / (1000 * 60 * 60 * 24 * 365);
        if (años < 2)  return 'El alumno debe tener al menos 2 años.';
        if (años > 25) return 'Verifica la fecha de nacimiento.';
        return null;
    },
    '#fecha_inscripcion': v => {
        if (!v) return 'La fecha de inscripción es obligatoria.';
        return null;
    },
    '#ciclo_id': v => {
        if (!v) return 'Debe seleccionar el ciclo escolar.';
        return null;
    },
    '#nivel_id': v => {
        if (!v) return 'Debe seleccionar el nivel.';
        return null;
    },
    '#grupo_id': v => {
        if (!v) return 'Debe seleccionar el grupo.';
        return null;
    },
    '#curp': v => {
        if (!v) return null; // opcional
        if (v.length !== 18) return 'La CURP debe tener exactamente 18 caracteres.';
        if (!/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9]{2}$/.test(v)) {
            return 'El formato de la CURP no es válido.';
        }
        return null;
    },
};

// ── Validar al salir de cada campo (blur) ─────────────────
Object.keys(reglas).forEach(function (selector) {
    $(document).on('blur', selector, function () {
        validarCampo(selector, reglas[selector]);
    });
    // También validar en tiempo real después del primer error (input)
    $(document).on('input change', selector, function () {
        if ($(selector).closest('.form-group').hasClass('has-error')) {
            validarCampo(selector, reglas[selector]);
        }
    });
});

// ── Validación de familia al cambiar tipo ─────────────────
$('input[name="tipo_familia"]').on('change', function () {
    limpiarEstado('#apellido_familia');
    limpiarEstado('#familia_id');
});

$(document).on('blur', '#apellido_familia', function () {
    if ($('input[name="tipo_familia"]:checked').val() !== 'nueva') return;
    if (!$(this).val().trim()) {
        marcarError('#apellido_familia', 'El nombre de la familia es obligatorio.');
    } else {
        marcarOk('#apellido_familia');
    }
});

$(document).on('change', '#familia_id', function () {
    if ($('input[name="tipo_familia"]:checked').val() !== 'existente') return;
    if (!$(this).val()) {
        marcarError('#familia_id', 'Debe seleccionar la familia.');
    } else {
        marcarOk('#familia_id');
    }
});

// ── Validación de contactos en tiempo real ────────────────
$(document).on('blur', '.contacto-item input[name$="[nombre]"]', function () {
    const $input = $(this);
    const $grupo = $input.closest('.form-group');
    if (!$input.val().trim()) {
        $grupo.addClass('has-error');
        if (!$grupo.find('.help-block.val-msg').length)
            $grupo.append('<span class="help-block val-msg"></span>');
        $grupo.find('.help-block.val-msg')
            .html('<i class="fa fa-exclamation-circle"></i> El nombre del contacto es obligatorio.')
            .show();
    } else {
        $grupo.removeClass('has-error').addClass('has-success');
        $grupo.find('.help-block.val-msg').hide();
    }
});

$(document).on('blur', '.contacto-item input[name$="[telefono_celular]"]', function () {
    const $input  = $(this);
    const $grupo  = $input.closest('.form-group');
    const telefono = $input.val().trim();
    let error = null;

    if (!telefono) {
        error = 'El teléfono es obligatorio.';
    } else if (!/^[0-9]{10}$/.test(telefono.replace(/\s|-/g, ''))) {
        error = 'Debe ser un número de 10 dígitos.';
    }

    if (error) {
        $grupo.addClass('has-error').removeClass('has-success');
        if (!$grupo.find('.help-block.val-msg').length)
            $grupo.append('<span class="help-block val-msg"></span>');
        $grupo.find('.help-block.val-msg')
            .html('<i class="fa fa-exclamation-circle"></i> ' + error)
            .show();
    } else {
        $grupo.removeClass('has-error').addClass('has-success');
        $grupo.find('.help-block.val-msg').hide();
    }
});

$(document).on('blur', '.contacto-item input[name$="[email]"]', function () {
    const $input = $(this);
    const $grupo = $input.closest('.form-group');
    const email  = $input.val().trim();
    if (!email) { $grupo.removeClass('has-error has-success'); return; }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        $grupo.addClass('has-error').removeClass('has-success');
        if (!$grupo.find('.help-block.val-msg').length)
            $grupo.append('<span class="help-block val-msg"></span>');
        $grupo.find('.help-block.val-msg')
            .html('<i class="fa fa-exclamation-circle"></i> El formato del correo no es válido.')
            .show();
    } else {
        $grupo.removeClass('has-error').addClass('has-success');
        $grupo.find('.help-block.val-msg').hide();
    }
});

// ── Validación completa al enviar ─────────────────────────
$('#form-alumno').on('submit', function (e) {
    let valido = true;

    // Validar todos los campos con regla definida
    Object.keys(reglas).forEach(function (selector) {
        if (!validarCampo(selector, reglas[selector])) {
            valido = false;
        }
    });

    // Validar familia
    const tipoFamilia = $('input[name="tipo_familia"]:checked').val();
    if (tipoFamilia === 'nueva' && !$('#apellido_familia').val().trim()) {
        marcarError('#apellido_familia', 'El nombre de la familia es obligatorio.');
        valido = false;
    }
    if (tipoFamilia === 'existente' && !$('#familia_id').val()) {
        marcarError('#familia_id', 'Debe seleccionar la familia.');
        valido = false;
    }

    // Validar contactos
    let tieneContactoValido = false;
    $('.contacto-item').each(function () {
        const $item   = $(this);
        const nombre  = $item.find('input[name$="[nombre]"]').val().trim();
        const tel     = $item.find('input[name$="[telefono_celular]"]').val().trim();
        const $gNom   = $item.find('input[name$="[nombre]"]').closest('.form-group');
        const $gTel   = $item.find('input[name$="[telefono_celular]"]').closest('.form-group');

        if (!nombre) {
            $gNom.addClass('has-error');
            if (!$gNom.find('.help-block.val-msg').length)
                $gNom.append('<span class="help-block val-msg"></span>');
            $gNom.find('.help-block.val-msg')
                .html('<i class="fa fa-exclamation-circle"></i> El nombre del contacto es obligatorio.')
                .show();
            valido = false;
        }
        if (!tel) {
            $gTel.addClass('has-error');
            if (!$gTel.find('.help-block.val-msg').length)
                $gTel.append('<span class="help-block val-msg"></span>');
            $gTel.find('.help-block.val-msg')
                .html('<i class="fa fa-exclamation-circle"></i> El teléfono es obligatorio.')
                .show();
            valido = false;
        }
        if (nombre && tel) tieneContactoValido = true;
    });

    if (!tieneContactoValido) {
        valido = false;
    }

    if (!valido) {
        e.preventDefault();

        // Scroll al primer campo con error
        const $primerError = $('.has-error').first();
        if ($primerError.length) {
            $('html, body').animate({
                scrollTop: $primerError.offset().top - 80
            }, 400);
        }

        return false;
    }

    // Todo correcto — deshabilitar botón para evitar doble envío
    $('#btn-guardar').prop('disabled', true)
        .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
});
</script>
@endpush
