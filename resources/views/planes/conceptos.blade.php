@extends('layouts.master')
@section('page_title', 'Configurar Plan: ' . $plan->nombre)

@section('content')

    {{-- ENCABEZADO DEL PLAN (Resumen) --}}
    <div class="box box-widget widget-user-2" style="margin-bottom: 15px;">
        <div class="widget-user-header bg-blue">
            <div class="pull-right">
                <a href="{{ route('planes.index') }}" class="btn btn-default btn-sm text-black"
                    style="color: black !important;">
                    <i class="fa fa-arrow-left"></i> Volver al Catálogo
                </a>
            </div>
            <h3 class="widget-user-username" style="margin-left: 0; font-weight: bold;">{{ $plan->nombre }}</h3>
            <h5 class="widget-user-desc" style="margin-left: 0;">
                Nivel: <strong>{{ $plan->nivel->nombre ?? 'N/A' }}</strong> |
                Vigencia: <strong>{{ $plan->fecha_inicio->format('d/m/Y') }} al
                    {{ $plan->fecha_fin->format('d/m/Y') }}</strong> |
                Pago: <strong>{{ ucfirst($plan->periodicidad) }}</strong>
            </h5>
        </div>
    </div>

    {{-- PANEL DE PESTAÑAS (TABS) --}}
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            {{-- Pestaña Activa (Conceptos) --}}
            <li class="active">
                <a href="#tab_conceptos" data-toggle="tab">
                    <i class="fa fa-tags text-green"></i> <strong>1. Conceptos de Cobro</strong>
                </a>
            </li>
            {{-- Pestaña Inactiva (Políticas) - OJO: Es un enlace a la otra ruta --}}
            <li>
                <a href="{{ route('planes.politicas.index', $plan->id) }}" class="text-muted">
                    <i class="fa fa-percent"></i> 2. Políticas y Recargos
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="tab_conceptos">

                {{-- BOTÓN AGREGAR CONCEPTO --}}
                <div class="clearfix" style="margin-bottom: 15px;">
                    <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modalAddConcepto">
                        <i class="fa fa-plus"></i> Agregar Concepto(s) al Plan
                    </button>
                    <p class="text-muted" style="margin-top: 10px;">
                        Aquí puedes administrar los cobros base que se le harán a los alumnos inscritos en este plan.
                    </p>
                </div>

                {{-- TABLA DE CONCEPTOS DEL PLAN --}}
                <table id="tabla-conceptos-plan" class="table table-bordered table-striped table-hover">
                    <thead class="bg-gray">
                        <tr>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Monto Asignado ($)</th>
                            <th class="text-center" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- OJO: Iteramos sobre los registros de la tabla intermedia --}}
                        @forelse ($plan->conceptos as $detalle)
                            <tr>
                                <td>
                                    <strong>{{ $detalle->concepto->nombre ?? ($detalle->nombre ?? 'N/A') }}</strong>
                                </td>
                                <td>
                                    <span
                                        class="label label-default">{{ ucfirst($detalle->concepto->tipo ?? ($detalle->tipo ?? 'N/A')) }}</span>
                                </td>
                                <td>
                                    <b class="text-success"
                                        style="font-size: 16px;">${{ number_format($detalle->monto ?? ($detalle->pivot->monto ?? 0), 2) }}</b>
                                </td>
                                <td class="text-center">
                                    {{-- Botón Editar Monto --}}
                                    <button class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#modalEditConcepto{{ $detalle->id ?? $detalle->pivot->id }}"
                                        title="Editar Monto">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    {{-- Botón Quitar --}}
                                    <form
                                        action="{{ route('planes.conceptos.destroy', [$plan->id, $detalle->id ?? $detalle->pivot->id]) }}"
                                        method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Quitar del Plan"
                                            onclick="return confirm('¿Seguro que deseas quitar este concepto? Si ya tiene cargos generados, el sistema no lo permitirá.');">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted" style="padding: 30px;">
                                    <i class="fa fa-info-circle fa-2x"></i><br>
                                    Este plan aún no tiene conceptos de cobro asignados.<br>
                                    Haz clic en el botón verde para agregar uno.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-modal id="modalAddConcepto" title="Asignar Conceptos al Plan" size="modal-lg">
        <form action="{{ route('planes.conceptos.store', $plan->id) }}" method="POST" id="form-conceptos-plan">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div
                        style="background-color: #f4f4f4; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px solid #ddd;">
                        <h4 style="margin-top: 0; font-size: 16px;">
                            <i class="fa fa-tags"></i> Conceptos a asignar
                            <button type="button" id="btn-add-fila-concepto" class="btn btn-success btn-xs pull-right">
                                <i class="fa fa-plus"></i> Añadir Concepto(s)
                            </button>
                        </h4>
                    </div>

                    <table class="table table-bordered table-striped" id="tabla-conceptos-dinamica">
                        <thead>
                            <tr class="bg-gray">
                                <th>Concepto Disponible</th>
                                <th style="width: 150px;">Monto ($)</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="wrapper-conceptos">
                            {{-- Aquí se insertarán las filas con jQuery --}}
                        </tbody>
                    </table>
                    <div id="sin-conceptos-msg" class="text-center text-muted"
                        style="padding: 20px; border: 1px dashed #ccc;">
                        No has añadido conceptos. Haz clic en "Añadir" para empezar.
                    </div>
                </div>
            </div>

            <hr>
            <div class="text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btn-guardar-conceptos" style="display: none;">
                    <i class="fa fa-save"></i> Guardar Conceptos
                </button>
            </div>
        </form>
    </x-modal>

    @foreach ($plan->conceptos as $detalle)
        <x-modal id="modalEditConcepto{{ $detalle->id ?? $detalle->pivot->id }}"
            title="Editar Monto: {{ $detalle->concepto->nombre ?? ($detalle->nombre ?? 'N/A') }}" size="modal-sm">
            <form action="{{ route('planes.conceptos.update', [$plan->id, $detalle->id ?? $detalle->pivot->id]) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nuevo Monto ($)</label>
                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control"
                        value="{{ $detalle->monto ?? ($detalle->pivot->monto ?? 0) }}" required>
                </div>

                <hr>
                <div class="text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-refresh"></i> Actualizar</button>
                </div>
            </form>
        </x-modal>
    @endforeach

