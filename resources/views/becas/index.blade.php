@extends('layouts.master')

@section('page_title', 'Becas')
@section('page_subtitle', 'Asignaciones de becas')

@section('breadcrumb')
    <li class="active">Becas</li>
@endsection

@push('styles')
    <style>
        .bec-stats {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .bec-stat-card {
            flex: 1;
            min-width: 150px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-top: 3px solid #3c8dbc;
            border-radius: 6px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .bec-stat-card.verde {
            border-top-color: #00a65a;
        }

        .bec-stat-card.naranja {
            border-top-color: #f39c12;
        }

        .bec-stat-card.morado {
            border-top-color: #8e44ad;
        }

        .bec-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf3fb;
            flex-shrink: 0;
        }

        .bec-stat-icon.verde {
            background: #e8f8f0;
        }

        .bec-stat-icon.naranja {
            background: #fef6e7;
        }

        .bec-stat-icon.morado {
            background: #f5eefb;
        }

        .bec-stat-num {
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
            color: #222;
        }

        .bec-stat-lbl {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .bec-panel {
            border-radius: 8px;
            border: 1px solid #e0e7ef;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            overflow: hidden;
        }

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
            min-width: 220px;
            max-width: 380px;
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

        .bec-select {
            height: 36px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 145px;
            max-width: 190px;
        }

        .bec-count-badge {
            background: #e8f0fb;
            color: #3c8dbc;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

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

        .bec-table tbody tr:hover td {
            background: #f0f7ff !important;
        }

        .bec-table td {
            padding: 10px 14px;
            vertical-align: middle;
            font-size: 13px;
            border-top: 1px solid #f0f3f7;
        }

        .bec-alumno {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }

        .bec-sub {
            font-size: 11px;
            color: #aab;
            margin-top: 2px;
        }

        .bec-nombre {
            font-size: 13px;
            font-weight: 700;
            color: #333;
        }

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

        .bec-plan-tag {
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #c9e3ff;
        }

        .bec-discount-tag {
            background: #f3e8fd;
            color: #6b21a8;
            border: 1px solid #d8b4fe;
        }

        .bec-badge-activa {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
        }

        .bec-badge-inactiva {
            background: #f0f3f7;
            color: #6b7280;
            border: 1px solid #dde4eb;
        }

        .bec-acciones {
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        .bec-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .bec-empty i {
            font-size: 52px;
            display: block;
            margin-bottom: 16px;
            color: #dde4ea;
        }

        .bec-empty h4 {
            font-size: 16px;
            color: #999;
            margin: 0 0 8px;
        }

        .bec-empty p {
            font-size: 13px;
            color: #bbb;
            margin: 0 0 20px;
        }

        .bec-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-top: 1px solid #edf1f5;
            background: #f9fafb;
            flex-wrap: wrap;
            gap: 8px;
        }

        .bec-footer-info {
            font-size: 12px;
            color: #aaa;
        }

        .bec-footer .pagination {
            margin: 0;
        }

        .bec-footer .pagination>li>a,
        .bec-footer .pagination>li>span {
            border-color: #dde4eb;
            color: #3c8dbc;
            font-size: 12px;
            padding: 4px 10px;
        }

        .bec-footer .pagination>.active>a,
        .bec-footer .pagination>.active>span {
            background: #3c8dbc;
            border-color: #3c8dbc;
        }
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

    <div class="bec-stats">
        <div class="bec-stat-card">
            <div class="bec-stat-icon">
                <i class="fa fa-graduation-cap" style="color:#3c8dbc;font-size:18px;"></i>
            </div>
            <div>
                <div class="bec-stat-num">{{ $statsTotal ?? $becas->total() }}</div>
                <div class="bec-stat-lbl">Asignaciones</div>
            </div>
        </div>
        <div class="bec-stat-card verde">
            <div class="bec-stat-icon verde">
                <i class="fa fa-check-circle" style="color:#00a65a;font-size:18px;"></i>
            </div>
            <div>
                <div class="bec-stat-num">{{ $statsActivas ?? '0' }}</div>
                <div class="bec-stat-lbl">Becas activas</div>
            </div>
        </div>
        <div class="bec-stat-card naranja">
            <div class="bec-stat-icon naranja">
                <i class="fa fa-ban" style="color:#f39c12;font-size:18px;"></i>
            </div>
            <div>
                <div class="bec-stat-num">{{ $statsInactivas ?? '0' }}</div>
                <div class="bec-stat-lbl">Inactivas</div>
            </div>
        </div>
        <div class="bec-stat-card morado">
            <div class="bec-stat-icon morado">
                <i class="fa fa-list" style="color:#8e44ad;font-size:18px;"></i>
            </div>
            <div>
                <div class="bec-stat-num">{{ $catalogo->count() }}</div>
                <div class="bec-stat-lbl">Tipos de beca</div>
            </div>
        </div>
    </div>

    <div class="box bec-panel">
        <form method="GET" action="{{ route('becas.index') }}" id="form-filtros-becas">
            <div class="bec-toolbar">
                <div class="bec-search-wrap">
                    <i class="fa fa-search bec-search-icon"></i>
                    <input type="text" name="buscar" class="form-control" placeholder="Alumno, matrícula, beca o plan..."
                        value="{{ request('buscar') }}" autocomplete="off">
                </div>

                <select name="catalogo_beca_id" class="bec-select" onchange="this.form.submit()" title="Filtrar por beca">
                    <option value="">Todas las becas</option>
                    @foreach ($catalogo as $item)
                        <option value="{{ $item->id }}" {{ request('catalogo_beca_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->nombre }}
                        </option>
                    @endforeach
                </select>

                <select name="plan_id" class="bec-select" onchange="this.form.submit()" title="Filtrar por plan">
                    <option value="">Todos los planes</option>
                    @foreach ($planes as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->nombre }}
                        </option>
                    @endforeach
                </select>

                <div class="btn-group" style="flex-shrink:0;">
                    <a href="{{ route('becas.index', request()->except('activo', 'page')) }}"
                        class="btn btn-sm btn-flat {{ ! request()->filled('activo') ? 'btn-primary' : 'btn-default' }}"
                        style="border-radius:4px 0 0 4px;font-size:12px;">
                        Todas
                    </a>
                    <a href="{{ route('becas.index', array_merge(request()->except('activo', 'page'), ['activo' => 1])) }}"
                        class="btn btn-sm btn-flat {{ request('activo') === '1' ? 'btn-success' : 'btn-default' }}"
                        style="font-size:12px;">
                        Activas
                    </a>
                    <a href="{{ route('becas.index', array_merge(request()->except('activo', 'page'), ['activo' => 0])) }}"
                        class="btn btn-sm btn-flat {{ request('activo') === '0' ? 'btn-default active' : 'btn-default' }}"
                        style="border-radius:0 4px 4px 0;font-size:12px;">
                        Inactivas
                    </a>
                </div>

                <button type="submit" class="btn btn-primary btn-flat btn-sm"
                    style="border-radius:20px;padding:5px 14px;flex-shrink:0;">
                    <i class="fa fa-search"></i> Buscar
                </button>

                @if (request()->anyFilled(['buscar', 'catalogo_beca_id', 'plan_id', 'activo']))
                    <a href="{{ route('becas.index') }}" class="btn btn-default btn-flat btn-sm"
                        style="border-radius:20px;padding:5px 14px;flex-shrink:0;" title="Quitar filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif

                @if ($becas->total() > 0)
                    <span class="bec-count-badge">
                        <i class="fa fa-graduation-cap"></i>
                        {{ $becas->total() }} beca{{ $becas->total() != 1 ? 's' : '' }}
                    </span>
                @endif

                <a href="{{ route('becas.create') }}" class="btn btn-success btn-flat btn-sm"
                    style="border-radius:20px;padding:5px 14px;white-space:nowrap;flex-shrink:0;">
                    <i class="fa fa-plus"></i> Asignar beca
                </a>

                <a href="{{ route('becas.catalogo') }}" class="btn btn-default btn-flat btn-sm"
                    style="border-radius:20px;padding:5px 14px;white-space:nowrap;flex-shrink:0;">
                    <i class="fa fa-list"></i> Catálogo
                </a>
            </div>
        </form>

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
                        <th style="width:10%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($becas as $beca)
                        <tr>
                            <td>
                                <div class="bec-alumno">{{ $beca->alumno->nombre_completo }}</div>
                                <div class="bec-sub">
                                    {{ $beca->alumno->matricula ?? 'Sin matrícula' }} · {{ $beca->ciclo->nombre ?? 'Ciclo sin nombre' }}
                                </div>
                            </td>
                            <td>
                                <div class="bec-nombre">{{ $beca->catalogoBeca->nombre }}</div>
                                <div class="bec-sub">{{ $beca->creadoPor?->nombre ? 'Creada por '.$beca->creadoPor->nombre : 'Sin usuario registrado' }}</div>
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
                                <div class="bec-sub">
                                    hasta {{ $beca->vigencia_fin?->format('d/m/Y') ?? 'sin fin' }}
                                </div>
                            </td>
                            <td>
                                <span class="bec-badge {{ $beca->activo ? 'bec-badge-activa' : 'bec-badge-inactiva' }}">
                                    <i class="fa fa-circle" style="font-size:7px;"></i>
                                    {{ $beca->activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
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
                                        <span class="text-muted" style="font-size:12px;">Sin acciones</span>
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

        @if ($becas->hasPages())
            <div class="bec-footer">
                <span class="bec-footer-info">
                    Mostrando <strong>{{ $becas->firstItem() }}</strong>-<strong>{{ $becas->lastItem() }}</strong>
                    de <strong>{{ $becas->total() }}</strong> beca(s)
                    @if (request()->anyFilled(['buscar', 'catalogo_beca_id', 'plan_id', 'activo']))
                        <span style="color:#3c8dbc;"> · filtrado</span>
                    @endif
                </span>
                <div>
                    {{ $becas->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
