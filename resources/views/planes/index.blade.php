@extends('layouts.master')

@section('page_title', 'Planes de pago')
@section('page_subtitle', $cicloActual?->nombre ? 'Configuración del ciclo ' . $cicloActual->nombre : 'Configuración y asignaciones')

@section('breadcrumb')
    <li class="active"><a href="{{ route('planes.index') }}">Planes de pago</a></li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">
    <style>
        .plan-card {
            border-left: 4px solid #3c8dbc;
            min-height: 210px;
        }

        .scope-help {
            margin-top: 8px;
            font-size: 12px;
            color: #777;
        }

        .scope-panel {
            display: none;
        }

        .scope-panel.is-active {
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $planes->count() }}</h3>
                    <p>Planes activos</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $asignaciones->where('origen', 'individual')->count() }}</h3>
                    <p>Asignaciones por alumno</p>
                </div>
                <div class="icon"><i class="fa fa-user"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $asignaciones->where('origen', 'grupo')->count() }}</h3>
                    <p>Asignaciones por grupo</p>
                </div>
                <div class="icon"><i class="fa fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $asignaciones->where('origen', 'nivel')->count() }}</h3>
                    <p>Asignaciones por nivel</p>
                </div>
                <div class="icon"><i class="fa fa-sitemap"></i></div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fa fa-exclamation-triangle"></i> Revisa el formulario de asignación.</strong>
            <ul style="margin: 8px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-7">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Planes disponibles</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        @forelse ($planes as $plan)
                            <div class="col-md-6">
                                <div class="box box-solid plan-card">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">{{ $plan->nombre }}</h3>
                                    </div>
                                    <div class="box-body">
                                        <p class="text-muted" style="margin-bottom: 8px;">
                                            {{ $plan->nivel?->nombre ?? 'Sin nivel' }} · {{ ucfirst($plan->periodicidad) }}
                                        </p>
                                        <p style="margin-bottom: 8px;">
                                            <strong>Vigencia:</strong>
                                            {{ optional($plan->fecha_inicio)->format('d/m/Y') }} al
                                            {{ optional($plan->fecha_fin)->format('d/m/Y') }}
                                        </p>
                                        <p style="margin-bottom: 8px;">
                                            <strong>Conceptos:</strong> {{ $plan->conceptos->count() }}
                                        </p>
                                        <p style="margin-bottom: 8px;">
                                            <strong>Descuentos:</strong> {{ $plan->politicasDescuentoActivas->count() }}
                                        </p>
                                        <p style="margin-bottom: 8px;">
                                            <strong>Recargo:</strong>
                                            {{ $plan->politicaRecargoActiva ? 'Activo' : 'No configurado' }}
                                        </p>
                                        <p style="margin-bottom: 0;">
                                            <strong>Asignaciones:</strong> {{ $plan->asignaciones_count }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-md-12">
                                <div class="alert alert-info" style="margin-bottom: 0;">
                                    No hay planes activos en el ciclo seleccionado.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> Asignar plan</h3>
                </div>
                <form method="POST" action="{{ route('planes.asignar') }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label>Plan de pago</label>
                            <select name="plan_id" class="form-control select2" data-placeholder="Selecciona un plan" required>
                                <option value=""></option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}" {{ (string) old('plan_id') === (string) $plan->id ? 'selected' : '' }}>
                                        {{ $plan->nombre }} · {{ $plan->nivel?->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alcance</label>
                            <select name="origen" id="origen-plan" class="form-control" required>
                                @foreach (['individual' => 'Alumno', 'grupo' => 'Grupo', 'nivel' => 'Nivel'] as $valor => $label)
                                    <option value="{{ $valor }}" {{ old('origen', 'individual') === $valor ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="scope-help">
                                La prioridad de aplicación es: alumno, luego grupo y finalmente nivel.
                            </p>
                        </div>

                        <div class="scope-panel" data-scope="individual">
                            <div class="form-group">
                                <label>Alumno</label>
                                <select name="alumno_id" class="form-control select2" data-placeholder="Selecciona un alumno">
                                    <option value=""></option>
                                    @foreach ($alumnos as $alumno)
                                        <option value="{{ $alumno->id }}" {{ (string) old('alumno_id') === (string) $alumno->id ? 'selected' : '' }}>
                                            {{ $alumno->nombre_completo }} ({{ $alumno->matricula }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="scope-panel" data-scope="grupo">
                            <div class="form-group">
                                <label>Grupo</label>
                                <select name="grupo_id" class="form-control select2" data-placeholder="Selecciona un grupo">
                                    <option value=""></option>
                                    @foreach ($grupos as $grupo)
                                        <option value="{{ $grupo->id }}" {{ (string) old('grupo_id') === (string) $grupo->id ? 'selected' : '' }}>
                                            {{ $grupo->grado?->nivel?->nombre }} · {{ $grupo->grado?->nombre }} {{ $grupo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="scope-panel" data-scope="nivel">
                            <div class="form-group">
                                <label>Nivel</label>
                                <select name="nivel_id" class="form-control select2" data-placeholder="Selecciona un nivel">
                                    <option value=""></option>
                                    @foreach ($niveles as $nivel)
                                        <option value="{{ $nivel->id }}" {{ (string) old('nivel_id') === (string) $nivel->id ? 'selected' : '' }}>
                                            {{ $nivel->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control"
                                           value="{{ old('fecha_inicio') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha fin</label>
                                    <input type="date" name="fecha_fin" class="form-control"
                                           value="{{ old('fecha_fin') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer text-right">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Guardar asignación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-table"></i> Asignaciones registradas</h3>
        </div>
        <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Alcance</th>
                        <th>Destino</th>
                        <th>Nivel</th>
                        <th>Vigencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asignaciones as $asignacion)
                        @php
                            $destino = match ($asignacion->origen) {
                                'individual' => $asignacion->alumno?->nombre_completo,
                                'grupo' => trim(($asignacion->grupo?->grado?->nombre ?? '') . ' ' . ($asignacion->grupo?->nombre ?? '')),
                                default => $asignacion->nivel?->nombre,
                            };

                            $nivelNombre = $asignacion->grupo?->grado?->nivel?->nombre
                                ?? $asignacion->nivel?->nombre
                                ?? $asignacion->plan?->nivel?->nombre;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $asignacion->plan?->nombre }}</strong><br>
                                <small class="text-muted">{{ $asignacion->plan?->periodicidad }}</small>
                            </td>
                            <td>
                                <span class="label label-primary">
                                    {{ ucfirst($asignacion->origen) }}
                                </span>
                            </td>
                            <td>{{ $destino ?: 'Sin referencia' }}</td>
                            <td>{{ $nivelNombre ?: 'Sin nivel' }}</td>
                            <td>
                                {{ optional($asignacion->fecha_inicio)->format('d/m/Y') ?? 'Inmediata' }}
                                —
                                {{ optional($asignacion->fecha_fin)->format('d/m/Y') ?? 'Abierta' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted" style="padding: 24px;">
                                No hay asignaciones registradas para este ciclo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            $('.select2').select2({
                allowClear: true,
                width: '100%',
                placeholder: function () {
                    return $(this).data('placeholder') || '-- Seleccionar --';
                }
            });

            function syncScopePanels() {
                var scope = $('#origen-plan').val();
                $('.scope-panel').removeClass('is-active');
                $('.scope-panel[data-scope="' + scope + '"]').addClass('is-active');
            }

            $('#origen-plan').on('change', syncScopePanels);
            syncScopePanels();
        });
    </script>
@endpush
