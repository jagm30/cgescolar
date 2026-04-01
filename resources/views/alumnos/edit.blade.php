@extends('layouts.master')

@section('page_title', 'Editar alumno')
@section('page_subtitle', $alumno->nombre . ' ' . $alumno->ap_paterno . ' — ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li><a href="{{ route('alumnos.show', $alumno->id) }}">{{ $alumno->ap_paterno }}</a></li>
@endsection

@section('content')

<form method="POST"
      action="{{ route('alumnos.update', $alumno->id) }}"
      enctype="multipart/form-data"
      id="form-editar-alumno">
@csrf
@method('PUT')

<div class="row">

    {{-- ══════════════════════════════════════════════
         COLUMNA IZQUIERDA — Datos editables
    ══════════════════════════════════════════════ --}}
    <div class="col-md-8">

        {{-- Datos personales --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-user"></i> Datos personales</h3>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                            <label for="nombre">Nombre(s) <span class="text-red">*</span></label>
                            <input type="text"
                                   name="nombre"
                                   id="nombre"
                                   class="form-control"
                                   value="{{ old('nombre', $alumno->nombre) }}"
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
                                   value="{{ old('ap_paterno', $alumno->ap_paterno) }}"
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
                                   value="{{ old('ap_materno', $alumno->ap_materno) }}"
                                   maxlength="100">
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
                                   value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento?->format('Y-m-d')) }}"
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
                                <option value="M"    {{ old('genero', $alumno->genero) === 'M'    ? 'selected' : '' }}>Masculino</option>
                                <option value="F"    {{ old('genero', $alumno->genero) === 'F'    ? 'selected' : '' }}>Femenino</option>
                                <option value="Otro" {{ old('genero', $alumno->genero) === 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
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
                                   value="{{ old('curp', $alumno->curp) }}"
                                   maxlength="18"
                                   style="text-transform:uppercase">
                            <span class="help-block">
                                <span id="curp-chars">{{ strlen($alumno->curp ?? '') }}</span>/18 caracteres
                            </span>
                            @error('curp')
                                <span class="help-block text-red"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- Fecha de baja (visible solo si el estado lo requiere) --}}
                        <div class="form-group {{ $errors->has('fecha_baja') ? 'has-error' : '' }}"
                             id="bloque-fecha-baja"
                             style="{{ in_array($alumno->estado, ['baja_temporal','baja_definitiva']) ? '' : 'display:none;' }}">
                            <label for="fecha_baja">Fecha de baja</label>
                            <input type="date"
                                   name="fecha_baja"
                                   id="fecha_baja"
                                   class="form-control"
                                   value="{{ old('fecha_baja', $alumno->fecha_baja?->format('Y-m-d')) }}">
                            @error('fecha_baja')
                                <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                            <label>Foto del alumno</label>

                            {{-- Foto actual --}}
                            @if($alumno->foto_url)
                                <div style="margin-bottom:8px;">
                                    <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                                         id="foto-actual"
                                         style="width:64px; height:64px; object-fit:cover; border-radius:4px; border:1px solid #ddd;"
                                         alt="Foto actual">
                                    <small class="text-muted" style="display:block; margin-top:4px;">Foto actual</small>
                                </div>
                            @endif

                            <div class="input-group">
                                <span class="input-group-btn">
                                    <label class="btn btn-default btn-flat" for="foto" style="margin:0;cursor:pointer;">
                                        <i class="fa fa-camera"></i>
                                        {{ $alumno->foto_url ? 'Cambiar foto' : 'Seleccionar foto' }}
                                    </label>
                                </span>
                                <input type="text" id="foto-nombre" class="form-control" placeholder="Sin cambios" readonly>
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Matrícula</label>
                            <input type="text"
                                   class="form-control"
                                   value="{{ $alumno->matricula }}"
                                   disabled>
                            <span class="help-block">La matrícula no se puede modificar.</span>
                        </div>
                    </div>
                </div>

                <div class="form-group {{ $errors->has('observaciones') ? 'has-error' : '' }}">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones"
                              id="observaciones"
                              class="form-control"
                              rows="2"
                              maxlength="1000">{{ old('observaciones', $alumno->observaciones) }}</textarea>
                </div>

            </div>{{-- /.box-body --}}
        </div>{{-- /.box --}}

        {{-- Contactos (solo visualización en edit) --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-phone"></i> Contactos familiares</h3>
                <div class="box-tools pull-right">
                    <small class="text-muted">Para modificar contactos ir a la ficha de familia</small>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table">
                    @forelse($alumno->contactos as $contacto)
                        <tr>
                            <td>
                                <strong>{{ $contacto->nombre }} {{ $contacto->ap_paterno }}</strong>
                                <small class="text-muted"> — {{ ucfirst($contacto->pivot->parentesco) }}</small>
                            </td>
                            <td>{{ $contacto->telefono_celular ?? '—' }}</td>
                            <td>{{ $contacto->email ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-muted text-center">Sin contactos registrados.</td>
                        </tr>
                    @endforelse
                </table>
            </div>
        </div>

    </div>{{-- /.col-md-8 --}}

    {{-- ══════════════════════════════════════════════
         COLUMNA DERECHA — Acciones y resumen
    ══════════════════════════════════════════════ --}}
    <div class="col-md-4">

        {{-- Acciones --}}
        <div class="box box-default">
            <div class="box-body">
                <button type="submit" class="btn btn-primary btn-block" id="btn-guardar">
                    <i class="fa fa-save"></i> Guardar cambios
                </button>
                <a href="{{ route('alumnos.show', $alumno->id) }}" class="btn btn-default btn-block">
                    <i class="fa fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </div>

        {{-- Resumen del alumno --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Resumen</h3>
            </div>
            <div class="box-body no-padding">
                <table class="table">
                    <tr>
                        <th style="color:#999; font-weight:400; width:45%;">Matrícula</th>
                        <td><code>{{ $alumno->matricula }}</code></td>
                    </tr>
                    <tr>
                        <th style="color:#999; font-weight:400;">Familia</th>
                        <td>{{ $alumno->familia?->apellido_familia ?? '—' }}</td>
                    </tr>
                    @if($alumno->inscripciones->isNotEmpty())
                    <tr>
                        <th style="color:#999; font-weight:400;">Inscripción</th>
                        <td>
                            @php $ins = $alumno->inscripciones->first(); @endphp
                            {{ $ins->grupo->grado->nombre }}°
                            {{ $ins->grupo->nombre }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Errores --}}
        @if($errors->any())
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-exclamation-triangle"></i> Corrige los errores
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

@endsection

@push('scripts')
<script>
const MAX_FOTO_BYTES = 2 * 1024 * 1024;

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

// ── Mostrar fecha de baja según estado ───────────────────
$('#estado').on('change', function () {
    const estado = $(this).val();
    if (estado === 'baja_temporal' || estado === 'baja_definitiva') {
        $('#bloque-fecha-baja').show();
        if (!$('#fecha_baja').val()) {
            $('#fecha_baja').val('{{ now()->format('Y-m-d') }}');
        }
    } else {
        $('#bloque-fecha-baja').hide();
        $('#fecha_baja').val('');
    }
});

// ── Deshabilitar botón al enviar ─────────────────────────
$('#form-editar-alumno').on('submit', function () {
    $('#btn-guardar').prop('disabled', true)
        .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
});
</script>
@endpush
