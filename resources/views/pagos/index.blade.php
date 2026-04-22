@extends('layouts.master')

@section('page_title', 'Historial de pagos')
@section('page_subtitle', 'Registro de todos los pagos recibidos')

@section('breadcrumb')
    <li class="active">Historial de pagos</li>
@endsection

@push('styles')
<style>
.hp-hero {
    background: linear-gradient(135deg, #1a6b3a 0%, #27a05a 100%);
    border-radius: 8px; padding: 20px 28px; margin-bottom: 22px;
    display: flex; align-items: center; gap: 0;
    box-shadow: 0 4px 16px rgba(39,160,90,.22);
    flex-wrap: wrap;
}
.hp-stat { text-align: center; padding: 0 24px; border-left: 1px solid rgba(255,255,255,.18); }
.hp-stat:first-child { border-left: none; padding-left: 0; }
.hp-stat-num { font-size: 26px; font-weight: 800; color: #fff; line-height: 1; }
.hp-stat-lbl { font-size: 10px; color: rgba(255,255,255,.65); margin-top: 3px;
               text-transform: uppercase; letter-spacing: .06em; }

.hp-table { width: 100%; border-collapse: collapse; }
.hp-table thead th {
    background: #f4f6f8; color: #6b7a8d;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; padding: 9px 12px;
    border-bottom: 2px solid #e4eaf0; white-space: nowrap;
}
.hp-table tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
.hp-table tbody tr:hover td { background: #f5f9ff; }
.hp-table td { padding: 10px 12px; vertical-align: middle; font-size: 13px; }

.hp-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700; padding: 3px 9px;
    border-radius: 10px; white-space: nowrap;
}
.hp-vigente  { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
.hp-anulado  { background: #fdecea; color: #b91c1c; border: 1px solid #fca5a5; }

.forma-icon { width: 28px; height: 28px; border-radius: 7px;
              display: inline-flex; align-items: center; justify-content: center;
              font-size: 12px; flex-shrink: 0; }
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

{{-- ══ HERO ══ --}}
<div class="hp-hero">
    <div class="hp-stat">
        <div class="hp-stat-num">{{ number_format($resumen['total']) }}</div>
        <div class="hp-stat-lbl">Total pagos</div>
    </div>
    <div class="hp-stat">
        <div class="hp-stat-num">${{ number_format($resumen['total_cobrado'], 0) }}</div>
        <div class="hp-stat-lbl">Total cobrado</div>
    </div>
    <div class="hp-stat">
        <div class="hp-stat-num" style="color:#a8e6cf;">{{ number_format($resumen['vigentes']) }}</div>
        <div class="hp-stat-lbl">Vigentes</div>
    </div>
    <div class="hp-stat">
        <div class="hp-stat-num" style="color:#ffcdd2;">{{ number_format($resumen['anulados']) }}</div>
        <div class="hp-stat-lbl">Anulados</div>
    </div>
    <div style="margin-left:auto; display:flex; gap:8px; align-items:center;">
        <a href="{{ route('pagos.corte') }}"
           class="btn btn-sm btn-flat"
           style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;">
            <i class="fa fa-print"></i> Corte del día
        </a>
    </div>
</div>

{{-- ══ FILTROS ══ --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            box-shadow:0 1px 4px rgba(0,0,0,.04);margin-bottom:20px;">
    <div style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;
                display:flex;align-items:center;gap:8px;">
        <i class="fa fa-filter" style="color:#3c8dbc;"></i>
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;
                     letter-spacing:.07em;color:#6b7a8d;">Filtros</span>
        @if(request()->hasAny(['folio','fecha_desde','fecha_hasta','forma_pago','estado']))
        <a href="{{ route('pagos.index') }}"
           style="margin-left:auto;font-size:11px;color:#b91c1c;text-decoration:none;">
            <i class="fa fa-times"></i> Limpiar
        </a>
        @endif
    </div>
    <form method="GET" action="{{ route('pagos.index') }}" style="padding:14px 16px;">
        <div class="row" style="margin:0 -8px;">
            <div class="col-sm-3" style="padding:0 8px;">
                <label style="font-size:11px;color:#6b7a8d;font-weight:600;margin-bottom:4px;">Folio</label>
                <input type="text" name="folio" value="{{ request('folio') }}"
                       placeholder="REC-2025-…"
                       class="form-control input-sm"
                       style="border-radius:6px;border-color:#dde4eb;">
            </div>
            <div class="col-sm-2" style="padding:0 8px;">
                <label style="font-size:11px;color:#6b7a8d;font-weight:600;margin-bottom:4px;">Fecha desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="form-control input-sm"
                       style="border-radius:6px;border-color:#dde4eb;">
            </div>
            <div class="col-sm-2" style="padding:0 8px;">
                <label style="font-size:11px;color:#6b7a8d;font-weight:600;margin-bottom:4px;">Fecha hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                       class="form-control input-sm"
                       style="border-radius:6px;border-color:#dde4eb;">
            </div>
            <div class="col-sm-2" style="padding:0 8px;">
                <label style="font-size:11px;color:#6b7a8d;font-weight:600;margin-bottom:4px;">Forma de pago</label>
                <select name="forma_pago" class="form-control input-sm"
                        style="border-radius:6px;border-color:#dde4eb;">
                    <option value="">Todas</option>
                    <option value="efectivo"      {{ request('forma_pago') === 'efectivo'      ? 'selected' : '' }}>Efectivo</option>
                    <option value="transferencia" {{ request('forma_pago') === 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                    <option value="tarjeta"       {{ request('forma_pago') === 'tarjeta'       ? 'selected' : '' }}>Tarjeta</option>
                    <option value="cheque"        {{ request('forma_pago') === 'cheque'        ? 'selected' : '' }}>Cheque</option>
                </select>
            </div>
            <div class="col-sm-2" style="padding:0 8px;">
                <label style="font-size:11px;color:#6b7a8d;font-weight:600;margin-bottom:4px;">Estado</label>
                <select name="estado" class="form-control input-sm"
                        style="border-radius:6px;border-color:#dde4eb;">
                    <option value="">Todos</option>
                    <option value="vigente" {{ request('estado') === 'vigente' ? 'selected' : '' }}>Vigente</option>
                    <option value="anulado" {{ request('estado') === 'anulado' ? 'selected' : '' }}>Anulado</option>
                </select>
            </div>
            <div class="col-sm-1" style="padding:0 8px;display:flex;align-items:flex-end;">
                <button type="submit" class="btn btn-primary btn-sm btn-flat" style="width:100%;border-radius:6px;">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- ══ TABLA ══ --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;">

    <div style="padding:12px 16px;background:#f8fafc;border-bottom:1px solid #e8ecf0;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;
                     letter-spacing:.07em;color:#6b7a8d;">
            <i class="fa fa-history" style="color:#27a05a;margin-right:5px;"></i>
            Pagos
            <span style="background:#e8f5ee;color:#27a05a;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;margin-left:4px;">
                {{ $pagos->total() }}
            </span>
        </span>
        <div style="display:flex;align-items:center;gap:8px;">
            <label style="font-size:11px;color:#8a9ab0;margin:0;">Mostrar</label>
            <form method="GET">
                @foreach(request()->except('per_page','page') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <select name="per_page" onchange="this.form.submit()"
                        class="form-control input-sm"
                        style="border-radius:6px;border-color:#dde4eb;display:inline-block;width:auto;">
                    @foreach([10, 25, 30, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div style="overflow-x:auto;">
        <table class="hp-table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Fecha</th>
                    <th>Alumno(s)</th>
                    <th>Cajero</th>
                    <th>Forma de pago</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:center;">Estado</th>
                    <th style="text-align:center; width:80px;"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($pagos as $pago)
            @php
                $alumnos = $pago->detalles
                    ->map(fn($d) => $d->cargo?->inscripcion?->alumno)
                    ->filter()
                    ->unique('id')
                    ->values();
                $formaIconos = [
                    'efectivo'      => ['icon'=>'fa-money',       'bg'=>'#e8f8f0','color'=>'#00875a'],
                    'transferencia' => ['icon'=>'fa-exchange',    'bg'=>'#e8f0fb','color'=>'#3c8dbc'],
                    'tarjeta'       => ['icon'=>'fa-credit-card', 'bg'=>'#f3e8fd','color'=>'#7c3aed'],
                    'cheque'        => ['icon'=>'fa-file-text-o', 'bg'=>'#fff8e1','color'=>'#b45309'],
                ];
                $fi = $formaIconos[$pago->forma_pago] ?? ['icon'=>'fa-question','bg'=>'#f0f3f7','color'=>'#6b7a8d'];
            @endphp
            <tr>
                <td>
                    <code style="font-size:12px;background:#f0f3f7;padding:2px 8px;border-radius:4px;color:#1a2634;font-weight:700;">
                        {{ $pago->folio_recibo }}
                    </code>
                </td>
                <td style="color:#4a5568;white-space:nowrap;">
                    {{ $pago->fecha_pago->format('d/m/Y') }}
                    <div style="font-size:11px;color:#b0bec5;">
                        {{ $pago->fecha_pago->diffForHumans() }}
                    </div>
                </td>
                <td>
                    @if($alumnos->isEmpty())
                        <span style="color:#b0bec5;">—</span>
                    @else
                        <div style="font-weight:600;color:#1a2634;">
                            {{ $alumnos->first()->ap_paterno }} {{ $alumnos->first()->ap_materno }},
                            {{ $alumnos->first()->nombre }}
                        </div>
                        @if($alumnos->count() > 1)
                        <div style="font-size:11px;color:#8a9ab0;">
                            +{{ $alumnos->count() - 1 }} alumno(s) más
                        </div>
                        @endif
                    @endif
                </td>
                <td style="color:#4a5568;">
                    <div>{{ $pago->cajero?->nombre ?? '—' }}</div>
                </td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:7px;">
                        <span class="forma-icon" style="background:{{ $fi['bg'] }};color:{{ $fi['color'] }};">
                            <i class="fa {{ $fi['icon'] }}"></i>
                        </span>
                        <span style="font-size:12px;color:#4a5568;">{{ ucfirst($pago->forma_pago) }}</span>
                    </span>
                    @if($pago->referencia)
                    <div style="font-size:11px;color:#8a9ab0;margin-top:2px;">
                        Ref: {{ $pago->referencia }}
                    </div>
                    @endif
                </td>
                <td style="text-align:right;font-weight:700;color:#1a2634;font-size:14px;">
                    @if($pago->estado === 'anulado')
                        <span style="text-decoration:line-through;color:#b0bec5;">
                            ${{ number_format($pago->monto_total, 2) }}
                        </span>
                    @else
                        ${{ number_format($pago->monto_total, 2) }}
                    @endif
                </td>
                <td style="text-align:center;">
                    <span class="hp-badge {{ $pago->estado === 'vigente' ? 'hp-vigente' : 'hp-anulado' }}">
                        <i class="fa fa-circle" style="font-size:6px;"></i>
                        {{ ucfirst($pago->estado) }}
                    </span>
                </td>
                <td style="text-align:center;">
                    <a href="{{ route('pagos.show', $pago->id) }}"
                       class="btn btn-xs btn-default btn-flat"
                       style="border-radius:5px;" title="Ver detalle">
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:56px 20px;text-align:center;">
                    <i class="fa fa-inbox" style="font-size:42px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
                    <p style="color:#b0bec5;margin:0;font-weight:600;">Sin pagos registrados</p>
                    @if(request()->hasAny(['folio','fecha_desde','fecha_hasta','forma_pago','estado']))
                    <p style="color:#b0bec5;margin:4px 0 0;font-size:12px;">Prueba con otros filtros</p>
                    @endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($pagos->hasPages())
    <div style="padding:12px 16px;border-top:1px solid #f0f3f7;background:#f8fafc;">
        {{ $pagos->links() }}
    </div>
    @endif

</div>

@endsection
