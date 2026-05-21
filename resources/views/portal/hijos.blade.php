@extends('layouts.master')

@section('page_title', 'Mis hijos')
@section('page_subtitle', 'Datos basicos y grupo inscrito')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Mis hijos</li>
@endsection

@push('styles')
    @include('portal._styles')
@endpush

@section('content')
    <div class="portal-card">
        <div class="portal-card-header">
            <h4 class="portal-card-title"><i class="fa fa-id-card-o"></i> Informacion de alumnos</h4>
            <span class="portal-pill portal-pill-ok">{{ $alumnos->count() }} registrado(s)</span>
        </div>

        @forelse ($alumnos as $alumno)
            @php
                $inscripcion = $alumno->inscripciones->where('activo', true)->first();
                $grupo = $inscripcion?->grupo;
                $estadoClass = $alumno->estado === 'activo' ? 'portal-pill-ok' : 'portal-pill-warn';
            @endphp
            <div class="portal-student">
                <div class="portal-avatar">
                    <i class="fa fa-user"></i>
                </div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <h4 class="portal-student-name">{{ $alumno->nombre_completo }}</h4>
                        <span class="portal-pill {{ $estadoClass }}">{{ ucfirst(str_replace('_', ' ', $alumno->estado)) }}</span>
                    </div>
                    <div class="row" style="margin-top:12px;">
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>Matricula</strong><br>{{ $alumno->matricula }}</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>CURP</strong><br>{{ $alumno->curp ?: 'No registrada' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>Nacimiento</strong><br>{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? 'No registrado' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="portal-meta"><strong>Genero</strong><br>{{ $alumno->genero ?: 'No registrado' }}</div>
                        </div>
                    </div>
                    <div style="margin-top:12px;padding:10px 12px;background:#f8fafc;border:1px solid #eef2f6;border-radius:8px;">
                        @if ($grupo)
                            <strong><i class="fa fa-graduation-cap"></i> Grupo actual:</strong>
                            {{ $grupo->grado->nivel->nombre ?? '' }} {{ $grupo->grado->nombre ?? '' }} {{ $grupo->nombre }}
                            <span class="text-muted">· {{ $inscripcion->ciclo->nombre ?? 'Ciclo activo' }}</span>
                        @else
                            <span class="text-muted"><i class="fa fa-info-circle"></i> Sin grupo activo registrado.</span>
                        @endif
                    </div>
                    <div class="portal-actions">
                        <a href="{{ route('portal.estado-cuenta', $alumno->id) }}" class="btn btn-primary btn-sm btn-flat">
                            <i class="fa fa-file-text-o"></i> Estado de cuenta
                        </a>
                        <a href="{{ route('portal.historial-pagos', $alumno->id) }}" class="btn btn-default btn-sm btn-flat">
                            <i class="fa fa-credit-card"></i> Historial de pagos
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
@endsection
