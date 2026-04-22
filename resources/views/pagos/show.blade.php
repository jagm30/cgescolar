@extends('layouts.master')

@section('page_title', 'Recibo ' . $pago->folio_recibo)
@section('page_subtitle', 'Detalle del pago')

@section('breadcrumb')
    <li><a href="{{ route('pagos.index') }}">Historial de pagos</a></li>
    <li class="active">{{ $pago->folio_recibo }}</li>
@endsection

@push('styles')
<style>
.recibo-card {
    border: 1px solid #e4eaf0; border-radius: 10px; background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden; margin-bottom: 20px;
}
.recibo-card-header {
    padding: 12px 16px; border-bottom: 1px solid #f0f3f7;
    background: #f8fafc; display: flex; align-items: center; gap: 8px;
}
.recibo-card-title {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7a8d;
}
.info-row {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 10px 16px; border-bottom: 1px solid #f5f7fa; font-size: 13px;
}
.info-row:last-child { border-bottom: none; }
.info-lbl  { color: #8a9ab0; font-size: 12px; min-width: 110px; }
.info-val  { font-weight: 600; color: #1a2634; text-align: right; }

.det-table { width: 100%; border-collapse: collapse; }
.det-table thead th {
    background: #f4f6f8; color: #6b7a8d;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; padding: 9px 12px;
    border-bottom: 2px solid #e4eaf0;
}
.det-table tbody tr { border-bottom: 1px solid #f0f3f7; }
.det-table tbody tr:last-child { border-bottom: none; }
.det-table td { padding: 10px 12px; vertical-align: middle; font-size: 13px; }
.det-table tfoot td {
    padding: 10px 12px; font-size: 13px;
    border-top: 2px solid #e4eaf0; background: #f8fafc;
}

.folio-banner {
    background: linear-gradient(135deg, #1a6b3a 0%, #27a05a 100%);
    border-radius: 8px; padding: 18px 22px; margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
    box-shadow: 0 4px 14px rgba(39,160,90,.22);
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" style="border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible" style="border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

@php
    $esAnulado   = $pago->estado === 'anulado';
    $totalDesc   = $pago->detalles->sum(fn($d) => (float)$d->descuento_beca + (float)$d->descuento_otros);
    $totalRecarg = $pago->detalles->sum(fn($d) => (float)$d->recargo_aplicado);
    $alumnos     = $pago->detalles
        ->map(fn($d) => $d->cargo?->inscripcion?->alumno)
        ->filter()->unique('id')->values();
@endphp

{{-- ══ BANNER FOLIO ══ --}}
<div class="folio-banner">
    <div>
        <div style="font-size:11px;color:rgba(255,255,255,.65);margin-bottom:3px;">Recibo</div>
        <div style="font-size:22px;font-weight:800;color:#fff;font-family:monospace;letter-spacing:.05em;">
            {{ $pago->folio_recibo }}
        </div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:4px;">
            {{ $pago->fecha_pago->format('d/m/Y') }}
            &nbsp;·&nbsp;
            Cajero: {{ $pago->cajero?->nombre ?? '—' }}
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <div style="text-align:right;">
            <div style="font-size:11px;color:rgba(255,255,255,.65);">Total cobrado</div>
            <div style="font-size:28px;font-weight:800;color:{{ $esAnulado ? 'rgba(255,255,255,.4)' : '#fff' }};
                        {{ $esAnulado ? 'text-decoration:line-through;' : '' }}">
                ${{ number_format($pago->monto_total, 2) }}
            </div>
        </div>
        @if($esAnulado)
        <span style="background:#fdecea;color:#b91c1c;font-size:12px;font-weight:700;
                     padding:4px 12px;border-radius:10px;border:1px solid #fca5a5;">
            <i class="fa fa-ban"></i> Anulado
        </span>
        @else
        <span style="background:#e8f8f0;color:#00875a;font-size:12px;font-weight:700;
                     padding:4px 12px;border-radius:10px;border:1px solid #b3e8d0;">
            <i class="fa fa-check-circle"></i> Vigente
        </span>
        @endif
    </div>
</div>

<div class="row">

{{-- ══ COLUMNA PRINCIPAL ══ --}}
<div class="col-md-8">

    {{-- Detalles de cargos --}}
    <div class="recibo-card">
        <div class="recibo-card-header">
            <i class="fa fa-list-alt" style="color:#27a05a;"></i>
            <span class="recibo-card-title">Conceptos pagados</span>
            <span style="background:#e8f5ee;color:#27a05a;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;margin-left:4px;">
                {{ $pago->detalles->count() }}
            </span>
        </div>
        <div style="overflow-x:auto;">
            <table class="det-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Alumno</th>
                        <th style="text-align:right;">Monto orig.</th>
                        <th style="text-align:right;">Dto. beca</th>
                        <th style="text-align:right;">Dto. otros</th>
                        <th style="text-align:right;">Recargo</th>
                        <th style="text-align:right;">Abonado</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($pago->detalles as $detalle)
                @php $cargo = $detalle->cargo; @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;color:#1a2634;">
                            {{ $cargo?->concepto?->nombre ?? '—' }}
                        </div>
                        @if($cargo?->periodo)
                        <code style="font-size:11px;background:#f0f3f7;padding:1px 6px;border-radius:3px;color:#4a5568;">
                            {{ $cargo->periodo }}
                        </code>
                        @endif
                    </td>
                    <td style="color:#4a5568;">
                        @php $alumno = $cargo?->inscripcion?->alumno; @endphp
                        @if($alumno)
                            <a href="{{ route('alumnos.show', $alumno->id) }}"
                               style="color:#3c8dbc;text-decoration:none;font-size:12px;">
                                {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }},
                                {{ $alumno->nombre }}
                            </a>
                            <div style="font-size:11px;color:#b0bec5;">{{ $alumno->matricula }}</div>
                        @else
                            <span style="color:#b0bec5;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;color:#1a2634;font-weight:600;">
                        ${{ number_format($cargo?->monto_original ?? 0, 2) }}
                    </td>
                    <td style="text-align:right;">
                        @if((float)$detalle->descuento_beca > 0)
                            <span style="color:#00875a;font-weight:600;">
                                -${{ number_format($detalle->descuento_beca, 2) }}
                            </span>
                        @else
                            <span style="color:#dde4eb;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @if((float)$detalle->descuento_otros > 0)
                            <span style="color:#00875a;font-weight:600;">
                                -${{ number_format($detalle->descuento_otros, 2) }}
                            </span>
                        @else
                            <span style="color:#dde4eb;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @if((float)$detalle->recargo_aplicado > 0)
                            <span style="color:#b91c1c;font-weight:600;">
                                +${{ number_format($detalle->recargo_aplicado, 2) }}
                            </span>
                        @else
                            <span style="color:#dde4eb;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:700;color:#1a2634;">
                        ${{ number_format($detalle->monto_abonado, 2) }}
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    @if($totalDesc > 0)
                    <tr>
                        <td colspan="5" style="text-align:right;color:#8a9ab0;font-size:12px;">Descuentos aplicados</td>
                        <td colspan="2" style="text-align:right;color:#00875a;font-weight:700;">
                            -${{ number_format($totalDesc, 2) }}
                        </td>
                    </tr>
                    @endif
                    @if($totalRecarg > 0)
                    <tr>
                        <td colspan="5" style="text-align:right;color:#8a9ab0;font-size:12px;">Recargos aplicados</td>
                        <td colspan="2" style="text-align:right;color:#b91c1c;font-weight:700;">
                            +${{ number_format($totalRecarg, 2) }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="5" style="text-align:right;font-weight:700;color:#1a2634;">Total cobrado</td>
                        <td colspan="2" style="text-align:right;font-weight:800;color:#1a2634;font-size:15px;">
                            ${{ number_format($pago->monto_total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Info de anulación --}}
    @if($esAnulado)
    <div class="recibo-card" style="border-color:#fca5a5;">
        <div class="recibo-card-header" style="background:#fdecea;">
            <i class="fa fa-ban" style="color:#b91c1c;"></i>
            <span class="recibo-card-title" style="color:#b91c1c;">Información de anulación</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Autorizado por</span>
            <span class="info-val">{{ $pago->autorizadoPor?->nombre ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Motivo</span>
            <span class="info-val" style="text-align:left;max-width:300px;">{{ $pago->motivo }}</span>
        </div>
    </div>
    @endif

</div>{{-- /col-md-8 --}}

{{-- ══ COLUMNA LATERAL ══ --}}
<div class="col-md-4">

    {{-- Datos del pago --}}
    <div class="recibo-card">
        <div class="recibo-card-header">
            <i class="fa fa-info-circle" style="color:#3c8dbc;"></i>
            <span class="recibo-card-title">Datos del pago</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Folio</span>
            <code style="font-size:12px;background:#f0f3f7;padding:2px 8px;border-radius:4px;font-weight:700;">
                {{ $pago->folio_recibo }}
            </code>
        </div>
        <div class="info-row">
            <span class="info-lbl">Fecha</span>
            <span class="info-val">{{ $pago->fecha_pago->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Cajero</span>
            <span class="info-val">{{ $pago->cajero?->nombre ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-lbl">Forma de pago</span>
            <span class="info-val">{{ ucfirst($pago->forma_pago) }}</span>
        </div>
        @if($pago->referencia)
        <div class="info-row">
            <span class="info-lbl">Referencia</span>
            <span class="info-val">{{ $pago->referencia }}</span>
        </div>
        @endif
        @if($alumnos->isNotEmpty())
        <div class="info-row" style="align-items:flex-start;">
            <span class="info-lbl">Alumno(s)</span>
            <div style="text-align:right;">
                @foreach($alumnos as $alumno)
                <div style="font-size:12px;font-weight:600;color:#1a2634;">
                    {{ $alumno->ap_paterno }} {{ $alumno->nombre }}
                </div>
                <div style="font-size:11px;color:#b0bec5;">{{ $alumno->matricula }}</div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Anular (solo admin, pago vigente) --}}
    @if(auth()->user()->esAdministrador() && !$esAnulado)
    <div class="recibo-card" style="border-color:#fca5a5;">
        <div class="recibo-card-header" style="background:#fdecea;">
            <i class="fa fa-ban" style="color:#b91c1c;"></i>
            <span class="recibo-card-title" style="color:#b91c1c;">Anular pago</span>
        </div>
        <div style="padding:14px 16px;">
            <form method="POST" action="{{ route('pagos.anular', $pago->id) }}"
                  id="form-anular"
                  onsubmit="return confirm('¿Confirma la anulación del pago {{ $pago->folio_recibo }}? Esta acción revertirá los estados de los cargos.')">
                @csrf
                @method('POST')
                <div style="margin-bottom:10px;">
                    <label style="font-size:11px;color:#b91c1c;font-weight:700;margin-bottom:4px;display:block;">
                        Motivo de anulación <span style="color:#b91c1c;">*</span>
                    </label>
                    <textarea name="motivo" rows="3"
                              class="form-control"
                              placeholder="Describe el motivo de la anulación…"
                              style="border-radius:6px;border-color:#fca5a5;font-size:13px;resize:vertical;"
                              required minlength="10" maxlength="500"></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-sm btn-flat btn-block"
                        style="border-radius:6px;">
                    <i class="fa fa-ban"></i> Anular pago
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Navegación --}}
    <div class="recibo-card">
        <div class="recibo-card-header">
            <i class="fa fa-bolt" style="color:#f39c12;"></i>
            <span class="recibo-card-title">Acciones</span>
        </div>
        <div>
            @if($alumnos->count() === 1)
            <a href="{{ route('alumnos.estado-cuenta', $alumnos->first()->id) }}"
               style="display:flex;align-items:center;gap:10px;padding:11px 16px;
                      border-bottom:1px solid #f4f6f8;text-decoration:none;color:#333;
                      font-size:13px;font-weight:500;transition:background .12s;"
               onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background=''">
                <span style="width:32px;height:32px;border-radius:8px;background:#e8f0fb;
                             display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa fa-user" style="color:#3c8dbc;font-size:13px;"></i>
                </span>
                Estado de cuenta
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endif
            <a href="{{ route('pagos.index') }}"
               style="display:flex;align-items:center;gap:10px;padding:11px 16px;
                      text-decoration:none;color:#333;font-size:13px;font-weight:500;transition:background .12s;"
               onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background=''">
                <span style="width:32px;height:32px;border-radius:8px;background:#f0f3f7;
                             display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa fa-arrow-left" style="color:#6b7a8d;font-size:13px;"></i>
                </span>
                Historial de pagos
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
        </div>
    </div>

</div>{{-- /col-md-4 --}}

</div>{{-- /row --}}
@endsection
