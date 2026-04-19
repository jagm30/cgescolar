@extends('layouts.master')

@section('page_title', 'Asignación de planes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">

    <style>
       /* FONDO GENERAL */
.content-wrapper {
    background: #f4f6f9;
}

/* CARD GENERAL */
.box {
    border-radius: 14px;
    border: none;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    overflow: hidden;
}

/* HEADER CON COLOR */
.box-primary .box-header {
    background: linear-gradient(135deg, #3c8dbc, #6fb1e7);
    color: white;
}

.box-warning .box-header {
    background: linear-gradient(135deg, #f39c12, #f7c56b);
    color: white;
}

.box-header .box-title {
    font-weight: 600;
}

/* BOTÓN VOLVER */
.btn-default {
    border-radius: 20px;
    border: none;
    background: rgba(255,255,255,0.2);
    color: white;
}

/* INPUTS */
.form-control {
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    padding: 10px;
    transition: 0.2s;
}

.form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 3px rgba(60,141,188,0.15);
}

/* SELECT2 */
.select2-container--default .select2-selection--single {
    border-radius: 10px;
    height: 42px;
    padding: 6px;
}

/* BOTÓN GUARDAR 🔥 */
.btn-primary {
    background: linear-gradient(135deg, #00c9a7, #00a884);
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    padding: 12px;
    transition: 0.3s;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* CONCEPTOS MÁS BONITOS */
#conceptos-list {
    background: #ffffff;
    border-radius: 12px;
    padding: 10px;
}

/* ITEM */
.list-group-item {
    border: none !important;
    border-radius: 10px;
    margin-bottom: 8px;
    padding: 10px;
    background: #f9fafb;
    transition: 0.2s;
}

.list-group-item:hover {
    background: #eef5fb;
}

/* PRECIO */
.label-default {
    background: #00a884 !important;
    border-radius: 20px;
    padding: 6px 12px;
    font-weight: bold;
}

/* CHECK MÁS BONITO */
.concepto-checkbox {
    transform: scale(1.2);
    cursor: pointer;
}
</style>
@endpush

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
    <li class="active">Asignar plan</li>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4><i class="icon fa fa-ban"></i> Revisa el formulario.</h4>
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('planes.asignar') }}" method="POST" id="form-asignar-plan">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Asignar plan</h3>
                        <div class="box-tools">
                            <a href="{{ route('planes.asignar.index') }}" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="box-body">

                        <div class="form-group">
                            <label>Plan de pago</label>
                            <select name="plan_id" id="plan_id" class="form-control select2"
                                data-placeholder="Selecciona un plan" required>
                                <option value="">Selecciona un plan</option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-fecha-inicio="{{ $plan->fecha_inicio?->format('Y-m-d') }}"
                                        data-fecha-fin="{{ $plan->fecha_fin?->format('Y-m-d') }}">
                                        {{ $plan->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alcance</label>
                            <select name="origen" id="origen" class="form-control">
                                <option value="individual">Alumno</option>
                                <option value="grupo">Grupo</option>
                                <option value="nivel">Nivel</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label id="labelDinamico">Alumno</label>
                            <select id="selectDinamico" class="form-control select2"></select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Fecha inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Fecha fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}" readonly>
                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i> Guardar asignación
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Conceptos del plan</h3>
                    </div>
                    <div class="box-body">
                        <div id="conceptos-list" class="well well-sm" style="max-height:470px; overflow-y:auto;">
                            <p class="text-muted text-center">Selecciona un plan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
        <script>
            const alumnos = @json($alumnos);
            const grupos = @json($grupos);
            const niveles = @json($niveles);
            const planes = @json($planesData);
            const oldOrigen = @json(old('origen', 'individual'));
            const oldAlumnoId = @json(old('alumno_id'));
            const oldGrupoId = @json(old('grupo_id'));
            const oldNivelId = @json(old('nivel_id'));

            function formatearNombreAlumno(item) {
                return [item.nombre, item.ap_paterno, item.ap_materno].filter(Boolean).join(' ');
            }

            function mostrarConceptosDelPlan(planId) {
                const conceptosList = document.getElementById('conceptos-list');

                if (!planId) {
                    conceptosList.innerHTML = '<p class="text-muted text-center">Selecciona un plan para ver los conceptos</p>';
                    return;
                }

                const plan = planes.find(p => p.id == planId);

                if (!plan || plan.conceptos.length === 0) {
                    conceptosList.innerHTML = '<p class="text-muted text-center">Este plan no tiene conceptos asociados</p>';
                    return;
                }

                let html = '<div class="list-group">';
                plan.conceptos.forEach(concepto => {
                    html += `
                        <div class="list-group-item" style="padding: 8px 0; border: none; border-bottom: 1px solid #ecf0f1;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" id="concepto_${concepto.id}"
                                       class="concepto-checkbox"
                                       data-plan-concepto-id="${concepto.id}"
                                       data-concepto-nombre="${concepto.nombre}"
                                       checked>
                                <label for="concepto_${concepto.id}" style="margin: 0; cursor: pointer; flex: 1;">
                                    <strong>${concepto.nombre}</strong>
                                </label>
                                <span class="label label-default">$${parseFloat(concepto.monto).toFixed(2)}</span>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';

                conceptosList.innerHTML = html;
            }

            function cargarOpciones(data, tipo) {
                let options = '<option value="">Selecciona una opción</option>';

                data.forEach(item => {
                    let nombre = '';

                    if (tipo === 'alumno') {
                        nombre = formatearNombreAlumno(item);
                    } else if (tipo === 'grupo') {
                        let nivel = item.grado?.nivel?.nombre ?? '';
                        let grado = item.grado?.nombre ?? '';
                        let grupo = item.nombre ?? '';

                        nombre = grado ? `${nivel} ${grado} ${grupo}` : `${nivel} ${grupo}`;
                    } else if (tipo === 'nivel') {
                        nombre = item.nombre ?? '';
                    }

                    options += `<option value="${item.id}">${nombre.trim()}</option>`;
                });

                $('#selectDinamico').html(options).val('').trigger('change');
            }

            function actualizarSelect() {
                const tipo = $('#origen').val();
                const $select = $('#selectDinamico');

                if (tipo === 'individual') {
                    $('#labelDinamico').text('Alumno');
                    $select.attr('name', 'alumno_id');
                    cargarOpciones(alumnos, 'alumno');
                    $select.val(oldAlumnoId || '').trigger('change');
                } else if (tipo === 'grupo') {
                    $('#labelDinamico').text('Grupo');
                    $select.attr('name', 'grupo_id');
                    cargarOpciones(grupos, 'grupo');
                    $select.val(oldGrupoId || '').trigger('change');
                } else if (tipo === 'nivel') {
                    $('#labelDinamico').text('Nivel');
                    $select.attr('name', 'nivel_id');
                    cargarOpciones(niveles, 'nivel');
                    $select.val(oldNivelId || '').trigger('change');
                }
            }



            $(function() {
                $('.select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return $(this).data('placeholder');
                    }
                });

                $('#origen').on('change', function() {
                    actualizarSelect();
                });

                $('#origen').on('change', function() {
                    actualizarSelect();
                });

                $('#origen').val(oldOrigen);
                actualizarSelect();

                // Manejar cambio de plan para poblar fechas y conceptos
                $('#plan_id').on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    const fechaInicio = selectedOption.data('fecha-inicio');
                    const fechaFin = selectedOption.data('fecha-fin');
                    const planId = $(this).val();

                    if (fechaInicio && fechaFin) {
                        $('#fecha_inicio').val(fechaInicio);
                        $('#fecha_fin').val(fechaFin);
                        $('#fecha_inicio').prop('readonly', true);
                        $('#fecha_fin').prop('readonly', true);
                    } else {
                        $('#fecha_inicio').val('');
                        $('#fecha_fin').val('');
                        $('#fecha_inicio').prop('readonly', false);
                        $('#fecha_fin').prop('readonly', false);
                    }

                    // Mostrar conceptos del plan
                    mostrarConceptosDelPlan(planId);
                });

                // Si hay un plan seleccionado al cargar, poblar fechas y conceptos
                if ($('#plan_id').val()) {
                    $('#plan_id').trigger('change');
                }

                // Manejar envío del formulario
                $('#form-asignar-plan').on('submit', function(e) {
                    e.preventDefault();

                    // Recopilar conceptos seleccionados
                    const conceptosSeleccionados = [];
                    $('input.concepto-checkbox:checked').each(function() {
                        conceptosSeleccionados.push($(this).data('plan-concepto-id'));
                    });

                    if (conceptosSeleccionados.length === 0) {
                        alert('Debe seleccionar al menos un concepto.');
                        return;
                    }

                    // Crear campo hidden para conceptos
                    if ($('#conceptos-hidden').length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'conceptos-hidden',
                            name: 'conceptos[]'
                        }).appendTo('#form-asignar-plan');
                    }

                    // Limpiar y agregar valores
                    $('#conceptos-hidden').remove();
                    conceptosSeleccionados.forEach(function(conceptoId) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'conceptos[]',
                            value: conceptoId
                        }).appendTo('#form-asignar-plan');
                    });

                    // Enviar formulario
                    this.submit();
                });
            });
        </script>
    @endpush
@endsection
