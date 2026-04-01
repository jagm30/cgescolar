@extends('layouts.master')
@section('page_title', 'Conceptos')

@section('content')
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Listado de Conceptos</h3>
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modalNuevoConcepto">
                <i class="fa fa-plus"></i> Nuevo Concepto
            </button>
        </div>
        <div class="box-body">

            <div
                style="background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #3c8dbc;">
                <form method="GET" action="{{ route('conceptos.index') }}">
                    <div class="row">
                        {{-- Mostrar N registros --}}
                        <div class="col-md-2">
                            <select name="mostrar" class="form-control" title="Registros por página">
                                <option value="10" {{ request('mostrar', '10') == '10' ? 'selected' : '' }}>10 filas
                                </option>
                                <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25 filas</option>
                                <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50 filas</option>
                                <option value="-1" {{ request('mostrar') == '-1' ? 'selected' : '' }}>Todas</option>
                            </select>
                        </div>

                        {{-- Filtro Tipo --}}
                        <div class="col-md-3">
                            <select name="tipo" class="form-control">
                                <option value="">Todos los tipos</option>
                                <option value="colegiatura" {{ request('tipo') == 'colegiatura' ? 'selected' : '' }}>
                                    Colegiatura</option>
                                <option value="inscripcion" {{ request('tipo') == 'inscripcion' ? 'selected' : '' }}>
                                    Inscripción</option>
                                <option value="cargo_unico" {{ request('tipo') == 'cargo_unico' ? 'selected' : '' }}>Cargo
                                    Único</option>
                                <option value="cargo_recurrente"
                                    {{ request('tipo') == 'cargo_recurrente' ? 'selected' : '' }}>Cargo Recurrente</option>
                            </select>
                        </div>

                        {{-- Filtro Estatus --}}
                        <div class="col-md-2">
                            <select name="activo" class="form-control">
                                <option value="">Estatus...</option>
                                <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>

                        {{-- Buscador --}}
                        <div class="col-md-3">
                            <input type="text" name="buscar" class="form-control" placeholder="Buscar concepto..."
                                value="{{ request('buscar') }}">
                        </div>

                        {{-- Botones --}}
                        <div class="col-md-2 text-right">
                            <button type="submit" class="btn btn-primary" title="Filtrar BD"><i
                                    class="fa fa-search"></i></button>
                            <a href="{{ route('conceptos.index') }}" class="btn btn-default" title="Limpiar"><i
                                    class="fa fa-eraser"></i> Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>

            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Descripcion</th>
                        <th>Tipo</th>
                        <th>Aplica beca</th>
                        <th>Aplica recargo</th>
                        <th>Clave</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conceptos as $concepto)
                        <tr>
                            <td>{{ $concepto->nombre }}</td>
                            <td>{{ $concepto->descripcion }}</td>
                            <td>
                                @php
                                    $badgeClass = match ($concepto->tipo) {
                                        'colegiatura' => 'label-success',
                                        'inscripcion' => 'label-info',
                                        'cargo_unico' => 'label-warning',
                                        'cargo_recurrente' => 'label-danger',
                                        default => 'label-default',
                                    };
                                @endphp
                                <span class="label {{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $concepto->tipo)) }}
                                </span>
                            </td>
                            <td>{{ $concepto->aplica_beca ? 'Sí' : 'No' }}</td>
                            <td>{{ $concepto->aplica_recargo ? 'Sí' : 'No' }}</td>
                            <td>{{ $concepto->clave_sat }}</td>
                            <td>{{ $concepto->activo ? 'Activo' : 'Inactivo' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#modalEditarConcepto{{ $concepto->id }}" title="Editar Concepto">
                                    <i class="fa fa-pencil"></i> Editar
                                </button>

                                <form action="{{ route('conceptos.destroy', $concepto->id) }}" method="POST"
                                    style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Estás seguro de que deseas desactivar el concepto: {{ $concepto->nombre }}?');"
                                        title="Desactivar Concepto">
                                        <i class="fa fa-ban"></i> Desactivar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <x-modal id="modalNuevoConcepto" title="<i class='fa fa-plus-circle'></i> Agregar Nuevo Concepto" size="modal-lg">
        <form action="{{ route('conceptos.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-tag"></i> Nombre del Concepto</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Inscripción Semestral"
                            required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-list"></i> Tipo</label>
                        {{-- Cambio a CLASE en lugar de ID --}}
                        <select name="tipo" class="form-control select-tipo-dinamico" required>
                            <option value="">Seleccione un tipo...</option>
                            <option value="colegiatura">Colegiatura</option>
                            <option value="inscripcion">Inscripción</option>
                            <option value="cargo_unico">Cargo Unico</option>
                            <option value="cargo_recurrente">Cargo Recurrente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-key"></i> Clave SAT</label>
                        <input type="text" name="clave_sat" class="form-control" maxlength="8"
                            placeholder="Clave de producto o servicio">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-align-left"></i> Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Breve descripción del concepto..."></textarea>
                    </div>

                    <div class="well well-sm">
                        <label style="display: block; margin-bottom: 10px;">
                            <strong>Configuraciones adicionales:</strong>
                        </label>

                        <div class="checkbox">
                            <label>
                                {{-- Cambio a CLASE en lugar de ID --}}
                                <input type="checkbox" name="aplica_beca" class="checkbox-beca-dinamico">
                                <span class="text-primary">Aplica para beca</span>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="aplica_recargo">
                                <span class="text-warning">Aplica recargo por mora</span>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="activo" checked>
                                <span class="label label-success">Estatus Activo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <hr style="margin-top: 10px; margin-bottom: 15px;">

            <div class="clearfix" style="padding-bottom: 10px;">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary pull-right">
                    <i class="fa fa-save"></i> Guardar Concepto
                </button>
            </div>
        </form>
    </x-modal>

    @foreach ($conceptos as $concepto)
        <x-modal id="modalEditarConcepto{{ $concepto->id }}"
            title="<i class='fa fa-pencil'></i> Editar Concepto: {{ $concepto->nombre }}" size="modal-lg">
            <form action="{{ route('conceptos.update', $concepto->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fa fa-tag"></i> Nombre del Concepto</label>
                            <input type="text" name="nombre" class="form-control" value="{{ $concepto->nombre }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-list"></i> Tipo</label>
                            {{-- Cambio a CLASE --}}
                            <select name="tipo" class="form-control select-tipo-dinamico" required>
                                <option value="colegiatura" {{ $concepto->tipo == 'colegiatura' ? 'selected' : '' }}>
                                    Colegiatura</option>
                                <option value="inscripcion" {{ $concepto->tipo == 'inscripcion' ? 'selected' : '' }}>
                                    Inscripción</option>
                                <option value="cargo_unico" {{ $concepto->tipo == 'cargo_unico' ? 'selected' : '' }}>Cargo
                                    Unico</option>
                                <option value="cargo_recurrente"
                                    {{ $concepto->tipo == 'cargo_recurrente' ? 'selected' : '' }}>Cargo Recurrente</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-key"></i> Clave SAT</label>
                            <input type="text" name="clave_sat" class="form-control" maxlength="8"
                                value="{{ $concepto->clave_sat }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fa fa-align-left"></i> Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="4">{{ $concepto->descripcion }}</textarea>
                        </div>

                        <div class="well well-sm">
                            <label style="display: block; margin-bottom: 10px;">
                                <strong>Configuraciones adicionales:</strong>
                            </label>

                            <div class="checkbox">
                                <label>
                                    {{-- Cambio a CLASE --}}
                                    <input type="checkbox" name="aplica_beca" class="checkbox-beca-dinamico"
                                        {{ $concepto->aplica_beca ? 'checked' : '' }}>
                                    <span class="text-primary">Aplica para beca</span>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="aplica_recargo"
                                        {{ $concepto->aplica_recargo ? 'checked' : '' }}>
                                    <span class="text-warning">Aplica recargo por mora</span>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="activo" {{ $concepto->activo ? 'checked' : '' }}>
                                    <span class="label {{ $concepto->activo ? 'label-success' : 'label-danger' }}">
                                        Estatus {{ $concepto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="margin-top: 10px; margin-bottom: 15px;">

                <div class="clearfix" style="padding-bottom: 10px;">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning pull-right">
                        <i class="fa fa-refresh"></i> Actualizar Concepto
                    </button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection

@push('scripts')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // 1. Inicialización de DataTables
            $('#example1').DataTable({
                "searching": false,
                "lengthChange": false,
                "pageLength": {{ request('mostrar', 10) }},
                "columnDefs": [{
                    "targets": [1, 2, 3, 4, 5, 6, 7],
                    "orderable": false
                }]
            });

            // 2. Lógica Dinámica de los Selects (Aplica para Crear y Editar)
            $('.select-tipo-dinamico').on('change', function() {
                let valorSeleccionado = $(this).val();
                // Encuentra el checkbox específico dentro del mismo modal/formulario
                let checkboxBeca = $(this).closest('form').find('.checkbox-beca-dinamico');

                if (valorSeleccionado === 'colegiatura') {
                    // Se marca y habilita para que el usuario pueda interactuar si quiere
                    checkboxBeca.prop('checked', true).prop('disabled', false);
                } else {
                    // Lo desmarcamos y LO BLOQUEAMOS (gris)
                    checkboxBeca.prop('checked', false).prop('disabled', true);
                }
            });

            //  Ejecutar la validación al cargar la página
            // Esto asegura que los modales de edición ya vengan con el checkbox bloqueado o desbloqueado correctamente
            $('.select-tipo-dinamico').trigger('change');
        });
    </script>
@endpush
