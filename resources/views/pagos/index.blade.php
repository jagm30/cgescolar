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
        @if(isset($configFiscal) && $configFiscal)
        <button type="button" id="btn-factura-global"
                class="btn btn-sm btn-flat"
                style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.5);border-radius:6px;">
            <i class="fa fa-globe"></i> Factura global
        </button>
        @endif
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
                    <th style="text-align:center; width:120px;"></th>
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
                    @php
                        $cfdiIndividual = $pago->cfdis->where('estado', 'vigente')->first();
                        $cfdiGlobal     = $pago->cfdiGlobal->where('estado', 'vigente')->first();
                    @endphp

                    @if($cfdiIndividual)
                        {{-- Factura individual emitida --}}
                        <a href="{{ route('cfdis.descargar', [$cfdiIndividual->id, 'pdf']) }}"
                           class="btn btn-xs btn-flat"
                           style="background:#fdecea;color:#c0392b;border:1px solid #fca5a5;border-radius:5px;margin-right:2px;"
                           title="Descargar PDF">
                            <i class="fa fa-file-pdf-o"></i>
                        </a>
                        <a href="{{ route('cfdis.descargar', [$cfdiIndividual->id, 'xml']) }}"
                           class="btn btn-xs btn-flat"
                           style="background:#e8f0fb;color:#2980b9;border:1px solid #90c2e7;border-radius:5px;margin-right:2px;"
                           title="Descargar XML">
                            <i class="fa fa-code"></i>
                        </a>
                        <button type="button"
                                class="btn btn-xs btn-flat btn-enviar-correo"
                                style="background:#e8f5ee;color:#00875a;border:1px solid #b3e8d0;border-radius:5px;margin-right:2px;"
                                data-cfdi-id="{{ $cfdiIndividual->id }}"
                                title="Enviar por correo">
                            <i class="fa fa-envelope-o"></i>
                        </button>
                    @elseif($cfdiGlobal)
                        {{-- Incluido en factura global --}}
                        <a href="{{ route('cfdis.descargar', [$cfdiGlobal->id, 'pdf']) }}"
                           class="btn btn-xs btn-flat"
                           style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:5px;margin-right:2px;"
                           title="Descargar PDF factura global {{ $cfdiGlobal->folio }}">
                            <i class="fa fa-file-pdf-o"></i>
                        </a>
                        <a href="{{ route('cfdis.descargar', [$cfdiGlobal->id, 'xml']) }}"
                           class="btn btn-xs btn-flat"
                           style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:5px;margin-right:2px;"
                           title="Descargar XML factura global {{ $cfdiGlobal->folio }}">
                            <i class="fa fa-code"></i>
                        </a>
                        <span class="hp-badge"
                              style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;
                                     font-size:10px;padding:2px 7px;vertical-align:middle;"
                              title="Folio: {{ $cfdiGlobal->folio }}">
                            <i class="fa fa-globe" style="font-size:9px;"></i> Global
                        </span>
                    @elseif($pago->estado === 'vigente' && isset($configFiscal) && $configFiscal)
                        {{-- Sin factura — se puede facturar individualmente --}}
                        <button type="button"
                                class="btn btn-xs btn-flat btn-facturar"
                                style="background:#7b2d8b;color:#fff;border-radius:5px;margin-right:3px;"
                                data-pago-id="{{ $pago->id }}"
                                title="Emitir CFDI">
                            <i class="fa fa-file-text-o"></i>
                        </button>
                    @endif

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