@endsection

@push('scripts')
    {{-- Forzamos la carga de las librerías porque el Master no las está mandando a esta ruta --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Usamos un try-catch para que si falla DataTable no rompa tu lógica del modal
            try {
                if ($.fn.DataTable) {
                    $('#tabla-conceptos-plan').DataTable({
                        "paging": true,
                        "lengthChange": false,
                        "searching": true,
                        "ordering": true,
                        "info": true,
                        "autoWidth": false,
                        "responsive": true,
                        "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                        },
                        "order": [
                            [0, "asc"]
                        ],
                        "columnDefs": [{
                            "orderable": false,
                            "targets": 3
                        }]
                    });
                    console.log("DataTable inicializado con éxito.");
                } else {
                    console.error("DataTables no está cargado en el navegador.");
                }
            } catch (e) {
                console.error("Error al iniciar DataTable:", e);
            }

            // --- Lógica del modal (esta parte dices que ya te jala bien) ---
            let contadorFilas = 0;
            $('#btn-add-fila-concepto').on('click', function() {
                const filaHtml = `
                <tr id="fila-con-${contadorFilas}">
                    <td>
                        <select name="conceptos[${contadorFilas}][concepto_id]" class="form-control input-sm" required>
                            <option value="">-- Seleccione --</option>
                            @foreach ($conceptosDisponibles as $cd)
                                <option value="{{ $cd->id }}">{{ $cd->nombre }} ({{ ucfirst($cd->tipo) }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" step="0.01" name="conceptos[${contadorFilas}][monto]" class="form-control input-sm" placeholder="0.00" required>
                        </div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-xs btn-remove-fila"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>`;
                $('#wrapper-conceptos').append(filaHtml);
                contadorFilas++;
                revisarEstadoTabla();
            });

            $(document).on('click', '.btn-remove-fila', function() {
                $(this).closest('tr').remove();
                revisarEstadoTabla();
            });

            function revisarEstadoTabla() {
                const numFilas = $('#wrapper-conceptos tr').length;
                if (numFilas > 0) {
                    $('#sin-conceptos-msg').hide();
                    $('#btn-guardar-conceptos').show();
                } else {
                    $('#sin-conceptos-msg').show();
                    $('#btn-guardar-conceptos').hide();
                }
            }
        });
    </script>
@endpush
