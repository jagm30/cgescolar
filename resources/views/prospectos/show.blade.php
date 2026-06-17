@extends('layouts.master')

@section('page_title', 'Detalle del prospecto')
@section('page_subtitle', $prospecto->nombre_completo)

@push('styles')
<style>
    /* ── Header compacto ── */
    .pro-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .pro-header h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #2d3a4a;
    }
    .pro-header-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    /* ── Badge de etapa ── */
    .pro-badge {
        display: inline-block;
        padding: 3px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
    }
    .pro-badge-prospecto   { background:#dbeafe; color:#1e40af; }
    .pro-badge-cita        { background:#cffafe; color:#0e7490; }
    .pro-badge-visita      { background:#d1fae5; color:#065f46; }
    .pro-badge-documentacion{ background:#fef9c3; color:#854d0e; }
    .pro-badge-aceptado    { background:#bbf7d0; color:#14532d; }
    .pro-badge-inscrito    { background:#e0e7ef; color:#1e3a5f; }
    .pro-badge-no_concretado{ background:#fee2e2; color:#991b1b; }

    /* ── Paneles ── */
    .pro-panel {
        border: 1px solid #e0e7ef;
        border-radius: 8px;
        background: #fff;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .pro-panel-header {
        background: #f4f6f8;
        border-bottom: 1px solid #e0e7ef;
        padding: 9px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pro-panel-header-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pro-panel-header span.title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7a8d;
    }
    .pro-panel-body {
        padding: 16px;
    }
    .pro-panel-footer {
        background: #f4f6f8;
        border-top: 1px solid #e0e7ef;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .pro-panel-footer .ml-auto { margin-left: auto; }

    /* ── Datos del prospecto ── */
    .pro-data-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #6b7a8d;
        margin-bottom: 2px;
    }
    .pro-data-value {
        font-size: 13px;
        color: #2d3a4a;
        margin-bottom: 12px;
    }

    /* ── Timeline de seguimientos ── */
    .seg-item {
        border-left: 3px solid #3c8dbc;
        padding: 8px 0 8px 14px;
        margin-bottom: 12px;
        position: relative;
    }
    .seg-item::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #3c8dbc;
        position: absolute;
        left: -5px;
        top: 12px;
    }
    .seg-item:last-child { margin-bottom: 0; }
    .seg-tipo {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #3c8dbc;
        margin-bottom: 2px;
    }
    .seg-meta {
        font-size: 11px;
        color: #8a96a3;
        margin-bottom: 4px;
    }
    .seg-nota {
        font-size: 13px;
        color: #3a4a5c;
        line-height: 1.45;
    }

    /* ── Documentos ── */
    .doc-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        margin-bottom: 8px;
        background: #f9fbfd;
        border: 1px solid #e0e7ef;
        border-radius: 7px;
    }
    .doc-item:last-child { margin-bottom: 0; }
    .doc-icon {
        width: 36px;
        height: 36px;
        border-radius: 7px;
        background: #e8eef5;
        color: #556270;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .doc-name {
        font-size: 13px;
        font-weight: 600;
        color: #2d3a4a;
        flex: 1;
        min-width: 0;
        word-break: break-word;
    }
    .doc-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .doc-badge-pendiente  { background:#fef9c3; color:#854d0e; }
    .doc-badge-entregado  { background:#d1fae5; color:#065f46; }
    .doc-badge-no_aplica  { background:#e0e7ef; color:#6b7a8d; }

    /* ── Panel lateral resumen ── */
    .pro-resumen {
        border: 1px solid #e0e7ef;
        border-radius: 8px;
        background: #f9fbfd;
        padding: 14px 16px;
        margin-bottom: 16px;
    }
    .pro-resumen .res-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7a8d;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .res-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #3a4a5c;
        padding: 5px 0;
        border-bottom: 1px solid #e0e7ef;
    }
    .res-row:last-child { border-bottom: none; }
    .res-val {
        font-weight: 700;
        color: #2d3a4a;
    }

    /* ── Botones ── */
    .btn { border-radius: 20px !important; font-size: 13px; }
    .btn-sm { padding: 5px 14px; }
    .btn-xs { padding: 3px 10px; font-size: 12px; }

    /* ── Alertas ── */
    .pro-alert-success {
        border-left: 3px solid #27ae60;
        border-radius: 6px;
        background: #f0fdf4;
        padding: 9px 14px;
        margin-bottom: 14px;
        font-size: 13px;
        color: #065f46;
    }
    .pro-alert-danger {
        border-left: 3px solid #e74c3c;
        border-radius: 6px;
        background: #fff5f5;
        padding: 9px 14px;
        margin-bottom: 14px;
        font-size: 13px;
    }
    .pro-alert-danger strong { color: #c0392b; display: block; margin-bottom: 4px; }
    .pro-alert-danger ul { margin: 0; padding-left: 18px; color: #a94442; }

    /* ── Modal compacto ── */
    .modal-body .form-group { margin-bottom: 12px; }
    .modal-body label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #6b7a8d;
        margin-bottom: 3px;
    }
    .modal-body .form-control {
        height: 32px;
        font-size: 13px;
        border-radius: 5px;
        border-color: #d0d7e2;
        padding: 4px 9px;
    }
    .modal-body textarea.form-control { height: auto; }
    .modal-footer .btn { border-radius: 20px; font-size: 13px; padding: 5px 16px; }
</style>
@endpush

@section('content')
    @php
        $etapas = [
            'prospecto'      => 'Prospecto',
            'cita'           => 'Cita',
            'visita'         => 'Visita',
            'documentacion'  => 'Documentación',
            'aceptado'       => 'Aceptado',
            'inscrito'       => 'Inscrito',
            'no_concretado'  => 'No concretado',
        ];

        $tiposSeguimiento = [
            'llamada'     => 'Llamada',
            'visita'      => 'Visita',
            'email'       => 'Correo',
            'cambio_etapa'=> 'Cambio de etapa',
            'nota'        => 'Nota',
        ];
    @endphp

    {{-- Alertas --}}
    @if (session('success'))
        <div class="pro-alert-success">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="pro-alert-danger">
            <strong><i class="fa fa-exclamation-circle"></i> Hay errores que debes revisar.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="pro-header">
        <h2>
            <i class="fa fa-user-circle-o" style="color:#3c8dbc;margin-right:6px;"></i>
            {{ $prospecto->nombre_completo }}
            <span class="pro-badge pro-badge-{{ $prospecto->etapa }}" style="margin-left:8px;vertical-align:middle;">
                {{ $etapas[$prospecto->etapa] ?? $prospecto->etapa }}
            </span>
        </h2>
        <div class="pro-header-actions">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalSeguimiento">
                <i class="fa fa-plus"></i> Seguimiento
            </button>
            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEtapa">
                <i class="fa fa-exchange"></i> Cambiar etapa
            </button>
            @if ($prospecto->etapa === 'aceptado' && !$prospecto->alumno_id)
                <a href="{{ route('alumnos.create', ['prospecto_id' => $prospecto->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-user-plus"></i> Registrar como alumno
                </a>
            @endif
            <a href="{{ route('prospectos.index') }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Admisiones
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Columna principal --}}
        <div class="col-md-8">

            {{-- Datos del prospecto --}}
            <div class="pro-panel">
                <div class="pro-panel-header">
                    <div class="pro-panel-header-left">
                        <i class="fa fa-child" style="color:#3c8dbc;"></i>
                        <span class="title">Datos del prospecto</span>
                    </div>
                </div>
                <div class="pro-panel-body">
                    {{-- Fila 1: alumno --}}
                    <div class="row">
                        <div class="col-md-5">
                            <div class="pro-data-label">Nivel de interés</div>
                            <div class="pro-data-value">{{ optional($prospecto->nivelInteres)->nombre ?: 'Sin definir' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="pro-data-label">Fecha de nacimiento</div>
                            <div class="pro-data-value">{{ optional($prospecto->fecha_nacimiento)->format('d/m/Y') ?: 'No registrada' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="pro-data-label">Primer contacto</div>
                            <div class="pro-data-value">{{ optional($prospecto->fecha_primer_contacto)->format('d/m/Y') ?: 'No registrada' }}</div>
                        </div>
                    </div>
                    {{-- Fila 2: contacto --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="pro-data-label">Contacto principal</div>
                            <div class="pro-data-value">{{ $prospecto->contacto_nombre }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="pro-data-label">Teléfono</div>
                            <div class="pro-data-value">{{ $prospecto->contacto_telefono }}</div>
                        </div>
                        <div class="col-md-5">
                            <div class="pro-data-label">Correo electrónico</div>
                            <div class="pro-data-value" style="word-break:break-all;">{{ $prospecto->contacto_email ?: 'Sin correo' }}</div>
                        </div>
                    </div>
                    {{-- Fila 3: canal / responsable --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="pro-data-label">Canal de contacto</div>
                            <div class="pro-data-value">{{ $prospecto->canal_contacto ? ucfirst(str_replace('_', ' ', $prospecto->canal_contacto)) : 'Sin canal' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="pro-data-label">Responsable</div>
                            <div class="pro-data-value">{{ optional($prospecto->responsable)->nombre ?: 'Sin asignar' }}</div>
                        </div>
                    </div>

                    @if ($prospecto->motivo_no_concrecion)
                        <div style="border-left:3px solid #e74c3c;padding:8px 12px;background:#fff5f5;border-radius:5px;margin-top:4px;">
                            <div class="pro-data-label" style="color:#c0392b;">Motivo de no concreción</div>
                            <div class="pro-data-value" style="margin-bottom:0;">{{ $prospecto->motivo_no_concrecion }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Seguimientos --}}
            <div class="pro-panel">
                <div class="pro-panel-header">
                    <div class="pro-panel-header-left">
                        <i class="fa fa-comments-o" style="color:#3c8dbc;"></i>
                        <span class="title">Seguimiento</span>
                    </div>
                </div>
                <div class="pro-panel-body">
                    @forelse ($prospecto->seguimientos as $seguimiento)
                        <div class="seg-item">
                            <div class="seg-tipo">{{ $tiposSeguimiento[$seguimiento->tipo_accion] ?? ucfirst($seguimiento->tipo_accion) }}</div>
                            <div class="seg-meta">
                                {{ optional($seguimiento->fecha)->format('d/m/Y') ?: '-' }}
                                &nbsp;·&nbsp;
                                {{ optional($seguimiento->usuario)->nombre ?: 'Sistema' }}
                            </div>
                            <div class="seg-nota">{{ $seguimiento->notas }}</div>
                        </div>
                    @empty
                        <p class="text-muted" style="font-size:13px;margin:0;">No hay seguimientos registrados.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Columna lateral --}}
        <div class="col-md-4">

            {{-- Resumen --}}
            <div class="pro-resumen">
                <div class="res-title"><i class="fa fa-bar-chart" style="color:#3c8dbc;"></i> Resumen</div>
                <div class="res-row">
                    <span>Total seguimientos</span>
                    <span class="res-val">{{ $prospecto->seguimientos->count() }}</span>
                </div>
                <div class="res-row">
                    <span>Documentos pendientes</span>
                    <span class="res-val">{{ $prospecto->documentos->where('estado', 'pendiente')->count() }}</span>
                </div>
                <div class="res-row">
                    <span>Alumno vinculado</span>
                    <span class="res-val">{{ $prospecto->alumno_id ? 'Sí' : 'No' }}</span>
                </div>
            </div>

            {{-- Documentos --}}
            <div class="pro-panel">
                <div class="pro-panel-header">
                    <div class="pro-panel-header-left">
                        <i class="fa fa-folder-open-o" style="color:#3c8dbc;"></i>
                        <span class="title">Documentos</span>
                    </div>
                    <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modalDocumento">
                        <i class="fa fa-plus"></i> Agregar
                    </button>
                </div>
                <div class="pro-panel-body">
                    @forelse ($prospecto->documentos as $documento)
                        <div class="doc-item">
                            <div class="doc-icon"><i class="fa fa-file-text"></i></div>
                            <div style="flex:1;min-width:0;">
                                <div class="doc-name">{{ $documento->tipo_documento }}</div>
                                <span class="doc-badge doc-badge-{{ $documento->estado }}">
                                    {{ str_replace('_', ' ', $documento->estado) }}
                                </span>
                            </div>
                            @if ($documento->archivo_url)
                                <a href="{{ route('prospectos.documentos.archivo', [$prospecto->id, $documento->id]) }}"
                                   class="btn btn-default btn-xs" title="{{ $documento->archivo_nombre ?: 'Ver archivo' }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted" style="font-size:13px;margin:0;">No hay documentos cargados.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Agregar seguimiento --}}
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
                            <input type="date" class="form-control" id="fecha" name="fecha"
                                value="{{ old('fecha', now()->toDateString()) }}" required>
                            <p class="help-block" style="font-size:11px;">Indica la fecha en que ocurrió la llamada, visita, correo o nota.</p>
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

    {{-- Modal: Agregar documento --}}
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
                            <label for="otro_documento">¿Cuál documento es?</label>
                            <input type="text" class="form-control" id="otro_documento" name="otro_documento"
                                value="{{ old('otro_documento') }}" maxlength="120">
                            <p class="help-block" style="font-size:11px;">Escribe el nombre del documento si no aparece en la lista.</p>
                        </div>
                        <div class="form-group">
                            <label for="archivo">Archivo</label>
                            <input type="file" class="form-control" id="archivo" name="archivo"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            <p class="help-block" style="font-size:11px;">Formatos: PDF, JPG, PNG, DOC, DOCX. Máximo 5 MB.</p>
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

    {{-- Modal: Cambiar etapa --}}
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
                            <label for="motivo_no_concrecion">Motivo de no concreción</label>
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
