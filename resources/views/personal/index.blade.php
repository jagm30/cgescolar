@extends('layouts.master')

@section('page_title', 'Personal')
@section('page_subtitle', 'Gestión de empleados')

@section('breadcrumb')
    <li class="active">Personal</li>
@endsection

@push('styles')
    <style>
        .per-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            border-radius: 4px 4px 0 0;
            flex-wrap: wrap;
        }

        .per-search-wrap {
            flex: 1;
            min-width: 200px;
            max-width: 360px;
            position: relative;
        }

        .per-search-wrap .form-control {
            padding-left: 38px;
            border-radius: 20px !important;
            border: 1px solid #d0dbe6;
            height: 36px;
            font-size: 13px;
            background: #fff;
            box-shadow: none;
        }

        .per-search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab;
            font-size: 14px;
            pointer-events: none;
        }

        .per-select {
            height: 36px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 130px;
        }

        .per-table { margin: 0; width: 100%; border-collapse: separate; border-spacing: 0; }

        .per-table thead tr th {
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

        .per-table tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
        .per-table tbody tr:last-child { border-bottom: none; }
        .per-table tbody tr:hover td { background: #f0f7ff !important; }
        .per-table td { padding: 10px 14px; vertical-align: middle; font-size: 13px; }

        .per-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            object-fit: cover; border: 2px solid #e0e6ed;
        }

        .per-avatar-placeholder {
            width: 38px; height: 38px; border-radius: 50%;
            background: #e8f0fb; color: #3c8dbc;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; border: 2px solid #c8dff5;
        }

        .per-nombre { font-size: 14px; font-weight: 700; color: #1a2634; line-height: 1.2; }
        .per-sub    { font-size: 11px; color: #aab; margin-top: 2px; }

        .per-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 700; padding: 3px 9px;
            border-radius: 12px; white-space: nowrap;
        }

        .per-badge-docente        { background:#e8f3ff; color:#2c6fad; border:1px solid #b3d4f5; }
        .per-badge-administrativo { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; }
        .per-badge-mantenimiento  { background:#fff8e6; color:#b45309; border:1px solid #fcd97d; }
        .per-badge-activo         { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; }
        .per-badge-inactivo       { background:#f4f6f8; color:#7a8898; border:1px solid #d0d9e2; }

        .per-empty { text-align: center; padding: 60px 20px; }
        .per-empty i { font-size: 52px; display: block; margin-bottom: 16px; color: #dde4ea; }
        .per-empty h4 { font-size: 16px; color: #999; margin: 0 0 8px; }
        .per-empty p  { font-size: 13px; color: #bbb; margin: 0 0 20px; }
    </style>
@endpush

@section('content')

    {{-- ── Encabezado + stats ── --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-id-badge text-blue"></i> Personal
            </h4>
            <div style="display:flex;gap:7px;flex-wrap:wrap;">
                <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-users"></i> {{ $totales->count() }} total
                </span>
                <span style="background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-check-circle"></i> {{ $totales->where('activo', true)->count() }} activos
                </span>
                <span style="background:#e8f3ff;color:#2c6fad;border:1px solid #b3d4f5;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-graduation-cap"></i> {{ $totales->filter(fn($e) => $e->tipo?->value === 'docente')->count() }} docentes
                </span>
                <span style="background:#fff8e6;color:#b45309;border:1px solid #fcd97d;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-wrench"></i> {{ $totales->filter(fn($e) => $e->tipo?->value === 'mantenimiento')->count() }} mantenimiento
                </span>
            </div>
        </div>
        @if (auth()->user()->esAdministrador())
            <a href="{{ route('personal.create') }}" class="btn btn-success btn-sm btn-flat"
               style="border-radius:20px;white-space:nowrap;flex-shrink:0;">
                <i class="fa fa-plus"></i> Nuevo empleado
            </a>
        @endif
    </div>

    {{-- ── Panel principal ── --}}
    <div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

        <form method="GET" action="{{ route('personal.index') }}">
            <div class="per-toolbar">

                <div class="per-search-wrap">
                    <i class="fa fa-search per-search-icon"></i>
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar empleado…"
                           value="{{ request('buscar') }}" autocomplete="off">
                </div>

                <select name="tipo" class="per-select" onchange="this.form.submit()" title="Filtrar por tipo">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo->value }}" {{ request('tipo') === $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->etiqueta() }}
                        </option>
                    @endforeach
                </select>

                <select name="activo" class="per-select" onchange="this.form.submit()" title="Filtrar por estatus">
                    <option value="">Todos los estatus</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>

                <select name="perPage" class="per-select" onchange="this.form.submit()" title="Por página">
                    @foreach ([5, 10, 25, 50] as $op)
                        <option value="{{ $op }}" {{ request('perPage', 10) == $op ? 'selected' : '' }}>
                            {{ $op }} / pág.
                        </option>
                    @endforeach
                </select>

                @if (request()->anyFilled(['buscar', 'tipo', 'activo', 'perPage']))
                    <a href="{{ route('personal.index') }}" class="btn btn-default btn-flat btn-sm"
                       style="border-radius:20px;padding:5px 14px;flex-shrink:0;" title="Quitar filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif

                <button type="submit" class="btn btn-default btn-flat btn-sm" style="border-radius:20px;">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </form>

        <div class="box-body no-padding">
            <table class="per-table">
                <thead>
                    <tr>
                        <th style="width:5%;"></th>
                        <th style="width:30%;">Empleado</th>
                        <th style="width:13%;">No. Empleado</th>
                        <th style="width:15%;">Tipo</th>
                        <th style="width:17%;">Teléfono / Email</th>
                        <th style="width:8%;">Estatus</th>
                        <th style="width:12%; text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empleados as $empleado)
                        <tr style="cursor:pointer;" onclick="window.location='{{ route('personal.show', $empleado) }}'">

                            <td>
                                @if ($empleado->foto_url)
                                    <img src="{{ asset('storage/' . $empleado->foto_url) }}"
                                         alt="{{ $empleado->nombre_completo }}" class="per-avatar">
                                @else
                                    <span class="per-avatar-placeholder">
                                        {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="per-nombre">{{ $empleado->nombre_completo }}</div>
                                @if ($empleado->rfc)
                                    <div class="per-sub"><i class="fa fa-id-card-o"></i> {{ $empleado->rfc }}</div>
                                @endif
                            </td>

                            <td>
                                <span style="font-family:monospace;background:#f0f3f7;padding:2px 7px;border-radius:4px;font-size:12px;border:1px solid #e2e8f0;">
                                    {{ $empleado->numero_empleado }}
                                </span>
                            </td>

                            <td>
                                @if ($empleado->tipo)
                                    <span class="per-badge per-badge-{{ $empleado->tipo->value }}">
                                        {{ $empleado->tipo->etiqueta() }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div style="font-size:13px;">
                                    <i class="fa fa-phone" style="color:#aab;"></i> {{ $empleado->telefono }}
                                </div>
                                <div class="per-sub">
                                    <i class="fa fa-envelope-o"></i> {{ $empleado->email }}
                                </div>
                            </td>

                            <td>
                                <span class="per-badge {{ $empleado->activo ? 'per-badge-activo' : 'per-badge-inactivo' }}">
                                    <i class="fa fa-circle" style="font-size:7px;"></i>
                                    {{ $empleado->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <td onclick="event.stopPropagation();">
                                <div style="display:flex;align-items:center;gap:4px;justify-content:center;">
                                    <a href="{{ route('personal.show', $empleado) }}"
                                       class="btn btn-info btn-xs btn-flat" style="border-radius:4px;" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    @if (auth()->user()->esAdministrador())
                                        <a href="{{ route('personal.edit', $empleado) }}"
                                           class="btn btn-primary btn-xs btn-flat" style="border-radius:4px;" title="Editar">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('personal.destroy', $empleado) }}" method="POST" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs btn-flat"
                                                    style="border-radius:4px;"
                                                    onclick="return confirm('¿Eliminar al empleado {{ $empleado->nombre_completo }}? Esta acción no se puede deshacer.')"
                                                    title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="per-empty">
                                    <i class="fa fa-id-badge"></i>
                                    @if (request()->anyFilled(['buscar', 'tipo', 'activo']))
                                        <h4>Sin resultados</h4>
                                        <p>No se encontraron empleados con los filtros aplicados.</p>
                                        <a href="{{ route('personal.index') }}" class="btn btn-default btn-sm" style="border-radius:20px;">
                                            <i class="fa fa-times"></i> Quitar filtros
                                        </a>
                                    @else
                                        <h4>No hay empleados registrados</h4>
                                        <p>Registra el primer empleado del plantel.</p>
                                        @if (auth()->user()->esAdministrador())
                                            <a href="{{ route('personal.create') }}" class="btn btn-success btn-sm" style="border-radius:20px;">
                                                <i class="fa fa-plus"></i> Nuevo empleado
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

        @if ($empleados->hasPages())
            <div class="box-footer"
                 style="border-top:1px solid #f0f3f7;padding:10px 15px;background:#fff;display:flex;justify-content:space-between;align-items:center;">
                <div class="text-muted" style="font-size:13px;">
                    Mostrando <b>{{ $empleados->firstItem() }}</b> a <b>{{ $empleados->lastItem() }}</b>
                    de <b>{{ $empleados->total() }}</b> empleados
                </div>
                {{ $empleados->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let timer;
            const buscar = document.querySelector('input[name="buscar"]');
            if (buscar) {
                buscar.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(() => this.closest('form').submit(), 500);
                });
            }
        });
    </script>
@endpush
