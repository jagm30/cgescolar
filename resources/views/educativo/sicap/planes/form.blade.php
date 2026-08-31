@extends('layouts.educativo')

@section('page_title', $plan->exists ? 'Editar plan' : 'Nuevo plan de estudio')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.sicap.planes.index') }}">Planes</a></li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-{{ $plan->exists ? 'pencil' : 'plus' }}"></i>
                    {{ $plan->exists ? 'Editar: ' . $plan->nombre : 'Nuevo plan de estudio' }}
                </h3>
            </div>

            <form id="form-plan"
                  action="{{ $plan->exists ? route('educativo.sicap.planes.update', $plan) : route('educativo.sicap.planes.store') }}"
                  method="POST">
                @csrf
                @if($plan->exists)
                    @method('PUT')
                @endif

                <div class="box-body">

                    <div class="form-group">
                        <label for="nombre">Nombre del plan <span class="text-danger">*</span></label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control"
                               value="{{ old('nombre', $plan->nombre) }}"
                               placeholder="Ej: Plan Primaria, Plan Preparatoria 2026"
                               required maxlength="150">
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="nivel_id">Nivel académico <span class="text-danger">*</span></label>
                                <select id="nivel_id" name="nivel_id" class="form-control" required>
                                    <option value="">— Seleccionar —</option>
                                    @foreach($niveles as $nivel)
                                    <option value="{{ $nivel->id }}"
                                        {{ old('nivel_id', $plan->nivel_id) == $nivel->id ? 'selected' : '' }}>
                                        {{ $nivel->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="escala_id">Escala de evaluación <span class="text-danger">*</span></label>
                                <select id="escala_id" name="escala_id" class="form-control" required>
                                    <option value="">— Seleccionar —</option>
                                    @foreach($escalas as $escala)
                                    <option value="{{ $escala->id }}"
                                        {{ old('escala_id', $plan->escala_id) == $escala->id ? 'selected' : '' }}>
                                        {{ $escala->nombre }} ({{ $escala->tipo->etiqueta() }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Periodicidad <span class="text-danger">*</span></label>
                                @foreach(\App\Enums\TipoPeriodo::cases() as $periodo)
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="tipo_periodo" value="{{ $periodo->value }}"
                                            {{ old('tipo_periodo', $plan->tipo_periodo?->value) === $periodo->value ? 'checked' : '' }}>
                                        {{ $periodo->etiqueta() }}
                                        <small class="text-muted">({{ $periodo->totalPeriodos() }} {{ $periodo->nombrePeriodo() }}s)</small>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="ciclo_id">
                                    Ciclo específico
                                    <small class="text-muted">(opcional)</small>
                                </label>
                                <select id="ciclo_id" name="ciclo_id" class="form-control">
                                    <option value="">Genérico (aplica a todos los ciclos)</option>
                                    @foreach($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}"
                                        {{ old('ciclo_id', $plan->ciclo_id) == $ciclo->id ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Solo necesario si el plan cambia para un ciclo en particular.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1"
                                    {{ old('activo', $plan->activo ?? true) ? 'checked' : '' }}>
                                Plan activo
                            </label>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        {{ $plan->exists ? 'Guardar cambios' : 'Crear plan' }}
                    </button>
                    <a href="{{ route('educativo.sicap.planes.index') }}" class="btn btn-default">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#form-plan').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url   : $(this).attr('action'),
            method: $(this).find('input[name="_method"]').val() || 'POST',
            data  : $(this).serialize(),
            success: function (res) {
                window.location.href = res.redirect ?? '/educativo/sicap/planes';
            },
            error: function (xhr) {
                const errores = xhr.responseJSON?.errors;
                if (errores) {
                    alert(Object.values(errores).flat().join('\n'));
                } else {
                    alert(xhr.responseJSON?.message ?? 'Error al guardar.');
                }
            }
        });
    });
});
</script>
@endpush
