@extends('layouts.master')

@section('page_title', 'Métricas de prospectos')
@section('page_subtitle', 'Resumen del ciclo activo')

@section('content')
    <div class="box box-primary">
        <form method="GET" action="{{ route('prospectos.metricas') }}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ciclo_id">Ciclo</label>
                            <select class="form-control" id="ciclo_id" name="ciclo_id">
                                @foreach ($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ (string) $cicloId === (string) $ciclo->id ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-default btn-block">Ver</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $datos['total_prospectos'] }}</h3>
                    <p>Total de prospectos</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $datos['total_inscritos'] }}</h3>
                    <p>Prospectos inscritos</p>
                </div>
                <div class="icon">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $datos['tasa_conversion'] }}</h3>
                    <p>Tasa de conversión</p>
                </div>
                <div class="icon">
                    <i class="fa fa-line-chart"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Prospectos por etapa</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Etapa</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($datos['por_etapa'] as $etapa => $total)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $etapa)) }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin datos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Prospectos por canal</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Canal</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($datos['por_canal'] as $canal => $total)
                                <tr>
                                    <td>{{ $canal ? ucfirst(str_replace('_', ' ', $canal)) : 'Sin canal' }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Sin datos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="box-footer">
                    <a href="{{ route('prospectos.index', ['ciclo_id' => $cicloId]) }}" class="btn btn-default">Volver a prospectos</a>
                </div>
            </div>
        </div>
    </div>
@endsection
