@extends('layouts.educativo')

@section('page_title', $escala->exists ? 'Editar escala' : 'Nueva escala')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.sicap.escalas.index') }}">Escalas</a></li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-{{ $escala->exists ? 'pencil' : 'plus' }}"></i>
                    {{ $escala->exists ? 'Editar: ' . $escala->nombre : 'Nueva escala de evaluación' }}
                </h3>
            </div>

            <form id="form-escala"
                  action="{{ $escala->exists ? route('educativo.sicap.escalas.update', $escala) : route('educativo.sicap.escalas.store') }}"
                  method="POST">
                @csrf
                @if($escala->exists)
                    @method('PUT')
                @endif

                <div class="box-body">

                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control"
                               value="{{ old('nombre', $escala->nombre) }}"
                               placeholder="Ej: Numérica 6–10, Descriptiva Preescolar"
                               required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label>Tipo <span class="text-danger">*</span></label>
                        <div>
                            @foreach(\App\Enums\TipoEscala::cases() as $tipo)
                            <label class="radio-inline">
                                <input type="radio" name="tipo" value="{{ $tipo->value }}"
                                    {{ old('tipo', $escala->tipo?->value) === $tipo->value ? 'checked' : '' }}>
                                {{ $tipo->etiqueta() }}
                            </label>
                            @endforeach
                        </div>
                        <small class="text-muted">
                            <strong>Numérica:</strong> valor decimal (ej: 8.5).
                            <strong>Literal:</strong> etiqueta descriptiva (ej: MA, A, EnP, I).
                        </small>
                    </div>

                    {{-- Campos solo para escala numérica --}}
                    <div id="campos-numericos" style="{{ old('tipo', $escala->tipo?->value) === 'literal' ? 'display:none' : '' }}">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="valor_minimo">Valor mínimo</label>
                                    <input type="number" id="valor_minimo" name="valor_minimo"
                                           class="form-control" step="0.01" min="0" max="100"
                                           value="{{ old('valor_minimo', $escala->valor_minimo) }}"
                                           placeholder="Ej: 6">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="valor_maximo">Valor máximo</label>
                                    <input type="number" id="valor_maximo" name="valor_maximo"
                                           class="form-control" step="0.01" min="0" max="100"
                                           value="{{ old('valor_maximo', $escala->valor_maximo) }}"
                                           placeholder="Ej: 10">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="valor_aprobatorio">Aprobatorio</label>
                                    <input type="number" id="valor_aprobatorio" name="valor_aprobatorio"
                                           class="form-control" step="0.01" min="0" max="100"
                                           value="{{ old('valor_aprobatorio', $escala->valor_aprobatorio) }}"
                                           placeholder="Ej: 6">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="activa" value="0">
                                <input type="checkbox" name="activa" value="1"
                                    {{ old('activa', $escala->activa ?? true) ? 'checked' : '' }}>
                                Escala activa
                            </label>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        {{ $escala->exists ? 'Guardar cambios' : 'Crear escala' }}
                    </button>
                    <a href="{{ route('educativo.sicap.escalas.index') }}" class="btn btn-default">
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
    // Mostrar/ocultar campos numéricos según el tipo seleccionado
    $('input[name="tipo"]').on('change', function () {
        $('#campos-numericos').toggle(this.value === 'numerica');
    });

    // Envío AJAX del formulario
    $('#form-escala').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url   : $(this).attr('action'),
            method: $(this).find('input[name="_method"]').val() || 'POST',
            data  : $(this).serialize(),
            success: function (res) {
                window.location.href = res.redirect ?? '/educativo/sicap/escalas';
            },
            error: function (xhr) {
                const errores = xhr.responseJSON?.errors;
                if (errores) {
                    const msg = Object.values(errores).flat().join('\n');
                    alert(msg);
                } else {
                    alert(xhr.responseJSON?.message ?? 'Error al guardar.');
                }
            }
        });
    });
});
</script>
@endpush
