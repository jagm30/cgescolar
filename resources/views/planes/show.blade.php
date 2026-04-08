@extends('layouts.master')
@section('page_title', 'Detalles del Plan de Pago')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <h3 class="profile-username text-center">{{ $plan->nombre }}</h3>
                    <p class="text-muted text-center">{{ $plan->nivel->nombre ?? 'Nivel no especificado' }}</p>

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Ciclo Escolar</b> <a class="pull-right">{{ $plan->ciclo->nombre ?? 'N/A' }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Periodicidad</b> <a class="pull-right"><span
                                    class="label label-info">{{ ucfirst($plan->periodicidad) }}</span></a>
                        </li>
                        <li class="list-group-item">
                            <b>Vigencia</b> <a class="pull-right">{{ $plan->fecha_inicio->format('d/m/Y') }} -
                                {{ $plan->fecha_fin->format('d/m/Y') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Estatus</b>
                            <a class="pull-right">
                                <span class="label {{ $plan->activo ? 'label-success' : 'label-danger' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </a>
                        </li>
                    </ul>

                    <a href="{{ route('planes.index') }}" class="btn btn-default btn-block"><b><i
                                class="fa fa-arrow-left"></i> Volver al Catálogo</b></a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-tags"></i> Conceptos del Plan</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Tipo</th>
                                <th>Aplica Beca</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plan->planPagoConceptos as $detalle)
                                <tr>
                                    <td>{{ $detalle->concepto->nombre ?? 'Concepto eliminado' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $detalle->concepto->tipo ?? '')) }}</td>
                                    <td>
                                        @if ($detalle->concepto && $detalle->concepto->aplica_beca)
                                            <span class="text-success"><i class="fa fa-check"></i> Sí</span>
                                        @else
                                            <span class="text-muted"><i class="fa fa-times"></i> No</span>
                                        @endif
                                    </td>
                                    <td><b class="text-primary">${{ number_format($detalle->monto, 2) }}</b></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay conceptos asignados a este
                                        plan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total del paquete:</th>
                                <th><b
                                        class="text-success">${{ number_format($plan->planPagoConceptos->sum('monto'), 2) }}</b>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-percent"></i> Descuentos</h3>
                        </div>
                        <div class="box-body">
                            @if ($plan->politicasDescuento->count() > 0)
                                <ul>
                                    @foreach ($plan->politicasDescuento as $descuento)
                                        <li>{{ $descuento->nombre }} -
                                            {{ $descuento->tipo_valor == 'porcentaje' ? $descuento->valor . '%' : '$' . $descuento->valor }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">Sin políticas de descuento.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Recargos</h3>
                        </div>
                        <div class="box-body">
                            @if ($plan->politicasRecargo->count() > 0)
                                <ul>
                                    @foreach ($plan->politicasRecargo as $recargo)
                                        <li>Día límite: {{ $recargo->dia_limite_pago }} |
                                            {{ $recargo->tipo_recargo == 'porcentaje' ? $recargo->valor . '%' : '$' . $recargo->valor }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">Sin políticas de recargo.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
