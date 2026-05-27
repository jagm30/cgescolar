@extends('layouts.master')

@section('page_title', 'Reporte de Bajas')
@section('page_subtitle', 'Historial de alumnos dados de baja')

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li class="active">Bajas</li>
@endsection

@push('styles')
<style>
    .baja-card {
        background: #fff;
        border: 1px solid #e8ecf0;
        border-radius: 8px;
        padding: 0;
        margin-bottom: 10px;
        overflow: hidden;
        transition: box-shadow .15s;
    }
    .baja-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.08); }
    .baja-card-body {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
    }
    .baja-avatar {
        width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 16px; color: #fff;
    }
    .baja-avatar.baja_temporal  { background: linear-gradient(135deg,#f39c12,#e67e22); }
    .baja-avatar.baja_definitiva { background: linear-gradient(135deg,#e74c3c,#c0392b); }
    .baja-info { flex: 1; min-width: 0; }
    .baja-nombre {
        font-weight: 700; font-size: 14px; color: #2d3748;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .baja-matricula { font-size: 11px; color: #9aa5b4; }
    .baja-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
    .baja-chip {
        font-size: 11px; padding: 2px 8px; border-radius: 10px;
        font-weight: 600; white-space: nowrap;
    }
    .chip-temporal  { background:#fff8e6; color:#b45309; border:1px solid #fcd97d; }
    .chip-definitiva { background:#fdecea; color:#b91c1c; border:1px solid #fca5a5; }
    .chip-motivo    { background:#f0f3f7; color:#4a5568; border:1px solid #e2e8f0; }
    .baja-fecha { font-size: 12px; color: #6b7a8d; flex-shrink:0; text-align:right; }
    .baja-detalle {
        font-size: 12px; color: #6b7a8d; padding: 0 16px 12px;
        border-top: 1px solid #f5f7fa; background: #fafbfc;
        padding-top: 8px;
    }
    .filtros-bar {
        background: #fff; border: 1px solid #e8ecf0; border-radius: 8px;
        padding: 14px 16px; margin-bottom: 16px;
        display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;
    }
    .filtros-bar .form-group { margin: 0; min-width: 160px; flex: 1; }
    .filtros-bar label { font-size: 11px; font-weight: 700; color: #6b7a8d; margin-bottom: 4px; }
    .filtros-bar select, .filtros-bar input { font-size: 12px; }
    .stats-row {
        display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;
    }
    .stat-pill {
        background: #fff; border: 1px solid #e8ecf0; border-radius: 20px;
        padding: 6px 16px; font-size: 13px; color: #4a5568;
        display: flex; align-items: center; gap: 6px;
    }
    .stat-pill strong { color: #2d3748; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">

        {{-- Filtros --}}
        <form method="GET" action="{{ route('alumnos.bajas') }}" id="formFiltros">
            <div class="filtros-bar">
                <div class="form-group">
                    <label>Buscar alumno</label>
                    <input type="text" name="buscar" class="form-control input-sm"
                           placeholder="Nombre, apellido o matrícula"
                           value="{{ request('buscar') }}">
                </div>

                <div class="form-group">
                    <label>Tipo de baja</label>
                    <select name="tipo" class="form-control input-sm" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="baja_temporal"   {{ request('tipo') === 'baja_temporal'   ? 'selected' : '' }}>Temporal</option>
                        <option value="baja_definitiva" {{ request('tipo') === 'baja_definitiva' ? 'selected' : '' }}>Definitiva</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Motivo</label>
                    <select name="motivo_categoria" class="form-control input-sm" onchange="this.form.submit()">
                        <option value="">Todos los motivos</option>
                        @foreach ($motivos as $motivo)
                            <option value="{{ $motivo->value }}"
                                {{ request('motivo_categoria') === $motivo->value ? 'selected' : '' }}>
                                {{ $motivo->etiqueta() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Ciclo escolar</label>
                    <select name="ciclo_id" class="form-control input-sm" onchange="this.form.submit()">
                        <option value="">Todos los ciclos</option>
                        @foreach ($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}"
                                {{ request('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                                {{ $ciclo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex;gap:6px;align-items:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm btn-flat" style="border-radius:4px;">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                    @if (request()->anyFilled(['buscar','tipo','motivo_categoria','ciclo_id']))
                        <a href="{{ route('alumnos.bajas') }}" class="btn btn-default btn-sm btn-flat"
                           style="border-radius:4px;">
                            <i class="fa fa-times"></i> Limpiar
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Estadísticas rápidas --}}
        <div class="stats-row">
            <div class="stat-pill">
                Total bajas: <strong>{{ $bajas->total() }}</strong>
            </div>
            <div class="stat-pill" style="color:#b45309;">
                Temporales: <strong>{{ $bajas->getCollection()->where('tipo','baja_temporal')->count() }}</strong>
            </div>
            <div class="stat-pill" style="color:#b91c1c;">
                Definitivas: <strong>{{ $bajas->getCollection()->where('tipo','baja_definitiva')->count() }}</strong>
            </div>
        </div>

        {{-- Lista --}}
        @forelse ($bajas as $baja)
            <div class="baja-card">
                <div class="baja-card-body">
                    <div class="baja-avatar {{ $baja->tipo }}">
                        {{ mb_strtoupper(mb_substr($baja->alumno->ap_paterno ?? '?', 0, 1)) }}
                    </div>
                    <div class="baja-info">
                        <div class="baja-nombre">
                            <a href="{{ route('alumnos.show', $baja->alumno_id) }}" style="color:inherit;">
                                {{ $baja->alumno->nombre_completo }}
                            </a>
                        </div>
                        <div class="baja-matricula">Mat. {{ $baja->alumno->matricula }}</div>
                        <div class="baja-meta">
                            <span class="baja-chip {{ $baja->tipo === 'baja_temporal' ? 'chip-temporal' : 'chip-definitiva' }}">
                                {{ $baja->tipoEtiqueta() }}
                            </span>
                            <span class="baja-chip chip-motivo">
                                <i class="fa fa-tag" style="font-size:9px;"></i>
                                {{ $baja->motivo_categoria->etiqueta() }}
                            </span>
                            @if ($baja->ciclo)
                                <span class="baja-chip chip-motivo">
                                    <i class="fa fa-calendar-o" style="font-size:9px;"></i>
                                    {{ $baja->ciclo->nombre }}
                                </span>
                            @endif
                            @if ($baja->registradoPor)
                                <span class="baja-chip chip-motivo" style="color:#9aa5b4;">
                                    <i class="fa fa-user-o" style="font-size:9px;"></i>
                                    {{ $baja->registradoPor->nombre }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="baja-fecha">
                        <div style="font-weight:700;color:#e74c3c;">{{ $baja->fecha_baja->format('d/m/Y') }}</div>
                        <div style="font-size:11px;color:#9aa5b4;">Fecha de baja</div>
                    </div>
                </div>
                @if ($baja->motivo_detalle)
                    <div class="baja-detalle">
                        <i class="fa fa-comment-o" style="margin-right:5px;"></i>{{ $baja->motivo_detalle }}
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align:center;padding:40px;color:#9aa5b4;">
                <i class="fa fa-check-circle" style="font-size:40px;display:block;margin-bottom:10px;color:#b3e8d0;"></i>
                <p>No se encontraron bajas con los filtros aplicados.</p>
            </div>
        @endforelse

        {{-- Paginación --}}
        @if ($bajas->hasPages())
            <div style="margin-top:16px;">
                {{ $bajas->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
