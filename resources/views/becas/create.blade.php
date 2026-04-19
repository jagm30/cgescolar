@extends('layouts.master')

@section('page_title', 'Asignar beca')

@section('breadcrumb')
    <li><a href="{{ route('becas.catalogo') }}">Becas</a></li>
    <li class="active">Asignar beca</li>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4><i class="icon fa fa-ban"></i> Revisa el formulario.</h4>
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('becas.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Datos de la beca</h3>
                        <div class="box-tools">
                            <a href="{{ route('becas.catalogo') }}" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                            <a href="{{ route('becas.index') }}" class="btn btn-default btn-sm" style="margin-left:6px;">
                                <i class="fa fa-list"></i> Asignaciones
                            </a>
                        </div>
                    </div>

                    <div class="box-body">
                        <div class="form-group">
                            <label>Alumno</label>
                            <select id="alumno-select" name="alumno_id" class="form-control" required>
                                <option value="">Selecciona un alumno</option>
                                @foreach ($alumnos as $alumno)
                                    <option value="{{ $alumno->id }}"
                                        {{ old('alumno_id', request('alumno_id')) == $alumno->id ? 'selected' : '' }}>
                                        {{ $alumno->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ciclo escolar</label>
                            <select name="ciclo_id" class="form-control" required>
                                <option value="">Selecciona un ciclo</option>
                                @foreach ($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}"
                                        {{ old('ciclo_id', $cicloActual?->id ?? '') == $ciclo->id ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tipo de beca</label>
                            <select name="catalogo_beca_id" class="form-control" required>
                                <option value="">Selecciona una beca</option>
                                @foreach ($catalogo as $beca)
                                    <option value="{{ $beca->id }}"
                                        {{ old('catalogo_beca_id') == $beca->id ? 'selected' : '' }}>
                                        {{ $beca->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Concepto</label>
                            <select name="concepto_id" class="form-control" required>
                                <option value="">Selecciona un concepto</option>
                                @foreach ($conceptos as $concepto)
                                    <option value="{{ $concepto->id }}"
                                        {{ old('concepto_id') == $concepto->id ? 'selected' : '' }}>
                                        {{ $concepto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Inicio de vigencia</label>
                                    <input type="date" name="vigencia_inicio" class="form-control"
                                        value="{{ old('vigencia_inicio', now()->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fin de vigencia</label>
                                    <input type="date" name="vigencia_fin" class="form-control"
                                        value="{{ old('vigencia_fin') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Motivo</label>
                            <textarea name="motivo" class="form-control" rows="3">{{ old('motivo') }}</textarea>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i> Guardar asignación
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Becas activas del alumno</h3>
                    </div>
                    <div class="box-body" id="info-becas-alumno">
                        <p class="text-muted">Selecciona un alumno para ver su estado de becas.</p>
                    </div>
                    <div class="box-footer">
                        <div class="checkbox" id="panel-deshabilitar">
                            <label>
                                <input type="checkbox" name="deshabilitar_beca_anterior" value="1"
                                    {{ old('deshabilitar_beca_anterior') ? 'checked' : '' }}>
                                Deshabilitar beca anterior si existe una activa para este alumno
                            </label>
                        </div>
                        <p class="help-block" id="texto-becas-alumno">
                            Solo se permite una beca activa por alumno en el ciclo seleccionado.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const becasAlumnoUrlTemplate = "{{ url('/becas/alumno') }}/:id/becas-activas";
            const alumnoSelect = document.getElementById('alumno-select');
            const infoPanel = document.getElementById('info-becas-alumno');

            function mostrarBecas(becas) {
                if (!becas.length) {
                    infoPanel.innerHTML = '<p class="text-muted">El alumno no tiene becas activas en el ciclo seleccionado.</p>';
                    return;
                }

                const lista = becas.map((beca) => `
                    <div class="well" style="padding:10px; margin-bottom:10px;">
                        <strong>${beca.nombre}</strong><br>
                        Concepto: ${beca.concepto}<br>
                        Vigencia: ${beca.vigencia_inicio} ${beca.vigencia_fin ? 'a ' + beca.vigencia_fin : ''}
                    </div>
                `).join('');

                infoPanel.innerHTML = `
                    <div class="alert alert-warning" style="margin-bottom:15px;">
                        <strong>El alumno tiene ${becas.length} beca(s) activa(s).</strong>
                    </div>
                    ${lista}
                `;
            }

            function cargarBecasAlumno(alumnoId) {
                if (!alumnoId) {
                    infoPanel.innerHTML = '<p class="text-muted">Selecciona un alumno para ver su estado de becas.</p>';
                    return;
                }

                fetch(becasAlumnoUrlTemplate.replace(':id', alumnoId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then((response) => response.json())
                    .then((data) => mostrarBecas(data.becas || []))
                    .catch(() => {
                        infoPanel.innerHTML = '<p class="text-danger">No se pudo cargar la información de becas.</p>';
                    });
            }

            alumnoSelect.addEventListener('change', function () {
                cargarBecasAlumno(this.value);
            });

            document.addEventListener('DOMContentLoaded', function () {
                const selectedValue = alumnoSelect.value;
                if (selectedValue) {
                    cargarBecasAlumno(selectedValue);
                }
            });
        </script>
    </form>
@endsection
