@extends('layouts.master')
@section('page_title', 'Configurar Conceptos de Cobro')
@section('page_subtitle', $plan->nombre)

{{-- MIGA DE PAN (BREADCRUMB) --}}
@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
    <li><a href="{{ route('planes.show', $plan->id) }}">{{ $plan->nombre }}</a></li>
    <li class="active">Conceptos de Cobro</li>
@endsection

@push('styles')
    <style>
        /* ── Cabecera del plan (Diseño Premium) ──────────── */
        .plan-header {
            background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
            border-radius: 4px;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .plan-header-nombre {
            font-size: 20px;
            font-weight: 700;
            line-height: 1.2;
        }

        .plan-header-sub {
            font-size: 12px;
            opacity: .75;
            margin-top: 4px;
        }

        .plan-badge {
            background: rgba(255, 255, 255, .2);
            color: #fff;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* ── Ajustes para los Tabs ── */
        .nav-tabs-custom {
            background: transparent;
            box-shadow: none;
        }

        .nav-tabs-custom>.nav-tabs {
            border-bottom: 2px solid #d0dde8;
        }

        .nav-tabs-custom>.nav-tabs>li.active>a {
            border-top: 3px solid #3c8dbc;
            border-radius: 6px 6px 0 0;
            font-weight: bold;
            color: #1e4d7b;
        }

        .nav-tabs-custom>.tab-content {
            background: transparent;
            padding: 20px 0 0 0;
            border: none;
        }

        /* ── Estilos de la Guía de Ayuda ── */
        .guia-ayuda {
            border-radius: 6px;
            background: #fff;
            border: 1px solid #d0dde8;
        }

        .guia-item {
            margin-bottom: 15px;
        }

        .guia-item i {
            margin-right: 5px;
        }
    </style>
@endpush

@section('content')

    {{-- ── CABECERA DEL PLAN ── --}}
    <div class="plan-header">
        <div
            style="width:48px;height:48px;border-radius:12px;flex-shrink:0; background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;">
            <i class="fa fa-file-text-o" style="font-size:22px;color:rgba(255,255,255,.9);"></i>
        </div>
        <div style="flex:1;">
            <div class="plan-header-nombre">{{ $plan->nombre }}</div>
            <div class="plan-header-sub" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;">
                <span class="plan-badge"><i class="fa fa-refresh"></i> {{ ucfirst($plan->periodicidad) }}</span>
                <span class="plan-badge"><i class="fa fa-calendar"></i> {{ $plan->fecha_inicio->format('d/m/Y') }} —
                    {{ $plan->fecha_fin->format('d/m/Y') }}</span>
                @if ($plan->activo)
                    <span class="plan-badge" style="background:rgba(39,174,96,.4);"><i class="fa fa-circle"></i>
                        Activo</span>
                @else
                    <span class="plan-badge" style="background:rgba(0,0,0,.3);"><i class="fa fa-circle-o"></i>
                        Inactivo</span>
                @endif
            </div>
        </div>
        <a href="{{ route('planes.index') }}" class="btn btn-xs btn-flat"
            style="color:rgba(255,255,255,.8);border:1px solid rgba(255,255,255,.4);background:rgba(255,255,255,.1);">
            <i class="fa fa-arrow-left"></i> Volver al Catálogo
        </a>
    </div>

    {{-- PANEL DE PESTAÑAS --}}
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#tab_conceptos" data-toggle="tab">
                    <i class="fa fa-tags text-blue"></i> <strong>1. Conceptos de Cobro</strong>
                </a>
            </li>
            <li>
                <a href="{{ route('planes.politicas.index', $plan->id) }}" class="text-muted">
                    <i class="fa fa-percent"></i> 2. Políticas y Recargos
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="tab_conceptos">
                <div class="row">

                    {{-- ════ COLUMNA IZQUIERDA: TABLA DE CONCEPTOS ════ --}}
                    <div class="col-md-8">
                        <div class="box box-primary" style="border-top: 3px solid #3c8dbc; border-radius: 6px;">
                            <div class="box-header with-border" style="padding: 16px;">
                                <h3 class="box-title" style="font-weight: 700; color: #1e4d7b;">
                                    <i class="fa fa-list"></i> Lista de Conceptos
                                    <span class="badge bg-blue"
                                        style="margin-left:6px;">{{ $plan->planPagoConceptos->count() }}</span>
                                </h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#modalAddConcepto" style="border-radius: 4px;">
                                        <i class="fa fa-plus"></i> Añadir Concepto(s)
                                    </button>
                                </div>
                            </div>

                            <div class="box-body">
                                <table id="tabla-conceptos-plan" class="table table-bordered table-striped table-hover"
                                    style="font-size: 13px;">
                                    <thead style="background-color: #f4f6f9; color: #333;">
                                        <tr>
                                            <th>Concepto</th>
                                            <th>Tipo</th>
                                            <th>Monto Asignado ($)</th>
                                            <th class="text-center" style="width: 120px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($plan->planPagoConceptos as $detalle)
                                            <tr>
                                                <td style="vertical-align: middle;">
                                                    <strong>{{ $detalle->concepto->nombre ?? 'N/A' }}</strong>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <span class="label label-default" style="font-size: 11px;">
                                                        {{ ucfirst($detalle->concepto->tipo ?? 'N/A') }}
                                                    </span>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <b class="text-success" style="font-size: 15px;">
                                                        ${{ number_format($detalle->monto, 2) }}
                                                    </b>
                                                </td>
                                                <td class="text-center" style="vertical-align: middle;">
                                                    <button class="btn btn-default btn-xs btn-flat" data-toggle="modal"
                                                        data-target="#modalEditConcepto{{ $detalle->id }}"
                                                        title="Editar Monto">
                                                        <i class="fa fa-pencil text-yellow"></i>
                                                    </button>
                                                    <form
                                                        action="{{ route('planes.conceptos.destroy', [$plan->id, $detalle->id]) }}"
                                                        method="POST" style="display: inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-default btn-xs btn-flat"
                                                            onclick="return confirm('¿Seguro que deseas quitar este concepto?');">
                                                            <i class="fa fa-trash text-red"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted"
                                                    style="padding: 40px 20px;">
                                                    <i class="fa fa-tags fa-3x"
                                                        style="color: #e0e0e0; display: block; margin-bottom: 10px;"></i>
                                                    No hay conceptos asignados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ════ COLUMNA DERECHA: GUÍA DE USO RECUPERADA ════ --}}
                    <div class="col-md-4">
                        <div class="guia-ayuda">
                            <div style="padding: 15px; border-bottom: 1px solid #d0dde8; background: #fcfcfc;">
                                <h4 style="margin:0; font-size:14px; font-weight: bold; color: #1e4d7b;">
                                    <i class="fa fa-question-circle text-yellow"></i> ¿Cómo funcionan los conceptos?
                                </h4>
                            </div>
                            <div style="padding: 15px;">
                                <div class="guia-item">
                                    <strong style="color:#3c8dbc;"><i class="fa fa-check"></i> Conceptos Base</strong><br>
                                    <span style="font-size:12px; color:#666;">Estos son los cobros principales (como
                                        colegiaturas, inscripciones o materiales) que se generarán automáticamente para los
                                        alumnos de este plan.</span>
                                </div>
                                <div
                                    style="background:#f0f7ff; border-left:3px solid #3c8dbc; padding:10px; border-radius:0 4px 4px 0; margin-bottom:15px;">
                                    <strong style="font-size:12px;">Monto Personalizado:</strong><br>
                                    <span style="font-size:11px;">Puedes definir un monto especifico para este plan,
                                        ignorando el precio por defecto que tiene el concepto en el catálogo general.</span>
                                </div>
                                <div class="guia-item">
                                    <strong style="color:#e74c3c;"><i class="fa fa-warning"></i> ¡Importante!</strong><br>
                                    <span style="font-size:11px; color:#666;">Si un concepto ya generó cargos o deudas en
                                        la cuenta de algún alumno inscrito, el
                                        sistema bloqueará su eliminación para proteger el historial financiero.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PARA AÑADIR CONCEPTOS --}}
    <x-modal id="modalAddConcepto" title="Asignar Conceptos al Plan" size="modal-lg">
        <form action="{{ route('planes.conceptos.store', $plan->id) }}" method="POST">
            @csrf
            <div
                style="background-color: #f4f6f9; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #d0dde8;">
                <h4 style="margin: 0; font-size: 15px; font-weight: bold; color: #1e4d7b;">
                    <i class="fa fa-tags"></i> Conceptos a asignar
                    <button type="button" id="btn-add-fila-concepto" class="btn btn-primary btn-xs pull-right"><i
                            class="fa fa-plus"></i> Añadir fila</button>
                </h4>
            </div>
            <table class="table table-bordered" id="tabla-conceptos-dinamica">
                <thead>
                    <tr style="background-color: #e8f0fb;">
                        <th>Concepto</th>
                        <th style="width: 180px;">Monto ($)</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="wrapper-conceptos"></tbody>
            </table>
            <div id="sin-conceptos-msg" class="text-center text-muted"
                style="padding: 20px; border: 2px dashed #d0dde8;">Añade conceptos para guardar.</div>
            <hr>
            <div class="text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btn-guardar-conceptos" style="display: none;"><i
                        class="fa fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </x-modal>

    {{-- MODALES PARA EDITAR (LÓGICA CORREGIDA) --}}
    @foreach ($plan->planPagoConceptos as $detalle)
        <x-modal id="modalEditConcepto{{ $detalle->id }}" title="Editar Monto" size="modal-sm">
            <form action="{{ route('planes.conceptos.update', [$plan->id, $detalle->id]) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Nuevo Monto ($)</label>
                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control"
                        value="{{ $detalle->monto }}" required>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-refresh"></i> Actualizar</button>
                </div>
            </form>
        </x-modal>
    @endforeach

@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización segura de DataTable
            try {
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
            } catch (e) {
                console.error("Error DataTable:", e);
            }

            // Lógica de filas dinámicas
            let contadorFilas = 0;
            $('#btn-add-fila-concepto').on('click', function() {
                const filaHtml = `<tr id="fila-con-${contadorFilas}">
                    <td><select name="conceptos[${contadorFilas}][concepto_id]" class="form-control" required><option value="">-- Seleccione --</option>@foreach ($conceptosDisponibles as $cd)<option value="{{ $cd->id }}">{{ $cd->nombre }} ({{ ucfirst($cd->tipo) }})</option>@endforeach</select></td>
                    <td><div class="input-group"><span class="input-group-addon">$</span><input type="number" step="0.01" name="conceptos[${contadorFilas}][monto]" class="form-control" required></div></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove-fila"><i class="fa fa-times"></i></button></td>
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
                if ($('#wrapper-conceptos tr').length > 0) {
                    $('#sin-conceptos-msg').hide();
                    $('#btn-guardar-conceptos').fadeIn();
                } else {
                    $('#sin-conceptos-msg').fadeIn();
                    $('#btn-guardar-conceptos').hide();
                }
            }
        });
    </script>
@endpush
