@extends('layouts.master')

@section('page_title', 'Condonaciones')
@section('page_subtitle', 'Historial de condonaciones')

@section('breadcrumb')
    <li class="active">Condonaciones</li>
@endsection

@push('styles')
    <style>
        .con-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            border-radius: 4px 4px 0 0;
            flex-wrap: wrap;
        }

        .con-select {
            height: 36px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 140px;
        }

        .con-table { margin: 0; width: 100%; border-collapse: separate; border-spacing: 0; }
        .con-table thead th {
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
        .con-table tbody tr { border-bottom: 1px solid #f0f3f7; }
        .con-table tbody tr:last-child { border-bottom: none; }
        .con-table tbody tr:hover td { background: #f0f7ff !important; }
        .con-table td { padding: 10px 14px; vertical-align: middle; font-size: 13px; }

        .con-alumno   { font-size: 14px; font-weight: 700; color: #1a2634; line-height: 1.2; }
        .con-sub      { font-size: 11px; color: #aab; margin-top: 2px; }
        .con-monto    { font-size: 15px; font-weight: 700; color: #1a6b2e; }

        .badge-activa    { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }
        .badge-cancelada { background:#f4f6f8; color:#7a8898; border:1px solid #d0d9e2; border-radius:12px; padding:2px 10px; font-size:11px; font-weight:700; }

        .con-empty { text-align:center; padding:60px 20px; }
        .con-empty i { font-size:52px; display:block; margin-bottom:16px; color:#dde4ea; }
        .con-empty h4 { font-size:16px; color:#999; margin:0 0 8px; }
        .con-empty p  { font-size:13px; color:#bbb; margin:0 0 20px; }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-scissors text-olive"></i> Condonaciones
            </h4>
            <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-list"></i> {{ $condonaciones->total() }} registros
            </span>
        </div>
        <a href="{{ route('condonaciones.create') }}" class="btn btn-success btn-sm btn-flat"
           style="border-radius:20px;white-space:nowrap;">
            <i class="fa fa-plus"></i> Nueva condonación
        </a>
    </div>

    <div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

        {{-- Filtros --}}
        <form method="GET" action="{{ route('condonaciones.index') }}">
            <div class="con-toolbar">
                <select name="alumno_id" class="con-select" onchange="this.form.submit()" title="Filtrar por alumno">
                    <option value="">Todos los alumnos</option>
                    @foreach ($alumnos as $alumno)
                        <option value="{{ $alumno->id }}" {{ request('alumno_id') == $alumno->id ? 'selected' : '' }}>
                            {{ $alumno->nombre_completo }}
                        </option>
                    @endforeach
                </select>

                <select name="estado" class="con-select" onchange="this.form.submit()" title="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="activa"    {{ request('estado') === 'activa'    ? 'selected' : '' }}>Activas</option>
                    <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Canceladas</option>
                </select>

                @if (request()->anyFilled(['alumno_id', 'estado']))
                    <a href="{{ route('condonaciones.index') }}" class="btn btn-default btn-flat btn-sm"
                       style="border-radius:20px;padding:5px 14px;" title="Quitar filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif
            </div>
        </form>

        {{-- Tabla --}}
        <div class="box-body no-padding">
            <table class="con-table">
                <thead>
                    <tr>
                        <th style="width:5%;">#</th>
                        <th style="width:25%;">Alumno</th>
                        <th style="width:30%;">Motivo</th>
                        <th style="width:13%;">Monto total</th>
                        <th style="width:10%;">Estado</th>
                        <th style="width:11%;">Registrado</th>
                        <th style="width:6%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($condonaciones as $cond)
                        <tr>
                            <td style="color:#aab;font-size:12px;">{{ $cond->id }}</td>
                            <td>
                                <div class="con-alumno">{{ $cond->alumno->nombre_completo }}</div>
                                <div class="con-sub">{{ $cond->ciclo->nombre ?? '—' }}</div>
                            </td>
                            <td style="font-size:13px;color:#444;">
                                {{ Str::limit($cond->motivo, 80) }}
                            </td>
                            <td>
                                <span class="con-monto">${{ number_format((float) $cond->monto_total, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge-{{ $cond->estado }}">
                                    <i class="fa fa-circle" style="font-size:7px;"></i>
                                    {{ ucfirst($cond->estado) }}
                                </span>
                            </td>
                            <td style="font-size:12px;color:#888;">
                                {{ $cond->creado_at?->format('d/m/Y') }}<br>
                                <span style="color:#aab;">{{ $cond->creadoPor?->nombre ?? '—' }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('condonaciones.show', $cond->id) }}"
                                   class="btn btn-info btn-xs btn-flat" style="border-radius:4px;" title="Ver detalle">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="con-empty">
                                    <i class="fa fa-scissors"></i>
                                    <h4>Sin condonaciones</h4>
                                    <p>No hay condonaciones registradas con los filtros actuales.</p>
                                    <a href="{{ route('condonaciones.create') }}" class="btn btn-success btn-sm"
                                       style="border-radius:20px;">
                                        <i class="fa fa-plus"></i> Nueva condonación
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($condonaciones->hasPages())
            <div class="box-footer" style="border-top:1px solid #f0f3f7;padding:10px 15px;background:#fff;
                                           display:flex;justify-content:space-between;align-items:center;">
                <div class="text-muted" style="font-size:13px;">
                    Mostrando <b>{{ $condonaciones->firstItem() }}</b> a <b>{{ $condonaciones->lastItem() }}</b>
                    de <b>{{ $condonaciones->total() }}</b> condonación(es)
                </div>
                <div>{{ $condonaciones->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
            </div>
        @endif

    </div>

@endsection
