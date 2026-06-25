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

<div class="row">

    {{-- ══ COLUMNA IZQUIERDA: búsqueda y datos del alumno ══ --}}
    <div class="col-md-5">

        {{-- Buscador de alumno --}}
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

        {{-- Tarjeta del alumno seleccionado --}}
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

    {{-- ══ COLUMNA DERECHA: formulario de reinscripción ══ --}}
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

                    {{-- Ciclo escolar --}}
                    <div class="form-group">
                        <label class="ri-label">Ciclo escolar <span style="color:#e74c3c;">*</span></label>
                        <select name="ciclo_id" id="cicloSelect"
                                class="form-control @error('ciclo_id') is-invalid @enderror"
                                style="border-radius:7px;border-color:#dde4eb;" required>
                            <option value="">— Selecciona el ciclo —</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
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

                    {{-- Grado --}}
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

                    {{-- Grupo --}}
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

                    {{-- Aviso si no hay alumno seleccionado --}}
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

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

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

    let searchTimer = null;

    /* ── Búsqueda ─────────────────────────────────── */
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

    /* ── Selección de alumno ──────────────────────── */
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

        alumnoIdInput.value     = a.id;
        alumnoNombre.textContent  = a.nombre_completo;
        alumnoMatricula.textContent = a.matricula;
        alumnoAvatar.textContent  = a.nombre_completo.charAt(0);
        alumnoEstado.textContent  = est.label;
        alumnoEstado.style.background = est.bg;
        alumnoEstado.style.color = est.color;

        alumnoInsc.textContent = a.inscripcion_actual
            ? `${a.inscripcion_actual.ciclo} · ${a.inscripcion_actual.grupo}`
            : 'Sin inscripción activa en el sistema';

        alumnoPanel.style.display = 'block';
        avisoSin.style.display    = 'none';
        habilitarSubmit();
    }

    btnCambiar.addEventListener('click', function () {
        alumnoIdInput.value = '';
        alumnoPanel.style.display = 'none';
        avisoSin.style.display    = 'block';
        buscarInput.value = '';
        buscarInput.focus();
        inhabilitarSubmit();
    });

    /* ── Ciclo → Grado (AJAX) ─────────────────────── */
    cicloSelect.addEventListener('change', function () {
        const cicloId = this.value;

        resetSelect(gradoSelect, '— Selecciona el grado —');
        resetSelect(grupoSelect, '— Primero selecciona el grado —');
        grupoSelect.classList.add('ri-select-disabled');

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
        });
    });

    /* ── Grado → Grupo (AJAX) ─────────────────────── */
    gradoSelect.addEventListener('change', function () {
        const gradoId  = this.value;
        const cicloId  = cicloSelect.value;

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
        });
    });

    /* ── Helpers ──────────────────────────────────── */
    function resetSelect(sel, placeholder) {
        sel.innerHTML = `<option value="">${placeholder}</option>`;
    }

    function habilitarSubmit() {
        if (alumnoIdInput.value) btnSubmit.disabled = false;
    }

    function inhabilitarSubmit() {
        btnSubmit.disabled = true;
    }

    /* ── Prevenir envío sin alumno ────────────────── */
    document.getElementById('reinscripcionForm').addEventListener('submit', function (e) {
        if (!alumnoIdInput.value) {
            e.preventDefault();
            buscarInput.focus();
            avisoSin.style.background = '#fce4e4';
            avisoSin.style.borderColor = '#e74c3c';
            avisoSin.style.color = '#c0392b';
        }
    });

})();
</script>
@endpush
