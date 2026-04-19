@extends('layouts.master')

@section('page_title', 'Becas')

@section('breadcrumb')
    <li class="active">Becas</li>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row" style="margin-bottom:20px;">
        <div class="col-md-12">
            <a href="{{ route('becas.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Asignar beca a alumno
            </a>
            <a href="{{ route('becas.catalogo') }}" class="btn btn-default">
                <i class="fa fa-list"></i> Catálogo de becas
            </a>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Asignaciones de becas</h3>
        </div>

        <div class="box-body table-responsive">
            @if ($becas->isEmpty())
                <div class="alert alert-info">
                    No existen asignaciones de becas para mostrar.
                </div>
            @else
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Beca</th>
                            <th>Concepto</th>
                            <th>Ciclo</th>
                            <th>Vigencia</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th style="width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($becas as $beca)
                            <tr>
                                <td>{{ $beca->alumno->nombre_completo }}</td>
                                <td>{{ $beca->catalogoBeca->nombre }}</td>
                                <td>{{ $beca->concepto->nombre ?? '—' }}</td>
                                <td>{{ $beca->ciclo->nombre ?? $beca->ciclo->fecha_inicio?->format('Y') }}</td>
                                <td>
                                    {{ $beca->vigencia_inicio?->format('d/m/Y') }}
                                    @if ($beca->vigencia_fin)
                                        - {{ $beca->vigencia_fin->format('d/m/Y') }}
                                    @else
                                        - sin fin
                                    @endif
                                </td>
                                <td>
                                    @if ($beca->activo)
                                        <span class="label label-success">Activa</span>
                                    @else
                                        <span class="label label-default">Inactiva</span>
                                    @endif
                                </td>
                                <td>{{ $beca->creadoPor?->nombre ?? '—' }}</td>
                                <td>
                                    @if ($beca->activo)
                                        <form action="{{ route('becas.destroy', $beca->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">
                                                <i class="fa fa-ban"></i> Deshabilitar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">No hay acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
