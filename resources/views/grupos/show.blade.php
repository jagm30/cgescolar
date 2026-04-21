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

        /* Tabla principal y responsive inteligente */
        .custom-responsive-container {
            width: 100%;
        }

        @media (max-width: 768px) {
            .custom-responsive-container {
                overflow-x: auto !important;
                padding-bottom: 120px;
            }
        }

        @media (min-width: 769px) {
            .custom-responsive-container {
                overflow: visible !important;
            }
        }

        .table-flat {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            overflow: visible !important;
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
            overflow: visible !important;
        }

        .table-flat tbody tr:hover {
            background-color: #fbfcfc;
        }

        /* Botones y Badges Planos */
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
            text-decoration: none;
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
                            <td>{{ $grupo->grado->nivel->nombre }} - {{ $grupo->grado->nombre }}</td>
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
                            <th>Alumnos</th>
                            <td><span
                                    style="font-size: 16px; color: #3498db; font-weight: bold;">{{ $grupo->inscripciones->count() }}</span>
                                <small class="text-muted">inscritos</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="box-footer"
                    style="background: #fafafa; border-top: 1px solid #edf1f2; padding: 15px 25px; border-radius: 0 0 8px 8px;">
                    <a href="{{ route('grupos.index') }}" class="btn-flat-sm btn-flat-default">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                    <a href="{{ route('planes.asignar.form', ['grupo_id' => $grupo->id, 'origen' => 'grupo']) }}"
                        class="btn-flat-sm btn-flat-info pull-right">
                        <i class="fa fa-link"></i> Asignar Plan
                    </a>
                </div>
            </div>
        </div>

        {{-- COLUMNA PRINCIPAL --}}
        <div class="col-md-8">
            <div class="box-flat">
                <div class="box-header-flat">
                    <h3 class="box-title-flat"><i class="fa fa-users"></i> Alumnos Inscritos</h3>
                    <div class="box-tools">
                        <a href="{{ route('grupos.reporte', $grupo->id) }}" target="_blank"
                            class="btn-flat-sm btn-flat-danger">
                            <i class="fa fa-file-pdf-o"></i> Descargar Lista
                        </a>
                    </div>
                </div>
                <div class="box-body-flat" style="padding: 0; overflow: visible !important;">
                    <div class="custom-responsive-container">
                        <table class="table-flat">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">#</th>
                                    <th>Matrícula</th>
                                    <th>Nombre Completo</th>
                                    <th class="text-center" style="width: 100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grupo->inscripciones as $index => $inscripcion)
                                    <tr>
                                        <td style="text-align: center; color: #95a5a6;">{{ $index + 1 }}</td>
                                        <td><code
                                                style="background: #f4f7f6; color: #7f8c8d; padding: 3px 6px; border-radius: 4px;">{{ $inscripcion->alumno->matricula }}</code>
                                        </td>
                                        <td style="font-weight: 500;">{{ $inscripcion->alumno->ap_paterno }}
                                            {{ $inscripcion->alumno->ap_materno }} {{ $inscripcion->alumno->nombre }}</td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn-action-flat btn-dropdown-manual" type="button"><i
                                                        class="fa fa-ellipsis-v"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li class="dropdown-header">Opciones</li>
                                                    <li><a href="{{ route('alumnos.show', $inscripcion->alumno->id) }}"><i
                                                                class="fa fa-eye text-blue"></i> Ver perfil</a></li>
                                                    <li><a
                                                            href="{{ route('alumnos.estado-cuenta', $inscripcion->alumno->id) }}"><i
                                                                class="fa fa-money text-green"></i> Estado de cuenta</a>
                                                    </li>
                                                    <li role="separator" class="divider"></li>
                                                    <li>
                                                        <a href="#" class="btn-cambiar-grupo"
                                                            data-alumno-id="{{ $inscripcion->alumno->id }}"
                                                            data-alumno-nombre="{{ $inscripcion->alumno->ap_paterno }} {{ $inscripcion->alumno->nombre }}">
                                                            <i class="fa fa-exchange text-warning"></i> Cambiar de grupo
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted" style="padding: 50px;">No hay
                                            alumnos inscritos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL ESTILO PLANO --}}
    <div class="modal fade" id="modalCambiarGrupo" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
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
                        <button type="button" class="btn-flat-sm btn-flat-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-flat-sm btn-flat-info"
                            style="background: #3498db; color: white;">Confirmar Cambio</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
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
