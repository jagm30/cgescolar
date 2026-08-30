@extends('layouts.master')

@section('page_title', 'Expediente médico')
@section('page_subtitle', $alumno->nombre_completo)

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li><a href="{{ route('portal.hijos') }}">Mis hijos</a></li>
    <li class="active">Expediente médico</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Tipografía y escala general ── */
        .exp-body { font-size: 15px; }

        /* ── Cabecera de sección ── */
        .exp-section-header {
            padding: 16px 16px 0;
        }
        .exp-section-title {
            font-size: 17px;
            font-weight: 800;
            color: #1a2634;
            margin: 0 0 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .exp-section-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .exp-section-desc {
            font-size: 13px;
            color: #7b8794;
            margin: 0 0 14px;
            padding-left: 46px;
        }
        .exp-divider {
            border: none;
            border-top: 1px solid #eef2f6;
            margin: 0 0 14px;
        }

        /* ── Campos de formulario ── */
        .exp-label {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 4px;
            display: block;
        }
        .exp-hint {
            font-size: 12px;
            color: #9aa5b4;
            font-weight: 400;
            margin-left: 4px;
        }
        .exp-control {
            font-size: 15px !important;
            height: 46px !important;
            border-radius: 8px !important;
        }
        textarea.exp-control {
            height: auto !important;
        }

        /* ── Botón principal de acción ── */
        .exp-btn-add {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 16px;
            border: 2px dashed;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            background: transparent;
            transition: background .15s, border-color .15s;
            margin-bottom: 16px;
        }
        .exp-btn-add-orange {
            border-color: #f39c12;
            color: #b45309;
        }
        .exp-btn-add-orange:hover { background: #fffbeb; }
        .exp-btn-add-purple {
            border-color: #8e44ad;
            color: #6b21a8;
        }
        .exp-btn-add-purple:hover { background: #faf5ff; }

        .exp-btn-add .exp-btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ── Botón guardar ── */
        .exp-btn-save {
            width: 100%;
            padding: 13px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
        }

        /* ── Tarjeta de ítem registrado ── */
        .exp-item-card {
            background: #fff;
            border: 1px solid #eef2f6;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
            position: relative;
        }
        .exp-item-title {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
            margin-bottom: 6px;
            padding-right: 36px;
        }
        .exp-item-meta {
            font-size: 13px;
            color: #6b7a8d;
            margin-top: 3px;
        }
        .exp-item-delete {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #fdecea;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            color: #b91c1c;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .exp-item-delete:hover { background: #fee2e2; }

        /* ── Panel del formulario inline ── */
        .exp-form-panel {
            background: #f8fafc;
            border: 2px solid #eef2f6;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }
        .exp-form-panel .form-group { margin-bottom: 12px; }
        .exp-form-panel .form-group:last-child { margin-bottom: 0; }

        /* ── Badges de riesgo ── */
        .exp-risk {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-right: 4px;
        }
        .exp-tag {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            background: #f0f3f7;
            color: #5a6a7a;
            margin-right: 4px;
        }

        /* ── Checkbox grande ── */
        .exp-checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            font-size: 14px;
            color: #2c3e50;
            font-weight: 600;
            line-height: 1.4;
        }
        .exp-checkbox-label input[type=checkbox] {
            width: 20px;
            height: 20px;
            margin-top: 1px;
            flex-shrink: 0;
            cursor: pointer;
        }

        /* ── Alerta de acción requerida ── */
        .exp-accion-alert {
            background: #fdecea;
            border-left: 4px solid #dd4b39;
            border-radius: 6px;
            padding: 10px 12px;
            margin-top: 8px;
        }
        .exp-accion-alert-title {
            font-size: 11px;
            font-weight: 800;
            color: #b91c1c;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 4px;
        }
        .exp-accion-alert-text { font-size: 13px; color: #7f1d1d; }

        /* ── Vacío ── */
        .exp-empty {
            text-align: center;
            padding: 24px 16px;
            color: #9aa5b4;
        }
        .exp-empty-icon { font-size: 36px; margin-bottom: 8px; }
        .exp-empty-text { font-size: 14px; }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .portal-card-header { flex-wrap: wrap; }
            .exp-section-desc { padding-left: 0; }
        }
    </style>
@endpush

@section('content')
<div class="exp-body">

    {{-- ── Cabecera ── --}}
    <div class="portal-hero" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div>
            <h3 style="font-size:18px;"><i class="fa fa-heartbeat" style="margin-right:8px;"></i>{{ $alumno->nombre_completo }}</h3>
            <p style="font-size:13px;">Matrícula {{ $alumno->matricula }} &nbsp;·&nbsp; Expediente médico</p>
        </div>
        <a href="{{ route('portal.hijos') }}"
           style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;
                  background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.45);
                  border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;">
            <i class="fa fa-arrow-left"></i> Regresar
        </a>
    </div>

    {{-- ── Aviso cuando la edición está deshabilitada ── --}}
    @unless ($portalEditarExpedienteHabilitado)
        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;
                    padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;
                    gap:10px;font-size:13px;color:#92400e;font-weight:600;">
            <i class="fa fa-lock" style="font-size:18px;flex-shrink:0;"></i>
            La edición del expediente médico está deshabilitada. Contacta a la escuela para realizar cambios.
        </div>
    @endunless

    {{-- ══════════════════════════════════════════
         SECCIÓN 1 — Datos generales de salud
    ══════════════════════════════════════════ --}}
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="exp-section-header">
            <div class="exp-section-title">
                <div class="exp-section-icon" style="background:#fdecea;color:#e74c3c;">
                    <i class="fa fa-user-md"></i>
                </div>
                Datos generales de salud
            </div>
            <p class="exp-section-desc">
                Información básica que necesita el personal de la escuela en caso de emergencia.
            </p>
        </div>
        <hr class="exp-divider">

        <form id="form-ficha" style="padding:0 16px 16px;">
            @csrf

            {{-- Tipo de sangre, peso y talla --}}
            <div class="row">
                <div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <label class="exp-label">
                            Tipo de sangre
                            <span class="exp-hint">(opcional)</span>
                        </label>
                        <select name="tipo_sangre" class="form-control exp-control">
                            <option value="">— No sé / No especificar —</option>
                            @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $tipo)
                                <option value="{{ $tipo }}" {{ $fm?->tipo_sangre === $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-4 col-xs-6">
                    <div class="form-group">
                        <label class="exp-label">Peso <span class="exp-hint">kg</span></label>
                        <input type="number" step="0.1" name="peso_kg" class="form-control exp-control"
                               value="{{ $fm?->peso_kg }}" placeholder="Ej: 35.5">
                    </div>
                </div>
                <div class="col-sm-4 col-xs-6">
                    <div class="form-group">
                        <label class="exp-label">Talla <span class="exp-hint">cm</span></label>
                        <input type="number" step="0.1" name="talla_cm" class="form-control exp-control"
                               value="{{ $fm?->talla_cm }}" placeholder="Ej: 130">
                    </div>
                </div>
            </div>

            {{-- Médico --}}
            <div class="row">
                <div class="col-sm-8 col-xs-12">
                    <div class="form-group">
                        <label class="exp-label">
                            Nombre del médico
                            <span class="exp-hint">(opcional)</span>
                        </label>
                        <input type="text" name="medico_nombre" class="form-control exp-control"
                               value="{{ $fm?->medico_nombre }}" placeholder="Dr. / Dra. Apellido">
                    </div>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div class="form-group">
                        <label class="exp-label">
                            Teléfono del médico
                            <span class="exp-hint">(opcional)</span>
                        </label>
                        <input type="text" name="medico_telefono" class="form-control exp-control"
                               value="{{ $fm?->medico_telefono }}" placeholder="10 dígitos">
                    </div>
                </div>
            </div>

            {{-- Hospital --}}
            <div class="form-group">
                <label class="exp-label">
                    Hospital de preferencia
                    <span class="exp-hint">(opcional)</span>
                </label>
                <input type="text" name="hospital_preferente" class="form-control exp-control"
                       value="{{ $fm?->hospital_preferente }}" placeholder="Nombre del hospital o clínica">
            </div>

            {{-- Discapacidad --}}
            <div class="form-group">
                <label class="exp-label">
                    ¿El alumno tiene alguna discapacidad?
                    <span class="exp-hint">(déjelo vacío si no aplica)</span>
                </label>
                <textarea name="discapacidad" class="form-control exp-control" rows="2"
                          placeholder="Describe brevemente la discapacidad si aplica">{{ $fm?->discapacidad }}</textarea>
            </div>

            {{-- Observaciones --}}
            <div class="form-group">
                <label class="exp-label">Observaciones para el personal escolar</label>
                <textarea name="observaciones_generales" class="form-control exp-control" rows="3"
                          placeholder="Ej: El alumno tiene migraña frecuente. En caso de crisis, permitirle descansar en un cuarto oscuro.">{{ $fm?->observaciones_generales }}</textarea>
            </div>

            @if ($portalEditarExpedienteHabilitado)
                <button type="button" class="exp-btn-save"
                        style="background:#e74c3c;color:#fff;"
                        onclick="guardarFicha()">
                    <i class="fa fa-check-circle" style="font-size:18px;"></i>
                    Guardar datos generales
                </button>
            @endif
        </form>
    </div>

    {{-- ══════════════════════════════════════════
         SECCIÓN 2 — Condiciones médicas
    ══════════════════════════════════════════ --}}
    <div class="portal-card" style="margin-bottom:16px;">
        <div class="exp-section-header">
            <div class="exp-section-title">
                <div class="exp-section-icon" style="background:#fff8e1;color:#e67e22;">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                Condiciones médicas
                @if ($alumno->condicionesMedicas->count() > 0)
                    <span style="background:#fff3cd;color:#856404;font-size:12px;font-weight:700;
                                 padding:2px 10px;border-radius:20px;">
                        {{ $alumno->condicionesMedicas->count() }} registrada(s)
                    </span>
                @endif
            </div>
            <p class="exp-section-desc">
                Alergias, enfermedades crónicas, o cualquier condición que el personal deba conocer.
            </p>
        </div>
        <hr class="exp-divider">

        <div style="padding:0 16px;">

            {{-- Ítems registrados --}}
            @foreach ($alumno->condicionesMedicas as $condicion)
                <div class="exp-item-card">
                    @if ($portalEditarExpedienteHabilitado)
                        <button type="button" class="exp-item-delete"
                                onclick="eliminarCondicion({{ $condicion->id }}, '{{ addslashes($condicion->nombre) }}')">
                            <i class="fa fa-trash-o"></i> Eliminar
                        </button>
                    @endif
                    <div class="exp-item-title">{{ $condicion->nombre }}</div>
                    <div>
                        <span class="exp-tag">{{ $condicion->tipoEtiqueta() }}</span>
                        <span class="exp-risk" style="color:#fff;background:{{ $condicion->colorRiesgo() }};">
                            {{ ucfirst($condicion->nivel_riesgo) }}
                        </span>
                    </div>
                    @if ($condicion->descripcion)
                        <div class="exp-item-meta" style="margin-top:6px;">{{ $condicion->descripcion }}</div>
                    @endif
                    @if ($condicion->requiere_accion && $condicion->accion_requerida)
                        <div class="exp-accion-alert">
                            <div class="exp-accion-alert-title"><i class="fa fa-bolt"></i> Acción requerida</div>
                            <div class="exp-accion-alert-text">{{ $condicion->accion_requerida }}</div>
                        </div>
                    @endif
                </div>
            @endforeach

            @if ($alumno->condicionesMedicas->isEmpty())
                <div class="exp-empty">
                    <div class="exp-empty-icon"><i class="fa fa-shield" style="color:#d1d9e0;"></i></div>
                    <div class="exp-empty-text">No hay condiciones registradas todavía.</div>
                </div>
            @endif

            {{-- Botón para abrir formulario --}}
            @if ($portalEditarExpedienteHabilitado)
                <button type="button" class="exp-btn-add exp-btn-add-orange"
                        id="btn-abrir-condicion"
                        onclick="abrirFormulario('form-condicion', 'btn-abrir-condicion')">
                    <div class="exp-btn-icon" style="background:#fff3cd;color:#e67e22;">
                        <i class="fa fa-plus"></i>
                    </div>
                    <span>Agregar condición médica</span>
                </button>
            @endif

            {{-- Formulario inline --}}
            <div id="form-condicion" style="display:none;">
                <div class="exp-form-panel">
                    <div style="font-size:15px;font-weight:700;color:#b45309;margin-bottom:14px;">
                        <i class="fa fa-exclamation-triangle" style="margin-right:6px;"></i>
                        Nueva condición médica
                    </div>
                    <form id="condicion-form">
                        @csrf
                        <div class="form-group">
                            <label class="exp-label">¿Qué tipo de condición es? <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-control exp-control" required>
                                <option value="">— Seleccionar tipo —</option>
                                <option value="padecimiento">Padecimiento (diabetes, epilepsia, asma…)</option>
                                <option value="alergia_alimento">Alergia a alimento</option>
                                <option value="alergia_medicamento">Alergia a medicamento</option>
                                <option value="alergia_ambiental">Alergia ambiental (polvo, polen…)</option>
                                <option value="discapacidad">Discapacidad</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="exp-label">Nombre o diagnóstico <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control exp-control" required
                                   placeholder="Ej: Asma, Alergia a cacahuate, Diabetes tipo 1">
                        </div>
                        <div class="form-group">
                            <label class="exp-label">¿Qué tan grave es? <span class="text-danger">*</span></label>
                            <select name="nivel_riesgo" class="form-control exp-control" required>
                                <option value="">— Seleccionar —</option>
                                <option value="leve">Leve — no pone en riesgo al alumno</option>
                                <option value="moderado">Moderado — requiere atención y seguimiento</option>
                                <option value="grave">Grave — puede complicarse rápidamente</option>
                                <option value="critico">Crítico — puede ser una emergencia</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="exp-label">
                                Descripción <span class="exp-hint">(opcional)</span>
                            </label>
                            <textarea name="descripcion" class="form-control exp-control" rows="2"
                                      placeholder="Agrega detalles que ayuden al personal a entender la condición"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="exp-checkbox-label">
                                <input type="checkbox" name="requiere_accion" id="req-accion" value="1"
                                       onchange="$('#grupo-accion').slideToggle(200)">
                                <span>El personal escolar debe hacer algo específico ante esta condición</span>
                            </label>
                        </div>
                        <div class="form-group" id="grupo-accion" style="display:none;">
                            <label class="exp-label" style="color:#b91c1c;">
                                <i class="fa fa-bolt"></i> ¿Qué debe hacer el personal? <span class="text-danger">*</span>
                            </label>
                            <textarea name="accion_requerida" class="form-control exp-control" rows="3"
                                      placeholder="Ej: Aplicar EpiPen inmediatamente y llamar al 911. El EpiPen está en la mochila del alumno."></textarea>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <button type="button" class="btn btn-default btn-flat"
                                    style="flex:1;min-width:120px;padding:12px;font-size:14px;"
                                    onclick="cerrarFormulario('form-condicion', 'btn-abrir-condicion')">
                                Cancelar
                            </button>
                            <button type="button" class="exp-btn-save"
                                    style="flex:2;min-width:160px;background:#f39c12;color:#fff;border-radius:8px;padding:12px;"
                                    onclick="guardarCondicion()">
                                <i class="fa fa-check-circle" style="font-size:17px;"></i>
                                Guardar condición
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════
         SECCIÓN 3 — Medicamentos autorizados (oculta)
    ══════════════════════════════════════════ --}}
    {{-- <div class="portal-card" style="margin-bottom:16px;display:none;"> --}}
    <div class="portal-card" style="margin-bottom:16px;display:none;">
        <div class="exp-section-header">
            <div class="exp-section-title">
                <div class="exp-section-icon" style="background:#f3e8fd;color:#8e44ad;">
                    <i class="fa fa-medkit"></i>
                </div>
                Medicamentos autorizados
                @if ($alumno->medicamentosAutorizados->count() > 0)
                    <span style="background:#f3e8fd;color:#6b21a8;font-size:12px;font-weight:700;
                                 padding:2px 10px;border-radius:20px;">
                        {{ $alumno->medicamentosAutorizados->count() }} registrado(s)
                    </span>
                @endif
            </div>
            <p class="exp-section-desc">
                Medicamentos que el personal escolar puede administrar al alumno con tu autorización.
            </p>
        </div>
        <hr class="exp-divider">

        <div style="padding:0 16px;">

            {{-- Ítems registrados --}}
            @foreach ($alumno->medicamentosAutorizados as $med)
                <div class="exp-item-card">
                    @if ($portalEditarExpedienteHabilitado)
                        <button type="button" class="exp-item-delete"
                                onclick="eliminarMedicamento({{ $med->id }}, '{{ addslashes($med->nombre_medicamento) }}')">
                            <i class="fa fa-trash-o"></i> Eliminar
                        </button>
                    @endif
                    <div class="exp-item-title">{{ $med->nombre_medicamento }}</div>
                    <div class="exp-item-meta">
                        <i class="fa fa-eyedropper" style="color:#8e44ad;margin-right:3px;"></i>
                        <strong>Dosis:</strong> {{ $med->dosis }}
                        &nbsp;·&nbsp;
                        <strong>Frecuencia:</strong> {{ $med->frecuencia }}
                        @if ($med->horario)
                            &nbsp;·&nbsp; {{ $med->horario }}
                        @endif
                    </div>
                    @if ($med->requiere_refrigeracion)
                        <div style="margin-top:6px;">
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;
                                         font-weight:600;color:#1565c0;background:#e3f2fd;
                                         border:1px solid #bbdefb;padding:3px 10px;border-radius:20px;">
                                <i class="fa fa-snowflake-o"></i> Requiere refrigeración
                            </span>
                        </div>
                    @endif
                    @if ($med->instrucciones)
                        <div class="exp-item-meta" style="margin-top:6px;font-style:italic;">
                            <i class="fa fa-info-circle" style="margin-right:3px;"></i>{{ $med->instrucciones }}
                        </div>
                    @endif
                    <div class="exp-item-meta" style="margin-top:6px;color:#9aa5b4;">
                        <i class="fa fa-user" style="margin-right:3px;"></i>
                        Autorizado por: <strong>{{ $med->contactoAutoriza?->nombre ?? '—' }}</strong>
                        @if ($med->vigencia_fin)
                            &nbsp;·&nbsp; Vigente hasta <strong>{{ $med->vigencia_fin->format('d/m/Y') }}</strong>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($alumno->medicamentosAutorizados->isEmpty())
                <div class="exp-empty">
                    <div class="exp-empty-icon"><i class="fa fa-medkit" style="color:#d1d9e0;"></i></div>
                    <div class="exp-empty-text">No hay medicamentos autorizados registrados todavía.</div>
                </div>
            @endif

            {{-- Botón para abrir formulario --}}
            @if ($portalEditarExpedienteHabilitado)
                <button type="button" class="exp-btn-add exp-btn-add-purple"
                        id="btn-abrir-medicamento"
                        onclick="abrirFormulario('form-medicamento', 'btn-abrir-medicamento')">
                    <div class="exp-btn-icon" style="background:#f3e8fd;color:#8e44ad;">
                        <i class="fa fa-plus"></i>
                    </div>
                    <span>Autorizar un medicamento</span>
                </button>
            @endif

            {{-- Formulario inline --}}
            <div id="form-medicamento" style="display:none;">
                <div class="exp-form-panel">
                    <div style="font-size:15px;font-weight:700;color:#6b21a8;margin-bottom:14px;">
                        <i class="fa fa-medkit" style="margin-right:6px;"></i>
                        Nuevo medicamento autorizado
                    </div>
                    <form id="medicamento-form">
                        @csrf
                        <div class="form-group">
                            <label class="exp-label">¿Quién autoriza este medicamento? <span class="text-danger">*</span></label>
                            <select name="autorizado_por_contacto" class="form-control exp-control" required>
                                <option value="">— Seleccionar familiar —</option>
                                @foreach ($contactosFamilia as $cf)
                                    <option value="{{ $cf->id }}">
                                        {{ trim("{$cf->nombre} {$cf->ap_paterno} {$cf->ap_materno}") }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="exp-label">Nombre del medicamento <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_medicamento" class="form-control exp-control" required
                                   placeholder="Ej: Salbutamol inhalador, Paracetamol 500mg">
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="exp-label">Dosis <span class="text-danger">*</span></label>
                                    <input type="text" name="dosis" class="form-control exp-control" required
                                           placeholder="Ej: 2 inhalaciones, 1 tableta">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="exp-label">¿Con qué frecuencia? <span class="text-danger">*</span></label>
                                    <input type="text" name="frecuencia" class="form-control exp-control" required
                                           placeholder="Ej: Cada 8 hrs, Solo en crisis">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="exp-label">Horario <span class="exp-hint">(opcional)</span></label>
                                    <input type="text" name="horario" class="form-control exp-control"
                                           placeholder="Ej: 12:00 pm con el lunch">
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <div class="form-group">
                                    <label class="exp-label">Autorización válida hasta <span class="exp-hint">(opcional)</span></label>
                                    <input type="date" name="vigencia_fin" class="form-control exp-control"
                                           min="{{ now()->addDay()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="exp-label">Instrucciones especiales <span class="exp-hint">(opcional)</span></label>
                            <textarea name="instrucciones" class="form-control exp-control" rows="2"
                                      placeholder="Ej: Darlo siempre con comida. Si vomita, no repetir dosis."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="exp-checkbox-label">
                                <input type="checkbox" name="requiere_refrigeracion" value="1">
                                <span>Este medicamento necesita refrigeración</span>
                            </label>
                        </div>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <button type="button" class="btn btn-default btn-flat"
                                    style="flex:1;min-width:120px;padding:12px;font-size:14px;"
                                    onclick="cerrarFormulario('form-medicamento', 'btn-abrir-medicamento')">
                                Cancelar
                            </button>
                            <button type="button" class="exp-btn-save"
                                    style="flex:2;min-width:160px;background:#8e44ad;color:#fff;border-radius:8px;padding:12px;"
                                    onclick="guardarMedicamento()">
                                <i class="fa fa-check-circle" style="font-size:17px;"></i>
                                Guardar medicamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Botón regresar final ── --}}
    <div style="margin-bottom:20px;">
        <a href="{{ route('portal.hijos') }}"
           style="display:inline-flex;align-items:center;gap:8px;padding:12px 20px;
                  background:#fff;border:1px solid #dde3ea;border-radius:8px;
                  color:#4a5568;font-size:14px;font-weight:600;text-decoration:none;">
            <i class="fa fa-arrow-left"></i> Regresar a Mis hijos
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    var urlFicha      = '{{ url("portal/hijos/" . $alumno->id . "/ficha-medica") }}';
    var urlCondicion  = '{{ url("portal/hijos/" . $alumno->id . "/condiciones-medicas") }}';
    var urlMedicament = '{{ url("portal/hijos/" . $alumno->id . "/medicamentos") }}';

    function abrirFormulario(formId, btnId) {
        $('#' + formId).slideDown(200);
        $('#' + btnId).hide();
    }

    function cerrarFormulario(formId, btnId) {
        $('#' + formId).slideUp(200);
        $('#' + btnId).show();
    }

    function toast(msg, tipo) {
        var bg = tipo === 'success' ? '#00875a' : '#b91c1c';
        var $t = $('<div>').css({
            position: 'fixed', bottom: '24px', right: '16px', left: '16px',
            background: bg, color: '#fff', padding: '14px 18px',
            borderRadius: '10px', zIndex: 9999, fontSize: '14px', fontWeight: '600',
            boxShadow: '0 4px 20px rgba(0,0,0,.22)', textAlign: 'center',
            maxWidth: '400px', margin: '0 auto',
        }).text(msg).appendTo('body');
        setTimeout(function () { $t.fadeOut(400, function () { $t.remove(); }); }, 3500);
    }

    function portalAjax(url, method, data, onSuccess) {
        $.ajax({
            url: url, type: method, data: data,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) { onSuccess(res); },
            error: function (xhr) {
                var msg = 'Error al procesar la solicitud.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.mensaje) msg = xhr.responseJSON.mensaje;
                    else if (xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors).flat()[0];
                }
                toast(msg, 'error');
            }
        });
    }

    function guardarFicha() {
        var form = document.getElementById('form-ficha');
        if (!form.reportValidity()) return;
        portalAjax(urlFicha, 'POST', $('#form-ficha').serialize(), function (res) {
            toast(res.mensaje, 'success');
        });
    }

    function guardarCondicion() {
        var form = document.getElementById('condicion-form');
        if (!form.reportValidity()) return;
        portalAjax(urlCondicion, 'POST', $('#condicion-form').serialize(), function (res) {
            toast(res.mensaje, 'success');
            setTimeout(function () { location.reload(); }, 1200);
        });
    }

    function guardarMedicamento() {
        var form = document.getElementById('medicamento-form');
        if (!form.reportValidity()) return;
        portalAjax(urlMedicament, 'POST', $('#medicamento-form').serialize(), function (res) {
            toast(res.mensaje, 'success');
            setTimeout(function () { location.reload(); }, 1200);
        });
    }

    function eliminarCondicion(id, nombre) {
        if (!confirm('¿Desea eliminar la condición "' + nombre + '"?')) return;
        portalAjax('{{ url("portal/condiciones-medicas") }}/' + id, 'DELETE', { _method: 'DELETE' }, function (res) {
            toast(res.mensaje, 'success');
            setTimeout(function () { location.reload(); }, 1000);
        });
    }

    function eliminarMedicamento(id, nombre) {
        if (!confirm('¿Desea eliminar el medicamento "' + nombre + '"?')) return;
        portalAjax('{{ url("portal/medicamentos") }}/' + id, 'DELETE', { _method: 'DELETE' }, function (res) {
            toast(res.mensaje, 'success');
            setTimeout(function () { location.reload(); }, 1000);
        });
    }
</script>
@endpush
