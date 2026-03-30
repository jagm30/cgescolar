@extends('layouts.master')

@section('page_title', 'Detalle del prospecto')
@section('page_subtitle', $prospecto->nombre)

@section('content')
    @php
        $etapas = [
            'prospecto' => 'Prospecto',
            'cita' => 'Cita',
            'visita' => 'Visita',
            'documentacion' => 'Documentacion',
            'aceptado' => 'Aceptado',
            'inscrito' => 'Inscrito',
            'no_concretado' => 'No concretado',
        ];

        $tiposSeguimiento = [
            'llamada' => 'Llamada',
            'visita' => 'Visita',
            'email' => 'Correo',
            'cambio_etapa' => 'Cambio de etapa',
            'nota' => 'Nota',
        ];

        $badgeEtapa = [
            'prospecto' => 'bg-light-blue',
            'cita' => 'bg-aqua',
            'visita' => 'bg-teal',
            'documentacion' => 'bg-yellow',
            'aceptado' => 'bg-green',
            'inscrito' => 'bg-navy',
            'no_concretado' => 'bg-red',
        ];

        $badgeDocumento = [
            'pendiente' => 'bg-yellow',
            'entregado' => 'bg-green',
            'no_aplica' => 'bg-gray',
        ];
    @endphp

    @if (session('success'))
        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Hay errores que debes revisar.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $prospecto->nombre }}</h3>
                    <div class="box-tools pull-right">
                        <span class="label {{ $badgeEtapa[$prospecto->etapa] ?? 'bg-gray' }}">
                            {{ $etapas[$prospecto->etapa] ?? $prospecto->etapa }}
                        </span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nivel de interes</strong>
                            <p>{{ optional($prospecto->nivelInteres)->nombre ?: 'Sin definir' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de nacimiento</strong>
                            <p>{{ optional($prospecto->fecha_nacimiento)->format('d/m/Y') ?: 'No registrada' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Contacto principal</strong>
                            <p>{{ $prospecto->contacto_nombre }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Telefono</strong>
                            <p>{{ $prospecto->contacto_telefono }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Correo</strong>
                            <p>{{ $prospecto->contacto_email ?: 'Sin correo' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Canal</strong>
                            <p>{{ $prospecto->canal_contacto ? ucfirst(str_replace('_', ' ', $prospecto->canal_contacto)) : 'Sin canal' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Primer contacto</strong>
                            <p>{{ optional($prospecto->fecha_primer_contacto)->format('d/m/Y') ?: 'No registrada' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Responsable</strong>
                            <p>{{ optional($prospecto->responsable)->nombre ?: 'Sin asignar' }}</p>
                        </div>
                    </div>

                    @if ($prospecto->motivo_no_concrecion)
                        <hr>
                        <strong>Motivo de no concrecion</strong>
                        <p>{{ $prospecto->motivo_no_concrecion }}</p>
                    @endif
                </div>
                <div class="box-footer">
                    <a href="{{ route('prospectos.index') }}" class="btn btn-default">Volver</a>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalSeguimiento">Agregar seguimiento</button>
                    <button type="button" class="btn btn-warning pull-right" data-toggle="modal" data-target="#modalEtapa">Cambiar etapa</button>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Seguimiento</h3>
                </div>
                <div class="box-body">
                    @forelse ($prospecto->seguimientos as $seguimiento)
                        <div class="post">
                            <div class="user-block">
                                <span class="username" style="margin-left: 0;">{{ $tiposSeguimiento[$seguimiento->tipo_accion] ?? ucfirst($seguimiento->tipo_accion) }}</span>
                                <span class="description" style="margin-left: 0;">
                                    {{ optional($seguimiento->fecha)->format('d/m/Y') ?: '-' }} | {{ optional($seguimiento->usuario)->nombre ?: 'Sistema' }}
                                </span>
                            </div>
                            <p>{{ $seguimiento->notas }}</p>
                        </div>
                    @empty
                        <p class="text-muted">No hay seguimientos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Documentos de admision</h3>
                </div>
                <div class="box-body">
                    @forelse ($prospecto->documentos as $documento)
                        <p>
                            <strong>{{ $documento->tipo_documento }}</strong><br>
                            <span class="label {{ $badgeDocumento[$documento->estado] ?? 'bg-gray' }}">
                                {{ ucfirst(str_replace('_', ' ', $documento->estado)) }}
                            </span>
                        </p>
                    @empty
                        <p class="text-muted">No hay documentos cargados para este prospecto.</p>
                    @endforelse
                </div>
            </div>

            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen</h3>
                </div>
                <div class="box-body">
                    <p><strong>Total seguimientos:</strong> {{ $prospecto->seguimientos->count() }}</p>
                    <p><strong>Documentos pendientes:</strong> {{ $prospecto->documentos->where('estado', 'pendiente')->count() }}</p>
                    <p><strong>Alumno vinculado:</strong> {{ $prospecto->alumno_id ? 'Si' : 'No' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSeguimiento" tabindex="-1" role="dialog" aria-labelledby="modalSeguimientoLabel">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('prospectos.seguimiento', $prospecto->id) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalSeguimientoLabel">Agregar seguimiento</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tipo_accion">Tipo de accion</label>
                            <select class="form-control" id="tipo_accion" name="tipo_accion" required>
                                @foreach ($tiposSeguimiento as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('tipo_accion') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="notas">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" rows="4" required>{{ old('notas') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar seguimiento</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalEtapa" tabindex="-1" role="dialog" aria-labelledby="modalEtapaLabel">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('prospectos.etapa', $prospecto->id) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalEtapaLabel">Cambiar etapa</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="etapa">Nueva etapa</label>
                            <select class="form-control" id="etapa" name="etapa" required>
                                @foreach ($etapas as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('etapa', $prospecto->etapa) === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notas_etapa">Notas del cambio</label>
                            <textarea class="form-control" id="notas_etapa" name="notas" rows="4" required>{{ old('notas') }}</textarea>
                        </div>
                        <div class="form-group" id="grupo_motivo_no_concrecion" style="display: {{ old('etapa', $prospecto->etapa) === 'no_concretado' ? 'block' : 'none' }};">
                            <label for="motivo_no_concrecion">Motivo no concrecion</label>
                            <textarea class="form-control" id="motivo_no_concrecion" name="motivo_no_concrecion" rows="3">{{ old('motivo_no_concrecion', $prospecto->motivo_no_concrecion) }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Guardar cambio</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            function toggleMotivo() {
                var show = $('#etapa').val() === 'no_concretado';
                $('#grupo_motivo_no_concrecion').toggle(show);
                $('#motivo_no_concrecion').prop('required', show);
            }

            $('#etapa').on('change', toggleMotivo);
            toggleMotivo();
        });
    </script>
@endpush
