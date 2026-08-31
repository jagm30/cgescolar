@extends('layouts.educativo')

@section('page_title', 'Boletas de calificaciones')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li class="active">Boletas</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-search"></i> Buscar alumno
                </h3>
            </div>
            <div class="box-body">
                <form method="GET" action="{{ route('educativo.boleta.index') }}"
                      class="form-inline">

                    {{-- Ciclo --}}
                    <div class="form-group" style="margin-right:10px;">
                        <label class="sr-only" for="ciclo_id">Ciclo</label>
                        <select name="ciclo_id" id="ciclo_id" class="form-control">
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ $ciclo->id == $cicloSeleccionado ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Búsqueda por nombre o matrícula --}}
                    <div class="form-group" style="margin-right:10px;">
                        <label class="sr-only" for="q">Buscar</label>
                        <input type="text" name="q" id="q"
                               class="form-control" style="min-width:280px;"
                               value="{{ $termino }}"
                               placeholder="Nombre, apellido o matrícula...">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                </form>
            </div>
        </div>

        {{-- Resultados --}}
        @if($termino)
            @if($alumnos->isEmpty())
                <div class="callout callout-warning">
                    No se encontraron alumnos inscritos que coincidan con
                    <strong>"{{ $termino }}"</strong> en este ciclo.
                </div>
            @else
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            Resultados ({{ $alumnos->count() }})
                        </h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Matrícula</th>
                                    <th>Nivel</th>
                                    <th>Grupo</th>
                                    <th style="width:120px;">Boleta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alumnos as $inscripcion)
                                <tr>
                                    <td>
                                        {{ $inscripcion->alumno->ap_paterno }}
                                        {{ $inscripcion->alumno->ap_materno }}
                                        {{ $inscripcion->alumno->nombre }}
                                    </td>
                                    <td>{{ $inscripcion->alumno->matricula ?? '—' }}</td>
                                    <td>{{ $inscripcion->grupo?->grado?->nivel?->nombre ?? '—' }}</td>
                                    <td>{{ $inscripcion->grupo?->nombre ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('educativo.boleta.show', [$inscripcion->alumno, 'ciclo_id' => $cicloSeleccionado]) }}"
                                           class="btn btn-sm btn-info">
                                            <i class="fa fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('educativo.boleta.pdf', [$inscripcion->alumno, 'ciclo_id' => $cicloSeleccionado]) }}"
                                           class="btn btn-sm btn-danger" target="_blank">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

    </div>
</div>
@endsection
