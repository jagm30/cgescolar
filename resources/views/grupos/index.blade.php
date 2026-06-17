@extends('layouts.master')
@section('page_title', 'Gestión de Grupos Escolares')

@section('breadcrumb')
    <li class="active">Grupos</li>
@endsection

@push('styles')
    <style>
        /* ══ TOOLBAR ══ */
        .grp-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-bottom: 1px solid #e8ecf0;
            background: #f9fafb;
            flex-wrap: wrap;
        }

        .grp-select {
            height: 32px !important;
            border-radius: 6px !important;
            border: 1px solid #d0dbe6 !important;
            font-size: 12px !important;
            color: #555 !important;
            padding: 0 8px !important;
            background: #fff !important;
            min-width: 120px;
            max-width: 160px;
            box-shadow: none !important;
        }

        .grp-search-wrap {
            position: relative;
            min-width: 180px;
            flex: 2;
        }

        .grp-search-wrap .form-control {
            padding-left: 32px;
            border-radius: 20px !important;
            border: 1px solid #d0dbe6;
            height: 32px;
            font-size: 13px;
            background: #fff;
            box-shadow: none;
        }

        .grp-search-wrap .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: 0 0 0 3px rgba(60,141,188,.12);
        }

        .grp-search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #aab;
            font-size: 13px;
            pointer-events: none;
        }

        /* ══ TABLA ══ */
        .grp-container {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e0e7ef;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
        }

        .grp-table { margin: 0; width: 100%; border-collapse: separate; border-spacing: 0; }

        .grp-table thead th {
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

        .grp-table tbody tr { border-bottom: 1px solid #f0f3f7; cursor: pointer; }
        .grp-table tbody tr:last-child { border-bottom: none; }
        .grp-table tbody tr:hover td { background: #f0f7ff !important; }

        .grp-table td {
            padding: 9px 14px;
            vertical-align: middle;
            font-size: 13px;
        }

        /* Identificador badge */
        .grp-badge {
            display: inline-flex;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            background: #e8f3ff;
            color: #2c6fad;
            border: 1px solid #c9e3ff;
        }

        /* Acciones */
        .grp-acciones {
            display: flex;
            align-items: center;
            gap: 4px;
            justify-content: center;
        }

        .grp-acciones form { display: contents; }

        /* Footer paginación */
        .grp-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 15px;
            background: #fff;
            border-top: 1px solid #f0f3f7;
            flex-wrap: wrap;
            gap: 8px;
        }

        .grp-footer-info { font-size: 13px; color: #6b7a8d; }

        /* Panel de ayuda */
        .grp-help {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
        }

        .grp-help-header {
            padding: 10px 14px;
            background: #f4f6f8;
            border-bottom: 2px solid #e0e6ed;
        }

        .grp-help-header h4 {
            margin: 0;
            font-size: 12px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .grp-help-body {
            padding: 12px 14px;
            font-size: 12px;
            color: #6b7a8d;
            line-height: 1.7;
        }

        .grp-help-body p { margin-bottom: 4px; }
        .grp-help-body hr { margin: 8px 0; border-color: #e0e7ef; }

        /* Suprimir flechas DataTables */
        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_desc::before,
        table.dataTable thead .sorting_desc::after {
            display: none !important;
            content: none !important;
        }
    </style>
@endpush

@section('content')

    {{-- ══ ENCABEZADO + STATS ══ --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-users text-blue"></i> Grupos Escolares
            </h4>
            <div style="display:flex;gap:7px;flex-wrap:wrap;">
                <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-th-large"></i> {{ $grupos->total() }} grupos
                </span>
                <span style="background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-check-circle"></i> {{ $grupos->where('activo', true)->count() }} activos
                </span>
                <span style="background:#fef6e7;color:#b45309;border:1px solid #fcd97d;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-graduation-cap"></i> {{ $grados->count() }} grados
                </span>
                <span style="background:#f5eef8;color:#7d3c98;border:1px solid #ebdef0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-calendar"></i> {{ $ciclo->nombre }}
                </span>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            @if ($grupos->count() > 0)
                <button type="button" class="btn btn-primary btn-sm btn-flat" style="border-radius:20px;"
                    data-toggle="modal" data-target="#modalMigrarGrupos">
                    <i class="fa fa-copy"></i> Migrar estructura
                </button>
            @endif
            <button type="button" class="btn btn-success btn-sm btn-flat" style="border-radius:20px;"
                data-toggle="modal" data-target="#modalNuevoGrupo">
                <i class="fa fa-plus"></i> Nuevo grupo
            </button>
        </div>
    </div>

    {{-- ══ PANEL PRINCIPAL ══ --}}
    <div class="row">
        <div class="col-md-9">
            <div class="grp-container">

                {{-- Toolbar filtros --}}
                <form method="GET" action="{{ route('grupos.index') }}">
                    <div class="grp-toolbar">
                        <select name="nivel_id" class="grp-select" onchange="this.form.submit()" title="Nivel">
                            <option value="">Todos los niveles</option>
                            @foreach ($niveles as $n)
                                <option value="{{ $n->id }}" {{ request('nivel_id') == $n->id ? 'selected' : '' }}>
                                    {{ $n->nombre }}
                                </option>
                            @endforeach
                        </select>

                        <select name="grado_id" class="grp-select" onchange="this.form.submit()" title="Grado">
                            <option value="">Todos los grados</option>
                            @foreach ($grados as $g)
                                @if (!request('nivel_id') || $g->nivel_id == request('nivel_id'))
                                    <option value="{{ $g->id }}" {{ request('grado_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->numero }}°
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        <select name="estatus" class="grp-select" onchange="this.form.submit()" title="Estatus">
                            <option value="activos"  {{ request('estatus') != 'inactivos' && request('estatus') != 'todos' ? 'selected' : '' }}>Activos</option>
                            <option value="inactivos" {{ request('estatus') == 'inactivos' ? 'selected' : '' }}>Inactivos</option>
                            <option value="todos"     {{ request('estatus') == 'todos'     ? 'selected' : '' }}>Todos</option>
                        </select>

                        <div class="grp-search-wrap">
                            <i class="fa fa-search grp-search-icon"></i>
                            <input type="text" id="buscador-manual" class="form-control"
                                   placeholder="Buscar grupo…">
                        </div>

                        @if (request()->anyFilled(['nivel_id', 'grado_id', 'estatus']))
                            <a href="{{ route('grupos.index') }}" class="btn btn-default btn-sm btn-flat"
                               style="border-radius:20px;padding:4px 12px;flex-shrink:0;" title="Limpiar filtros">
                                <i class="fa fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>

                {{-- Tabla --}}
                <table id="tabla-grupos" class="grp-table">
                    <thead>
                        <tr>
                            <th>Nivel / Grado</th>
                            <th>Identificador</th>
                            <th class="text-center">Cupo</th>
                            <th class="text-center">Inscritos</th>
                            <th class="text-center">Disponibles</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grupos as $g)
                            <tr data-href="{{ route('grupos.show', $g['id']) }}">
                                <td>
                                    <span style="font-weight:700;color:#1a2634;">{{ $g['grado']['nivel']['nombre'] }}</span><br>
                                    <small class="text-muted">{{ $g['grado']['numero'] }}°</small>
                                </td>
                                <td>
                                    <span class="grp-badge">{{ $g['nombre'] }}</span>
                                </td>
                                <td class="text-center">{{ $g['cupo_maximo'] ?? '∞' }}</td>
                                <td class="text-center">
                                    @if ($g['alumnos_inscritos'] == 0)
                                        <span class="badge bg-gray">0</span>
                                    @elseif ($g['cupo_maximo'] > 0 && $g['alumnos_inscritos'] >= $g['cupo_maximo'])
                                        <span class="badge bg-red">{{ $g['alumnos_inscritos'] }}</span>
                                    @else
                                        <span class="badge bg-blue">{{ $g['alumnos_inscritos'] }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($g['disponibles'] !== null)
                                        <b class="text-{{ $g['disponibles'] <= 5 ? 'red' : 'green' }}">{{ $g['disponibles'] }}</b>
                                    @else
                                        <span class="text-muted">∞</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="grp-acciones">
                                        <button type="button" class="btn btn-default btn-xs btn-abrir-modal-credencial"
                                            data-id="{{ $g['id'] }}" data-tipo="lote" title="Imprimir lote">
                                            <i class="fa fa-id-card text-yellow"></i>
                                        </button>
                                        <a href="{{ route('grupos.show', $g['id']) }}" class="btn btn-default btn-xs"
                                            title="Ver alumnos">
                                            <i class="fa fa-users text-blue"></i>
                                        </a>
                                        <button class="btn btn-default btn-xs" data-toggle="modal"
                                            data-target="#modalEditarGrupo{{ $g['id'] }}" title="Editar">
                                            <i class="fa fa-pencil text-orange"></i>
                                        </button>
                                        <form action="{{ route('grupos.status', $g['id']) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-default btn-xs"
                                                title="{{ $g['activo'] ? 'Desactivar' : 'Activar' }}">
                                                <i class="fa fa-power-off {{ $g['activo'] ? 'text-green' : 'text-red' }}"></i>
                                            </button>
                                        </form>
                                        @if ($g['alumnos_inscritos'] == 0)
                                            <form action="{{ route('grupos.destroy', $g['id']) }}" method="POST"
                                                class="form-eliminar-grupo">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-default btn-xs btn-trigger-eliminar"
                                                    title="Eliminar permanente">
                                                    <i class="fa fa-trash text-red"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-default btn-xs disabled"
                                                title="Tiene alumnos (no se puede borrar)">
                                                <i class="fa fa-trash text-muted"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;padding:50px 20px;color:#aab;">
                                    <i class="fa fa-folder-open-o" style="font-size:36px;display:block;margin-bottom:10px;color:#dde4ea;"></i>
                                    No hay grupos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($grupos->hasPages())
                    <div class="grp-footer">
                        <span class="grp-footer-info">
                            Mostrando <strong>{{ $grupos->firstItem() }}</strong>–<strong>{{ $grupos->lastItem() }}</strong>
                            de <strong>{{ $grupos->total() }}</strong> grupos
                        </span>
                        <div>{{ $grupos->appends(request()->query())->links() }}</div>
                    </div>
                @endif

            </div>
        </div>

        {{-- Panel de ayuda --}}
        <div class="col-md-3">
            <div class="grp-help">
                <div class="grp-help-header">
                    <h4><i class="fa fa-info-circle text-blue"></i> Ayuda del módulo</h4>
                </div>
                <div class="grp-help-body">
                    <p><strong>Indicadores de cupo:</strong></p>
                    <p><i class="fa fa-circle text-gray"></i> <b>Gris:</b> Sin alumnos.</p>
                    <p><i class="fa fa-circle text-blue"></i> <b>Azul:</b> Con cupo disponible.</p>
                    <p><i class="fa fa-circle text-red"></i> <b>Rojo:</b> Cupo agotado.</p>
                    <hr>
                    <p><strong>Acciones disponibles:</strong></p>
                    <p><i class="fa fa-users text-blue"></i> <b>Ver:</b> Gestionar alumnos.</p>
                    <p><i class="fa fa-pencil text-orange"></i> <b>Editar:</b> Nombre o cupo.</p>
                    <p><i class="fa fa-power-off text-green"></i> <b>Activo:</b> Grupo visible.</p>
                    <p><i class="fa fa-power-off text-red"></i> <b>Inactivo:</b> Grupo congelado.</p>
                    <p><i class="fa fa-trash text-red"></i> <b>Eliminar:</b> Borrado permanente.</p>
                    <hr>
                    <p><i class="fa fa-exclamation-triangle text-orange"></i> Solo se eliminan grupos <b>vacíos</b>.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL NUEVO GRUPO ══ --}}
    <x-modal id="modalNuevoGrupo" title="Crear Nuevo Grupo — {{ $cicloActual->nombre }}" size="modal-md">
        <form action="{{ route('grupos.store') }}" method="POST" id="form-nuevo-grupo">
            @csrf
            <input type="hidden" name="ciclo_id" value="{{ $cicloActual->id ?? '' }}">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nivel <span class="text-red">*</span></label>
                        <select id="filtro_nivel" class="form-control" required>
                            <option value="">— Seleccione nivel —</option>
                            @foreach ($grados->pluck('nivel')->unique('id') as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Grado <span class="text-red">*</span></label>
                        <select name="grado_id" id="filtro_grado" class="form-control" required disabled>
                            <option value="">— Seleccione primero un nivel —</option>
                            @foreach ($grados as $g)
                                <option value="{{ $g->id }}" data-nivel="{{ $g->nivel->id }}" hidden>
                                    {{ $g->numero }}°
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Nombre <span class="text-red">*</span></label>
                <input type="text" name="nombre" class="form-control" maxlength="10"
                       placeholder="Ej: A, B, UNICO" required>
            </div>

            <div class="form-group">
                <label>Docente Asignado <small class="text-muted">(Opcional)</small></label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" name="docente" class="form-control" placeholder="Nombre completo del maestro">
                </div>
            </div>

            <div class="form-group">
                <label>Cupo Máximo <small class="text-muted">(Opcional)</small></label>
                <input type="number" name="cupo_maximo" class="form-control" min="1" max="100" placeholder="Ej: 35">
            </div>

            <div class="text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btn-guardar-grupo">
                    <i class="fa fa-save"></i> Guardar grupo
                </button>
            </div>
        </form>
    </x-modal>

    {{-- ══ MODAL MIGRAR GRUPOS ══ --}}
    <div class="modal fade" id="modalMigrarGrupos" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius:8px;overflow:hidden;">
                <form action="{{ route('grupos.migrar') }}" method="POST">
                    @csrf
                    <div class="modal-header" style="background:#3c8dbc;">
                        <button type="button" class="close" data-dismiss="modal"
                            style="color:white;opacity:1;"><span>&times;</span></button>
                        <h4 class="modal-title" style="color:white;">
                            <i class="fa fa-copy"></i> Migrar Estructura
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" style="font-size:13px;border-radius:6px;">
                            <i class="fa fa-info-circle"></i>
                            Copia los salones actuales al ciclo destino <b>sin incluir alumnos</b>.
                        </div>
                        <input type="hidden" name="ciclo_origen_id" value="{{ $ciclo->id }}">
                        <div class="form-group">
                            <label>Ciclo de destino:</label>
                            <select name="ciclo_destino_id" class="form-control" required>
                                <option value="">— Seleccionar ciclo —</option>
                                @foreach ($ciclosDisponibles ?? [] as $cd)
                                    @if ($cd->id != $ciclo->id)
                                        <option value="{{ $cd->id }}">{{ $cd->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Iniciar migración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MODALES EDITAR GRUPO ══ --}}
    @foreach ($grupos as $g)
        <x-modal id="modalEditarGrupo{{ $g['id'] }}" title="Editar Grupo" size="modal-sm">
            <form action="{{ route('grupos.update', $g['id']) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group">
                    <label>Nombre del Grupo</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $g['nombre'] }}" required>
                </div>
                <div class="form-group">
                    <label>Cupo Máximo</label>
                    <input type="number" name="cupo_maximo" class="form-control" value="{{ $g['cupo_maximo'] }}">
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-refresh"></i> Actualizar</button>
                </div>
            </form>
        </x-modal>
    @endforeach

    {{-- ══ MODAL CONFIRMAR ELIMINACIÓN ══ --}}
    <div class="modal fade" id="modalConfirmEliminar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-red">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> ¿Confirmar eliminación?</h4>
                </div>
                <div class="modal-body text-center">
                    <p>¿Estás seguro de que deseas eliminar este grupo?<br><b>Esta acción no se puede deshacer.</b></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar-ok">Eliminar grupo</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL ELEGIR DISEÑO CREDENCIAL ══ --}}
    <div class="modal fade" id="modalElegirDiseno" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-id-badge text-primary"></i> Elegir diseño</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Selecciona el diseño:</label>
                        <select id="select-diseno-credencial" class="form-control">
                            <option value="">— Seleccione un diseño —</option>
                            @foreach ($disenos as $diseno)
                                <option value="{{ $diseno->id }}">{{ $diseno->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-procesar-impresion">
                        <i class="fa fa-print"></i> Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {

            // ── 1. SELECTS EN CASCADA (NIVEL -> GRADO) ──
            const $selectNivel   = $('#filtro_nivel');
            const $selectGrado   = $('#filtro_grado');
            const $opcionesGrado = $selectGrado.find('option[data-nivel]');

            $selectNivel.on('change', function() {
                const nivelSeleccionado = $(this).val();
                $selectGrado.val('');

                if (!nivelSeleccionado) {
                    $selectGrado.prop('disabled', true);
                    $selectGrado.find('option:first').text('— Seleccione primero un nivel —');
                } else {
                    $selectGrado.prop('disabled', false);
                    $selectGrado.find('option:first').text('— Seleccione grado —');
                }

                $opcionesGrado.each(function() {
                    if ($(this).data('nivel') == nivelSeleccionado) {
                        $(this).removeAttr('hidden').prop('disabled', false);
                    } else {
                        $(this).attr('hidden', true).prop('disabled', true);
                    }
                });
            });

            // ── 2. DATATABLES ──
            var table = $('#tabla-grupos').DataTable({
                paging: false, info: false, searching: true, dom: 't', ordering: false,
                language: { url: '//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json' }
            });

            $('#buscador-manual').on('keyup', function() {
                table.search(this.value).draw();
            });

            // ── 3. ELIMINACIÓN CON MODAL ──
            let formularioAEliminar = null;

            $(document).on('click', '.btn-trigger-eliminar', function() {
                formularioAEliminar = $(this).closest('form');
                $('#modalConfirmEliminar').modal('show');
            });

            $('#btn-confirmar-eliminar-ok').on('click', function() {
                if (!formularioAEliminar) return;
                let btn   = $(this);
                let token = formularioAEliminar.find('input[name="_token"]').val();

                $.ajax({
                    url: formularioAEliminar.attr('action'),
                    type: 'DELETE',
                    data: { _token: token },
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Eliminando...');
                    },
                    success: function() {
                        $('#modalConfirmEliminar').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Eliminar grupo');
                        $('#modalConfirmEliminar').modal('hide');
                        mostrarToastError(xhr);
                    }
                });
            });

            // ── 4. CREACIÓN AJAX ──
            $('#form-nuevo-grupo').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn  = $('#btn-guardar-grupo');
                let datosLimpios = {};
                form.serializeArray().forEach(item => { datosLimpios[item.name] = item.value; });

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: JSON.stringify(datosLimpios),
                    contentType: 'application/json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': datosLimpios._token
                    },
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
                    },
                    success: function() { location.reload(); },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-save"></i> Guardar grupo');
                        mostrarToastError(xhr);
                    }
                });
            });

            // ── 5. CREDENCIALES ──
            let printId = null, printTipo = null;

            $(document).on('click', '.btn-abrir-modal-credencial', function() {
                printId   = $(this).data('id');
                printTipo = $(this).data('tipo');
                $('#select-diseno-credencial').val('');
                $('#modalElegirDiseno').modal('show');
            });

            $('#btn-procesar-impresion').click(function() {
                let disenoId = $('#select-diseno-credencial').val();
                if (!disenoId) { alert('Por favor, selecciona un diseño válido.'); return; }

                let urlLote       = "{{ route('credenciales.imprimirLote', ['credencial_id' => 'DISENO_ID', 'grupo_id' => 'TARGET_ID']) }}";
                let urlIndividual = "{{ route('credenciales.imprimirIndividual', ['credencial' => 'DISENO_ID', 'alumno' => 'TARGET_ID']) }}";
                let urlFinal = (printTipo === 'lote') ? urlLote : urlIndividual;
                urlFinal = urlFinal.replace('DISENO_ID', disenoId).replace('TARGET_ID', printId);

                window.open(urlFinal, '_blank');
                $('#modalElegirDiseno').modal('hide');
            });

            // ── 6. TOAST DE ERROR ──
            function mostrarToastError(xhr) {
                let msg = 'Ocurrió un error inesperado.';
                if (xhr.responseJSON?.mensaje)  msg = xhr.responseJSON.mensaje;
                else if (xhr.responseJSON?.errors)  msg = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('<br>');
                else if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;

                $('#toast-dinamico-js').remove();
                $('body').append(`<div id="toast-dinamico-js" class="alert alert-danger alert-dismissible"
                    style="position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;box-shadow:0 4px 8px rgba(0,0,0,.2);border-radius:6px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-ban"></i> Error</h4>${msg}</div>`);
                $('#toast-dinamico-js').fadeIn('fast').delay(5000).fadeOut('slow');
            }

            // ── 7. FILAS CLICKEABLES ──
            $('#tabla-grupos tbody').on('click', 'tr[data-href]', function(e) {
                if ($(e.target).closest('button, a, input, form').length > 0) return;
                window.location.href = $(this).data('href');
            });
        });
    </script>
@endpush
