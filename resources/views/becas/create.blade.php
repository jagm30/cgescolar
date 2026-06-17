@extends('layouts.master')

@section('page_title', 'Asignar beca')
@section('page_subtitle', 'Nueva asignación')

@section('breadcrumb')
    <li><a href="{{ route('becas.index') }}">Becas</a></li>
    <li class="active">Asignar beca</li>
@endsection

@push('styles')
    <style>
        .bec-form-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 18px;
            align-items: start;
        }

        .bec-form-panel,
        .bec-side-panel {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

        .bec-form-header,
        .bec-side-header {
            background: #f4f6f8;
            border-bottom: 2px solid #e0e7ef;
            padding: 13px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .bec-form-title {
            font-size: 13px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .bec-form-body,
        .bec-side-body {
            padding: 14px 16px;
        }

        .bec-form-footer,
        .bec-side-footer {
            padding: 10px 16px;
            border-top: 1px solid #edf1f5;
            background: #f9fafb;
        }

        .bec-section-title {
            font-size: 11px;
            font-weight: 800;
            color: #3c8dbc;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 2px 0 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .bec-form-body .form-group {
            margin-bottom: 10px;
        }

        .bec-form-body label {
            font-size: 12px;
            margin-bottom: 3px;
        }

        .bec-form-panel .form-control {
            border-radius: 6px !important;
            border: 1px solid #d0dbe6;
            box-shadow: none;
            height: 32px;
            font-size: 13px;
            padding: 4px 10px;
        }

        .bec-form-panel textarea.form-control {
            height: auto;
        }

        .bec-form-panel .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, .12);
        }

        .bec-discount-preview {
            background: #f3e8fd !important;
            color: #6b21a8;
            border-color: #d8b4fe !important;
            font-weight: 800;
        }

        .bec-action-bar {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .bec-side-kicker {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 2px;
        }

        .bec-side-main {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }

        .bec-student-empty,
        .bec-student-error {
            text-align: center;
            padding: 16px 12px;
            color: #9aa6b2;
        }

        .bec-student-empty i,
        .bec-student-error i {
            display: block;
            font-size: 30px;
            color: #dde4ea;
            margin-bottom: 8px;
        }

        .bec-student-error {
            color: #dd4b39;
        }

        .bec-student-error i {
            color: #f4b8b0;
        }

        .bec-current-alert {
            border-radius: 6px;
            border: 1px solid #f5d68a;
            background: #fff8e6;
            color: #8a5d08;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .bec-current-card {
            border: 1px solid #e0e7ef;
            border-left: 3px solid #f39c12;
            border-radius: 6px;
            padding: 11px 12px;
            margin-bottom: 10px;
            background: #fff;
        }

        .bec-current-title {
            font-size: 13px;
            font-weight: 800;
            color: #1a2634;
        }

        .bec-current-meta {
            font-size: 12px;
            color: #7a8794;
            margin-top: 3px;
        }

        .bec-checkbox-card {
            border: 1px solid #dde4eb;
            border-radius: 6px;
            background: #fff;
            padding: 11px 12px;
            margin: 0 0 10px;
        }

        .bec-help {
            color: #8a9ab0;
            font-size: 12px;
            margin: 0;
        }

        @media (max-width: 991px) {
            .bec-form-shell {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Revisa el formulario.</h4>
            <ul style="margin-bottom:0;">
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
            <i class="fa fa-graduation-cap text-blue"></i> Asignar beca
        </h4>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <a href="{{ route('becas.catalogo') }}" class="btn btn-default btn-sm btn-flat"
               style="border-radius:20px;">
                <i class="fa fa-list"></i> Catálogo
            </a>
            <a href="{{ route('becas.index') }}" class="btn btn-default btn-sm btn-flat"
               style="border-radius:20px;">
                <i class="fa fa-arrow-left"></i> Asignaciones
            </a>
        </div>
    </div>

    <form action="{{ route('becas.store') }}" method="POST">
        @csrf

        <div class="bec-form-shell">
            <div class="bec-form-panel">
                <div class="bec-form-header">
                    <h3 class="bec-form-title">
                        <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
                        Datos de la beca
                    </h3>
                </div>

                <div class="bec-form-body">
                    <div class="bec-section-title">
                        <i class="fa fa-user"></i> Alumno y ciclo
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group {{ $errors->has('alumno_id') ? 'has-error' : '' }}">
                                <label for="alumno-select">Alumno <span class="text-red">*</span></label>
                                <select id="alumno-select" name="alumno_id" class="form-control" required>
                                    <option value="">Selecciona un alumno</option>
                                    @foreach ($alumnos as $alumno)
                                        <option value="{{ $alumno->id }}"
                                            {{ old('alumno_id', request('alumno_id')) == $alumno->id ? 'selected' : '' }}>
                                            {{ $alumno->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('alumno_id')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                                <label for="ciclo_id">Ciclo escolar <span class="text-red">*</span></label>
                                <select name="ciclo_id" id="ciclo_id" class="form-control" required>
                                    <option value="">Selecciona un ciclo</option>
                                    @foreach ($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}"
                                            {{ old('ciclo_id', $cicloActual?->id ?? '') == $ciclo->id ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ciclo_id')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bec-section-title" style="margin-top:10px;">
                        <i class="fa fa-percent"></i> Configuración
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group {{ $errors->has('catalogo_beca_id') ? 'has-error' : '' }}">
                                <label for="catalogo-beca-select">Tipo de beca <span class="text-red">*</span></label>
                                <select name="catalogo_beca_id" id="catalogo-beca-select" class="form-control" required>
                                    <option value="">Selecciona una beca</option>
                                    @foreach ($catalogo as $beca)
                                        <option value="{{ $beca->id }}" data-tipo="{{ $beca->tipo }}"
                                            data-valor="{{ $beca->valor }}"
                                            {{ old('catalogo_beca_id') == $beca->id ? 'selected' : '' }}>
                                            {{ $beca->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('catalogo_beca_id')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="descuento-display">Descuento</label>
                                <input type="text" id="descuento-display" class="form-control bec-discount-preview" readonly
                                    placeholder="Selecciona una beca">
                            </div>
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                        <label for="plan_id">Plan de pagos <span class="text-red">*</span></label>
                        <select name="plan_id" id="plan_id" class="form-control" required>
                            <option value="">{{ $planes->isEmpty() ? 'Selecciona primero un alumno' : 'Selecciona un plan' }}</option>
                            @foreach ($planes as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->nombre }}{{ $plan->nivel ? ' · '.$plan->nivel->nombre : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="bec-help" id="plan-help" style="margin-top:6px;">
                            Solo se muestran los planes asignados al alumno seleccionado.
                        </p>
                        @error('plan_id')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="bec-section-title" style="margin-top:10px;">
                        <i class="fa fa-calendar"></i> Vigencia y motivo
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('vigencia_inicio') ? 'has-error' : '' }}">
                                <label for="vigencia_inicio">Inicio de vigencia <span class="text-red">*</span></label>
                                <input type="date" name="vigencia_inicio" id="vigencia_inicio" class="form-control"
                                    value="{{ old('vigencia_inicio', now()->format('Y-m-d')) }}" required>
                                @error('vigencia_inicio')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('vigencia_fin') ? 'has-error' : '' }}">
                                <label for="vigencia_fin">Fin de vigencia <span class="text-red">*</span></label>
                                <input type="date" name="vigencia_fin" id="vigencia_fin" class="form-control"
                                    value="{{ old('vigencia_fin') }}" required>
                                @error('vigencia_fin')
                                    <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group {{ $errors->has('motivo') ? 'has-error' : '' }}">
                        <label for="motivo">Motivo</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="2">{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <span class="help-block"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="bec-form-footer">
                    <div class="bec-action-bar">
                        <a href="{{ route('becas.index') }}" class="btn btn-default btn-flat"
                            style="border-radius:20px;padding:6px 16px;">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-flat"
                            style="border-radius:20px;padding:6px 18px;">
                            <i class="fa fa-save"></i> Guardar asignación
                        </button>
                    </div>
                </div>
            </div>

            <aside class="bec-side-panel">
                <div class="bec-side-header">
                    <h3 class="bec-form-title">
                        <i class="fa fa-id-card" style="color:#3c8dbc;"></i>
                        Becas activas del alumno
                    </h3>
                </div>

                <div class="bec-side-body">
                    <div id="info-becas-alumno">
                        <div class="bec-student-empty">
                            <i class="fa fa-user-circle"></i>
                            Selecciona un alumno para ver su estado de becas.
                        </div>
                    </div>
                </div>

                <div class="bec-side-footer">
                    <div class="bec-checkbox-card" id="panel-deshabilitar">
                        <label style="margin:0;font-weight:600;color:#4a5568;">
                            <input type="checkbox" name="deshabilitar_beca_anterior" value="1"
                                {{ old('deshabilitar_beca_anterior') ? 'checked' : '' }}>
                            Deshabilitar beca anterior si existe una activa para este alumno
                        </label>
                    </div>
                    <p class="bec-help" id="texto-becas-alumno">
                        Solo se permite una beca activa por alumno en el ciclo seleccionado.
                    </p>
                </div>
            </aside>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const becasAlumnoUrlTemplate = "{{ url('/becas/alumno') }}/:id/becas-activas";
        const planesAlumnoUrlTemplate = @json(route('planes.asignacion-alumno', ['alumnoId' => '__ALUMNO_ID__']));
        const selectedPlanId = @json((string) old('plan_id'));
        const alumnoSelect = document.getElementById('alumno-select');
        const planSelect = document.getElementById('plan_id');
        const planHelp = document.getElementById('plan-help');
        const catalogoBecaSelect = document.getElementById('catalogo-beca-select');
        const descuentoDisplay = document.getElementById('descuento-display');
        const infoPanel = document.getElementById('info-becas-alumno');

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function actualizarDescuentoDisplay() {
            const selectedOption = catalogoBecaSelect.options[catalogoBecaSelect.selectedIndex];

            if (!selectedOption.value) {
                descuentoDisplay.value = '';
                return;
            }

            const tipo = selectedOption.getAttribute('data-tipo');
            const valor = parseFloat(selectedOption.getAttribute('data-valor'));

            descuentoDisplay.value = tipo === 'porcentaje' ? valor + '%' : '$' + valor.toFixed(2);
        }

        function mostrarBecas(becas) {
            if (!becas.length) {
                infoPanel.innerHTML = `
                    <div class="bec-student-empty">
                        <i class="fa fa-check-circle"></i>
                        El alumno no tiene becas activas en el ciclo seleccionado.
                    </div>
                `;
                return;
            }

            const lista = becas.map((beca) => `
                <div class="bec-current-card">
                    <div class="bec-current-title">${escapeHtml(beca.nombre)}</div>
                    <div class="bec-current-meta">
                        Plan: ${escapeHtml(beca.destino)}
                    </div>
                    <div class="bec-current-meta">
                        Vigencia: ${escapeHtml(beca.vigencia_inicio)} ${beca.vigencia_fin ? 'a ' + escapeHtml(beca.vigencia_fin) : ''}
                    </div>
                </div>
            `).join('');

            infoPanel.innerHTML = `
                <div class="bec-current-alert">
                    <i class="fa fa-exclamation-triangle"></i>
                    El alumno tiene ${becas.length} beca(s) activa(s).
                </div>
                ${lista}
            `;
        }

        function cargarBecasAlumno(alumnoId) {
            if (!alumnoId) {
                infoPanel.innerHTML = `
                    <div class="bec-student-empty">
                        <i class="fa fa-user-circle"></i>
                        Selecciona un alumno para ver su estado de becas.
                    </div>
                `;
                return;
            }

            fetch(becasAlumnoUrlTemplate.replace(':id', alumnoId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then((response) => response.json())
                .then((data) => mostrarBecas(data.becas || []))
                .catch(() => {
                    infoPanel.innerHTML = `
                        <div class="bec-student-error">
                            <i class="fa fa-exclamation-circle"></i>
                            No se pudo cargar la información de becas.
                        </div>
                    `;
                });
        }

        function limpiarPlanes(mensaje = 'Selecciona primero un alumno') {
            planSelect.innerHTML = `<option value="">${escapeHtml(mensaje)}</option>`;
            planSelect.disabled = true;
        }

        function mostrarPlanes(asignaciones, planSeleccionado = '') {
            const planes = new Map();

            asignaciones.forEach((asignacion) => {
                if (asignacion.plan && asignacion.plan.id) {
                    planes.set(String(asignacion.plan.id), asignacion.plan);
                }
            });

            if (!planes.size) {
                limpiarPlanes('El alumno no tiene planes asignados');
                return;
            }

            planSelect.disabled = false;
            planSelect.innerHTML = '<option value="">Selecciona un plan</option>';

            Array.from(planes.values())
                .sort((a, b) => String(a.nombre).localeCompare(String(b.nombre)))
                .forEach((plan) => {
                    const option = document.createElement('option');
                    option.value = plan.id;
                    option.textContent = plan.nivel && plan.nivel.nombre
                        ? `${plan.nombre} · ${plan.nivel.nombre}`
                        : plan.nombre;
                    option.selected = String(plan.id) === String(planSeleccionado);
                    planSelect.appendChild(option);
                });
        }

        function cargarPlanesAlumno(alumnoId, planSeleccionado = '') {
            if (!alumnoId) {
                limpiarPlanes();
                return;
            }

            limpiarPlanes('Cargando planes...');

            fetch(planesAlumnoUrlTemplate.replace('__ALUMNO_ID__', alumnoId), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Sin planes asignados');
                    }

                    return response.json();
                })
                .then((data) => {
                    mostrarPlanes(data.asignaciones || [], planSeleccionado);
                })
                .catch(() => {
                    limpiarPlanes('El alumno no tiene planes asignados');
                    planHelp.textContent = 'Asigna primero un plan de pagos al alumno para poder registrar una beca.';
                });
        }

        catalogoBecaSelect.addEventListener('change', actualizarDescuentoDisplay);

        alumnoSelect.addEventListener('change', function () {
            cargarBecasAlumno(this.value);
            cargarPlanesAlumno(this.value);
            planHelp.textContent = 'Solo se muestran los planes asignados al alumno seleccionado.';
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (alumnoSelect.value) {
                cargarBecasAlumno(alumnoSelect.value);
                cargarPlanesAlumno(alumnoSelect.value, selectedPlanId);
            } else {
                limpiarPlanes();
            }

            actualizarDescuentoDisplay();
        });
    </script>
@endpush
