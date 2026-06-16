@extends('layouts.master')

@section('page_title', 'Gestión de Grados')
@section('page_subtitle', 'Configuración académica')

@push('styles')
    <style>
        /* Toolbar y Filtros */
        .con-filter-toolbar {
            display: flex;
            gap: 10px;
            background: #fff;
            padding: 10px 14px;
            border-radius: 8px 8px 0 0;
            border: 1px solid #e2e8f0;
            border-bottom: none;
            align-items: center;
        }

        .con-select {
            height: 35px;
            border-radius: 6px;
            border: 1px solid #d2d6de;
            padding: 0 10px;
            color: #475569;
            font-size: 13px;
            min-width: 150px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin-right: 5px;
        }

        /* Tabla SaaS */
        .con-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }

        .con-table thead th {
            background: #fcfcfc;
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            padding: 8px 12px;
            border-bottom: 2px solid #f0f2f5;
        }

        .con-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f0f3f7;
            vertical-align: middle;
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
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .con-clave {
            font-family: monospace;
            background: #f8fafc;
            padding: 2px 7px;
            border-radius: 4px;
            color: #4a5568;
            border: 1px solid #e2e8f0;
            font-size: 12px;
        }

        /* Botones de acción */
        .btn-action-flat {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action-flat:hover {
            transform: translateY(-2px);
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
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
    </style>
@endpush

@section('content')

    {{-- ══ ENCABEZADO + STATS ══ --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-graduation-cap text-blue"></i> Grados
            </h4>
            <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-list-ol"></i> {{ $grados->total() }} registros
            </span>
        </div>
        <button class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#modal-nuevo"
                style="border-radius:20px;white-space:nowrap;flex-shrink:0;">
            <i class="fa fa-plus"></i> Nuevo grado
        </button>
    </div>

    <div class="row">
        {{-- COLUMNA PRINCIPAL (TABLA) --}}
        <div class="col-md-9">
            <div class="con-filter-toolbar">
                <form method="GET" action="{{ route('grados.index') }}" id="form-filtros"
                    style="display: flex; gap: 15px; width: 100%; align-items: center;">

                    <div>
                        <span class="filter-label">Mostrar:</span>
                        <select name="mostrar" class="con-select" style="min-width: 80px;" onchange="this.form.submit()">
                            <option value="10" {{ request('mostrar') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <div>
                        <span class="filter-label">Nivel:</span>
                        <select name="nivel_id" class="con-select" onchange="this.form.submit()">
                            <option value="">Todos los niveles</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel->id }}"
                                    {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-left: auto;">
                        <a href="{{ route('grados.index') }}" class="btn btn-default btn-sm" title="Limpiar Filtros">
                            <i class="fa fa-eraser"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <div class="box"
                style="border: none; border-radius: 0 0 8px 8px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);">
                <div class="box-body no-padding">
                    <table class="con-table">
                        <thead>
                            <tr>
                                <th>Nivel</th>
                                <th>Grado</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grados as $grado)
                                <tr>
                                    <td><span class="con-badge-nivel">{{ $grado->nivel->nombre }}</span></td>
                                    <td><span class="con-clave">{{ $grado->numero }}° Grado</span></td>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <button class="btn-action-flat" data-toggle="modal" data-target="#modal-editar"
                                                data-id="{{ $grado->id }}" data-numero="{{ $grado->numero }}"
                                                data-nivel="{{ $grado->nivel_id }}" title="Editar">
                                                <i class="fa fa-pencil text-blue"></i>
                                            </button>

                                            <form action="{{ route('grados.destroy', $grado->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action-flat" title="Eliminar"
                                                    onclick="return confirm('¿Seguro que deseas eliminar este grado?')">
                                                    <i class="fa fa-trash text-red"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center" style="padding: 20px; color: #94a3b8;">No se
                                        encontraron registros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación Nativa --}}
                @if ($grados->total() > 0)
                    <div class="box-footer clearfix"
                        style="background: #fff; border-radius: 0 0 8px 8px; padding: 15px; border-top: 1px solid #f0f3f7;">
                        <div class="pull-left" style="color: #64748b; font-size: 12px; margin-top: 8px;">
                            Mostrando {{ $grados->firstItem() }} a {{ $grados->lastItem() }} de {{ $grados->total() }}
                            registros
                        </div>
                        <div class="pull-right" style="margin: 0;">
                            {{ $grados->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div> {{-- FIN COLUMNA PRINCIPAL --}}

        {{-- COLUMNA LATERAL (AYUDA) --}}
        <div class="col-md-3">
            <div class="box-ayuda">
                <div class="ayuda-header"><i class="fa fa-info-circle text-blue"></i> Ayuda del Módulo</div>
                <div class="ayuda-body">
                    <div
                        style="font-weight: 700; font-size: 12px; color: #94a3b8; margin-bottom: 10px; text-transform: uppercase;">
                        Definiciones:
                    </div>

                    <div class="ayuda-item">
                        <i class="fa fa-tag text-blue"></i>
                        <span><b>Nivel:</b> Categoría superior (ej. Primaria, Bachillerato).</span>
                    </div>
                    <div class="ayuda-item">
                        <i class="fa fa-list-ol text-muted"></i>
                        <span><b>Grado:</b> Número de año dentro del nivel.</span>
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; margin: 15px 0;"></div>

                    <div style="background: #fff8f1; border: 1px solid #ffe7d3; padding: 10px; border-radius: 6px;">
                        <span style="color: #c2410c; font-size: 12px; font-weight: 700;">
                            <i class="fa fa-warning"></i> Seguridad:
                        </span>
                        <p style="font-size: 11px; color: #9a3412; margin: 5px 0 0;">
                            No se puede eliminar un grado si tiene grupos asociados en ciclos actuales o pasados.
                        </p>
                    </div>
                </div>
            </div>
        </div> {{-- FIN COLUMNA LATERAL --}}
    </div> {{-- FIN DEL ROW --}}
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
