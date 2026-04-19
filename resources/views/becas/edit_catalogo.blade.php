@extends('layouts.master')

@section('page_title', 'Editar beca')

@section('breadcrumb')
    <li><a href="{{ route('becas.catalogo') }}">Catálogo de becas</a></li>
    <li class="active">Editar beca</li>
@endsection

@section('content')
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

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Editar beca</h3>
            <div class="box-tools">
                <a href="{{ route('becas.catalogo') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="box-body">
            <form action="{{ route('becas.catalogo.update', $beca->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $beca->nombre) }}" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $beca->descripcion) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="porcentaje" {{ old('tipo', $beca->tipo) === 'porcentaje' ? 'selected' : '' }}>Porcentaje</option>
                        <option value="monto_fijo" {{ old('tipo', $beca->tipo) === 'monto_fijo' ? 'selected' : '' }}>Monto fijo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Valor</label>
                    <input type="number" name="valor" class="form-control" value="{{ old('valor', $beca->valor) }}" step="0.01" min="0.01" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Actualizar beca
                </button>
            </form>
        </div>
    </div>
@endsection
