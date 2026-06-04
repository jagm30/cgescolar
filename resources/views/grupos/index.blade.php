@extends('layouts.master')
@section('page_title', 'Gestión de Grupos Escolares')

@section('content')

    {{-- ── ESTILOS DIRECTOS (Ya no usamos push para evitar problemas) ── --}}
    <style>
        .grupos-container {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #d0dde8;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-grupos thead th {
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 12px 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .pln-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            background: #f9fafc;
            border-top: 1px solid #d0dde8;
        }

        .pln-footer-info {
            font-size: 13px;
            color: #66788a;
        }

        .label-grupo {
            font-size: 14px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .filter-box {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #d0dde8;
        }

        .guia-ayuda {
            border-radius: 6px;
            background: #fff;
            border: 1px solid #d0dde8;
            margin-bottom: 20px;
        }

        /* ── ELIMINADOR FORZADO DE FLECHAS DE DATATABLES ── */
        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc,
        table.dataTable thead th::before,
        table.dataTable thead th::after {
            display: none !important;
            background-image: none !important;
            content: none !important;
        }
    </style>
    {{--  WIDGETS DE RESUMEN --}}
    <div class="row" style="margin-bottom: 10px;">
        {{-- Total Grupos --}}
        <div class="col-md-3 col-sm-6">
            <div
                style="background: #fff; border-radius: 8px; border: 1px solid #d0dde8; border-top: 3px solid #3498db; padding: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; height: 85px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div
                    style="width: 45px; height: 45px; border-radius: 50%; background: #ebf5fb; color: #3498db; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid #d6eaf8;">
                    <i class="fa fa-users"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-weight:bold; color: #2c3e50; font-size: 20px;">{{ $grupos->total() }}</h3>
                    <p class="text-muted"
                        style="margin:0; text-transform:uppercase; font-size:9px; letter-spacing: 0.5px; font-weight: 600;">
                        Total Grupos</p>
                </div>
            </div>
        </div>

        {{-- Grupos Activos --}}
        <div class="col-md-3 col-sm-6">
            <div
                style="background: #fff; border-radius: 8px; border: 1px solid #d0dde8; border-top: 3px solid #2ecc71; padding: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; height: 85px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div
                    style="width: 45px; height: 45px; border-radius: 50%; background: #eafaf1; color: #2ecc71; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid #d5f5e3;">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-weight:bold; color: #2c3e50; font-size: 20px;">
                        {{ $grupos->where('activo', true)->count() }}</h3>
                    <p class="text-muted"
                        style="margin:0; text-transform:uppercase; font-size:9px; letter-spacing: 0.5px; font-weight: 600;">
                        Activos (Página)</p>
                </div>
            </div>
        </div>

        {{-- Grados --}}
        <div class="col-md-3 col-sm-6">
            <div
                style="background: #fff; border-radius: 8px; border: 1px solid #d0dde8; border-top: 3px solid #f39c12; padding: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; height: 85px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div
                    style="width: 45px; height: 45px; border-radius: 50%; background: #fef9e7; color: #f39c12; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid #fcf3cf;">
                    <i class="fa fa-graduation-cap"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-weight:bold; color: #2c3e50; font-size: 20px;">{{ $grados->count() }}</h3>
                    <p class="text-muted"
                        style="margin:0; text-transform:uppercase; font-size:9px; letter-spacing: 0.5px; font-weight: 600;">
                        Grados</p>
                </div>
            </div>
        </div>

        {{-- Ciclo Activo --}}
        <div class="col-md-3 col-sm-6">
            <div
                style="background: #fff; border-radius: 8px; border: 1px solid #d0dde8; border-top: 3px solid #9b59b6; padding: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; height: 85px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div
                    style="width: 45px; height: 45px; border-radius: 50%; background: #f5eef8; color: #9b59b6; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 1px solid #ebdef0;">
                    <i class="fa fa-calendar"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-weight:bold; color: #2c3e50; font-size: 20px;">{{ $ciclo->nombre }}</h3>
                    <p class="text-muted"
                        style="margin:0; text-transform:uppercase; font-size:9px; letter-spacing: 0.5px; font-weight: 600;">
                        Ciclo Activo</p>
                </div>
            </div>
        </div>
    </div>
    {{-- ── ENCABEZADO ── --}}
    <div class="row" style="margin-bottom: 20px; display: flex; align-items: center; flex-wrap: wrap;">
        {{-- Columna del Título --}}
        <div class="col-md-6 col-sm-12">
            <h2 style="margin: 0; font-weight: bold; color: #1e4d7b;">
                <i class="fa fa-users text-blue"></i> Grupos Escolares
            </h2>

        </div>

        {{-- Columna de Botones --}}
        <div class="col-md-6 col-sm-12 text-right"
            style="display: flex; gap: 10px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
            {{-- Botón Migrar con estilo plano para que combine --}}
            @if ($grupos->count() > 0)
                <button type="button" class="btn btn-primary btn-flat" data-toggle="modal"
                    data-target="#modalMigrarGrupos">
                    <i class="fa fa-copy"></i> Migrar Estructura a Nuevo Ciclo
                </button>
            @endif
            {{-- Botón Nuevo Grupo --}}
            <button type="button" class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalNuevoGrupo">
                <i class="fa fa-plus"></i> Crear Nuevo Grupo
            </button>
        </div>
    </div>

    {{-- ── FILTROS Y BUSCADOR ── --}}
    <div class="filter-box">
        <form method="GET" action="{{ route('grupos.index') }}" class="row">
            {{-- Nivel (2.5/12) --}}
            <div class="col-md-2">
                <label class="small text-muted">NIVEL</label>
                <select name="nivel_id" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach ($niveles as $n)
                        <option value="{{ $n->id }}" {{ request('nivel_id') == $n->id ? 'selected' : '' }}>
                            {{ $n->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Grado (2.5/12) --}}
            <div class="col-md-2">
                <label class="small text-muted">GRADO</label>
                <select name="grado_id" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach ($grados as $g)
                        @if (!request('nivel_id') || $g->nivel_id == request('nivel_id'))
                            <option value="{{ $g->id }}" {{ request('grado_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->nombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Estatus (2/12) --}}
            <div class="col-md-2">
                <label class="small text-muted">ESTATUS</label>
                <select name="estatus" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="activos"
                        {{ request('estatus') != 'inactivos' && request('estatus') != 'todos' ? 'selected' : '' }}>Activos
                    </option>
                    <option value="inactivos" {{ request('estatus') == 'inactivos' ? 'selected' : '' }}>Inactivos</option>
                    <option value="todos" {{ request('estatus') == 'todos' ? 'selected' : '' }}>Todos</option>
                </select>
            </div>

            {{-- Buscador (4/12) --}}
            <div class="col-md-4">
                <label class="small text-muted">BUSCAR POR NOMBRE</label>
                <div class="input-group">
                    <input type="text" id="buscador-manual" class="form-control input-sm"
                        placeholder="Escribe el nombre...">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
            </div>

            {{-- Limpiar (2/12) --}}
            <div class="col-md-2 text-right">
                <label class="small text-muted" style="display: block;">&nbsp;</label>
                <a href="{{ route('grupos.index') }}" class="btn btn-default btn-sm btn-block" title="Limpiar Filtros">
                    <i class="fa fa-eraser"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- ── TABLA DE DATOS Y AYUDA ── --}}
    <div class="row">
        <div class="col-md-9">
            <div class="grupos-container">
                <table id="tabla-grupos" class="table table-hover table-grupos" style="margin-bottom: 0;">
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
                            <tr data-href="{{ route('grupos.show', $g['id']) }}" style="cursor: pointer;">
                                <td>
                                    <span class="text-blue"
                                        style="font-weight: bold;">{{ $g['grado']['nivel']['nombre'] }}</span><br>
                                    <small class="text-muted">{{ $g['grado']['numero'] }}°</small>
                                </td>
                                <td style="vertical-align: middle;">
                                    <span class="label label-info label-grupo">{{ $g['nombre'] }}</span>
                                </td>
                                <td class="text-center" style="vertical-align: middle;">{{ $g['cupo_maximo'] ?? '∞' }}
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    @if ($g['alumnos_inscritos'] == 0)
                                        <span class="badge bg-gray">0</span>
                                    @elseif ($g['cupo_maximo'] > 0 && $g['alumnos_inscritos'] >= $g['cupo_maximo'])
                                        <span class="badge bg-red">{{ $g['alumnos_inscritos'] }}</span>
                                    @else
                                        <span class="badge bg-blue">{{ $g['alumnos_inscritos'] }}</span>
                                    @endif
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    @if ($g['disponibles'] !== null)
                                        <b
                                            class="text-{{ $g['disponibles'] <= 5 ? 'red' : 'green' }}">{{ $g['disponibles'] }}</b>
                                    @else
                                        <span class="text-muted">∞</span>
                                    @endif
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-xs btn-abrir-modal-credencial"
                                            data-id="{{ $g['id'] }}" data-tipo="lote" title="Imprimir Lote">
                                            <i class="fa fa-id-card text-yellow"></i>
                                        </button>

                                        {{-- BOTÓN VER --}}
                                        <a href="{{ route('grupos.show', $g['id']) }}" class="btn btn-default btn-xs"
                                            title="Ver alumnos">
                                            <i class="fa fa-users text-blue"></i>
                                        </a>

                                        {{-- BOTÓN EDITAR --}}
                                        <button class="btn btn-default btn-xs" data-toggle="modal"
                                            data-target="#modalEditarGrupo{{ $g['id'] }}" title="Editar">
                                            <i class="fa fa-pencil text-orange"></i>
                                        </button>

                                        {{-- BOTÓN DESACTIVAR/ACTIVAR --}}
                                        <form action="{{ route('grupos.status', $g['id']) }}" method="POST"
                                            style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-default btn-xs"
                                                title="{{ $g['activo'] ? 'Desactivar' : 'Activar' }}">
                                                <i
                                                    class="fa fa-power-off {{ $g['activo'] ? 'text-green' : 'text-red' }}"></i>
                                            </button>
                                        </form>

                                        {{-- BOTÓN ELIMINAR (Solo si no tiene inscritos) --}}
                                        @if ($g['alumnos_inscritos'] == 0)
                                            <form action="{{ route('grupos.destroy', $g['id']) }}" method="POST"
                                                class="form-eliminar-grupo" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-default btn-xs btn-trigger-eliminar"
                                                    title="Eliminar Permanente">
                                                    <i class="fa fa-trash text-red"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-default btn-xs disabled"
                                                title="Tiene alumnos (No se puede borrar)">
                                                <i class="fa fa-trash text-muted"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 40px;"><i
                                        class="fa fa-folder-open-o fa-2x text-muted"></i><br><br>No hay grupos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($grupos->hasPages())
                    <div class="pln-footer">
                        <span class="pln-footer-info">
                            Mostrando
                            <strong>{{ $grupos->firstItem() }}</strong>–<strong>{{ $grupos->lastItem() }}</strong> de
                            <strong>{{ $grupos->total() }}</strong> grupos
                        </span>
                        <div>{{ $grupos->appends(request()->query())->links() }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── GUÍA DE AYUDA LATERAL ── --}}
        <div class="col-md-3">
            <div class="guia-ayuda">
                <div style="padding: 12px; background: #fcfcfc; border-bottom: 1px solid #d0dde8;">
                    <h4 style="margin:0; font-size:13px; font-weight:bold;"><i class="fa fa-info-circle text-blue"></i>
                        Ayuda del Módulo</h4>
                </div>
                <div style="padding: 12px; font-size: 12px; color: #666; line-height: 1.6;">
                    {{-- ESTADO DE CUPOS --}}
                    <p style="margin-bottom: 5px;"><strong>Indicadores de Cupo:</strong></p>
                    <p style="margin-bottom: 3px;"><i class="fa fa-circle text-gray"></i> <b>Gris:</b> Sin alumnos.</p>
                    <p style="margin-bottom: 3px;"><i class="fa fa-circle text-blue"></i> <b>Azul:</b> Con cupo
                        disponible.</p>
                    <p style="margin-bottom: 8px;"><i class="fa fa-circle text-red"></i> <b>Rojo:</b> Cupo agotado.</p>

                    <hr style="margin: 10px 0;">

                    {{-- EXPLICACIÓN DE BOTONES --}}
                    <p style="margin-bottom: 8px;"><strong>Acciones Disponibles:</strong></p>
                    <p style="margin-bottom: 5px;"><i class="fa fa-users text-blue"></i> <b>Ver:</b> Gestionar alumnos del
                        grupo.</p>
                    <p style="margin-bottom: 5px;"><i class="fa fa-pencil text-orange"></i> <b>Editar:</b> Cambiar nombre
                        o cupo.</p>
                    <p style="margin-bottom: 5px;"><i class="fa fa-power-off text-green"></i> <b>Activo:</b> El grupo es
                        visible.</p>
                    <p style="margin-bottom: 5px;"><i class="fa fa-power-off text-red"></i> <b>Inactivo:</b> Grupo
                        oculto/congelado.</p>
                    <p style="margin-bottom: 5px;"><i class="fa fa-trash text-red"></i> <b>Eliminar:</b> Borrado
                        permanente.</p>

                    <hr style="margin: 10px 0;">

                    <p><i class="fa fa-exclamation-triangle text-orange"></i> <b>Seguridad:</b> Solo se pueden
                        <b>Eliminar</b> grupos vacíos. Los grupos con alumnos solo pueden ser <b>Desactivados</b> para
                        proteger el historial escolar.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL NUEVO GRUPO ── --}}
    <x-modal id="modalNuevoGrupo" title="Crear Nuevo Grupo - {{ $cicloActual->nombre }}" size="modal-md">
        <form action="{{ route('grupos.store') }}" method="POST" id="form-nuevo-grupo">
            @csrf
            {{-- ── INYECCIÓN DEL CICLO ACTUAL ── --}}
            <input type="hidden" name="ciclo_id" value="{{ $cicloActual->id ?? '' }}">

            <div class="row">
                {{-- Selector de Nivel --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nivel <span class="text-red">*</span></label>
                        <select id="filtro_nivel" class="form-control" required>
                            <option value="">-- Seleccione Nivel --</option>
                            {{-- Extraemos los niveles únicos de la colección de grados --}}
                            @foreach ($grados->pluck('nivel')->unique('id') as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Selector de Grado (Inicia deshabilitado) --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Grado <span class="text-red">*</span></label>
                        <select name="grado_id" id="filtro_grado" class="form-control" required disabled>
                            <option value="">-- Seleccione primero un nivel --</option>
                            @foreach ($grados as $g)
                                {{-- Guardamos el id del nivel en un atributo data para leerlo con JS --}}
                                <option value="{{ $g->id }}" data-nivel="{{ $g->nivel->id }}" hidden>
                                    {{ $g->numero }}°
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Nombre <span class="text-red">*</span></label>
                        <input type="text" name="nombre" class="form-control" maxlength="10"
                            placeholder="Ej: A, B, UNICO" required>
                    </div>
                </div>
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
                <input type="number" name="cupo_maximo" class="form-control" min="1" max="100"
                    placeholder="Ej: 35">
            </div>

            <div class="text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btn-guardar-grupo"><i class="fa fa-save"></i> Guardar
                    Grupo</button>
            </div>
        </form>
    </x-modal>
    {{-- ── MODAL MIGRAR  GRUPOs ── --}}
    <div class="modal fade" id="modalMigrarGrupos" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <form action="{{ route('grupos.migrar') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary" style="border-radius: 12px 12px 0 0;">
                        <button type="button" class="close" data-dismiss="modal"
                            style="color:white; opacity:1;"><span>&times;</span></button>
                        <h4 class="modal-title" style="color:white;"><i class="fa fa-copy"></i> <b>Migrar Estructura</b>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info" style="font-size: 13px;">
                            <i class="fa fa-info-circle"></i> Esta acción copiará los salones actuales al nuevo ciclo
                            seleccionado, <b>sin incluir a los alumnos</b>.
                        </div>

                        {{-- Usamos $ciclo->id porque es la variable que ya tienes en el index --}}
                        <input type="hidden" name="ciclo_origen_id" value="{{ $ciclo->id }}">

                        <div class="form-group">
                            <label>Ciclo de Destino:</label>
                            <select name="ciclo_destino_id" class="form-control" required>
                                <option value="">-- Seleccionar Ciclo --</option>
                                {{-- Usamos $ciclosDisponibles que es la que sí manda tu controlador --}}
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
                        <button type="submit" class="btn btn-primary">Iniciar Migración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MODALES EDITAR GRUPO ── --}}
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
    {{-- ── MODAL DE CONFIRMACIÓN DE ELIMINACIÓN ── --}}
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
                    <p>¿Estás seguro de que deseas eliminar este grupo? <br><b>Esta acción no se puede deshacer.</b></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btn-confirmar-eliminar-ok">Eliminar Grupo</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalElegirDiseno" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-id-badge text-primary"></i> Elegir Diseño</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Selecciona el diseño a utilizar:</label>
                        <select id="select-diseno-credencial" class="form-control">
                            <option value="">-- Seleccione un diseño --</option>
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
    {{-- Librerías de DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {

            // ── 1. LÓGICA DE SELECTS EN CASCADA (NIVEL -> GRADO) ──
            // La ponemos hasta arriba para que no dependa de si DataTables choca o no
            const $selectNivel = $('#filtro_nivel');
            const $selectGrado = $('#filtro_grado');
            const $opcionesGrado = $selectGrado.find('option[data-nivel]');

            $selectNivel.on('change', function() {
                const nivelSeleccionado = $(this).val();

                // Reiniciamos el selector de grado
                $selectGrado.val('');

                if (!nivelSeleccionado) {
                    $selectGrado.prop('disabled', true);
                    $selectGrado.find('option:first').text('-- Seleccione primero un nivel --');
                } else {
                    $selectGrado.prop('disabled', false);
                    $selectGrado.find('option:first').text('-- Seleccione Grado --');
                }

                // Filtramos las opciones
                $opcionesGrado.each(function() {
                    if ($(this).data('nivel') == nivelSeleccionado) {
                        $(this).removeAttr('hidden').prop('disabled', false);
                    } else {
                        $(this).attr('hidden', true).prop('disabled', true);
                    }
                });
            });

            // ── 2. INICIALIZACIÓN DE DATATABLES ──
            var table = $('#tabla-grupos').DataTable({
                "paging": false,
                "info": false,
                "searching": true,
                "dom": 't',
                "ordering": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                }
            });

            $('#buscador-manual').on('keyup', function() {
                table.search(this.value).draw();
            });

            // ── 3. LÓGICA DE ELIMINACIÓN CON MODAL ──
            let formularioAEliminar = null;

            $(document).on('click', '.btn-trigger-eliminar', function() {
                formularioAEliminar = $(this).closest('form');
                $('#modalConfirmEliminar').modal('show');
            });

            $('#btn-confirmar-eliminar-ok').on('click', function() {
                if (formularioAEliminar) {
                    let btn = $(this);
                    let token = formularioAEliminar.find('input[name="_token"]').val();

                    $.ajax({
                        url: formularioAEliminar.attr('action'),
                        type: 'DELETE',
                        data: {
                            _token: token
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        beforeSend: function() {
                            btn.prop('disabled', true).html(
                                '<i class="fa fa-spinner fa-spin"></i> Eliminando...');
                        },
                        success: function(res) {
                            $('#modalConfirmEliminar').modal('hide');
                            location.reload();
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).text('Eliminar Grupo');
                            $('#modalConfirmEliminar').modal('hide');
                            mostrarToastError(xhr);
                        }
                    });
                }
            });

            // ── 4. LÓGICA DE CREACIÓN (AJAX) ──
            $('#form-nuevo-grupo').on('submit', function(e) {
                e.preventDefault();
                let form = $(this);
                let btn = $('#btn-guardar-grupo');
                let datosLimpios = {};
                form.serializeArray().forEach(item => {
                    datosLimpios[item.name] = item.value;
                });

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
                        btn.prop('disabled', true).html(
                            '<i class="fa fa-spinner fa-spin"></i> Guardando...');
                    },
                    success: function() {
                        location.reload();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(
                            '<i class="fa fa-save"></i> Guardar Grupo');
                        mostrarToastError(xhr);
                    }
                });
            });

            // ── 5. LÓGICA DE IMPRESIÓN DE CREDENCIALES ──
            let printId = null;
            let printTipo = null;

            $(document).on('click', '.btn-abrir-modal-credencial', function() {
                printId = $(this).data('id');
                printTipo = $(this).data('tipo');
                $('#select-diseno-credencial').val('');
                $('#modalElegirDiseno').modal('show');
            });

            $('#btn-procesar-impresion').click(function() {
                let disenoId = $('#select-diseno-credencial').val();

                if (!disenoId) {
                    alert("Por favor, selecciona un diseño válido.");
                    return;
                }

                let urlLote =
                    "{{ route('credenciales.imprimirLote', ['credencial_id' => 'DISENO_ID', 'grupo_id' => 'TARGET_ID']) }}";
                let urlIndividual =
                    "{{ route('credenciales.imprimirIndividual', ['credencial' => 'DISENO_ID', 'alumno' => 'TARGET_ID']) }}";
                let urlFinal = (printTipo === 'lote') ? urlLote : urlIndividual;
                urlFinal = urlFinal.replace('DISENO_ID', disenoId).replace('TARGET_ID', printId);

                window.open(urlFinal, '_blank');
                $('#modalElegirDiseno').modal('hide');
            });

            // ── 6. FUNCIONES GLOBALES DE ERROR ──
            function mostrarToastError(xhr) {
                let msg = 'Ocurrió un error inesperado.';
                if (xhr.responseJSON && xhr.responseJSON.mensaje) msg = xhr.responseJSON.mensaje;
                else if (xhr.responseJSON && xhr.responseJSON.errors) msg = Object.values(xhr.responseJSON.errors)
                    .map(err => err[0]).join('<br>');
                else if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

                $('#toast-dinamico-js').remove();
                let toastHTML = `<div id="toast-dinamico-js" class="alert alert-danger alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-ban"></i> Error</h4> ${msg} </div>`;
                $('body').append(toastHTML);
                $('#toast-dinamico-js').fadeIn('fast').delay(5000).fadeOut('slow');
            }

            // ── 7. HACER FILAS CLICKEABLES ──
            $('#tabla-grupos tbody').on('click', 'tr[data-href]', function(e) {
                if ($(e.target).closest('button, a, input, form, .btn-group').length > 0) {
                    return;
                }
                window.location.href = $(this).data('href');
            });
        });
    </script>
@endpush
