@extends('layouts.master')
@section('page_title', 'Políticas del Plan: ' . $plan->nombre)

@section('content')


    {{-- ENCABEZADO DEL PLAN (Idéntico a la pestaña 1 para mantener la ilusión) --}}
    <div class="box box-widget widget-user-2" style="margin-bottom: 15px;">
        <div class="widget-user-header bg-blue">
            <div class="pull-right">
                <a href="{{ route('planes.index') }}" class="btn btn-default btn-sm text-black"
                    style="color: black !important;">
                    <i class="fa fa-arrow-left"></i> Volver al Catálogo
                </a>
            </div>
            <h3 class="widget-user-username" style="margin-left: 0; font-weight: bold;">{{ $plan->nombre }}</h3>
            <h5 class="widget-user-desc" style="margin-left: 0;">
                Nivel: <strong>{{ $plan->nivel->nombre ?? 'N/A' }}</strong> |
                Vigencia: <strong>{{ $plan->fecha_inicio->format('d/m/Y') }} al
                    {{ $plan->fecha_fin->format('d/m/Y') }}</strong> |
                Pago: <strong>{{ ucfirst($plan->periodicidad) }}</strong>
            </h5>
        </div>
    </div>

    {{-- PANEL DE PESTAÑAS (TABS) --}}
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            {{-- Pestaña Inactiva (Conceptos) - Enlace a la otra vista --}}
            <li>
                <a href="{{ route('planes.conceptos.index', $plan->id) }}" class="text-muted">
                    <i class="fa fa-tags"></i> 1. Conceptos de Cobro
                </a>
            </li>
            {{-- Pestaña Activa (Políticas) --}}
            <li class="active">
                <a href="#tab_politicas" data-toggle="tab">
                    <i class="fa fa-percent text-yellow"></i> <strong>2. Políticas y Recargos</strong>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="tab_politicas">

                <div class="row">
                    <div class="col-md-7">
                        <div class="box box-warning box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-gift"></i> Políticas de Descuento</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool"
                                        style="color: #fff; background-color: rgba(0,0,0,0.1);" data-toggle="modal"
                                        data-target="#modalAddDescuento">
                                        <i class="fa fa-plus"></i> Nuevo Descuento
                                    </button>
                                </div>
                            </div>
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Día Límite</th>
                                            <th>Valor</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($plan->politicasDescuento as $descuento)
                                            <tr>
                                                <td>{{ $descuento->nombre }}</td>
                                                <td>
                                                    <span class="label label-success">Día
                                                        {{ $descuento->dia_limite ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <b>{{ $descuento->tipo_valor == 'porcentaje' ? '%' : '$' }}{{ number_format($descuento->valor, 2) }}</b>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-warning btn-xs" data-toggle="modal"
                                                        data-target="#modalEditDescuento{{ $descuento->id }}"
                                                        title="Editar"><i class="fa fa-pencil"></i></button>
                                                    <form
                                                        action="{{ route('planes.politicas.descuento.destroy', [$plan->id, $descuento->id]) }}"
                                                        method="POST" style="display:inline-block;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-xs"
                                                            onclick="return confirm('¿Borrar descuento?');"
                                                            title="Borrar"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No hay descuentos
                                                    configurados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="box box-danger box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-calendar-times-o"></i> Recargo por Mora</h3>
                            </div>
                            <div class="box-body">
                                @if ($plan->politicaRecargo)
                                    <div class="callout callout-danger" style="margin-bottom: 10px;">
                                        <h4>Aplicar después del día:
                                            <strong>{{ $plan->politicaRecargo->dia_limite_pago }}</strong>
                                        </h4>
                                        <p>
                                            Penalización:
                                            <strong>{{ $plan->politicaRecargo->tipo_recargo == 'porcentaje' ? '%' : '$' }}{{ number_format($plan->politicaRecargo->valor, 2) }}</strong><br>
                                            @if ($plan->politicaRecargo->tope_maximo)
                                                Tope Máximo:
                                                <strong>${{ number_format($plan->politicaRecargo->tope_maximo, 2) }}</strong>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal"
                                            data-target="#modalEditRecargo"><i class="fa fa-pencil"></i> Modificar
                                            Recargo</button>
                                        <form
                                            action="{{ route('planes.politicas.recargo.destroy', [$plan->id, $plan->politicaRecargo->id]) }}"
                                            method="POST" style="display:inline-block;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Eliminar la política de recargo?');"><i
                                                    class="fa fa-trash"></i> Eliminar</button>
                                        </form>
                                    </div>
                                @else
                                    <p class="text-muted text-center" style="margin: 20px 0;">Este plan no genera recargos
                                        por atraso.</p>
                                    <div class="text-center">
                                        <button class="btn btn-danger btn-sm" data-toggle="modal"
                                            data-target="#modalAddRecargo"><i class="fa fa-plus"></i> Configurar
                                            Recargo</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <x-modal id="modalAddDescuento" title="Nuevo Descuento" size="modal-md">
        <form action="{{ route('planes.politicas.descuento.store', $plan->id) }}" method="POST">
            @csrf
            <div class="form-group"><label>Nombre del Descuento</label><input type="text" name="nombre"
                    class="form-control" placeholder="Ej: Pronto Pago" required></div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group"><label>Día Límite del mes</label><input type="number" name="dia_limite"
                            class="form-control" placeholder="Ej: 10"></div>
                </div>
                <div class="col-md-6">
                    <div class="form-group"><label>Tipo de Descuento</label>
                        <select name="tipo_valor" class="form-control" required>
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="monto_fijo">Monto Fijo ($)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group"><label>Valor</label><input type="number" step="0.01" name="valor"
                    class="form-control" required></div>
            <div class="text-right"><button type="button" class="btn btn-default"
                    data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-warning">Guardar
                    Descuento</button></div>
        </form>
    </x-modal>

    @foreach ($plan->politicasDescuento as $descuento)
        <x-modal id="modalEditDescuento{{ $descuento->id }}" title="Editar Descuento" size="modal-md">
            <form action="{{ route('planes.politicas.descuento.update', [$plan->id, $descuento->id]) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group"><label>Nombre</label><input type="text" name="nombre" class="form-control"
                        value="{{ $descuento->nombre }}" required></div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Día Límite</label><input type="number" name="dia_limite"
                                class="form-control" value="{{ $descuento->dia_limite }}"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>Tipo</label>
                            <select name="tipo_valor" class="form-control" required>
                                <option value="porcentaje" {{ $descuento->tipo_valor == 'porcentaje' ? 'selected' : '' }}>
                                    Porcentaje (%)</option>
                                <option value="monto_fijo" {{ $descuento->tipo_valor == 'monto_fijo' ? 'selected' : '' }}>
                                    Monto Fijo ($)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group"><label>Valor</label><input type="number" step="0.01" name="valor"
                        class="form-control" value="{{ $descuento->valor }}" required></div>
                <div class="text-right"><button type="submit" class="btn btn-warning">Actualizar</button></div>
            </form>
        </x-modal>
    @endforeach

    @if (!$plan->politicaRecargo)
        <x-modal id="modalAddRecargo" title="Configurar Recargo" size="modal-md">
            <form action="{{ route('planes.politicas.recargo.store', $plan->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Aplicar después del día</label><input type="number"
                                name="dia_limite_pago" class="form-control" placeholder="Ej: 10" required></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>Tipo</label><select name="tipo_recargo" class="form-control"
                                required>
                                <option value="porcentaje">Porcentaje (%)</option>
                                <option value="monto_fijo">Monto Fijo ($)</option>
                            </select></div>
                    </div>
                </div>
                <div class="form-group"><label>Valor del Recargo</label><input type="number" step="0.01"
                        name="valor" class="form-control" required></div>
                <div class="form-group"><label>Tope Máximo ($) <small>(Opcional)</small></label><input type="number"
                        step="0.01" name="tope_maximo" class="form-control" placeholder="Límite máximo a cobrar">
                </div>
                <div class="text-right"><button type="submit" class="btn btn-danger">Guardar Recargo</button></div>
            </form>
        </x-modal>
    @else
        <x-modal id="modalEditRecargo" title="Modificar Recargo" size="modal-md">
            <form action="{{ route('planes.politicas.recargo.update', [$plan->id, $plan->politicaRecargo->id]) }}"
                method="POST">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label>Aplicar después del día</label><input type="number"
                                name="dia_limite_pago" class="form-control"
                                value="{{ $plan->politicaRecargo->dia_limite_pago }}" required></div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group"><label>Tipo</label><select name="tipo_recargo" class="form-control"
                                required>
                                <option value="porcentaje"
                                    {{ $plan->politicaRecargo->tipo_recargo == 'porcentaje' ? 'selected' : '' }}>Porcentaje
                                    (%)</option>
                                <option value="monto_fijo"
                                    {{ $plan->politicaRecargo->tipo_recargo == 'monto_fijo' ? 'selected' : '' }}>Monto Fijo
                                    ($)</option>
                            </select></div>
                    </div>
                </div>
                <div class="form-group"><label>Valor</label><input type="number" step="0.01" name="valor"
                        class="form-control" value="{{ $plan->politicaRecargo->valor }}" required></div>
                <div class="form-group"><label>Tope Máximo ($) <small>(Opcional)</small></label><input type="number"
                        step="0.01" name="tope_maximo" class="form-control"
                        value="{{ $plan->politicaRecargo->tope_maximo }}"></div>
                <div class="text-right"><button type="submit" class="btn btn-danger">Actualizar Recargo</button></div>
            </form>
        </x-modal>
    @endif

@endsection
