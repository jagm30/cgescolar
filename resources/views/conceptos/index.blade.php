@extends('layouts.master')

@section('page_title', 'Conceptos')
@section('page_subtitle', 'Conceptos de cobro')

@section('breadcrumb')
    <li class="active">Conceptos</li>
@endsection

@push('styles')
    <style>
        /* ══════════════════════════════════════════
                                                                                                                       ESTADÍSTICAS
                                                                                                                    ══════════════════════════════════════════ */
        .con-stats {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .con-stat-card {
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

        .con-stat-card.verde {
            border-top-color: #00a65a;
        }

        .con-stat-card.rojo {
            border-top-color: #dd4b39;
        }

        .con-stat-card.naranja {
            border-top-color: #f39c12;
        }

        .con-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf3fb;
            flex-shrink: 0;
        }

        .con-stat-icon.verde {
            background: #e8f8f0;
        }

        .con-stat-icon.rojo {
            background: #fdecea;
        }

        .con-stat-icon.naranja {
            background: #fef6e7;
        }

        .con-stat-num {
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
            color: #222;
        }

        .con-stat-lbl {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ══════════════════════════════════════════
                                                                                                                       TOOLBAR
                                                                                                                    ══════════════════════════════════════════ */
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

        .con-search-wrap {
            flex: 1;
            min-width: 200px;
            max-width: 360px;
            position: relative;
        }

        .con-search-wrap .form-control {
            padding-left: 38px;
            border-radius: 20px !important;
            border: 1px solid #d0dbe6;
            height: 36px;
            font-size: 13px;
            background: #fff;
            box-shadow: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .con-search-wrap .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, .12);
        }

        .con-search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab;
            font-size: 14px;
            pointer-events: none;
        }

        .con-search-clear {
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

        .con-search-clear:hover {
            color: #dd4b39;
        }

        .con-select {
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

        .con-count-badge {
            background: #e8f0fb;
            color: #3c8dbc;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ══════════════════════════════════════════
                                                                                                                       TABLA
                                                                                                                    ══════════════════════════════════════════ */
        .con-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .con-table thead tr th {
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

        .con-table tbody tr {
            border-bottom: 1px solid #f0f3f7;
            transition: background .1s;
        }

        .con-table tbody tr:last-child {
            border-bottom: none;
        }

        .con-table tbody tr:hover td {
            background: #f0f7ff !important;
        }

        .con-table td {
            padding: 10px 14px;
            vertical-align: middle;
            font-size: 13px;
        }

        /* Nombre */
        .con-nombre {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }

        .con-sub {
            font-size: 11px;
            color: #aab;
            margin-top: 2px;
        }

        .con-clave {
            font-family: monospace;
            font-size: 12px;
            background: #f0f3f7;
            padding: 2px 7px;
            border-radius: 4px;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        /* Tipo badge */
        .con-badge {
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

        .con-badge-colegiatura {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
        }

        .con-badge-inscripcion {
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #b3d4f5;
        }

        .con-badge-cargo_unico {
            background: #fff8e6;
            color: #b45309;
            border: 1px solid #fcd97d;
        }

        .con-badge-cargo_recurrente {
            background: #fdecea;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .con-badge-activo {
            background: #e8f8f0;
            color: #00875a;
            border: 1px solid #b3e8d0;
        }

        .con-badge-inactivo {
            background: #f4f6f8;
            color: #7a8898;
            border: 1px solid #d0d9e2;
        }

        /* Check / times icons */
        .con-bool-yes {
            color: #00a65a;
            font-size: 15px;
        }

        .con-bool-no {
            color: #ccc;
            font-size: 15px;
        }

        /* Acciones */
        .con-acciones {
            display: flex;
            gap: 4px;
        }

        /* ══════════════════════════════════════════
                                                                                                                       EMPTY STATE
                                                                                                                    ══════════════════════════════════════════ */
        .con-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .con-empty i {
            font-size: 52px;
            display: block;
            margin-bottom: 16px;
            color: #dde4ea;
        }

        .con-empty h4 {
            font-size: 16px;
            color: #999;
            margin: 0 0 8px;
        }

        .con-empty p {
            font-size: 13px;
            color: #bbb;
            margin: 0 0 20px;
        }
    </style>
@endpush

@section('content')
    {{-- ══ ESTADÍSTICAS ══ --}}
    <div class="con-stats">
        <div class="con-stat-card">
            <div class="con-stat-icon">
                <i class="fa fa-tags" style="color:#3c8dbc;font-size:18px;"></i>
            </div>
            <div>
                <div class="con-stat-num">{{ $totales->count() }}</div>
                <div class="con-stat-lbl">Total conceptos</div>
            </div>
        </div>
        <div class="con-stat-card verde">
            <div class="con-stat-icon verde">
                <i class="fa fa-check-circle" style="color:#00a65a;font-size:18px;"></i>
            </div>
            <div>
                <div class="con-stat-num">{{ $totales->where('activo', true)->count() }}</div>
                <div class="con-stat-lbl">Activos</div>
            </div>
        </div>
        <div class="con-stat-card rojo">
            <div class="con-stat-icon rojo">
                <i class="fa fa-ban" style="color:#dd4b39;font-size:18px;"></i>
            </div>
            <div>
                <div class="con-stat-num">{{ $totales->where('activo', false)->count() }}</div>
                <div class="con-stat-lbl">Inactivos</div>
            </div>
        </div>
        <div class="con-stat-card naranja">
            <div class="con-stat-icon naranja">
                <i class="fa fa-list-alt" style="color:#f39c12;font-size:18px;"></i>
            </div>
            <div>
                <div class="con-stat-num">{{ $totales->pluck('tipo')->unique()->count() }}</div>
                <div class="con-stat-lbl">Tipos distintos</div>
            </div>
        </div>
    </div>

    {{-- ══ PANEL PRINCIPAL ══ --}}
    <div class="box"
        style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

        {{-- Toolbar ─────────────────────────────────── --}}
        <form method="GET" action="{{ route('conceptos.index') }}" id="form-filtros">
            <div class="con-toolbar">

                {{-- Búsqueda --}}
                <div class="con-search-wrap">
                    <i class="fa fa-search con-search-icon"></i>
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar concepto…"
                        value="{{ request('buscar') }}" autocomplete="off">
                    @if (request('buscar'))
                        <a href="{{ route('conceptos.index', request()->except('buscar')) }}" class="con-search-clear"
                            title="Limpiar">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    @endif
                </div>

                {{-- Filtro tipo --}}
                <select name="tipo" class="con-select" onchange="this.form.submit()" title="Filtrar por tipo">
                    <option value="">Todos los tipos</option>
                    <option value="colegiatura" {{ request('tipo') == 'colegiatura' ? 'selected' : '' }}>Colegiatura
                    </option>
                    <option value="inscripcion" {{ request('tipo') == 'inscripcion' ? 'selected' : '' }}>Inscripción
                    </option>
                    <option value="cargo_unico" {{ request('tipo') == 'cargo_unico' ? 'selected' : '' }}>Cargo único
                    </option>
                    <option value="cargo_recurrente" {{ request('tipo') == 'cargo_recurrente' ? 'selected' : '' }}>Cargo
                        recurrente</option>
                </select>

                {{-- Filtro estatus dropdown --}}
                <select name="activo" class="con-select" onchange="this.form.submit()" title="Filtrar por estatus">
                    <option value="">Todos los estatus</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>


                {{-- Filtro numero de concepto por pagina --}}
                <select name="perPage" class="con-select" onchange="this.form.submit()" title="Elementos por página">
                    @foreach ([5, 10, 25, 50, 100] as $op)
                        <option value="{{ $op }}" {{ request('perPage', 10) == $op ? 'selected' : '' }}>
                            {{ $op }} / pág.
                        </option>
                    @endforeach
                </select>


                {{-- Limpiar filtros --}}
                @if (request()->anyFilled(['buscar', 'tipo', 'activo', 'perPage']))
                    <a href="{{ route('conceptos.index') }}" class="btn btn-default btn-flat btn-sm"
                        style="border-radius:20px;padding:5px 14px;flex-shrink:0;" title="Quitar todos los filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif

                {{-- Nuevo concepto --}}
                <button type="button" class="btn btn-success btn-flat btn-sm"
                    style="border-radius:20px;padding:5px 14px;white-space:nowrap;flex-shrink:0;margin-left:auto;"
                    data-toggle="modal" data-target="#modalNuevoConcepto">
                    <i class="fa fa-plus"></i> Nuevo concepto
                </button>

            </div>
        </form>

        {{-- Tabla ───────────────────────────────────── --}}
        <div class="box-body no-padding">
            <table class="con-table">
                <thead>
                    <tr>
                        <th style="width:30%;">Concepto</th>
                        <th style="width:12%; text-align:right;">Monto base</th>
                        <th style="width:13%;">Tipo</th>
                        <th style="width:8%; text-align:center;">Beca</th>
                        <th style="width:8%; text-align:center;">Recargo</th>
                        <th style="width:10%;">Clave SAT</th>
                        <th style="width:8%;">Estatus</th>
                        <th style="width:11%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conceptos as $concepto)
                        <tr>
                            {{-- CONCEPTO --}}
                            <td>
                                <div class="con-nombre">{{ $concepto->nombre }}</div>
                            </td>

                            {{-- MONTO --}}
                            <td class="text-right">
                                @if ($concepto->monto)
                                    <span
                                        style="font-weight:bold; color:#00a65a;">${{ number_format($concepto->monto, 2) }}</span>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>

                            {{-- TIPO --}}
                            <td>
                                <span class="con-badge con-badge-{{ $concepto->tipo }}">
                                    {{ ucfirst(str_replace('_', ' ', $concepto->tipo)) }}
                                </span>
                            </td>

                            {{-- APLICA BECA --}}
                            <td class="text-center">
                                @if ($concepto->aplica_beca)
                                    <i class="fa fa-check-circle con-bool-yes" title="Aplica beca"></i>
                                @else
                                    <i class="fa fa-times-circle con-bool-no" title="No aplica beca"></i>
                                @endif
                            </td>

                            {{-- APLICA RECARGO --}}
                            <td class="text-center">
                                @if ($concepto->aplica_recargo)
                                    <i class="fa fa-check-circle con-bool-yes" title="Aplica recargo"></i>
                                @else
                                    <i class="fa fa-times-circle con-bool-no" title="No aplica recargo"></i>
                                @endif
                            </td>

                            {{-- CLAVE SAT --}}
                            <td>
                                @if ($concepto->clave_sat)
                                    <span class="con-clave">{{ $concepto->clave_sat }}</span>
                                @else
                                    <span style="font-size:12px;color:#ccc;font-style:italic;">—</span>
                                @endif
                            </td>

                            {{-- ESTATUS --}}
                            <td>
                                <span
                                    class="con-badge {{ $concepto->activo ? 'con-badge-activo' : 'con-badge-inactivo' }}">
                                    <i class="fa fa-circle" style="font-size:7px;"></i>
                                    {{ $concepto->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="con-acciones">
                                    <button type="button" class="btn btn-primary btn-xs btn-flat"
                                        style="border-radius:4px;" data-toggle="modal"
                                        data-target="#modalEditarConcepto{{ $concepto->id }}" title="Editar">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    @if ($concepto->activo)
                                        <form action="{{ route('conceptos.destroy', $concepto->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs btn-flat"
                                                style="border-radius:4px;"
                                                onclick="return confirm('¿Desactivar el concepto: {{ $concepto->nombre }}?')"
                                                title="Desactivar">
                                                <i class="fa fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('conceptos.update', $concepto->id) }}" method="POST"
                                            style="display:inline-block;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="activo" value="1">
                                            <button type="submit" class="btn btn-success btn-xs btn-flat"
                                                style="border-radius:4px;"
                                                onclick="return confirm('¿Activar el concepto: {{ $concepto->nombre }}?')"
                                                title="Activar">
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="con-empty">
                                    <i class="fa fa-tags"></i>
                                    @if (request()->anyFilled(['buscar', 'tipo', 'activo']))
                                        <h4>Sin resultados</h4>
                                        <p>No se encontraron conceptos con los filtros aplicados.</p>
                                        <a href="{{ route('conceptos.index') }}" class="btn btn-default btn-sm"
                                            style="border-radius:20px;">
                                            <i class="fa fa-times"></i> Quitar filtros
                                        </a>
                                    @else
                                        <h4>No hay conceptos registrados</h4>
                                        <p>Crea el primer concepto de cobro.</p>
                                        <button type="button" class="btn btn-success btn-sm" style="border-radius:20px;"
                                            data-toggle="modal" data-target="#modalNuevoConcepto">
                                            <i class="fa fa-plus"></i> Nuevo concepto
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div> {{-- ══ CONTROLES DE PAGINACIÓN ENTERPRISE ══ --}}
        {{-- ══ CONTROLES DE PAGINACIÓN ENTERPRISE ══ --}}
        @if ($conceptos->hasPages())
            <style>
                /* Forzamos a que Bootstrap no le ponga márgenes gigantes a los botones */
                .con-pagination-wrap .pagination {
                    margin: 0 !important;
                }

                .con-pagination-wrap .page-link {
                    padding: 5px 10px;
                    /* Hacemos los botones un poco más sutiles */
                    font-size: 13px;
                }
            </style>

            <div class="box-footer con-pagination-wrap"
                style="border-top: 1px solid #f0f3f7; padding: 10px 15px; background: #fff; display: flex; justify-content: space-between; align-items: center;">
                <div class="text-muted" style="font-size: 13px;">
                    Mostrando <b>{{ $conceptos->firstItem() }}</b> a <b>{{ $conceptos->lastItem() }}</b> de
                    <b>{{ $conceptos->total() }}</b> conceptos
                </div>
                <div>
                    {{ $conceptos->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
    </div>

    </div>

    {{-- ══ MODAL NUEVO ══ --}}
    <x-modal id="modalNuevoConcepto" title=" Agregar Nuevo Concepto" size="modal-lg">
        <form action="{{ route('conceptos.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-tag"></i> Nombre del Concepto</label>
                        <input type="text" name="nombre" class="form-control"
                            placeholder="Ej: Inscripción Semestral" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-list"></i> Tipo</label>
                        <select name="tipo" class="form-control select-tipo-dinamico" required>
                            <option value="">Seleccione un tipo...</option>
                            <option value="colegiatura">Colegiatura</option>
                            <option value="inscripcion">Inscripción</option>
                            <option value="cargo_unico">Cargo Unico</option>
                            <option value="cargo_recurrente">Cargo Recurrente</option>
                        </select>
                    </div>
                    <div class="form-group div-monto-dinamico" style="display: none;">
                        <label><i class="fa fa-dollar"></i> Monto Base (Opcional)</label>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" step="0.01" min="0" name="monto" class="form-control"
                                placeholder="0.00">
                        </div>
                        <small class="text-muted">Si se deja en blanco, se deberá definir al crear el plan.</small>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-key"></i> Clave SAT</label>
                        <input type="text" name="clave_sat" class="form-control" maxlength="8"
                            placeholder="Clave de producto o servicio">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-align-left"></i> Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Breve descripción del concepto..."></textarea>
                    </div>

                    <div class="well well-sm">
                        <label style="display: block; margin-bottom: 10px;">
                            <strong>Configuraciones adicionales:</strong>
                        </label>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="aplica_beca" class="checkbox-beca-dinamico">
                                <span class="text-primary">Aplica para beca</span>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="aplica_recargo">
                                <span class="text-warning">Aplica recargo por mora</span>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="activo" checked>
                                <span class="label label-success">Estatus Activo</span>
                            </label>
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
                    <i class="fa fa-save"></i> Guardar Concepto
                </button>
            </div>
        </form>
    </x-modal>

    {{-- ══ MODALES EDITAR ══ --}}
    @foreach ($conceptos as $concepto)
        <x-modal id="modalEditarConcepto{{ $concepto->id }}" title="Editar Concepto: {{ $concepto->nombre }}"
            size="modal-lg">
            <form action="{{ route('conceptos.update', $concepto->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fa fa-tag"></i> Nombre del Concepto</label>
                            <input type="text" name="nombre" class="form-control" value="{{ $concepto->nombre }}"
                                required>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-list"></i> Tipo</label>
                            <select name="tipo" class="form-control select-tipo-dinamico" required>
                                <option value="colegiatura" {{ $concepto->tipo == 'colegiatura' ? 'selected' : '' }}>
                                    Colegiatura</option>
                                <option value="inscripcion" {{ $concepto->tipo == 'inscripcion' ? 'selected' : '' }}>
                                    Inscripción</option>
                                <option value="cargo_unico" {{ $concepto->tipo == 'cargo_unico' ? 'selected' : '' }}>Cargo
                                    Unico</option>
                                <option value="cargo_recurrente"
                                    {{ $concepto->tipo == 'cargo_recurrente' ? 'selected' : '' }}>Cargo Recurrente</option>
                            </select>
                        </div>
                        <div class="form-group div-monto-dinamico"
                            style="display: {{ in_array($concepto->tipo, ['cargo_unico', 'cargo_recurrente']) ? 'block' : 'none' }};">
                            <label><i class="fa fa-dollar"></i> Monto Base (Opcional)</label>
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                <input type="number" step="0.01" min="0" name="monto" class="form-control"
                                    value="{{ $concepto->monto }}" placeholder="0.00">
                            </div>
                            <small class="text-muted">Si se deja en blanco, se deberá definir al crear el plan.</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fa fa-key"></i> Clave SAT</label>
                            <input type="text" name="clave_sat" class="form-control" maxlength="8"
                                value="{{ $concepto->clave_sat }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fa fa-align-left"></i> Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="4">{{ $concepto->descripcion }}</textarea>
                        </div>

                        <div class="well well-sm">
                            <label style="display: block; margin-bottom: 10px;">
                                <strong>Configuraciones adicionales:</strong>
                            </label>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="aplica_beca" class="checkbox-beca-dinamico"
                                        {{ $concepto->aplica_beca ? 'checked' : '' }}>
                                    <span class="text-primary">Aplica para beca</span>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="aplica_recargo"
                                        {{ $concepto->aplica_recargo ? 'checked' : '' }}>
                                    <span class="text-warning">Aplica recargo por mora</span>
                                </label>
                            </div>

                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="activo" {{ $concepto->activo ? 'checked' : '' }}>
                                    <span class="label {{ $concepto->activo ? 'label-success' : 'label-danger' }}">
                                        Estatus {{ $concepto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="margin-top: 10px; margin-bottom: 15px;">

                <div class="clearfix" style="padding-bottom: 10px;">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning pull-right">
                        <i class="fa fa-refresh"></i> Actualizar Concepto
                    </button>
                </div>
            </form>
        </x-modal>
    @endforeach

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Lógica dinámica del select de tipo (Agregamos la 'e' de evento)
            $('.select-tipo-dinamico').on('change', function(e) {
                let searchTimer;
                document.querySelector('input[name="buscar"]').addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => this.closest('form').submit(), 500);
                });

                let valorSeleccionado = $(this).val();
                let form = $(this).closest('form');

                // Atrapamos todos los elementos
                let checkboxBeca = form.find('.checkbox-beca-dinamico');
                let checkboxRecargo = form.find('input[name="aplica_recargo"]');
                let checkboxActivo = form.find('input[name="activo"]');
                let divMonto = form.find('.div-monto-dinamico');
                let inputMonto = form.find('input[name="monto"]');
                let inputClaveSat = form.find('input[name="clave_sat"]'); // <-- Atrapamos el input SAT

                // ── LÓGICA DE CHECKBOXES ──
                if (valorSeleccionado === 'colegiatura') {
                    // 1. La beca siempre se habilita si es colegiatura
                    checkboxBeca.prop('checked', true).prop('disabled', false);

                    // 2. TRUCO DE INGENIERO: Solo forzamos los otros checks si el usuario cambió el select manualmente.
                    if (!e.isTrigger) {
                        checkboxRecargo.prop('checked', true);
                        checkboxActivo.prop('checked', true);
                    }
                } else {
                    // Si no es colegiatura, se apaga y bloquea la beca.
                    checkboxBeca.prop('checked', false).prop('disabled', true);
                }

                // ── LÓGICA DE CLAVE SAT AUTOMÁTICA ──
                // Solo rellenamos si el evento viene de un clic humano (para no dañar la edición)
                if (!e.isTrigger) {
                    if (valorSeleccionado === 'colegiatura' || valorSeleccionado === 'inscripcion') {
                        inputClaveSat.val('86121500');
                    } else {
                        inputClaveSat.val(''); // Limpiamos si elige un cargo distinto
                    }
                }

                // ── LÓGICA DE MONTO ──
                if (valorSeleccionado === 'cargo_unico' || valorSeleccionado === 'cargo_recurrente') {
                    divMonto.slideDown();
                } else {
                    divMonto.slideUp();
                    if (!e.isTrigger) {
                        inputMonto.val(''); // Limpiamos el monto si elige otro tipo
                    }
                }
            });

            // Ejecutar al cargar para modales de edición (asegura que se muestre correcto al abrir)
            $('.select-tipo-dinamico').trigger('change');

            // ── EL TRUCO DEL FUGITIVO: Evita que la tabla corte los dropdowns ──
            $('table').on('show.bs.dropdown', function(e) {
                var $dropdownContainer = $(e.target);
                var $menu = $dropdownContainer.find('.dropdown-menu');

                $('body').append($menu.detach());
                var buttonOffset = $dropdownContainer.offset();

                $menu.css({
                    'display': 'block',
                    'top': buttonOffset.top + $dropdownContainer.outerHeight(),
                    'left': buttonOffset.left - $menu.outerWidth() + $dropdownContainer
                        .outerWidth(),
                    'position': 'absolute',
                    'z-index': 999999
                });

                $dropdownContainer.data('menu-fugitivo', $menu);
            });

            $('table').on('hide.bs.dropdown', function(e) {
                var $dropdownContainer = $(e.target);
                var $menu = $dropdownContainer.data('menu-fugitivo');

                if ($menu) {
                    $dropdownContainer.append($menu.detach());
                    $menu.css({
                        'display': '',
                        'top': '',
                        'left': '',
                        'position': '',
                        'z-index': ''
                    });
                }
            });
        });
    </script>
@endpush
