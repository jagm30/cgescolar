@extends('layouts.master')
@section('page_title', 'Ciclos Escolares')
@section('page_subtitle', 'Configuración académica')

@push('styles')
    <style>
        /* ══════════════════════════════════════════
                       CABECERA (Solo Botón a la Derecha)
                    ══════════════════════════════════════════ */
        .con-stats {
            display: flex;
            justify-content: flex-end;
            /* Empuja el botón a la derecha */
            margin-bottom: 20px;
            width: 100%;
        }

        /* BOTÓN REGISTRAR SIMPLE (Estilo Píldora) */
        .btn-registrar-simple {
            background-color: #00a65a;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 40px;
            white-space: nowrap;
        }

        .btn-registrar-simple:hover {
            background-color: #008d4c;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* ══════════════════════════════════════════
                       TABLA Y COMPONENTES
                    ══════════════════════════════════════════ */
        .con-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .con-table thead tr th {
            background: #f4f6f8;
            color: #6b7a8d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 12px 14px;
            border-bottom: 2px solid #e0e6ed;
        }

        .con-table td {
            padding: 12px 14px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f0f3f7;
        }

        .con-nombre {
            font-weight: 700;
            color: #1a2634;
            font-size: 14px;
        }

        .con-fecha {
            font-family: monospace;
            color: #4a5568;
            background: #f8fafc;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        /* Badges de Estado */
        .badge-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-activo {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
        }

        .badge-cerrado {
            background: #f4f6f8;
            color: #7a8898;
            border: 1px solid #d0d9e2;
        }

        .badge-config {
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #b3d4f5;
        }

        /* Botón Activar (Seleccionar) */
        .btn-activar {
            background: #fff;
            border: 1px solid #d0dbe6;
            color: #3c8dbc;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }

        .btn-activar:hover {
            background: #3c8dbc;
            color: white;
            border-color: #3c8dbc;
        }
    </style>
@endpush

@section('content')

    {{-- CABECERA: Solo Botón --}}
    <div class="con-stats">
        <button class="btn-registrar-simple" data-toggle="modal" data-target="#modal-nuevo">
            <i class="fa fa-plus"></i>
            <span>Nuevo Ciclo Escolar</span>
        </button>
    </div>

    <div class="box box-solid" style="border-radius: 6px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.05);">
        <div class="box-body no-padding">
            <table class="con-table">
                <thead>
                    <tr>
                        <th>Nombre del Ciclo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th class="text-center">Estado</th>
                        <th width="220px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ciclos as $ciclo)
                        <tr>
                            <td>
                                <div class="con-nombre">{{ $ciclo->nombre }}</div>
                            </td>
                            <td><span
                                    class="con-fecha">{{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }}</span>
                            </td>
                            <td><span
                                    class="con-fecha">{{ \Carbon\Carbon::parse($ciclo->fecha_fin)->format('d/m/Y') }}</span>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge-status 
                                    {{ $ciclo->estado == 'activo' ? 'badge-activo' : ($ciclo->estado == 'cerrado' ? 'badge-cerrado' : 'badge-config') }}">
                                    {{ ucfirst($ciclo->estado) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    {{-- Botón Activar (Seleccionar) --}}
                                    <form action="{{ route('ciclos.seleccionar', $ciclo->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-activar" title="Seleccionar este ciclo">
                                            <i class="fa fa-check-circle"></i> Seleccionar
                                        </button>
                                    </form>

                                    {{-- Botón Editar --}}
                                    <button class="btn btn-default btn-xs"
                                        style="border-radius: 50%; width: 26px; height: 26px;" data-toggle="modal"
                                        data-target="#modal-editar" data-id="{{ $ciclo->id }}"
                                        data-nombre="{{ $ciclo->nombre }}" data-inicio="{{ $ciclo->fecha_inicio }}"
                                        data-fin="{{ $ciclo->fecha_fin }}" data-estado="{{ $ciclo->estado }}">
                                        <i class="fa fa-pencil text-blue"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
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


@endsection

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
