@extends('layouts.master')

@section('page_title', 'Catálogo de becas')

@section('breadcrumb')
    <li><a href="{{ route('becas.catalogo') }}">Becas</a></li>
    <li class="active">Catálogo</li>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <h4><i class="icon fa fa-ban"></i> Revisa el formulario.</h4>
            <ul>
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row" style="margin-bottom:20px;">
        <div class="col-md-12">
            <a href="{{ route('becas.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Asignar beca a alumno
            </a>
            <a href="{{ route('becas.index') }}" class="btn btn-default">
                <i class="fa fa-list"></i> Asignaciones de becas
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Agregar nueva beca</h3>
                </div>
                <div class="box-body">
                    <form action="{{ route('becas.catalogo.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Tipo</label>
                            <select name="tipo" class="form-control" required>
                                <option value="">Selecciona un tipo</option>
                                <option value="porcentaje" {{ old('tipo') === 'porcentaje' ? 'selected' : '' }}>Porcentaje</option>
                                <option value="monto_fijo" {{ old('tipo') === 'monto_fijo' ? 'selected' : '' }}>Monto fijo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Valor</label>
                            <input type="number" name="valor" class="form-control" value="{{ old('valor') }}" step="0.01" min="0.01" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i> Guardar beca
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Becas del catálogo</h3>
                </div>
                <div class="box-body table-responsive">
                    @if ($catalogo->isEmpty())
                        <div class="alert alert-info">
                            No hay becas en el catálogo.
                        </div>
                    @else
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Estado</th>
                                    <th style="width:160px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($catalogo as $item)
                                    <tr>
                                        <td>{{ $item->nombre }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $item->tipo)) }}</td>
                                        <td>
                                            @if ($item->tipo === 'porcentaje')
                                                {{ number_format($item->valor, 2) }}%
                                            @else
                                                ${{ number_format($item->valor, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->activo)
                                                <span class="label label-success">Activo</span>
                                            @else
                                                <span class="label label-default">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('becas.catalogo.edit', $item->id) }}" class="btn btn-xs btn-info">
                                                <i class="fa fa-pencil"></i> Editar
                                            </a>
                                            @if ($item->activo)
                                                <form action="{{ route('becas.catalogo.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger">
                                                        <i class="fa fa-ban"></i> Desactivar
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
