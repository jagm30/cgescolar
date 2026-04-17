@extends('layouts.master')

@section('page_title', 'Gestión de Grados')
@section('page_subtitle', 'Configuración académica')

@section('breadcrumb')
    <li class="active">Grados</li>
@endsection

@push('styles')
    <style>
        /* Estilos integrados del diseño profesional */
        .con-stats {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .con-stat-card {
            flex: 1;
            min-width: 130px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-top: 3px solid #3c8dbc;
            border-radius: 6px;
            padding: 14px 18px;
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
        }

        .con-stat-num {
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
            color: #222;
        }

        .con-stat-lbl {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .con-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            border-radius: 4px 4px 0 0;
            flex-wrap: wrap;
        }

        .con-select {
            height: 36px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 140px;
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
            letter-spacing: .06em;
            padding: 12px 14px;
            border-bottom: 2px solid #e0e6ed;
            border-top: none;
        }

        .con-table tbody tr:hover td {
            background: #f0f7ff !important;
        }

        .con-table td {
            padding: 12px 14px;
            vertical-align: middle;
            font-size: 13px;
            border-bottom: 1px solid #f0f3f7;
        }

        .con-nombre {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }

        .con-clave {
            font-family: monospace;
            font-size: 12px;
            background: #f0f3f7;
            padding: 2px 7px;
            border-radius: 4px;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        .con-badge-nivel {
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #b3d4f5;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 12px;
        }

        /* Ajuste para botones de acción con texto */
        .con-acciones {
            display: flex;
            gap: 8px;
            /* Espacio entre botones */
            justify-content: center;
        }

        .btn-accion-texto {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            /* Espacio entre icono y texto */
            padding: 6px 12px !important;
            /* Más relleno a los lados para alargarlos */
            border-radius: 4px !important;
            font-size: 12px !important;
            font-weight: 600;
            transition: all 0.2s;
            min-width: 90px;
            /* Asegura que todos tengan un largo similar */
        }

        .btn-accion-texto:hover {
            background-color: #f4f6f8 !important;
            border-color: #d0dbe6 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }


        /* Hacer las tarjetas de stats más pequeñas y compactas */
        .con-stat-card {
            flex: 0 1 auto;
            /* No obliga a crecer a todo el ancho */
            min-width: 200px;
            padding: 10px 15px;
        }

        .con-stat-num {
            font-size: 20px;
        }

        .btn-nuevo-grado {
            width: auto;
            padding: 8px 20px;
            font-weight: bold;
            height: 40px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
@endpush

@section('content')
@section('content')
    <div class="con-main-wrapper">

        {{-- Cabecera: Conteo y Botón en la misma línea --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div class="con-stat-card" style="margin-bottom: 0; border-top: 3px solid #3c8dbc;">
                <div class="con-stat-icon"><i class="fa fa-graduation-cap text-blue"></i></div>
                <div>
                    <div class="con-stat-num">{{ $grados->count() }}</div>
                    <div class="con-stat-lbl">Grados Totales</div>
                </div>
            </div>

            <button class="btn btn-success btn-nuevo-grado" data-toggle="modal" data-target="#modal-nuevo">
                <i class="fa fa-plus"></i> NUEVO GRADO
            </button>
        </div>

        <div class="box box-solid" style="border-radius: 6px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.05);">
            {{-- Toolbar de Filtros --}}
            <div class="con-toolbar">
                <form method="GET" action="{{ route('grados.index') }}" id="form-filtros"
                    style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;">
                    <select name="mostrar" class="con-select" style="width: 100px;" onchange="this.form.submit()">
                        <option value="10" {{ request('mostrar', '10') == '10' ? 'selected' : '' }}>10 filas</option>
                        <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25 filas</option>
                        <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50 filas</option>
                    </select>

                    <select name="nivel_id" class="con-select" onchange="this.form.submit()">
                        <option value="">Todos los niveles</option>
                        @foreach ($niveles as $nivel)
                            <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                {{ $nivel->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <select name="numero" class="con-select" style="width: 130px;" onchange="this.form.submit()">
                        <option value="">Cualquier grado</option>
                        @for ($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" {{ request('numero') == $i ? 'selected' : '' }}>
                                {{ $i }}° Grado</option>
                        @endfor
                    </select>

                    <div style="margin-left: auto; display: flex; gap: 5px;">
                        <a href="{{ route('grados.index') }}" class="btn btn-default btn-sm"><i
                                class="fa fa-eraser"></i></a>
                    </div>
                </form>
            </div>

            <div class="box-body no-padding">
                <table id="grados" class="con-table">
                    <thead>
                        <tr>
                            <th width="20%">Nivel</th>
                            <th width="15%">Ordinal</th>
                            <th>Nombre del Grado</th>
                            <th width="220px" class="text-center">Acciones</th>
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
                                    <div class="con-acciones">
                                        <button type="button" class="btn btn-default btn-sm btn-accion-texto"
                                            data-toggle="modal" data-target="#modal-editar" data-id="{{ $grado->id }}"
                                            data-nombre="{{ $grado->nombre }}" data-numero="{{ $grado->numero }}"
                                            data-nivel="{{ $grado->nivel_id }}">
                                            <i class="fa fa-pencil text-yellow"></i> <span>Editar</span>
                                        </button>

                                        <form action="{{ route('grados.destroy', $grado->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-default btn-sm btn-accion-texto"
                                                onclick="return confirm('¿Eliminar?')">
                                                <i class="fa fa-trash text-red"></i> <span>Eliminar</span>
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
    </div>
@endsection

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
    });
</script>
@endpush
