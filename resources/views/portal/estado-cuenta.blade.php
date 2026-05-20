@extends('layouts.master')

@section('page_title', 'Estado de cuenta')
@section('page_subtitle', $alumno->nombre_completo)

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li><a href="{{ route('portal.hijos') }}">Mis hijos</a></li>
    <li class="active">Estado de cuenta</li>
@endsection

@push('styles')
    @include('portal._styles')
@endpush

@section('content')
    <div class="portal-hero">
        <h3>{{ $alumno->nombre_completo }}</h3>
        <p>
            Matricula {{ $alumno->matricula }}
            @if ($inscripcion->grupo)
                · {{ $inscripcion->grupo->grado->nivel->nombre ?? '' }} {{ $inscripcion->grupo->grado->nombre ?? '' }} {{ $inscripcion->grupo->nombre }}
            @endif
            @if ($inscripcion->ciclo)
                · {{ $inscripcion->ciclo->nombre }}
            @endif
        </p>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="portal-stat">
                <div class="portal-stat-label">Cargado</div>
                <div class="portal-stat-value">${{ number_format($resumen['total_cargado'], 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="portal-stat">
                <div class="portal-stat-label">Pagado</div>
                <div class="portal-stat-value" style="color:#00875a;">${{ number_format($resumen['total_pagado'], 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="portal-stat">
                <div class="portal-stat-label">Pendiente</div>
                <div class="portal-stat-value" style="color:{{ $resumen['total_pendiente'] > 0 ? '#b91c1c' : '#00875a' }};">
                    ${{ number_format($resumen['total_pendiente'], 2) }}
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="portal-stat">
                <div class="portal-stat-label">Vencidos</div>
                <div class="portal-stat-value" style="color:{{ $resumen['cargos_vencidos'] > 0 ? '#b91c1c' : '#00875a' }};">
                    {{ $resumen['cargos_vencidos'] }}
                </div>
            </div>
        </div>
    </div>

    <div class="portal-card">
        <div class="portal-card-header">
            <h4 class="portal-card-title"><i class="fa fa-file-text-o"></i> Cargos del ciclo activo</h4>
            <a href="{{ route('portal.historial-pagos', $alumno->id) }}" class="btn btn-default btn-sm btn-flat">
                <i class="fa fa-credit-card"></i> Ver pagos
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover portal-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Periodo</th>
                        <th>Vencimiento</th>
                        <th class="text-right">Monto</th>
                        <th class="text-right">Pagado</th>
                        <th class="text-right">Pendiente</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cargos as $cargo)
                        @php
                            $pillClass = match ($cargo['estado']) {
                                'pagado', 'condonado' => 'portal-pill-ok',
                                'vencido', 'parcial_vencido' => 'portal-pill-danger',
                                default => 'portal-pill-warn',
                            };
                        @endphp
                        <tr>
                            <td>{{ $cargo['concepto'] }}</td>
                            <td>{{ $cargo['periodo'] ?: 'N/A' }}</td>
                            <td>{{ $cargo['fecha_vencimiento']?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="text-right">${{ number_format($cargo['monto_original'], 2) }}</td>
                            <td class="text-right">${{ number_format($cargo['saldo_abonado'], 2) }}</td>
                            <td class="text-right">${{ number_format($cargo['saldo_pendiente'], 2) }}</td>
                            <td class="text-center">
                                <span class="portal-pill {{ $pillClass }}">{{ ucfirst(str_replace('_', ' ', $cargo['estado'])) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="portal-empty">
                                    <i class="fa fa-inbox" style="font-size:34px;margin-bottom:10px;"></i>
                                    <div>Sin cargos registrados para el ciclo activo.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
