@extends('layouts.master')

@section('page_title', 'Detalle del prospecto')
@section('page_subtitle', $prospecto->nombre)

@section('content')
    @php
        $etapas = [
            'prospecto' => 'Prospecto',
            'cita' => 'Cita',
            'visita' => 'Visita',
            'documentacion' => 'Documentación',
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
                            <strong>Nivel de interés</strong>
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
                            <strong>Teléfono</strong>
                            <p>{{ $prospecto->contacto_telefono }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Correo electrónico</strong>
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
                        <strong>Motivo de no concreción</strong>
                        <p>{{ $prospecto->motivo_no_concrecion }}</p>
                    @endif
                </div>
                <div class="box-footer">
                    <a href="{{ route('prospectos.index') }}" class="btn btn-default">Volver</a>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalSeguimiento">Agregar seguimiento</button>
                    @if ($prospecto->etapa === 'aceptado' && !$prospecto->alumno_id)
                        <a href="{{ route('alumnos.create', ['prospecto_id' => $prospecto->id]) }}" class="btn btn-primary">Registrar como alumno</a>
                    @endif
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
                    <h3 class="box-title">Documentos de admisión</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalDocumento">Agregar documento</button>
                    </div>
                </div>
                <div class="box-body">
                    @forelse ($prospecto->documentos as $documento)
                        <div style="display: flex; align-items: center; gap: 12px; padding: 10px 12px; margin-bottom: 10px; background: #f8fbff; border: 1px solid #e2ebf3; border-radius: 14px;">
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: #e8eef5; color: #556270; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                                <i class="fa fa-file-text"></i>
                            </div>
                            <div style="min-width: 0; flex: 1;">
                                <div style="font-size: 15px; font-weight: 700; color: #1f2933; line-height: 1.25; margin-bottom: 6px; word-break: break-word;">{{ $documento->tipo_documento }}</div>
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 7px; background: #e4f4ea; color: #137a4b; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;">{{ str_replace('_', ' ', $documento->estado) }}</span>
                            </div>
                            @if ($documento->archivo_url)
                                <div style="flex-shrink: 0;">
                                    <a href="{{ route('prospectos.documentos.archivo', [$prospecto->id, $documento->id]) }}" class="btn btn-default" style="border-radius: 12px; padding: 8px 14px; font-weight: 700; font-size: 13px; color: #0b6aa8; background: #eef3f8; border-color: #eef3f8;" title="{{ $documento->archivo_nombre ?: 'Ver archivo' }}">
                                        Ver archivo
                                    </a>
                                </div>
                            @endif
                        </div>
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
                            <label for="tipo_accion">Tipo de acción</label>
                            <select class="form-control" id="tipo_accion" name="tipo_accion" required>
                                @foreach ($tiposSeguimiento as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('tipo_accion') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha">Fecha del seguimiento</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required>
                            <p class="help-block">Indica la fecha en que ocurrió la llamada, visita, correo o nota.</p>
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

    <div class="modal fade" id="modalDocumento" tabindex="-1" role="dialog" aria-labelledby="modalDocumentoLabel">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('prospectos.documentos.store', $prospecto->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalDocumentoLabel">Agregar documento</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tipo_documento">Tipo de documento</label>
                            <select class="form-control" id="tipo_documento" name="tipo_documento" required>
                                <option value="">Selecciona un documento</option>
                                @foreach ($tiposDocumento as $tipoDocumento)
                                    <option value="{{ $tipoDocumento }}" {{ old('tipo_documento') === $tipoDocumento ? 'selected' : '' }}>{{ $tipoDocumento }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" id="grupo_otro_documento" style="display: {{ old('tipo_documento') === 'Otro' ? 'block' : 'none' }};">
                            <label for="otro_documento">Cuál documento es</label>
                            <input type="text" class="form-control" id="otro_documento" name="otro_documento" value="{{ old('otro_documento') }}" maxlength="120">
                            <p class="help-block">Escribe el nombre del documento si no aparece en la lista.</p>
                        </div>
                        <div class="form-group">
                            <label for="archivo">Archivo</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            <p class="help-block">Formatos permitidos: PDF, JPG, JPEG, PNG, DOC y DOCX. Tamaño máximo: 5 MB.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar documento</button>
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
                            <label for="motivo_no_concrecion">Motivo no concreción</label>
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

            function toggleOtroDocumento() {
                var show = $('#tipo_documento').val() === 'Otro';
                $('#grupo_otro_documento').toggle(show);
                $('#otro_documento').prop('required', show);

                if (!show) {
                    $('#otro_documento').val('');
                }
            }

            $('#etapa').on('change', toggleMotivo);
            $('#tipo_documento').on('change', toggleOtroDocumento);

            toggleMotivo();
            toggleOtroDocumento();
        });
    </script>
@endpush
