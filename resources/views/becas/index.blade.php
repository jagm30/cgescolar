@extends('layouts.master')

@section('page_title', 'Becas')
@section('page_subtitle', 'Asignaciones de becas')

@section('breadcrumb')
    <li class="active">Becas</li>
@endsection

@push('styles')
    <style>
        /* ══ TOOLBAR ══ */
        .bec-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            border-radius: 4px 4px 0 0;
            flex-wrap: wrap;
        }

        .bec-search-wrap {
            flex: 1;
            min-width: 200px;
            max-width: 360px;
            position: relative;
        }

        .bec-search-wrap .form-control {
            padding-left: 38px;
            border-radius: 20px !important;
            border: 1px solid #d0dbe6;
            height: 36px;
            font-size: 13px;
            background: #fff;
            box-shadow: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .bec-search-wrap .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, .12);
        }

        .bec-search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab;
            font-size: 14px;
            pointer-events: none;
        }

        .bec-search-clear {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            line-height: 1;
        }

        .bec-search-clear:hover { color: #dd4b39; }

        .bec-select {
            height: 36px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 130px;
            max-width: 180px;
        }

        /* ══ TABLA ══ */
        .bec-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .bec-table thead tr th {
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

        .bec-table tbody tr { border-bottom: 1px solid #f0f3f7; }
        .bec-table tbody tr:last-child { border-bottom: none; }
        .bec-table tbody tr:hover td { background: #f0f7ff !important; }

        .bec-table td {
            padding: 10px 14px;
            vertical-align: middle;
            font-size: 13px;
        }

        .bec-alumno { font-size: 14px; font-weight: 700; color: #1a2634; line-height: 1.2; }
        .bec-sub    { font-size: 11px; color: #aab; margin-top: 2px; }
        .bec-nombre { font-size: 13px; font-weight: 700; color: #333; }

        .bec-plan-tag,
        .bec-discount-tag,
        .bec-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 12px;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .bec-plan-tag      { background: #e8f3ff; color: #2c6fad; border: 1px solid #c9e3ff; }
        .bec-discount-tag  { background: #f3e8fd; color: #6b21a8; border: 1px solid #d8b4fe; }
        .bec-badge-activa  { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
        .bec-badge-inactiva{ background: #f4f6f8; color: #7a8898; border: 1px solid #d0d9e2; }

        /* Acciones */
        .bec-acciones {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .bec-acciones form { display: contents; }

        /* Empty state */
        .bec-empty          { text-align: center; padding: 60px 20px; }
        .bec-empty i        { font-size: 52px; display: block; margin-bottom: 16px; color: #dde4ea; }
        .bec-empty h4       { font-size: 16px; color: #999; margin: 0 0 8px; }
        .bec-empty p        { font-size: 13px; color: #bbb; margin: 0 0 20px; }

        /* Paginación */
        .bec-pagination-wrap .pagination { margin: 0 !important; }
        .bec-pagination-wrap .page-link  { padding: 5px 10px; font-size: 13px; }
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

    {{-- ══ ENCABEZADO + STATS ══ --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-graduation-cap text-blue"></i> Becas
            </h4>
            <div style="display:flex;gap:7px;flex-wrap:wrap;">
                <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-graduation-cap"></i> {{ $statsTotal ?? $becas->total() }} total
                </span>
                <span style="background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-check-circle"></i> {{ $statsActivas ?? 0 }} activas
                </span>
                <span style="background:#fdecea;color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-ban"></i> {{ $statsInactivas ?? 0 }} inactivas
                </span>
                <span style="background:#f5eef8;color:#7d3c98;border:1px solid #ebdef0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-list"></i> {{ $catalogo->count() }} tipos
                </span>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <a href="{{ route('becas.catalogo') }}" class="btn btn-default btn-sm btn-flat"
               style="border-radius:20px;">
                <i class="fa fa-list"></i> Catálogo
            </a>
            <a href="{{ route('becas.create') }}" class="btn btn-success btn-sm btn-flat"
               style="border-radius:20px;white-space:nowrap;">
                <i class="fa fa-plus"></i> Asignar beca
            </a>
        </div>
    </div>

    {{-- ══ PANEL PRINCIPAL ══ --}}
    <div class="box"
        style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

        {{-- Toolbar ──────────────────────────────── --}}
        <form method="GET" action="{{ route('becas.index') }}" id="form-filtros-becas">
            <div class="bec-toolbar">

                {{-- Búsqueda --}}
                <div class="bec-search-wrap">
                    <i class="fa fa-search bec-search-icon"></i>
                    <input type="text" name="buscar" class="form-control"
                           placeholder="Alumno, matrícula, beca…"
                           value="{{ request('buscar') }}" autocomplete="off">
                    @if (request('buscar'))
                        <a href="{{ route('becas.index', request()->except('buscar', 'page')) }}"
                           class="bec-search-clear" title="Limpiar">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    @endif
                </div>

                {{-- Filtro catálogo --}}
                <select name="catalogo_beca_id" class="bec-select" onchange="this.form.submit()" title="Filtrar por beca">
                    <option value="">Todas las becas</option>
                    @foreach ($catalogo as $item)
                        <option value="{{ $item->id }}" {{ request('catalogo_beca_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Filtro plan --}}
                <select name="plan_id" class="bec-select" onchange="this.form.submit()" title="Filtrar por plan">
                    <option value="">Todos los planes</option>
                    @foreach ($planes as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Filtro estatus --}}
                <select name="activo" class="bec-select" onchange="this.form.submit()" title="Filtrar por estatus">
                    <option value="">Todos los estatus</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activas</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivas</option>
                </select>

                {{-- Por página --}}
                <select name="perPage" class="bec-select" onchange="this.form.submit()" title="Elementos por página">
                    @foreach ([10, 25, 50, 100] as $op)
                        <option value="{{ $op }}" {{ request('perPage', 10) == $op ? 'selected' : '' }}>
                            {{ $op }} / pág.
                        </option>
                    @endforeach
                </select>

                {{-- Limpiar filtros --}}
                @if (request()->anyFilled(['buscar', 'catalogo_beca_id', 'plan_id', 'activo', 'perPage']))
                    <a href="{{ route('becas.index') }}" class="btn btn-default btn-flat btn-sm"
                       style="border-radius:20px;padding:5px 14px;flex-shrink:0;" title="Quitar filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif

            </div>
        </form>

        {{-- Tabla ──────────────────────────────────── --}}
        <div class="box-body no-padding">
            <table class="bec-table">
                <thead>
                    <tr>
                        <th style="width:22%;">Alumno</th>
                        <th style="width:18%;">Beca</th>
                        <th style="width:18%;">Plan</th>
                        <th style="width:12%;">Descuento</th>
                        <th style="width:15%;">Vigencia</th>
                        <th style="width:9%;">Estado</th>
                        <th style="width:6%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($becas as $beca)
                        <tr>
                            <td>
                                <div class="bec-alumno">{{ $beca->alumno->nombre_completo }}</div>
                                <div class="bec-sub">
                                    {{ $beca->alumno->matricula ?? 'Sin matrícula' }} · {{ $beca->ciclo->nombre ?? '—' }}
                                </div>
                            </td>
                            <td>
                                <div class="bec-nombre">{{ $beca->catalogoBeca->nombre }}</div>
                                <div class="bec-sub">
                                    {{ $beca->creadoPor?->nombre ? 'Por ' . $beca->creadoPor->nombre : 'Sin usuario registrado' }}
                                </div>
                            </td>
                            <td>
                                <span class="bec-plan-tag">
                                    <i class="fa fa-credit-card"></i>
                                    {{ $beca->destino_beca }}
                                </span>
                            </td>
                            <td>
                                <span class="bec-discount-tag">
                                    @if ($beca->catalogoBeca->tipo === 'porcentaje')
                                        {{ number_format((float) $beca->catalogoBeca->valor, 2) }}%
                                    @else
                                        ${{ number_format((float) $beca->catalogoBeca->valor, 2) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="bec-nombre">{{ $beca->vigencia_inicio?->format('d/m/Y') }}</div>
                                <div class="bec-sub">hasta {{ $beca->vigencia_fin?->format('d/m/Y') ?? 'sin fin' }}</div>
                            </td>
                            <td>
                                <span class="bec-badge {{ $beca->activo ? 'bec-badge-activa' : 'bec-badge-inactiva' }}">
                                    <i class="fa fa-circle" style="font-size:7px;"></i>
                                    {{ $beca->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="bec-acciones">
                                    @if ($beca->activo)
                                        <form action="{{ route('becas.destroy', $beca->id) }}" method="POST"
                                              onsubmit="return confirm('¿Deshabilitar esta beca?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs btn-flat"
                                                    style="border-radius:4px;" title="Deshabilitar">
                                                <i class="fa fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="bec-empty">
                                    <i class="fa fa-graduation-cap"></i>
                                    @if (request()->anyFilled(['buscar', 'catalogo_beca_id', 'plan_id', 'activo']))
                                        <h4>Sin resultados</h4>
                                        <p>No se encontraron becas con los filtros aplicados.</p>
                                        <a href="{{ route('becas.index') }}" class="btn btn-default btn-sm"
                                           style="border-radius:20px;">
                                            <i class="fa fa-times"></i> Quitar filtros
                                        </a>
                                    @else
                                        <h4>No hay becas asignadas</h4>
                                        <p>Asigna la primera beca a un alumno.</p>
                                        <a href="{{ route('becas.create') }}" class="btn btn-success btn-sm"
                                           style="border-radius:20px;">
                                            <i class="fa fa-plus"></i> Asignar beca
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación ──────────────────────────────── --}}
        @if ($becas->hasPages())
            <div class="box-footer bec-pagination-wrap"
                 style="border-top:1px solid #f0f3f7;padding:10px 15px;background:#fff;
                        display:flex;justify-content:space-between;align-items:center;">
                <div class="text-muted" style="font-size:13px;">
                    Mostrando <b>{{ $becas->firstItem() }}</b> a <b>{{ $becas->lastItem() }}</b>
                    de <b>{{ $becas->total() }}</b> beca(s)
                    @if (request()->anyFilled(['buscar', 'catalogo_beca_id', 'plan_id', 'activo']))
                        <span style="color:#3c8dbc;"> · filtrado</span>
                    @endif
                </div>
                <div>{{ $becas->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
            </div>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let searchTimer;
            $('input[name="buscar"]').on('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => this.closest('form').submit(), 500);
            });
        });
    </script>
@endpush
