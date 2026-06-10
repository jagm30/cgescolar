@extends('layouts.master')

@section('page_title', 'Planes de Pago')
@section('page_subtitle', 'Catálogo de planes del ciclo escolar')

@section('breadcrumb')
    <li class="active">Planes de Pago</li>
@endsection

@push('styles')
    {{-- ── LIBRERÍA SELECT2 ── --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* ── DISEÑO IDÉNTICO A TU IMAGEN DE EJEMPLO ── */
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: 34px !important;
            border: 1px solid #ccc !important;
            border-radius: 4px !important;
            padding: 4px 12px;
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
        }

        .select2-dropdown {
            border: 1px solid #ccc !important;
            border-radius: 0 0 4px 4px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 99999 !important;
            /* Inmortalidad sobre el modal */
        }

        .select2-search__field {
            border: 1px solid #aaa !important;
            /* Borde al escribir */
            border-radius: 3px;
        }

        /* ══════════════════════════════════════════
                                                                   ESTADÍSTICAS Y TABLAS (Tu CSS original)
                                                                ══════════════════════════════════════════ */
        .pln-stats {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .pln-stat-card {
            flex: 1;
            min-width: 130px;
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

        .pln-stat-card.verde {
            border-top-color: #00a65a;
        }

        .pln-stat-card.naranja {
            border-top-color: #f39c12;
        }

        .pln-stat-card.morado {
            border-top-color: #8e44ad;
        }

        .pln-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf3fb;
            flex-shrink: 0;
        }

        .pln-stat-icon.verde {
            background: #e8f8f0;
        }

        .pln-stat-icon.naranja {
            background: #fef6e7;
        }

        .pln-stat-icon.morado {
            background: #f5eefb;
        }

        .pln-stat-num {
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
            color: #222;
        }

        .pln-stat-lbl {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ══════════════════════════════════════════
                                                                                                                   TOOLBAR
                                                                                                                ══════════════════════════════════════════ */
        .pln-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            border-radius: 4px 4px 0 0;
            flex-wrap: wrap;
        }

        .pln-select {
            height: 36px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 150px;
            max-width: 200px;
        }

        .pln-count-badge {
            background: #e8f0fb;
            color: #3c8dbc;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pln-clone-badge {
            background: #fff3cd;
            color: #856404;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
            flex-shrink: 0;
            display: none;
        }

        /* ══════════════════════════════════════════
                                                                                                                   TABLA
                                                                                                                ══════════════════════════════════════════ */
        .pln-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .pln-table thead tr th {
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

        .pln-table tbody tr {
            border-bottom: 1px solid #f0f3f7;
            transition: background .1s;
        }

        .pln-table tbody tr:last-child {
            border-bottom: none;
        }

        .pln-table tbody tr:hover td {
            background: #f0f7ff !important;
        }

        .pln-table tbody tr.row-selected td {
            background: #fffbea !important;
        }

        .pln-table td {
            padding: 10px 14px;
            vertical-align: middle;
            font-size: 13px;
        }

        /* Nombre */
        .pln-nombre {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }

        .pln-sub {
            font-size: 11px;
            color: #aab;
            margin-top: 2px;
        }

        /* Nivel badge */
        .pln-nivel-tag {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e8f3ff;
            color: #2c6fad;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* Periodicidad badge */
        .pln-badge {
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

        .pln-badge-mensual {
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #b3d4f5;
        }

        .pln-badge-bimestral {
            background: #e8f0ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }

        .pln-badge-semestral {
            background: #fff8e6;
            color: #b45309;
            border: 1px solid #fcd97d;
        }

        .pln-badge-anual {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
        }

        .pln-badge-unico {
            background: #f5eefb;
            color: #6b21a8;
            border: 1px solid #d8b4fe;
        }

        .pln-badge-activo {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
        }

        .pln-badge-inactivo {
            background: #f4f6f8;
            color: #7a8898;
            border: 1px solid #d0d9e2;
        }

        /* Vigencia */
        .pln-vigencia {
            font-size: 12px;
            color: #555;
            white-space: nowrap;
        }

        .pln-vigencia .sep {
            color: #ccc;
            margin: 0 4px;
        }

        /* Acciones */
        .pln-acciones {
            display: flex;
            gap: 4px;
            justify-content: center;
        }

        /* ══════════════════════════════════════════
                                                                                                                   EMPTY STATE
                                                                                                                ══════════════════════════════════════════ */
        .pln-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .pln-empty i {
            font-size: 52px;
            display: block;
            margin-bottom: 16px;
            color: #dde4ea;
        }

        .pln-empty h4 {
            font-size: 16px;
            color: #999;
            margin: 0 0 8px;
        }

        .pln-empty p {
            font-size: 13px;
            color: #bbb;
            margin: 0 0 20px;
        }

        /* ══════════════════════════════════════════
                                                FOOTER / PAGINACIÓN
                                            ══════════════════════════════════════════ */
        .pln-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-top: 1px solid #edf1f5;
            background: #f9fafb;
            flex-wrap: wrap;
            gap: 8px;
        }

        .pln-footer-info {
            font-size: 12px;
            color: #aaa;
        }

        .pln-footer .pagination {
            margin: 0;
        }

        .pln-footer .pagination>li>a,
        .pln-footer .pagination>li>span {
            border-color: #dde4eb;
            color: #3c8dbc;
            font-size: 12px;
            padding: 4px 10px;
        }

        .pln-footer .pagination>.active>a,
        .pln-footer .pagination>.active>span {
            background: #3c8dbc;
            border-color: #3c8dbc;
        }

        /* Checkbox styling */
        .pln-check {
            cursor: pointer;
            width: 15px;
            height: 15px;
        }
    </style>
@endpush

@section('content')



    {{-- ══ ESTADÍSTICAS ══ --}}
    <div class="pln-stats">
        <div class="pln-stat-card">
            <div class="pln-stat-icon">
                <i class="fa fa-file-text-o" style="color:#3c8dbc;font-size:18px;"></i>
            </div>
            <div>
                <div class="pln-stat-num">{{ $planes->total() }}</div>
                <div class="pln-stat-lbl">Total planes</div>
            </div>
        </div>
        <div class="pln-stat-card verde">
            <div class="pln-stat-icon verde">
                <i class="fa fa-check-circle" style="color:#00a65a;font-size:18px;"></i>
            </div>
            <div>
                <div class="pln-stat-num">{{ $planes->getCollection()->where('activo', true)->count() }}</div>
                <div class="pln-stat-lbl">Activos (página)</div>
            </div>
        </div>
        <div class="pln-stat-card naranja">
            <div class="pln-stat-icon naranja">
                <i class="fa fa-graduation-cap" style="color:#f39c12;font-size:18px;"></i>
            </div>
            <div>
                <div class="pln-stat-num">{{ $niveles->count() }}</div>
                <div class="pln-stat-lbl">Niveles escolares</div>
            </div>
        </div>
        <div class="pln-stat-card morado">
            <div class="pln-stat-icon morado">
                <i class="fa fa-calendar" style="color:#8e44ad;font-size:18px;"></i>
            </div>
            <div>
                <div class="pln-stat-num">{{ $cicloActual->nombre ?? '—' }}</div>
                <div class="pln-stat-lbl">Ciclo activo</div>
            </div>
        </div>
    </div>

    {{-- ══ PANEL PRINCIPAL ══ --}}
    <div class="box"
        style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:visible;">

        {{-- Toolbar ─────────────────────────────────── --}}
        <form method="GET" action="{{ route('planes.index') }}" id="form-filtros">
            <div class="pln-toolbar">

                {{-- Filtro nivel --}}
                <select name="nivel_id" class="pln-select" onchange="this.form.submit()" title="Filtrar por nivel">
                    <option value="">Todos los niveles</option>
                    @foreach ($niveles as $nivel)
                        <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Limpiar filtros --}}
                @if (request()->filled('nivel_id'))
                    <a href="{{ route('planes.index') }}" class="btn btn-default btn-flat btn-sm"
                        style="border-radius:20px;padding:5px 14px;flex-shrink:0;" title="Quitar filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif



                {{-- Badge seleccionados --}}
                <span class="pln-clone-badge" id="badge-seleccionados">
                    <i class="fa fa-check-square-o"></i>
                    <span id="txt-seleccionados">0</span> seleccionado(s)
                </span>

                {{-- Ayuda --}}
                <button type="button" class="btn btn-info btn-flat btn-sm"
                    style="border-radius:20px;padding:5px 12px;flex-shrink:0;" data-toggle="modal" data-target="#modalAyuda"
                    title="Ayuda"> Ayuda
                    <i class="fa fa-question-circle"></i>
                </button>

                {{-- Botones acción --}}
                <div style="display:flex;gap:6px;margin-left:auto;flex-shrink:0;">
                    <button type="button" class="btn btn-warning btn-flat btn-sm" id="btn-clonar-masivo" disabled
                        data-toggle="modal" data-target="#modalClonacionMasiva"
                        style="border-radius:20px;padding:5px 14px;">
                        <i class="fa fa-copy"></i> Clonar seleccionados
                    </button>
                    <button type="button" class="btn btn-success btn-flat btn-sm" data-toggle="modal"
                        data-target="#modalNuevoPlan" style="border-radius:20px;padding:5px 14px;">
                        <i class="fa fa-plus"></i> Nuevo plan
                    </button>
                </div>

            </div>
        </form>

        {{-- Tabla ───────────────────────────────────── --}}
        <div class="box-body no-padding">
            <table class="pln-table" id="tabla-planes">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">
                            <input type="checkbox" id="select-all-planes" class="pln-check">
                        </th>
                        <th style="width:28%;">Nombre del Plan</th>
                        <th style="width:14%;">Nivel</th>
                        <th style="width:12%;">Periodicidad</th>
                        <th style="width:20%;">Vigencia</th>
                        <th style="width:9%;">Estatus</th>
                        <th style="width:17%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planes as $plan)
                        <tr data-href="{{ route('planes.show', $plan->id) }}" style="cursor: pointer;">
                            {{-- CHECKBOX --}}
                            <td class="text-center">
                                <input type="checkbox" class="plan-checkbox pln-check" value="{{ $plan->id }}">
                            </td>

                            {{-- NOMBRE --}}
                            <td>
                                <div class="pln-nombre">{{ $plan->nombre }}</div>
                                <div class="pln-sub">
                                    <i class="fa fa-tag" style="font-size:10px;"></i>
                                    {{ $plan->conceptos->count() }}
                                    concepto{{ $plan->conceptos->count() != 1 ? 's' : '' }}
                                    asignado{{ $plan->conceptos->count() != 1 ? 's' : '' }}
                                </div>
                            </td>

                            {{-- NIVEL --}}
                            <td>
                                @if ($plan->nivel)
                                    <span class="pln-nivel-tag">{{ $plan->nivel->nombre }}</span>
                                @else
                                    <span style="font-size:12px;color:#ccc;font-style:italic;">—</span>
                                @endif
                            </td>

                            {{-- PERIODICIDAD --}}
                            <td>
                                <span class="pln-badge pln-badge-{{ $plan->periodicidad }}">
                                    {{ ucfirst($plan->periodicidad) }}
                                </span>
                            </td>

                            {{-- VIGENCIA --}}
                            <td>
                                <span class="pln-vigencia">
                                    <i class="fa fa-calendar-o" style="color:#bbb;font-size:11px;"></i>
                                    {{ $plan->fecha_inicio->format('d/m/Y') }}
                                    <span class="sep">—</span>
                                    {{ $plan->fecha_fin->format('d/m/Y') }}
                                </span>
                            </td>

                            {{-- ESTATUS --}}
                            <td>
                                <span class="pln-badge {{ $plan->activo ? 'pln-badge-activo' : 'pln-badge-inactivo' }}">
                                    <i class="fa fa-circle" style="font-size:7px;"></i>
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="pln-acciones">
                                    <div class="dropdown">
                                        <button class="btn btn-default btn-xs btn-flat dropdown-toggle" type="button"
                                            id="dropdownMenu{{ $plan->id }}" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false" title="Más acciones"
                                            style="border-radius:4px; padding:3px 8px;">
                                            Ver opciones <span class="caret"></span>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-right"
                                            aria-labelledby="dropdownMenu{{ $plan->id }}">

                                            <li>
                                                <a href="{{ route('planes.show', $plan->id) }}" title="Ver resumen">
                                                    <i class="fa fa-eye"></i> Ver resumen
                                                </a>
                                            </li>

                                            <li>
                                                <a href="#" data-toggle="modal"
                                                    data-target="#modalEditarPlan{{ $plan->id }}" title="Editar">
                                                    <i class="fa fa-pencil"></i> Editar
                                                </a>
                                            </li>

                                            <li role="separator" class="divider"></li>

                                            <li>
                                                <a href="{{ route('planes.conceptos.index', $plan->id) }}"
                                                    title="Configurar plan">
                                                    <i class="fa fa-cogs text-primary"></i> Configurar
                                                </a>
                                            </li>

                                            @if ($plan->activo)
                                                <li>
                                                    <form action="{{ route('planes.destroy', $plan->id) }}"
                                                        method="POST" style="margin: 0;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-link"
                                                            style="width: 100%; text-align: left; padding: 3px 20px; color: #333; text-decoration: none;"
                                                            title="Desactivar"
                                                            onclick="return confirm('¿Desactivar este plan?');">
                                                            <i class="fa fa-ban text-danger"></i> Desactivar
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <form action="{{ route('planes.update', $plan->id) }}" method="POST"
                                                        style="margin: 0;">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="activo" value="1">
                                                        <button type="submit" class="btn btn-link"
                                                            style="width: 100%; text-align: left; padding: 3px 20px; color: #333; text-decoration: none;"
                                                            title="Reactivar"
                                                            onclick="return confirm('¿Reactivar este plan?');">
                                                            <i class="fa fa-refresh text-success"></i> Reactivar
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="pln-empty">
                                    <i class="fa fa-file-text-o"></i>
                                    @if (request()->filled('nivel_id'))
                                        <h4>Sin resultados</h4>
                                        <p>No se encontraron planes para el nivel seleccionado.</p>
                                        <a href="{{ route('planes.index') }}" class="btn btn-default btn-sm"
                                            style="border-radius:20px;">
                                            <i class="fa fa-times"></i> Quitar filtros
                                        </a>
                                    @else
                                        <h4>No hay planes registrados</h4>
                                        <p>Crea el primer plan de pago del ciclo escolar.</p>
                                        <button type="button" class="btn btn-success btn-sm" style="border-radius:20px;"
                                            data-toggle="modal" data-target="#modalNuevoPlan">
                                            <i class="fa fa-plus"></i> Nuevo plan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación ───────────────────────────────── --}}
        @if ($planes->hasPages())
            <div class="pln-footer">
                <span class="pln-footer-info">
                    Mostrando <strong>{{ $planes->firstItem() }}</strong>–<strong>{{ $planes->lastItem() }}</strong>
                    de <strong>{{ $planes->total() }}</strong> plan(es)
                    @if (request()->filled('nivel_id'))
                        <span style="color:#3c8dbc;"> · filtrado</span>
                    @endif
                </span>
                <div>
                    {{ $planes->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

    </div>

    {{-- ══ MODAL NUEVO PLAN ══ --}}
    <x-modal id="modalNuevoPlan" title="Crear nuevo Plan de Pago para el ciclo <b>{{ $cicloActual->nombre }}</b>"
        size="modal-lg">
        <form action="{{ route('planes.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger" style="margin: 0 15px 15px 15px; padding: 10px;">
                    <h4 style="font-size: 15px; margin-top: 0;"><i class="icon fa fa-ban"></i> Por favor corrige los
                        siguientes errores:</h4>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label><i class="fa fa-file-text-o"></i> Nombre de Plan</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}"
                            placeholder="Ej: Plan Anual Secundaria" required>
                    </div>
                    <input type="hidden" name="ciclo_id" value="{{ $cicloActual->id }}">

                    <div class="form-group">
                        <label><i class="fa fa-calendar"></i> Ciclo Escolar</label>
                        <input type="text" class="form-control" value="{{ $cicloActual->nombre }}" readonly disabled>
                        <small class="text-muted">El plan se creará automáticamente en el ciclo actual.</small>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-graduation-cap"></i> Nivel Escolar</label>
                        <select name="nivel_id" class="form-control" required>
                            <option value="">Seleccione un nivel...</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel->id }}"
                                    {{ old('nivel_id') == $nivel->id ? 'selected' : '' }}>{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-clock-o"></i> Periodicidad</label>
                        <select name="periodicidad" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="mensual" {{ old('periodicidad') == 'mensual' ? 'selected' : '' }}>Mensual
                            </option>
                            <option value="bimestral" {{ old('periodicidad') == 'bimestral' ? 'selected' : '' }}>
                                Bimestral</option>
                            <option value="semestral" {{ old('periodicidad') == 'semestral' ? 'selected' : '' }}>
                                Semestral</option>
                            <option value="anual" {{ old('periodicidad') == 'anual' ? 'selected' : '' }}>Anual
                            </option>
                            <option value="unico" {{ old('periodicidad') == 'unico' ? 'selected' : '' }}>Pago Único
                            </option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control"
                                    value="{{ old('fecha_inicio') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control"
                                    value="{{ old('fecha_fin') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    {{-- CONCEPTOS --}}
                    <div style="background-color: #f4f4f4; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                        <h4 style="margin-top: 0; font-size: 16px;"><i class="fa fa-tags"></i> Conceptos del Plan
                            <button type="button" id="btn-agregar-concepto" class="btn btn-success btn-xs pull-right">
                                <i class="fa fa-plus"></i> Añadir
                            </button>
                        </h4>
                    </div>
                    <table class="table table-bordered table-striped" id="tabla-conceptos-modal">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th style="width: 120px;">Monto ($)</th>
                                <th style="width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (old('conceptos'))
                                @foreach (old('conceptos') as $index => $concepto)
                                    <tr id="fila-concepto-{{ $index }}">
                                        <td>
                                            {{-- Select configurado con la clase y el ancho correcto --}}
                                            <select name="conceptos[{{ $index }}][concepto_id]"
                                                class="form-control input-sm select-concepto-magico select2-busqueda"
                                                style="width: 100%;" required>
                                                <option value="">Seleccione...</option>
                                                @foreach ($conceptos->sortBy('nombre') as $c)
                                                    <option value="{{ $c->id }}" data-monto="{{ $c->monto }}"
                                                        {{ isset($concepto['concepto_id']) && $concepto['concepto_id'] == $c->id ? 'selected' : '' }}>
                                                        {{ $c->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" step="0.01" min="0"
                                                name="conceptos[{{ $index }}][monto]"
                                                class="form-control input-sm input-monto-magico"
                                                value="{{ $concepto['monto'] ?? '' }}" required></td>
                                        <td class="text-center"><button type="button"
                                                class="btn btn-danger btn-xs btn-eliminar-fila"
                                                data-id="{{ $index }}"><i class="fa fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div id="mensaje-vacio-modal" class="text-center text-muted"
                        style="padding: 10px; {{ old('conceptos') ? 'display: none;' : '' }}">No hay conceptos.</div>

                    {{-- DESCUENTOS --}}
                    <div
                        style="background-color: #fcf8e3; padding: 15px; border-radius: 5px; margin-top: 15px; border: 1px solid #faebcc;">
                        <h4 style="margin-top: 0; font-size: 15px; color: #8a6d3b; margin-bottom: 15px;">
                            <i class="fa fa-percent"></i> Políticas de Descuento (Pronto Pago)
                        </h4>
                        <div id="contenedor-descuentos">
                            <div class="row header-descuentos"
                                style="margin-bottom: 5px; display: {{ old('descuentos') ? 'flex' : 'none' }}; align-items: center;">
                                <div class="col-md-3"><label style="font-size: 11px; margin-bottom: 0;">Nombre</label>
                                </div>
                                <div class="col-md-3"><label style="font-size: 11px; margin-bottom: 0;">Tipo</label></div>
                                <div class="col-md-3"><label style="font-size: 11px; margin-bottom: 0;">Valor ($ o
                                        %)</label></div>
                                <div class="col-md-2"><label style="font-size: 11px; margin-bottom: 0;">Día Límite</label>
                                </div>
                                <div class="col-md-1"></div>
                            </div>
                            @if (old('descuentos'))
                                @foreach (old('descuentos') as $index => $desc)
                                    <div class="row fila-desc" id="fila-desc-{{ $index }}"
                                        style="margin-bottom: 8px;">
                                        <div class="col-md-3"><input type="text"
                                                name="descuentos[{{ $index }}][nombre]"
                                                class="form-control input-sm" value="{{ $desc['nombre'] ?? '' }}"
                                                placeholder="Nombre" required></div>
                                        <div class="col-md-3"><select name="descuentos[{{ $index }}][tipo_valor]"
                                                class="form-control input-sm">
                                                <option value="porcentaje"
                                                    {{ isset($desc['tipo_valor']) && $desc['tipo_valor'] == 'porcentaje' ? 'selected' : '' }}>
                                                    %</option>
                                                <option value="monto_fijo"
                                                    {{ isset($desc['tipo_valor']) && $desc['tipo_valor'] == 'monto_fijo' ? 'selected' : '' }}>
                                                    $</option>
                                            </select></div>
                                        <div class="col-md-3"><input type="number" step="0.01" min="0"
                                                name="descuentos[{{ $index }}][valor]"
                                                class="form-control input-sm" value="{{ $desc['valor'] ?? '' }}"
                                                required></div>
                                        <div class="col-md-2"><input type="number" min="1" max="31"
                                                name="descuentos[{{ $index }}][dia_limite]"
                                                class="form-control input-sm" value="{{ $desc['dia_limite'] ?? '' }}"
                                                placeholder="Día"></div>
                                        <div class="col-md-1 text-right"><button type="button"
                                                class="btn btn-danger btn-xs btn-remove-desc"
                                                data-id="{{ $index }}"><i class="fa fa-times"></i></button></div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" id="btn-add-descuento" class="btn btn-warning btn-xs"
                            style="margin-top: 5px;">
                            <i class="fa fa-plus"></i> Agregar Descuento
                        </button>
                    </div>

                    {{-- RECARGOS --}}
                    <div
                        style="background-color: #f2dede; padding: 15px; border-radius: 5px; margin-top: 15px; border: 1px solid #ebccd1;">
                        <h4 style="margin-top: 0; margin-bottom: 15px; font-size: 15px; color: #a94442;">
                            <i class="fa fa-calendar-times-o"></i> Política de Recargo (Mora)
                        </h4>
                        <div class="row">
                            <div class="col-md-4">
                                <label style="font-size: 11px;">Día Límite Pago</label>
                                <input type="number" min="1" max="31" name="recargo[dia_limite_pago]"
                                    class="form-control input-sm" placeholder="Ej: 10"
                                    value="{{ old('recargo.dia_limite_pago') }}">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px;">Tipo Recargo</label>
                                <select name="recargo[tipo_recargo]" class="form-control input-sm">
                                    <option value="porcentaje"
                                        {{ old('recargo.tipo_recargo') == 'porcentaje' ? 'selected' : '' }}>Porcentaje %
                                    </option>
                                    <option value="monto_fijo"
                                        {{ old('recargo.tipo_recargo') == 'monto_fijo' ? 'selected' : '' }}>Monto Fijo $
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px;">Valor ($ o %)</label>
                                <input type="number" step="0.01" min="0" name="recargo[valor]"
                                    class="form-control input-sm" placeholder="0.00" value="{{ old('recargo.valor') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr style="margin-top: 10px; margin-bottom: 15px;">
            <div class="clearfix" style="padding-bottom: 10px;">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary pull-right">
                    <i class="fa fa-save"></i> Guardar Plan Completo
                </button>
            </div>
        </form>
    </x-modal>

    {{-- ══ MODALES EDITAR ══ --}}
    @foreach ($planes as $plan)
        <x-modal id="modalEditarPlan{{ $plan->id }}" title="Editar Plan: {{ $plan->nombre }}" size="modal-md">
            <form action="{{ route('planes.update', $plan->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Nombre del Plan</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $plan->nombre }}" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control"
                                value="{{ $plan->fecha_inicio->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control"
                                value="{{ $plan->fecha_fin->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Estatus</label>
                    <select name="activo" class="form-control">
                        <option value="1" {{ $plan->activo ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$plan->activo ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <div class="callout callout-info">
                    <p style="font-size: 12px;">
                        <i class="fa fa-info-circle"></i> Edita conceptos, descuentos y recargos desde el botón
                        "Configurar".
                    </p>
                </div>
                <div class="modal-footer no-padding">
                    <button type="submit" class="btn btn-warning pull-right">Guardar Cambios</button>
                </div>
            </form>
        </x-modal>
    @endforeach

    {{-- ══ MODAL CLONACIÓN MASIVA ══ --}}
    <x-modal id="modalClonacionMasiva" title="Clonar Planes Seleccionados" size="modal-md">
        <form action="{{ route('planes.clonar.masivo') }}" method="POST" id="form-clonar-masivo">
            @csrf
            <div id="contenedor-ids-clonar"></div>

            <div class="alert alert-info">
                <h4><i class="icon fa fa-info"></i> Instrucciones</h4>
                Se crearán copias exactas de los planes seleccionados (incluyendo sus conceptos y políticas) en el ciclo
                escolar de destino escogido.
            </div>

            <div class="form-group">
                <label>Ciclo Escolar Destino</label>
                <select name="ciclo_destino_id" class="form-control" required>
                    <option value="">Seleccione el ciclo destino...</option>
                    @foreach ($ciclosDisponibles as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Sufijo para los nombres (Opcional)</label>
                <input type="text" name="sufijo" class="form-control" placeholder="Ej: - COPIA ">
            </div>

            <div class="modal-footer no-padding">
                <button type="submit" class="btn btn-primary pull-right">Comenzar Clonación</button>
            </div>
        </form>
    </x-modal>

    {{-- ══ MODAL AYUDA ══ --}}
    <x-modal id="modalAyuda" title="¿Cómo usar esta pantalla?" size="modal-md">
        <div style="line-height:1.7;">

            <p style="font-size:13px;color:#555;margin-bottom:16px;">
                Esta pantalla te permite gestionar los <strong>planes de pago</strong>
                que ofreces a tus estudiantes. Un plan de pago es un conjunto de conceptos (colegiaturas, inscripciones,
                cargos) con una vigencia determinada. Puedes crear planes diferentes para cada nivel educativo y
                configurarlos.
            </p>

            <div style="display:flex;flex-direction:column;gap:12px;">

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-filter" style="color:#3c8dbc;margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Filtros</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Puedes filtrar los planes por nivel educativo usando el menú desplegable. El botón de "X" limpia
                            los filtros y muestra todos los planes.
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-copy" style="color:#3c8dbc;margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Clonar</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Puedes seleccionar uno o varios planes usando las casillas de verificación y luego hacer clic en
                            "Clonar seleccionados" para crear copias exactas en otro ciclo escolar. Esto es útil para
                            reutilizar configuraciones sin tener que crearlas desde cero.
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-plus-circle" style="color:#00a65a;margin-top:2px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Nuevo plan</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Abre el formulario para crear un plan de pago. Puedes seleccionar conceptos de cobro para
                            incluir en el plan. Ademas puedes configurar descuentos por pronto pago y recargos por mora.
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <span class="caret" style="color:#f39c12;margin-top:10px;flex-shrink:0;"></span>
                    <div>
                        <strong style="font-size:13px;">Ver Opciones: Ver / Editar / Desactivar / Configurar</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Usa el menú de acciones <span class="caret"></span> en cada fila.
                            <br>
                            <strong>Ver resumen</strong>: Muestra un resumen con los detalles del plan.
                            <br>
                            <strong>Editar</strong>: Permite modificar el nombre, fechas y estatus del plan.
                            <br>
                            <strong>Configurar</strong>: Aquí puedes agregar o quitar conceptos, y configurar descuentos
                            y recargos.
                            <br>
                            <strong>Desactivar/Reactivar</strong>: Cambia el estatus del plan. Los planes inactivos no se
                            mostrarán como opción de pago para los estudiantes, pero se mantienen en el sistema por si
                            deseas reactivarlos en el futuro.
                        </p>
                    </div>
                </div>

            </div>

            <hr style="margin:16px 0 10px;">

        </div>

        <div class="clearfix" style="padding-bottom:4px;">
            <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                <i class="fa fa-times"></i> Cerrar
            </button>
        </div>
    </x-modal>

@endsection

@push('scripts')
    {{-- Solo traemos el MOTOR de Select2, sin los estilos, para respetar tu AdminLTE --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Redireccionar al resumen al hacer click en la fila
            $('#tabla-planes tbody').on('click', 'tr', function(e) {
                // Si el usuario hace click en un checkbox, un botón, un enlace o dentro del dropdown, ignoramos la redirección
                if ($(e.target).closest('input[type="checkbox"], .dropdown, .pln-acciones, a, button')
                    .length) {
                    return;
                }

                // Obtenemos la URL guardada en el data-href de la fila
                let url = $(this).data('href');
                if (url) {
                    window.location = url;
                }
            });

            // ── Conceptos en modal ───────────────────────────────
            let indiceConcepto = Date.now();

            // Función maestra BLINDADA para inicializar Select2
            function activarSelect2(selector) {
                // EL ESCUDO: Solo se ejecuta si la librería cargó con éxito
                if ($.fn.select2) {
                    $(selector).select2({
                        placeholder: "Seleccione un concepto...",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#modalNuevoPlan')
                    });
                } else {
                    console.warn("Select2 no detectado. Usando select nativo para evitar caída del sistema.");
                }
            }

            // 1. Inicializar los selects que ya existen al cargar la página
            activarSelect2('.select2-busqueda');

            // Botón Añadir Concepto
            $('#btn-agregar-concepto').click(function() {
                $('#mensaje-vacio-modal').hide();

                // 2. Inyectamos la fila
                let fila = `<tr id="fila-concepto-${indiceConcepto}">
            <td><select name="conceptos[${indiceConcepto}][concepto_id]" class="form-control input-sm select-concepto-magico select2-busqueda" style="width: 100%;" required>
                <option value="">Seleccione...</option>
                @foreach ($conceptos->sortBy('nombre') as $c)<option value="{{ $c->id }}" data-monto="{{ $c->monto }}">{{ $c->nombre }}</option>@endforeach
            </select></td>
            <td><input type="number" step="0.01" min="0" name="conceptos[${indiceConcepto}][monto]" class="form-control input-sm input-monto-magico" required></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-eliminar-fila" data-id="${indiceConcepto}"><i class="fa fa-trash"></i></button></td>
        </tr>`;

                $('#tabla-conceptos-modal tbody').append(fila);

                // 3. Inicializamos Select2 para el elemento nuevo
                activarSelect2(`#fila-concepto-${indiceConcepto} .select2-busqueda`);

                indiceConcepto++;
            });

            // Eliminar fila de concepto
            $('#tabla-conceptos-modal').on('click', '.btn-eliminar-fila', function() {
                $('#fila-concepto-' + $(this).data('id')).remove();
                if ($('#tabla-conceptos-modal tbody tr').length === 0) $('#mensaje-vacio-modal').show();
            });

            // ── AUTO-COMPLETAR MONTO DEL CONCEPTO ──
            $('#tabla-conceptos-modal').on('change', '.select-concepto-magico', function() {
                let monto = $(this).find(':selected').data('monto');
                let inputMonto = $(this).closest('tr').find('.input-monto-magico');

                if (monto !== undefined && monto !== '') {
                    inputMonto.val(monto);
                } else {
                    inputMonto.val('');
                }
            });

            // ── Descuentos en modal ──────────────────────────────
            let indiceDesc = Date.now();

            $('#btn-add-descuento').click(function() {
                $('.header-descuentos').show();
                let html = `<div class="row fila-desc" id="fila-desc-${indiceDesc}" style="margin-bottom: 8px;">
            <div class="col-md-3"><input type="text" name="descuentos[${indiceDesc}][nombre]" class="form-control input-sm" placeholder="Nombre" required></div>
            <div class="col-md-3"><select name="descuentos[${indiceDesc}][tipo_valor]" class="form-control input-sm"><option value="porcentaje">%</option><option value="monto_fijo">$</option></select></div>
            <div class="col-md-3"><input type="number" step="0.01" min="0" name="descuentos[${indiceDesc}][valor]" class="form-control input-sm" required placeholder="0.00"></div>
            <div class="col-md-2"><input type="number" min="1" max="31" name="descuentos[${indiceDesc}][dia_limite]" class="form-control input-sm" placeholder="Día"></div>
            <div class="col-md-1 text-right"><button type="button" class="btn btn-danger btn-xs btn-remove-desc" data-id="${indiceDesc}"><i class="fa fa-times"></i></button></div>
        </div>`;
                $('#contenedor-descuentos').append(html);
                indiceDesc++;
            });

            $('#contenedor-descuentos').on('click', '.btn-remove-desc', function() {
                $('#fila-desc-' + $(this).data('id')).remove();
            });

            // ── Selección masiva y Clonación ─────────────────────
            $('#select-all-planes').click(function() {
                $('.plan-checkbox').prop('checked', this.checked);
                actualizarBotonClonar();
            });

            $(document).on('change', '.plan-checkbox', function() {
                actualizarBotonClonar();
            });

            function actualizarBotonClonar() {
                let n = $('.plan-checkbox:checked').length;
                let boton = $('#btn-clonar-masivo');
                let badge = $('#badge-seleccionados');
                if (n > 0) {
                    boton.prop('disabled', false).addClass('btn-warning').removeClass('btn-default');
                    badge.show().find('#txt-seleccionados').text(n);
                } else {
                    boton.prop('disabled', true).addClass('btn-default').removeClass('btn-warning');
                    badge.hide();
                }
            }

            $('#form-clonar-masivo').submit(function() {
                let contenedor = $('#contenedor-ids-clonar');
                contenedor.empty();
                $('.plan-checkbox:checked').each(function() {
                    contenedor.append(
                        `<input type="hidden" name="plan_ids[]" value="${$(this).val()}">`);
                });
            });

            // ── Auto-abrir modal si hay errores de validación ──
            @if ($errors->any())
                setTimeout(function() {
                    $('#modalNuevoPlan').modal('show');
                }, 300);
            @endif
        });
    </script>
@endpush
