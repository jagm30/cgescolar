@extends('layouts.master')

@section('page_title', 'Alumnos')
@section('page_subtitle', 'Lista de alumnos inscritos')

@section('content')

{{-- ── Filtros ── --}}
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-filter"></i> Filtros</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <form method="GET" action="{{ route('alumnos.index') }}" id="form-filtros">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Buscar</label>
                        <input type="text"
                               name="buscar"
                               class="form-control"
                               placeholder="Nombre, matrícula o CURP..."
                               value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Nivel</label>
                        <select name="nivel_id" class="form-control">
                            <option value="">-- Todos los niveles --</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}"
                                    {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                                    {{ $nivel->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Grupo</label>
                        <select name="grupo_id" class="form-control">
                            <option value="">-- Todos los grupos --</option>
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}"
                                    {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                                    {{ $grupo->grado->nombre }}° {{ $grupo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">-- Todos --</option>
                            <option value="activo"          {{ request('estado') === 'activo'          ? 'selected' : '' }}>Activo</option>
                            <option value="baja_temporal"   {{ request('estado') === 'baja_temporal'   ? 'selected' : '' }}>Baja temporal</option>
                            <option value="baja_definitiva" {{ request('estado') === 'baja_definitiva' ? 'selected' : '' }}>Baja definitiva</option>
                            <option value="egresado"        {{ request('estado') === 'egresado'        ? 'selected' : '' }}>Egresado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('alumnos.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Tabla de alumnos ── --}}
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-users"></i>
            Alumnos
            <span class="badge bg-blue">{{ $alumnos->total() }}</span>
        </h3>
        <div class="box-tools pull-right">
            @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
            <a href="{{ route('alumnos.create') }}" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> Registrar alumno
            </a>
            @endif
        </div>
    </div>
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width:60px;">Foto</th>
                    <th>Matrícula</th>
                    <th>Nombre completo</th>
                    <th>Nivel / Grupo</th>
                    <th>Familia</th>
                    <th style="width:80px;">Estado</th>
                    <th style="width:120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumnos as $alumno)
                    @php
                        $inscripcion = $alumno->inscripciones->first();
                    @endphp
                    <tr>
                        {{-- Foto --}}
                        <td>
                            @if($alumno->foto_url)
                                <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                                     style="width:38px; height:38px; object-fit:cover; border-radius:50%; border:1px solid #ddd;"
                                     alt="Foto">
                            @else
                                <div style="width:38px; height:38px; border-radius:50%; background:#ecf0f1; display:flex; align-items:center; justify-content:center; border:1px solid #ddd;">
                                    <i class="fa fa-user" style="color:#bdc3c7; font-size:16px;"></i>
                                </div>
                            @endif
                        </td>

                        {{-- Matrícula --}}
                        <td>
                            <code>{{ $alumno->matricula }}</code>
                        </td>

                        {{-- Nombre --}}
                        <td>
                            <strong>{{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}</strong><br>
                            <small class="text-muted">{{ $alumno->nombre }}</small>
                        </td>

                        {{-- Nivel / Grupo --}}
                        <td>
                            @if($inscripcion)
                                <span class="label label-default">
                                    {{ $inscripcion->grupo->grado->nivel->nombre }}
                                </span>
                                {{ $inscripcion->grupo->grado->nombre }}°
                                <strong>{{ $inscripcion->grupo->nombre }}</strong>
                            @else
                                <span class="text-muted">Sin inscripción</span>
                            @endif
                        </td>

                        {{-- Familia --}}
                        <td>
                            @if($alumno->familia)
                                <small>{{ $alumno->familia->apellido_familia }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>

                        {{-- Estado --}}
                        <td>
                            @switch($alumno->estado)
                                @case('activo')
                                    <span class="label label-success">Activo</span>
                                    @break
                                @case('baja_temporal')
                                    <span class="label label-warning">Baja temporal</span>
                                    @break
                                @case('baja_definitiva')
                                    <span class="label label-danger">Baja</span>
                                    @break
                                @case('egresado')
                                    <span class="label label-info">Egresado</span>
                                    @break
                                @default
                                    <span class="label label-default">{{ $alumno->estado }}</span>
                            @endswitch
                        </td>

                        {{-- Acciones --}}
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('alumnos.show', $alumno->id) }}"
                                   class="btn btn-default btn-xs"
                                   title="Ver detalle">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                                <a href="{{ route('alumnos.edit', $alumno->id) }}"
                                   class="btn btn-primary btn-xs"
                                   title="Editar">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                @endif
                                @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
                                <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                                   class="btn btn-warning btn-xs"
                                   title="Estado de cuenta">
                                    <i class="fa fa-dollar"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding:30px;">
                            <i class="fa fa-users" style="font-size:32px; color:#ddd;"></i>
                            <p class="text-muted" style="margin-top:8px;">
                                No se encontraron alumnos
                                @if(request()->anyFilled(['buscar','nivel_id','grupo_id','estado']))
                                    con los filtros aplicados.
                                    <a href="{{ route('alumnos.index') }}">Limpiar filtros</a>
                                @else
                                    registrados en este ciclo.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($alumnos->hasPages())
    <div class="box-footer clearfix">
        <div class="pull-left text-muted" style="padding-top:6px;">
            Mostrando {{ $alumnos->firstItem() }}–{{ $alumnos->lastItem() }}
            de {{ $alumnos->total() }} alumnos
        </div>
        <div class="pull-right">
            {{ $alumnos->appends(request()->query())->links() }}
        </div>
    </div>
    @endif

</div>{{-- /.box --}}

@endsection
