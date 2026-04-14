@extends('layouts.master')
@section('page_title', 'Niveles Escolares')

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Listado de Niveles</h3>
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modal-nuevo">
                <i class="fa fa-plus"></i> Nuevo Nivel
            </button>
        </div>

        <div id="contenedor-filtro" style="float: left; margin-right: 15px;">
            <label style="font-weight: normal;">Estado:
                <select id="filtro-estado" class="form-control input-sm" style="width: auto; display: inline-block;">
                    <option value="">Todos</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </label>
        </div>

        <div class="box-body">
            <table id="niveles" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50">Orden</th>
                        <th>Nombre del Nivel</th>
                        <th>REVOE</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($niveles as $nivel)
                        <tr>
                            <td class="text-center"><b>{{ $nivel->orden }}</b></td>
                            <td>{{ $nivel->nombre }}</td>
                            <td>{{ $nivel->revoe ?? 'N/A' }}</td>
                            <td>
                                <span class="label {{ $nivel->activo ? 'label-success' : 'label-danger' }}">
                                    {{ $nivel->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#modal-editar" data-id="{{ $nivel->id }}"
                                    data-nombre="{{ $nivel->nombre }}" data-revoe="{{ $nivel->revoe }}"
                                    data-orden="{{ $nivel->orden }}" data-activo="{{ $nivel->activo }}">
                                    <i class="fa fa-pencil"></i> Editar
                                </button>

                                @if ($nivel->activo)
                                    <form action="{{ route('niveles.destroy', $nivel->id) }}" method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de dar de baja este nivel?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fa fa-arrow-down"></i> Dar de baja
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <x-modal id="modal-editar" title="Editar Nivel Escolar">
        <form id="form-editar" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nombre del Nivel</label>
                        <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Orden Visual</label>
                        <input type="number" name="orden" id="edit-orden" class="form-control" min="1" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>REVOE <small class="text-muted">(Dato informativo - No editable)</small></label>
                <input type="text" id="edit-revoe" class="form-control" readonly style="background-color: #eee;">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="activo" id="edit-activo" class="form-control" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL NUEVO --}}
    <x-modal id="modal-nuevo" title="Registrar Nuevo Nivel">
        <form action="{{ route('niveles.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-9">
                    <div class="form-group">
                        <label>Nombre del Nivel</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Primaria" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" name="orden" class="form-control" value="1" min="1" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>REVOE</label>
                <input type="text" name="revoe" class="form-control" placeholder="Opcional">
            </div>

            <input type="hidden" name="activo" value="1">

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Crear Nivel</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#niveles').DataTable({
                "language": {
                    "url": "{{ asset('/bower_components/idioma/datatables_spanish.json') }}"
                },
                "order": [
                    [0, "asc"]
                ], // Ordenar por la columna 0 (Orden visual) por defecto
                "initComplete": function() {
                    var filtro = $('#contenedor-filtro');
                    $('#niveles_length').after(filtro);
                }
            });

            $(document).on('change', '#filtro-estado', function() {
                var valor = $(this).val();
                table.column(3).search(valor).draw();
            });
        });

        // Script para llenar el modal de edición
        $('#modal-editar').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nombre = button.data('nombre');
            var revoe = button.data('revoe');
            var orden = button.data('orden');
            var activo = button.data('activo');

            var modal = $(this);
            modal.find('#edit-nombre').val(nombre);
            modal.find('#edit-revoe').val(revoe ? revoe : 'N/A');
            modal.find('#edit-orden').val(orden);
            modal.find('#edit-activo').val(activo);

            var url = "{{ route('niveles.update', ':id') }}";
            url = url.replace(':id', id);
            modal.find('#form-editar').attr('action', url);
        });
    </script>
@endpush