{{-- ══ MODAL FACTURACIÓN ══ --}}
@if(isset($configFiscal) && $configFiscal)
<div class="modal fade" id="modalFacturar" tabindex="-1" role="dialog" aria-labelledby="modalFacturarLabel">
    <div class="modal-dialog" role="document" style="max-width:480px;">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;">

            <div class="modal-header" style="background:linear-gradient(135deg,#6a1a7b 0%,#9d3fb5 100%);border-bottom:none;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" id="modalFacturarLabel" style="color:#fff;font-size:15px;font-weight:700;">
                    <i class="fa fa-file-text-o"></i> Emitir CFDI
                </h4>
            </div>

            <div class="modal-body" id="modalFacturarBody" style="padding:20px;">
                <div id="mf-loading" style="text-align:center;padding:30px 0;color:#b0bec5;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top:10px;font-size:13px;">Cargando…</p>
                </div>

                <div id="mf-content" style="display:none;">
                    <div id="mf-pago-info"
                         style="background:#f8fafc;border:1px solid #e4eaf0;border-radius:8px;
                                padding:12px 14px;margin-bottom:16px;font-size:13px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <code id="mf-folio"
                                  style="font-size:13px;background:#f0f3f7;padding:2px 8px;
                                         border-radius:4px;font-weight:700;color:#1a2634;"></code>
                            <span style="font-size:16px;font-weight:800;color:#1a6b3a;" id="mf-monto"></span>
                        </div>
                        <div id="mf-alumnos" style="margin-top:6px;color:#6b7a8d;font-size:12px;"></div>
                    </div>

                    <form id="form-facturar" method="POST">
                        @csrf

                        <div style="margin-bottom:14px;">
                            <label style="font-size:11px;font-weight:700;color:#4a5568;
                                          display:block;margin-bottom:6px;">
                                RFC del receptor
                            </label>
                            <div id="mf-razones"></div>
                            <label style="display:flex;align-items:center;gap:8px;padding:7px 9px;
                                          border:1px solid #e0e7ef;border-radius:6px;cursor:pointer;
                                          font-weight:400;background:#fafbfc;margin-bottom:0;">
                                <input type="radio" name="razon_social_id" value="" id="mf-publico">
                                <span>
                                    <span style="display:block;font-size:12px;font-weight:700;color:#1a2634;">XAXX010101000</span>
                                    <span style="display:block;font-size:11px;color:#8a9ab0;">Público en general</span>
                                </span>
                            </label>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label style="font-size:11px;font-weight:700;color:#4a5568;
                                          display:block;margin-bottom:4px;">
                                Uso CFDI
                            </label>
                            <select name="uso_cfdi" class="form-control input-sm" style="border-radius:5px;">
                                <option value="D10">D10 — Servicios educativos</option>
                                <option value="S01">S01 — Sin efectos fiscales</option>
                                <option value="G03">G03 — Gastos en general</option>
                                <option value="CN01">CN01 — Nómina</option>
                            </select>
                        </div>

                        <div id="mf-error"
                             style="display:none;background:#fdecea;color:#b91c1c;
                                    padding:9px 12px;border-radius:6px;font-size:12px;
                                    margin-bottom:12px;"></div>

                        <button type="submit" id="mf-submit"
                                class="btn btn-sm btn-flat btn-block"
                                style="background:#7b2d8b;color:#fff;border-radius:6px;font-weight:600;">
                            <i class="fa fa-file-text-o"></i> Emitir CFDI
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══ MODAL ENVIAR CORREO ══ --}}
<div class="modal fade" id="modalEnviarCorreo" tabindex="-1" role="dialog" aria-labelledby="modalEnviarCorreoLabel">
    <div class="modal-dialog" role="document" style="max-width:460px;">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;">

            <div class="modal-header" style="background:linear-gradient(135deg,#0a7d49 0%,#27a05a 100%);border-bottom:none;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" id="modalEnviarCorreoLabel" style="color:#fff;font-size:15px;font-weight:700;">
                    <i class="fa fa-envelope-o"></i> Enviar factura por correo
                </h4>
            </div>

            <div class="modal-body" style="padding:20px;">
                <div id="mec-loading" style="text-align:center;padding:30px 0;color:#b0bec5;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top:10px;font-size:13px;">Cargando contactos…</p>
                </div>

                <div id="mec-content" style="display:none;">
                    <form id="form-enviar-correo" method="POST">
                        @csrf

                        {{-- Contactos familiares --}}
                        <div style="margin-bottom:14px;">
                            <label style="font-size:11px;font-weight:700;color:#4a5568;
                                          display:block;margin-bottom:6px;">
                                Contactos familiares
                            </label>
                            <div id="mec-contactos"></div>
                            <div id="mec-sin-contactos"
                                 style="display:none;font-size:12px;color:#b0bec5;
                                        padding:8px 0;font-style:italic;">
                                <i class="fa fa-info-circle"></i>
                                No hay contactos con correo registrado.
                            </div>
                        </div>

                        {{-- Correo destino (editable) --}}
                        <div style="margin-bottom:16px;">
                            <label for="mec-email-destino"
                                   style="font-size:11px;font-weight:700;color:#4a5568;
                                          display:block;margin-bottom:4px;">
                                Correo destino
                                <span style="font-weight:400;color:#8a9ab0;">
                                    — selecciona un contacto o escribe otro
                                </span>
                            </label>
                            <input type="email" id="mec-email-destino" name="email"
                                   class="form-control input-sm"
                                   placeholder="correo@ejemplo.com"
                                   style="border-radius:5px;"
                                   required>
                        </div>

                        <div id="mec-error"
                             style="display:none;background:#fdecea;color:#b91c1c;
                                    padding:9px 12px;border-radius:6px;font-size:12px;
                                    margin-bottom:12px;"></div>

                        <button type="submit" id="mec-submit"
                                class="btn btn-sm btn-flat btn-block"
                                style="background:#00875a;color:#fff;border-radius:6px;font-weight:600;">
                            <i class="fa fa-paper-plane"></i> Enviar
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var formCorreoBase   = '{{ route('cfdis.form-correo', '__ID__') }}';
    var enviarCorreoBase = '{{ route('cfdis.enviar-correo', '__ID__') }}';

    $(document).on('click', '.btn-enviar-correo', function () {
        var cfdiId = $(this).data('cfdi-id');

        // Reset
        $('#mec-loading').show();
        $('#mec-content').hide();
        $('#mec-error').hide().text('');
        $('#mec-contactos').html('');
        $('#mec-email-destino').val('');
        $('#mec-submit').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Enviar');
        $('#modalEnviarCorreo').modal('show');

        $.getJSON(formCorreoBase.replace('__ID__', cfdiId))
            .done(function (data) {
                // Renderizar contactos
                if (data.contactos && data.contactos.length > 0) {
                    var html = '';
                    $.each(data.contactos, function (i, c) {
                        var checked = c.es_defecto ? 'checked' : '';
                        var borde   = c.es_defecto ? '#b3e8d0' : '#e0e7ef';
                        var fondo   = c.es_defecto ? '#f0faf5' : '#fafbfc';
                        html += '<label style="display:flex;align-items:flex-start;gap:8px;padding:8px 10px;' +
                            'border:1px solid ' + borde + ';background:' + fondo + ';' +
                            'border-radius:6px;margin-bottom:4px;cursor:pointer;font-weight:400;">' +
                            '<input type="radio" class="mec-radio" value="' + $('<span>').text(c.email).html() + '" ' +
                            checked + ' style="margin-top:3px;flex-shrink:0;">' +
                            '<span style="min-width:0;">' +
                            '<span style="display:block;font-size:12px;font-weight:700;color:#1a2634;">' +
                            $('<span>').text(c.nombre).html() + '</span>' +
                            '<span style="display:block;font-size:11px;color:#4a5568;word-break:break-all;">' +
                            $('<span>').text(c.email).html() + '</span>' +
                            (c.es_defecto
                                ? '<span style="font-size:10px;background:#d1fae5;color:#065f46;' +
                                  'padding:1px 7px;border-radius:8px;margin-top:2px;display:inline-block;">' +
                                  'Receptor del CFDI</span>'
                                : '') +
                            '</span></label>';
                    });
                    $('#mec-contactos').html(html).show();
                    $('#mec-sin-contactos').hide();

                    // Precargar correo del radio seleccionado
                    var defecto = $('#mec-contactos .mec-radio:checked').val();
                    if (defecto) $('#mec-email-destino').val(defecto);
                } else {
                    $('#mec-contactos').hide();
                    $('#mec-sin-contactos').show();
                    if (data.email_defecto) $('#mec-email-destino').val(data.email_defecto);
                }

                // Clic en radio → actualiza el campo de correo
                $('#mec-contactos').off('change').on('change', '.mec-radio', function () {
                    $('#mec-email-destino').val($(this).val());
                });

                $('#form-enviar-correo').attr('action', enviarCorreoBase.replace('__ID__', cfdiId));
                $('#mec-loading').hide();
                $('#mec-content').show();
            })
            .fail(function () {
                $('#mec-loading').html(
                    '<div style="color:#b91c1c;font-size:13px;padding:20px 0;text-align:center;">' +
                    '<i class="fa fa-exclamation-circle"></i> No se pudieron cargar los datos.</div>'
                );
            });
    });

    $('#form-enviar-correo').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#mec-submit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Enviando…');
        $('#mec-error').hide().text('');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function (resp) {
            $('#modalEnviarCorreo').modal('hide');
            $('<div class="alert alert-success alert-dismissible" style="border-radius:8px;margin-bottom:16px;">' +
              '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
              '<i class="fa fa-check-circle"></i> ' + (resp.message || 'Factura enviada correctamente.') +
              '</div>').prependTo('.content').hide().slideDown(200);
        })
        .fail(function (xhr) {
            var msg = 'Error al enviar.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (ex) {}
            $('#mec-error').text(msg).show();
            $btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Enviar');
        });
    });
}());
</script>
@endpush

