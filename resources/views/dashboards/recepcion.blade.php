@extends('layouts.master')

@section('page_title', 'Dashboard Recepción')
@section('page_subtitle', now()->isoFormat('dddd D [de] MMMM [de] YYYY'))

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@push('styles')
<style>
/* ── KPI Cards ─────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.kpi-card {
    background: #fff; border: 1px solid #e4eaf0; border-radius: 10px;
    padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; flex-direction: column; gap: 4px;
}
.kpi-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #8a9ab0; }
.kpi-value { font-size: 26px; font-weight: 800; line-height: 1.1; color: #1a2634; }
.kpi-sub   { font-size: 11px; color: #b0bec5; margin-top: 2px; }
.kpi-icon  {
    width: 38px; height: 38px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; margin-bottom: 6px;
}

/* ── Section cards ────────────────────────── */
.dash-card {
    background: #fff; border: 1px solid #e4eaf0; border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 20px;
}
.dash-card-header {
    padding: 11px 16px; background: #f8fafc; border-bottom: 1px solid #e8ecf0;
    display: flex; align-items: center; gap: 8px;
}
.dash-card-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7a8d;
}

/* ── Table ────────────────────────────────── */
.dt { width: 100%; border-collapse: collapse; }
.dt thead th {
    background: #f4f6f8; color: #6b7a8d; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    padding: 8px 12px; border-bottom: 2px solid #e4eaf0; white-space: nowrap;
}
.dt tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
.dt tbody tr:last-child { border-bottom: none; }
.dt tbody tr:hover td { background: #f8fbff; }
.dt td { padding: 9px 12px; font-size: 13px; vertical-align: middle; }

/* ── Nivel bars ───────────────────────────── */
.nivel-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid #f4f6f8;
}
.nivel-row:last-child { border-bottom: none; }
.nivel-nombre { font-size: 13px; font-weight: 600; color: #333; min-width: 110px; }
.nivel-bar-wrap { flex: 1; background: #f0f3f7; border-radius: 6px; height: 8px; overflow: hidden; }
.nivel-bar { height: 100%; border-radius: 6px; background: linear-gradient(90deg, #8e44ad, #a855f7); }
.nivel-count { font-size: 13px; font-weight: 700; color: #1a2634; min-width: 36px; text-align: right; }

/* ── Pipeline ─────────────────────────────── */
.pipeline-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 0; border-bottom: 1px solid #f4f6f8; font-size: 13px;
}
.pipeline-item:last-child { border-bottom: none; }
.pipeline-dot {
    width: 10px; height: 10px; border-radius: 50%;
    flex-shrink: 0; margin-right: 10px; display: inline-block;
}
.pipeline-num {
    font-weight: 700; font-size: 14px; color: #1a2634;
    background: #f0f3f7; padding: 1px 9px; border-radius: 10px;
}

/* ── Estado badge ─────────────────────────── */
.estado-badge {
    display: inline-block; font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 8px; white-space: nowrap;
}
</style>
@endpush

@section('content')

{{-- ══ SALUDO ══ --}}
<div style="margin-bottom:20px;">
    <h2 style="font-size:20px;font-weight:800;color:#1a2634;margin:0;">
        Hola, {{ auth()->user()->nombre }}
        <span style="font-weight:400;color:#8a9ab0;">— Recepción</span>
    </h2>
    <p style="font-size:13px;color:#b0bec5;margin:4px 0 0;">
        Resumen de alumnos y admisiones · {{ $cicloActual->nombre ?? 'Sin ciclo activo' }}
    </p>
</div>

{{-- ══ KPIs PRINCIPALES ══ --}}
<div class="kpi-grid">

    {{-- Inscritos ciclo actual --}}
    <div class="kpi-card" style="border-left:4px solid #27a05a;">
        <div class="kpi-icon" style="background:#e8f8f0;color:#00875a;">
            <i class="fa fa-graduation-cap"></i>
        </div>
        <div class="kpi-label">Inscritos ciclo</div>
        <div class="kpi-value" style="color:#00875a;">{{ number_format($totalInscritos) }}</div>
        <div class="kpi-sub">{{ $cicloActual->nombre ?? '—' }}</div>
    </div>

    {{-- Alumnos activos --}}
    <div class="kpi-card" style="border-left:4px solid #3c8dbc;">
        <div class="kpi-icon" style="background:#e8f0fb;color:#2980b9;">
            <i class="fa fa-users"></i>
        </div>
        <div class="kpi-label">Alumnos activos</div>
        <div class="kpi-value" style="color:#2980b9;">{{ number_format($alumnosActivos) }}</div>
        <div class="kpi-sub">Total en el sistema</div>
    </div>

    {{-- Familias --}}
    <div class="kpi-card" style="border-left:4px solid #f39c12;">
        <div class="kpi-icon" style="background:#fff8e1;color:#b45309;">
            <i class="fa fa-home"></i>
        </div>
        <div class="kpi-label">Familias activas</div>
        <div class="kpi-value" style="color:#b45309;">{{ number_format($totalFamilias) }}</div>
        <div class="kpi-sub">Registradas en el sistema</div>
    </div>

    {{-- Prospectos --}}
    <div class="kpi-card" style="border-left:4px solid #8e44ad;">
        <div class="kpi-icon" style="background:#f3e8fd;color:#7b2d8b;">
            <i class="fa fa-user-plus"></i>
        </div>
        <div class="kpi-label">Prospectos activos</div>
        <div class="kpi-value" style="color:#7b2d8b;">{{ number_format($totalProspectos) }}</div>
        <div class="kpi-sub">En proceso de admisión</div>
    </div>

</div>

<div class="row">

{{-- ══ COLUMNA IZQUIERDA ══ --}}
<div class="col-md-8">

    {{-- Últimos alumnos registrados --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-users" style="color:#3c8dbc;"></i>
            <span class="dash-card-title">Últimos alumnos registrados</span>
            <a href="{{ route('alumnos.index') }}"
               style="margin-left:auto;font-size:11px;color:#3c8dbc;text-decoration:none;">
                Ver todos <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        @if($ultimosAlumnos->isEmpty())
        <div style="padding:40px;text-align:center;color:#b0bec5;">
            <i class="fa fa-users" style="font-size:36px;display:block;margin-bottom:10px;"></i>
            Sin alumnos registrados
        </div>
        @else
        <div style="overflow-x:auto;">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre</th>
                        <th>Nivel / Grupo</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;width:50px;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($ultimosAlumnos as $alumno)
                @php
                    $insc = $alumno->inscripciones->first();
                    $estadoConfig = match($alumno->estado) {
                        'activo'           => ['label' => 'Activo',    'bg' => '#e8f8f0', 'color' => '#00875a'],
                        'baja_temporal'    => ['label' => 'Baja temp.','bg' => '#fff8e1', 'color' => '#b45309'],
                        'baja_definitiva'  => ['label' => 'Baja def.', 'bg' => '#fdecea', 'color' => '#b91c1c'],
                        'egresado'         => ['label' => 'Egresado',  'bg' => '#e8f0fb', 'color' => '#2c6fad'],
                        default            => ['label' => ucfirst($alumno->estado), 'bg' => '#f0f3f7', 'color' => '#6b7a8d'],
                    };
                @endphp
                <tr>
                    <td>
                        <code style="font-size:11px;background:#f0f3f7;padding:2px 7px;
                                     border-radius:4px;font-weight:700;color:#1a2634;">
                            {{ $alumno->matricula }}
                        </code>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#1a2634;font-size:12px;">
                            {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
                        </div>
                    </td>
                    <td style="font-size:12px;color:#4a5568;">
                        @if($insc)
                            <div style="font-weight:600;">{{ $insc->grupo?->grado?->nivel?->nombre ?? '—' }}</div>
                            <div style="font-size:11px;color:#b0bec5;">{{ $insc->grupo?->nombre ?? '—' }}</div>
                        @else
                            <span style="color:#b0bec5;">Sin inscripción</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span class="estado-badge"
                              style="background:{{ $estadoConfig['bg'] }};color:{{ $estadoConfig['color'] }};">
                            {{ $estadoConfig['label'] }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ route('alumnos.show', $alumno->id) }}"
                           class="btn btn-xs btn-default btn-flat"
                           style="border-radius:5px;" title="Ver perfil">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Inscritos por nivel --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-bar-chart" style="color:#8e44ad;"></i>
            <span class="dash-card-title">Inscritos por nivel — {{ $cicloActual->nombre ?? '' }}</span>
            <a href="{{ route('alumnos.index') }}"
               style="margin-left:auto;font-size:11px;color:#3c8dbc;text-decoration:none;">
                Ver alumnos <i class="fa fa-arrow-right"></i>
            </a>
        </div>
        <div style="padding:14px 18px;">
            @forelse($inscritosPorNivel as $nivel)
            @php $pct = $totalInscritos > 0 ? round($nivel->total / $totalInscritos * 100) : 0; @endphp
            <div class="nivel-row">
                <span class="nivel-nombre">{{ $nivel->nombre }}</span>
                <div class="nivel-bar-wrap">
                    <div class="nivel-bar" style="width:{{ $pct }}%;"></div>
                </div>
                <span class="nivel-count">{{ $nivel->total }}</span>
                <span style="font-size:10px;color:#aaa;min-width:30px;text-align:right;">{{ $pct }}%</span>
            </div>
            @empty
            <div style="text-align:center;padding:30px;color:#ccc;font-size:13px;">
                <i class="fa fa-graduation-cap" style="font-size:26px;display:block;margin-bottom:8px;"></i>
                Sin inscripciones en este ciclo.
            </div>
            @endforelse

            @if($totalInscritos > 0)
            <div style="display:flex;justify-content:space-between;align-items:center;
                        margin-top:12px;padding-top:12px;border-top:2px solid #e4eaf0;">
                <span style="font-size:12px;font-weight:700;color:#1a2634;">Total inscritos</span>
                <span style="font-size:18px;font-weight:800;color:#8e44ad;">{{ $totalInscritos }}</span>
            </div>
            @endif
        </div>
    </div>

</div>{{-- /col-md-8 --}}

{{-- ══ COLUMNA DERECHA ══ --}}
<div class="col-md-4">

    {{-- Acciones rápidas --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-bolt" style="color:#f39c12;"></i>
            <span class="dash-card-title">Acciones rápidas</span>
        </div>
        <div>
            @php
                $acciones = [
                    ['route' => 'alumnos.create',       'icon' => 'fa-user-plus',   'label' => 'Registrar alumno',      'color' => '#2980b9', 'bg' => '#e8f0fb'],
                    ['route' => 'prospectos.create',     'icon' => 'fa-star',        'label' => 'Nuevo prospecto',       'color' => '#7b2d8b', 'bg' => '#f3e8fd'],
                    ['route' => 'reinscripciones.index', 'icon' => 'fa-refresh',     'label' => 'Reinscripciones',       'color' => '#00875a', 'bg' => '#e8f8f0'],
                    ['route' => 'familias.index',        'icon' => 'fa-home',        'label' => 'Familias',              'color' => '#b45309', 'bg' => '#fff8e1'],
                    ['route' => 'alumnos.index',         'icon' => 'fa-users',       'label' => 'Lista de alumnos',      'color' => '#6b7a8d', 'bg' => '#f0f3f7'],
                    ['route' => 'prospectos.index',      'icon' => 'fa-filter',      'label' => 'Pipeline admisiones',   'color' => '#c0392b', 'bg' => '#fdecea'],
                ];
            @endphp
            @foreach($acciones as $acc)
            <a href="{{ route($acc['route']) }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 16px;
                      border-bottom:1px solid #f4f6f8;text-decoration:none;color:#1a2634;
                      font-size:13px;transition:background .12s;"
               onmouseover="this.style.background='#f8fbff'" onmouseout="this.style.background=''">
                <span style="width:30px;height:30px;border-radius:7px;background:{{ $acc['bg'] }};
                             color:{{ $acc['color'] }};display:flex;align-items:center;
                             justify-content:center;font-size:12px;flex-shrink:0;">
                    <i class="fa {{ $acc['icon'] }}"></i>
                </span>
                <span style="font-weight:500;">{{ $acc['label'] }}</span>
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Pipeline de admisiones --}}
    <div class="dash-card">
        <div class="dash-card-header">
            <i class="fa fa-filter" style="color:#8e44ad;"></i>
            <span class="dash-card-title">Pipeline de admisiones</span>
            <a href="{{ route('prospectos.index') }}"
               style="margin-left:auto;font-size:11px;color:#3c8dbc;text-decoration:none;">
                Ver <i class="fa fa-arrow-right"></i>
            </a>
        </div>
        <div style="padding:14px 16px;">
        @php
            $etapasConfig = [
                'nuevo'         => ['color' => '#3c8dbc', 'label' => 'Nuevo contacto'],
                'contactado'    => ['color' => '#8e44ad', 'label' => 'Contactado'],
                'visita'        => ['color' => '#f39c12', 'label' => 'Visita agendada'],
                'documentacion' => ['color' => '#00a65a', 'label' => 'Documentación'],
                'evaluacion'    => ['color' => '#e67e22', 'label' => 'En evaluación'],
            ];
        @endphp
        @if($totalProspectos > 0)
            @foreach($etapasConfig as $etapa => $cfg)
            @php $n = $prospectosPorEtapa[$etapa] ?? 0; @endphp
            <div class="pipeline-item">
                <div style="display:flex;align-items:center;flex:1;">
                    <span class="pipeline-dot" style="background:{{ $cfg['color'] }};"></span>
                    <span style="font-size:12px;color:#555;">{{ $cfg['label'] }}</span>
                </div>
                <span class="pipeline-num"
                      style="{{ $n === 0 ? 'color:#b0bec5;' : '' }}">{{ $n }}</span>
            </div>
            @endforeach

            <div style="display:flex;justify-content:space-between;align-items:center;
                        margin-top:12px;padding-top:10px;border-top:1px solid #f0f3f7;">
                <span style="font-size:11px;color:#aaa;">Total en proceso</span>
                <strong style="font-size:15px;color:#7b2d8b;">{{ $totalProspectos }}</strong>
            </div>
        @else
            <div style="padding:20px;text-align:center;color:#b0bec5;">
                <i class="fa fa-check-circle" style="font-size:28px;color:#b3e8d0;display:block;margin-bottom:8px;"></i>
                Sin prospectos activos en este ciclo.
                <div style="margin-top:10px;">
                    <a href="{{ route('prospectos.create') }}"
                       class="btn btn-xs btn-flat"
                       style="background:#f3e8fd;color:#7b2d8b;border-radius:12px;font-size:11px;">
                        <i class="fa fa-plus"></i> Agregar prospecto
                    </a>
                </div>
            </div>
        @endif
        </div>
    </div>

</div>{{-- /col-md-4 --}}

</div>{{-- /row --}}

@endsection
