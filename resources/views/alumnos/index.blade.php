@extends('layouts.master')

@section('page_title', 'Alumnos')
@section('page_subtitle', 'Alumnos inscritos')

@section('breadcrumb')
    <li class="active">Alumnos</li>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════
   ESTADÍSTICAS
══════════════════════════════════════════ */
.alm-stats {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.alm-stat-card {
    flex: 1;
    min-width: 130px;
    background: #fff;
    border: 1px solid #e4eaf0;
    border-top: 3px solid #3c8dbc;
    border-radius: 6px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.alm-stat-card.verde   { border-top-color: #00a65a; }
.alm-stat-card.naranja { border-top-color: #f39c12; }
.alm-stat-card.morado  { border-top-color: #8e44ad; }
.alm-stat-icon {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: #eaf3fb; flex-shrink: 0;
}
.alm-stat-icon.verde   { background: #e8f8f0; }
.alm-stat-icon.naranja { background: #fef6e7; }
.alm-stat-icon.morado  { background: #f5eefb; }
.alm-stat-num  { font-size: 26px; font-weight: 800; line-height: 1; color: #222; }
.alm-stat-lbl  { font-size: 11px; color: #999; margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }

/* ══════════════════════════════════════════
   TOOLBAR
══════════════════════════════════════════ */
.alm-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid #e8ecf0;
    background: #f9fafb;
    border-radius: 4px 4px 0 0;
    flex-wrap: wrap;
}
.alm-search-wrap {
    flex: 1;
    min-width: 200px;
    max-width: 360px;
    position: relative;
}
.alm-search-wrap .form-control {
    padding-left: 38px;
    border-radius: 20px !important;
    border: 1px solid #d0dbe6;
    height: 36px;
    font-size: 13px;
    background: #fff;
    box-shadow: none;
    transition: border-color .15s, box-shadow .15s;
}
.alm-search-wrap .form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 3px rgba(60,141,188,.12);
}
.alm-search-icon {
    position: absolute; left: 13px; top: 50%;
    transform: translateY(-50%);
    color: #aab; font-size: 14px; pointer-events: none;
}
.alm-search-clear {
    position: absolute; right: 12px; top: 50%;
    transform: translateY(-50%);
    color: #aab; cursor: pointer; font-size: 14px;
    text-decoration: none; line-height: 1;
}
.alm-search-clear:hover { color: #dd4b39; }

.alm-select {
    height: 36px !important;
    border-radius: 6px !important;
    border: 1px solid #d0dbe6 !important;
    font-size: 12px !important;
    color: #555 !important;
    padding: 0 8px !important;
    background: #fff !important;
    min-width: 120px;
    max-width: 160px;
}
.alm-count-badge {
    background: #e8f0fb;
    color: #3c8dbc;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 12px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ══════════════════════════════════════════
   TABLA
══════════════════════════════════════════ */
.alm-table { margin: 0; border-collapse: separate; border-spacing: 0; width: 100%; }
.alm-table thead tr th {
    background: #f4f6f8;
    color: #6b7a8d;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 10px 14px;
    border-bottom: 2px solid #e0e6ed;
    border-top: none;
    white-space: nowrap;
}
.alm-table tbody tr {
    border-bottom: 1px solid #f0f3f7;
    transition: background .1s;
}
.alm-table tbody tr[data-href] { cursor: pointer; }
.alm-table tbody tr:last-child { border-bottom: none; }
.alm-table tbody tr:hover td   { background: #f0f7ff !important; }
.alm-table td {
    padding: 10px 14px;
    vertical-align: middle;
    font-size: 13px;
}

/* Avatar */
.alm-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e8ecf0;
    display: block;
}
.alm-avatar-placeholder {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #3c8dbc, #2c6fad);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 14px; font-weight: 800; color: #fff;
    box-shadow: 0 2px 6px rgba(60,141,188,.25);
}
.alm-avatar-placeholder.baja_temporal   { background: linear-gradient(135deg,#f39c12,#e67e22); box-shadow:0 2px 6px rgba(243,156,18,.3); }
.alm-avatar-placeholder.baja_definitiva { background: linear-gradient(135deg,#e74c3c,#c0392b); box-shadow:0 2px 6px rgba(231,76,60,.3); }
.alm-avatar-placeholder.egresado        { background: linear-gradient(135deg,#8e44ad,#6c3483); box-shadow:0 2px 6px rgba(142,68,173,.3); }

/* Nombre */
.alm-nombre    { font-size: 14px; font-weight: 700; color: #1a2634; line-height: 1.2; }
.alm-sub       { font-size: 11px; color: #aab; margin-top: 2px; }
.alm-matricula { font-family: monospace; font-size: 12px; background: #f0f3f7; padding: 2px 7px; border-radius: 4px; color: #4a5568; border: 1px solid #e2e8f0; }

/* Nivel badge */
.alm-nivel-tag {
    display: inline-block;
    font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 10px;
    background: #e8f3ff; color: #2c6fad;
    text-transform: uppercase; letter-spacing: .04em;
    margin-bottom: 3px;
}
.alm-grupo-txt { font-size: 13px; font-weight: 600; color: #333; }

/* Familia */
.alm-familia-lnk {
    font-size: 13px; color: #3c8dbc; font-weight: 600;
    text-decoration: none;
}
.alm-familia-lnk:hover { text-decoration: underline; color: #2c6fad; }

/* Estado badge */
.alm-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700;
    padding: 3px 9px; border-radius: 12px;
    letter-spacing: .02em; white-space: nowrap;
}
.alm-badge-activo          { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; }
.alm-badge-baja_temporal   { background:#fff8e6; color:#b45309; border:1px solid #fcd97d; }
.alm-badge-baja_definitiva { background:#fdecea; color:#b91c1c; border:1px solid #fca5a5; }
.alm-badge-egresado        { background:#f3e8fd; color:#6b21a8; border:1px solid #d8b4fe; }

/* Acciones */
.alm-acciones { display: flex; gap: 4px; }

/* ══════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════ */
.alm-empty {
    text-align: center;
    padding: 60px 20px;
}
.alm-empty i  { font-size: 52px; display: block; margin-bottom: 16px; color: #dde4ea; }
.alm-empty h4 { font-size: 16px; color: #999; margin: 0 0 8px; }
.alm-empty p  { font-size: 13px; color: #bbb; margin: 0 0 20px; }

/* ══════════════════════════════════════════
   FOOTER / PAGINACIÓN
══════════════════════════════════════════ */
.alm-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-top: 1px solid #edf1f5;
    background: #f9fafb;
    flex-wrap: wrap;
    gap: 8px;
}
.alm-footer-info { font-size: 12px; color: #aaa; }
.alm-footer .pagination { margin: 0; }
.alm-footer .pagination > li > a,
.alm-footer .pagination > li > span {
    border-color: #dde4eb; color: #3c8dbc;
    font-size: 12px; padding: 4px 10px;
}
.alm-footer .pagination > .active > a,
.alm-footer .pagination > .active > span {
    background: #3c8dbc; border-color: #3c8dbc;
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" style="border-radius:6px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- ══ ESTADÍSTICAS ══ --}}
<div class="alm-stats">
    <div class="alm-stat-card">
        <div class="alm-stat-icon">
            <i class="fa fa-users" style="color:#3c8dbc;font-size:18px;"></i>
        </div>
        <div>
            <div class="alm-stat-num">{{ $statsTotal ?? $alumnos->total() }}</div>
            <div class="alm-stat-lbl">Total alumnos</div>
        </div>
    </div>
    <div class="alm-stat-card verde">
        <div class="alm-stat-icon verde">
            <i class="fa fa-check-circle" style="color:#00a65a;font-size:18px;"></i>
        </div>
        <div>
            <div class="alm-stat-num">{{ $statsActivos ?? '—' }}</div>
            <div class="alm-stat-lbl">Alumnos activos</div>
        </div>
    </div>
    <div class="alm-stat-card naranja">
        <div class="alm-stat-icon naranja">
            <i class="fa fa-graduation-cap" style="color:#f39c12;font-size:18px;"></i>
        </div>
        <div>
            <div class="alm-stat-num">{{ $statsInscritos ?? '—' }}</div>
            <div class="alm-stat-lbl">Inscritos este ciclo</div>
        </div>
    </div>
    <div class="alm-stat-card morado">
        <div class="alm-stat-icon morado">
            <i class="fa fa-th-large" style="color:#8e44ad;font-size:18px;"></i>
        </div>
        <div>
            <div class="alm-stat-num">{{ $grupos->count() }}</div>
            <div class="alm-stat-lbl">Grupos activos</div>
        </div>
    </div>
</div>

{{-- ══ PANEL PRINCIPAL ══ --}}
<div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

    {{-- Toolbar ─────────────────────────────────── --}}
    <form method="GET" action="{{ route('alumnos.index') }}" id="form-filtros">
    <div class="alm-toolbar">

        {{-- Búsqueda --}}
        <div class="alm-search-wrap">
            <i class="fa fa-search alm-search-icon"></i>
            <input type="text" name="buscar" class="form-control"
                   placeholder="Nombre, matrícula o CURP…"
                   value="{{ request('buscar') }}"
                   autocomplete="off">
            @if(request('buscar'))
            <a href="{{ route('alumnos.index', request()->except('buscar','page')) }}"
               class="alm-search-clear" title="Limpiar">
                <i class="fa fa-times-circle"></i>
            </a>
            @endif
        </div>

        {{-- Filtro nivel --}}
        <select name="nivel_id" class="alm-select" onchange="this.form.submit()"
                title="Filtrar por nivel">
            <option value="">Todos los niveles</option>
            @foreach($niveles as $nivel)
            <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                {{ $nivel->nombre }}
            </option>
            @endforeach
        </select>

        {{-- Filtro grupo --}}
        <select name="grupo_id" class="alm-select" onchange="this.form.submit()"
                title="Filtrar por grupo">
            <option value="">Todos los grupos</option>
            @foreach($grupos as $grupo)
            <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                {{ $grupo->grado->nombre }}° {{ $grupo->nombre }}
            </option>
            @endforeach
        </select>

        {{-- Filtro estado --}}
        <div class="btn-group" style="flex-shrink:0;">
            <a href="{{ route('alumnos.index', array_merge(request()->except('estado','page'), [])) }}"
               class="btn btn-sm btn-flat {{ !request()->filled('estado') ? 'btn-primary' : 'btn-default' }}"
               style="border-radius:4px 0 0 4px;font-size:12px;">
                Todos
            </a>
            <a href="{{ route('alumnos.index', array_merge(request()->except('estado','page'), ['estado'=>'activo'])) }}"
               class="btn btn-sm btn-flat {{ request('estado') === 'activo' ? 'btn-success' : 'btn-default' }}"
               style="font-size:12px;">
                Activos
            </a>
            <a href="{{ route('alumnos.index', array_merge(request()->except('estado','page'), ['estado'=>'baja_temporal'])) }}"
               class="btn btn-sm btn-flat {{ request('estado') === 'baja_temporal' ? 'btn-warning' : 'btn-default' }}"
               style="font-size:12px;">
                Baja temporal
            </a>
            <a href="{{ route('alumnos.index', array_merge(request()->except('estado','page'), ['estado'=>'egresado'])) }}"
               class="btn btn-sm btn-flat {{ request('estado') === 'egresado' ? 'btn-default active' : 'btn-default' }}"
               style="border-radius:0 4px 4px 0;font-size:12px;">
                Egresados
            </a>
        </div>

        {{-- Botón buscar (si escribe y da Enter o clic) --}}
        <button type="submit" class="btn btn-primary btn-flat btn-sm"
                style="border-radius:20px;padding:5px 14px;flex-shrink:0;">
            <i class="fa fa-search"></i> Buscar
        </button>

        {{-- Limpiar filtros --}}
        @if(request()->anyFilled(['buscar','nivel_id','grupo_id','estado']))
        <a href="{{ route('alumnos.index') }}"
           class="btn btn-default btn-flat btn-sm"
           style="border-radius:20px;padding:5px 14px;flex-shrink:0;"
           title="Quitar todos los filtros">
            <i class="fa fa-times"></i>
        </a>
        @endif

        {{-- Contador --}}
        @if($alumnos->total() > 0)
        <span class="alm-count-badge">
            <i class="fa fa-user"></i>
            {{ $alumnos->total() }} alumno{{ $alumnos->total() != 1 ? 's' : '' }}
        </span>
        @endif

        {{-- Nuevo alumno --}}
        @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
        <a href="{{ route('alumnos.create') }}"
           class="btn btn-success btn-flat btn-sm"
           style="border-radius:20px;padding:5px 14px;white-space:nowrap;flex-shrink:0;">
            <i class="fa fa-plus"></i> Registrar alumno
        </a>
        @endif

    </div>
    </form>

    {{-- Tabla ───────────────────────────────────── --}}
    <div class="box-body no-padding">
        <table class="alm-table">
            <thead>
                <tr>
                    <th style="width:52px;"></th>
                    <th style="width:11%;">Matrícula</th>
                    <th style="width:26%;">Nombre</th>
                    <th style="width:20%;">Nivel / Grupo</th>
                    <th style="width:18%;">Familia</th>
                    <th style="width:10%;">Estado</th>
                    <th style="width:10%;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($alumnos as $alumno)
            @php
                $inscripcion = $alumno->inscripciones->first();
                $inicial     = mb_strtoupper(mb_substr($alumno->ap_paterno, 0, 1));
                $estado      = $alumno->estado;
            @endphp
            <tr data-href="{{ route('alumnos.show', $alumno->id) }}">
                {{-- AVATAR --}}
                <td>
                    @if($alumno->foto_url)
                        <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                             class="alm-avatar" alt="{{ $alumno->nombre }}">
                    @else
                        <div class="alm-avatar-placeholder {{ $estado }}">
                            {{ $inicial }}
                        </div>
                    @endif
                </td>

                {{-- MATRÍCULA --}}
                <td>
                    <span class="alm-matricula">{{ $alumno->matricula }}</span>
                </td>

                {{-- NOMBRE --}}
                <td>
                    <div class="alm-nombre">
                        {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                    </div>
                    <div class="alm-sub">{{ $alumno->nombre }}</div>
                </td>

                {{-- NIVEL / GRUPO --}}
                <td>
                    @if($inscripcion)
                        <div class="alm-nivel-tag">{{ $inscripcion->grupo->grado->nivel->nombre ?? '' }}</div>
                        <div class="alm-grupo-txt">
                            {{ $inscripcion->grupo->grado->nombre }}°
                            <strong>{{ $inscripcion->grupo->nombre }}</strong>
                        </div>
                    @else
                        <span style="font-size:12px;color:#ccc;font-style:italic;">Sin inscripción</span>
                    @endif
                </td>

                {{-- FAMILIA --}}
                <td>
                    @if($alumno->familia)
                        <a href="{{ route('familias.show', $alumno->familia->id) }}"
                           class="alm-familia-lnk"
                           title="Ver familia">
                            <i class="fa fa-home" style="font-size:11px;opacity:.6;margin-right:3px;"></i>
                            {{ $alumno->familia->apellido_familia }}
                        </a>
                    @else
                        <span style="font-size:12px;color:#ccc;font-style:italic;">—</span>
                    @endif
                </td>

                {{-- ESTADO --}}
                <td>
                    <span class="alm-badge alm-badge-{{ $estado }}">
                        <i class="fa fa-circle" style="font-size:7px;"></i>
                        @switch($estado)
                            @case('activo')          Activo          @break
                            @case('baja_temporal')   Baja temporal   @break
                            @case('baja_definitiva') Baja definitiva @break
                            @case('egresado')        Egresado        @break
                            @default {{ ucfirst($estado) }}
                        @endswitch
                    </span>
                </td>

                {{-- ACCIONES --}}
                <td>
                    <div class="alm-acciones">
                        <a href="{{ route('alumnos.show', $alumno->id) }}"
                           class="btn btn-default btn-xs btn-flat"
                           style="border-radius:4px;" title="Ver ficha">
                            <i class="fa fa-eye"></i>
                        </a>
                        @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <a href="{{ route('alumnos.edit', $alumno->id) }}"
                           class="btn btn-primary btn-xs btn-flat"
                           style="border-radius:4px;" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endif
                        @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
                        <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                           class="btn btn-warning btn-xs btn-flat"
                           style="border-radius:4px;" title="Estado de cuenta">
                            <i class="fa fa-dollar"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="alm-empty">
                        <i class="fa fa-users"></i>
                        @if(request()->anyFilled(['buscar','nivel_id','grupo_id','estado']))
                        <h4>Sin resultados</h4>
                        <p>No se encontraron alumnos con los filtros aplicados.</p>
                        <a href="{{ route('alumnos.index') }}"
                           class="btn btn-default btn-sm" style="border-radius:20px;">
                            <i class="fa fa-times"></i> Quitar filtros
                        </a>
                        @else
                        <h4>No hay alumnos registrados</h4>
                        <p>Registra el primer alumno del ciclo escolar.</p>
                        @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <a href="{{ route('alumnos.create') }}"
                           class="btn btn-success btn-sm" style="border-radius:20px;">
                            <i class="fa fa-plus"></i> Registrar alumno
                        </a>
                        @endif
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación ───────────────────────────────── --}}
    @if($alumnos->hasPages())
    <div class="alm-footer">
        <span class="alm-footer-info">
            Mostrando <strong>{{ $alumnos->firstItem() }}</strong>–<strong>{{ $alumnos->lastItem() }}</strong>
            de <strong>{{ $alumnos->total() }}</strong> alumno(s)
            @if(request()->anyFilled(['buscar','nivel_id','grupo_id','estado']))
            <span style="color:#3c8dbc;"> · filtrado</span>
            @endif
        </span>
        <div>
            {{ $alumnos->appends(request()->query())->links() }}
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
document.querySelectorAll('.alm-table tbody tr[data-href]').forEach(function(row) {
    row.addEventListener('click', function(e) {
        if (e.target.closest('a, button, input, select')) return;
        window.location.href = row.dataset.href;
    });
});
</script>
@endpush

@endsection
