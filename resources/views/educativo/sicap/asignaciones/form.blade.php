@extends('layouts.educativo')

@section('page_title', isset($asignacion->id) ? 'Editar asignación' : 'Nueva asignación')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.sicap.asignaciones.index') }}">Asignaciones docentes</a></li>
    <li class="active">{{ isset($asignacion->id) ? 'Editar' : 'Nueva' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user-plus"></i>
                    {{ isset($asignacion->id) ? 'Editar asignación docente' : 'Nueva asignación docente' }}
                </h3>
            </div>

            <form method="POST"
                  action="{{ isset($asignacion->id)
                      ? route('educativo.sicap.asignaciones.update', $asignacion)
                      : route('educativo.sicap.asignaciones.store') }}"
                  id="form-asignacion">
                @csrf
                @if(isset($asignacion->id)) @method('PUT') @endif

                <div class="box-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        {{-- Ciclo escolar --}}
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                                <label for="ciclo_id">Ciclo escolar <span class="text-danger">*</span></label>
                                <select name="ciclo_id" id="ciclo_id" class="form-control">
                                    <option value="">— Selecciona un ciclo —</option>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}"
                                            {{ old('ciclo_id', $asignacion->ciclo_id ?? $cicloId) == $ciclo->id ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ciclo_id')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Docente --}}
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('docente_id') ? 'has-error' : '' }}">
                                <label for="docente_id">Docente <span class="text-danger">*</span></label>
                                <select name="docente_id" id="docente_id" class="form-control">
                                    <option value="">— Selecciona un docente —</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}"
                                            {{ old('docente_id', $asignacion->docente_id) == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('docente_id')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Grupo --}}
                    <div class="form-group {{ $errors->has('grupo_id') ? 'has-error' : '' }}">
                        <label for="grupo_id">Grupo <span class="text-danger">*</span></label>
                        <select name="grupo_id" id="grupo_id" class="form-control">
                            <option value="">— Selecciona un grupo —</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}"
                                    {{ old('grupo_id', $asignacion->grupo_id) == $grupo->id ? 'selected' : '' }}>
                                    {{ $grupo->grado->nivel->nombre ?? '' }}
                                    {{ $grupo->grado->nombre ?? '' }}
                                    {{ $grupo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('grupo_id')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Materia o Campo formativo (mutuamente excluyentes) --}}
                    <div class="callout callout-info" style="padding:10px 15px; margin-bottom:15px;">
                        <small>
                            <i class="fa fa-info-circle"></i>
                            Selecciona <strong>una materia</strong> (Primaria, Secundaria, Preparatoria)
                            <em>o</em> un <strong>campo formativo</strong> (Preescolar). No ambos.
                        </small>
                    </div>

                    {{-- Materia --}}
                    <div class="form-group {{ $errors->has('materia_id') ? 'has-error' : '' }}">
                        <label for="materia_id">Materia</label>
                        <select name="materia_id" id="materia_id" class="form-control">
                            <option value="">— Ninguna —</option>
                            @foreach($materias->groupBy(fn($m) => $m->plan->nivel->nombre ?? 'Sin nivel') as $nivel => $mats)
                                <optgroup label="{{ $nivel }}">
                                    @foreach($mats as $materia)
                                        <option value="{{ $materia->id }}"
                                            {{ old('materia_id', $asignacion->materia_id) == $materia->id ? 'selected' : '' }}>
                                            {{ $materia->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('materia_id')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Campo formativo --}}
                    <div class="form-group {{ $errors->has('campo_id') ? 'has-error' : '' }}">
                        <label for="campo_id">Campo formativo <small class="text-muted">(Preescolar)</small></label>
                        <select name="campo_id" id="campo_id" class="form-control">
                            <option value="">— Ninguno —</option>
                            @foreach($campos as $campo)
                                <option value="{{ $campo->id }}"
                                    {{ old('campo_id', $asignacion->campo_id) == $campo->id ? 'selected' : '' }}>
                                    {{ $campo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('campo_id')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Activa --}}
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="activa" value="0">
                                <input type="checkbox" name="activa" value="1"
                                    {{ old('activa', $asignacion->activa ?? true) ? 'checked' : '' }}>
                                Asignación activa
                            </label>
                        </div>
                    </div>

                </div>{{-- /.box-body --}}

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        {{ isset($asignacion->id) ? 'Guardar cambios' : 'Crear asignación' }}
                    </button>
                    <a href="{{ route('educativo.sicap.asignaciones.index', ['ciclo_id' => $cicloId]) }}"
                       class="btn btn-default">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Cuando el usuario selecciona una materia, limpiar campo formativo (y viceversa)
    $('#materia_id').on('change', function () {
        if ($(this).val()) {
            $('#campo_id').val('');
        }
    });

    $('#campo_id').on('change', function () {
        if ($(this).val()) {
            $('#materia_id').val('');
        }
    });
});
</script>
@endpush
