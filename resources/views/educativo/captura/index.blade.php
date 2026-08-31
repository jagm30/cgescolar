@extends('layouts.educativo')

@section('page_title', 'Captura de calificaciones')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li class="active">Captura de calificaciones</li>
@endsection

@section('content')

{{-- Filtro de ciclo (solo administrador necesita cambiarlo) --}}
@if(auth()->user()->esAdministrador())
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-body">
                <form method="GET" action="{{ route('educativo.captura.index') }}"
                      class="form-inline">
                    <div class="form-group">
                        <label class="sr-only" for="ciclo_id">Ciclo escolar</label>
                        <select name="ciclo_id" id="ciclo_id" class="form-control input-sm"
                                onchange="this.form.submit()">
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ $ciclo->id == $cicloSeleccionado ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@forelse($asignaciones as $grupoNombre => $asigs)
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-users"></i> {{ $grupoNombre }}
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Materia / Campo formativo</th>
                            <th>Docente</th>
                            <th>Tipo</th>
                            <th style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asigs as $asignacion)
                        <tr>
                            <td>{{ $asignacion->etiquetaContenido() }}</td>
                            <td>{{ $asignacion->docente->nombre_completo }}</td>
                            <td>
                                @if($asignacion->esDePreescolar())
                                    <span class="label label-warning">Campo formativo</span>
                                @else
                                    <span class="label label-primary">Materia</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('educativo.captura.show', $asignacion) }}"
                                   class="btn btn-sm btn-success">
                                    <i class="fa fa-pencil-square-o"></i> Capturar
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@empty
<div class="row">
    <div class="col-xs-12">
        <div class="callout callout-warning">
            <h4><i class="fa fa-warning"></i> Sin asignaciones</h4>
            <p>
                @if(auth()->user()->esDocente())
                    No tienes asignaciones activas para este ciclo escolar.
                    Contacta al administrador si crees que esto es un error.
                @else
                    No hay asignaciones activas para este ciclo.
                    <a href="{{ route('educativo.sicap.asignaciones.create') }}">
                        Crear una asignación
                    </a>.
                @endif
            </p>
        </div>
    </div>
</div>
@endforelse

@endsection
