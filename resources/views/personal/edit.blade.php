@extends('layouts.master')

@section('page_title', 'Editar Empleado')
@section('page_subtitle', $empleado->nombre_completo)

@section('breadcrumb')
    <li><a href="{{ route('personal.index') }}">Personal</a></li>
    <li><a href="{{ route('personal.show', $empleado) }}">{{ $empleado->nombre_completo }}</a></li>
    <li class="active">Editar</li>
@endsection

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box box-warning" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-pencil"></i> Editar empleado</h3>
                </div>

                <form action="{{ route('personal.update', $empleado) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

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
                                           value="{{ old('numero_empleado', $empleado->numero_empleado) }}"
                                           maxlength="20" required>
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
                                            <option value="{{ $tipo->value }}"
                                                {{ old('tipo', $empleado->tipo?->value) === $tipo->value ? 'selected' : '' }}>
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

                        {{-- Fila 2: Nombre + Apellidos --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-user"></i> Nombre(s) <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" class="form-control"
                                           value="{{ old('nombre', $empleado->nombre) }}" maxlength="100" required>
                                    @error('nombre')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('ap_paterno') ? 'has-error' : '' }}">
                                    <label>Apellido paterno <span class="text-danger">*</span></label>
                                    <input type="text" name="ap_paterno" class="form-control"
                                           value="{{ old('ap_paterno', $empleado->ap_paterno) }}" maxlength="100" required>
                                    @error('ap_paterno')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('ap_materno') ? 'has-error' : '' }}">
                                    <label>Apellido materno</label>
                                    <input type="text" name="ap_materno" class="form-control"
                                           value="{{ old('ap_materno', $empleado->ap_materno) }}" maxlength="100">
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
                                           value="{{ old('telefono', $empleado->telefono) }}" maxlength="20" required>
                                    @error('telefono')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-envelope-o"></i> Correo electrónico <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email', $empleado->email) }}" maxlength="150" required>
                                    @error('email')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- RFC --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('rfc') ? 'has-error' : '' }}">
                                    <label><i class="fa fa-id-card-o"></i> RFC <small class="text-muted">(opcional, 13 caracteres)</small></label>
                                    <input type="text" name="rfc" class="form-control"
                                           value="{{ old('rfc', $empleado->rfc) }}" maxlength="13"
                                           style="text-transform:uppercase;">
                                    @error('rfc')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Domicilio --}}
                        <div class="form-group {{ $errors->has('domicilio') ? 'has-error' : '' }}">
                            <label><i class="fa fa-map-marker"></i> Domicilio <span class="text-danger">*</span></label>
                            <textarea name="domicilio" class="form-control" rows="2" maxlength="500">{{ old('domicilio', $empleado->domicilio) }}</textarea>
                            @error('domicilio')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Foto --}}
                        <div class="form-group {{ $errors->has('foto') ? 'has-error' : '' }}">
                            <label><i class="fa fa-camera"></i> Foto</label>
                            @if ($empleado->foto_url)
                                <div style="margin-bottom:8px;">
                                    <img src="{{ asset('storage/' . $empleado->foto_url) }}"
                                         alt="Foto actual" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #d0dbe6;">
                                    <small class="text-muted" style="margin-left:8px;">Foto actual — sube una nueva para reemplazarla.</small>
                                </div>
                            @endif
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp">
                            @error('foto')
                                <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>{{-- /box-body --}}

                    <div class="box-footer" style="display:flex;justify-content:space-between;">
                        <a href="{{ route('personal.show', $empleado) }}" class="btn btn-default btn-flat">
                            <i class="fa fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning btn-flat">
                            <i class="fa fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.querySelector('input[name="rfc"]')?.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    </script>
@endpush
