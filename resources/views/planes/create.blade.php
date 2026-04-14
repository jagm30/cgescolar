@extends('layouts.master')

@section('page_title', 'Nuevo plan de pago')
@section('page_subtitle', 'Configurar plan y conceptos')

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/alt/AdminLTE-select2.min.css') }}">
@endpush

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fa fa-exclamation-triangle"></i> Revisa el formulario.</strong>
            <ul style="margin: 8px 0 0 18px; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('planes.store') }}" id="plan-form">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Datos generales</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ciclo</label>
                                    <select name="ciclo_id" class="form-control select2" required>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ (string) old('ciclo_id', $cicloId) === (string) $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Nivel</label>
                                    <select name="nivel_id" class="form-control select2" required>
                                        <option value=""></option>
                                        @foreach ($niveles as $nivel)
                                            <option value="{{ $nivel->id }}" {{ (string) old('nivel_id') === (string) $nivel->id ? 'selected' : '' }}>
                                                {{ $nivel->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Periodicidad</label>
                                    <select name="periodicidad" class="form-control" required>
                                        @foreach (['mensual', 'bimestral', 'semestral', 'anual', 'unico'] as $periodicidad)
                                            <option value="{{ $periodicidad }}" {{ old('periodicidad') === $periodicidad ? 'selected' : '' }}>
                                                {{ ucfirst($periodicidad) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control" value="{{ old('fecha_inicio') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha fin</label>
                                    <input type="date" name="fecha_fin" class="form-control" value="{{ old('fecha_fin') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Conceptos del plan</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success btn-xs" id="btn-add-concepto">
                                <i class="fa fa-plus"></i> Agregar concepto
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div id="conceptos-wrapper"></div>
                    </div>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tags"></i> Descuentos opcionales</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-warning btn-xs" id="btn-add-descuento">
                                <i class="fa fa-plus"></i> Agregar descuento
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div id="descuentos-wrapper"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exclamation-circle"></i> Recargo opcional</h3>
                    </div>
                    <div class="box-body">
                        <div class="checkbox" style="margin-top: 0;">
                            <label>
                                <input type="checkbox" id="toggle-recargo" {{ old('recargo.valor') ? 'checked' : '' }}>
                                Configurar recargo por mora
                            </label>
                        </div>

                        <div id="recargo-panel" style="display: none;">
                            <div class="form-group">
                                <label>Día límite de pago</label>
                                <input type="number" name="recargo[dia_limite_pago]" class="form-control"
                                       min="1" max="31" step="1" placeholder="Ej. 10"
                                       value="{{ old('recargo.dia_limite_pago') }}">
                            </div>
                            <div class="form-group">
                                <label>Tipo de recargo</label>
                                <select name="recargo[tipo_recargo]" class="form-control">
                                    <option value="porcentaje" {{ old('recargo.tipo_recargo') === 'porcentaje' ? 'selected' : '' }}>Porcentaje</option>
                                    <option value="monto_fijo" {{ old('recargo.tipo_recargo') === 'monto_fijo' ? 'selected' : '' }}>Monto fijo</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Valor</label>
                                <input type="number" name="recargo[valor]" class="form-control"
                                       min="0.01" step="0.01" value="{{ old('recargo.valor') }}">
                            </div>
                            <div class="form-group">
                                <label>Tope máximo</label>
                                <input type="number" name="recargo[tope_maximo]" class="form-control"
                                       min="0.01" step="0.01" value="{{ old('recargo.tope_maximo') }}">
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i> Guardar plan
                        </button>
                        <a href="{{ route('planes.index') }}" class="btn btn-default btn-block">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script type="text/template" id="tpl-concepto">
        <div class="row concepto-row" data-index="__INDEX__" style="margin-bottom: 10px;">
            <div class="col-md-7">
                <div class="form-group">
                    <select name="conceptos[__INDEX__][concepto_id]" class="form-control select2 concepto-select" data-placeholder="Selecciona un concepto" required>
                        <option value=""></option>
                        @foreach ($conceptos as $concepto)
                            <option value="{{ $concepto->id }}">{{ $concepto->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <input type="number" name="conceptos[__INDEX__][monto]" class="form-control" placeholder="Monto"
                           min="0.01" step="0.01" required>
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="margin-top: 2px;">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </script>

    <script type="text/template" id="tpl-descuento">
        <div class="row descuento-row" data-index="__INDEX__" style="margin-bottom: 10px;">
            <div class="col-md-4">
                <div class="form-group">
                    <input type="text" name="descuentos[__INDEX__][nombre]" class="form-control" placeholder="Nombre">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <select name="descuentos[__INDEX__][tipo_valor]" class="form-control">
                        <option value="porcentaje">Porcentaje</option>
                        <option value="monto_fijo">Monto fijo</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <input type="number" name="descuentos[__INDEX__][valor]" class="form-control" placeholder="Valor" min="0.01" step="0.01">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <input type="number" name="descuentos[__INDEX__][dia_limite]" class="form-control" placeholder="Día" min="1" max="31">
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="margin-top: 2px;">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            let conceptoIndex = 0;
            let descuentoIndex = 0;

            function activateSelect2(container) {
                container.find('.select2').select2({
                    allowClear: true,
                    width: '100%',
                    placeholder: function () {
                        return $(this).data('placeholder') || '-- Seleccionar --';
                    }
                });
            }

            function addConceptoRow() {
                const tpl = $('#tpl-concepto').html().replaceAll('__INDEX__', conceptoIndex++);
                const $row = $(tpl);
                $('#conceptos-wrapper').append($row);
                activateSelect2($row);
            }

            function addDescuentoRow() {
                const tpl = $('#tpl-descuento').html().replaceAll('__INDEX__', descuentoIndex++);
                $('#descuentos-wrapper').append($(tpl));
            }

            $('.select2').select2({
                allowClear: true,
                width: '100%',
                placeholder: function () {
                    return $(this).data('placeholder') || '-- Seleccionar --';
                }
            });

            $('#btn-add-concepto').on('click', addConceptoRow);
            $('#btn-add-descuento').on('click', addDescuentoRow);

            $(document).on('click', '.btn-remove-row', function () {
                $(this).closest('.concepto-row, .descuento-row').remove();
            });

            $('#toggle-recargo').on('change', function () {
                $('#recargo-panel').toggle(this.checked);
            }).trigger('change');

            addConceptoRow();
        });
    </script>
@endpush
