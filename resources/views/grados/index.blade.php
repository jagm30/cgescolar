@extends('layouts.master')
@section('page_title', 'Gestión de Grados')

@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Listado de Grados Académicos</h3>
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modal-nuevo">
                <i class="fa fa-plus"></i> Nuevo Grado
            </button>
        </div>

        <div class="box-body">
            {{-- SECCIÓN DE FILTROS ADAPTADA --}}
            <form method="GET" action="{{ route('grados.index') }}" id="form-filtros">
                <div class="row">
                    {{-- Mostrar N registros --}}
                    <div class="col-md-2">
                        <label><small>Registros</small></label>
                        <select name="mostrar" class="form-control" onchange="this.form.submit()">
                            <option value="10" {{ request('mostrar', '10') == '10' ? 'selected' : '' }}>10 filas
                            </option>
                            <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25 filas</option>
                            <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50 filas</option>
                            <option value="-1" {{ request('mostrar') == '-1' ? 'selected' : '' }}>Todas</option>
                        </select>
                    </div>

                    {{-- Filtro Nivel (Sustituye a 'Tipo') --}}
                    <div class="col-md-4">
                        <label><small>Nivel Escolar</small></label>
                        <select name="nivel_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Todos los niveles</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel->id }}"
                                    {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro Grado (Sustituye a 'Estatus') --}}
                    <div class="col-md-3">
                        <label><small>Número de Grado</small></label>
                        <select name="numero" class="form-control" onchange="this.form.submit()">
                            <option value="">Cualquier grado</option>
                            @for ($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ request('numero') == $i ? 'selected' : '' }}>
                                    {{ $i }}° Grado</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="col-md-3 text-right" style="padding-top: 23px;">
                        <button type="submit" class="btn btn-primary" title="Filtrar">
                            <i class="fa fa-search"></i>
                        </button>
                        <a href="{{ route('grados.index') }}" class="btn btn-default" title="Limpiar">
                            <i class="fa fa-eraser"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <hr>

            <table id="grados" class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Nivel Escolar</th>
                        <th>Grado</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grados as $grado)
                        <tr>
                            <td>
                                <span>{{ $grado->nivel->nombre }}</span>
                            </td>
                            <td class="text-center"><b>{{ $grado->numero }}°</b></td>
                            <td>{{ $grado->nombre }}</td>
                            <td>
                                <button type="button" class="btn btn-warning btn-xs" data-toggle="modal"
                                    data-target="#modal-editar" data-id="{{ $grado->id }}"
                                    data-nombre="{{ $grado->nombre }}" data-numero="{{ $grado->numero }}"
                                    data-nivel="{{ $grado->nivel_id }}">
                                    <i class="fa fa-pencil"></i>
                                </button>

                                <form action="{{ route('grados.destroy', $grado->id) }}" method="POST"
                                    style="display:inline;" onsubmit="return confirm('¿Desea eliminar este grado?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL NUEVO --}}
    <x-modal id="modal-nuevo" title="Registrar Nuevo Grado">
        <form action="{{ route('grados.store') }}" method="POST">
            @csrf

            {{-- Nivel Escolar en su propia fila --}}
            <div class="form-group">
                <label>Nivel Escolar <span class="text-danger">*</span></label>
                <select name="nivel_id" class="form-control" required>
                    <option value="">Seleccione un nivel...</option>
                    @foreach ($niveles as $nivel)
                        <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Grado <span class="text-danger">*</span></label>
                        <input type="number" name="numero" class="form-control" min="1" placeholder="Ej: 1"
                            required>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nombre<span class="text-danger">*</span></label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Primero de Primaria"
                            required>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 15px 0 0 0;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> Guardar Grado
                </button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDITAR --}}
    <x-modal id="modal-editar" title="Editar Grado">
        <form id="form-editar" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nivel Escolar</label>
                <select name="nivel_id" id="edit-nivel_id" class="form-control" required>
                    @foreach ($niveles as $nivel)
                        <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nombre del Grado</label>
                        <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Número</label>
                        <input type="number" name="numero" id="edit-numero" class="form-control" min="1"
                            required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Configuración de DataTable
            var table = $('#grados').DataTable({
                "language": {
                    "url": "{{ asset('/bower_components/idioma/datatables_spanish.json') }}"
                },
                "order": [
                    [0, "asc"],
                    [1, "asc"]
                ],
                "pageLength": {{ request('mostrar') == '-1' ? 1000 : request('mostrar', 10) }}, // Se sincroniza con el filtro de arriba
                "dom": 'rtp', // 'f' eliminada para quitar el buscador de texto que pediste
            });
        });

        // Lógica para cargar datos en el Modal de Editar
        $('#modal-editar').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var modal = $(this);

            modal.find('#edit-nombre').val(button.data('nombre'));
            modal.find('#edit-numero').val(button.data('numero'));
            modal.find('#edit-nivel_id').val(button.data('nivel'));

            var url = "{{ route('grados.update', ':id') }}".replace(':id', id);
            modal.find('#form-editar').attr('action', url);
        });
    </script>
@endpush
