@extends('layouts.master')

@section('page_title', 'Facturas electrónicas')
@section('page_subtitle', 'Consulta de CFDIs emitidos')

@section('breadcrumb')
    <li class="active">Facturas</li>
@endsection

@push('styles')
<style>
.fac-table { width: 100%; border-collapse: collapse; }
.fac-table thead th {
    background: #f4f6f8; color: #6b7a8d;
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; padding: 9px 12px;
    border-bottom: 2px solid #e4eaf0; white-space: nowrap;
}
.fac-table tbody tr { border-bottom: 1px solid #f0f3f7; transition: background .1s; }
.fac-table tbody tr:hover td { background: #f5f9ff; }
.fac-table td { padding: 10px 12px; vertical-align: middle; font-size: 13px; }

.fac-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; padding: 2px 8px;
    border-radius: 10px; white-space: nowrap;
}
.badge-individual { background: #f3e8fd; color: #7b2d8b; border: 1px solid #d8b4fe; }
.badge-global     { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.badge-vigente    { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
.badge-cancelado  { background: #fdecea; color: #b91c1c; border: 1px solid #fca5a5; }
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

{{-- ══ ENCABEZADO + STATS ══ --}}
<div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
            box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-file-text-o text-blue"></i> Facturas electrónicas
        </h4>
        <div style="display:flex;gap:7px;flex-wrap:wrap;">
            <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-list"></i> {{ number_format($resumen['total']) }} CFDIs
            </span>
            <span style="background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-check-circle"></i> {{ number_format($resumen['vigentes']) }} vigentes
            </span>
            @if($resumen['cancelados'] > 0)
            <span style="background:#fdecea;color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-ban"></i> {{ number_format($resumen['cancelados']) }} cancelados
            </span>
            @endif
            <span style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-globe"></i> {{ number_format($resumen['globales']) }} globales
            </span>
        </div>
    </div>
    @if(isset($configFiscal) && $configFiscal)
    <a href="{{ route('pagos.index') }}" class="btn btn-default btn-sm btn-flat"
       style="border-radius:20px;flex-shrink:0;">
        <i class="fa fa-arrow-left"></i> Historial de pagos
    </a>
    @endif
</div>

{{-- ══ PANEL PRINCIPAL ══ --}}
<div style="border:1px solid #e4eaf0;border-radius:10px;background:#fff;
            box-shadow:0 1px 4px rgba(0,0,0,.04);overflow:hidden;">

    {{-- Toolbar + filtros --}}
    <form method="GET" action="{{ route('facturas.index') }}"
          style="display:flex;align-items:center;gap:8px;padding:10px 14px;
                 background:#f9fafb;border-bottom:1px solid #e8ecf0;flex-wrap:wrap;">

        <input type="text" name="folio" value="{{ request('folio') }}"
               placeholder="Folio fiscal…"
               class="form-control input-sm"
               style="border-radius:20px;border-color:#dde4eb;max-width:150px;height:32px;">

        <select name="tipo" class="form-control input-sm"
                style="border-radius:6px;border-color:#dde4eb;height:32px;max-width:120px;">
            <option value="">Todos los tipos</option>
            <option value="individual" {{ request('tipo') === 'individual' ? 'selected' : '' }}>Individual</option>
            <option value="global"     {{ request('tipo') === 'global'     ? 'selected' : '' }}>Global</option>
        </select>

        <select name="estado" class="form-control input-sm"
                style="border-radius:6px;border-color:#dde4eb;height:32px;max-width:110px;">
            <option value="">Todos</option>
            <option value="vigente"   {{ request('estado') === 'vigente'   ? 'selected' : '' }}>Vigente</option>
            <option value="cancelado" {{ request('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
        </select>

        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
               class="form-control input-sm"
               style="border-radius:6px;border-color:#dde4eb;max-width:130px;height:32px;"
               title="Fecha desde">

        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
               class="form-control input-sm"
               style="border-radius:6px;border-color:#dde4eb;max-width:130px;height:32px;"
               title="Fecha hasta">

        <select name="per_page" onchange="this.form.submit()"
                class="form-control input-sm"
                style="border-radius:6px;border-color:#dde4eb;height:32px;max-width:90px;">
            @foreach([10, 25, 50] as $n)
                <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }} / pág.</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary btn-sm btn-flat"
                style="border-radius:20px;padding:4px 14px;height:32px;">
            <i class="fa fa-search"></i>
        </button>

        @if(request()->hasAny(['folio','tipo','estado','fecha_desde','fecha_hasta']))
        <a href="{{ route('facturas.index') }}" class="btn btn-default btn-sm btn-flat"
           style="border-radius:20px;padding:4px 10px;height:32px;" title="Quitar filtros">
            <i class="fa fa-times"></i>
        </a>
        @endif

        <span style="background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:600;
                     padding:3px 12px;border-radius:12px;white-space:nowrap;margin-left:auto;">
            <i class="fa fa-file-text-o"></i> {{ $cfdis->total() }} resultado(s)
        </span>
    </form>

    <div style="overflow-x:auto;">
        <table class="fac-table">
            <thead>
                <tr>
                    <th>Folio fiscal</th>
                    <th style="text-align:center;">Tipo</th>
                    <th>Receptor</th>
                    <th>Referencia</th>
                    <th>Fecha timbrado</th>
                    <th style="text-align:right;">Monto</th>
                    <th style="text-align:center;">Estado</th>
                    <th style="text-align:center;width:110px;"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($cfdis as $cfdi)
            @php
                $esGlobal = $cfdi->tipo === 'global';
                $monto    = $esGlobal
                    ? (float) ($cfdi->pagos_sum_monto_total ?? 0)
                    : (float) ($cfdi->pago?->monto_total ?? 0);
            @endphp
            <tr>
                {{-- Folio fiscal --}}
                <td>
                    <code style="font-size:12px;background:#f0f3f7;padding:2px 8px;
                                 border-radius:4px;color:#1a2634;font-weight:700;">
                        {{ $cfdi->folio ?? '—' }}
                    </code>
                    @if($cfdi->uuid_sat)
                    <div style="font-size:10px;color:#b0bec5;margin-top:2px;font-family:monospace;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                         title="{{ $cfdi->uuid_sat }}">
                        {{ $cfdi->uuid_sat }}
                    </div>
                    @endif
                </td>

                {{-- Tipo --}}
                <td style="text-align:center;">
                    <span class="fac-badge {{ $esGlobal ? 'badge-global' : 'badge-individual' }}">
                        <i class="fa {{ $esGlobal ? 'fa-globe' : 'fa-user' }}" style="font-size:9px;"></i>
                        {{ $esGlobal ? 'Global' : 'Individual' }}
                    </span>
                </td>

                {{-- Receptor --}}
                <td>
                    @if($cfdi->razonSocial)
                        <div style="font-weight:700;color:#1a2634;font-size:12px;">
                            {{ $cfdi->razonSocial->rfc }}
                        </div>
                        <div style="font-size:11px;color:#6b7a8d;">
                            {{ $cfdi->razonSocial->razon_social }}
                        </div>
                    @else
                        <div style="font-weight:700;color:#1a2634;font-size:12px;">XAXX010101000</div>
                        <div style="font-size:11px;color:#6b7a8d;">Público en general</div>
                    @endif
                </td>

                {{-- Referencia --}}
                <td style="color:#4a5568;font-size:12px;">
                    @if($esGlobal)
                        @if($cfdi->fecha_desde && $cfdi->fecha_hasta)
                        <div>
                            <i class="fa fa-calendar-o" style="color:#2e86de;font-size:10px;"></i>
                            {{ $cfdi->fecha_desde->format('d/m/Y') }} — {{ $cfdi->fecha_hasta->format('d/m/Y') }}
                        </div>
                        @endif
                        <div style="font-size:11px;color:#8a9ab0;margin-top:2px;">
                            {{ $cfdi->pagos_count }} pago(s) agrupado(s)
                        </div>
                    @else
                        @if($cfdi->pago)
                        <a href="{{ route('pagos.show', $cfdi->pago->id) }}"
                           style="color:#3c8dbc;text-decoration:none;font-weight:600;">
                            {{ $cfdi->pago->folio_recibo }}
                        </a>
                        <div style="font-size:11px;color:#8a9ab0;margin-top:2px;">
                            {{ $cfdi->pago->fecha_pago->format('d/m/Y') }}
                        </div>
                        @else
                            <span style="color:#b0bec5;">—</span>
                        @endif
                    @endif
                </td>

                {{-- Fecha timbrado --}}
                <td style="color:#4a5568;white-space:nowrap;">
                    @if($cfdi->fecha_timbrado)
                        {{ $cfdi->fecha_timbrado->format('d/m/Y') }}
                        <div style="font-size:11px;color:#b0bec5;">
                            {{ $cfdi->fecha_timbrado->format('H:i') }}
                        </div>
                    @else
                        <span style="color:#b0bec5;">—</span>
                    @endif
                </td>

                {{-- Monto --}}
                <td style="text-align:right;font-weight:700;color:#1a2634;font-size:14px;">
                    @if($cfdi->estado === 'cancelado')
                        <span style="text-decoration:line-through;color:#b0bec5;">
                            ${{ number_format($monto, 2) }}
                        </span>
                    @else
                        ${{ number_format($monto, 2) }}
                    @endif
                </td>

                {{-- Estado --}}
                <td style="text-align:center;">
                    <span class="fac-badge {{ $cfdi->estado === 'vigente' ? 'badge-vigente' : 'badge-cancelado' }}">
                        <i class="fa fa-circle" style="font-size:6px;"></i>
                        {{ ucfirst($cfdi->estado) }}
                    </span>
                </td>

                {{-- Acciones --}}
                <td style="text-align:center;">
                    @if($cfdi->factura_uid)
                    <a href="{{ route('cfdis.descargar', [$cfdi->id, 'pdf']) }}"
                       class="btn btn-xs btn-flat"
                       style="background:#fdecea;color:#c0392b;border:1px solid #fca5a5;border-radius:5px;margin-right:2px;"
                       title="Descargar PDF">
                        <i class="fa fa-file-pdf-o"></i>
                    </a>
                    <a href="{{ route('cfdis.descargar', [$cfdi->id, 'xml']) }}"
                       class="btn btn-xs btn-flat"
                       style="background:#e8f0fb;color:#2980b9;border:1px solid #90c2e7;border-radius:5px;margin-right:2px;"
                       title="Descargar XML">
                        <i class="fa fa-code"></i>
                    </a>
                    @endif

                    @if($cfdi->estado === 'vigente' && auth()->user()->esAdministrador())
                    <button type="button"
                            class="btn btn-xs btn-flat btn-cancelar-cfdi"
                            style="background:#fff8e1;color:#92400e;border:1px solid #fde68a;border-radius:5px;"
                            data-cfdi-id="{{ $cfdi->id }}"
                            data-folio="{{ $cfdi->folio }}"
                            title="Cancelar CFDI">
                        <i class="fa fa-ban"></i>
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="padding:56px 20px;text-align:center;">
                    <i class="fa fa-file-text-o" style="font-size:42px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
                    <p style="color:#b0bec5;margin:0;font-weight:600;">Sin facturas registradas</p>
                    @if(request()->hasAny(['folio','tipo','estado','fecha_desde','fecha_hasta']))
                    <p style="color:#b0bec5;margin:4px 0 0;font-size:12px;">Prueba con otros filtros</p>
                    @endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($cfdis->hasPages())
    <div style="padding:12px 16px;border-top:1px solid #f0f3f7;background:#f8fafc;">
        {{ $cfdis->links() }}
    </div>
    @endif

</div>

{{-- ══ MODAL CANCELAR CFDI ══ --}}
@if(auth()->user()->esAdministrador())
<div class="modal fade" id="modalCancelarCfdi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="max-width:400px;">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#b91c1c 0%,#ef4444 100%);border-bottom:none;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;"><span>&times;</span></button>
                <h4 class="modal-title" style="color:#fff;font-size:15px;font-weight:700;">
                    <i class="fa fa-ban"></i> Cancelar CFDI
                </h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <p style="font-size:13px;color:#4a5568;margin-bottom:14px;">
                    Folio: <strong id="mc-folio"></strong>
                </p>
                <form id="form-cancelar-cfdi" method="POST">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:4px;">
                            Motivo SAT
                        </label>
                        <select name="motivo" class="form-control input-sm" style="border-radius:5px;">
                            <option value="02">02 — Comprobante emitido con errores sin relación</option>
                            <option value="01">01 — Comprobante emitido con errores con relación</option>
                            <option value="03">03 — No se llevó a cabo la operación</option>
                            <option value="04">04 — Operación nominativa relacionada en factura global</option>
                        </select>
                    </div>
                    <div id="mc-error"
                         style="display:none;background:#fdecea;color:#b91c1c;
                                padding:9px 12px;border-radius:6px;font-size:12px;margin-bottom:12px;"></div>
                    <button type="submit" id="mc-submit"
                            class="btn btn-danger btn-sm btn-flat btn-block" style="border-radius:6px;">
                        <i class="fa fa-ban"></i> Confirmar cancelación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var cancelarBase = '{{ route('cfdis.cancelar', '__ID__') }}';

    $(document).on('click', '.btn-cancelar-cfdi', function () {
        var cfdiId = $(this).data('cfdi-id');
        var folio  = $(this).data('folio');
        $('#mc-folio').text(folio || 'CFDI #' + cfdiId);
        $('#mc-error').hide().text('');
        $('#mc-submit').prop('disabled', false).html('<i class="fa fa-ban"></i> Confirmar cancelación');
        $('#form-cancelar-cfdi').attr('action', cancelarBase.replace('__ID__', cfdiId));
        $('#modalCancelarCfdi').modal('show');
    });

    $('#form-cancelar-cfdi').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#mc-submit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cancelando…');
        $('#mc-error').hide().text('');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .done(function (resp) {
            $('#modalCancelarCfdi').modal('hide');
            $('<div class="alert alert-success alert-dismissible" style="border-radius:8px;margin-bottom:16px;">' +
              '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
              '<i class="fa fa-check-circle"></i> ' + (resp.message || 'CFDI cancelado correctamente.') +
              '</div>').prependTo('.content').hide().slideDown(200);
            setTimeout(function () { location.reload(); }, 2000);
        })
        .fail(function (xhr) {
            var msg = 'Error al cancelar.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (ex) {}
            $('#mc-error').text(msg).show();
            $btn.prop('disabled', false).html('<i class="fa fa-ban"></i> Confirmar cancelación');
        });
    });
}());
</script>
@endpush
@endif

@endsection
