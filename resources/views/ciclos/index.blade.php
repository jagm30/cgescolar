@extends('layouts.master')
@section('page_title', 'Ciclos Escolares')
@section('page_subtitle', 'Configuración académica')

@push('styles')
    <style>
        /* Estructura Global */
        .content-wrapper {
            background-color: #f4f7f6 !important;
        }

        .con-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        /* Tabla SaaS */
        .con-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 8px;
        }

        .con-table thead th {
            background: #fcfcfc;
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            padding: 15px;
            border-bottom: 2px solid #f0f2f5;
        }

        .con-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f3f7;
            vertical-align: middle;
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
            font-size: 12px;
        }

        /* Badges de Estado */
        .badge-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .badge-activo {
            background: #e8f6f3;
            color: #1abc9c;
            border-color: #d1f2eb;
        }

        .badge-cerrado {
            background: #fdf2f2;
            color: #e74c3c;
            border-color: #fae1e1;
        }

        .badge-config {
            background: #e8f3ff;
            color: #2c6fad;
            border-color: #b3d4f5;
        }

        /* Botón Seleccionar (Activar) */
        .btn-activar-saas {
            background: #fff;
            border: 1px solid #d0dbe6;
            color: #3c8dbc;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
            cursor: pointer;
        }

        .btn-activar-saas:hover {
            background: #3c8dbc;
            color: white;
            border-color: #3c8dbc;
            transform: translateY(-1px);
        }

        /* Panel de Ayuda */
        .box-ayuda {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .ayuda-header {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f2f5;
            font-weight: 700;
            color: #2c3e50;
            font-size: 14px;
        }

        .ayuda-body {
            padding: 15px;
        }

        .ayuda-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 13px;
            color: #475569;
        }

        .con-filter-toolbar {
            display: flex;
            gap: 10px;
            background: #fff;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            border: 1px solid #e2e8f0;
            border-bottom: none;
            align-items: center;
        }

        .filter-select {
            height: 35px;
            border-radius: 6px;
            border: 1px solid #d2d6de;
            padding: 0 10px;
            color: #475569;
            font-size: 13px;
            outline: none;
            min-width: 150px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin-right: 5px;
        }
    </style>
@endpush

