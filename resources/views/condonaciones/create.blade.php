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

        #resumen-total {
            font-size: 22px;
            font-weight: 800;
            color: #1a6b2e;
        }

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
    </style>
@endpush

@section('content')

<form id="form-condonacion" method="POST" action="{{ route('condonaciones.store') }}">
    @csrf

    <input type="hidden" name="ciclo_id" value="{{ $cicloActual?->id }}">

    <div class="con-shell">

        {{-- ── Panel izquierdo: cargos ──────────────────── --}}
        <div class="con-panel">
            <div class="con-panel-header">
                <i class="fa fa-list-alt text-blue"></i>
                <h4 class="con-panel-title">Cargos a condonar</h4>
            </div>

            <div class="con-panel-body">

                {{-- Selector de alumno --}}
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

                {{-- Estado: sin alumno --}}
                <div id="estado-sin-alumno" class="cargo-empty">
                    <i class="fa fa-user-o"></i>
                    Selecciona un alumno para ver sus cargos pendientes.
                </div>

                {{-- Estado: cargando --}}
                <div id="estado-cargando" style="display:none;" class="cargo-empty">
                    <i class="fa fa-spinner fa-spin"></i>
                    Cargando cargos…
                </div>

                {{-- Estado: sin cargos --}}
                <div id="estado-vacio" style="display:none;" class="cargo-empty">
                    <i class="fa fa-check-circle text-success"></i>
                    Este alumno no tiene cargos pendientes en el ciclo actual.
                </div>

                {{-- Tabla de cargos --}}
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

        {{-- ── Panel derecho: datos generales ──────────── --}}
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

            {{-- Resumen --}}
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
                       style="margin-top:6px;">
                        Cancelar
                    </a>
                </div>
            </div>

        </div>
    </div>

</form>

@endsection

@push('scripts')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
    $(function () {

        // ── Select2 para alumno ──────────────────────────
        $('#select-alumno').select2({
            placeholder: '— Selecciona un alumno —',
            allowClear: true,
            width: '100%',
        });

        // ── Cuando cambia el alumno ──────────────────────
        $('#select-alumno').on('change', function () {
            const alumnoId = $(this).val();
            if (!alumnoId) {
                mostrarEstado('sin-alumno');
                return;
            }
            cargarCargos(alumnoId);
        });

        // ── Check-todos ──────────────────────────────────
        $('#check-todos').on('change', function () {
            const checked = $(this).is(':checked');
            $('.check-cargo').prop('checked', checked).trigger('change');
        });

        // ── Cargar cargos del alumno ─────────────────────
        function cargarCargos(alumnoId) {
            mostrarEstado('cargando');
            $('#tbody-cargos').empty();

            $.get('{{ route('condonaciones.cargos-alumno', '__id__') }}'.replace('__id__', alumnoId), function (cargos) {
                if (!cargos.length) {
                    mostrarEstado('vacio');
                    return;
                }

                cargos.forEach((cargo, idx) => {
                    const fila = construirFila(cargo, idx);
                    $('#tbody-cargos').append(fila);
                });

                bindMontoInputs();
                mostrarEstado('cargos');
                actualizarResumen();
            }).fail(function () {
                mostrarEstado('vacio');
                alert('Error al cargar cargos del alumno.');
            });
        }

        // ── Construir fila de cargo ──────────────────────
        function construirFila(cargo, idx) {
            const estadoBadge = cargo.estado === 'parcial'
                ? '<span class="estado-badge estado-parcial">Parcial</span>'
                : '<span class="estado-badge estado-pendiente">Pendiente</span>';

            return `
            <tr>
                <td>
                    <input type="checkbox" class="check-cargo" data-idx="${idx}"
                           data-saldo="${cargo.saldo_pendiente}" data-cargo-id="${cargo.id}">
                </td>
                <td>
                    <div style="font-weight:700;font-size:13px;">${cargo.etiqueta}</div>
                    <div style="font-size:11px;color:#aab;">${cargo.fecha_vencimiento ?? ''} &nbsp; ${estadoBadge}</div>
                </td>
                <td class="text-right" style="font-size:13px;color:#555;">
                    $${formatNum(cargo.monto_original)}
                </td>
                <td class="text-right" style="font-size:13px;font-weight:700;color:#c0392b;">
                    $${formatNum(cargo.saldo_pendiente)}
                </td>
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

        // ── Bind eventos a inputs de monto ───────────────
        function bindMontoInputs() {
            $(document).off('change', '.check-cargo').on('change', '.check-cargo', function () {
                const idx  = $(this).data('idx');
                const checked = $(this).is(':checked');
                const saldo = parseFloat($(this).data('saldo'));

                $(`input[name="detalles[${idx}][monto]"]`).prop('disabled', !checked);
                $(`input[name="detalles[${idx}][cargo_id]"]`).prop('disabled', !checked);

                if (checked) {
                    $(`input[name="detalles[${idx}][monto]"]`).val(saldo.toFixed(2)).focus();
                } else {
                    $(`input[name="detalles[${idx}][monto]"]`).val('');
                }
                actualizarResumen();
            });

            $(document).off('input', '.input-monto').on('input', '.input-monto', function () {
                const max = parseFloat($(this).data('max'));
                const val = parseFloat($(this).val()) || 0;
                if (val > max) $(this).val(max.toFixed(2));
                actualizarResumen();
            });
        }

        // ── Actualizar totales del resumen ────────────────
        function actualizarResumen() {
            let total = 0;
            let numCargos = 0;

            $('.check-cargo:checked').each(function () {
                const idx = $(this).data('idx');
                const monto = parseFloat($(`input[name="detalles[${idx}][monto]"]`).val()) || 0;
                total += monto;
                numCargos++;
            });

            $('#resumen-total').text('$' + formatNum(total));
            $('#resumen-num-cargos').text(numCargos);
            $('#btn-guardar').prop('disabled', total <= 0 || numCargos === 0);
        }

        // ── Validar antes de enviar ───────────────────────
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

        // ── Helpers ──────────────────────────────────────
        function mostrarEstado(estado) {
            $('#estado-sin-alumno, #estado-cargando, #estado-vacio, #contenedor-cargos').hide();
            if (estado === 'sin-alumno') $('#estado-sin-alumno').show();
            else if (estado === 'cargando') $('#estado-cargando').show();
            else if (estado === 'vacio') $('#estado-vacio').show();
            else if (estado === 'cargos') $('#contenedor-cargos').show();
        }

        function formatNum(num) {
            return parseFloat(num || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Si ya había alumno seleccionado (old input)
        const alumnoInicial = $('#select-alumno').val();
        if (alumnoInicial) cargarCargos(alumnoInicial);

    });
    </script>
@endpush
