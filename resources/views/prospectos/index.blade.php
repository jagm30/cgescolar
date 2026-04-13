@extends('layouts.master')

@section('page_title', 'Prospectos')
@section('page_subtitle', 'Control de admisiones')

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

        $badges = [
            'prospecto' => 'bg-light-blue',
            'cita' => 'bg-aqua',
            'visita' => 'bg-teal',
            'documentacion' => 'bg-yellow',
            'aceptado' => 'bg-green',
            'inscrito' => 'bg-navy',
            'no_concretado' => 'bg-red',
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
            <strong>Hay errores en el formulario.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total</span>
                    <span class="info-box-number">{{ $prospectos->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">En página</span>
                    <span class="info-box-number">{{ $prospectos->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-filter"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Filtro etapa</span>
                    <span class="info-box-number">{{ request('etapa') ? ($etapas[request('etapa')] ?? request('etapa')) : 'Todas' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-purple"><i class="fa fa-bar-chart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Métricas</span>
                    <span class="info-box-number"><a href="{{ route('prospectos.metricas', ['ciclo_id' => $cicloId]) }}" class="text-black">Ver reporte</a></span>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Tabla de prospectos</h3>
            <div class="box-tools pull-right">
                <a href="{{ route('prospectos.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Nuevo prospecto
                </a>
                <a href="{{ route('prospectos.metricas', ['ciclo_id' => $cicloId]) }}" class="btn btn-default btn-sm">
                    <i class="fa fa-line-chart"></i> Métricas
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('prospectos.index') }}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="buscar">Buscar</label>
                            <input type="text" class="form-control" id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, contacto o teléfono">
                        </div>
                    </div>
                    <div class="col-md-3">
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
                            <label for="etapa">Etapa</label>
                            <select class="form-control" id="etapa" name="etapa">
                                <option value="">Todas</option>
                                @foreach ($etapas as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ request('etapa') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="en_proceso">Estado</label>
                            <select class="form-control" id="en_proceso" name="en_proceso">
                                <option value="">Todos</option>
                                <option value="1" {{ request('en_proceso') ? 'selected' : '' }}>En proceso</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="per_page">Por página</label>
                            <select class="form-control" id="per_page" name="per_page">
                                @foreach ([10, 20, 50] as $size)
                                    <option value="{{ $size }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-default btn-block">Ir</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="box-body table-responsive no-padding">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Prospecto</th>
                        <th>Contacto</th>
                        <th>Nivel</th>
                        <th>Canal</th>
                        <th>Ciclo</th>
                        <th>Etapa</th>
                        <th>Fecha</th>
                        <th>Responsable</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prospectos as $prospecto)
                        <tr>
                            <td>
                                <strong>{{ $prospecto->nombre }}</strong><br>
                                <small class="text-muted">{{ optional($prospecto->fecha_nacimiento)->format('d/m/Y') ?: 'Sin fecha de nacimiento' }}</small>
                            </td>
                            <td>
                                {{ $prospecto->contacto_nombre }}<br>
                                <small>{{ $prospecto->contacto_telefono }}</small><br>
                                <small class="text-muted">{{ $prospecto->contacto_email ?: 'Sin correo' }}</small>
                            </td>
                            <td>{{ optional($prospecto->nivelInteres)->nombre ?: 'Sin definir' }}</td>
                            <td>{{ $prospecto->canal_contacto ? ucfirst(str_replace('_', ' ', $prospecto->canal_contacto)) : 'Sin canal' }}</td>
                            <td>{{ optional($prospecto->ciclo)->nombre ?: 'Sin ciclo' }}</td>
                            <td>
                                <span class="label {{ $badges[$prospecto->etapa] ?? 'bg-gray' }}">
                                    {{ $etapas[$prospecto->etapa] ?? $prospecto->etapa }}
                                </span>
                            </td>
                            <td>{{ optional($prospecto->fecha_primer_contacto)->format('d/m/Y') ?: '-' }}</td>
                            <td>{{ optional($prospecto->responsable)->nombre ?: 'Sin asignar' }}</td>
                            <td class="text-right">
                                <a href="{{ route('prospectos.show', $prospecto->id) }}" class="btn btn-xs btn-info">Ver detalles</a>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-warning"
                                    data-toggle="modal"
                                    data-target="#modalEtapa"
                                    data-id="{{ $prospecto->id }}"
                                    data-nombre="{{ $prospecto->nombre }}"
                                    data-etapa="{{ $prospecto->etapa }}"
                                    data-motivo="{{ $prospecto->motivo_no_concrecion }}">
                                    Cambiar etapa
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted" style="padding: 30px;">
                                No hay prospectos registrados con los filtros actuales.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($prospectos->hasPages())
            <div class="box-footer clearfix">
                {{ $prospectos->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <div class="modal fade" id="modalEtapa" tabindex="-1" role="dialog" aria-labelledby="modalEtapaLabel">
        <div class="modal-dialog" role="document">
            <form method="POST" id="formCambiarEtapa">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modalEtapaLabel">Cambiar etapa</h4>
                    </div>
                    <div class="modal-body">
                        <p>Prospecto: <strong id="modalProspectoNombre">-</strong></p>
                        <div class="form-group">
                            <label for="modal_etapa">Nueva etapa</label>
                            <select class="form-control" id="modal_etapa" name="etapa" required>
                                @foreach ($etapas as $valor => $etiqueta)
                                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="modal_notas">Notas</label>
                            <textarea class="form-control" id="modal_notas" name="notas" rows="4" required placeholder="Describe el motivo del cambio"></textarea>
                        </div>
                        <div class="form-group" id="contenedorMotivoNoConcrecion" style="display: none;">
                            <label for="modal_motivo_no_concrecion">Motivo no concreción</label>
                            <textarea class="form-control" id="modal_motivo_no_concrecion" name="motivo_no_concrecion" rows="3" placeholder="Indica por qué no se concreto"></textarea>
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
            var modal = $('#modalEtapa');
            var form = $('#formCambiarEtapa');
            var etapaSelect = $('#modal_etapa');
            var motivoGroup = $('#contenedorMotivoNoConcrecion');
            var motivoInput = $('#modal_motivo_no_concrecion');

            function toggleMotivo() {
                var mostrar = etapaSelect.val() === 'no_concretado';
                motivoGroup.toggle(mostrar);
                motivoInput.prop('required', mostrar);
            }

            modal.on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);

                form.attr('action', '{{ url('prospectos') }}/' + button.data('id') + '/etapa');
                $('#modalProspectoNombre').text(button.data('nombre'));
                etapaSelect.val(button.data('etapa'));
                motivoInput.val(button.data('motivo') || '');
                $('#modal_notas').val('');
                toggleMotivo();
            });

            etapaSelect.on('change', toggleMotivo);
        });
    </script>
@endpush
