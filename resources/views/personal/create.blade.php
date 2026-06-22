@extends('layouts.master')

@section('page_title', 'Nuevo Empleado')
@section('page_subtitle', 'Registrar empleado')

@section('breadcrumb')
    <li><a href="{{ route('personal.index') }}">Personal</a></li>
    <li class="active">Nuevo empleado</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-primary" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user-plus"></i> Registrar nuevo empleado</h3>
                </div>

                <form action="{{ route('personal.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="box-body">

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <ul class="mb-0" style="margin:0;padding-left:18px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Fila 1: Número de empleado + Tipo --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('numero_empleado') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-hashtag"></i> Número de empleado <span class="text-danger">*</span></label>
                                    <input type="text" name="numero_empleado" class="form-control"
                                           value="{{ old('numero_empleado') }}" placeholder="Ej: EMP-001" maxlength="20" required>
                                    @error('numero_empleado')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('tipo') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-briefcase"></i> Tipo de personal <span class="text-danger">*</span></label>
                                    <select name="tipo" class="form-control" required>
                                        <option value="">Seleccionar tipo…</option>
                                        @foreach ($tipos as $tipo)
                                            <option value="{{ $tipo->value }}" {{ old('tipo') === $tipo->value ? 'selected' : '' }}>
                                                {{ $tipo->etiqueta() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tipo')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Fila 2: Nombre + Apellido paterno --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-user"></i> Nombre(s) <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control"
                                           value="{{ old('nombre') }}" maxlength="100" required>
                                    @error('nombre')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('ap_paterno') ? 'has-error' : '' }}">
                                    <label>Apellido paterno <span class="text-danger">*</span></label>
                                    <input type="text" name="ap_paterno" class="form-control"
                                           value="{{ old('ap_paterno') }}" maxlength="100" required>
                                    @error('ap_paterno')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('ap_materno') ? 'has-error' : '' }}">
                                    <label>Apellido materno</label>
                                    <input type="text" name="ap_materno" class="form-control"
                                           value="{{ old('ap_materno') }}" maxlength="100">
                                    @error('ap_materno')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Fila 3: Teléfono + Email --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('telefono') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-phone"></i> Teléfono <span class="text-danger">*</span></label>
                                    <input type="text" name="telefono" class="form-control"
                                           value="{{ old('telefono') }}" maxlength="20" required>
                                    @error('telefono')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-envelope-o"></i> Correo electrónico <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email') }}" maxlength="150" required>
                                    @error('email')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Fila 4: RFC --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('rfc') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-id-card-o"></i> RFC <small class="text-muted">(opcional, 13 caracteres)</small></label>
                                    <input type="text" name="rfc" class="form-control"
                                           value="{{ old('rfc') }}" maxlength="13"
                                           placeholder="Ej: LOAM850101AB1" style="text-transform:uppercase;">
                                    @error('rfc')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Domicilio --}}
                        <div class="form-group {{ $errors->has('domicilio') ? 'has-error' : '' }}">
                            <label><i class="fa fa-map-marker"></i> Domicilio <span class="text-danger">*</span></label>
                            <textarea name="domicilio" class="form-control" rows="2"
                                      maxlength="500" placeholder="Calle, número, colonia, ciudad…">{{ old('domicilio') }}</textarea>
                            @error('domicilio')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Foto --}}
                        <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                            <label><i class="fa fa-camera"></i> Foto <small class="text-muted">(opcional, máx. 2 MB)</small></label>
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                            @error('foto')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>{{-- /box-body --}}

                    <div class="box-footer" style="display:flex;justify-content:space-between;">
                        <a href="{{ route('personal.index') }}" class="btn btn-default btn-flat">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-flat">
                            <i class="fa fa-save"></i> Registrar empleado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // RFC siempre en mayúsculas
        document.querySelector('input[name="rfc"]')?.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    </script>
@endpush
