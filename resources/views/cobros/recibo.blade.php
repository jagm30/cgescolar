@extends('layouts.master')

@section('page_title', 'Resumen de Cobro')
@section('page_subtitle', 'Folio: ' . $pago->folio_recibo)

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            {{-- Alertas de éxito si viene de pagar --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Tarjeta Principal del Resumen --}}
            <div class="box box-solid" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <div class="box-header with-border" style="padding: 20px; background: #fafafa;">
                    <h3 class="box-title" style="font-weight: 700; color: #2c3e50;">
                        <i class="fa fa-file-text-o text-blue"></i> Operación Exitosa
                    </h3>
                    <div class="box-tools">
                        {{-- ¡AQUÍ ESTÁ LA MAGIA! Llama a la ruta PDF en nueva pestaña --}}
                        <a href="{{ route('cobros.pdf', $pago->id) }}" target="_blank" class="btn btn-primary btn-flat">
                            <i class="fa fa-print"></i> Generar Recibo Oficial (PDF)
                        </a>
                        <a href="{{ route('cobros.index') }}" class="btn btn-success btn-flat">
                            <i class="fa fa-plus"></i> Nuevo Cobro
                        </a>
                    </div>
                </div>

                <div class="box-body" style="padding: 25px;">
                    {{-- Bloque de datos rápidos --}}
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-sm-4">
                            <label style="color:#7f8c8d; font-size:11px; text-transform:uppercase;">Folio de Recibo</label>
                            <p style="font-family: monospace; font-size: 18px; font-weight: bold; color: #1e4d7b;">
                                {{ $pago->folio_recibo }}
                            </p>
                        </div>
                        <div class="col-sm-4">
                            <label style="color:#7f8c8d; font-size:11px; text-transform:uppercase;">Fecha de Pago</label>
                            <p style="font-size: 15px; font-weight: 600;">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-sm-4">
                            <label style="color:#7f8c8d; font-size:11px; text-transform:uppercase;">Método de Pago</label>
                            <p><span class="label label-info"
                                    style="font-size:12px;">{{ strtoupper($pago->forma_pago) }}</span></p>
                        </div>
                    </div>

                    @if ($alumno)
                        <div class="well well-sm"
                            style="background: #f8fafc; border-color: #e2e8f0; padding: 15px; border-radius: 6px;">
                            <label
                                style="color:#94a3b8; font-size:10px; text-transform:uppercase; display:block; margin-bottom:5px;">Alumno
                                Involucrado</label>
                            <span style="font-size: 16px; font-weight: 700; color: #334155;">
                                {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                            </span>
                            <code
                                style="float: right; font-size: 12px; background: #fff; border: 1px solid #e2e8f0;">{{ $alumno->matricula }}</code>
                        </div>
                    @endif

                    {{-- Tabla simple del desglose web --}}
                    <table class="table table-bordered table-striped" style="margin-top: 20px;">
                        <thead>
                            <tr style="background: #f4f6f9; color: #1e4d7b;">
                                <th>Concepto / Periodo</th>
                                <th class="text-right">Monto Base</th>
                                <th class="text-right">Descuentos</th>
                                <th class="text-right">Recargos</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pago->detalles as $detalle)
                                <tr>
                                    <td>
                                        <b>{{ $detalle->cargo->concepto->nombre }}</b>
                                        <small class="text-muted" style="display:block;">Periodo:
                                            {{ $detalle->cargo->periodo }}</small>
                                    </td>
                                    <td class="text-right">${{ number_format($detalle->monto_abonado, 2) }}</td>
                                    <td class="text-right text-green">
                                        -${{ number_format($detalle->descuento_beca + $detalle->descuento_otros, 2) }}
                                    </td>
                                    <td class="text-right text-red">+${{ number_format($detalle->recargo_aplicado, 2) }}
                                    </td>
                                    <td class="text-right" style="font-weight: 700;">
                                        ${{ number_format($detalle->monto_final, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Gran Total --}}
                    <div class="text-right" style="margin-top: 25px; border-top: 2px solid #f4f6f9; padding-top: 15px;">
                        <span style="font-size: 16px; color: #7f8c8d; font-weight: 600; margin-right: 15px;">Monto Total
                            Pagado:</span>
                        <span
                            style="font-size: 28px; font-weight: bold; color: #1e4d7b;">${{ number_format($pago->monto_total, 2) }}</span>
                    </div>
                </div>

                <div class="box-footer" style="background: #fafafa; padding: 15px 25px; text-center">
                    <a href="{{ route('alumnos.estado-cuenta', $alumno?->id) }}" class="btn btn-default btn-flat btn-sm">
                        <i class="fa fa-list-alt"></i> Ver estado de cuenta del alumno
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection
