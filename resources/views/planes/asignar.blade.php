@extends('layouts.master')

@section('page_title', 'Asignación de planes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">

    <style>
        /* ══ PANELES ══ */
        .asg-panel {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
            margin-bottom: 0;
        }

        .asg-panel-header {
            background: #f4f6f8;
            border-bottom: 2px solid #e0e7ef;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .asg-panel-title {
            font-size: 12px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0;
        }

        .asg-panel-body {
            padding: 14px 16px;
        }

        .asg-panel-footer {
            padding: 10px 16px;
            border-top: 1px solid #edf1f5;
            background: #f9fafb;
        }

        /* ══ FORM COMPACTO ══ */
        .asg-panel-body .form-group {
            margin-bottom: 10px;
        }

        .asg-panel-body label {
            font-size: 12px;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .asg-panel-body .form-control {
            border-radius: 6px !important;
            border: 1px solid #d0dbe6;
            box-shadow: none;
            height: 32px;
            font-size: 13px;
            padding: 4px 10px;
            color: #1a2634;
        }

        .asg-panel-body .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60,141,188,.12);
        }

        /* ══ SELECT2 alineado a 32px ══ */
        .select2-container--default .select2-selection--single {
            border: 1px solid #d0dbe6;
            border-radius: 6px !important;
            height: 32px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1a2634;
            font-size: 13px;
            line-height: 30px;
            padding-left: 10px;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #aab;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60,141,188,.12);
        }

        /* ══ CONCEPTOS ══ */
        #conceptos-list {
            max-height: 390px;
            overflow-y: auto;
        }

        .concepto-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid #f0f3f7;
            margin-bottom: 6px;
            background: #fafbfc;
            transition: background .1s;
        }

        .concepto-item:hover { background: #f0f7ff; border-color: #d6e8f8; }
        .concepto-item:last-child { margin-bottom: 0; }

        .concepto-item label {
            margin: 0;
            cursor: pointer;
            flex: 1;
            font-size: 13px;
            font-weight: 600;
            color: #1a2634;
            text-transform: none;
            letter-spacing: 0;
        }

        .concepto-monto {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
            white-space: nowrap;
        }

        .concepto-checkbox {
            width: 15px;
            height: 15px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .concepto-empty {
            text-align: center;
            padding: 30px 16px;
            color: #aab;
            font-size: 13px;
        }

        .concepto-empty i {
            display: block;
            font-size: 28px;
            color: #dde4ea;
            margin-bottom: 8px;
        }

        /* ══ TIPO BADGE ══ */
        .concepto-tipo {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 1px 7px;
            border-radius: 10px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .tipo-colegiatura    { background:#e8f0fb; color:#1e4d7b; border:1px solid #c3d6f5; }
        .tipo-inscripcion    { background:#fef9e7; color:#7d6608; border:1px solid #f9e79f; }
        .tipo-cargo_unico    { background:#f9ebf8; color:#6c3483; border:1px solid #e8b4e6; }
        .tipo-cargo_recurrente { background:#e8f8f0; color:#1e6641; border:1px solid #a9dfbf; }

        /* ══ PREVIEW PERÍODOS ══ */
        #periodos-preview {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px solid #e0e7ef;
        }
        .periodos-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7a8d;
            margin-bottom: 7px;
        }
        .periodos-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .periodo-pill {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 12px;
            background: #e8f0fb;
            color: #1e4d7b;
            border: 1px solid #c3d6f5;
        }
        .periodos-empty {
            font-size: 12px;
            color: #aab;
        }
    </style>
@endpush

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
    <li class="active">Asignar plan</li>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ══ ENCABEZADO ══ --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-credit-card text-blue"></i> Asignar plan de pago
        </h4>
        <a href="{{ route('planes.asignar.index') }}" class="btn btn-default btn-sm btn-flat"
           style="border-radius:20px;flex-shrink:0;">
            <i class="fa fa-arrow-left"></i> Asignaciones
        </a>
    </div>

    {{-- ══ FORMULARIO ══ --}}
    <form action="{{ route('planes.asignar') }}" method="POST" id="form-asignar-plan">
        @csrf

        <div class="row">
            {{-- ── PANEL IZQUIERDO: FORMULARIO ── --}}
            <div class="col-md-6">
                <div class="asg-panel">
                    <div class="asg-panel-header">
                        <i class="fa fa-credit-card" style="color:#3c8dbc;"></i>
                        <h4 class="asg-panel-title">Datos de la asignación</h4>
                    </div>

                    <div class="asg-panel-body">

                        <div class="form-group">
                            <label>Plan de pago <span class="text-red">*</span></label>
                            <select name="plan_id" id="plan_id" class="form-control select2"
                                    data-placeholder="Selecciona un plan" required>
                                <option value="">Selecciona un plan</option>
                                @foreach ($planes as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-fecha-inicio="{{ $plan->fecha_inicio?->format('Y-m-d') }}"
                                        data-fecha-fin="{{ $plan->fecha_fin?->format('Y-m-d') }}"
                                        @selected(old('plan_id') == $plan->id)>
                                        {{ $plan->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Alcance <span class="text-red">*</span></label>
                            <select name="origen" id="origen" class="form-control">
                                <option value="individual">Alumno</option>
                                <option value="grupo">Grupo</option>
                                <option value="nivel">Nivel</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label id="labelDinamico">Alumno <span class="text-red">*</span></label>
                            <select id="selectDinamico" class="form-control select2"></select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha inicio</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                                           class="form-control" value="{{ old('fecha_inicio') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fecha fin</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin"
                                           class="form-control" value="{{ old('fecha_fin') }}">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="asg-panel-footer" style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm btn-flat"
                                style="border-radius:20px;padding:6px 18px;">
                            <i class="fa fa-save"></i> Guardar asignación
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── PANEL DERECHO: CONCEPTOS ── --}}
            <div class="col-md-6">
                <div class="asg-panel">
                    <div class="asg-panel-header">
                        <i class="fa fa-list-alt" style="color:#3c8dbc;"></i>
                        <h4 class="asg-panel-title">Conceptos del plan</h4>
                    </div>

                    <div class="asg-panel-body">
                        <div id="conceptos-list">
                            <div class="concepto-empty">
                                <i class="fa fa-list-alt"></i>
                                Selecciona un plan para ver sus conceptos
                            </div>
                        </div>

                        <div id="periodos-preview" style="display:none;">
                            <div class="periodos-label">
                                <i class="fa fa-calendar" style="color:#3c8dbc;margin-right:4px;"></i>
                                Períodos a generar
                            </div>
                            <div class="periodos-pills" id="periodos-pills"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
        <script>
            const alumnos     = @json($alumnos);
            const grupos      = @json($grupos);
            const niveles     = @json($niveles);
            const planes      = @json($planesData);
            const oldOrigen   = @json(old('origen', 'individual'));
            const oldAlumnoId = @json(old('alumno_id', $preAlumnoId ?? null));
            const oldGrupoId  = @json(old('grupo_id'));
            const oldNivelId  = @json(old('nivel_id'));
            const oldFechaInicio = @json(old('fecha_inicio'));
            const oldFechaFin    = @json(old('fecha_fin'));

            function formatearNombreAlumno(item) {
                return [item.nombre, item.ap_paterno, item.ap_materno].filter(Boolean).join(' ');
            }

            const mesesEs = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

            const tipoBadgeClase = {
                colegiatura:     'tipo-colegiatura',
                inscripcion:     'tipo-inscripcion',
                cargo_unico:     'tipo-cargo_unico',
                cargo_recurrente:'tipo-cargo_recurrente',
            };
            const tipoLabel = {
                colegiatura:     'Colegiatura',
                inscripcion:     'Inscripción',
                cargo_unico:     'Único',
                cargo_recurrente:'Recurrente',
            };

            function calcularPeriodosJS(fechaInicioStr, fechaFinStr, periodicidad) {
                if (!fechaInicioStr || !fechaFinStr) return [];

                // Parsear como fecha local (sin desplazamiento de zona horaria)
                const parteInicio = fechaInicioStr.split('-').map(Number);
                const parteFin    = fechaFinStr.split('-').map(Number);
                const inicio = new Date(parteInicio[0], parteInicio[1] - 1, parteInicio[2]);
                const fin    = new Date(parteFin[0],    parteFin[1] - 1,    parteFin[2]);

                if (inicio > fin) return [];

                if (periodicidad === 'unico') {
                    return [`${parteInicio[0]}-${String(parteInicio[1]).padStart(2,'0')}`];
                }

                const mesesPorIntervalo = { mensual: 1, bimestral: 2, semestral: 6, anual: 12 };
                const intervalo = mesesPorIntervalo[periodicidad] || 1;

                const periodos = [];
                const actual = new Date(inicio);

                while (actual <= fin) {
                    const y = actual.getFullYear();
                    const m = actual.getMonth() + 1;
                    periodos.push(`${y}-${String(m).padStart(2,'0')}`);
                    actual.setMonth(actual.getMonth() + intervalo);
                }

                return periodos;
            }

            function actualizarPreviewPeriodos() {
                const planId = $('#plan_id').val();
                const plan   = planes.find(p => p.id == planId);
                const preview = document.getElementById('periodos-preview');
                const pillsEl = document.getElementById('periodos-pills');

                if (!plan) { preview.style.display = 'none'; return; }

                // Usar fechas del formulario si están presentes; si no, las del plan
                const fi = $('#fecha_inicio').val() || plan.fecha_inicio;
                const ff = $('#fecha_fin').val()    || plan.fecha_fin;

                const periodos = calcularPeriodosJS(fi, ff, plan.periodicidad);

                if (periodos.length === 0) { preview.style.display = 'none'; return; }

                pillsEl.innerHTML = periodos.map(p => {
                    const [y, m] = p.split('-');
                    return `<span class="periodo-pill">${mesesEs[parseInt(m,10)-1]} ${y}</span>`;
                }).join('');

                preview.style.display = 'block';
            }

            function mostrarConceptosDelPlan(planId) {
                const conceptosList = document.getElementById('conceptos-list');

                if (!planId) {
                    conceptosList.innerHTML = `
                        <div class="concepto-empty">
                            <i class="fa fa-list-alt"></i>
                            Selecciona un plan para ver sus conceptos
                        </div>`;
                    document.getElementById('periodos-preview').style.display = 'none';
                    return;
                }

                const plan = planes.find(p => p.id == planId);

                if (!plan || plan.conceptos.length === 0) {
                    conceptosList.innerHTML = `
                        <div class="concepto-empty">
                            <i class="fa fa-inbox"></i>
                            Este plan no tiene conceptos asociados
                        </div>`;
                    document.getElementById('periodos-preview').style.display = 'none';
                    return;
                }

                let html = '';
                plan.conceptos.forEach(concepto => {
                    const badgeClase = tipoBadgeClase[concepto.tipo] || 'tipo-cargo_unico';
                    const badgeTxt   = tipoLabel[concepto.tipo]      || concepto.tipo;
                    html += `
                        <div class="concepto-item">
                            <input type="checkbox" id="concepto_${concepto.id}"
                                   class="concepto-checkbox"
                                   data-plan-concepto-id="${concepto.id}"
                                   data-concepto-nombre="${concepto.nombre}"
                                   checked>
                            <label for="concepto_${concepto.id}">${concepto.nombre}</label>
                            <span class="concepto-tipo ${badgeClase}">${badgeTxt}</span>
                            <span class="concepto-monto">$${parseFloat(concepto.monto).toFixed(2)}</span>
                        </div>`;
                });

                conceptosList.innerHTML = html;
                actualizarPreviewPeriodos();
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
                const tipo    = $('#origen').val();
                const $select = $('#selectDinamico');

                if (tipo === 'individual') {
                    $('#labelDinamico').html('Alumno <span class="text-red">*</span>');
                    $select.attr('name', 'alumno_id');
                    cargarOpciones(alumnos, 'alumno');
                    $select.val(oldAlumnoId || '').trigger('change');
                } else if (tipo === 'grupo') {
                    $('#labelDinamico').html('Grupo <span class="text-red">*</span>');
                    $select.attr('name', 'grupo_id');
                    cargarOpciones(grupos, 'grupo');
                    $select.val(oldGrupoId || '').trigger('change');
                } else if (tipo === 'nivel') {
                    $('#labelDinamico').html('Nivel <span class="text-red">*</span>');
                    $select.attr('name', 'nivel_id');
                    cargarOpciones(niveles, 'nivel');
                    $select.val(oldNivelId || '').trigger('change');
                }
            }

            $(function() {
                $('.select2').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() { return $(this).data('placeholder'); }
                });

                $('#origen').on('change', actualizarSelect);
                $('#origen').val(oldOrigen);
                actualizarSelect();

                function actualizarDatosDelPlan(preservarFechasAnteriores = false) {
                    const selectedOption = $(this).find('option:selected');
                    const fechaInicio    = selectedOption.data('fecha-inicio');
                    const fechaFin       = selectedOption.data('fecha-fin');
                    const planId         = $(this).val();

                    if (!preservarFechasAnteriores || !oldFechaInicio) {
                        $('#fecha_inicio').val(fechaInicio || '');
                    }
                    if (!preservarFechasAnteriores || !oldFechaFin) {
                        $('#fecha_fin').val(fechaFin || '');
                    }

                    mostrarConceptosDelPlan(planId);
                }

                $('#plan_id').on('change', function() {
                    actualizarDatosDelPlan.call(this);
                });

                if ($('#plan_id').val()) {
                    actualizarDatosDelPlan.call($('#plan_id')[0], true);
                }

                // Recalcular preview de períodos cuando el usuario cambia las fechas
                $('#fecha_inicio, #fecha_fin').on('change', actualizarPreviewPeriodos);

                $('#form-asignar-plan').on('submit', function(e) {
                    e.preventDefault();

                    const conceptosSeleccionados = [];
                    $('input.concepto-checkbox:checked').each(function() {
                        conceptosSeleccionados.push($(this).data('plan-concepto-id'));
                    });

                    if (conceptosSeleccionados.length === 0) {
                        alert('Debe seleccionar al menos un concepto.');
                        return;
                    }

                    $('#conceptos-hidden').remove();
                    conceptosSeleccionados.forEach(function(conceptoId) {
                        $('<input>').attr({ type: 'hidden', name: 'conceptos[]', value: conceptoId })
                                   .appendTo('#form-asignar-plan');
                    });

                    this.submit();
                });
            });
        </script>
    @endpush
@endsection
