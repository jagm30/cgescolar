@extends('layouts.master')
@section('page_title', 'Detalles del Plan de Pago')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <div class="text-center" style="margin-bottom: 15px;">
                        <span class="img-circle img-bordered-sm bg-blue" style="padding: 20px; display: inline-block;">
                            <i class="fa fa-university fa-4x"></i>
                        </span>
                    </div>

                    <h3 class="profile-username text-center" style="font-weight: bold;">{{ $plan->nombre }}</h3>
                    <p class="text-muted text-center"><i class="fa fa-graduation-cap"></i>
                        {{ $plan->nivel->nombre ?? 'Nivel no especificado' }}</p>

                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Ciclo Escolar</b> <span
                                class="pull-right text-bold text-blue">{{ $plan->ciclo->nombre ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Periodicidad</b>
                            <span class="pull-right label label-info" style="font-size: 11px;">
                                <i class="fa fa-refresh"></i> {{ ucfirst($plan->periodicidad) }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Vigencia</b>
                            <span class="pull-right text-muted">
                                {{ $plan->fecha_inicio->format('d/m/Y') }} - {{ $plan->fecha_fin->format('d/m/Y') }}
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Estado</b>
                            <span class="pull-right">
                                @if ($plan->activo)
                                    <span class="label label-success"><i class="fa fa-check"></i> Activo</span>
                                @else
                                    <span class="label label-danger"><i class="fa fa-times"></i> Inactivo</span>
                                @endif
                            </span>
                        </li>
                    </ul>

                    <div class="row">
                        <div class="col-xs-6">
                            <a href="{{ route('planes.conceptos.index', $plan->id) }}" class="btn btn-warning btn-block">
                                <i class="fa fa-cog"></i> <b>Configurar</b>
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <a href="{{ route('planes.index') }}" class="btn btn-default btn-block">
                                <i class="fa fa-arrow-left"></i> <b>Catálogo</b>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- CAJA DE CONCEPTOS --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-money text-green"></i> Conceptos Incluidos en el Paquete</h3>
                </div>
                <div class="box-body no-padding">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr class="bg-gray">
                                <th style="width: 40%">Nombre del Concepto</th>
                                <th>Tipo</th>
                                <th class="text-center">Aplica Beca</th>
                                <th class="text-right">Monto Unitario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plan->planPagoConceptos as $detalle)
                                <tr>
                                    <td><strong>{{ $detalle->concepto->nombre ?? 'Concepto eliminado' }}</strong></td>
                                    <td><span
                                            class="text-muted small">{{ ucfirst(str_replace('_', ' ', $detalle->concepto->tipo ?? '')) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($detalle->concepto && $detalle->concepto->aplica_beca)
                                            <i class="fa fa-check-circle text-success"
                                                title="Este concepto permite becas"></i>
                                        @else
                                            <i class="fa fa-minus-circle text-muted" title="Monto fijo sin beca"></i>
                                        @endif
                                    </td>
                                    <td class="text-right"><span
                                            class="text-bold">${{ number_format($detalle->monto, 2) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay conceptos asignados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="box-footer bg-gray-light">
                    <h4 class="text-right" style="margin: 0;">
                        Total del Paquete: <span class="text-success"
                            style="font-weight: 800; font-size: 24px; margin-left: 10px;">
                            ${{ number_format($plan->planPagoConceptos->sum('monto'), 2) }}
                        </span>
                    </h4>
                </div>
            </div>

            <div class="row">
                {{-- DESCUENTOS --}}
                <div class="col-md-6">
                    <div class="info-box bg-yellow-active">
                        <span class="info-box-icon"><i class="fa fa-gift"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" style="font-weight: bold;">Políticas de Descuento</span>
                            <div class="info-box-number" style="font-size: 13px; font-weight: normal;">
                                @forelse ($plan->politicasDescuento as $descuento)
                                    <div>• {{ $descuento->nombre }}:
                                        <strong>{{ $descuento->tipo_valor == 'porcentaje' ? $descuento->valor . '%' : '$' . $descuento->valor }}</strong>
                                    </div>
                                @empty
                                    <span>No hay descuentos configurados</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RECARGOS --}}
                <div class="col-md-6">
                    <div class="info-box bg-red-active">
                        <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" style="font-weight: bold;">Recargo por Mora</span>
                            <div class="info-box-number" style="font-size: 13px; font-weight: normal;">
                                @if ($plan->politicaRecargo)
                                    <div>Aplica después del día
                                        <strong>{{ $plan->politicaRecargo->dia_limite_pago }}</strong>
                                    </div>
                                    <div>Monto:
                                        <strong>{{ $plan->politicaRecargo->tipo_recargo == 'porcentaje' ? $plan->politicaRecargo->valor . '%' : '$' . $plan->politicaRecargo->valor }}</strong>
                                    </div>
                                @else
                                    <span>Este plan no genera recargos</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