{{-- ══ MODAL FACTURA GLOBAL ══ --}}
<div class="modal fade" id="modalFacturaGlobal" tabindex="-1" role="dialog" aria-labelledby="modalFGLabel">
    <div class="modal-dialog" role="document" style="max-width:500px;">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;">

            <div class="modal-header" style="background:linear-gradient(135deg,#0e5fa3 0%,#2e86de 100%);border-bottom:none;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" id="modalFGLabel" style="color:#fff;font-size:15px;font-weight:700;">
                    <i class="fa fa-globe"></i> Factura global — Público en general
                </h4>
            </div>

            <div class="modal-body" style="padding:20px;">

                {{-- Explicación --}}
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;
                            padding:10px 14px;margin-bottom:18px;font-size:12px;color:#1e40af;">
                    <i class="fa fa-info-circle" style="margin-right:5px;"></i>
                    Agrupa todos los pagos <strong>vigentes sin CFDI</strong> en el período seleccionado
                    y emite un único CFDI a <strong>XAXX010101000 — Público en general</strong>.
                </div>

                <form id="form-factura-global" method="POST" action="{{ route('cfdis.emitir-global') }}">
                    @csrf

                    {{-- Rango de fechas --}}
                    <div class="row" style="margin:0 -6px 14px;">
                        <div class="col-sm-6" style="padding:0 6px;">
                            <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">
                                Fecha desde
                            </label>
                            <input type="date" name="fecha_desde" id="fg-fecha-desde"
                                   class="form-control input-sm"
                                   style="border-radius:6px;border-color:#dde4eb;"
                                   required>
                        </div>
                        <div class="col-sm-6" style="padding:0 6px;">
                            <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">
                                Fecha hasta
                            </label>
                            <input type="date" name="fecha_hasta" id="fg-fecha-hasta"
                                   class="form-control input-sm"
                                   style="border-radius:6px;border-color:#dde4eb;"
                                   required>
                        </div>
                    </div>

                    {{-- Periodicidad --}}
                    <div style="margin-bottom:16px;">
                        <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">
                            Periodicidad
                        </label>
                        <select name="periodicidad" id="fg-periodicidad"
                                class="form-control input-sm"
                                style="border-radius:6px;border-color:#dde4eb;">
                            <option value="04" selected>04 — Mensual</option>
                            <option value="02">02 — Semanal</option>
                            <option value="01">01 — Diaria</option>
                            <option value="03">03 — Decena</option>
                        </select>
                    </div>

                    {{-- Botón previsualizar --}}
                    <button type="button" id="fg-btn-preview"
                            class="btn btn-sm btn-default btn-flat btn-block"
                            style="border-radius:6px;margin-bottom:16px;border-color:#dde4eb;">
                        <i class="fa fa-search"></i> Previsualizar
                    </button>

                    {{-- Panel de previsualización --}}
                    <div id="fg-preview-panel" style="display:none;margin-bottom:16px;">
                        <div id="fg-preview-cargando"
                             style="text-align:center;padding:16px 0;color:#b0bec5;">
                            <i class="fa fa-spinner fa-spin"></i> Consultando…
                        </div>
                        <div id="fg-preview-resultado" style="display:none;">
                            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 14px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                    <span style="font-size:12px;color:#166534;font-weight:700;">
                                        <i class="fa fa-check-circle"></i> Pagos sin factura encontrados
                                    </span>
                                    <span id="fg-count"
                                          style="background:#dcfce7;color:#166534;font-size:13px;
                                                 font-weight:800;padding:2px 10px;border-radius:10px;"></span>
                                </div>
                                <div style="display:flex;justify-content:space-between;
                                            border-top:1px solid #bbf7d0;padding-top:8px;margin-top:4px;">
                                    <span style="font-size:12px;color:#4a5568;">Total a facturar</span>
                                    <span id="fg-monto"
                                          style="font-size:15px;font-weight:800;color:#1a6b3a;"></span>
                                </div>
                                <div id="fg-conceptos" style="margin-top:10px;border-top:1px solid #bbf7d0;padding-top:8px;"></div>
                            </div>
                        </div>
                        <div id="fg-preview-vacio" style="display:none;">
                            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;
                                        padding:12px 14px;font-size:12px;color:#92400e;text-align:center;">
                                <i class="fa fa-exclamation-triangle"></i>
                                No hay pagos sin factura en el período seleccionado.
                            </div>
                        </div>
                    </div>

                    {{-- Error --}}
                    <div id="fg-error"
                         style="display:none;background:#fdecea;color:#b91c1c;
                                padding:9px 12px;border-radius:6px;font-size:12px;margin-bottom:12px;"></div>

                    {{-- Botón emitir (oculto hasta previsualizar) --}}
                    <button type="submit" id="fg-submit"
                            class="btn btn-sm btn-flat btn-block"
                            style="display:none;background:#0e5fa3;color:#fff;border-radius:6px;font-weight:600;">
                        <i class="fa fa-globe"></i> Emitir factura global
                    </button>

                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var previewUrl    = '{{ route('cfdis.preview-global') }}';
    var emitirGlobalUrl = '{{ route('cfdis.emitir-global') }}';

    // Defaults: primer y último día del mes actual
    var hoy    = new Date();
    var primerDia = hoy.getFullYear() + '-' +
                    String(hoy.getMonth() + 1).padStart(2, '0') + '-01';
    var ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    var ultimoDiaStr = ultimoDia.getFullYear() + '-' +
                       String(ultimoDia.getMonth() + 1).padStart(2, '0') + '-' +
                       String(ultimoDia.getDate()).padStart(2, '0');

    $('#btn-factura-global').on('click', function () {
        // Resetear modal
        $('#fg-fecha-desde').val(primerDia);
        $('#fg-fecha-hasta').val(ultimoDiaStr);
        $('#fg-preview-panel').hide();
        $('#fg-preview-resultado').hide();
        $('#fg-preview-vacio').hide();
        $('#fg-preview-cargando').hide();
        $('#fg-submit').hide();
        $('#fg-error').hide().text('');
        $('#fg-btn-preview').prop('disabled', false).html('<i class="fa fa-search"></i> Previsualizar');
        $('#modalFacturaGlobal').modal('show');
    });

    // Resetear submit si cambian las fechas después de previsualizar
    $('#fg-fecha-desde, #fg-fecha-hasta, #fg-periodicidad').on('change', function () {
        $('#fg-preview-panel').hide();
        $('#fg-submit').hide();
        $('#fg-error').hide().text('');
    });

    $('#fg-btn-preview').on('click', function () {
        var desde  = $('#fg-fecha-desde').val();
        var hasta  = $('#fg-fecha-hasta').val();

        if (!desde || !hasta) {
            $('#fg-error').text('Selecciona un rango de fechas.').show();
            return;
        }
        if (desde > hasta) {
            $('#fg-error').text('La fecha desde no puede ser mayor que la fecha hasta.').show();
            return;
        }

        $('#fg-error').hide().text('');
        $('#fg-preview-panel').show();
        $('#fg-preview-cargando').show();
        $('#fg-preview-resultado').hide();
        $('#fg-preview-vacio').hide();
        $('#fg-submit').hide();

        $.getJSON(previewUrl, { fecha_desde: desde, fecha_hasta: hasta })
            .done(function (data) {
                $('#fg-preview-cargando').hide();

                if (data.pagos_count === 0) {
                    $('#fg-preview-vacio').show();
                    return;
                }

                $('#fg-count').text(data.pagos_count + ' pago(s)');
                $('#fg-monto').text('$' + Number(data.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                // Desglose por concepto
                var html = '';
                if (data.resumen_conceptos && data.resumen_conceptos.length > 0) {
                    html += '<div style="font-size:11px;color:#4a5568;font-weight:700;margin-bottom:4px;">Desglose por concepto</div>';
                    $.each(data.resumen_conceptos, function (i, c) {
                        html += '<div style="display:flex;justify-content:space-between;' +
                            'font-size:12px;color:#4a5568;padding:2px 0;">' +
                            '<span>' + $('<div>').text(c.nombre).html() + '</span>' +
                            '<span style="font-weight:600;">$' +
                            Number(c.monto).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}) +
                            '</span></div>';
                    });
                }
                $('#fg-conceptos').html(html);

                $('#fg-preview-resultado').show();
                $('#fg-submit').show();
            })
            .fail(function (xhr) {
                $('#fg-preview-cargando').hide();
                var msg = 'Error al consultar.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    msg = resp.message || resp.errors && Object.values(resp.errors)[0][0] || msg;
                } catch (ex) {}
                $('#fg-error').text(msg).show();
            });
    });

    $('#form-factura-global').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#fg-submit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo…');
        $('#fg-error').hide().text('');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function (resp) {
            $('#modalFacturaGlobal').modal('hide');
            $('<div class="alert alert-success alert-dismissible" style="border-radius:8px;margin-bottom:16px;">' +
              '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
              '<i class="fa fa-check-circle"></i> ' + (resp.message || 'Factura global emitida correctamente.') +
              '</div>').prependTo('.content').hide().slideDown(200);
            setTimeout(function () { location.reload(); }, 2500);
        })
        .fail(function (xhr) {
            var msg = 'Error al emitir la factura global.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (ex) {}
            $('#fg-error').text(msg).show();
            $btn.prop('disabled', false).html('<i class="fa fa-globe"></i> Emitir factura global');
        });
    });
}());
</script>
@endpush

