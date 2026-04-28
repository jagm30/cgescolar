@extends('layouts.master')

@section('page_title', 'Reporte de Deudores')
@section('page_subtitle', 'Adeudos pendientes por alumno')

@section('breadcrumb')
    <li><a href="#">Reportes</a></li>
    <li class="active">Deudores</li>
@endsection

@section('content')
    {{-- ── Filtros ──────────────────────────────────────── --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-filter"></i> Filtros</h3>
        </div>
        <div class="box-body">
            <form method="GET" action="{{ route('reportes.deudores') }}">
                <div class="row">
                    {{-- Ciclo escolar --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ciclo escolar</label>
                            <select name="ciclo_id" class="form-control">
                                @foreach ($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ $ciclo->id == $cicloId ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tipo de adeudo --}}
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Incluir tipo de adeudo</label>
                            <div style="padding-top: 6px; display: flex; gap: 20px; flex-wrap: wrap;">
                                <label style="font-weight: normal; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="estados[]" value="pendiente"
                                           {{ in_array('pendiente', $estados) ? 'checked' : '' }}>
                                    <span class="label label-warning" style="font-size: 12px;">Pendientes</span>
                                    <small class="text-muted">(vigentes)</small>
                                </label>
                                <label style="font-weight: normal; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="estados[]" value="vencido"
                                           {{ in_array('vencido', $estados) ? 'checked' : '' }}>
                                    <span class="label label-danger" style="font-size: 12px;">Vencidos</span>
                                </label>
                                <label style="font-weight: normal; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="checkbox" name="estados[]" value="parcial"
                                           {{ in_array('parcial', $estados) ? 'checked' : '' }}>
                                    <span class="label label-default" style="font-size: 12px;">Parciales</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div style="padding-top: 2px; display: flex; gap: 8px;">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-search"></i> Consultar
                                </button>
                                @if ($deudores->isNotEmpty())
                                    <button type="button" class="btn btn-success" onclick="window.print()">
                                        <i class="fa fa-print"></i> Imprimir
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Resumen (small-boxes) ───────────────────────── --}}
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $resumen['total_deudores'] }}</h3>
                    <p>Alumnos con adeudo</p>
                </div>
                <div class="icon"><i class="fa fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $resumen['total_pendientes'] }}</h3>
                    <p>Cargos pendientes</p>
                </div>
                <div class="icon"><i class="fa fa-clock-o"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>{{ $resumen['total_vencidos'] }}</h3>
                    <p>Cargos vencidos</p>
                </div>
                <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>${{ number_format($resumen['gran_total'], 2) }}</h3>
                    <p>Total adeudado</p>
                </div>
                <div class="icon"><i class="fa fa-money"></i></div>
            </div>
        </div>
    </div>

    {{-- ── Tabla de deudores ───────────────────────────── --}}
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-list"></i>
                Deudores — {{ $ciclos->firstWhere('id', $cicloId)?->nombre ?? $cicloId }}
            </h3>
            <div class="box-tools pull-right">
                <span class="label label-danger" style="font-size:13px; padding:6px 12px;">
                    {{ $deudores->count() }} alumno(s)
                </span>
            </div>
        </div>

        <div class="box-body no-padding">
            @if ($deudores->isEmpty())
                <div class="text-center" style="padding: 40px;">
                    <i class="fa fa-check-circle" style="font-size: 50px; color: #27ae60;"></i>
                    <p style="margin-top: 15px; font-size: 16px; color: #555;">
                        No hay alumnos con adeudos en este ciclo.
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                        <thead style="background:#c0392b; color:#fff;">
                            <tr>
                                <th>#</th>
                                <th>Alumno</th>
                                <th>Matrícula</th>
                                <th>Grupo / Nivel</th>
                                <th class="text-center">Pendientes</th>
                                <th class="text-center">Vencidos</th>
                                <th class="text-center">Parciales</th>
                                <th class="text-right">Total adeudo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deudores as $i => $d)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <strong>
                                            {{ $d['alumno']->ap_paterno }}
                                            {{ $d['alumno']->ap_materno }}
                                            {{ $d['alumno']->nombre }}
                                        </strong>
                                    </td>
                                    <td>{{ $d['alumno']->matricula ?? '—' }}</td>
                                    <td>
                                        @if ($d['grupo'])
                                            {{ $d['grupo']->nombre }}
                                            @if ($d['nivel'])
                                                <small class="text-muted">/ {{ $d['nivel']->nombre }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($d['pendientes'] > 0)
                                            <span class="label label-warning">{{ $d['pendientes'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($d['vencidos'] > 0)
                                            <span class="label label-danger">{{ $d['vencidos'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($d['parciales'] > 0)
                                            <span class="label label-default">{{ $d['parciales'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <strong style="color:#c0392b;">
                                            ${{ number_format($d['total_adeudo'], 2) }}
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('alumnos.estado-cuenta', $d['alumno']->id) }}"
                                           class="btn btn-xs btn-primary" title="Ver estado de cuenta">
                                            <i class="fa fa-eye"></i> Estado de cuenta
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f9f9f9; font-weight:bold;">
                                <td colspan="7" class="text-right">Gran total:</td>
                                <td class="text-right" style="color:#c0392b;">
                                    ${{ number_format($resumen['gran_total'], 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .small-box.bg-purple {
            background-color: #7b2d8b !important;
            color: #fff;
        }
        @media print {
            .sidebar, .main-header, .content-header, .box-tools,
            form, .btn, .no-print { display: none !important; }
            .content-wrapper { margin-left: 0 !important; }
            .small-box { page-break-inside: avoid; }
        }
    </style>
@endpush
