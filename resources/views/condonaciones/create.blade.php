@extends('layouts.master')

@section('page_title', 'Nueva condonación')
@section('page_subtitle', 'Registrar descuento por condonación')

@section('breadcrumb')
    <li><a href="{{ route('condonaciones.index') }}">Condonaciones</a></li>
    <li class="active">Nueva</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">
    <style>
        .con-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 18px;
            align-items: start;
        }

        .con-panel {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
        }

        .con-panel-header {
            background: #f4f6f8;
            border-bottom: 2px solid #e0e7ef;
            padding: 13px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .con-panel-title {
            font-size: 12px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0;
        }

        .con-panel-body  { padding: 16px 18px; }
        .con-panel-footer { padding: 10px 18px; border-top: 1px solid #edf1f5; background: #f9fafb; }

        /* Tabla de cargos */
        .cargo-table { width:100%; border-collapse:separate; border-spacing:0; }
        .cargo-table thead th {
            background: #f4f6f8;
            color: #6b7a8d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 8px 12px;
            border-bottom: 2px solid #e0e6ed;
            border-top: none;
        }
        .cargo-table tbody td { padding: 9px 12px; font-size: 13px; vertical-align: middle; border-bottom: 1px solid #f0f3f7; }
        .cargo-table tbody tr:last-child td { border-bottom: none; }
        .cargo-table tbody tr:hover td { background: #f8fbff; }

        .monto-input {
            width: 110px;
            text-align: right;
            border-radius: 6px !important;
            border: 1px solid #cdd7e0 !important;
            font-size: 13px;
            padding: 5px 8px;
        }
        .monto-input:focus { border-color: #3c8dbc !important; box-shadow: 0 0 0 2px rgba(60,141,188,.15); }

        #resumen-total { font-size: 22px; font-weight: 800; color: #1a6b2e; }
        #resumen-total-masiva { font-size: 22px; font-weight: 800; color: #1a6b2e; }

        .cargo-empty { text-align:center; padding:40px 20px; color:#aab; font-size:13px; }
        .cargo-empty i { font-size:36px; display:block; margin-bottom:10px; }

        .estado-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }
        .estado-pendiente { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .estado-parcial   { background:#cce5ff; color:#004085; border:1px solid #b8daff; }

        /* Tabs */
        .con-tabs {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            margin-bottom: 18px;
        }
        .con-tabs .nav-tabs {
            border-bottom: 2px solid #e0e7ef;
            padding: 0 18px;
            background: #f4f6f8;
            border-radius: 8px 8px 0 0;
        }
        .con-tabs .nav-tabs > li > a {
            font-weight: 700;
            font-size: 13px;
            color: #6b7a8d;
            border-radius: 6px 6px 0 0;
            margin-right: 4px;
        }
        .con-tabs .nav-tabs > li.active > a {
            color: #3c8dbc;
            border-bottom-color: #fff;
            background: #fff;
        }
        .con-tabs .tab-content { padding: 18px; }

        /* Lista de alumnos masiva */
        .alumno-check-list {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #e0e7ef;
            border-radius: 6px;
        }
        .alumno-check-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-bottom: 1px solid #f0f3f7;
            font-size: 13px;
            cursor: pointer;
        }
        .alumno-check-item:last-child { border-bottom: none; }
        .alumno-check-item:hover { background: #f8fbff; }
        .alumno-check-item input[type="checkbox"] { cursor: pointer; }
        .alumno-matricula { font-size: 11px; color: #aab; }
    </style>
@endpush

@section('content')

<div class="con-tabs">
    <ul class="nav nav-tabs" id="con-mode-tabs">
        <li class="active">
            <a href="#tab-individual" data-toggle="tab">
                <i class="fa fa-user"></i> Individual
            </a>
        </li>
        <li>
            <a href="#tab-masiva" data-toggle="tab">
                <i class="fa fa-users"></i> Por Plan (Masiva)
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ══════════════════════════════════════════════════════
             TAB 1 — Individual
        ══════════════════════════════════════════════════════ --}}
        <div id="tab-individual" class="tab-pane active">
            <form id="form-condonacion" method="POST" action="{{ route('condonaciones.store') }}">
                @csrf
                <input type="hidden" name="ciclo_id" value="{{ $cicloActual?->id }}">

                <div class="con-shell">

                    {{-- Panel izquierdo: cargos --}}
                    <div class="con-panel">
                        <div class="con-panel-header">
                            <i class="fa fa-list-alt text-blue"></i>
                            <h4 class="con-panel-title">Cargos a condonar</h4>
                        </div>
                        <div class="con-panel-body">

                            <div class="form-group">
                                <label>Alumno <span class="text-danger">*</span></label>
                                <select id="select-alumno" name="alumno_id" class="form-control" style="width:100%;" required>
                                    <option value="">— Selecciona un alumno —</option>
                                    @foreach ($alumnos as $alumno)
                                        <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                                            {{ $alumno->nombre_completo }}
                                            @if($alumno->matricula) ({{ $alumno->matricula }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('alumno_id')
                                    <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div id="estado-sin-alumno" class="cargo-empty">
                                <i class="fa fa-user-o"></i>
                                Selecciona un alumno para ver sus cargos pendientes.
                            </div>
                            <div id="estado-cargando" style="display:none;" class="cargo-empty">
                                <i class="fa fa-spinner fa-spin"></i>
                                Cargando cargos…
                            </div>
                            <div id="estado-vacio" style="display:none;" class="cargo-empty">
                                <i class="fa fa-check-circle text-success"></i>
                                Este alumno no tiene cargos pendientes en el ciclo actual.
                            </div>

                            <div id="contenedor-cargos" style="display:none;overflow-x:auto;">
                                <table class="cargo-table">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="check-todos" title="Seleccionar todos"></th>
                                            <th>Cargo / Periodo</th>
                                            <th class="text-right">Original</th>
                                            <th class="text-right">Saldo pendiente</th>
                                            <th class="text-right">Monto a condonar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-cargos"></tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    {{-- Panel derecho: datos + resumen --}}
                    <div>
                        <div class="con-panel" style="margin-bottom:14px;">
                            <div class="con-panel-header">
                                <i class="fa fa-pencil text-blue"></i>
                                <h4 class="con-panel-title">Datos de la condonación</h4>
                            </div>
                            <div class="con-panel-body">
                                <div class="form-group">
                                    <label>Motivo <span class="text-danger">*</span></label>
                                    <textarea name="motivo" class="form-control" rows="4"
                                              placeholder="Describe el motivo de la condonación (mín. 10 caracteres)"
                                              style="resize:vertical;" required>{{ old('motivo') }}</textarea>
                                    @error('motivo')
                                        <span class="text-danger" style="font-size:12px;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:12px;color:#888;">Ciclo escolar</label>
                                    <p style="font-size:13px;font-weight:700;margin:0;">{{ $cicloActual?->nombre ?? 'Sin ciclo activo' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="con-panel">
                            <div class="con-panel-header">
                                <i class="fa fa-calculator text-blue"></i>
                                <h4 class="con-panel-title">Resumen</h4>
                            </div>
                            <div class="con-panel-body" style="text-align:center;padding:20px;">
                                <div style="font-size:12px;color:#888;margin-bottom:4px;">Total a condonar</div>
                                <div id="resumen-total">$0.00</div>
                                <div style="font-size:12px;color:#aab;margin-top:8px;">
                                    <span id="resumen-num-cargos">0</span> cargo(s) seleccionado(s)
                                </div>
                            </div>
                            <div class="con-panel-footer">
                                <button type="submit" id="btn-guardar" class="btn btn-success btn-block btn-flat" disabled>
                                    <i class="fa fa-check"></i> Registrar condonación
                                </button>
                                <a href="{{ route('condonaciones.index') }}" class="btn btn-default btn-block btn-flat"
                                   style="margin-top:6px;">Cancelar</a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        {{-- ══════════════════════════════════════════════════════
             TAB 2 — Por Plan (Masiva)
        ══════════════════════════════════════════════════════ --}}
        <div id="tab-masiva" class="tab-pane">
            <form id="form-masiva" method="POST" action="{{ route('condonaciones.masiva') }}">
                @csrf
                <input type="hidden" name="ciclo_id" value="{{ $cicloActual?->id }}">

                <div class="con-shell">

                    {{-- Panel izquierdo: plan + conceptos + alumnos --}}
                    <div>

                        {{-- Paso 1: Seleccionar plan --}}
                        <div class="con-panel" style="margin-bottom:14px;">
                            <div class="con-panel-header">
                                <i class="fa fa-calendar text-blue"></i>
                                <h4 class="con-panel-title">Paso 1 — Seleccionar plan de pago</h4>
                            </div>
                            <div class="con-panel-body">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Plan de pago <span class="text-danger">*</span></label>
                                    <select id="select-plan-masiva" name="plan_id" class="form-control" style="width:100%;" required>
                                        <option value="">— Selecciona un plan —</option>
                                        @foreach ($planes as $plan)
                                            <option value="{{ $plan->id }}">{{ $plan->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Paso 2: Conceptos (aparece tras seleccionar plan) --}}
                        <div class="con-panel" id="panel-conceptos-masiva" style="margin-bottom:14px;display:none;">
                            <div class="con-panel-header">
                                <i class="fa fa-tag text-blue"></i>
                                <h4 class="con-panel-title">Paso 2 — Monto a condonar por concepto</h4>
                            </div>
                            <div class="con-panel-body" style="padding:0;">
                                <table class="cargo-table" id="tabla-conceptos-masiva">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;text-align:center;">Incl.</th>
                                            <th>Concepto</th>
                                            <th class="text-right">Monto del plan</th>
                                            <th class="text-right">Monto a condonar <small class="text-muted">(0 = omitir)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-conceptos-masiva"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Paso 3: Alumnos (aparece tras seleccionar plan) --}}
                        <div class="con-panel" id="panel-alumnos-masiva" style="display:none;">
                            <div class="con-panel-header">
                                <i class="fa fa-users text-blue"></i>
                                <h4 class="con-panel-title">Paso 3 — Seleccionar alumnos</h4>
                                <div style="margin-left:auto;">
                                    <label style="margin:0;font-size:12px;font-weight:600;color:#3c8dbc;cursor:pointer;">
                                        <input type="checkbox" id="check-todos-masiva"> Todos
                                    </label>
                                </div>
                            </div>
                            <div class="con-panel-body" style="padding:0;">
                                <div id="estado-cargando-masiva" class="cargo-empty" style="display:none;">
                                    <i class="fa fa-spinner fa-spin"></i> Cargando alumnos…
                                </div>
                                <div id="estado-sin-alumnos-masiva" class="cargo-empty" style="display:none;">
                                    <i class="fa fa-exclamation-circle text-warning"></i>
                                    Este plan no tiene alumnos asignados en el ciclo actual.
                                </div>
                                <div id="buscador-alumnos-masiva" style="padding:10px 12px;border-bottom:1px solid #e0e7ef;display:none;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                        <input type="text" id="filtro-alumno-masiva" class="form-control"
                                               placeholder="Buscar por nombre o matrícula…">
                                        <span class="input-group-btn">
                                            <button type="button" id="limpiar-filtro-masiva" class="btn btn-default"
                                                    title="Limpiar búsqueda">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <div id="conteo-alumnos-masiva" style="font-size:11px;color:#aab;margin-top:5px;min-height:14px;"></div>
                                </div>
                                <div class="alumno-check-list" id="lista-alumnos-masiva"></div>
                            </div>
                        </div>

                    </div>

                    {{-- Panel derecho: motivo + resumen --}}
                    <div>
                        <div class="con-panel" style="margin-bottom:14px;">
                            <div class="con-panel-header">
                                <i class="fa fa-pencil text-blue"></i>
                                <h4 class="con-panel-title">Datos de la condonación</h4>
                            </div>
                            <div class="con-panel-body">
                                <div class="form-group">
                                    <label>Motivo <span class="text-danger">*</span></label>
                                    <textarea name="motivo" id="motivo-masiva" class="form-control" rows="4"
                                              placeholder="Describe el motivo de la condonación (mín. 10 caracteres)"
                                              style="resize:vertical;" required></textarea>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label style="font-size:12px;color:#888;">Ciclo escolar</label>
                                    <p style="font-size:13px;font-weight:700;margin:0;">{{ $cicloActual?->nombre ?? 'Sin ciclo activo' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="con-panel">
                            <div class="con-panel-header">
                                <i class="fa fa-calculator text-blue"></i>
                                <h4 class="con-panel-title">Resumen</h4>
                            </div>
                            <div class="con-panel-body" style="text-align:center;padding:20px;">
                                <div style="font-size:12px;color:#888;margin-bottom:4px;">Total estimado por alumno</div>
                                <div id="resumen-total-masiva">$0.00</div>
                                <div style="font-size:12px;color:#aab;margin-top:8px;">
                                    <span id="resumen-alumnos-masiva">0</span> alumno(s) seleccionado(s)
                                </div>
                            </div>
                            <div class="con-panel-footer">
                                <button type="submit" id="btn-guardar-masiva" class="btn btn-success btn-block btn-flat" disabled>
                                    <i class="fa fa-check"></i> Registrar condonaciones
                                </button>
                                <a href="{{ route('condonaciones.index') }}" class="btn btn-default btn-block btn-flat"
                                   style="margin-top:6px;">Cancelar</a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

    </div>{{-- /tab-content --}}
</div>{{-- /con-tabs --}}

@endsection

@push('scripts')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
    $(function () {

        // ════════════════════════════════════════════════════════
        // TAB INDIVIDUAL
        // ════════════════════════════════════════════════════════

        $('#select-alumno').select2({
            placeholder: '— Selecciona un alumno —',
            allowClear: true,
            width: '100%',
        });

        $('#select-alumno').on('change', function () {
            const alumnoId = $(this).val();
            if (!alumnoId) { mostrarEstado('sin-alumno'); return; }
            cargarCargos(alumnoId);
        });

        $('#check-todos').on('change', function () {
            const checked = $(this).is(':checked');
            $('.check-cargo').prop('checked', checked).trigger('change');
        });

        function cargarCargos(alumnoId) {
            mostrarEstado('cargando');
            $('#tbody-cargos').empty();

            $.get('{{ route('condonaciones.cargos-alumno', '__id__') }}'.replace('__id__', alumnoId), function (cargos) {
                if (!cargos.length) { mostrarEstado('vacio'); return; }

                cargos.forEach((cargo, idx) => $('#tbody-cargos').append(construirFila(cargo, idx)));
                bindMontoInputs();
                mostrarEstado('cargos');
                actualizarResumen();
            }).fail(function () {
                mostrarEstado('vacio');
                alert('Error al cargar cargos del alumno.');
            });
        }

        function construirFila(cargo, idx) {
            const estadoBadge = cargo.estado === 'parcial'
                ? '<span class="estado-badge estado-parcial">Parcial</span>'
                : '<span class="estado-badge estado-pendiente">Pendiente</span>';

            return `
            <tr>
                <td><input type="checkbox" class="check-cargo" data-idx="${idx}"
                           data-saldo="${cargo.saldo_pendiente}" data-cargo-id="${cargo.id}"></td>
                <td>
                    <div style="font-weight:700;font-size:13px;">${cargo.etiqueta}</div>
                    <div style="font-size:11px;color:#aab;">${cargo.fecha_vencimiento ?? ''} &nbsp; ${estadoBadge}</div>
                </td>
                <td class="text-right" style="font-size:13px;color:#555;">$${fmt(cargo.monto_original)}</td>
                <td class="text-right" style="font-size:13px;font-weight:700;color:#c0392b;">$${fmt(cargo.saldo_pendiente)}</td>
                <td class="text-right">
                    <input type="hidden" name="detalles[${idx}][cargo_id]" value="${cargo.id}" disabled class="hidden-cargo-id">
                    <input type="number" class="monto-input form-control input-monto"
                           name="detalles[${idx}][monto]"
                           value="" min="0.01" max="${cargo.saldo_pendiente}" step="0.01"
                           placeholder="0.00" disabled
                           data-max="${cargo.saldo_pendiente}" data-idx="${idx}">
                </td>
            </tr>`;
        }

        function bindMontoInputs() {
            $(document).off('change', '.check-cargo').on('change', '.check-cargo', function () {
                const idx     = $(this).data('idx');
                const checked = $(this).is(':checked');
                const saldo   = parseFloat($(this).data('saldo'));

                $(`input[name="detalles[${idx}][monto]"]`).prop('disabled', !checked);
                $(`input[name="detalles[${idx}][cargo_id]"]`).prop('disabled', !checked);

                if (checked) $(`input[name="detalles[${idx}][monto]"]`).val(saldo.toFixed(2)).focus();
                else         $(`input[name="detalles[${idx}][monto]"]`).val('');

                actualizarResumen();
            });

            $(document).off('input', '.input-monto').on('input', '.input-monto', function () {
                const max = parseFloat($(this).data('max'));
                const val = parseFloat($(this).val()) || 0;
                if (val > max) $(this).val(max.toFixed(2));
                actualizarResumen();
            });
        }

        function actualizarResumen() {
            let total = 0, numCargos = 0;
            $('.check-cargo:checked').each(function () {
                const idx   = $(this).data('idx');
                const monto = parseFloat($(`input[name="detalles[${idx}][monto]"]`).val()) || 0;
                total += monto;
                numCargos++;
            });
            $('#resumen-total').text('$' + fmt(total));
            $('#resumen-num-cargos').text(numCargos);
            $('#btn-guardar').prop('disabled', total <= 0 || numCargos === 0);
        }

        $('#form-condonacion').on('submit', function (e) {
            let valido = true;
            $('.check-cargo:checked').each(function () {
                const idx   = $(this).data('idx');
                const max   = parseFloat($(`input[name="detalles[${idx}][monto]"]`).data('max'));
                const monto = parseFloat($(`input[name="detalles[${idx}][monto]"]`).val()) || 0;
                if (monto <= 0 || monto > max) {
                    alert('Revisa que todos los montos sean mayores a 0 y no excedan el saldo pendiente.');
                    valido = false;
                    return false;
                }
            });
            if (!valido) e.preventDefault();
        });

        function mostrarEstado(estado) {
            $('#estado-sin-alumno, #estado-cargando, #estado-vacio, #contenedor-cargos').hide();
            if (estado === 'sin-alumno') $('#estado-sin-alumno').show();
            else if (estado === 'cargando') $('#estado-cargando').show();
            else if (estado === 'vacio')   $('#estado-vacio').show();
            else if (estado === 'cargos')  $('#contenedor-cargos').show();
        }

        const alumnoInicial = $('#select-alumno').val();
        if (alumnoInicial) cargarCargos(alumnoInicial);

        // ════════════════════════════════════════════════════════
        // TAB MASIVA
        // ════════════════════════════════════════════════════════

        $('#select-plan-masiva').select2({
            placeholder: '— Selecciona un plan —',
            allowClear: true,
            width: '100%',
        });

        $('#select-plan-masiva').on('change', function () {
            const planId = $(this).val();
            if (!planId) {
                $('#panel-conceptos-masiva, #panel-alumnos-masiva').hide();
                $('#tbody-conceptos-masiva').empty();
                $('#lista-alumnos-masiva').empty();
                $('#buscador-alumnos-masiva').hide();
                $('#filtro-alumno-masiva').val('');
                $('#conteo-alumnos-masiva').text('');
                actualizarResumenMasiva();
                return;
            }
            cargarDatosPlan(planId);
        });

        function cargarDatosPlan(planId) {
            $('#panel-conceptos-masiva').hide();
            $('#panel-alumnos-masiva').show();
            $('#estado-cargando-masiva').show();
            $('#estado-sin-alumnos-masiva').hide();
            $('#buscador-alumnos-masiva').hide();
            $('#filtro-alumno-masiva').val('');
            $('#conteo-alumnos-masiva').text('');
            $('#lista-alumnos-masiva').empty();
            $('#tbody-conceptos-masiva').empty();

            const url = '{{ route('planes.alumnos-asignados', '__id__') }}'.replace('__id__', planId);

            $.get(url, function (data) {
                $('#estado-cargando-masiva').hide();

                // Renderizar conceptos
                data.conceptos.forEach((c, idx) => {
                    const badge = c.ya_condonado
                        ? ` <span style="font-size:10px;font-weight:600;background:#fff3cd;color:#856404;
                                         border:1px solid #ffc107;border-radius:8px;padding:1px 6px;vertical-align:middle;"
                               title="${c.ultima_condonacion ? 'Cond. #' + c.ultima_condonacion.id + ' del ' + c.ultima_condonacion.fecha : 'Ya condonado'}">
                               ↺ reemplaza cond.${c.ultima_condonacion ? ' #' + c.ultima_condonacion.id : ''}
                           </span>`
                        : '';

                    $('#tbody-conceptos-masiva').append(`
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" class="check-concepto-masiva" data-idx="${idx}" checked>
                            </td>
                            <td style="font-weight:600;">${c.nombre}${badge}</td>
                            <td class="text-right" style="color:#555;">$${fmt(c.monto)}</td>
                            <td class="text-right">
                                <input type="hidden" name="conceptos[${idx}][concepto_id]" value="${c.concepto_id}">
                                <input type="number" class="monto-input form-control monto-masiva"
                                       name="conceptos[${idx}][monto]"
                                       value="${c.monto.toFixed(2)}"
                                       min="0" max="${c.monto}" step="0.01"
                                       data-max="${c.monto}" placeholder="0.00">
                            </td>
                        </tr>
                    `);
                });

                $('#panel-conceptos-masiva').show();

                // Renderizar alumnos
                if (!data.alumnos.length) {
                    $('#estado-sin-alumnos-masiva').show();
                } else {
                    data.alumnos.forEach(a => {
                        const mat = a.matricula ? `<span class="alumno-matricula">(${a.matricula})</span>` : '';
                        $('#lista-alumnos-masiva').append(`
                            <label class="alumno-check-item">
                                <input type="checkbox" name="alumno_ids[]" value="${a.id}" class="check-alumno-masiva">
                                <span>${a.nombre_completo} ${mat}</span>
                            </label>
                        `);
                    });
                    $('#buscador-alumnos-masiva').show();
                }

                actualizarResumenMasiva();
            }).fail(function () {
                $('#estado-cargando-masiva').hide();
                $('#estado-sin-alumnos-masiva').show();
            });
        }

        $(document).on('change', '.check-alumno-masiva, .monto-masiva', actualizarResumenMasiva);

        // Checkbox por concepto: habilita/deshabilita el monto correspondiente
        $(document).on('change', '.check-concepto-masiva:not(:disabled)', function () {
            const idx     = $(this).data('idx');
            const checked = $(this).is(':checked');
            const $hiddenId = $(`input[name="conceptos[${idx}][concepto_id]"]`);
            const $monto    = $(`input[name="conceptos[${idx}][monto]"]`);

            $hiddenId.prop('disabled', !checked);
            $monto.prop('disabled', !checked);

            if (!checked) {
                $monto.val('0');
            } else {
                const max = parseFloat($monto.data('max')) || 0;
                if ((parseFloat($monto.val()) || 0) <= 0) {
                    $monto.val(max.toFixed(2));
                }
            }

            actualizarResumenMasiva();
        });

        $('#check-todos-masiva').on('change', function () {
            const checked = $(this).is(':checked');
            $('#lista-alumnos-masiva .alumno-check-item:visible .check-alumno-masiva').prop('checked', checked);
            actualizarResumenMasiva();
        });

        $('#filtro-alumno-masiva').on('input', function () {
            const q = $(this).val().toLowerCase().trim();
            let visibles = 0, total = 0;

            $('#lista-alumnos-masiva .alumno-check-item').each(function () {
                const texto = $(this).text().toLowerCase();
                const mostrar = !q || texto.includes(q);
                $(this).toggle(mostrar);
                if (mostrar) visibles++;
                total++;
            });

            $('#conteo-alumnos-masiva').text(q ? `Mostrando ${visibles} de ${total} alumnos` : '');

            const sinVisiblesChecked = $('#lista-alumnos-masiva .alumno-check-item:visible .check-alumno-masiva:not(:checked)').length;
            $('#check-todos-masiva').prop('checked', visibles > 0 && sinVisiblesChecked === 0);
        });

        $('#limpiar-filtro-masiva').on('click', function () {
            $('#filtro-alumno-masiva').val('').trigger('input');
        });

        $(document).on('input', '.monto-masiva:not(:disabled)', function () {
            const max = parseFloat($(this).data('max'));
            const val = parseFloat($(this).val()) || 0;
            if (max && val > max) $(this).val(max.toFixed(2));
            actualizarResumenMasiva();
        });

        function actualizarResumenMasiva() {
            let totalPorAlumno = 0;
            $('.monto-masiva:not(:disabled)').each(function () {
                totalPorAlumno += parseFloat($(this).val()) || 0;
            });

            const numAlumnos = $('.check-alumno-masiva:checked').length;

            $('#resumen-total-masiva').text('$' + fmt(totalPorAlumno));
            $('#resumen-alumnos-masiva').text(numAlumnos);

            const hayConceptosActivos = $('.monto-masiva:not(:disabled)').length > 0;
            const hayAlumnos          = numAlumnos > 0;
            const hayMonto            = totalPorAlumno > 0;
            $('#btn-guardar-masiva').prop('disabled', !hayConceptosActivos || !hayAlumnos || !hayMonto);
        }

        $('#form-masiva').on('submit', function (e) {
            if ($('.check-alumno-masiva:checked').length === 0) {
                alert('Selecciona al menos un alumno.');
                e.preventDefault();
                return;
            }
            const totalMonto = $('.monto-masiva:not(:disabled)').toArray()
                .reduce((acc, el) => acc + (parseFloat(el.value) || 0), 0);
            if (totalMonto <= 0) {
                alert('Al menos un concepto debe tener un monto mayor a $0.00.');
                e.preventDefault();
            }
        });

        // ── Shared helper ────────────────────────────────────
        function fmt(num) {
            return parseFloat(num || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

    });
    </script>
@endpush
