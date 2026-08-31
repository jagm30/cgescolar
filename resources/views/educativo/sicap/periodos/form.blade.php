@extends('layouts.educativo')

@section('page_title', isset($periodo->id) ? 'Editar período' : 'Nuevo período')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.sicap.periodos.index') }}">Períodos evaluativos</a></li>
    <li class="active">{{ isset($periodo->id) ? 'Editar' : 'Nuevo' }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-calendar"></i>
                    {{ isset($periodo->id) ? 'Editar período evaluativo' : 'Nuevo período evaluativo' }}
                </h3>
            </div>

            <form method="POST"
                  action="{{ isset($periodo->id)
                      ? route('educativo.sicap.periodos.update', $periodo)
                      : route('educativo.sicap.periodos.store') }}"
                  id="form-periodo">
                @csrf
                @if(isset($periodo->id)) @method('PUT') @endif

                <div class="box-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Ciclo escolar --}}
                    <div class="form-group {{ $errors->has('ciclo_id') ? 'has-error' : '' }}">
                        <label for="ciclo_id">Ciclo escolar <span class="text-danger">*</span></label>
                        <select name="ciclo_id" id="ciclo_id" class="form-control">
                            <option value="">— Selecciona un ciclo —</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ old('ciclo_id', $periodo->ciclo_id) == $ciclo->id ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('ciclo_id')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Plan de estudios --}}
                    <div class="form-group {{ $errors->has('plan_id') ? 'has-error' : '' }}">
                        <label for="plan_id">Plan de estudios <span class="text-danger">*</span></label>
                        <select name="plan_id" id="plan_id" class="form-control">
                            <option value="">— Selecciona un plan —</option>
                            @foreach($planes as $plan)
                                <option value="{{ $plan->id }}"
                                    {{ old('plan_id', $periodo->plan_id) == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->nivel->nombre }} — {{ $plan->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <span class="help-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- Nombre --}}
                        <div class="col-sm-8">
                            <div class="form-group {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre"
                                       class="form-control"
                                       value="{{ old('nombre', $periodo->nombre) }}"
                                       maxlength="50"
                                       placeholder="Ej. 1er Trimestre">
                                @error('nombre')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Número --}}
                        <div class="col-sm-4">
                            <div class="form-group {{ $errors->has('numero') ? 'has-error' : '' }}">
                                <label for="numero">Número <span class="text-danger">*</span></label>
                                <input type="number" name="numero" id="numero"
                                       class="form-control"
                                       value="{{ old('numero', $periodo->numero) }}"
                                       min="1" max="6">
                                @error('numero')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Fecha inicio --}}
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('fecha_inicio') ? 'has-error' : '' }}">
                                <label for="fecha_inicio">Fecha de inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio"
                                       class="form-control"
                                       value="{{ old('fecha_inicio', $periodo->fecha_inicio?->format('Y-m-d')) }}">
                                @error('fecha_inicio')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Fecha fin --}}
                        <div class="col-sm-6">
                            <div class="form-group {{ $errors->has('fecha_fin') ? 'has-error' : '' }}">
                                <label for="fecha_fin">Fecha de cierre</label>
                                <input type="date" name="fecha_fin" id="fecha_fin"
                                       class="form-control"
                                       value="{{ old('fecha_fin', $periodo->fecha_fin?->format('Y-m-d')) }}">
                                @error('fecha_fin')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>{{-- /.box-body --}}

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        {{ isset($periodo->id) ? 'Guardar cambios' : 'Crear período' }}
                    </button>
                    <a href="{{ route('educativo.sicap.periodos.index') }}"
                       class="btn btn-default">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
