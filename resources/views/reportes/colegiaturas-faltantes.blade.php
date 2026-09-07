@extends('layouts.master')

@php
    $periodoLabel = $periodos->firstWhere('valor', $periodo)['label'] ?? $periodo;
@endphp

@section('page_title', 'Colegiaturas Faltantes')
@section('page_subtitle', 'Alumnos sin cargo de colegiatura del mes seleccionado')

@section('breadcrumb')
    <li><a href="#">Reportes</a></li>
    <li class="active">Colegiaturas faltantes</li>
@endsection

@push('styles')
<style>
.cf-table { width:100%; border-collapse:collapse; }
.cf-table thead th {
    background:#f4f6f8; color:#6b7a8d;
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; padding:9px 12px;
    border-bottom:2px solid #e4eaf0; white-space:nowrap;
}
.cf-table tbody tr { border-bottom:1px solid #f0f3f7; transition:background .1s; }
.cf-table tbody tr:hover td { background:#fff8f6; }
.cf-table td { padding:9px 12px; vertical-align:middle; font-size:13px; }

@media print {
    .sidebar, .main-header, .content-header, .no-print { display:none !important; }
    .content-wrapper { margin-left:0 !important; }
}
</style>
@endpush

@section('content')

{{-- ══ ENCABEZADO + STATS ══ --}}
<div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
            box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-graduation-cap text-orange"></i> Colegiaturas faltantes
        </h4>
        <div style="display:flex;gap:7px;flex-wrap:wrap;">
            <span style="background:#fff8e1;color:#b45309;border:1px solid #fde68a;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-users"></i> {{ $alumnos->count() }} alumnos
            </span>
            @if($periodo)
            <span style="background:#eaf3fb;color:#2980b9;border:1px solid #90c2e7;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-calendar"></i> {{ $periodoLabel }}
            </span>
            @endif
        </div>
    </div>
    @if($alumnos->isNotEmpty())
    <div class="no-print" style="display:flex;gap:6px;flex-shrink:0;">
        <button type="button" onclick="window.print()" class="btn btn-default btn-sm btn-flat"
                style="border-radius:20px;">
            <i class="fa fa-print"></i> Imprimir
        </button>
    </div>
    @endif
</div>

{{-- ══ PANEL PRINCIPAL ══ --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;">

    {{-- Toolbar + filtros de ciclo y mes --}}
    <form method="GET" action="{{ route('reportes.colegiaturas-faltantes') }}"
          class="no-print"
          style="display:flex;align-items:center;gap:8px;padding:10px 14px;
                 background:#f9fafb;border-bottom:1px solid #e8ecf0;flex-wrap:wrap;">

        <select name="ciclo_id" class="form-control input-sm"
                style="border-radius:6px;border-color:#dde4eb;height:32px;max-width:180px;">
            @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->id }}" {{ $ciclo->id == $cicloId ? 'selected' : '' }}>
                    {{ $ciclo->nombre }}
                </option>
            @endforeach
        </select>

        <select name="periodo" class="form-control input-sm"
                style="border-radius:6px;border-color:#dde4eb;height:32px;max-width:180px;">
            @foreach($periodos as $p)
                <option value="{{ $p['valor'] }}" {{ $p['valor'] === $periodo ? 'selected' : '' }}>
                    {{ $p['label'] }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary btn-sm btn-flat"
                style="border-radius:20px;padding:4px 14px;height:32px;">
            <i class="fa fa-search"></i> Consultar
        </button>

        <span style="background:#fff8e1;color:#b45309;font-size:12px;font-weight:600;
                     padding:3px 12px;border-radius:12px;white-space:nowrap;margin-left:auto;">
            <i class="fa fa-users"></i> {{ $alumnos->count() }} alumno(s)
        </span>
    </form>

    <div style="overflow-x:auto;">
        @if(! $periodo)
            <div style="padding:56px 20px;text-align:center;">
                <i class="fa fa-exclamation-triangle" style="font-size:42px;color:#e74c3c;display:block;margin-bottom:12px;"></i>
                <p style="color:#b0bec5;margin:0;font-weight:600;">
                    El ciclo seleccionado no tiene meses disponibles para consultar.
                </p>
            </div>
        @elseif($alumnos->isEmpty())
            <div style="padding:56px 20px;text-align:center;">
                <i class="fa fa-check-circle" style="font-size:42px;color:#27ae60;display:block;margin-bottom:12px;"></i>
                <p style="color:#b0bec5;margin:0;font-weight:600;">
                    Todos los alumnos activos tienen su cargo de colegiatura de {{ $periodoLabel }}.
                </p>
            </div>
        @else
            <table class="cf-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Matrícula</th>
                        <th>Grupo / Nivel</th>
                        <th style="text-align:center;width:150px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alumnos as $i => $a)
                    <tr>
                        <td style="color:#94a3b8;font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <span style="font-weight:700;color:#1a2634;">
                                {{ $a['alumno']->ap_paterno }}
                                {{ $a['alumno']->ap_materno }},
                                {{ $a['alumno']->nombre }}
                            </span>
                        </td>
                        <td>
                            <code style="font-size:12px;background:#f0f3f7;padding:2px 7px;
                                         border-radius:4px;color:#4a5568;">
                                {{ $a['alumno']->matricula ?? '—' }}
                            </code>
                        </td>
                        <td style="color:#4a5568;">
                            @if($a['grupo'])
                                {{ $a['grupo']->nombre }}
                                @if($a['nivel'])
                                    <span style="font-size:11px;color:#94a3b8;">/ {{ $a['nivel']->nombre }}</span>
                                @endif
                            @else
                                <span style="color:#b0bec5;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('alumnos.estado-cuenta', $a['alumno']->id) }}"
                               class="btn btn-xs btn-flat"
                               style="background:#eaf3fb;color:#2980b9;border:1px solid #90c2e7;
                                      border-radius:5px;" title="Ver estado de cuenta">
                                <i class="fa fa-eye"></i> Estado de cuenta
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>

@endsection
