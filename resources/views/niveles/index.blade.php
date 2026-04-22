@extends('layouts.master')

@section('page_title', 'Gestión de Niveles')
@section('page_subtitle', 'Configuración académica')

@push('styles')
    <style>
        /* Estructura de la página */
        .content-wrapper {
            background-color: #f4f7f6 !important;
        }

        .con-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        /* Estilo SaaS para la caja de ayuda */
        .box-ayuda {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .ayuda-header {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f2f5;
            color: #2c3e50;
            font-weight: 700;
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
            color: #64748b;
        }

        .ayuda-item i {
            width: 20px;
            text-align: center;
        }

        .ayuda-sep {
            border-top: 1px solid #f0f2f5;
            margin: 15px 0;
        }

        /* Tabla y Arrastre */
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
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
        }

        /* ICONO DE ARRASTRE REFINADO */
        .handle {
            cursor: grab;
            color: #cbd5e0;
            text-align: center;
            transition: color 0.2s;
        }

        .handle:hover {
            color: #3498db;
        }

        .handle i {
            font-size: 16px;
        }

        /* Botones y Badges */
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .bg-activo {
            background: #e8f6f3;
            color: #1abc9c;
            border: 1px solid #d1f2eb;
        }

        .bg-inactivo {
            background: #fdf2f2;
            color: #e74c3c;
            border: 1px solid #fae1e1;
        }

        .btn-action-flat {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            color: #7f8c8d;
            transition: all 0.2s;
        }

        .btn-action-flat:hover {
            transform: translateY(-2px);
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush

@section('content')

    <div class="con-stats">
        <div style="display: flex; gap: 15px;">
            <div
                style="background: #fff; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-graduation-cap" style="color: #3498db; font-size: 20px;"></i>
                <div>
                    <span
                        style="display: block; font-size: 18px; font-weight: 800; line-height: 1;">{{ $niveles->count() }}</span>
                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">Niveles</span>
                </div>
            </div>
        </div>
        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-nuevo"
            style="border-radius: 6px; font-weight: 600;">
            <i class="fa fa-plus"></i> Nuevo Nivel
        </button>
    </div>

    <div class="row">
        {{-- COLUMNA DE LA TABLA --}}
        <div class="col-md-9">
            <div class="box" style="border: none; border-radius: 8px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);">
                <div class="box-body no-padding">
                    <table class="con-table">
                        <thead>
                            <tr>
                                <th width="50"></th>
                                <th>Información del Nivel</th>
                                <th>REVOE</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-tbody">
                            @foreach ($niveles as $nivel)
                                <tr data-id="{{ $nivel->id }}" class="fila-nivel">
                                    <td class="handle" title="Arrastrar para ordenar"
                                        style="cursor: grab; color: #cbd5e0; text-align: center;">
                                        <i class="fa fa-sort" style="font-size: 16px;"></i>
                                        <strong class="label-orden" style="display: none;">{{ $nivel->orden }}</strong>
                                    </td>
                                    <td><b style="color: #2c3e50;">{{ $nivel->nombre }}</b></td>
                                    <td>
                                        <code
                                            style="color: #7f8c8d; background: #f8f9fa; padding: 3px 6px; border-radius: 4px;">
                                            {{ $nivel->revoe ?? 'N/A' }}
                                        </code>
                                    </td>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <button class="btn-action-flat" data-toggle="modal" data-target="#modal-editar"
                                                data-id="{{ $nivel->id }}" data-nombre="{{ $nivel->nombre }}"
                                                data-revoe="{{ $nivel->revoe }}" data-orden="{{ $nivel->orden }}"
                                                data-activo="{{ $nivel->activo }}" title="Editar">
                                                <i class="fa fa-pencil text-blue"></i>
                                            </button>

                                            <form action="{{ route('niveles.forceDelete', $nivel->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action-flat" title="Eliminar"
                                                    onclick="return confirm('¿Eliminar permanente?')">
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
        </div>

        {{-- PANEL DE AYUDA (Estilo imagen) --}}
        <div class="col-md-3">
            <div class="box-ayuda"
                style="background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <div class="ayuda-header"
                    style="padding: 12px 15px; border-bottom: 1px solid #f0f2f5; font-weight: 700; color: #2c3e50;">
                    <i class="fa fa-info-circle text-blue"></i> Ayuda del Módulo
                </div>
                <div class="ayuda-body" style="padding: 15px;">
                    <div
                        style="font-weight: 700; font-size: 12px; color: #94a3b8; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Acciones Disponibles:</div>

                    <div class="ayuda-item"
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 13px; color: #475569;">
                        <i class="fa fa-sort text-muted" style="width: 20px; text-align: center;"></i>
                        <span><b>Ordenar:</b> Arrastra filas para cambiar prioridad.</span>
                    </div>
                    <div class="ayuda-item"
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 13px; color: #475569;">
                        <i class="fa fa-pencil text-blue" style="width: 20px; text-align: center;"></i>
                        <span><b>Editar:</b> Modificar nombre o REVOE.</span>
                    </div>
                    <div class="ayuda-item"
                        style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 13px; color: #475569;">
                        <i class="fa fa-trash text-red" style="width: 20px; text-align: center;"></i>
                        <span><b>Eliminar:</b> Borrado físico permanente.</span>
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; margin: 15px 0;"></div>

                    <div style="background: #fff8f1; border: 1px solid #ffe7d3; padding: 12px; border-radius: 6px;">
                        <span
                            style="color: #c2410c; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                            <i class="fa fa-warning"></i> Seguridad:
                        </span>
                        <p style="font-size: 11px; color: #9a3412; margin: 5px 0 0; line-height: 1.4;">
                            Solo se pueden <b>Eliminar</b> niveles sin grados asociados para proteger el historial escolar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <x-modal id="modal-editar" title="Editar Nivel Escolar">
        <form id="form-editar" method="POST">
            @csrf @method('PUT')
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
                <label>REVOE <small class="text-muted">(Opcional)</small></label>
                <input type="text" name="revoe" id="edit-revoe" class="form-control">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="activo" id="edit-activo" class="form-control" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Actualizar Datos</button>
            </div>
        </form>
    </x-modal>
    {{-- MODAL NUEVO --}}
    <x-modal id="modal-nuevo" title="Registrar Nuevo Nivel">
        <form action="{{ route('niveles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nombre del Nivel <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Primaria" required>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>REVOE</label>
                        <input type="text" name="revoe" class="form-control" placeholder="Opcional">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" name="orden" class="form-control"
                            value="{{ $niveles->max('orden') + 1 }}" min="1">
                    </div>
                </div>
            </div>
            <input type="hidden" name="activo" value="1">
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar Nivel</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica de Arrastre (Sortable)
            const el = document.getElementById('sortable-tbody');
            if (el) {
                Sortable.create(el, {
                    handle: '.handle',
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    onEnd: function() {
                        let niveles = [];
                        document.querySelectorAll('.fila-nivel').forEach((fila, index) => {
                            const nuevoOrden = index + 1;
                            fila.querySelector('.label-orden').innerText = nuevoOrden;
                            niveles.push({
                                id: fila.dataset.id,
                                orden: nuevoOrden
                            });
                        });

                        fetch("{{ route('niveles.reordenar') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    niveles: niveles
                                })
                            })
                            .then(res => res.json())
                            .then(data => console.log("Orden sincronizado"))
                            .catch(err => console.error("Error al reordenar"));
                    }
                });
            }


            $('#modal-editar').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var modal = $(this);

                modal.find('#edit-nombre').val(button.data('nombre'));
                modal.find('#edit-orden').val(button.data('orden'));
                modal.find('#edit-activo').val(button.data('activo'));

                let revoe = button.data('revoe');
                modal.find('#edit-revoe').val(revoe && revoe !== 'N/A' ? revoe : '');

                var url = "{{ route('niveles.update', ':id') }}".replace(':id', button.data('id'));
                modal.find('#form-editar').attr('action', url);
            });
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
