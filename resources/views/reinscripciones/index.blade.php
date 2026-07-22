@extends('layouts.master')

@section('page_title', 'Reinscripciones')
@section('page_subtitle', 'Inscribir alumnos existentes a un nuevo ciclo escolar')

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li class="active">Reinscripciones</li>
@endsection

@push('styles')
<style>
/* ════ LAYOUT ════ */
.ri-panel {
    border:1px solid #e4eaf0; border-radius:10px; background:#fff;
    box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; margin-bottom:20px;
}
.ri-panel-header {
    padding:11px 18px; background:#f8fafc; border-bottom:1px solid #e8ecf0;
    display:flex; align-items:center; gap:8px;
}
.ri-panel-title {
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:#6b7a8d;
}
.ri-label {
    font-size:11px; font-weight:700; color:#6b7a8d;
    text-transform:uppercase; letter-spacing:.04em;
    display:block; margin-bottom:5px;
}

/* ════ AUTOCOMPLETE ════ */
.ri-search-wrap { position:relative; }
.ri-dropdown {
    position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:999;
    background:#fff; border:1px solid #dde4eb; border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,.1); max-height:300px; overflow-y:auto;
    display:none;
}
.ri-dropdown-item {
    padding:10px 14px; cursor:pointer; border-bottom:1px solid #f0f3f7;
    display:flex; align-items:center; gap:12px;
}
.ri-dropdown-item:last-child { border-bottom:none; }
.ri-dropdown-item:hover { background:#f5f9ff; }
.ri-dropdown-empty { padding:16px; text-align:center; color:#b0bec5; font-size:13px; }

/* ════ TARJETA ALUMNO ════ */
.ri-alumno-card {
    display:none; padding:14px 18px;
    background:#f0f7ff; border:1px solid #d0e8fb;
    border-radius:8px; margin-bottom:16px;
}
.ri-alumno-avatar {
    width:44px; height:44px; border-radius:50%; background:#3c8dbc;
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:18px; font-weight:800; flex-shrink:0;
}

/* ════ CASCADA SELECT ════ */
.ri-select-disabled { opacity:.5; pointer-events:none; }

/* ════ ESTADO BADGE ════ */
.estado-badge {
    display:inline-block; font-size:10px; font-weight:700; padding:2px 7px;
    border-radius:6px; letter-spacing:.03em;
}

/* ════ MASIVA — TABLA DE ALUMNOS ════ */
.ri-alumno-row-bloqueado {
    opacity:.55; background:#f8f8f8 !important;
}
.ri-alumno-row-bloqueado td { color:#aaa; }
.ri-check-all-wrap {
    display:flex; align-items:center; gap:8px;
    padding:10px 14px 0; font-size:12px; color:#6b7a8d;
}

/* ════ TABS ════ */
.ri-tabs { border-bottom:2px solid #e4eaf0; margin-bottom:22px; }
.ri-tabs .nav-tabs { border:none; }
.ri-tabs .nav-tabs > li > a {
    border:none; border-radius:0; padding:10px 22px;
    font-size:12px; font-weight:700; color:#8a9ab0;
    text-transform:uppercase; letter-spacing:.06em;
    border-bottom:3px solid transparent; margin-bottom:-2px;
}
.ri-tabs .nav-tabs > li.active > a,
.ri-tabs .nav-tabs > li.active > a:hover {
    color:#3c8dbc; border-bottom-color:#3c8dbc;
    background:transparent;
}
.ri-tabs .nav-tabs > li > a:hover { color:#3c8dbc; background:transparent; }
</style>
@endpush

@section('content')

{{-- ══ ALERTAS ══ --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible" style="border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle" style="margin-right:6px;"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible" style="border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle" style="margin-right:6px;"></i>{{ session('error') }}
</div>
@endif

{{-- ══ TABS ══ --}}
<div class="ri-tabs">
    <ul class="nav nav-tabs" id="riMainTabs">
        <li class="active">
            <a href="#tab-individual" data-toggle="tab">
                <i class="fa fa-user" style="margin-right:6px;"></i>Individual
            </a>
        </li>
        <li>
            <a href="#tab-masiva" data-toggle="tab">
                <i class="fa fa-users" style="margin-right:6px;"></i>Masiva por grupo
            </a>
        </li>
    </ul>
</div>

<div class="tab-content">

    {{-- ══════════════════════════════════════════════════════════
         TAB 1 — INSCRIPCIÓN INDIVIDUAL (contenido original)
    ══════════════════════════════════════════════════════════ --}}
    <div class="tab-pane active" id="tab-individual">
        <div class="row">

            {{-- Columna izquierda: búsqueda y datos del alumno --}}
            <div class="col-md-5">

                <div class="ri-panel" style="overflow:visible;">
                    <div class="ri-panel-header" style="border-radius:10px 10px 0 0;overflow:hidden;">
                        <i class="fa fa-search" style="color:#3c8dbc;font-size:13px;"></i>
                        <span class="ri-panel-title">Buscar alumno</span>
                    </div>
                    <div style="padding:16px;">
                        <label class="ri-label">Nombre o matrícula</label>
                        <div class="ri-search-wrap">
                            <input type="text" id="buscarInput"
                                   class="form-control"
                                   placeholder="Escribe el nombre o matrícula del alumno..."
                                   autocomplete="off"
                                   style="border-radius:7px;border-color:#dde4eb;">
                            <div class="ri-dropdown" id="buscarDropdown"></div>
                        </div>
                        <p style="font-size:11px;color:#b0bec5;margin:8px 0 0;">
                            El alumno debe estar registrado previamente en el sistema.
                        </p>
                    </div>
                </div>

                <div class="ri-panel" id="alumnoPanel" style="display:none;">
                    <div class="ri-panel-header">
                        <i class="fa fa-user" style="color:#3c8dbc;font-size:13px;"></i>
                        <span class="ri-panel-title">Alumno seleccionado</span>
                        <button type="button" id="btnCambiarAlumno"
                                class="btn btn-xs btn-default btn-flat"
                                style="margin-left:auto;border-radius:5px;">
                            <i class="fa fa-times"></i> Cambiar
                        </button>
                    </div>
                    <div style="padding:16px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                            <div class="ri-alumno-avatar" id="alumnoAvatar">?</div>
                            <div>
                                <div id="alumnoNombre" style="font-size:15px;font-weight:700;color:#1a2634;"></div>
                                <div id="alumnoMatricula" style="font-size:12px;color:#8a9ab0;margin-top:2px;"></div>
                                <span id="alumnoEstadoBadge" class="estado-badge" style="margin-top:4px;"></span>
                            </div>
                        </div>
                        <div style="background:#f8fafc;border-radius:7px;padding:10px 14px;font-size:12px;">
                            <div style="color:#6b7a8d;font-weight:600;margin-bottom:6px;text-transform:uppercase;font-size:10px;letter-spacing:.05em;">
                                Inscripción actual
                            </div>
                            <div id="alumnoInscripcionActual" style="color:#4a5568;"></div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Columna derecha: formulario de reinscripción --}}
            <div class="col-md-7">
                <div class="ri-panel">
                    <div class="ri-panel-header">
                        <i class="fa fa-edit" style="color:#27ae60;font-size:13px;"></i>
                        <span class="ri-panel-title">Datos de la reinscripción</span>
                    </div>
                    <div style="padding:20px;">

                        <form method="POST" action="{{ route('reinscripciones.store') }}" id="reinscripcionForm">
                            @csrf

                            <input type="hidden" name="alumno_id" id="alumnoIdInput">

                            <div class="form-group">
                                <label class="ri-label">Ciclo escolar <span style="color:#e74c3c;">*</span></label>
                                <select name="ciclo_id" id="cicloSelect"
                                        class="form-control @error('ciclo_id') is-invalid @enderror"
                                        style="border-radius:7px;border-color:#dde4eb;" required>
                                    <option value="">— Selecciona el ciclo —</option>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}"
                                            {{ old('ciclo_id', $cicloActual?->id) == $ciclo->id ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }}
                                            @if($ciclo->estado === 'activo')
                                                (Ciclo activo)
                                            @elseif($ciclo->estado === 'configuracion')
                                                (En configuración)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('ciclo_id')
                                <div class="invalid-feedback" style="display:block;color:#e74c3c;font-size:12px;margin-top:4px;">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="ri-label">Grado <span style="color:#e74c3c;">*</span></label>
                                <select name="grado_id" id="gradoSelect"
                                        class="form-control ri-select-disabled @error('grado_id') is-invalid @enderror"
                                        style="border-radius:7px;border-color:#dde4eb;" required>
                                    <option value="">— Primero selecciona el ciclo —</option>
                                </select>
                                @error('grado_id')
                                <div class="invalid-feedback" style="display:block;color:#e74c3c;font-size:12px;margin-top:4px;">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="ri-label">Grupo <span style="color:#e74c3c;">*</span></label>
                                <select name="grupo_id" id="grupoSelect"
                                        class="form-control ri-select-disabled @error('grupo_id') is-invalid @enderror"
                                        style="border-radius:7px;border-color:#dde4eb;" required>
                                    <option value="">— Primero selecciona el grado —</option>
                                </select>
                                @error('grupo_id')
                                <div class="invalid-feedback" style="display:block;color:#e74c3c;font-size:12px;margin-top:4px;">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Bloqueo: alumno ya inscrito en el ciclo seleccionado --}}
                            <div id="avisoYaInscrito" style="display:none;
                                 background:#fce4e4;border:1px solid #e74c3c;border-radius:8px;
                                 padding:12px 16px;font-size:13px;color:#c0392b;margin-bottom:16px;">
                                <i class="fa fa-lock" style="margin-right:6px;"></i>
                                <span id="avisoYaInscritoTexto"></span>
                            </div>

                            <div id="avisoSinAlumno"
                                 style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;
                                        padding:12px 16px;font-size:13px;color:#b45309;margin-bottom:16px;">
                                <i class="fa fa-info-circle" style="margin-right:6px;"></i>
                                Selecciona primero un alumno usando el buscador de la izquierda.
                            </div>

                            <div style="display:flex;gap:10px;align-items:center;margin-top:4px;">
                                <button type="submit" id="btnSubmit"
                                        class="btn btn-success btn-flat"
                                        style="border-radius:7px;font-weight:700;padding:8px 22px;"
                                        disabled>
                                    <i class="fa fa-check"></i> Reinscribir alumno
                                </button>
                                <a href="{{ route('alumnos.index') }}" class="btn btn-default btn-flat" style="border-radius:7px;">
                                    Cancelar
                                </a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>{{-- /tab-individual --}}


    {{-- ══════════════════════════════════════════════════════════
         TAB 2 — INSCRIPCIÓN MASIVA POR GRUPO
    ══════════════════════════════════════════════════════════ --}}
    <div class="tab-pane" id="tab-masiva">

        <div class="row">

            {{-- ── Panel izquierdo: grupo de ORIGEN ── --}}
            <div class="col-md-5">
                <div class="ri-panel">
                    <div class="ri-panel-header">
                        <i class="fa fa-history" style="color:#e67e22;font-size:13px;"></i>
                        <span class="ri-panel-title">Grupo de origen (ciclo anterior)</span>
                    </div>
                    <div style="padding:16px;">

                        <div class="form-group">
                            <label class="ri-label">Ciclo anterior</label>
                            <select id="mCicloOrigenSelect" class="form-control" style="border-radius:7px;border-color:#dde4eb;">
                                <option value="">— Selecciona el ciclo —</option>
                                @foreach($ciclosTodos as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->nombre }}
                                        @if($c->estado === 'activo') (Activo)
                                        @elseif($c->estado === 'configuracion') (En configuración)
                                        @elseif($c->estado === 'cerrado') (Cerrado)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="ri-label">Grado</label>
                            <select id="mGradoOrigenSelect" class="form-control ri-select-disabled" style="border-radius:7px;border-color:#dde4eb;">
                                <option value="">— Primero selecciona el ciclo —</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="ri-label">Grupo</label>
                            <select id="mGrupoOrigenSelect" class="form-control ri-select-disabled" style="border-radius:7px;border-color:#dde4eb;">
                                <option value="">— Primero selecciona el grado —</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Panel derecho: grupo de DESTINO ── --}}
            <div class="col-md-7">
                <div class="ri-panel">
                    <div class="ri-panel-header">
                        <i class="fa fa-sign-in" style="color:#27ae60;font-size:13px;"></i>
                        <span class="ri-panel-title">Reinscribir a (ciclo actual)</span>
                    </div>
                    <div style="padding:16px;">

                        <div class="form-group">
                            <label class="ri-label">Ciclo de destino <span style="color:#e74c3c;">*</span></label>
                            <select id="mCicloDestinoSelect" class="form-control" style="border-radius:7px;border-color:#dde4eb;">
                                <option value="">— Selecciona el ciclo —</option>
                                @foreach($ciclos as $c)
                                    <option value="{{ $c->id }}" {{ $cicloActual?->id == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                        @if($c->estado === 'activo') (Ciclo activo)
                                        @elseif($c->estado === 'configuracion') (En configuración)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="ri-label">Grado <span style="color:#e74c3c;">*</span></label>
                            <select id="mGradoDestinoSelect" class="form-control ri-select-disabled" style="border-radius:7px;border-color:#dde4eb;">
                                <option value="">— Primero selecciona el ciclo —</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="ri-label">Grupo destino <span style="color:#e74c3c;">*</span></label>
                            <select id="mGrupoDestinoSelect" class="form-control ri-select-disabled" style="border-radius:7px;border-color:#dde4eb;">
                                <option value="">— Primero selecciona el grado —</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

        </div>{{-- /row filtros --}}

        {{-- ── Aviso: faltan selecciones ── --}}
        <div id="mAvisoFiltros"
             style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;
                    padding:12px 16px;font-size:13px;color:#b45309;margin-bottom:20px;">
            <i class="fa fa-info-circle" style="margin-right:6px;"></i>
            Selecciona el <strong>grupo de origen</strong> y el <strong>ciclo de destino</strong> para cargar la lista de alumnos.
        </div>

        {{-- ── Tabla de alumnos ── --}}
        <div id="mAlumnosPanel" style="display:none;">
            <div class="ri-panel">
                <div class="ri-panel-header">
                    <i class="fa fa-list" style="color:#3c8dbc;font-size:13px;"></i>
                    <span class="ri-panel-title" id="mAlumnosPanelTitle">Alumnos del grupo</span>
                    <span id="mContadorBadge"
                          style="margin-left:auto;background:#3c8dbc;color:#fff;font-size:10px;
                                 font-weight:700;padding:2px 9px;border-radius:10px;"></span>
                </div>

                {{-- Cabecera: select all + leyenda --}}
                <div style="padding:12px 18px 0;display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <label style="margin:0;display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;cursor:pointer;">
                        <input type="checkbox" id="mSelectAll" style="width:16px;height:16px;">
                        Seleccionar todos los disponibles
                    </label>
                    <span style="font-size:11px;color:#8a9ab0;">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e74c3c;margin-right:4px;"></span>
                        Bloqueado (ya inscrito en el ciclo destino)
                    </span>
                </div>

                <div style="padding:12px 0 0;">
                    <table class="table table-hover" style="margin-bottom:0;">
                        <thead>
                            <tr style="background:#f8fafc;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#6b7a8d;">
                                <th style="width:40px;padding:10px 18px;"></th>
                                <th style="padding:10px 8px;">Alumno</th>
                                <th style="padding:10px 8px;">Matrícula</th>
                                <th style="padding:10px 8px;">Estado</th>
                                <th style="padding:10px 8px;">Inscripción destino</th>
                            </tr>
                        </thead>
                        <tbody id="mAlumnosTbody"></tbody>
                    </table>
                </div>

                <div style="padding:16px 18px;border-top:1px solid #f0f3f7;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <button type="button" id="mBtnSubmit"
                            class="btn btn-success btn-flat"
                            style="border-radius:7px;font-weight:700;padding:8px 22px;"
                            disabled>
                        <i class="fa fa-check-circle"></i> Reinscribir seleccionados
                    </button>
                    <span id="mSeleccionadosLabel" style="font-size:12px;color:#8a9ab0;"></span>
                </div>

            </div>
        </div>{{-- /mAlumnosPanel --}}

        {{-- ── Resultado de la reinscripción masiva ── --}}
        <div id="mResultPanel" style="display:none;"></div>

    </div>{{-- /tab-masiva --}}

</div>{{-- /tab-content --}}

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ═══════════════════════════════════════════════════════
       TAB INDIVIDUAL — lógica existente
    ═══════════════════════════════════════════════════════ */
    const buscarInput    = document.getElementById('buscarInput');
    const dropdown       = document.getElementById('buscarDropdown');
    const alumnoPanel    = document.getElementById('alumnoPanel');
    const alumnoIdInput  = document.getElementById('alumnoIdInput');
    const alumnoNombre   = document.getElementById('alumnoNombre');
    const alumnoMatricula= document.getElementById('alumnoMatricula');
    const alumnoEstado   = document.getElementById('alumnoEstadoBadge');
    const alumnoAvatar   = document.getElementById('alumnoAvatar');
    const alumnoInsc     = document.getElementById('alumnoInscripcionActual');
    const btnCambiar     = document.getElementById('btnCambiarAlumno');
    const avisoSin       = document.getElementById('avisoSinAlumno');
    const btnSubmit      = document.getElementById('btnSubmit');
    const cicloSelect    = document.getElementById('cicloSelect');
    const gradoSelect    = document.getElementById('gradoSelect');
    const grupoSelect    = document.getElementById('grupoSelect');

    const avisoYaInscrito      = document.getElementById('avisoYaInscrito');
    const avisoYaInscritoTexto = document.getElementById('avisoYaInscritoTexto');

    const preselectGradoId = '{{ old('grado_id') }}';
    const preselectGrupoId = '{{ old('grupo_id') }}';

    let searchTimer      = null;
    let alumnoActual     = null;   // objeto completo del alumno seleccionado

    buscarInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        if (q.length < 2) { cerrarDropdown(); return; }

        searchTimer = setTimeout(() => {
            fetch(`{{ route('reinscripciones.buscar') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(alumnos => renderDropdown(alumnos))
            .catch(() => cerrarDropdown());
        }, 280);
    });

    document.addEventListener('click', function (e) {
        if (!buscarInput.contains(e.target) && !dropdown.contains(e.target)) {
            cerrarDropdown();
        }
    });

    function renderDropdown(alumnos) {
        if (!alumnos.length) {
            dropdown.innerHTML = '<div class="ri-dropdown-empty"><i class="fa fa-search"></i> Sin resultados</div>';
            dropdown.style.display = 'block';
            return;
        }

        const estadoStyle = {
            'activo':           { bg:'#e8f8f0', color:'#00875a', label:'Activo' },
            'baja_temporal':    { bg:'#fff8e1', color:'#b45309', label:'Baja temporal' },
            'baja_definitiva':  { bg:'#fce4e4', color:'#c0392b', label:'Baja definitiva' },
            'egresado':         { bg:'#f3e8fd', color:'#7c3aed', label:'Egresado' },
        };

        dropdown.innerHTML = alumnos.map(a => {
            const est = estadoStyle[a.estado] || { bg:'#f0f3f7', color:'#6b7a8d', label: a.estado };
            const insc = a.inscripcion_actual
                ? `${a.inscripcion_actual.ciclo} · ${a.inscripcion_actual.grupo}`
                : 'Sin inscripción activa';
            return `
            <div class="ri-dropdown-item" data-alumno='${JSON.stringify(a)}'>
                <div style="width:36px;height:36px;border-radius:50%;background:#3c8dbc;
                            display:flex;align-items:center;justify-content:center;
                            color:#fff;font-size:14px;font-weight:800;flex-shrink:0;">
                    ${a.nombre_completo.charAt(0)}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;color:#1a2634;font-size:13px;">${a.nombre_completo}</div>
                    <div style="font-size:11px;color:#8a9ab0;margin-top:2px;">${a.matricula} · ${insc}</div>
                </div>
                <span style="background:${est.bg};color:${est.color};font-size:10px;font-weight:700;
                             padding:2px 7px;border-radius:6px;white-space:nowrap;">${est.label}</span>
            </div>`;
        }).join('');
        dropdown.style.display = 'block';

        dropdown.querySelectorAll('.ri-dropdown-item').forEach(item => {
            item.addEventListener('click', function () {
                seleccionarAlumno(JSON.parse(this.dataset.alumno));
            });
        });
    }

    function cerrarDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
    }

    function seleccionarAlumno(a) {
        cerrarDropdown();
        buscarInput.value = '';

        const estadoStyle = {
            'activo':           { bg:'#e8f8f0', color:'#00875a', label:'Activo' },
            'baja_temporal':    { bg:'#fff8e1', color:'#b45309', label:'Baja temporal' },
            'baja_definitiva':  { bg:'#fce4e4', color:'#c0392b', label:'Baja definitiva' },
            'egresado':         { bg:'#f3e8fd', color:'#7c3aed', label:'Egresado' },
        };
        const est = estadoStyle[a.estado] || { bg:'#f0f3f7', color:'#6b7a8d', label: a.estado };

        alumnoIdInput.value       = a.id;
        alumnoNombre.textContent  = a.nombre_completo;
        alumnoMatricula.textContent = a.matricula;
        alumnoAvatar.textContent  = a.nombre_completo.charAt(0);
        alumnoEstado.textContent  = est.label;
        alumnoEstado.style.background = est.bg;
        alumnoEstado.style.color      = est.color;

        alumnoInsc.textContent = a.inscripcion_actual
            ? `${a.inscripcion_actual.ciclo} · ${a.inscripcion_actual.grupo}`
            : 'Sin inscripción activa en el sistema';

        alumnoActual = a;
        alumnoPanel.style.display = 'block';
        avisoSin.style.display    = 'none';
        verificarInscripcionEnCiclo();
    }

    btnCambiar.addEventListener('click', function () {
        alumnoActual        = null;
        alumnoIdInput.value = '';
        alumnoPanel.style.display      = 'none';
        avisoYaInscrito.style.display  = 'none';
        avisoSin.style.display         = 'block';
        buscarInput.value = '';
        buscarInput.focus();
        inhabilitarSubmit();
    });

    /** Revisa si el alumno ya tiene inscripción activa en el ciclo seleccionado. */
    function verificarInscripcionEnCiclo() {
        avisoYaInscrito.style.display = 'none';

        if (! alumnoActual || ! cicloSelect.value) {
            habilitarSubmit();
            return;
        }

        const cicloId  = String(cicloSelect.value);
        const match    = (alumnoActual.ciclos_inscritos || [])
            .find(i => String(i.ciclo_id) === cicloId);

        if (match) {
            const grupoNombre = match.grupo ?? 'un grupo';
            avisoYaInscritoTexto.textContent =
                `${alumnoActual.nombre_completo} ya está inscrito en ${grupoNombre} para este ciclo. ` +
                `No se puede realizar una nueva inscripción.`;
            avisoYaInscrito.style.display = 'block';
            inhabilitarSubmit();
        } else {
            habilitarSubmit();
        }
    }

    cicloSelect.addEventListener('change', function () {
        const cicloId = this.value;

        resetSelect(gradoSelect, '— Selecciona el grado —');
        resetSelect(grupoSelect, '— Primero selecciona el grado —');
        grupoSelect.classList.add('ri-select-disabled');
        verificarInscripcionEnCiclo();

        if (!cicloId) {
            gradoSelect.classList.add('ri-select-disabled');
            return;
        }

        fetch(`{{ route('grupos.gradosPorCiclo') }}?ciclo_id=${cicloId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(grados => {
            gradoSelect.innerHTML = '<option value="">— Selecciona el grado —</option>'
                + grados.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            gradoSelect.classList.remove('ri-select-disabled');
            if (preselectGradoId) {
                gradoSelect.value = preselectGradoId;
                if (gradoSelect.value) gradoSelect.dispatchEvent(new Event('change'));
            }
        });
    });

    gradoSelect.addEventListener('change', function () {
        const gradoId = this.value;
        const cicloId = cicloSelect.value;

        resetSelect(grupoSelect, '— Selecciona el grupo —');

        if (!gradoId || !cicloId) {
            grupoSelect.classList.add('ri-select-disabled');
            return;
        }

        fetch(`{{ route('grupos.gruposPorCicloGrado') }}?ciclo_id=${cicloId}&grado_id=${gradoId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(grupos => {
            if (!grupos.length) {
                grupoSelect.innerHTML = '<option value="">Sin grupos disponibles para este ciclo y grado</option>';
                grupoSelect.classList.add('ri-select-disabled');
                return;
            }
            grupoSelect.innerHTML = '<option value="">— Selecciona el grupo —</option>'
                + grupos.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            grupoSelect.classList.remove('ri-select-disabled');
            if (preselectGrupoId) grupoSelect.value = preselectGrupoId;
        });
    });

    function resetSelect(sel, placeholder) {
        sel.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function habilitarSubmit() {
        if (alumnoIdInput.value && avisoYaInscrito.style.display === 'none') {
            btnSubmit.disabled = false;
        }
    }

    function inhabilitarSubmit() {
        btnSubmit.disabled = true;
    }

    if (cicloSelect.value) cicloSelect.dispatchEvent(new Event('change'));

    document.getElementById('reinscripcionForm').addEventListener('submit', function (e) {
        if (!alumnoIdInput.value) {
            e.preventDefault();
            buscarInput.focus();
            avisoSin.style.background  = '#fce4e4';
            avisoSin.style.borderColor = '#e74c3c';
            avisoSin.style.color       = '#c0392b';
            return;
        }

        if (avisoYaInscrito.style.display !== 'none') {
            e.preventDefault();
            avisoYaInscrito.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });


    /* ═══════════════════════════════════════════════════════
       TAB MASIVA — lógica de inscripción por grupo
    ═══════════════════════════════════════════════════════ */
    const mCicloOrigen    = document.getElementById('mCicloOrigenSelect');
    const mGradoOrigen    = document.getElementById('mGradoOrigenSelect');
    const mGrupoOrigen    = document.getElementById('mGrupoOrigenSelect');
    const mCicloDest      = document.getElementById('mCicloDestinoSelect');
    const mGradoDest      = document.getElementById('mGradoDestinoSelect');
    const mGrupoDest      = document.getElementById('mGrupoDestinoSelect');
    const mAvisoFiltros   = document.getElementById('mAvisoFiltros');
    const mAlumnosPanel   = document.getElementById('mAlumnosPanel');
    const mAlumnosTbody   = document.getElementById('mAlumnosTbody');
    const mSelectAll      = document.getElementById('mSelectAll');
    const mBtnSubmit      = document.getElementById('mBtnSubmit');
    const mSelLabel       = document.getElementById('mSeleccionadosLabel');
    const mContadorBadge  = document.getElementById('mContadorBadge');
    const mPanelTitle     = document.getElementById('mAlumnosPanelTitle');
    const mResultPanel    = document.getElementById('mResultPanel');

    const estadoStyle = {
        'activo':           { bg:'#e8f8f0', color:'#00875a', label:'Activo' },
        'baja_temporal':    { bg:'#fff8e1', color:'#b45309', label:'Baja temporal' },
        'baja_definitiva':  { bg:'#fce4e4', color:'#c0392b', label:'Baja definitiva' },
        'egresado':         { bg:'#f3e8fd', color:'#7c3aed', label:'Egresado' },
    };

    /* ── Cascada ORIGEN ── */
    mCicloOrigen.addEventListener('change', function () {
        const cicloId = this.value;
        resetSelect(mGradoOrigen, '— Primero selecciona el ciclo —');
        resetSelect(mGrupoOrigen, '— Primero selecciona el grado —');
        mGradoOrigen.classList.add('ri-select-disabled');
        mGrupoOrigen.classList.add('ri-select-disabled');
        ocultarTablaAlumnos();

        if (!cicloId) return;

        fetch(`{{ route('grupos.gradosPorCiclo') }}?ciclo_id=${cicloId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(grados => {
            mGradoOrigen.innerHTML = '<option value="">— Selecciona el grado —</option>'
                + grados.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            mGradoOrigen.classList.remove('ri-select-disabled');
        });
    });

    mGradoOrigen.addEventListener('change', function () {
        const gradoId = this.value;
        const cicloId = mCicloOrigen.value;
        resetSelect(mGrupoOrigen, '— Selecciona el grupo —');
        mGrupoOrigen.classList.add('ri-select-disabled');
        ocultarTablaAlumnos();

        if (!gradoId || !cicloId) return;

        fetch(`{{ route('grupos.gruposPorCicloGrado') }}?ciclo_id=${cicloId}&grado_id=${gradoId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(grupos => {
            if (!grupos.length) {
                mGrupoOrigen.innerHTML = '<option value="">Sin grupos en este ciclo y grado</option>';
                return;
            }
            mGrupoOrigen.innerHTML = '<option value="">— Selecciona el grupo —</option>'
                + grupos.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            mGrupoOrigen.classList.remove('ri-select-disabled');
        });
    });

    mGrupoOrigen.addEventListener('change', function () {
        ocultarTablaAlumnos();
        intentarCargarAlumnos();
    });

    /* ── Cascada DESTINO ── */
    mCicloDest.addEventListener('change', function () {
        const cicloId = this.value;
        resetSelect(mGradoDest, '— Primero selecciona el ciclo —');
        resetSelect(mGrupoDest, '— Primero selecciona el grado —');
        mGradoDest.classList.add('ri-select-disabled');
        mGrupoDest.classList.add('ri-select-disabled');
        ocultarTablaAlumnos();

        if (!cicloId) return;

        fetch(`{{ route('grupos.gradosPorCiclo') }}?ciclo_id=${cicloId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(grados => {
            mGradoDest.innerHTML = '<option value="">— Selecciona el grado —</option>'
                + grados.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            mGradoDest.classList.remove('ri-select-disabled');
        });

        // Si ya hay grupo origen seleccionado, recargar con nuevo destino
        if (mGrupoOrigen.value) intentarCargarAlumnos();
    });

    mGradoDest.addEventListener('change', function () {
        const gradoId = this.value;
        const cicloId = mCicloDest.value;
        resetSelect(mGrupoDest, '— Selecciona el grupo —');
        mGrupoDest.classList.add('ri-select-disabled');

        if (!gradoId || !cicloId) return;

        fetch(`{{ route('grupos.gruposPorCicloGrado') }}?ciclo_id=${cicloId}&grado_id=${gradoId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(grupos => {
            if (!grupos.length) {
                mGrupoDest.innerHTML = '<option value="">Sin grupos en este ciclo y grado</option>';
                return;
            }
            mGrupoDest.innerHTML = '<option value="">— Selecciona el grupo —</option>'
                + grupos.map(g => `<option value="${g.id}">${g.label}</option>`).join('');
            mGrupoDest.classList.remove('ri-select-disabled');
        });
    });

    /* ── Cargar alumnos ── */
    function intentarCargarAlumnos() {
        const grupoOrigenId  = mGrupoOrigen.value;
        const cicloDestinoId = mCicloDest.value;

        if (!grupoOrigenId || !cicloDestinoId) {
            mAvisoFiltros.style.display = 'block';
            return;
        }

        mAvisoFiltros.style.display = 'none';
        mAlumnosTbody.innerHTML = `
            <tr><td colspan="5" style="text-align:center;padding:20px;color:#8a9ab0;">
                <i class="fa fa-spinner fa-spin"></i> Cargando alumnos…
            </td></tr>`;
        mAlumnosPanel.style.display = 'block';
        mResultPanel.style.display  = 'none';
        mBtnSubmit.disabled = true;
        mSelectAll.checked  = false;

        fetch(`{{ route('reinscripciones.alumnosPorGrupo') }}?grupo_origen_id=${grupoOrigenId}&ciclo_destino_id=${cicloDestinoId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(alumnos => renderTablaAlumnos(alumnos))
        .catch(() => {
            mAlumnosTbody.innerHTML = `
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#e74c3c;">
                    <i class="fa fa-exclamation-circle"></i> Error al cargar alumnos.
                </td></tr>`;
        });
    }

    function renderTablaAlumnos(alumnos) {
        if (!alumnos.length) {
            mAlumnosTbody.innerHTML = `
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#8a9ab0;">
                    <i class="fa fa-inbox"></i> No se encontraron alumnos en este grupo.
                </td></tr>`;
            mContadorBadge.textContent = '0 alumnos';
            mPanelTitle.textContent    = 'Alumnos del grupo';
            actualizarContadorSeleccionados();
            return;
        }

        const total    = alumnos.length;
        const libres   = alumnos.filter(a => !a.ya_inscrito).length;
        mContadorBadge.textContent = `${total} alumno${total !== 1 ? 's' : ''}`;
        mPanelTitle.textContent    = `Alumnos del grupo (${libres} disponibles)`;

        mAlumnosTbody.innerHTML = alumnos.map(a => {
            const est     = estadoStyle[a.estado] || { bg:'#f0f3f7', color:'#6b7a8d', label: a.estado };
            const bloq    = a.ya_inscrito;
            const rowCls  = bloq ? 'ri-alumno-row-bloqueado' : '';
            const checkEl = bloq
                ? `<input type="checkbox" disabled title="Ya inscrito en el ciclo de destino">`
                : `<input type="checkbox" class="m-alumno-check" value="${a.id}" style="width:15px;height:15px;">`;
            const inscBadge = bloq
                ? `<span style="background:#fce4e4;color:#c0392b;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;">
                       <i class="fa fa-lock" style="margin-right:3px;"></i>Ya inscrito
                   </span>`
                : `<span style="background:#e8f8f0;color:#00875a;font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;">
                       Disponible
                   </span>`;

            return `
            <tr class="${rowCls}">
                <td style="padding:10px 18px;">${checkEl}</td>
                <td style="padding:10px 8px;font-weight:600;color:#1a2634;font-size:13px;">${a.nombre_completo}</td>
                <td style="padding:10px 8px;font-size:12px;color:#8a9ab0;">${a.matricula}</td>
                <td style="padding:10px 8px;">
                    <span class="estado-badge" style="background:${est.bg};color:${est.color};">${est.label}</span>
                </td>
                <td style="padding:10px 8px;">${inscBadge}</td>
            </tr>`;
        }).join('');

        // Escuchar cambios en checkboxes individuales
        document.querySelectorAll('.m-alumno-check').forEach(cb => {
            cb.addEventListener('change', actualizarContadorSeleccionados);
        });

        actualizarContadorSeleccionados();
    }

    function ocultarTablaAlumnos() {
        mAlumnosPanel.style.display = 'none';
        mResultPanel.style.display  = 'none';
        mAvisoFiltros.style.display = 'block';
        mBtnSubmit.disabled = true;
        mSelectAll.checked  = false;
    }

    /* ── Select all ── */
    mSelectAll.addEventListener('change', function () {
        document.querySelectorAll('.m-alumno-check').forEach(cb => {
            cb.checked = this.checked;
        });
        actualizarContadorSeleccionados();
    });

    function actualizarContadorSeleccionados() {
        const checks = document.querySelectorAll('.m-alumno-check');
        const selected = document.querySelectorAll('.m-alumno-check:checked');
        mBtnSubmit.disabled = selected.length === 0 || !mGrupoDest.value;

        if (checks.length === 0) {
            mSelLabel.textContent = '';
            return;
        }
        mSelLabel.textContent = `${selected.length} de ${checks.length} seleccionados`;
        mSelectAll.checked    = selected.length === checks.length && checks.length > 0;
        mSelectAll.indeterminate = selected.length > 0 && selected.length < checks.length;
    }

    /* ── Vigilar selección de grupo destino para actualizar botón ── */
    mGrupoDest.addEventListener('change', actualizarContadorSeleccionados);

    /* ── Envío masivo ── */
    mBtnSubmit.addEventListener('click', function () {
        const alumnoIds = Array.from(document.querySelectorAll('.m-alumno-check:checked'))
            .map(cb => cb.value);

        if (!alumnoIds.length) return;

        const grupoDestinoId  = mGrupoDest.value;
        const cicloDestinoId  = mCicloDest.value;
        const gradoDestinoId  = mGradoDest.value;

        if (!grupoDestinoId || !cicloDestinoId || !gradoDestinoId) {
            alert('Debes seleccionar el ciclo, grado y grupo de destino antes de continuar.');
            return;
        }

        mBtnSubmit.disabled   = true;
        mBtnSubmit.innerHTML  = '<i class="fa fa-spinner fa-spin"></i> Procesando…';

        const body = new URLSearchParams();
        body.append('_token', document.querySelector('meta[name="csrf-token"]')?.content
            || '{{ csrf_token() }}');
        body.append('ciclo_id', cicloDestinoId);
        body.append('grado_id', gradoDestinoId);
        body.append('grupo_id', grupoDestinoId);
        alumnoIds.forEach(id => body.append('alumno_ids[]', id));

        fetch('{{ route('reinscripciones.storeMasiva') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: body.toString(),
        })
        .then(r => r.json())
        .then(data => {
            mBtnSubmit.innerHTML = '<i class="fa fa-check-circle"></i> Reinscribir seleccionados';

            if (data.message) {
                // Error de validación devuelto como JSON 422
                mResultPanel.innerHTML = `
                    <div class="alert alert-danger alert-dismissible" style="border-radius:8px;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fa fa-exclamation-circle" style="margin-right:6px;"></i>${data.message}
                    </div>`;
                mResultPanel.style.display = 'block';
                mBtnSubmit.disabled = false;
                return;
            }

            const msj = data.inscritos > 0
                ? `<strong>${data.inscritos} alumno${data.inscritos !== 1 ? 's' : ''} reinscrito${data.inscritos !== 1 ? 's' : ''}</strong>
                   correctamente al grupo <strong>${data.grupo}</strong> del ciclo <strong>${data.ciclo}</strong>.`
                : 'No se inscribió ningún alumno.';

            const omitidosMsj = data.omitidos > 0
                ? `<br><small style="color:#b45309;">${data.omitidos} omitido${data.omitidos !== 1 ? 's' : ''} (ya inscritos o no encontrados).</small>`
                : '';

            mResultPanel.innerHTML = `
                <div class="alert alert-${data.inscritos > 0 ? 'success' : 'warning'} alert-dismissible" style="border-radius:8px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-${data.inscritos > 0 ? 'check-circle' : 'info-circle'}" style="margin-right:6px;"></i>
                    ${msj}${omitidosMsj}
                </div>`;
            mResultPanel.style.display = 'block';

            // Recargar lista para reflejar los ahora bloqueados
            if (data.inscritos > 0) intentarCargarAlumnos();
        })
        .catch(() => {
            mBtnSubmit.innerHTML  = '<i class="fa fa-check-circle"></i> Reinscribir seleccionados';
            mBtnSubmit.disabled   = false;
            mResultPanel.innerHTML = `
                <div class="alert alert-danger alert-dismissible" style="border-radius:8px;">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-exclamation-circle" style="margin-right:6px;"></i>
                    Error de conexión. Intenta nuevamente.
                </div>`;
            mResultPanel.style.display = 'block';
        });
    });

    /* ── Inicializar cascada destino si ya hay ciclo activo preseleccionado ── */
    if (mCicloDest.value) mCicloDest.dispatchEvent(new Event('change'));

})();
</script>
@endpush
