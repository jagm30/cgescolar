@extends('layouts.master')

@section('page_title', 'Gestión de Grados')
@section('page_subtitle', 'Configuración académica')

@push('styles')
    <style>
        .con-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            width: 100%;
        }

        .con-stat-card {
            flex: 0 0 auto;
            min-width: 200px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-top: 3px solid #3c8dbc;
            border-radius: 6px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .con-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf3fb;
            flex-shrink: 0;
            color: #3c8dbc;
            font-size: 18px;
        }

        .con-stat-num {
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
            color: #222;
        }

        .con-stat-lbl {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: .04em;
        }


        .btn-registrar-simple {
            margin-left: auto;
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


        .con-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            border-radius: 4px 4px 0 0;
        }

        .con-select {
            height: 34px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            background: #fff !important;
        }

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

        .con-badge-nivel {
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #b3d4f5;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .con-clave {
            font-family: monospace;
            background: #f0f3f7;
            padding: 2px 7px;
            border-radius: 4px;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')

    <div class="con-stats">
        <div class="con-stat-card">
            <div class="con-stat-icon"><i class="fa fa-graduation-cap"></i></div>
            <div>
                <div class="con-stat-num">{{ $grados->count() }}</div>
                <div class="con-stat-lbl">Grados Totales</div>
            </div>
        </div>

        <button class="btn-registrar-simple" data-toggle="modal" data-target="#modal-nuevo">
            <i class="fa fa-plus"></i>
            <span>Registrar Nuevo Grado</span>
        </button>
    </div>

    <div class="box box-solid" style="border-radius: 6px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.05);">
        {{-- Toolbar de Filtros --}}
        <div class="con-toolbar">
            <form method="GET" action="{{ route('grados.index') }}" id="form-filtros"
                style="display: flex; gap: 10px; width: 100%;">
                <select name="mostrar" class="con-select" style="width: 110px;" onchange="this.form.submit()">
                    <option value="10" {{ request('mostrar') == '10' ? 'selected' : '' }}>10 filas</option>
                    <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25 filas</option>
                    <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50 filas</option>
                </select>

                <select name="nivel_id" class="con-select" onchange="this.form.submit()">
                    <option value="">Todos los niveles</option>
                    @foreach ($niveles as $nivel)
                        <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                            {{ $nivel->nombre }}</option>
                    @endforeach
                </select>

                <div style="margin-left: auto;">
                    <a href="{{ route('grados.index') }}" class="btn btn-default btn-sm"><i class="fa fa-eraser"></i>
                        Limpiar</a>
                </div>
            </form>
        </div>

        <div class="box-body no-padding">
            <table class="con-table">
                <thead>
                    <tr>
                        <th width="20%">Nivel</th>
                        <th width="15%">Ordinal</th>
                        <th>Nombre del Grado</th>
                        <th width="150px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grados as $grado)
                        <tr>
                            <td><span class="con-badge-nivel">{{ $grado->nivel->nombre }}</span></td>
                            <td><span class="con-clave">{{ $grado->numero }}° Año</span></td>
                            <td>
                                <div class="con-nombre">{{ $grado->nombre }}</div>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <button class="btn btn-default btn-xs" data-toggle="modal" data-target="#modal-editar"
                                        data-id="{{ $grado->id }}" data-nombre="{{ $grado->nombre }}"
                                        data-numero="{{ $grado->numero }}" data-nivel="{{ $grado->nivel_id }}">
                                        <i class="fa fa-pencil text-blue"></i>
                                    </button>
                                    <form action="{{ route('grados.destroy', $grado->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-default btn-xs"
                                            onclick="return confirm('¿Eliminar?')">
                                            <i class="fa fa-trash text-red"></i>
                                        </button>
                                    </form>
                                </div>
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
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Grado</label>
                        <input type="number" name="numero" id="edit-numero" class="form-control" min="1"
                            required>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
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
            // Cuando el modal termine de abrirse, poner el foco en el primer campo y seleccionar todo
            $('#modal-editar').on('shown.bs.modal', function() {
                $('#edit-nombre').focus().select();
            });

            // Hacer que cualquier input que reciba clic dentro del modal se seleccione automáticamente
            $('#modal-editar input').on('focus', function() {
                $(this).select();
            });
        });
    </script>
@endpush
