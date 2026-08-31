@extends('layouts.educativo')

@section('page_title', 'Boleta de calificaciones')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li class="active">Boleta</li>
@endsection

@section('content')
<div class="row">
    {{-- Controles: selector de ciclo + botón PDF --}}
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-body">
                <form method="GET" action="{{ route('educativo.boleta.show', $alumno) }}"
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
                    &nbsp;
                    <a href="{{ route('educativo.boleta.pdf', [$alumno, 'ciclo_id' => $cicloSeleccionado]) }}"
                       class="btn btn-danger btn-sm" target="_blank">
                        <i class="fa fa-file-pdf-o"></i> Descargar PDF
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Previsualización de la boleta --}}
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-primary" id="boleta-preview">
            <div class="box-body">
                @include('educativo.boleta._contenido', [
                    'alumno'                => $alumno,
                    'ciclo'                 => $ciclo,
                    'grupo'                 => $grupo,
                    'plan'                  => $plan,
                    'periodos'              => $periodos,
                    'esPreescolar'          => $esPreescolar,
                    'filas'                 => $filas,
                    'promedio_sep'          => $promedio_sep,
                    'promedio_institucional' => $promedio_institucional,
                ])
            </div>
        </div>
    </div>
</div>
@endsection
