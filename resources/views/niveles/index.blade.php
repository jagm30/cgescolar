@extends('layouts.master')
@section('page_title', 'Niveles Escolares')

@push('styles')
    <style>
        /* Estilo para la fila que se está arrastrando */
        .sortable-ghost {
            opacity: 0.4;
            background-color: #d2d6de !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Efecto al pasar el mouse sobre la columna de orden */
        .handle {
            transition: background-color 0.2s;
        }

        .handle:hover {
            background-color: #f4f4f4 !important;
            color: #3c8dbc;
        }
    </style>
    @push('styles')
        <style>
            .con-toolbar {
                background: #f9f9f9;
                padding: 10px;
                border: 1px solid #eee;
                border-radius: 4px 4px 0 0;
                display: flex;
                align-items: center;
            }

            .con-select {
                height: 30px;
                border: 1px solid #d2d6de;
                border-radius: 4px;
                padding: 0 5px;
                outline: none;
            }

            .con-table {
                width: 100%;
                border-collapse: collapse;
            }

            .con-table thead th {
                background: #f4f4f4;
                padding: 12px 10px;
                border-bottom: 2px solid #eee;
                text-align: left;
                font-size: 13px;
            }

            .con-table tbody tr {
                border-bottom: 1px solid #eee;
                transition: background 0.2s;
            }

            .con-table tbody td {
                padding: 10px;
                vertical-align: middle;
            }

            .con-badge-nivel {
                background: #3c8dbc;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: bold;
            }

            .con-acciones {
                display: flex;
                gap: 5px;
                justify-content: center;
            }

            .btn-accion-texto {
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 5px 10px;
                display: flex;
                align-items: center;
                gap: 5px;
                transition: all 0.2s;
            }

            .btn-accion-texto:hover {
                background: #f4f4f4;
            }

            /* Estilo para el arrastre */
            .sortable-ghost {
                opacity: 0.3;
                background: #3c8dbc !important;
            }

            .handle {
                cursor: ns-resize;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                line-height: 1;
                padding: 5px;
                border-radius: 4px;
            }

            .handle:hover {
                background: #eee;
            }
        </style>
    @endpush
@endpush

@section('content')
@section('content')
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Gestión de Niveles</h3>
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modal-nuevo">
                <i class="fa fa-plus"></i> Nuevo Nivel
            </button>
        </div>

        {{-- Toolbar de Filtros (Sin DataTables, manejado por GET o Alpine) --}}
        <div class="con-toolbar">
            <form method="GET" action="{{ route('niveles.index') }}" style="display: flex; gap: 10px; width: 100%;">
                <select name="estado" class="con-select" onchange="this.form.submit()">
                    <option value="1" {{ request('estado', '1') == '1' ? 'selected' : '' }}>Solo Activos</option>
                    <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivos</option>
                    <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos los estados</option>
                </select>

                <div style="margin-left: auto;">
                    <a href="{{ route('niveles.index') }}" class="btn btn-default btn-sm"><i class="fa fa-eraser"></i>
                        Limpiar</a>
                </div>
            </form>
        </div>

        <div class="box-body no-padding">
            <table class="con-table">
                <thead>
                    <tr>
                        <th width="80px" class="text-center">Orden</th>
                        <th>Nombre del Nivel</th>
                        <th>REVOE</th>
                        <th width="120px" class="text-center">Estado</th>
                        <th width="250px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="sortable-tbody">
                    @foreach ($niveles as $nivel)
                        <tr data-id="{{ $nivel->id }}" class="fila-nivel">
                            <td class="handle">
                                <i class="fa fa-chevron-up text-muted" style="font-size: 8px;"></i>
                                <strong class="label-orden" style="font-size: 15px;">{{ $nivel->orden }}</strong>
                                <i class="fa fa-chevron-down text-muted" style="font-size: 8px;"></i>
                            </td>
                            <td>
                                <div style="font-weight: bold; color: #333;">{{ $nivel->nombre }}</div>
                            </td>
                            <td><span style="font-family: monospace; color: #666;">{{ $nivel->revoe ?? 'N/A' }}</span></td>
                            <td class="text-center">
                                <span class="label {{ $nivel->activo ? 'label-success' : 'label-danger' }}">
                                    {{ $nivel->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="con-acciones">
                                    <button type="button" class="btn-accion-texto" data-toggle="modal"
                                        data-target="#modal-editar" data-id="{{ $nivel->id }}"
                                        data-nombre="{{ $nivel->nombre }}" data-revoe="{{ $nivel->revoe }}"
                                        data-orden="{{ $nivel->orden }}" data-activo="{{ $nivel->activo }}">
                                        <i class="fa fa-pencil text-yellow"></i> <span>Editar</span>
                                    </button>

                                    @if ($nivel->activo)
                                        <form action="{{ route('niveles.destroy', $nivel->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-accion-texto"
                                                onclick="return confirm('¿Dar de baja?')">
                                                <i class="fa fa-arrow-down text-red"></i> <span>Baja</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL NUEVO --}}
    <x-modal id="modal-nuevo" title="Registrar Nuevo Nivel">
        <form action="{{ route('niveles.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nombre del Nivel <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Primaria" required
                    title="El nombre es obligatorio">
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
                        <input type="number" name="orden" class="form-control" placeholder="Ej: 1, 2, 3..."
                            min="1">
                    </div>
                </div>
            </div>

            <input type="hidden" name="activo" value="1">

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> Guardar Nivel
                </button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
    {{-- Importante: Asegúrate de tener SortableJS disponible --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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
                ],
                "columnDefs": [{
                        "orderable": false,
                        "targets": 0
                    } // Desactivar ordenamiento clicable en la columna de arrastre
                ]
            });
        });

        function gestionOrden() {
            return {
                initSortable() {
                    const tabla = document.querySelector('#niveles tbody');
                    Sortable.create(tabla, {
                        handle: '.handle',
                        animation: 250,
                        ghostClass: 'sortable-ghost',
                        onEnd: () => {
                            this.guardarNuevoOrden();
                        }
                    });
                },
                guardarNuevoOrden() {
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
                        .catch(err => alert("Error al guardar el orden"));
                }
            }
        }
    </script>
@endpush
