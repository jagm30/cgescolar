@extends('layouts.master')

@section('page_title', $alumno->nombre . ' ' . $alumno->ap_paterno)
@section('page_subtitle', 'Matrícula: ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
@endsection

@section('content')

<div class="row">

    {{-- ══════════════════════════════════════════
         COLUMNA IZQUIERDA — Perfil y datos clave
    ══════════════════════════════════════════ --}}
    <div class="col-md-4">

        {{-- Tarjeta de perfil --}}
        <div class="box box-primary">
            <div class="box-body" style="text-align:center; padding:24px 16px;">

                {{-- Foto --}}
                @if($alumno->foto_url)
                    <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                         style="width:100px; height:100px; object-fit:cover; border-radius:50%; border:3px solid #3c8dbc; margin-bottom:12px;"
                         alt="Foto del alumno">
                @else
                    <div style="width:100px; height:100px; border-radius:50%; background:#ecf0f1; border:3px solid #ddd; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                        <i class="fa fa-user" style="font-size:42px; color:#bdc3c7;"></i>
                    </div>
                @endif

                <h4 style="margin:0 0 4px;">
                    {{ $alumno->nombre }}<br>
                    <strong>{{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}</strong>
                </h4>
                <code style="font-size:13px;">{{ $alumno->matricula }}</code>

                <div style="margin-top:10px;">
                    @switch($alumno->estado)
                        @case('activo')
                            <span class="label label-success label-lg">Activo</span>@break
                        @case('baja_temporal')
                            <span class="label label-warning label-lg">Baja temporal</span>@break
                        @case('baja_definitiva')
                            <span class="label label-danger label-lg">Baja definitiva</span>@break
                        @case('egresado')
                            <span class="label label-info label-lg">Egresado</span>@break
                    @endswitch
                </div>
            </div>
            <div class="box-footer" style="text-align:center;">
                @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                <a href="{{ route('alumnos.edit', $alumno->id) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-pencil"></i> Editar
                </a>
                @endif
                @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
                <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}" class="btn btn-warning btn-sm">
                    <i class="fa fa-dollar"></i> Estado de cuenta
                </a>
                @endif
            </div>
        </div>

        {{-- Datos personales --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Datos personales</h3>
            </div>
            <div class="box-body no-padding">
                <table class="table">
                    <tr>
                        <th style="width:45%; color:#999; font-weight:400;">Fecha nacimiento</th>
                        <td>{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th style="color:#999; font-weight:400;">Género</th>
                        <td>
                            @switch($alumno->genero)
                                @case('M') Masculino @break
                                @case('F') Femenino  @break
                                @case('Otro') Otro   @break
                                @default —
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <th style="color:#999; font-weight:400;">CURP</th>
                        <td><code>{{ $alumno->curp ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <th style="color:#999; font-weight:400;">Fecha inscripción</th>
                        <td>{{ $alumno->fecha_inscripcion?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    @if($alumno->fecha_baja)
                    <tr>
                        <th style="color:#999; font-weight:400;">Fecha baja</th>
                        <td>{{ $alumno->fecha_baja->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                    @if($alumno->observaciones)
                    <tr>
                        <th style="color:#999; font-weight:400;">Observaciones</th>
                        <td><small>{{ $alumno->observaciones }}</small></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Familia --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-home"></i> Familia</h3>
            </div>
            <div class="box-body">
                @if($alumno->familia)
                    <p>
                        <strong>{{ $alumno->familia->apellido_familia }}</strong>
                    </p>
                    <a href="{{ route('familias.show', $alumno->familia->id) }}"
                       class="btn btn-default btn-xs">
                        <i class="fa fa-external-link"></i> Ver familia completa
                    </a>
                @else
                    <p class="text-muted">Sin familia registrada.</p>
                @endif
            </div>
        </div>

    </div>{{-- /.col-md-4 --}}

    {{-- ══════════════════════════════════════════
         COLUMNA DERECHA — Inscripciones, contactos,
         documentos y becas
    ══════════════════════════════════════════ --}}
    <div class="col-md-8">

        {{-- Inscripciones --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-graduation-cap"></i> Inscripciones</h3>
            </div>
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ciclo</th>
                            <th>Nivel</th>
                            <th>Grado / Grupo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alumno->inscripciones as $inscripcion)
                            <tr>
                                <td>{{ $inscripcion->grupo->grado->nivel->nombre ?? '—' }}</td>
                                <td>
                                    <span class="label label-default">
                                        {{ $inscripcion->grupo->grado->nivel->nombre ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    {{ $inscripcion->grupo->grado->nombre }}°
                                    <strong>{{ $inscripcion->grupo->nombre }}</strong>
                                </td>
                                <td>{{ $inscripcion->fecha?->format('d/m/Y') }}</td>
                                <td>
                                    @if($inscripcion->activo)
                                        <span class="label label-success">Activa</span>
                                    @else
                                        <span class="label label-default">Inactiva</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin inscripciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Contactos familiares --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-phone"></i> Contactos familiares</h3>
            </div>
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Permisos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alumno->contactos as $contacto)
                            <tr>
                                <td>
                                    <strong>{{ $contacto->nombre }} {{ $contacto->ap_paterno }}</strong>
                                    @if($contacto->pivot->orden == 1)
                                        <span class="label label-primary" style="font-size:10px;">Principal</span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($contacto->pivot->parentesco) }}</td>
                                <td>{{ $contacto->telefono_celular ?? '—' }}</td>
                                <td>
                                    <small>{{ $contacto->email ?? '—' }}</small>
                                </td>
                                <td>
                                    @if($contacto->pivot->autorizado_recoger)
                                        <span class="label label-success" title="Autorizado para recoger">
                                            <i class="fa fa-check"></i> Recoger
                                        </span>
                                    @endif
                                    @if($contacto->pivot->es_responsable_pago)
                                        <span class="label label-warning" title="Responsable de pagos">
                                            <i class="fa fa-dollar"></i> Pagos
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Sin contactos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Documentos y Becas en dos columnas --}}
        <div class="row">

            {{-- Documentos --}}
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Documentos</h3>
                        <div class="box-tools pull-right">
                            @php
                                $pendientes = $alumno->documentos->where('estado','pendiente')->count();
                                $total      = $alumno->documentos->count();
                            @endphp
                            @if($total > 0)
                                <span class="badge {{ $pendientes > 0 ? 'bg-red' : 'bg-green' }}">
                                    {{ $total - $pendientes }}/{{ $total }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <ul class="list-group list-group-unbordered" style="margin:0;">
                            @forelse($alumno->documentos as $doc)
                                <li class="list-group-item" style="padding:8px 12px;">
                                    <span>
                                        @switch($doc->estado)
                                            @case('entregado')
                                                <i class="fa fa-check-circle text-green"></i> @break
                                            @case('no_aplica')
                                                <i class="fa fa-minus-circle text-muted"></i> @break
                                            @default
                                                <i class="fa fa-clock-o text-red"></i>
                                        @endswitch
                                        {{ $doc->tipo_documento }}
                                    </span>
                                    @if($doc->archivo_url)
                                        <a href="{{ asset('storage/' . $doc->archivo_url) }}"
                                           target="_blank"
                                           class="pull-right btn btn-default btn-xs">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    @endif
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">
                                    Sin documentos registrados.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Becas --}}
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-star"></i> Becas activas</h3>
                    </div>
                    <div class="box-body no-padding">
                        <ul class="list-group list-group-unbordered" style="margin:0;">
                            @forelse($alumno->becas->where('activo', true) as $beca)
                                <li class="list-group-item" style="padding:8px 12px;">
                                    <strong>{{ $beca->catalogoBeca->nombre }}</strong><br>
                                    <small class="text-muted">
                                        {{ $beca->catalogoBeca->tipo === 'porcentaje'
                                            ? $beca->catalogoBeca->valor . '%'
                                            : '$' . number_format($beca->catalogoBeca->valor, 2) }}
                                        — {{ $beca->concepto->nombre ?? '—' }}
                                    </small>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">
                                    Sin becas asignadas.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>{{-- /.row --}}

    </div>{{-- /.col-md-8 --}}

</div>{{-- /.row --}}

@endsection
