@extends('layouts.master')

@section('page_title', 'Detalle del plan')
@section('page_subtitle', $plan->nombre)

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Datos generales</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Nombre</dt>
                        <dd>{{ $plan->nombre }}</dd>

                        <dt>Ciclo</dt>
                        <dd>{{ $plan->ciclo?->nombre ?? 'Sin ciclo' }}</dd>

                        <dt>Nivel</dt>
                        <dd>{{ $plan->nivel?->nombre ?? 'Sin nivel' }}</dd>

                        <dt>Periodicidad</dt>
                        <dd>{{ ucfirst($plan->periodicidad) }}</dd>

                        <dt>Vigencia</dt>
                        <dd>
                            {{ optional($plan->fecha_inicio)->format('d/m/Y') }}
                            al
                            {{ optional($plan->fecha_fin)->format('d/m/Y') }}
                        </dd>

                        <dt>Estado</dt>
                        <dd>
                            <span class="label {{ $plan->activo ? 'label-success' : 'label-default' }}">
                                {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="box-footer">
                    <a href="{{ route('planes.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </a>
                </div>
            </div>

            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $plan->planPagoConceptos->count() }}</h3>
                    <p>Conceptos configurados</p>
                </div>
                <div class="icon"><i class="fa fa-list"></i></div>
            </div>

            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $plan->politicasDescuento->count() }}</h3>
                    <p>Descuentos activos</p>
                </div>
                <div class="icon"><i class="fa fa-tags"></i></div>
            </div>

            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $plan->asignaciones->count() }}</h3>
                    <p>Asignaciones registradas</p>
                </div>
                <div class="icon"><i class="fa fa-link"></i></div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list-alt"></i> Conceptos del plan</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plan->planPagoConceptos as $concepto)
                                <tr>
                                    <td>{{ $concepto->concepto?->nombre ?? 'Sin concepto' }}</td>
                                    <td>$ {{ number_format((float) $concepto->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted" style="padding: 18px;">
                                        Este plan aún no tiene conceptos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-tags"></i> Descuentos configurados</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Día límite</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plan->politicasDescuento as $descuento)
                                <tr>
                                    <td>{{ $descuento->nombre }}</td>
                                    <td>{{ $descuento->tipo_valor === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}</td>
                                    <td>
                                        {{ $descuento->tipo_valor === 'porcentaje' ? number_format((float) $descuento->valor, 2).' %' : '$ '.number_format((float) $descuento->valor, 2) }}
                                    </td>
                                    <td>{{ $descuento->dia_limite ?: 'Sin límite' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted" style="padding: 18px;">
                                        No hay descuentos configurados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Recargo por mora</h3>
                </div>
                <div class="box-body">
                    @php
                        $recargo = $plan->politicasRecargo->first();
                    @endphp

                    @if ($recargo)
                        <dl class="dl-horizontal" style="margin-bottom: 0;">
                            <dt>Día límite</dt>
                            <dd>{{ $recargo->dia_limite_pago }}</dd>

                            <dt>Tipo</dt>
                            <dd>{{ $recargo->tipo_recargo === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}</dd>

                            <dt>Valor</dt>
                            <dd>
                                {{ $recargo->tipo_recargo === 'porcentaje' ? number_format((float) $recargo->valor, 2).' %' : '$ '.number_format((float) $recargo->valor, 2) }}
                            </dd>

                            <dt>Tope máximo</dt>
                            <dd>{{ $recargo->tope_maximo ? '$ '.number_format((float) $recargo->tope_maximo, 2) : 'Sin tope' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted" style="margin-bottom: 0;">Este plan no tiene recargo configurado.</p>
                    @endif
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-link"></i> Asignaciones</h3>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Vigencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($plan->asignaciones as $asignacion)
                                <tr>
                                    <td>{{ ucfirst($asignacion->origen) }}</td>
                                    <td>
                                        @if ($asignacion->origen === 'individual')
                                            {{ $asignacion->alumno?->nombre_completo ?? 'Alumno no disponible' }}
                                        @elseif ($asignacion->origen === 'grupo')
                                            {{ ($asignacion->grupo?->grado?->nombre ?? '') . ' ' . ($asignacion->grupo?->nombre ?? '') }}
                                        @else
                                            {{ $asignacion->nivel?->nombre ?? 'Nivel no disponible' }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($asignacion->fecha_inicio)->format('d/m/Y') ?? 'Inmediata' }}
                                        —
                                        {{ optional($asignacion->fecha_fin)->format('d/m/Y') ?? 'Abierta' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted" style="padding: 18px;">
                                        Este plan aún no tiene asignaciones.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
