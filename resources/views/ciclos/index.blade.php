@extends('layouts.master')
@section('page_title', 'Ciclos escolares')

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"></h3>
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modal-nuevo">
                <i class="fa fa-plus"></i> Nuevo Ciclo Escolar
            </button>

        </div>

        <div id="contenedor-filtro" style="float: left; margin-right: 15px;">
            <label style="font-weight: normal;">Estado:
                <select id="filtro-estado" class="form-control input-sm" style="width: auto; display: inline-block;">
                    <option value="">Todos</option>
                    <option value="Activo">Activo</option>
                    <option value="Cerrado">Cerrado</option>
                    <option value="Configuracion">Configuración</option>
                </select>
            </label>
        </div>

        <div class="box-body">
            <table id="ciclos" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nombre del Ciclo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ciclos as $ciclo)
                        <tr>
                            <td>{{ $ciclo->nombre }}</td>
                            <td>{{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($ciclo->fecha_fin)->format('d/m/Y') }}</td>
                            <td>
                                <span
                                    class="label {{ $ciclo->estado == 'activo'
                                        ? 'label-success'
                                        : ($ciclo->estado == 'cerrado'
                                            ? 'label-warning'
                                            : ($ciclo->estado == 'configuracion'
                                                ? 'label-info'
                                                : 'label-default')) }}">
                                    {{ ucfirst($ciclo->estado) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#modal-editar" data-id="{{ $ciclo->id }}"
                                    data-nombre="{{ $ciclo->nombre }}" data-inicio="{{ $ciclo->fecha_inicio }}"
                                    data-fin="{{ $ciclo->fecha_fin }}" data-estado="{{ $ciclo->estado }}">
                                    <i class="fa fa-pencil"></i> Editar
                                </button>

                                <form action="{{ route('ciclos.seleccionar', $ciclo->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-info btn-sm">
                                        Seleccionar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection
<x-modal id="modal-editar" title="Editar Ciclo Escolar">
    <form id="form-editar" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre del Ciclo</label>
            <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" id="edit-inicio" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="edit-fin" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" id="edit-estado" class="form-control" required>
                <option value="activo">Activo</option>
                <option value="cerrado">Cerrado</option>
                <option value="configuracion">Configuración</option>
            </select>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Actualizar Ciclo</button>
        </div>
    </form>
</x-modal>

<x-modal id="modal-nuevo" title="Registrar Nuevo Ciclo Escolar">
    <form action="{{ route('ciclos.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nombre del Ciclo</label>
            <input type="text" name="nombre" class="form-control" placeholder="Ej: 2026-2027" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Estado Inicial</label>
            <select name="estado" class="form-control" required>
                <option value="configuracion" selected>Configuración</option>
                <option value="activo">Activo</option>
                <option value="cerrado">Cerrado</option>
            </select>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Crear Ciclo</button>
        </div>
    </form>
</x-modal>

@push('scripts')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    {{-- configuracion de datatables para mostrar el filtro al lado del selector de "Mostrar" y agregar el filtro por estado,
    ademas de configurar el idioma a español --}}
    <script>
        $(document).ready(function() {
            var table = $('#ciclos').DataTable({
                "language": {
                    "url": "{{ asset('/bower_components/idioma/datatables_spanish.json') }}"
                },
                "initComplete": function() {

                    var filtro = $('#contenedor-filtro');
                    $('#ciclos_length').after(filtro);
                }
            });

            // Filtro por columna
            $(document).on('change', '#filtro-estado', function() {
                var valor = $(this).val();
                table.column(3).search(valor).draw();
            });
        });
    </script>
    <script>
        $('#modal-editar').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);

            var id = button.data('id');
            var nombre = button.data('nombre');
            var estado = button.data('estado');


            var inicio = button.data('inicio').split(' ')[0];
            var fin = button.data('fin').split(' ')[0];

            var modal = $(this);

            modal.find('#edit-nombre').val(nombre);
            modal.find('#edit-inicio').val(inicio);
            modal.find('#edit-fin').val(fin);
            modal.find('#edit-estado').val(estado);

            var url = "{{ route('ciclos.update', ':id') }}";
            url = url.replace(':id', id);
            modal.find('#form-editar').attr('action', url);
        });
    </script>
    <script>
        $('#modal-nuevo').on('show.bs.modal', function() {
            $(this).find('form')[0].reset();
        });
    </script>
@endpush
