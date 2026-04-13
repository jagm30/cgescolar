@extends('layouts.master')

@section('page_title', 'Recibo de pago')
@section('page_subtitle', 'Folio: ' . $pago->folio_recibo)

@section('breadcrumb')
    <li><a href="{{ route('cobros.index') }}">Cobros</a></li>
    <li class="active">Recibo {{ $pago->folio_recibo }}</li>
@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    .main-header, .main-sidebar, .main-footer { display: none !important; }
    .recibo-wrap { max-width: 100%; box-shadow: none; }
}
.recibo-wrap {
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}
.recibo-header {
    background: linear-gradient(135deg, #1e4d7b, #3c8dbc);
    padding: 24px 30px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.recibo-folio {
    font-size: 13px;
    opacity: .8;
    margin-bottom: 4px;
}
.recibo-folio strong {
    font-family: monospace;
    font-size: 18px;
    opacity: 1;
    display: block;
    letter-spacing: .08em;
}
.recibo-alumno {
    background: #f7fbff;
    border-bottom: 1px solid #e8f0f8;
    padding: 16px 30px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.recibo-alumno-nombre {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
}
.recibo-alumno-sub {
    font-size: 12px;
    color: #999;
    margin-top: 3px;
}
.recibo-tabla {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.recibo-tabla th {
    background: #f5f5f5;
    padding: 10px 20px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #999;
    font-weight: 600;
    border-bottom: 1px solid #eee;
}
.recibo-tabla td {
    padding: 12px 20px;
    border-bottom: 1px solid #f5f5f5;
    vertical-align: middle;
}
.recibo-tabla tr:last-child td { border-bottom: none; }
.recibo-total {
    background: linear-gradient(135deg, #1e4d7b, #3c8dbc);
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
}
.recibo-meta {
    padding: 16px 30px;
    background: #fafafa;
    border-top: 1px solid #eee;
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #888;
}
.recibo-meta strong { color: #333; display: block; font-size: 13px; }
</style>
@endpush

@section('content')

{{-- Alertas --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible no-print">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Acciones --}}
<div class="text-right" style="margin-bottom:14px;" class="no-print">
    <button onclick="window.print()" class="btn btn-default btn-flat no-print">
        <i class="fa fa-print"></i> Imprimir
    </button>
    <a href="{{ route('cobros.index') }}" class="btn btn-success btn-flat no-print">
        <i class="fa fa-plus"></i> Nuevo cobro
    </a>
</div>

<div class="recibo-wrap">

    {{-- Encabezado --}}
    <div class="recibo-header">
        <div>
            <div style="font-size:11px;opacity:.7;text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px;">
                Recibo de pago
            </div>
            <div style="font-size:24px;font-weight:700;">{{ config('app.name', 'CGesEscolar') }}</div>
            <div style="font-size:12px;opacity:.75;margin-top:4px;">
                {{ $pago->fecha_pago->format('d \d\e F \d\e Y') }}
            </div>
        </div>
        <div style="text-align:right;">
            <div class="recibo-folio">
                Folio
                <strong>{{ $pago->folio_recibo }}</strong>
            </div>
            <div style="margin-top:10px;">
                <span style="background:rgba(255,255,255,.2);color:#fff;
                              padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">
                    {{ strtoupper($pago->forma_pago) }}
                </span>
            </div>
            @if($pago->referencia)
            <div style="font-size:11px;opacity:.7;margin-top:6px;">
                Ref: {{ $pago->referencia }}
            </div>
            @endif
        </div>
    </div>

    {{-- Alumno --}}
    @if($alumno)
    <div class="recibo-alumno">
        <div style="width:46px;height:46px;border-radius:50%;background:#3c8dbc;flex-shrink:0;
                    display:flex;align-items:center;justify-content:center;overflow:hidden;">
            @if($alumno->foto_url)
                <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                     style="width:100%;height:100%;object-fit:cover;">
            @else
                <i class="fa fa-user" style="color:#fff;font-size:18px;"></i>
            @endif
        </div>
        <div>
            <div class="recibo-alumno-nombre">
                {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
            </div>
            <div class="recibo-alumno-sub">
                <code style="font-size:11px;background:#e8f0fb;padding:1px 6px;border-radius:8px;">
                    {{ $alumno->matricula }}
                </code>
            </div>
        </div>
    </div>
    @endif

    {{-- Conceptos pagados --}}
    <table class="recibo-tabla">
        <thead>
            <tr>
                <th style="width:40%;">Concepto</th>
                <th style="width:13%;text-align:right;">Monto</th>
                <th style="width:13%;text-align:right;">Desc. beca</th>
                <th style="width:13%;text-align:right;">Desc. extra</th>
                <th style="width:11%;text-align:right;">Recargo</th>
                <th style="width:13%;text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pago->detalles as $detalle)
            <tr>
                <td>
                    <div style="font-size:14px;font-weight:600;color:#222;">
                        {{ $detalle->cargo->concepto->nombre }}
                    </div>
                    <div style="font-size:11px;color:#aaa;margin-top:2px;">
                        Periodo {{ $detalle->cargo->periodo }}
                    </div>
                </td>
                <td style="text-align:right;">${{ number_format($detalle->monto_abonado, 2) }}</td>
                <td style="text-align:right;color:#27ae60;">
                    @if($detalle->descuento_beca > 0)
                        -${{ number_format($detalle->descuento_beca, 2) }}
                    @else
                        <span style="color:#ddd;">—</span>
                    @endif
                </td>
                <td style="text-align:right;color:#27ae60;">
                    @if($detalle->descuento_otros > 0)
                        -${{ number_format($detalle->descuento_otros, 2) }}
                    @else
                        <span style="color:#ddd;">—</span>
                    @endif
                </td>
                <td style="text-align:right;color:#e74c3c;">
                    @if($detalle->recargo_aplicado > 0)
                        +${{ number_format($detalle->recargo_aplicado, 2) }}
                    @else
                        <span style="color:#ddd;">—</span>
                    @endif
                </td>
                <td style="text-align:right;font-weight:700;font-size:15px;">
                    ${{ number_format($detalle->monto_final, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total --}}
    <div class="recibo-total">
        <div style="font-size:14px;opacity:.85;">Total pagado</div>
        <div style="font-size:32px;font-weight:700;">${{ number_format($pago->monto_total, 2) }}</div>
    </div>

    {{-- Metadatos --}}
    <div class="recibo-meta">
        <div>
            <strong>{{ $pago->cajero->nombre ?? 'Sistema' }}</strong>
            Cajero
        </div>
        <div>
            <strong>{{ $pago->fecha_pago->format('d/m/Y') }}</strong>
            Fecha
        </div>
        <div>
            <strong>{{ ucfirst($pago->forma_pago) }}</strong>
            Forma de pago
        </div>
        @if($pago->referencia)
        <div>
            <strong>{{ $pago->referencia }}</strong>
            Referencia
        </div>
        @endif
        <div style="margin-left:auto;text-align:right;">
            <strong style="color:#27ae60;font-size:14px;">
                <i class="fa fa-check-circle"></i> Vigente
            </strong>
            Estado del recibo
        </div>
    </div>

</div>

<div class="text-center no-print" style="margin-top:16px;">
    <a href="{{ route('alumnos.estado-cuenta', $alumno?->id) }}"
       class="btn btn-info btn-flat btn-sm" style="margin-right:6px;">
        <i class="fa fa-list-alt"></i> Ver estado de cuenta
    </a>
    <a href="{{ route('cobros.alumno', $alumno?->id) }}"
       class="btn btn-default btn-flat btn-sm">
        <i class="fa fa-dollar"></i> Otro cobro a este alumno
    </a>
</div>

@endsection
