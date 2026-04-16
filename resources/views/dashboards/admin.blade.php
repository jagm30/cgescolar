@extends('layouts.master')

@section('page_title', 'Dashboard')
@section('page_subtitle', 'Resumen general · ' . ($cicloActual->nombre ?? 'Sin ciclo activo'))

@section('breadcrumb')
    <li class="active">Dashboard</li>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════
   KPI CARDS
══════════════════════════════════════════ */
.dash-kpi-row {
    display: flex;
    gap: 14px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}
.dash-kpi {
    flex: 1;
    min-width: 160px;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #e4eaf0;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    position: relative;
    overflow: hidden;
}
.dash-kpi::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.dash-kpi.azul::before   { background: #3c8dbc; }
.dash-kpi.verde::before  { background: #00a65a; }
.dash-kpi.naranja::before{ background: #f39c12; }
.dash-kpi.rojo::before   { background: #dd4b39; }

.dash-kpi-icon {
    width: 50px; height: 50px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.dash-kpi.azul    .dash-kpi-icon { background: #eaf3fb; }
.dash-kpi.verde   .dash-kpi-icon { background: #e8f8f0; }
.dash-kpi.naranja .dash-kpi-icon { background: #fef6e7; }
.dash-kpi.rojo    .dash-kpi-icon { background: #fdecea; }

.dash-kpi-num  { font-size: 28px; font-weight: 800; line-height: 1; color: #1a2634; }
.dash-kpi-lbl  { font-size: 11px; color: #9aacb8; text-transform: uppercase; letter-spacing: .05em; margin-top: 3px; }
.dash-kpi-sub  { font-size: 11px; color: #aaa; margin-top: 4px; }
.dash-kpi-sub.positivo { color: #00a65a; }
.dash-kpi-sub.negativo { color: #dd4b39; }

/* ══════════════════════════════════════════
   PANEL GENÉRICO
══════════════════════════════════════════ */
.dash-panel {
    background: #fff;
    border: 1px solid #e0e7ef;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
    overflow: hidden;
    margin-bottom: 22px;
}
.dash-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 13px 18px;
    border-bottom: 1px solid #edf1f5;
    background: #f9fafb;
}
.dash-panel-title {
    font-size: 13px;
    font-weight: 700;
    color: #3a4a5a;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.dash-panel-title i { color: #3c8dbc; }
.dash-panel-body  { padding: 16px 18px; }

/* ══════════════════════════════════════════
   TABLA PAGOS
══════════════════════════════════════════ */
.dash-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.dash-table thead th {
    background: #f4f6f8;
    color: #6b7a8d;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 9px 14px;
    border-bottom: 2px solid #e0e6ed;
    border-top: none;
    white-space: nowrap;
}
.dash-table tbody tr {
    border-bottom: 1px solid #f0f3f7;
    transition: background .1s;
}
.dash-table tbody tr:last-child { border-bottom: none; }
.dash-table tbody tr:hover td   { background: #f7fbff; }
.dash-table td {
    padding: 9px 14px;
    vertical-align: middle;
    font-size: 12px;
    color: #333;
}
.dash-folio {
    font-family: monospace;
    font-size: 11px;
    background: #f0f3f7;
    padding: 2px 7px;
    border-radius: 4px;
    color: #4a5568;
    border: 1px solid #e2e8f0;
}
.dash-monto { font-weight: 700; font-size: 13px; color: #1a2634; }

/* ══════════════════════════════════════════
   BADGE
══════════════════════════════════════════ */
.dash-badge {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 10px; white-space: nowrap;
}
.dash-badge-efectivo  { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
.dash-badge-tarjeta   { background: #e8f3ff; color: #2c6fad; border: 1px solid #b3d4f5; }
.dash-badge-transferencia { background: #f5eefb; color: #6b21a8; border: 1px solid #d8b4fe; }
.dash-badge-cheque    { background: #fff8e6; color: #b45309; border: 1px solid #fcd97d; }
.dash-badge-otro      { background: #f4f6f8; color: #7a8898; border: 1px solid #d0d9e2; }

/* ══════════════════════════════════════════
   ACCIONES RÁPIDAS
══════════════════════════════════════════ */
.dash-accion {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    border-bottom: 1px solid #f4f6f8;
    text-decoration: none;
    color: #333;
    font-size: 13px;
    font-weight: 500;
    transition: background .12s;
}
.dash-accion:hover { background: #f5f9ff; color: #3c8dbc; text-decoration: none; }
.dash-accion:last-child { border-bottom: none; }
.dash-accion-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

/* ══════════════════════════════════════════
   NIVELES
══════════════════════════════════════════ */
.nivel-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f4f6f8;
}
.nivel-row:last-child { border-bottom: none; }
.nivel-nombre { font-size: 13px; font-weight: 600; color: #333; min-width: 110px; }
.nivel-bar-wrap { flex: 1; background: #f0f3f7; border-radius: 6px; height: 8px; overflow: hidden; }
.nivel-bar { height: 100%; border-radius: 6px; background: linear-gradient(90deg, #3c8dbc, #2c6fad); transition: width .6s ease; }
.nivel-count { font-size: 13px; font-weight: 700; color: #1a2634; min-width: 36px; text-align: right; }

/* ══════════════════════════════════════════
   PROSPECTOS PIPELINE
══════════════════════════════════════════ */
.pipeline-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid #f4f6f8;
    font-size: 13px;
}
.pipeline-item:last-child { border-bottom: none; }
.pipeline-dot {
    width: 10px; height: 10px; border-radius: 50%;
    flex-shrink: 0; margin-right: 10px;
    display: inline-block;
}
.pipeline-num {
    font-weight: 700; font-size: 14px; color: #1a2634;
    background: #f0f3f7; padding: 1px 9px; border-radius: 10px;
}

/* ══════════════════════════════════════════
   CICLO HEADER STRIP
══════════════════════════════════════════ */
.ciclo-strip {
    background: linear-gradient(135deg, #1e4d7b, #3c8dbc);
    border-radius: 8px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
    flex-wrap: wrap;
    gap: 10px;
}
.ciclo-strip-left { display: flex; align-items: center; gap: 14px; }
.ciclo-strip h4   { color: #fff; margin: 0; font-size: 16px; font-weight: 700; }
.ciclo-strip p    { color: rgba(255,255,255,.75); margin: 0; font-size: 12px; }
</style>
@endpush

@section('content')

{{-- ══ CICLO STRIP ══ --}}
<div class="ciclo-strip">
    <div class="ciclo-strip-left">
        <div style="width:42px;height:42px;background:rgba(255,255,255,.15);border-radius:10px;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa fa-calendar" style="color:#fff;font-size:18px;"></i>
        </div>
        <div>
            <h4>{{ $cicloActual->nombre ?? 'Sin ciclo activo' }}</h4>
            <p>
                Bienvenido, <strong style="color:#fff;">{{ auth()->user()->nombre ?? auth()->user()->name }}</strong>
                @if($cicloActual)
                    &nbsp;·&nbsp; {{ $cicloActual->fecha_inicio->format('d/m/Y') }} — {{ $cicloActual->fecha_fin->format('d/m/Y') }}
                @endif
            </p>
        </div>
    </div>
    <a href="{{ route('alumnos.index') }}"
       class="btn btn-sm btn-flat"
       style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:20px;font-size:12px;">
        <i class="fa fa-users"></i> Ver alumnos
    </a>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="dash-kpi-row">

    {{-- Alumnos activos --}}
    <div class="dash-kpi azul">
        <div class="dash-kpi-icon">
            <i class="fa fa-users" style="color:#3c8dbc;font-size:22px;"></i>
        </div>
        <div>
            <div class="dash-kpi-num">{{ number_format($alumnosActivos) }}</div>
            <div class="dash-kpi-lbl">Alumnos activos</div>
            <div class="dash-kpi-sub">{{ number_format($totalAlumnos) }} en total</div>
        </div>
    </div>

    {{-- Inscritos ciclo --}}
    <div class="dash-kpi verde">
        <div class="dash-kpi-icon">
            <i class="fa fa-graduation-cap" style="color:#00a65a;font-size:22px;"></i>
        </div>
        <div>
            <div class="dash-kpi-num">{{ number_format($totalInscritos) }}</div>
            <div class="dash-kpi-lbl">Inscritos ciclo</div>
            <div class="dash-kpi-sub">{{ $totalFamilias }} familias activas</div>
        </div>
    </div>

    {{-- Cobrado hoy --}}
    <div class="dash-kpi naranja">
        <div class="dash-kpi-icon">
            <i class="fa fa-dollar" style="color:#f39c12;font-size:22px;"></i>
        </div>
        <div>
            <div class="dash-kpi-num" style="font-size:22px;">${{ number_format($cobradoHoy, 0) }}</div>
            <div class="dash-kpi-lbl">Cobrado hoy</div>
            @if($cobradoAyer > 0)
            <div class="dash-kpi-sub {{ $cobradoHoy >= $cobradoAyer ? 'positivo' : 'negativo' }}">
                <i class="fa fa-{{ $cobradoHoy >= $cobradoAyer ? 'arrow-up' : 'arrow-down' }}"></i>
                Ayer ${{ number_format($cobradoAyer, 0) }}
            </div>
            @else
            <div class="dash-kpi-sub">Mes: ${{ number_format($cobradoMes, 0) }}</div>
            @endif
        </div>
    </div>

    {{-- Cargos pendientes --}}
    <div class="dash-kpi rojo">
        <div class="dash-kpi-icon">
            <i class="fa fa-exclamation-triangle" style="color:#dd4b39;font-size:22px;"></i>
        </div>
        <div>
            <div class="dash-kpi-num">{{ number_format($cargosPendientes) }}</div>
            <div class="dash-kpi-lbl">Cargos pendientes</div>
            <div class="dash-kpi-sub negativo">
                {{ $cargosVencidos }} vencidos
                @if($montoPendiente > 0)
                 · ${{ number_format($montoPendiente, 0) }}
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ══ FILA PRINCIPAL ══ --}}
<div class="row">

    {{-- ── PAGOS RECIENTES (col-8) ── --}}
    <div class="col-md-8">
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fa fa-credit-card"></i> Últimos pagos registrados
                </h3>
                <a href="{{ route('pagos.index') }}"
                   class="btn btn-default btn-xs btn-flat"
                   style="border-radius:12px;font-size:11px;">
                    Ver todos <i class="fa fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Forma pago</th>
                            <th>Cajero</th>
                            <th style="text-align:right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($ultimosPagos as $pago)
                    <tr>
                        <td>
                            <a href="{{ route('pagos.show', $pago->id) }}"
                               style="text-decoration:none;">
                                <span class="dash-folio">
                                    {{ $pago->folio_recibo ?? '#'.$pago->id }}
                                </span>
                            </a>
                        </td>
                        <td style="color:#888;">
                            {{ $pago->fecha_pago->format('d/m/Y') }}
                        </td>
                        <td>
                            @php
                                $fp = $pago->forma_pago ?? 'otro';
                                $fpClass = match($fp) {
                                    'efectivo'       => 'efectivo',
                                    'tarjeta'        => 'tarjeta',
                                    'transferencia'  => 'transferencia',
                                    'cheque'         => 'cheque',
                                    default          => 'otro',
                                };
                            @endphp
                            <span class="dash-badge dash-badge-{{ $fpClass }}">
                                {{ ucfirst($fp) }}
                            </span>
                        </td>
                        <td style="color:#666;">
                            {{ $pago->cajero->nombre ?? '—' }}
                        </td>
                        <td style="text-align:right;">
                            <span class="dash-monto">${{ number_format($pago->monto_total, 2) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center;padding:40px;color:#ccc;">
                            <i class="fa fa-credit-card" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                            No hay pagos registrados.
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($ultimosPagos->isNotEmpty())
            <div style="padding:10px 18px;border-top:1px solid #edf1f5;background:#f9fafb;
                        display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:11px;color:#aaa;">
                    Cobrado este mes:
                    <strong style="color:#333;">${{ number_format($cobradoMes, 2) }}</strong>
                </span>
                <a href="{{ route('pagos.create') }}"
                   class="btn btn-success btn-xs btn-flat"
                   style="border-radius:12px;font-size:11px;">
                    <i class="fa fa-plus"></i> Nuevo cobro
                </a>
            </div>
            @endif
        </div>

        {{-- ── ALUMNOS POR NIVEL ── --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fa fa-bar-chart"></i> Inscritos por nivel — {{ $cicloActual->nombre ?? '' }}
                </h3>
                <a href="{{ route('alumnos.index') }}"
                   class="btn btn-default btn-xs btn-flat"
                   style="border-radius:12px;font-size:11px;">
                    Ver alumnos <i class="fa fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
            <div class="dash-panel-body">
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
            </div>
        </div>
    </div>

    {{-- ── COLUMNA DERECHA (col-4) ── --}}
    <div class="col-md-4">

        {{-- Acciones rápidas --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fa fa-bolt"></i> Acciones rápidas
                </h3>
            </div>
            <div style="padding:4px 0;">
                <a href="{{ route('pagos.create') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#fff8e1;">
                        <i class="fa fa-dollar" style="color:#f39c12;font-size:16px;"></i>
                    </div>
                    Realizar cobro
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                </a>
                <a href="{{ route('alumnos.create') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#e8f0fb;">
                        <i class="fa fa-user-plus" style="color:#3c8dbc;font-size:15px;"></i>
                    </div>
                    Registrar alumno
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                </a>
                <a href="{{ route('familias.index') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#e8f5e9;">
                        <i class="fa fa-home" style="color:#4caf50;font-size:15px;"></i>
                    </div>
                    Familias
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                </a>
                <a href="{{ route('cargos.index') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#fdecea;">
                        <i class="fa fa-exclamation-circle" style="color:#dd4b39;font-size:15px;"></i>
                    </div>
                    Cargos pendientes
                    @if($cargosPendientes > 0)
                    <span style="margin-left:auto;background:#fdecea;color:#dd4b39;
                                 font-size:11px;font-weight:700;padding:1px 8px;border-radius:10px;">
                        {{ $cargosPendientes }}
                    </span>
                    @else
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                    @endif
                </a>
                <a href="{{ route('prospectos.index') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#f3e8fd;">
                        <i class="fa fa-star" style="color:#8e44ad;font-size:15px;"></i>
                    </div>
                    Prospectos
                    @if($totalProspectos > 0)
                    <span style="margin-left:auto;background:#f3e8fd;color:#8e44ad;
                                 font-size:11px;font-weight:700;padding:1px 8px;border-radius:10px;">
                        {{ $totalProspectos }}
                    </span>
                    @else
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                    @endif
                </a>
                <a href="{{ route('conceptos.index') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#f0f3f7;">
                        <i class="fa fa-tags" style="color:#607d8b;font-size:14px;"></i>
                    </div>
                    Conceptos de cobro
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                </a>
                <a href="{{ route('planes.index') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#f0f3f7;">
                        <i class="fa fa-file-text-o" style="color:#607d8b;font-size:14px;"></i>
                    </div>
                    Planes de pago
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                </a>
                <a href="{{ route('usuarios.index') }}" class="dash-accion">
                    <div class="dash-accion-icon" style="background:#e0f7fa;">
                        <i class="fa fa-lock" style="color:#00838f;font-size:14px;"></i>
                    </div>
                    Usuarios del sistema
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#ddd;font-size:11px;"></i>
                </a>
            </div>
        </div>

        {{-- Pipeline prospectos --}}
        @if($totalProspectos > 0)
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fa fa-filter"></i> Pipeline de admisiones
                </h3>
                <a href="{{ route('prospectos.index') }}"
                   class="btn btn-default btn-xs btn-flat"
                   style="border-radius:12px;font-size:11px;">
                    Ver <i class="fa fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
            <div class="dash-panel-body" style="padding-top:8px;padding-bottom:8px;">
                @php
                    $etapasConfig = [
                        'nuevo'         => ['color' => '#3c8dbc', 'label' => 'Nuevo contacto'],
                        'contactado'    => ['color' => '#8e44ad', 'label' => 'Contactado'],
                        'visita'        => ['color' => '#f39c12', 'label' => 'Visita agendada'],
                        'documentacion' => ['color' => '#00a65a', 'label' => 'Documentación'],
                        'evaluacion'    => ['color' => '#e67e22', 'label' => 'En evaluación'],
                    ];
                @endphp
                @foreach($etapasConfig as $etapa => $cfg)
                @php $n = $prospectosPorEtapa[$etapa] ?? 0; @endphp
                @if($n > 0)
                <div class="pipeline-item">
                    <div style="display:flex;align-items:center;flex:1;">
                        <span class="pipeline-dot" style="background:{{ $cfg['color'] }};"></span>
                        <span style="font-size:12px;color:#555;">{{ $cfg['label'] }}</span>
                    </div>
                    <span class="pipeline-num">{{ $n }}</span>
                </div>
                @endif
                @endforeach

                <div style="margin-top:12px;padding-top:10px;border-top:1px solid #f0f3f7;
                            display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:11px;color:#aaa;">Total en proceso</span>
                    <strong style="font-size:15px;color:#1a2634;">{{ $totalProspectos }}</strong>
                </div>
            </div>
        </div>
        @endif

        {{-- Mini resumen cargos --}}
        <div class="dash-panel">
            <div class="dash-panel-header">
                <h3 class="dash-panel-title">
                    <i class="fa fa-pie-chart"></i> Estado de cargos
                </h3>
                <a href="{{ route('cargos.index') }}"
                   class="btn btn-default btn-xs btn-flat"
                   style="border-radius:12px;font-size:11px;">
                    Ver <i class="fa fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>
            <div class="dash-panel-body" style="padding-top:10px;padding-bottom:10px;">
                <div class="pipeline-item">
                    <div style="display:flex;align-items:center;flex:1;">
                        <span class="pipeline-dot" style="background:#dd4b39;"></span>
                        <span style="font-size:12px;color:#555;">Vencidos</span>
                    </div>
                    <span class="pipeline-num" style="background:#fdecea;color:#b91c1c;">{{ $cargosVencidos }}</span>
                </div>
                <div class="pipeline-item">
                    <div style="display:flex;align-items:center;flex:1;">
                        <span class="pipeline-dot" style="background:#f39c12;"></span>
                        <span style="font-size:12px;color:#555;">Pendientes</span>
                    </div>
                    <span class="pipeline-num">{{ $cargosPendientes - $cargosVencidos }}</span>
                </div>
                <div style="margin-top:10px;padding-top:10px;border-top:1px solid #f0f3f7;
                            display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:11px;color:#aaa;">Monto por cobrar</span>
                    <strong style="font-size:14px;color:#dd4b39;">
                        ${{ number_format($montoPendiente, 0) }}
                    </strong>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