@push('scripts')
<script>
(function () {
    var emitirBase     = '{{ route('cfdis.emitir', '__ID__') }}';
    var formFacturaBase = '{{ route('pagos.form-factura', '__ID__') }}';

    $(document).on('click', '.btn-facturar', function () {
        var pagoId = $(this).data('pago-id');

        $('#mf-loading').show();
        $('#mf-content').hide();
        $('#mf-error').hide().text('');
        $('#mf-submit').prop('disabled', false).html('<i class="fa fa-file-text-o"></i> Emitir CFDI');
        $('#modalFacturar').modal('show');

        $.getJSON(formFacturaBase.replace('__ID__', pagoId))
            .done(function (data) {
                $('#mf-folio').text(data.folio);
                $('#mf-monto').text('$' + data.monto);
                $('#mf-alumnos').text(data.alumnos.join(' · '));

                var html = '';
                $.each(data.razones, function (i, rs) {
                    var checked = (i === 0) ? 'checked' : '';
                    html += '<label style="display:flex;align-items:flex-start;gap:8px;padding:7px 9px;' +
                        'border:1px solid #e0e7ef;border-radius:6px;margin-bottom:4px;' +
                        'cursor:pointer;font-weight:400;background:#fafbfc;">' +
                        '<input type="radio" name="razon_social_id" value="' + rs.id + '" ' + checked + ' style="margin-top:3px;flex-shrink:0;">' +
                        '<span>' +
                        '<span style="display:block;font-size:12px;font-weight:700;color:#1a2634;">' + $('<div>').text(rs.rfc).html() + '</span>' +
                        '<span style="display:block;font-size:11px;color:#4a5568;">' + $('<div>').text(rs.razon_social).html() + '</span>' +
                        (rs.contacto ? '<span style="display:block;font-size:10px;color:#b0bec5;">' + $('<div>').text(rs.contacto).html() + '</span>' : '') +
                        '</span></label>';
                });
                $('#mf-razones').html(html);

                if (data.razones.length === 0) {
                    $('#mf-publico').prop('checked', true);
                }

                $('#form-facturar').attr('action', emitirBase.replace('__ID__', pagoId));
                $('#mf-loading').hide();
                $('#mf-content').show();
            })
            .fail(function () {
                $('#mf-loading').html(
                    '<div style="color:#b91c1c;font-size:13px;padding:20px 0;">' +
                    '<i class="fa fa-exclamation-circle"></i> No se pudieron cargar los datos.</div>'
                );
            });
    });

    $('#form-facturar').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#mf-submit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo…');
        $('#mf-error').hide().text('');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function (resp) {
            $('#modalFacturar').modal('hide');
            $('<div class="alert alert-success alert-dismissible" style="border-radius:8px;margin-bottom:16px;">' +
              '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
              '<i class="fa fa-check-circle"></i> ' + (resp.message || 'CFDI emitido correctamente.') +
              '</div>').prependTo('.content').hide().slideDown(200);
            setTimeout(function () { location.reload(); }, 2000);
        })
        .fail(function (xhr) {
            var msg = 'Error al emitir CFDI.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (ex) {}
            $('#mf-error').text(msg).show();
            $btn.prop('disabled', false).html('<i class="fa fa-file-text-o"></i> Emitir CFDI');
        });
    });
}());
</script>
@endpush
@endif

@endsection
