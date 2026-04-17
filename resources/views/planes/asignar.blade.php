@extends('layouts.master')

@section('page_title', 'Asignación de planes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">
@endpush

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
    <li class="active">Asignar plan</li>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Revisa el formulario.</h4>
            <ul>
                @foreach($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa"></i> Asignar plan</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('planes.asignar') }}" method="POST" id="form-asignar-plan">
                        @csrf

                        <div class="form-group">
                            <label>Plan de pago</label>
                            <select name="plan_id" id="plan_id" class="form-control select2" style="width: 100%;" data-placeholder="Selecciona un plan" required>
                                <option value="">Selecciona un plan</option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}" data-fecha-inicio="{{ $plan->fecha_inicio?->format('Y-m-d') }}" data-fecha-fin="{{ $plan->fecha_fin?->format('Y-m-d') }}" {{ (string) old('plan_id') === (string) $plan->id ? 'selected' : '' }}>
                                        {{ $plan->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Alcance</label>
                            <select name="origen" id="origen" class="form-control">
                                <option value="individual" {{ old('origen', 'individual') === 'individual' ? 'selected' : '' }}>Alumno</option>
                                <option value="grupo" {{ old('origen') === 'grupo' ? 'selected' : '' }}>Grupo</option>
                                <option value="nivel" {{ old('origen') === 'nivel' ? 'selected' : '' }}>Nivel</option>
                            </select>
                            @error('origen')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label id="labelDinamico">Alumno</label>
                            <select id="selectDinamico" class="form-control select2" style="width: 100%;" data-placeholder="Selecciona una opción" required>
                                <option value="">Selecciona una opción</option>
                            </select>
                            @error('alumno_id')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                            @error('grupo_id')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                            @error('nivel_id')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha inicio</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha fin</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Conceptos del Plan -->
                        <div class="form-group">
                            <label><i class="fa fa-list"></i> Conceptos del plan</label>
                            <div id="conceptos-list" class="well well-sm" style="max-height: 300px; overflow-y: auto; min-height: 100px; padding: 10px;">
                                <p class="text-muted text-center">Selecciona un plan para ver los conceptos</p>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="box-footer">
                    <button type="submit" form="form-asignar-plan" class="btn btn-primary btn-block">
                        <i class="fa fa-save"></i> Guardar asignación
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Asignaciones</h3>
                </div>
                <div class="box-body table-responsive">
                    @if ($asignaciones->hasPages())
                        <div class="row" style="margin-bottom: 10px;">
                            <div class="col-sm-6">
                                <span class="text-muted">Mostrando {{ $asignaciones->firstItem() ?: 0 }}-{{ $asignaciones->lastItem() ?: 0 }} de {{ $asignaciones->total() }} asignaciones</span>
                            </div>
                            <div class="col-sm-6 text-right">
                                {{ $asignaciones->links('vendor.pagination.adminlte') }}
                            </div>
                        </div>
                    @endif

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Asignado a</th>
                                <th>Tipo</th>
                                <th>Fecha inicio</th>
                                <th>Fecha fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asignaciones as $a)
                                <tr>
                                    <td>{{ $a->plan->nombre }}</td>
                                    <td>{{ $a->alumno?->nombre_completo ?? $a->grupo?->nombre ?? $a->nivel?->nombre ?? '-' }}</td>
                                    <td>
                                        <span class="label label-info">{{ ucfirst($a->origen) }}</span>
                                    </td>
                                    <td>{{ $a->fecha_inicio?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $a->fecha_fin?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay asignaciones</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

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
                                       data-concepto-id="${concepto.id}"
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



            $(function () {
                $('.select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function () {
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
            });
        </script>
    @endpush
@endsection
