@extends('layouts.master')

@section('page_title', 'Reporte de Deudores')
@section('page_subtitle', 'Adeudos pendientes por alumno')

@section('breadcrumb')
    <li><a href="#">Reportes</a></li>
    <li class="active">Deudores</li>
@endsection

@push('styles')
<style>
.deu-table { width:100%; border-collapse:collapse; }
.deu-table thead th {
    background:#f4f6f8; color:#6b7a8d;
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; padding:9px 12px;
    border-bottom:2px solid #e4eaf0; white-space:nowrap;
}
.deu-table tbody tr { border-bottom:1px solid #f0f3f7; transition:background .1s; }
.deu-table tbody tr:hover td { background:#fff8f6; }
.deu-table td { padding:9px 12px; vertical-align:middle; font-size:13px; }
.deu-table tfoot td {
    padding:9px 12px; font-weight:700; font-size:13px;
    background:#fdf5f5; border-top:2px solid #fca5a5;
}

.deu-badge {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:22px; height:20px; padding:0 6px;
    border-radius:10px; font-size:11px; font-weight:700;
}
.deu-pendiente { background:#fff8e1; color:#b45309; border:1px solid #fde68a; }
.deu-vencido   { background:#fdecea; color:#b91c1c; border:1px solid #fca5a5; }
.deu-parcial   { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }

@media print {
    .sidebar, .main-header, .content-header, .no-print { display:none !important; }
    .content-wrapper { margin-left:0 !important; }
}
</style>
@endpush

@section('content')

{{-- ══ ENCABEZADO + STATS ══ --}}
<div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
            box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-exclamation-triangle text-red"></i> Deudores
        </h4>
        <div style="display:flex;gap:7px;flex-wrap:wrap;">
            <span style="background:#fdecea;color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-users"></i> {{ $resumen['total_deudores'] }} alumnos
            </span>
            <span style="background:#fff8e1;color:#b45309;border:1px solid #fde68a;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-clock-o"></i> {{ $resumen['total_pendientes'] }} pendientes
            </span>
            <span style="background:#fdecea;color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-exclamation-circle"></i> {{ $resumen['total_vencidos'] }} vencidos
            </span>
            <span style="background:#f3e8fd;color:#7c3aed;border:1px solid #ddd6fe;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-money"></i> ${{ number_format($resumen['gran_total'], 0) }} adeudado
            </span>
        </div>
    </div>
    @if($deudores->isNotEmpty())
    <div class="no-print" style="display:flex;gap:6px;flex-shrink:0;">
        <button type="button" onclick="window.print()" class="btn btn-default btn-sm btn-flat"
                style="border-radius:20px;">
            <i class="fa fa-print"></i> Imprimir
        </button>
        <a href="{{ route('reportes.deudores.pdf') }}?ciclo_id={{ $cicloId }}&{{ collect($estados)->map(fn($e) => 'estados[]='.$e)->implode('&') }}"
           target="_blank"
           class="btn btn-danger btn-sm btn-flat"
           style="border-radius:20px;">
            <i class="fa fa-file-pdf-o"></i> PDF
        </a>
        <a href="{{ route('reportes.deudores.pdf-detalle') }}?ciclo_id={{ $cicloId }}&{{ collect($estados)->map(fn($e) => 'estados[]='.$e)->implode('&') }}"
           target="_blank"
           class="btn btn-warning btn-sm btn-flat"
           style="border-radius:20px;">
            <i class="fa fa-file-pdf-o"></i> PDF Detallado
        </a>
    </div>
    @endif
</div>

{{-- ══ PANEL PRINCIPAL ══ --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;">

    {{-- Toolbar + filtros --}}
    <form method="GET" action="{{ route('reportes.deudores') }}"
          class="no-print"
          style="display:flex;align-items:center;gap:8px;padding:10px 14px;
                 background:#f9fafb;border-bottom:1px solid #e8ecf0;flex-wrap:wrap;">

        <select name="ciclo_id" class="form-control input-sm"
                style="border-radius:6px;border-color:#dde4eb;height:32px;max-width:180px;">
            @foreach($ciclos as $ciclo)
                <option value="{{ $ciclo->id }}" {{ $ciclo->id == $cicloId ? 'selected' : '' }}>
                    {{ $ciclo->nombre }}
                </option>
            @endforeach
        </select>

        <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;white-space:nowrap;">
            Incluir:
        </span>

        <label style="display:flex;align-items:center;gap:5px;font-weight:normal;margin:0;cursor:pointer;white-space:nowrap;">
            <input type="checkbox" name="estados[]" value="pendiente"
                   {{ in_array('pendiente', $estados) ? 'checked' : '' }}>
            <span style="background:#fff8e1;color:#b45309;border:1px solid #fde68a;
                         border-radius:10px;padding:1px 8px;font-size:11px;font-weight:700;">
                Pendientes
            </span>
        </label>

        <label style="display:flex;align-items:center;gap:5px;font-weight:normal;margin:0;cursor:pointer;white-space:nowrap;">
            <input type="checkbox" name="estados[]" value="vencido"
                   {{ in_array('vencido', $estados) ? 'checked' : '' }}>
            <span style="background:#fdecea;color:#b91c1c;border:1px solid #fca5a5;
                         border-radius:10px;padding:1px 8px;font-size:11px;font-weight:700;">
                Vencidos
            </span>
        </label>

        <label style="display:flex;align-items:center;gap:5px;font-weight:normal;margin:0;cursor:pointer;white-space:nowrap;">
            <input type="checkbox" name="estados[]" value="parcial"
                   {{ in_array('parcial', $estados) ? 'checked' :'' }}>
            <span style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;
                         border-radius:10px;padding:1px 8px;font-size:11px;font-weight:700;">
                Parciales
            </span>
        </label>

        <button type="submit" class="btn btn-primary btn-sm btn-flat"
                style="border-radius:20px;padding:4px 14px;height:32px;">
            <i class="fa fa-search"></i> Consultar
        </button>

        <span style="background:#fdecea;color:#b91c1c;font-size:12px;font-weight:600;
                     padding:3px 12px;border-radius:12px;white-space:nowrap;margin-left:auto;">
            <i class="fa fa-users"></i> {{ $deudores->count() }} deudor(es)
        </span>
    </form>

    <div style="overflow-x:auto;">
        @if($deudores->isEmpty())
            <div style="padding:56px 20px;text-align:center;">
                <i class="fa fa-check-circle" style="font-size:42px;color:#27ae60;display:block;margin-bottom:12px;"></i>
                <p style="color:#b0bec5;margin:0;font-weight:600;">No hay alumnos con adeudos en este ciclo.</p>
            </div>
        @else
            <table class="deu-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Matrícula</th>
                        <th>Grupo / Nivel</th>
                        <th style="text-align:center;">Pendientes</th>
                        <th style="text-align:center;">Vencidos</th>
                        <th style="text-align:center;">Parciales</th>
                        <th style="text-align:right;">Total adeudo</th>
                        <th style="text-align:center;width:120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deudores as $i => $d)
                    <tr>
                        <td style="color:#94a3b8;font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <span style="font-weight:700;color:#1a2634;">
                                {{ $d['alumno']->ap_paterno }}
                                {{ $d['alumno']->ap_materno }},
                                {{ $d['alumno']->nombre }}
                            </span>
                        </td>
                        <td>
                            <code style="font-size:12px;background:#f0f3f7;padding:2px 7px;
                                         border-radius:4px;color:#4a5568;">
                                {{ $d['alumno']->matricula ?? '—' }}
                            </code>
                        </td>
                        <td style="color:#4a5568;">
                            @if($d['grupo'])
                                {{ $d['grupo']->nombre }}
                                @if($d['nivel'])
                                    <span style="font-size:11px;color:#94a3b8;">/ {{ $d['nivel']->nombre }}</span>
                                @endif
                            @else
                                <span style="color:#b0bec5;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($d['pendientes'] > 0)
                                <span class="deu-badge deu-pendiente">{{ $d['pendientes'] }}</span>
                            @else
                                <span style="color:#b0bec5;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($d['vencidos'] > 0)
                                <span class="deu-badge deu-vencido">{{ $d['vencidos'] }}</span>
                            @else
                                <span style="color:#b0bec5;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($d['parciales'] > 0)
                                <span class="deu-badge deu-parcial">{{ $d['parciales'] }}</span>
                            @else
                                <span style="color:#b0bec5;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;font-weight:700;color:#b91c1c;font-size:14px;">
                            ${{ number_format($d['total_adeudo'], 2) }}
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('alumnos.estado-cuenta', $d['alumno']->id) }}"
                               class="btn btn-xs btn-flat"
                               style="background:#eaf3fb;color:#2980b9;border:1px solid #90c2e7;
                                      border-radius:5px;" title="Ver estado de cuenta">
                                <i class="fa fa-eye"></i> Estado de cuenta
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align:right;color:#64748b;">Gran total:</td>
                        <td style="text-align:right;color:#b91c1c;">
                            ${{ number_format($resumen['gran_total'], 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

</div>

@endsection
