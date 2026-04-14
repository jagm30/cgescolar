@extends('layouts.master')

@section('page_title', 'Detalle del cargo')
@section('page_subtitle', $cargo->concepto?->nombre)

@section('breadcrumb')
    <li><a href="{{ route('cargos.index') }}">Cargos</a></li>
@endsection

@section('content')
    @php
        $estado = $cargo->estado_real;

        $claseEstado = match ($estado) {
            'pagado' => 'label-success',
            'parcial', 'parcial_vencido' => 'label-warning',
            'vencido' => 'label-danger',
            'condonado' => 'label-info',
            default => 'label-default',
        };

        $grupo = $cargo->inscripcion?->grupo;

        // Blindaje por si preview no viene definido
        $preview = $preview ?? [
            'monto_original' => 0,
            'descuento_beca' => 0,
            'beca_aplicada' => null,
            'descuento_otros' => 0,
            'descuento_manual' => 0,
            'descuentos_detalle' => [],
            'recargo' => 0,
            'saldo_ya_abonado' => 0,
            'total_a_cobrar' => 0,
        ];
    @endphp

    <div class="row">
        <div class="col-md-4">

            {{-- ALUMNO --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-user"></i> Alumno</h3>
                </div>
                <div class="box-body">
                    <p><strong>{{ $cargo->inscripcion?->alumno?->nombre_completo ?? 'Sin alumno' }}</strong></p>
                    <p class="text-muted">Matrícula: {{ $cargo->inscripcion?->alumno?->matricula ?? 'N/D' }}</p>
                    <p class="text-muted">
                        Grupo:
                        {{ $grupo ? ($grupo->grado?->nombre . ' ' . $grupo->nombre) : 'Sin grupo' }}
                    </p>
                </div>
            </div>

            {{-- DATOS --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Datos del cargo</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Concepto</dt>
                        <dd>{{ $cargo->concepto?->nombre ?? 'Sin concepto' }}</dd>

                        <dt>Período</dt>
                        <dd>{{ $cargo->periodo }}</dd>

                        <dt>Vencimiento</dt>
                        <dd class="{{ in_array($estado, ['vencido', 'parcial_vencido']) ? 'text-red' : '' }}">
                            {{ optional($cargo->fecha_vencimiento)->format('d/m/Y') ?? 'Sin fecha' }}
                        </dd>

                        <dt>Estado</dt>
                        <dd>
                            <span class="label {{ $claseEstado }}">
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </span>
                        </dd>

                        <dt>Origen</dt>
                        <dd>{{ ucfirst($cargo->asignacion?->origen ?? 'manual') }}</dd>
                    </dl>
                </div>

                <div class="box-footer">
                    <a href="{{ route('cargos.index') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">

            {{-- RESUMEN --}}
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calculator"></i> Resumen de cobro</h3>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th>Monto original</th>
                                <td>$ {{ number_format((float) $preview['monto_original'], 2) }}</td>
                            </tr>

                            <tr>
                                <th>Descuento por beca</th>
                                <td>
                                    $ {{ number_format((float) $preview['descuento_beca'], 2) }}
                                    @if ($preview['beca_aplicada'])
                                        <br><small class="text-muted">{{ $preview['beca_aplicada'] }}</small>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>Otros descuentos</th>
                                <td>
                                    $ {{ number_format((float) $preview['descuento_otros'], 2) }}

                                    @if (!empty($preview['descuentos_detalle']))
                                        <ul class="list-unstyled" style="margin-top:6px;">
                                            @foreach ($preview['descuentos_detalle'] as $d)
                                                <li>
                                                    <small class="text-muted">
                                                        {{ $d['nombre'] }}:
                                                        $ {{ number_format((float) $d['monto'], 2) }}
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>Descuentos manuales</th>
                                <td>$ {{ number_format((float) $preview['descuento_manual'], 2) }}</td>
                            </tr>

                            <tr>
                                <th>Recargo</th>
                                <td>$ {{ number_format((float) $preview['recargo'], 2) }}</td>
                            </tr>

                            <tr>
                                <th>Total abonado</th>
                                <td>$ {{ number_format((float) $preview['saldo_ya_abonado'], 2) }}</td>
                            </tr>

                            <tr class="success">
                                <th style="font-size:16px;">Total a cobrar hoy</th>
                                <td style="font-size:18px;">
                                    <strong>$ {{ number_format((float) $preview['total_a_cobrar'], 2) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ABONOS --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-credit-card"></i> Abonos registrados</h3>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Fecha</th>
                                <th>Monto abonado</th>
                                <th>Recargo</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cargo->detallesPagosVigentes as $detalle)
                                <tr>
                                    <td>{{ $detalle->pago?->folio_recibo ?? 'Sin folio' }}</td>
                                    <td>{{ optional($detalle->pago?->fecha_pago)->format('d/m/Y') ?? '-' }}</td>
                                    <td>$ {{ number_format((float) $detalle->monto_abonado, 2) }}</td>
                                    <td>$ {{ number_format((float) $detalle->recargo_aplicado, 2) }}</td>
                                    <td>$ {{ number_format((float) $detalle->monto_final, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted" style="padding:18px;">
                                        Este cargo aún no tiene pagos aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- DESCUENTOS --}}
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-minus-circle"></i> Descuentos manuales</h3>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Motivo</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cargo->descuentos as $descuento)
                                <tr>
                                    <td>{{ $descuento->motivo ?? 'Sin motivo' }}</td>
                                    <td>$ {{ number_format((float) $descuento->monto, 2) }}</td>
                                    <td>{{ optional($descuento->created_at)->format('d/m/Y H:i') ?? 'N/D' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted" style="padding:18px;">
                                        No hay descuentos manuales aplicados.
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
