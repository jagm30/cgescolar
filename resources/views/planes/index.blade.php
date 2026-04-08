@extends('layouts.master')
@section('page_title', 'Planes de Pago')

@section('content')

    {{-- CAJA DE ERRORES DE VALIDACIÓN --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> ¡No se pudo guardar el plan!</h4>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Catálogo de Planes de Pago</h3>

            {{-- Botón que abre el x-modal --}}
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modalNuevoPlan">
                <i class="fa fa-plus"></i> Nuevo Plan
            </button>
        </div>
        <div class="box-body">

            <div
                style="background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #3c8dbc;">
                <form method="GET" action="{{ route('planes.index') }}">
                    <div class="row">

                        {{-- Mostrar N registros --}}
                        <div class="col-md-2">
                            <label style="font-size: 12px; color: #666;">Registros:</label>
                            <select name="mostrar" class="form-control input-sm">
                                <option value="10" {{ request('mostrar', '10') == '10' ? 'selected' : '' }}>10 filas
                                </option>
                                <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25 filas</option>
                                <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50 filas</option>
                                <option value="-1" {{ request('mostrar') == '-1' ? 'selected' : '' }}>Todas</option>
                            </select>
                        </div>

                        {{-- Filtro por Nivel Escolar --}}
                        <div class="col-md-4">
                            <label style="font-size: 12px; color: #666;">Nivel Escolar:</label>
                            <select name="nivel_id" class="form-control input-sm">
                                <option value="">Todos los niveles</option>
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel->id }}"
                                        {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                        {{ $nivel->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Botones (empujados a la derecha) --}}
                        <div class="col-md-6 text-right" style="margin-top: 22px;">
                            <button type="submit" class="btn btn-primary btn-sm" title="Filtrar BD">
                                <i class="fa fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('planes.index') }}" class="btn btn-default btn-sm" title="Limpiar">
                                <i class="fa fa-eraser"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <table id="tabla-planes" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre del Plan</th>
                        <th>Nivel</th>
                        <th>Periodicidad</th>
                        <th>Vigencia</th>
                        <th>Estatus</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($planes as $plan)
                        <tr>
                            <td>
                                <strong>{{ $plan->nombre }}</strong>
                                <br>
                                <small class="text-muted"><i class="fa fa-tag"></i> {{ $plan->conceptos->count() }}
                                    conceptos asignados</small>
                            </td>
                            <td>{{ $plan->nivel->nombre ?? 'N/A' }}</td>
                            <td>
                                <span class="label label-info">{{ ucfirst($plan->periodicidad) }}</span>
                            </td>
                            <td>
                                {{ $plan->fecha_inicio->format('d/m/Y') }} - {{ $plan->fecha_fin->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="label {{ $plan->activo ? 'label-success' : 'label-danger' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">

                                {{-- BOTÓN PRINCIPAL: Configurar (Lleva al SHOW donde se administran los conceptos) --}}
                                <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-primary btn-sm"
                                    title="Configurar Plan y Conceptos">
                                    <i class="fa fa-cogs"></i> Configurar
                                </a>

                                {{-- BOTÓN SECUNDARIO: Editar nombre/fechas (Lleva al EDIT) --}}
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                    data-target="#modalEditarPlan{{ $plan->id }}" title="Editar Nombre o Fechas">
                                    <i class="fa fa-pencil"></i>
                                </button>

                                {{-- BOTÓN DESACTIVAR / REACTIVAR --}}
                                @if ($plan->activo)
                                    <form action="{{ route('planes.destroy', $plan->id) }}" method="POST"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Estás seguro de que deseas desactivar el plan: {{ $plan->nombre }}?');"
                                            title="Desactivar Plan">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('planes.update', $plan->id) }}" method="POST"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="activo" value="1">
                                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#modalEditarPlan{{ $plan->id }}"
                                            title="Editar Nombre o Fechas">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No se encontraron planes para este ciclo
                                escolar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal id="modalNuevoPlan" title="Crear Nuevo Plan de Pago" size="modal-lg">
        <form action="{{ route('planes.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label><i class="fa fa-file-text-o"></i> Nombre del Plan</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Plan Anual Secundaria"
                            required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Ciclo Escolar</label>
                        <select name="ciclo_id" class="form-control" required>
                            @foreach ($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}" {{ $cicloId == $ciclo->id ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-graduation-cap"></i> Nivel Escolar</label>
                        <select name="nivel_id" class="form-control" required>
                            <option value="">Seleccione un nivel...</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-clock-o"></i> Periodicidad</label>
                        <select name="periodicidad" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="mensual">Mensual</option>
                            <option value="bimestral">Bimestral</option>
                            <option value="semestral">Semestral</option>
                            <option value="anual">Anual</option>
                            <option value="unico">Pago Único</option>
                        </select>
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
                </div>

                <div class="col-md-7">
                    <div style="background-color: #f4f4f4; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                        <h4 style="margin-top: 0; font-size: 16px;"><i class="fa fa-tags"></i> Conceptos del Plan
                            <button type="button" id="btn-agregar-concepto" class="btn btn-success btn-xs pull-right">
                                <i class="fa fa-plus"></i> Añadir
                            </button>
                        </h4>
                    </div>

                    <table class="table table-bordered table-striped" id="tabla-conceptos-modal">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th style="width: 120px;">Monto ($)</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <div id="mensaje-vacio-modal" class="text-center text-muted" style="padding: 15px;">
                        No hay conceptos. Haz clic en "Añadir" para empezar.
                    </div>
                </div>
            </div>

            <hr style="margin-top: 10px; margin-bottom: 15px;">

            <div class="clearfix" style="padding-bottom: 10px;">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary pull-right">
                    <i class="fa fa-save"></i> Guardar Plan Completo
                </button>
            </div>
        </form>
    </x-modal>
    @foreach ($planes as $plan)
        <x-modal id="modalEditarPlan{{ $plan->id }}" title="Editar Plan: {{ $plan->nombre }}" size="modal-md">
            <form action="{{ route('planes.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label><i class="fa fa-file-text-o"></i> Nombre del Plan</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $plan->nombre }}" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fa fa-calendar-check-o"></i> Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control"
                                value="{{ $plan->fecha_inicio->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fa fa-calendar-times-o"></i> Fecha de Fin</label>
                            <input type="date" name="fecha_fin" class="form-control"
                                value="{{ $plan->fecha_fin->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fa fa-toggle-on"></i> Estatus</label>
                    <select name="activo" class="form-control">
                        <option value="1" {{ $plan->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$plan->activo ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="callout callout-info" style="margin-top: 15px; margin-bottom: 0; padding: 10px;">
                    <p style="margin: 0; font-size: 13px;">
                        <i class="fa fa-info-circle"></i> Para agregar, editar o eliminar los <strong>conceptos de
                            cobro</strong> de este plan, debes usar el botón azul de "Configurar" en la tabla principal.
                    </p>
                </div>

                <hr style="margin-top: 15px; margin-bottom: 15px;">

                <div class="clearfix" style="padding-bottom: 10px;">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning pull-right">
                        <i class="fa fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </x-modal>
    @endforeach

@endsection

@push('scripts')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // 1. Inicializar DataTable
            $('#tabla-planes').DataTable({
                "lengthChange": false,
                "pageLength": {{ request('mostrar', 10) }},
                "ordering": false,
                "columnDefs": [{
                    "targets": [5], // Quitar flechas a la columna de Acciones
                    "orderable": false
                }],
                "language": {
                    "search": "Buscar:"
                }
            });

            // 2. Lógica Dinámica para el Modal de Crear Plan
            let indiceConcepto = 0;

            $('#btn-agregar-concepto').click(function() {
                $('#mensaje-vacio-modal').hide();

                let nuevaFila = `
                    <tr id="fila-concepto-${indiceConcepto}">
                        <td>
                            <select name="conceptos[${indiceConcepto}][concepto_id]" class="form-control input-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach ($conceptos as $concepto)
                                    <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="conceptos[${indiceConcepto}][monto]" class="form-control input-sm" placeholder="0.00" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-id="${indiceConcepto}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#tabla-conceptos-modal tbody').append(nuevaFila);
                indiceConcepto++;
            });

            // Eliminar fila dinámica del modal
            $('#tabla-conceptos-modal').on('click', '.btn-eliminar-fila', function() {
                let id = $(this).data('id');
                $('#fila-concepto-' + id).remove();

                if ($('#tabla-conceptos-modal tbody tr').length === 0) {
                    $('#mensaje-vacio-modal').show();
                }
            });
        });
    </script>
@endpush
