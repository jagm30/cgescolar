@extends('layouts.master')

@section('page_title', 'Historial de pagos')
@section('page_subtitle', $alumno->nombre_completo)

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li><a href="{{ route('portal.hijos') }}">Mis hijos</a></li>
    <li class="active">Pagos</li>
@endsection

@push('styles')
    @include('portal._styles')
@endpush

@section('content')
    <div class="portal-card">
        <div class="portal-card-header">
            <h4 class="portal-card-title"><i class="fa fa-credit-card"></i> Pagos de {{ $alumno->nombre_completo }}</h4>
            <a href="{{ route('portal.estado-cuenta', $alumno->id) }}" class="btn btn-default btn-sm btn-flat">
                <i class="fa fa-file-text-o"></i> Estado de cuenta
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover portal-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Conceptos</th>
                        <th>Forma de pago</th>
                        <th class="text-right">Monto</th>
                        <th class="text-center">Factura</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos as $pago)
                        <tr>
                            <td><code>{{ $pago['folio_recibo'] ?: 'N/A' }}</code></td>
                            <td>{{ $pago['fecha_pago']?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td>{{ $pago['conceptos'] ?: 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $pago['forma_pago'] ?? 'N/A')) }}</td>
                            <td class="text-right">${{ number_format($pago['monto_total'], 2) }}</td>
                            <td class="text-center">
                                @if ($pago['tiene_factura'])
                                    <span class="portal-pill portal-pill-ok">
                                        <i class="fa fa-check"></i> {{ $pago['cfdi_uuid'] ?: 'Emitida' }}
                                    </span>
                                @else
                                    <span class="portal-pill portal-pill-warn">Sin factura</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="portal-empty">
                                    <i class="fa fa-credit-card" style="font-size:34px;margin-bottom:10px;"></i>
                                    <div>Sin pagos registrados para este alumno.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
