@extends('layouts.master')

@section('page_title', 'Portal de padres')
@section('page_subtitle', 'Resumen familiar')

@section('breadcrumb')
    <li class="active">Portal</li>
@endsection

@push('styles')
    @include('portal._styles')
@endpush

@section('content')
    <div class="portal-hero">
        <h3>Bienvenido, {{ auth()->user()->nombre }}</h3>
        <p>Consulta la informacion escolar y financiera de tus hijos desde un solo lugar.</p>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="portal-stat">
                <div class="portal-stat-label">Hijos</div>
                <div class="portal-stat-value">{{ $resumen['hijos'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="portal-stat">
                <div class="portal-stat-label">Inscritos</div>
                <div class="portal-stat-value">{{ $resumen['inscritos'] }}</div>
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
    </div>

    <div class="portal-card">
        <div class="portal-card-header">
            <h4 class="portal-card-title"><i class="fa fa-users"></i> Mis hijos</h4>
            <a href="{{ route('portal.hijos') }}" class="btn btn-primary btn-sm btn-flat">
                <i class="fa fa-list"></i> Ver todos
            </a>
        </div>

        @forelse ($alumnos as $alumno)
            @php
                $inscripcion = $alumno->inscripciones->where('activo', true)->first();
                $grupo = $inscripcion?->grupo;
            @endphp
            <div class="portal-student">
                <div class="portal-avatar">
                    <i class="fa fa-user"></i>
                </div>
                <div style="flex:1;">
                    <h4 class="portal-student-name">{{ $alumno->nombre_completo }}</h4>
                    <div class="portal-meta">
                        Matricula {{ $alumno->matricula }}
                        @if ($grupo)
                            · {{ $grupo->grado->nivel->nombre ?? '' }} {{ $grupo->grado->nombre ?? '' }} {{ $grupo->nombre }}
                        @else
                            · Sin grupo activo
                        @endif
                    </div>
                    <div class="portal-actions">
                        <a href="{{ route('portal.estado-cuenta', $alumno->id) }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-file-text-o"></i> Estado de cuenta
                        </a>
                        <a href="{{ route('portal.historial-pagos', $alumno->id) }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-credit-card"></i> Pagos
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div style="padding:16px;">
                <div class="portal-empty">
                    <i class="fa fa-users" style="font-size:38px;margin-bottom:10px;"></i>
                    <div>No hay alumnos vinculados a tu familia.</div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Accesos rápidos --}}
    <div class="row" style="margin-top:4px;">
        <div class="col-sm-6">
            <a href="{{ route('portal.fotos') }}" class="portal-card" style="display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;color:inherit;">
                <div class="portal-avatar" style="flex-shrink:0;">
                    <i class="fa fa-camera"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:13px;color:#172b3a;">Fotografías</div>
                    <div style="font-size:11px;color:#7b8794;">Carga fotos de alumnos y contactos</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6">
            <a href="{{ route('portal.razones-sociales') }}" class="portal-card" style="display:flex;align-items:center;gap:10px;padding:10px 14px;text-decoration:none;color:inherit;">
                <div class="portal-avatar" style="flex-shrink:0;">
                    <i class="fa fa-building-o"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:13px;color:#172b3a;">Datos fiscales</div>
                    <div style="font-size:11px;color:#7b8794;">Gestiona tus razones sociales</div>
                </div>
            </a>
        </div>
    </div>
@endsection
