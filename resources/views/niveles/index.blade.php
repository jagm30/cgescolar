@extends('layouts.master')

@section('page_title', 'Gestión de Niveles')
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

        .handle {
            cursor: ns-resize;
            text-align: center;
            color: #aab;
            background: #fafafa;
            border-right: 1px solid #f0f3f7;
        }

        .con-nombre {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
        }

        .con-clave {
            font-family: monospace;
            font-size: 12px;
            background: #f0f3f7;
            padding: 2px 7px;
            border-radius: 4px;
            color: #4a5568;
        }

        .con-badge-activo {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')


    <div class="con-stats">
        <div class="con-stat-card">
            <div class="con-stat-icon"><i class="fa fa-graduation-cap"></i></div>
            <div>
                <div class="con-stat-num">{{ $niveles->count() }}</div>
                <div class="con-stat-lbl">Niveles Totales</div>
            </div>
        </div>

        <button class="btn-registrar-simple" data-toggle="modal" data-target="#modal-nuevo">
            <i class="fa fa-plus"></i>
            <span>Registrar Nuevo Nivel</span>
        </button>
    </div>

    {{-- TABLA --}}
    <div class="box box-default" style="border-top: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div class="box-body no-padding">
            <table class="con-table">
                <thead>
                    <tr>
                        <th width="60px" class="text-center">Orden</th>
                        <th>Información del Nivel</th>
                        <th>Clave / REVOE</th>
                        <th width="120px" class="text-center">Estado</th>
                        <th width="120px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="sortable-tbody">
                    @foreach ($niveles as $nivel)
                        <tr data-id="{{ $nivel->id }}" class="fila-nivel">
                            <td class="handle">
                                <i class="fa fa-ellipsis-v"></i><br>
                                <strong class="label-orden">{{ $nivel->orden }}</strong>
                            </td>
                            <td>
                                <div class="con-nombre">{{ $nivel->nombre }}</div>
                            </td>
                            <td>
                                <span class="con-clave">{{ $nivel->revoe ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="con-badge-activo">Activo</span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <button class="btn btn-default btn-xs" data-toggle="modal" data-target="#modal-editar"
                                        data-id="{{ $nivel->id }}" data-nombre="{{ $nivel->nombre }}"
                                        data-revoe="{{ $nivel->revoe }}" data-orden="{{ $nivel->orden }}"
                                        data-activo="{{ $nivel->activo }}">
                                        <i class="fa fa-pencil text-blue"></i>
                                    </button>
                                    <form action="{{ route('niveles.destroy', $nivel->id) }}" method="POST"
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