@section('content')


    <div class="con-stats">
        {{-- Indicador de total --}}
        <div
            style="background: #fff; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
            <i class="fa fa-calendar" style="color: #3498db;"></i>
            <span style="font-weight: 800;">{{ $ciclos->total() }}</span> {{-- Usamos total() por la paginación de Laravel --}}
        </div>

        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-nuevo" style="border-radius: 6px;">
            <i class="fa fa-plus"></i> Nuevo Ciclo
        </button>
    </div>

    <div class="row">
        <div class="col-md-9">
            {{-- TOOLBAR DE FILTRADO (SUBMIT AUTOMÁTICO) --}}
            <form method="GET" action="{{ route('ciclos.index') }}" class="con-filter-toolbar">
                <div>
                    <span class="filter-label">Mostrar:</span>
                    <select name="mostrar" class="filter-select" style="min-width: 70px;" onchange="this.form.submit()">
                        <option value="10" {{ request('mostrar') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                <div>
                    <span class="filter-label">Estado:</span>
                    <select name="estado" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                        <option value="configuracion" {{ request('estado') == 'configuracion' ? 'selected' : '' }}>
                            Configuración</option>
                    </select>
                </div>

                <div>
                    <span class="filter-label">Año:</span>
                    <select name="anio" class="filter-select" onchange="this.form.submit()">
                        <option value="">Cualquier año</option>
                        @php
                            $anios = App\Models\CicloEscolar::selectRaw('YEAR(fecha_inicio) as anio')
                                ->distinct()
                                ->pluck('anio')
                                ->sortDesc();
                        @endphp
                        @foreach ($anios as $a)
                            <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>
                                {{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="box"
                style="border: none; border-radius: 0 0 8px 8px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);">
                <div class="box-body no-padding">
                    <table class="con-table">
                        <thead>
                            <tr>
                                <th>Nombre del Ciclo</th>
                                <th>Periodo</th>
                                <th class="text-center">Estado</th>
                                <th width="200" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ciclos as $ciclo)
                                <tr>
                                    <td>
                                        <div class="con-nombre">{{ $ciclo->nombre }}</div>
                                    </td>
                                    <td>
                                        <span
                                            class="con-fecha">{{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }}</span>
                                        <i class="fa fa-arrow-right text-muted" style="font-size: 10px; margin: 0 5px;"></i>
                                        <span
                                            class="con-fecha">{{ \Carbon\Carbon::parse($ciclo->fecha_fin)->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge-status {{ $ciclo->estado == 'activo' ? 'badge-activo' : ($ciclo->estado == 'cerrado' ? 'badge-cerrado' : 'badge-config') }}">
                                            {{ ucfirst($ciclo->estado) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            {{-- Botón Seleccionar --}}
                                            <form action="{{ route('ciclos.seleccionar', $ciclo->id) }}" method="POST"
                                                style="margin:0;">
                                                @csrf
                                                <button type="submit" class="btn-activar-saas" title="Seleccionar">
                                                    <i class="fa fa-check-circle"></i>
                                                </button>
                                            </form>

                                            {{-- Botón Editar --}}
                                            <button class="btn-action-flat" data-toggle="modal" data-target="#modal-editar"
                                                data-id="{{ $ciclo->id }}" data-nombre="{{ $ciclo->nombre }}"
                                                data-inicio="{{ $ciclo->fecha_inicio }}"
                                                data-fin="{{ $ciclo->fecha_fin }}" data-estado="{{ $ciclo->estado }}">
                                                <i class="fa fa-pencil text-blue"></i>
                                            </button>

                                            {{-- Botón ELIMINAR PERMANENTE --}}
                                            <form action="{{ route('ciclos.forceDelete', $ciclo->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action-flat"
                                                    title="Eliminar definitivamente"
                                                    onclick="return confirm('¿Eliminar permanentemente de la base de datos? Esta acción no se puede deshacer.')">
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
                {{-- PAGINACIÓN MANUAL DE LARAVEL --}}
                <div class="box-footer clearfix" style="background: #fff; border-radius: 0 0 8px 8px;">
                    <div class="pull-left" style="margin: 20px 0; color: #94a3b8; font-size: 12px;">
                        Mostrando {{ $ciclos->firstItem() }} a {{ $ciclos->lastItem() }} de {{ $ciclos->total() }}
                        registros
                    </div>
                    <div class="pull-right">
                        {{ $ciclos->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- AYUDA DEL MÓDULO --}}
        <div class="col-md-3">
            <div class="box-ayuda">
                <div class="ayuda-header"><i class="fa fa-info-circle text-blue"></i> Ayuda del Módulo</div>
                <div class="ayuda-body">
                    <div class="ayuda-item">
                        <i class="fa fa-check-circle text-blue"></i>
                        <span><b>Seleccionar:</b> Establece el ciclo como el predeterminado del sistema.</span>
                    </div>
                    <div class="ayuda-item">
                        <i class="fa fa-refresh text-aqua"></i>
                        <span><b>Activo:</b> El ciclo está operando actualmente.</span>
                    </div>
                    <div class="ayuda-item">
                        <i class="fa fa-lock text-red"></i>
                        <span><b>Cerrado:</b> Ciclo finalizado, solo lectura.</span>
                    </div>
                    <div class="ayuda-item">
                        <i class="fa fa-cog text-muted"></i>
                        <span><b>Configuración:</b> Ciclo en preparación, no visible.</span>
                    </div>
                    <div style="border-top: 1px solid #f1f5f9; margin: 15px 0;"></div>
                    <div style="background: #f0f7ff; border: 1px solid #cfe2ff; padding: 10px; border-radius: 6px;">
                        <span style="color: #084298; font-size: 12px; font-weight: 700;"><i class="fa fa-lightbulb-o"></i>
                            Tip:</span>
                        <p style="font-size: 11px; color: #084298; margin-top: 5px;">Recuerda que solo puede haber un ciclo
                            <b>Activo</b> seleccionado a la vez para evitar conflictos en inscripciones.
                        </p>
                    </div>
                </div>
            </div>
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
    <script>
        $(document).ready(function() {

            // Script para cargar datos en el MODAL EDITAR
            $('#modal-editar').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var modal = $(this);

                modal.find('#edit-nombre').val(button.data('nombre'));
                modal.find('#edit-inicio').val(button.data('inicio').split(' ')[0]);
                modal.find('#edit-fin').val(button.data('fin').split(' ')[0]);
                modal.find('#edit-estado').val(button.data('estado'));

                var url = "{{ route('ciclos.update', ':id') }}".replace(':id', id);
                modal.find('#form-editar').attr('action', url);
            });

            // Resetear modal nuevo
            $('#modal-nuevo').on('show.bs.modal', function() {
                $(this).find('form')[0].reset();
            });
        });
    </script>
@endpush
