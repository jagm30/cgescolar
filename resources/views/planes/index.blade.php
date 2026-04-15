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
            <div class="pull-right" style="display: flex; gap: 10px;">
                <button type="button" class="btn btn-default" id="btn-clonar-masivo" disabled data-toggle="modal"
                    data-target="#modalClonacionMasiva">
                    <i class="fa fa-copy"></i> Clonar Seleccionados
                </button>

                <button class="btn btn-success" data-toggle="modal" data-target="#modalNuevoPlan">
                    <i class="fa fa-plus"></i> Nuevo Plan
                </button>
            </div>
        </div>
        <div class="box-body">

            {{-- FILTROS --}}
            <div
                style="background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #3c8dbc;">
                <form method="GET" action="{{ route('planes.index') }}">
                    <div class="row">
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
                        <th width="30px" class="text-center"><input type="checkbox" id="select-all-planes"></th>
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
                            <td class="text-center">
                                <input type="checkbox" class="plan-checkbox" value="{{ $plan->id }}">
                            </td>
                            <td>
                                <strong>{{ $plan->nombre }}</strong>
                                <br>
                                <small class="text-muted"><i class="fa fa-tag"></i> {{ $plan->conceptos->count() }}
                                    conceptos asignados</small>
                            </td>
                            <td>{{ $plan->nivel->nombre ?? 'N/A' }}</td>
                            <td><span class="label label-info">{{ ucfirst($plan->periodicidad) }}</span></td>
                            <td>{{ $plan->fecha_inicio->format('d/m/Y') }} - {{ $plan->fecha_fin->format('d/m/Y') }}</td>
                            <td>
                                <span class="label {{ $plan->activo ? 'label-success' : 'label-danger' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 2px; justify-content: center; align-items: center;">
                                    <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-info btn-sm"
                                        title="Ver Resumen">
                                        <i class="fa fa-eye"></i> Ver
                                    </a>

                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#modalEditarPlan{{ $plan->id }}" title="Editar Nombre o Fechas">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    @if ($plan->activo)
                                        <form action="{{ route('planes.destroy', $plan->id) }}" method="POST"
                                            style="margin: 0;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Estás seguro?');">
                                                <i class="fa fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('planes.update', $plan->id) }}" method="POST"
                                            style="margin: 0;">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="activo" value="1">
                                            <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('¿Reactivar?');">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('planes.conceptos.index', $plan->id) }}"
                                        class="btn btn-primary btn-sm" title="Configurar Plan y Conceptos">
                                        <i class="fa fa-cogs"></i> Configurar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No se encontraron planes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL NUEVO PLAN --}}
    <x-modal id="modalNuevoPlan" title="Crear nuevo Plan de Pago para el ciclo <b>{{ $cicloActual->nombre }}</b>"
        size="modal-lg">
        <form action="{{ route('planes.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label><i class="fa fa-file-text-o"></i> Nombre de Plan</label>
                        <input type="text" name="nombre" class="form-control"
                            placeholder="Ej: Plan Anual Secundaria" required>
                    </div>
                    <input type="hidden" name="ciclo_id" value="{{ $cicloActual->id }}">

                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Ciclo Escolar</label>
                        <input type="text" class="form-control" value="{{ $cicloActual->nombre }}" readonly disabled>
                        <small class="text-muted">El plan se creará automáticamente en el ciclo actual.</small>
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
                            <div class="form-group"><label>Fecha Inicio</label><input type="date" name="fecha_inicio"
                                    class="form-control" required></div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group"><label>Fecha Fin</label><input type="date" name="fecha_fin"
                                    class="form-control" required></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div style="background-color: #f4f4f4; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                        <h4 style="margin-top: 0; font-size: 16px;"><i class="fa fa-tags"></i> Conceptos del Plan
                            <button type="button" id="btn-agregar-concepto" class="btn btn-success btn-xs pull-right"><i
                                    class="fa fa-plus"></i> Añadir</button>
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
                        <tbody></tbody>
                    </table>
                    <div id="mensaje-vacio-modal" class="text-center text-muted" style="padding: 10px;">No hay conceptos.
                    </div>

                    <div
                        style="background-color: #fcf8e3; padding: 10px; border-radius: 5px; margin-top: 15px; border: 1px solid #faebcc;">
                        <h4 style="margin-top: 0; font-size: 15px; color: #8a6d3b;"><i class="fa fa-percent"></i>
                            Políticas de Descuento (Pronto Pago)</h4>
                        <div id="contenedor-descuentos"></div>
                        <button type="button" id="btn-add-descuento" class="btn btn-warning btn-xs"><i
                                class="fa fa-plus"></i> Agregar Descuento</button>
                    </div>

                    <div
                        style="background-color: #f2dede; padding: 10px; border-radius: 5px; margin-top: 10px; border: 1px solid #ebccd1;">
                        <h4 style="margin-top: 0; font-size: 15px; color: #a94442;"><i class="fa fa-calendar-times-o"></i>
                            Política de Recargo (Mora)</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <label style="font-size: 11px;">Día Límite Pago</label>
                                <input type="number" name="recargo[dia_limite_pago]" class="form-control input-sm"
                                    placeholder="Ej: 10">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px;">Tipo Recargo</label>
                                <select name="recargo[tipo_recargo]" class="form-control input-sm">
                                    <option value="porcentaje">Porcentaje %</option>
                                    <option value="fijo">Monto Fijo $</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px;">Valor</label>
                                <input type="number" step="0.01" name="recargo[valor]" class="form-control input-sm"
                                    placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr style="margin-top: 10px; margin-bottom: 15px;">
            <div class="clearfix" style="padding-bottom: 10px;">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i
                        class="fa fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Guardar Plan
                    Completo</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDITAR PLAN --}}
    @foreach ($planes as $plan)
        <x-modal id="modalEditarPlan{{ $plan->id }}" title="Editar Plan: {{ $plan->nombre }}" size="modal-md">
            <form action="{{ route('planes.update', $plan->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Nombre del Plan</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $plan->nombre }}" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Fecha Inicio</label><input type="date" name="fecha_inicio"
                                class="form-control" value="{{ $plan->fecha_inicio->format('Y-m-d') }}" required></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>Fecha Fin</label><input type="date" name="fecha_fin"
                                class="form-control" value="{{ $plan->fecha_fin->format('Y-m-d') }}" required></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Estatus</label>
                    <select name="activo" class="form-control">
                        <option value="1" {{ $plan->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$plan->activo ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="callout callout-info">
                    <p style="font-size: 12px;"><i class="fa fa-info-circle"></i> Edita conceptos, descuentos y recargos
                        desde el botón "Configurar".</p>
                </div>
                <div class="modal-footer no-padding"><button type="submit" class="btn btn-warning pull-right">Guardar
                        Cambios</button></div>
            </form>
        </x-modal>
    @endforeach

    {{-- MODAL CLONACIÓN MASIVA --}}
    <x-modal id="modalClonacionMasiva" title="Clonar Planes Seleccionados" size="modal-md">
        <form action="{{ route('planes.clonar.masivo') }}" method="POST" id="form-clonar-masivo">
            @csrf
            <div id="contenedor-ids-clonar"></div>

            <div class="alert alert-info">
                <h4><i class="icon fa fa-info"></i> Instrucciones</h4>
                Se crearán copias exactas de los planes seleccionados (incluyendo sus conceptos y políticas) en el ciclo
                escolar de destino escogido.
            </div>

            <div class="form-group">
                <label>Ciclo Escolar Destino</label>
                <select name="ciclo_destino_id" class="form-control" required>
                    <option value="">Seleccione el ciclo destino...</option>
                    {{-- CAMBIAMOS $ciclos por $ciclosDisponibles --}}
                    @foreach ($ciclosDisponibles as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Sufijo para los nombres (Opcional)</label>
                <input type="text" name="sufijo" class="form-control" placeholder="Ej: - COPIA ">
            </div>

            <div class="modal-footer no-padding">
                <button type="submit" class="btn btn-primary pull-right">Comenzar Clonación</button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // 1. DataTable
            $('#tabla-planes').DataTable({
                "lengthChange": false,
                "pageLength": {{ request('mostrar', 10) }},
                "ordering": false
            });

            // 2. Lógica Conceptos
            let indiceConcepto = 0;
            $('#btn-agregar-concepto').click(function() {
                $('#mensaje-vacio-modal').hide();
                let fila = `<tr id="fila-concepto-${indiceConcepto}">
                    <td><select name="conceptos[${indiceConcepto}][concepto_id]" class="form-control input-sm" required><option value="">Seleccione...</option>@foreach ($conceptos as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach</select></td>
                    <td><input type="number" step="0.01" name="conceptos[${indiceConcepto}][monto]" class="form-control input-sm" required></td>
                    <td><button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-id="${indiceConcepto}"><i class="fa fa-trash"></i></button></td>
                </tr>`;
                $('#tabla-conceptos-modal tbody').append(fila);
                indiceConcepto++;
            });

            $('#tabla-conceptos-modal').on('click', '.btn-eliminar-fila', function() {
                $('#fila-concepto-' + $(this).data('id')).remove();
                if ($('#tabla-conceptos-modal tbody tr').length === 0) $('#mensaje-vacio-modal').show();
            });

            // 3. Lógica Descuentos
            let indiceDesc = 0;
            $('#btn-add-descuento').click(function() {
                let html = `<div class="row" id="fila-desc-${indiceDesc}" style="margin-bottom: 5px;">
                    <div class="col-md-4"><input type="text" name="descuentos[${indiceDesc}][nombre]" class="form-control input-sm" placeholder="Nombre" required></div>
                    <div class="col-md-3"><select name="descuentos[${indiceDesc}][tipo_valor]" class="form-control input-sm"><option value="porcentaje">%</option><option value="fijo">$</option></select></div>
                    <div class="col-md-2"><input type="number" name="descuentos[${indiceDesc}][valor]" class="form-control input-sm" required></div>
                    <div class="col-md-2"><input type="number" name="descuentos[${indiceDesc}][dia_limite]" class="form-control input-sm" placeholder="Día"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-danger btn-xs btn-remove-desc" data-id="${indiceDesc}"><i class="fa fa-times"></i></button></div>
                </div>`;
                $('#contenedor-descuentos').append(html);
                indiceDesc++;
            });

            $(document).on('click', '.btn-remove-desc', function() {
                $('#fila-desc-' + $(this).data('id')).remove();
            });

            // 4. Auto-cerrar alertas
            setTimeout(function() {
                $('.alert-dismissible').slideUp('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // 5. Lógica Selección Masiva y Clonación
            $('#select-all-planes').click(function() {
                $('.plan-checkbox').prop('checked', this.checked);
                actualizarEstadoBotonClonar();
            });

            $(document).on('change', '.plan-checkbox', function() {
                actualizarEstadoBotonClonar();
            });

            function actualizarEstadoBotonClonar() {
                let seleccionados = $('.plan-checkbox:checked').length;
                let boton = $('#btn-clonar-masivo');
                if (seleccionados > 0) {
                    boton.prop('disabled', false).addClass('btn-primary').removeClass('btn-default');
                    boton.html(`<i class="fa fa-copy"></i> Clonar ${seleccionados} planes`);
                } else {
                    boton.prop('disabled', true).addClass('btn-default').removeClass('btn-primary');
                    boton.html('<i class="fa fa-copy"></i> Clonar Seleccionados');
                }
            }

            $('#form-clonar-masivo').submit(function() {
                let contenedor = $('#contenedor-ids-clonar');
                contenedor.empty();
                $('.plan-checkbox:checked').each(function() {
                    contenedor.append(
                        `<input type="hidden" name="plan_ids[]" value="${$(this).val()}">`);
                });
            });
        });
    </script>
@endpush
