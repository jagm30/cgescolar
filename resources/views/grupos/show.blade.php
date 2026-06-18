@extends('layouts.master')

@section('page_title', 'Detalle del Grupo')

@section('breadcrumb')
    <li><a href="{{ route('grupos.index') }}">Grupos</a></li>
    <li class="active">{{ $grupo->grado->nivel->nombre }} - {{ $grupo->grado->nombre }}</li>
@endsection

@push('styles')
    <style>
        /* Estilos globales para la vista plana */
        .content-wrapper {
            background-color: #f4f7f6 !important;
        }

        .box-flat {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03) !important;
            margin-bottom: 25px;
            background: #fff;
            overflow: visible !important;
        }

        .box-header-flat {
            padding: 18px 25px;
            border-bottom: 1px solid #edf1f2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .box-title-flat {
            font-size: 17px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .box-title-flat i {
            color: #3498db;
        }

        .box-body-flat {
            padding: 25px;
        }

        /* Estilos para la tabla de info lateral */
        .table-info-flat {
            margin-bottom: 0;
            width: 100%;
        }

        .table-info-flat tr th {
            color: #7f8c8d;
            font-weight: 500;
            font-size: 13px;
            width: 40%;
            text-align: left;
            padding: 12px 0 !important;
        }

        .table-info-flat tr td {
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
            text-align: left;
            padding: 12px 0 12px 15px !important;
        }

        /* Tabla principal */
        .custom-responsive-container {
            width: 100%;
        }

        @media (max-width: 768px) {
            .custom-responsive-container {
                overflow-x: auto !important;
                padding-bottom: 120px;
            }
        }

        .table-flat {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-flat thead th {
            background-color: #fcfcfc;
            color: #95a5a6;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            padding: 15px;
            border-bottom: 2px solid #edf1f2;
        }

        .table-flat tbody td {
            padding: 18px 15px;
            border-bottom: 1px solid #edf1f2;
            vertical-align: middle;
            color: #34495e;
            font-size: 14px;
        }

        .table-flat tbody tr:hover {
            background-color: #fbfcfc;
        }

        /* Estilo para fila inactiva/historial */
        .tr-inactivo {
            background-color: #fcfcfc;
            opacity: 0.7;
        }

        /* Botones Planos */
        .btn-flat-sm {
            padding: 7px 15px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.2s;
            display: inline-block;
        }

        .btn-flat-default {
            background-color: #f4f7f6;
            color: #7f8c8d;
        }

        .btn-flat-info {
            background-color: #e8f6f3;
            color: #1abc9c;
        }

        .btn-flat-danger {
            background-color: #fdf2f2;
            color: #e74c3c;
        }

        .btn-action-flat {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #f8f9fa;
            color: #7f8c8d;
            border: none;
        }

        /* Dropdown Estilo SaaS */
        .dropdown.open .dropdown-menu {
            display: block !important;
            z-index: 9999 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
            margin-top: 5px;
            border-radius: 8px;
        }

        .dropdown-menu>li>a {
            padding: 10px 15px;
            font-size: 13px;
            color: #444;
        }

        .dropdown-menu>li>a:hover {
            background-color: #f0f7ff !important;
            color: #3c8dbc !important;
        }

        .check-alumno {
            cursor: pointer;
            width: 17px;
            height: 17px;
            accent-color: #605ca8;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        {{-- COLUMNA LATERAL --}}
        <div class="col-md-4">
            <div class="box-flat">
                <div class="box-header-flat">
                    <h3 class="box-title-flat"><i class="fa fa-building-o"></i> Datos del Grupo</h3>
                </div>
                <div class="box-body-flat">
                    <table class="table table-info-flat">
                        <tr>
                            <th>Nivel / Grado</th>
                            <td>{{ $grupo->grado->nivel->nombre }} - {{ $grupo->grado->numero }}°</td>
                        </tr>
                        <tr>
                            <th>Grupo</th>
                            <td><span class="label label-primary"
                                    style="border-radius:4px; padding: 4px 8px;">{{ $grupo->nombre }}</span></td>
                        </tr>
                        <tr>
                            <th>Docente</th>
                            <td>{{ $grupo->docente ?? 'No asignado' }}</td>
                        </tr>
                        <tr>
                            <th>Ciclo Escolar</th>
                            <td>{{ $grupo->ciclo->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Alumnos Activos</th>
                            <td><span
                                    style="font-size: 16px; color: #3498db; font-weight: bold;">{{ $grupo->inscripciones->where('activo', true)->count() }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="box-footer"
                    style="background: #fafafa; border-top: 1px solid #edf1f2; padding: 15px 25px; border-radius: 0 0 8px 8px;">
                    <a href="{{ route('grupos.index') }}" class="btn-flat-sm btn-flat-default"><i
                            class="fa fa-arrow-left"></i> Volver</a>
                    <a href="{{ route('planes.asignar.form', ['grupo_id' => $grupo->id, 'origen' => 'grupo']) }}"
                        class="btn-flat-sm btn-flat-info pull-right"><i class="fa fa-link"></i> Asignar Plan</a>
                </div>
            </div>
        </div>

        {{-- COLUMNA PRINCIPAL --}}
        <div class="col-md-8">
            <div class="box-flat">
                <form id="form-egreso-masivo" action="{{ route('grupos.egresar-todo', $grupo->id) }}" method="POST">
                    @csrf
                    <div class="box-header-flat">
                        <h3 class="box-title-flat"><i class="fa fa-users"></i> Alumnos del Grupo</h3>
                        <div class="box-tools" style="display: flex; gap: 8px; align-items: center;">

                            {{-- BOTÓN 1: PROMOCIÓN --}}
                            <button type="button" id="btn-trigger-promocion" disabled
                                style="background-color: #f8f9fa; color: #a0aec0; border: 1px solid #e2e8f0; padding: 5px 12px; border-radius: 4px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: not-allowed; transition: all 0.3s ease;">
                                <i class="fa fa-arrow-circle-up"></i> Promocionar / Reinscribir
                            </button>

                            {{-- BOTÓN 2: EGRESO (Dinámico) --}}
                            @php
                                $esGradoFinal =
                                    $grupo->grado->nivel->nombre == 'Preparatoria' && $grupo->grado->nombre == '6';
                            @endphp

                            @if ($esGradoFinal)
                                <button type="button" id="btn-trigger-modal-egreso" disabled
                                    style="background-color: #f8f9fa; color: #a0aec0; border: 1px solid #e2e8f0; padding: 5px 12px; border-radius: 4px; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: not-allowed; transition: all 0.3s ease;">
                                    <i class="fa fa-graduation-cap"></i> Egresar (Fin)
                                </button>
                            @endif

                            <a href="{{ route('grupos.reporte', $grupo->id) }}" target="_blank"
                                class="btn-flat-sm btn-flat-danger"><i class="fa fa-file-pdf-o"></i> Reporte (Activos)</a>

                            <a href="{{ route('grupos.reporte-pagos', $grupo->id) }}" target="_blank"
                                class="btn-flat-sm btn-flat-success"><i class="fa fa-money"></i> Reporte de Pagos</a>
                        </div>
                    </div>
                    <div class="box-body-flat" style="padding: 0;">
                        <div class="custom-responsive-container">
                            <table class="table-flat">
                                <thead>
                                    <tr>
                                        <th style="width: 40px; text-align: center;"><input type="checkbox" id="check-all"
                                                class="check-alumno"></th>
                                        <th style="width: 50px; text-align: center;">#</th>
                                        <th>Matrícula</th>
                                        <th>Nombre Completo</th>
                                        <th class="text-center" style="width: 100px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($grupo->inscripciones as $index => $inscripcion)
                                        <tr class="{{ !$inscripcion->activo ? 'tr-inactivo' : '' }}">
                                            <td style="text-align: center;">
                                                @if ($inscripcion->activo)
                                                    <input type="checkbox" name="inscripciones_ids[]"
                                                        value="{{ $inscripcion->id }}" class="check-alumno check-item">
                                                @else
                                                    <i class="fa fa-history text-muted"
                                                        title="Ya no pertenece a este grupo"></i>
                                                @endif
                                            </td>
                                            <td style="text-align: center; color: #95a5a6;">{{ $index + 1 }}</td>
                                            <td><code
                                                    style="background: #f4f7f6; color: #7f8c8d; padding: 3px 6px; border-radius: 4px;">{{ $inscripcion->alumno->matricula }}</code>
                                            </td>
                                            <td style="font-weight: 500;">
                                                {{ $inscripcion->alumno->ap_paterno }}
                                                {{ $inscripcion->alumno->ap_materno }} {{ $inscripcion->alumno->nombre }}

                                                @if (!$inscripcion->activo)
                                                    <br>
                                                    @if ($inscripcion->alumno->estado === 'activo')
                                                        {{-- Lógica para deducir si fue promoción --}}
                                                        @php
                                                            // Buscamos si el alumno tiene alguna inscripción activa en un ciclo diferente al actual
                                                            $tieneInscripcionNueva = $inscripcion->alumno->inscripciones
                                                                ->where('activo', true)
                                                                ->where('ciclo_id', '!=', $grupo->ciclo_id)
                                                                ->first();
                                                        @endphp

                                                        @if ($tieneInscripcionNueva)
                                                            <small class="label"
                                                                style="background-color: #2ecc71; color: white; font-size: 9px; padding: 2px 6px; border-radius: 3px;">
                                                                <i class="fa fa-arrow-up"></i> PROMOCIONADO
                                                            </small>
                                                        @else
                                                            <small class="label"
                                                                style="background-color: #f39c12; color: white; font-size: 9px; padding: 2px 6px; border-radius: 3px;">
                                                                <i class="fa fa-exchange"></i> QUITADO DEL GRUPO / CAMBIO
                                                            </small>
                                                        @endif
                                                    @else
                                                        {{-- Si el alumno ya es Egresado o Baja --}}
                                                        <small class="label label-default"
                                                            style="font-size: 9px; padding: 2px 6px; border-radius: 3px;">
                                                            INSCRIPCIÓN CERRADA
                                                            ({{ strtoupper(str_replace('_', ' ', $inscripcion->alumno->estado)) }})
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn-action-flat btn-dropdown-manual" type="button"
                                                        data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
                                                    <ul class="dropdown-menu dropdown-menu-right"
                                                        style="min-width: 200px; padding: 8px 0; border-radius: 8px;">
                                                        <li class="dropdown-header">Opciones</li>
                                                        <li><a href="{{ route('alumnos.show', $inscripcion->alumno->id) }}"
                                                                style="padding: 8px 20px;"><i class="fa fa-eye"
                                                                    style="color: #3498db; width:24px"></i> Ver perfil</a>
                                                        </li>
                                                        <li><a href="{{ route('alumnos.reporte', $inscripcion->alumno->id) }}"
                                                                style="padding: 8px 20px;"><i class="fa fa-file-pdf-o"
                                                                    style="color: #db3434; width:24px"></i> Ver ficha de
                                                                alumno</a>
                                                        </li>
                                                        <li><a href="{{ route('alumnos.estado-cuenta', $inscripcion->alumno->id) }}"
                                                                style="padding: 8px 20px;"><i class="fa fa-money"
                                                                    style="color: #2ecc71; width:24px"></i> Estado de
                                                                cuenta</a></li>

                                                        @if ($inscripcion->activo)
                                                            <li role="separator" class="divider"></li>
                                                            <li><a href="#" class="btn-action-confirm"
                                                                    data-type="quitar" data-id="{{ $inscripcion->id }}"
                                                                    data-nombre="{{ $inscripcion->alumno->nombre }}"
                                                                    style="padding: 8px 20px;"><i
                                                                        class="fa fa-user-times text-red"
                                                                        style="width:24px"></i> Quitar del grupo</a></li>
                                                            <li><a href="#" class="btn-action-confirm"
                                                                    data-type="baja_temporal"
                                                                    data-id="{{ $inscripcion->alumno->id }}"
                                                                    data-nombre="{{ $inscripcion->alumno->nombre }}"
                                                                    style="padding: 8px 20px;"><i class="fa fa-clock-o"
                                                                        style="color: #f39c12; width:24px"></i> Dar de baja
                                                                    temporal</a></li>
                                                            <li><a href="#" class="btn-action-confirm"
                                                                    data-type="baja_definitiva"
                                                                    data-id="{{ $inscripcion->alumno->id }}"
                                                                    data-nombre="{{ $inscripcion->alumno->nombre }}"
                                                                    style="padding: 8px 20px;"><i
                                                                        class="fa fa-user-times text-red"
                                                                        style="width:24px"></i> Dar de baja definitiva</a>
                                                            </li>
                                                            <li role="separator" class="divider"></li>
                                                            <li><a href="#" class="btn-cambiar-grupo"
                                                                    data-alumno-id="{{ $inscripcion->alumno->id }}"
                                                                    data-alumno-nombre="{{ $inscripcion->alumno->ap_paterno }} {{ $inscripcion->alumno->nombre }}"
                                                                    style="padding: 8px 20px;"><i
                                                                        class="fa fa-exchange text-warning"
                                                                        style="width:24px"></i> Cambiar de grupo</a></li>
                                                        @else
                                                            <li role="separator" class="divider"></li>
                                                            <li class="dropdown-header" style="color: #00a65a;">
                                                                Reincorporación</li>
                                                            <li>
                                                                <a href="{{ route('alumnos.edit', $inscripcion->alumno->id) }}#paso3"
                                                                    style="padding: 8px 20px; color: #00a65a; font-weight: 600;">
                                                                    <i class="fa fa-refresh" style="width:24px"></i>
                                                                    Gestionar Reingreso
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted" style="padding: 50px;">No
                                                hay historial de alumnos en este grupo.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FORMULARIOS OCULTOS --}}
    @foreach ($grupo->inscripciones as $inscripcion)
        @if ($inscripcion->activo)
            <form id="delete-form-{{ $inscripcion->id }}"
                action="{{ route('inscripciones.destroy', $inscripcion->id) }}" method="POST" style="display: none;">
                @csrf @method('DELETE')
            </form>
            <form id="baja-form-{{ $inscripcion->alumno->id }}"
                action="{{ route('alumnos.darBaja', $inscripcion->alumno->id) }}" method="POST" style="display: none;">
                @csrf @method('PATCH')
                <input type="hidden" name="tipo_baja" value="">
            </form>
        @endif
    @endforeach

    {{-- MODAL DE CONFIRMACIÓN ESTILIZADO --}}
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document" style="width: 350px; margin-top: 15vh;">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
                <div class="modal-body" style="padding: 35px 25px; text-align: center;">
                    <div id="icon-container"
                        style="width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <i id="confirm-icon" class="fa" style="font-size: 32px;"></i>
                    </div>
                    <h4 id="confirm-title"
                        style="font-weight: 700; color: #2c3e50; margin-bottom: 10px; font-size: 18px;"></h4>
                    <p id="confirm-text" style="color: #7f8c8d; font-size: 14px; line-height: 1.6;"></p>

                    <div id="razon-baja-container" style="display: none; margin-top: 15px; text-align: left;">
                        <label style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase;">Razón
                            de la baja:</label>
                        <textarea id="razon_baja_input" class="form-control" rows="3" placeholder="Escribe el motivo..."
                            style="border-radius: 8px; resize: none; border: 1px solid #e2e8f0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border: none; padding: 0 25px 30px; display: flex; gap: 12px;">
                    <button type="button" class="btn-flat-sm btn-flat-default" data-dismiss="modal"
                        style="flex: 1; height: 40px;">Cancelar</button>
                    <button type="button" id="btn-confirm-submit" class="btn-flat-sm"
                        style="flex: 1; color: white; height: 40px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"></button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CAMBIAR GRUPO --}}
    <div class="modal fade" id="modalCambiarGrupo" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <form action="{{ route('grupos.cambiar-alumno', $grupo->id) }}" method="POST">
                    @csrf
                    <div class="modal-header" style="border-bottom: 1px solid #f0f2f5; padding: 20px 25px;">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title" style="font-weight: 700; color: #2c3e50;">Mover Alumno</h4>
                    </div>
                    <div class="modal-body" style="padding: 25px;">
                        <input type="hidden" name="alumno_id" id="input_alumno_id">
                        <div
                            style="background: #f8fafd; border-radius: 8px; padding: 15px; margin-bottom: 20px; border: 1px solid #eef2f7;">
                            <span
                                style="display: block; font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 5px;">Alumno
                                seleccionado</span>
                            <span id="nombre_alumno_modal"
                                style="font-size: 15px; font-weight: 600; color: #334155;"></span>
                        </div>
                        <div class="form-group">
                            <label
                                style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Grupo
                                Destino (Mismo Grado)</label>
                            <select name="grupo_destino_id" class="form-control" required
                                style="border-radius: 8px; height: 45px; border: 1px solid #e2e8f0;">
                                <option value="">-- Seleccionar Grupo --</option>
                                @foreach ($gruposDisponibles ?? [] as $g)
                                    @if ($g->id !== $grupo->id)
                                        <option value="{{ $g->id }}">{{ $g->grado->nombre }}° {{ $g->nombre }}
                                            ({{ $g->inscripciones_count }}/{{ $g->cupo_maximo ?? '∞' }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"
                        style="border-top: 1px solid #f0f2f5; padding: 15px 25px; background: #fafbfc; border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn-flat-sm btn-flat-default"
                            data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-flat-sm btn-flat-info"
                            style="background: #3498db; color: white;">Confirmar Cambio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PROMOCIÓN MASIVA --}}
    <div class="modal fade" id="modalPromocionMasiva" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content"
                style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <form action="{{ route('grupos.promocionar-masivo') }}" method="POST">
                    @csrf
                    <input type="hidden" name="grupo_origen_id" value="{{ $grupo->id }}">
                    <div id="ids-alumnos-promocion"></div>

                    <div class="modal-header" style="background: #2ecc71; color: white; border-radius: 12px 12px 0 0;">
                        <button type="button" class="close" data-dismiss="modal"
                            style="color:white; opacity:1;"><span>&times;</span></button>
                        <h4 class="modal-title"><b><i class="fa fa-rocket"></i> Promoción de Alumnos</b></h4>
                    </div>
                    <div class="modal-body">
                        <p style="font-size: 15px;">Vas a promover a <b id="contador-promocion">0</b> alumnos del
                            <b>{{ $grupo->grado->nivel->nombre }} - {{ $grupo->grado->numero }}°</b> del grupo
                            <b>{{ $grupo->nombre }}</b>.
                        </p>
                        <hr>

                        {{-- SELECT 1: Ciclo --}}
                        <div class="form-group">
                            <label>Ciclo Escolar Destino:</label>
                            <select name="ciclo_destino_id" id="select-ciclo-promocion" class="form-control" required
                                style="border-radius: 8px;">
                                <option value="">-- Seleccionar Ciclo --</option>
                                @foreach ($ciclosDisponibles ?? [] as $cicloD)
                                    @if (in_array($cicloD->estado, ['activo', 'configuracion']) && $cicloD->id > $grupo->ciclo_id)
                                        <option value="{{ $cicloD->id }}">
                                            {{ $cicloD->nombre }}
                                            | {{ $cicloD->estado == 'activo' ? '🟢 Activo' : '⚙️ Configuración' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        {{-- SELECT 2: Grado (se llena al elegir ciclo) --}}
                        <div class="form-group">
                            <label>Nivel / Grado al que pasan:</label>
                            <select name="grado_destino_id" id="select-grado-promocion" class="form-control" required
                                style="border-radius: 8px;" disabled>
                                <option value="">-- Primero selecciona un ciclo --</option>
                            </select>
                        </div>

                        {{-- SELECT 3: Grupo (se llena al elegir grado) --}}
                        <div class="form-group">
                            <label>Grupo Destino:</label>
                            <select name="grupo_destino_id" id="select-grupo-promocion" class="form-control" required
                                style="border-radius: 8px;" disabled>
                                <option value="">-- Primero selecciona un grado --</option>
                            </select>
                        </div>

                        <div class="alert alert-info" style="border-radius: 8px; font-size: 13px;">
                            <i class="fa fa-info-circle"></i> Esto creará una nueva inscripción para el nuevo ciclo. Los
                            alumnos seguirán estando <b>ACTIVOS</b>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"
                            style="background: #2ecc71; border: none; font-weight: bold;">Realizar Promoción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let formToSubmit = null;
            let currentType = null;

            // ── SELECTS ENCADENADOS: Ciclo → Grado → Grupo ──────────────────────

            // 1. Al cambiar CICLO: carga grados y resetea grupo
            $('#select-ciclo-promocion').on('change', function() {
                const cicloId = $(this).val();
                const gradoOrigenId = '{{ $grupo->grado_id }}'; // Aquí inyectamos el grado actual

                const $gradoSelect = $('#select-grado-promocion');
                const $grupoSelect = $('#select-grupo-promocion');

                // Resetear selects dependientes
                $grupoSelect.html('<option value="">-- Primero selecciona un grado --</option>').prop('disabled', true);
                $gradoSelect.html('<option value="">-- Cargando grados... --</option>').prop('disabled', true);

                if (!cicloId) {
                    $gradoSelect.html('<option value="">-- Primero selecciona un ciclo --</option>');
                    return;
                }

                // Llamada AJAX para obtener grados filtrados
                $.getJSON('{{ route('grupos.gradosPorCiclo') }}', {
                    ciclo_id: cicloId,
                    grado_origen_id: gradoOrigenId
                })
                .done(function(grados) {
                    $gradoSelect.html('<option value="">-- Seleccionar Grado --</option>');
                    if (grados.length === 0) {
                        $gradoSelect.html('<option value="">-- Sin grados disponibles --</option>');
                        return;
                    }
                    grados.forEach(function(gr) {
                        $gradoSelect.append(`<option value="${gr.id}">${gr.label}</option>`);
                    });
                    $gradoSelect.prop('disabled', false);
                })
                .fail(function() {
                    $gradoSelect.html('<option value="">-- Error al cargar --</option>');
                });
            });

            // 2. Al cambiar GRADO: carga grupos
            $('#select-grado-promocion').on('change', function() {
                const gradoId = $(this).val();
                const cicloId = $('#select-ciclo-promocion').val();
                const $grupoSelect = $('#select-grupo-promocion');

                $grupoSelect.html('<option value="">-- Cargando grupos... --</option>').prop('disabled', true);

                if (!gradoId || !cicloId) {
                    $grupoSelect.html('<option value="">-- Primero selecciona un grado --</option>');
                    return;
                }

                // Llamada AJAX para obtener grupos
                $.getJSON('{{ route('grupos.gruposPorCicloGrado') }}', {
                    ciclo_id: cicloId,
                    grado_id: gradoId
                })
                .done(function(grupos) {
                    $grupoSelect.html('<option value="">-- Seleccionar Grupo --</option>');
                    if (grupos.length === 0) {
                        $grupoSelect.html('<option value="">-- Sin grupos en este grado/ciclo --</option>');
                        return;
                    }
                    grupos.forEach(function(g) {
                        $grupoSelect.append(`<option value="${g.id}">${g.label}</option>`);
                    });
                    $grupoSelect.prop('disabled', false);
                })
                .fail(function() {
                    $grupoSelect.html('<option value="">-- Error al cargar --</option>');
                });
            });

            // ── LÓGICA DE CONFIRMACIONES Y MODALES (BAJAS, EGRESOS, QUITAR) ──
            $('.btn-action-confirm, #btn-trigger-modal-egreso').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                const type = btn.data('type') || 'egreso';
                const id = btn.data('id');
                const nombre = btn.data('nombre');
                currentType = type;

                let config = {
                    quitar: {
                        title: '¿Quitar del salón?',
                        text: `¿Seguro que deseas remover a <b>${nombre}</b> de este grupo?`,
                        icon: 'fa-user-times',
                        color: '#e74c3c',
                        bg: '#fdf2f2',
                        btnText: 'Quitar',
                        form: `#delete-form-${id}`
                    },
                    baja_temporal: {
                        title: '¿Baja Temporal?',
                        text: `Se registrará la baja temporal de <b>${nombre}</b>.`,
                        icon: 'fa-clock-o',
                        color: '#f39c12',
                        bg: '#fdf8e4',
                        btnText: 'Baja Temporal',
                        form: `#baja-form-${id}`,
                        tipo: 'baja_temporal'
                    },
                    baja_definitiva: {
                        title: '¿Baja Definitiva?',
                        text: `Se registrará la baja definitiva de <b>${nombre}</b>.`,
                        icon: 'fa-ban',
                        color: '#e74c3c',
                        bg: '#fdf2f2',
                        btnText: 'Baja Definitiva',
                        form: `#baja-form-${id}`,
                        tipo: 'baja_definitiva'
                    },
                    egreso: {
                        title: '¿Egresar seleccionados?',
                        text: `Se procesará el egreso de <b>${$('.check-item:checked').length}</b> alumnos marcados.`,
                        icon: 'fa-graduation-cap',
                        color: '#605ca8',
                        bg: '#f4f3ff',
                        btnText: 'Egresar',
                        form: '#form-egreso-masivo'
                    }
                };

                const c = config[type];
                $('#confirm-title').text(c.title);
                $('#confirm-text').html(c.text);
                $('#confirm-icon').attr('class', 'fa ' + c.icon).css('color', c.color);
                $('#icon-container').css('background-color', c.bg);
                $('#btn-confirm-submit').text(c.btnText).css('background-color', c.color);

                if (type.includes('baja')) {
                    $('#razon-baja-container').show();
                    $('#razon_baja_input').val('');
                } else {
                    $('#razon-baja-container').hide();
                }

                formToSubmit = c.form;
                if (c.tipo) {
                    $(formToSubmit).find('input[name="tipo_baja"]').val(c.tipo);
                }
                $('#modalConfirmacion').modal('show');
            });

            // ── LÓGICA DE PROMOCIÓN MASIVA (BOTÓN PRINCIPAL) ──
            $('#btn-trigger-promocion').on('click', function(e) {
                e.preventDefault();
                var ids = [];
                $('.check-item:checked').each(function() {
                    ids.push($(this).val());
                });

                $('#contador-promocion').text(ids.length);
                $('#ids-alumnos-promocion').empty();
                ids.forEach(id => {
                    $('#ids-alumnos-promocion').append(`<input type="hidden" name="inscripciones_ids[]" value="${id}">`);
                });

                $('#modalPromocionMasiva').modal('show');
            });

            // Confirmar Submit de modales
            $('#btn-confirm-submit').on('click', function() {
                if (currentType.includes('baja')) {
                    let razon = $('#razon_baja_input').val();
                    let tipoTexto = (currentType === 'baja_temporal') ? 'Baja Temporal' : 'Baja Definitiva';
                    $(formToSubmit).find('input[name="observaciones"]').remove();
                    $(formToSubmit).append(`<input type="hidden" name="observaciones" value="${tipoTexto}: ${razon}">`);
                }
                $(formToSubmit).submit();
            });

            // ── LÓGICA DE CHECKBOXES ──
            function actualizarEstadoBoton() {
                var seleccionados = $('.check-item:checked').length;
                var btnEgreso = $('#btn-trigger-modal-egreso');
                var btnPromocion = $('#btn-trigger-promocion');

                if (seleccionados > 0) {
                    btnPromocion.prop('disabled', false).css({
                        'background-color': '#f0fff4',
                        'color': '#276749',
                        'border-color': '#c6f6d5',
                        'cursor': 'pointer'
                    });
                    if (btnEgreso.length) {
                        btnEgreso.prop('disabled', false).css({
                            'background-color': '#fff5f5',
                            'color': '#c53030',
                            'border-color': '#fed7d7',
                            'cursor': 'pointer'
                        });
                    }
                } else {
                    btnPromocion.prop('disabled', true).css({
                        'background-color': '#f8f9fa',
                        'color': '#a0aec0',
                        'border-color': '#e2e8f0',
                        'cursor': 'not-allowed'
                    });
                    if (btnEgreso.length) {
                        btnEgreso.prop('disabled', true).css({
                            'background-color': '#f8f9fa',
                            'color': '#a0aec0',
                            'border-color': '#e2e8f0',
                            'cursor': 'not-allowed'
                        });
                    }
                }
            }

            $('#check-all').on('change', function() {
                $('.check-item').prop('checked', $(this).is(':checked'));
                actualizarEstadoBoton();
            });

            $(document).on('change', '.check-item', function() {
                if (!$(this).is(':checked')) $('#check-all').prop('checked', false);
                if ($('.check-item:checked').length === $('.check-item').length) $('#check-all').prop('checked', true);
                actualizarEstadoBoton();
            });

            // ── LÓGICA DE DROPDOWNS Y CAMBIAR GRUPO ──
            $('.btn-dropdown-manual').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var current = $(this).closest('.dropdown');
                $('.dropdown').not(current).removeClass('open');
                current.toggleClass('open');
            });

            $('.btn-cambiar-grupo').on('click', function(e) {
                e.preventDefault();
                $('#input_alumno_id').val($(this).data('alumno-id'));
                $('#nombre_alumno_modal').text($(this).data('alumno-nombre'));
                $('#modalCambiarGrupo').modal('show');
                $('.dropdown').removeClass('open');
            });

            $(document).on('click', function() {
                $('.dropdown').removeClass('open');
            });
        });
    </script>
@endpush
