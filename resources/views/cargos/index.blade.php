@extends('layouts.master')

@section('page_title', 'Cargos')
@section('page_subtitle', 'Listado y generación')

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">
@endpush

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $resumen['total'] }}</h3>
                    <p>Total de cargos</p>
                </div>
                <div class="icon"><i class="fa fa-file-text-o"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $resumen['pendientes'] }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon"><i class="fa fa-clock-o"></i></div>
            </div>
        </div>

        <div class="col-md-3 col-lg-2">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $resumen['vencidos'] }}</h3>
                    <p>Vencidos</p>
                </div>
                <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
            </div>
        </div>

        <div class="col-md-3 col-lg-2">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>{{ $resumen['parciales'] }}</h3>
                    <p>Parciales</p>
                </div>
                <div class="icon"><i class="fa fa-adjust"></i></div>
            </div>
        </div>

        <div class="col-md-3 col-lg-2">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $resumen['pagados'] }}</h3>
                    <p>Pagados</p>
                </div>
                <div class="icon"><i class="fa fa-check"></i></div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filtros</h3>

            @if (auth()->user()->esAdministrador())
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalGenerarCargos">
                        <i class="fa fa-refresh"></i> Generar cargos
                    </button>
                </div>
            @endif
        </div>

        <div class="box-body">
            <form method="GET" action="{{ route('cargos.index') }}">
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alumno</label>
                            <select name="alumno_id" class="form-control select2" style="width: 100%;" data-placeholder="Buscar alumno...">
                                <option value=""></option>
                                @foreach ($alumnos as $alumno)
                                    <option value="{{ $alumno->id }}"
                                        {{ (string) request('alumno_id') === (string) $alumno->id ? 'selected' : '' }}>
                                        {{ $alumno->nombre_completo }} ({{ $alumno->matricula }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="estado" class="form-control select2" style="width: 100%;" data-placeholder="Buscar estado...">
                                <option value=""></option>
                                @foreach (['pendiente', 'vencido', 'parcial', 'parcial_vencido', 'pagado', 'condonado'] as $estado)
                                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Período</label>
                            <select name="periodo" class="form-control select2" style="width: 100%;" data-placeholder="Buscar período...">
                                <option value=""></option>
                                @foreach ($periodos as $periodo)
                                    <option value="{{ $periodo }}" {{ request('periodo') === $periodo ? 'selected' : '' }}>
                                        {{ $periodo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group" style="margin-top: 24px;">
                            <button type="submit" class="btn btn-primary btn-flat" title="Buscar">
                                <i class="fa fa-search"></i>
                            </button>

                            <a href="{{ route('cargos.index') }}" class="btn btn-default btn-flat" title="Limpiar filtros">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Listado de cargos</h3>
        </div>

        <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Grupo</th>
                        <th>Concepto</th>
                        <th>Período</th>
                        <th>Vence</th>
                        <th>Monto</th>
                        <th>Abonado</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                        <th style="width: 100px;">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($cargos as $cargo)
                        <tr>
                            <td>
                                <strong>{{ $cargo->inscripcion?->alumno?->nombre_completo }}</strong><br>
                                <small class="text-muted">
                                    {{ $cargo->inscripcion?->alumno?->matricula }}
                                </small>
                            </td>

                            <td>
                                {{ optional($cargo->inscripcion?->grupo?->grado)->nombre }}
                                {{ optional($cargo->inscripcion?->grupo)->nombre }}
                            </td>

                            <td>{{ $cargo->concepto?->nombre }}</td>

                            <td><code>{{ $cargo->periodo }}</code></td>

                            <td class="{{ in_array($cargo->estado_real, ['vencido', 'parcial_vencido']) ? 'text-red' : '' }}">
                                {{ optional($cargo->fecha_vencimiento)->format('d/m/Y') }}
                            </td>

                            <td>$ {{ number_format((float) $cargo->monto_original, 2) }}</td>
                            <td>$ {{ number_format((float) $cargo->saldo_abonado, 2) }}</td>
                            <td>$ {{ number_format((float) $cargo->saldo_pendiente_base, 2) }}</td>

                            <td>
                                @php
                                    $estado = $cargo->estado_real;
                                    $clase = match ($estado) {
                                        'pagado' => 'label-success',
                                        'parcial', 'parcial_vencido' => 'label-warning',
                                        'vencido' => 'label-danger',
                                        'condonado' => 'label-info',
                                        default => 'label-default',
                                    };
                                @endphp

                                <span class="label {{ $clase }}">
                                    {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('cargos.show', $cargo->id) }}"
                                   class="btn btn-default btn-xs"
                                   title="Ver detalle">
                                    <i class="fa fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center" style="padding: 30px;">
                                <i class="fa fa-file-text-o text-muted" style="font-size: 32px;"></i>
                                <p class="text-muted" style="margin-top: 10px;">
                                    No se encontraron cargos con los filtros indicados.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="box-footer clearfix">
            {{ $cargos->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- MODAL --}}
    @if (auth()->user()->esAdministrador())
        <x-modal id="modalGenerarCargos" title="Generar cargos del ciclo" size="modal-md">
            <form action="{{ route('cargos.generar') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Ciclo escolar</label>
                    <select name="ciclo_id" class="form-control select2" required>
                        @foreach ($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}"
                                {{ (int) $cicloId === (int) $ciclo->id ? 'selected' : '' }}>
                                {{ $ciclo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <p class="text-muted">
                    Se tomarán los planes asignados por prioridad: alumno, grupo y nivel.
                    Los cargos existentes no se duplicarán.
                </p>

                <div class="text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-refresh"></i> Generar
                    </button>
                </div>
            </form>
        </x-modal>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2').select2({
                allowClear: true,
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || '-- Seleccionar --';
                }
            });

            @if (request('mostrar_generador'))
                $('#modalGenerarCargos').modal('show');
            @endif
        });
    </script>
@endpush
