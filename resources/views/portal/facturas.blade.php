@extends('layouts.master')

@section('page_title', 'Facturación')
@section('page_subtitle', 'Mis pagos y facturas')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Facturación</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Hero ── */
        .fac-hero {
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            border-radius: 14px;
            padding: 20px 18px;
            margin-bottom: 16px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .fac-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .fac-hero-titulo { font-size: 20px; font-weight: 800; margin: 0 0 4px; }
        .fac-hero-sub    { font-size: 13px; color: rgba(255,255,255,.8); margin: 0; line-height: 1.4; }

        /* ── Aviso datos fiscales ── */
        .fac-aviso {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            color: #92400e;
            line-height: 1.5;
        }
        .fac-aviso i { font-size: 20px; flex-shrink: 0; margin-top: 1px; }
        .fac-aviso a { color: #b45309; font-weight: 700; text-decoration: underline; }

        /* ── Cabecera de alumno ── */
        .fac-alumno-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px 12px 0 0;
            border-bottom: none;
            margin-top: 18px;
        }
        .fac-alumno-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #d1fae5;
            color: #065f46;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .fac-alumno-nombre {
            font-size: 16px;
            font-weight: 800;
            color: #1a2634;
            margin: 0 0 2px;
        }
        .fac-alumno-grupo {
            font-size: 12px;
            color: #7b8794;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .fac-alumno-count {
            margin-left: auto;
            font-size: 12px;
            font-weight: 700;
            color: #9aa5b4;
            white-space: nowrap;
        }

        /* ── Tarjeta de pago ── */
        .fac-pago {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-top: none;
            margin-bottom: 0;
        }
        .fac-pago:last-child {
            border-radius: 0 0 12px 12px;
            margin-bottom: 0;
        }
        .fac-pago + .fac-pago { border-top: 1px solid #f0f3f7; }

        /* Cabecera del pago */
        .fac-pago-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px 10px;
        }
        .fac-pago-fecha {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
        }
        .fac-pago-folio {
            font-size: 11px;
            color: #9aa5b4;
            margin-top: 2px;
        }
        .fac-pago-monto {
            font-size: 20px;
            font-weight: 800;
            color: #065f46;
            white-space: nowrap;
        }

        /* Conceptos y forma de pago */
        .fac-pago-conceptos {
            padding: 0 16px 10px;
            font-size: 13px;
            color: #4a5568;
            line-height: 1.4;
        }
        .fac-pago-forma {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #6b7a8d;
            background: #f0f3f7;
            padding: 3px 9px;
            border-radius: 20px;
            margin-left: 16px;
            margin-bottom: 10px;
        }

        /* Sección de factura */
        .fac-pago-factura {
            border-top: 1px solid #f0f3f7;
            padding: 11px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .fac-status {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
        }
        .fac-status-ok      { color: #065f46; }
        .fac-status-pending { color: #9aa5b4; }
        .fac-status-locked  { color: #b0bec5; }

        .fac-acciones { display: flex; gap: 8px; flex-wrap: wrap; }
        .fac-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none !important;
            transition: opacity .15s;
        }
        .fac-btn:hover     { opacity: .85; }
        .fac-btn-pdf       { background: #fee2e2; color: #b91c1c; }
        .fac-btn-xml       { background: #f0f3f7; color: #4a5568; }
        .fac-btn-solicitar { background: #059669; color: #fff; }
        .fac-btn-solicitar:hover { color: #fff; }

        /* ── Sin pagos para este hijo ── */
        .fac-sin-pagos {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-top: none;
            border-radius: 0 0 12px 12px;
            text-align: center;
            padding: 24px 16px;
            color: #9aa5b4;
            font-size: 13px;
        }

        /* ── Sin hijos ── */
        .fac-empty {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 14px;
            text-align: center;
            padding: 48px 20px;
            color: #9aa5b4;
        }
        .fac-empty i { font-size: 48px; color: #d1d9e0; display: block; margin-bottom: 14px; }
        .fac-empty p { font-size: 15px; margin: 0; }

        /* ── Nav inferior ── */
        .fac-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none !important;
            border: 1px solid #dde3ea;
            background: #fff;
            color: #4a5568;
            transition: background .15s;
            margin-top: 18px;
            margin-bottom: 20px;
        }
        .fac-nav-btn:hover { background: #f0f3f7; color: #1a2634; }

        /* ── Modal ── */
        .fac-modal-label {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            display: block;
        }
        .fac-modal-control {
            font-size: 15px !important;
            height: 46px !important;
            border-radius: 8px !important;
        }
        .fac-modal-hint { font-size: 12px; color: #9aa5b4; margin-top: 4px; }

        /* ── Toast ── */
        #fac-toast {
            display: none;
            position: fixed;
            bottom: 24px;
            left: 16px;
            right: 16px;
            z-index: 9999;
            background: #065f46;
            color: #fff;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,.22);
            max-width: 440px;
            margin: 0 auto;
        }

        @media (max-width: 420px) {
            .fac-pago-monto { font-size: 17px; }
            .fac-btn { padding: 7px 10px; font-size: 12px; }
        }
    </style>
@endpush

@section('content')

    {{-- ── Hero ── --}}
    <div class="fac-hero">
        <div class="fac-hero-icon"><i class="fa fa-file-text-o"></i></div>
        <div>
            <div class="fac-hero-sub">
                Aquí puedes solicitar facturas electrónicas (CFDI) de tus pagos o descargar las que ya emitiste.
            </div>
        </div>
    </div>

    {{-- ── Aviso si no hay RFC ── --}}
    @if ($razonesSociales->isEmpty())
        <div class="fac-aviso">
            <i class="fa fa-exclamation-triangle"></i>
            <div>
                Para solicitar facturas necesitas registrar tu RFC primero.
                <br>
                <a href="{{ route('portal.razones-sociales') }}">
                    <i class="fa fa-building-o"></i> Ir a Datos fiscales
                </a>
            </div>
        </div>
    @endif

    {{-- ── Sin hijos ── --}}
    @if ($alumnosConPagos->isEmpty())
        <div class="fac-empty">
            <i class="fa fa-users"></i>
            <p>No hay alumnos vinculados a tu cuenta.</p>
        </div>
    @else

        @foreach ($alumnosConPagos as $item)
            @php
                $alumno     = $item['alumno'];
                $pagos      = $item['pagos'];
                $inscripcion = $alumno->inscripciones->first();
                $grupo       = $inscripcion?->grupo;
                $totalPagos  = $pagos->count();
            @endphp

            {{-- ── Cabecera del alumno ── --}}
            <div class="fac-alumno-header">
                <div class="fac-alumno-avatar"><i class="fa fa-user"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="fac-alumno-nombre">{{ $alumno->nombre_completo }}</div>
                    <div class="fac-alumno-grupo">
                        <i class="fa fa-graduation-cap"></i>
                        @if ($grupo)
                            {{ $grupo->grado->nivel->nombre ?? '' }}
                            {{ $grupo->grado->nombre ?? '' }}
                            {{ $grupo->nombre }}
                        @else
                            Sin grupo activo
                        @endif
                    </div>
                </div>
                <div class="fac-alumno-count">
                    {{ $totalPagos }} pago(s)
                </div>
            </div>

            {{-- ── Pagos del alumno ── --}}
            @if ($pagos->isEmpty())
                <div class="fac-sin-pagos">
                    <i class="fa fa-inbox" style="font-size:28px;margin-bottom:8px;display:block;color:#d1d9e0;"></i>
                    Aún no hay pagos registrados para este alumno.
                </div>
            @else
                @foreach ($pagos as $pago)
                    <div class="fac-pago @if ($loop->last) fac-pago-last @endif"
                         style="@if ($loop->last) border-radius:0 0 12px 12px; @endif">

                        {{-- Fecha y monto --}}
                        <div class="fac-pago-header">
                            <div>
                                <div class="fac-pago-fecha">
                                    <i class="fa fa-calendar-check-o" style="color:#059669;margin-right:5px;"></i>
                                    {{ $pago['fecha_pago']?->locale('es')->translatedFormat('d \d\e F \d\e Y') ?? '—' }}
                                </div>
                                @if ($pago['folio_recibo'])
                                    <div class="fac-pago-folio">Folio: {{ $pago['folio_recibo'] }}</div>
                                @endif
                            </div>
                            <div class="fac-pago-monto">${{ number_format($pago['monto_total'], 2) }}</div>
                        </div>

                        {{-- Conceptos --}}
                        <div class="fac-pago-conceptos">
                            <i class="fa fa-tag" style="color:#9aa5b4;margin-right:4px;"></i>
                            {{ $pago['conceptos'] ?: 'Sin concepto registrado' }}
                        </div>
                        <span class="fac-pago-forma">
                            <i class="fa fa-money"></i>
                            {{ ucfirst(str_replace('_', ' ', $pago['forma_pago'] ?? 'No especificada')) }}
                        </span>

                        {{-- Sección factura --}}
                        <div class="fac-pago-factura" id="fac-area-{{ $pago['id'] }}">
                            @if ($pago['tiene_factura'])
                                <div class="fac-status fac-status-ok">
                                    <i class="fa fa-check-circle" style="font-size:18px;"></i>
                                    Factura emitida
                                </div>
                                <div class="fac-acciones">
                                    <a href="{{ route('portal.cfdis.descargar', [$pago['cfdi_id'], 'pdf']) }}"
                                       class="fac-btn fac-btn-pdf">
                                        <i class="fa fa-file-pdf-o"></i> PDF
                                    </a>
                                    <a href="{{ route('portal.cfdis.descargar', [$pago['cfdi_id'], 'xml']) }}"
                                       class="fac-btn fac-btn-xml">
                                        <i class="fa fa-code"></i> XML
                                    </a>
                                </div>
                            @elseif (! $pago['puede_facturar'])
                                <div class="fac-status fac-status-locked">
                                    <i class="fa fa-clock-o"></i>
                                    Plazo para facturar vencido
                                </div>
                            @elseif (! $pago['todos_facturables'])
                                <div class="fac-status fac-status-locked">
                                    <i class="fa fa-ban"></i>
                                    Este pago contiene conceptos no facturables
                                </div>
                            @elseif ($razonesSociales->isNotEmpty())
                                <div class="fac-status fac-status-pending">
                                    <i class="fa fa-file-o" style="font-size:17px;"></i>
                                    Sin factura
                                </div>
                                <button type="button"
                                        class="fac-btn fac-btn-solicitar btn-facturar"
                                        data-pago-id="{{ $pago['id'] }}">
                                    <i class="fa fa-send"></i> Solicitar factura
                                </button>
                            @else
                                <div class="fac-status fac-status-locked">
                                    <i class="fa fa-lock"></i>
                                    Registra tu RFC para facturar
                                </div>
                                <a href="{{ route('portal.razones-sociales') }}" class="fac-btn fac-btn-xml">
                                    <i class="fa fa-building-o"></i> Agregar RFC
                                </a>
                            @endif
                        </div>

                    </div>
                @endforeach
            @endif

        @endforeach

        <a href="{{ route('portal.dashboard') }}" class="fac-nav-btn">
            <i class="fa fa-arrow-left"></i> Regresar al inicio
        </a>

    @endif

    {{-- ══════════════════════════════════════
         Modal: Solicitar factura
    ══════════════════════════════════════ --}}
    <div class="modal fade" id="modal-facturar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none;">

                <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #eef2f6;padding:16px 18px;">
                    <button type="button" class="close" data-dismiss="modal" style="font-size:22px;">&times;</button>
                    <h4 class="modal-title" style="font-size:17px;font-weight:800;color:#1a2634;">
                        <i class="fa fa-file-text-o" style="color:#059669;margin-right:7px;"></i>
                        Solicitar factura electrónica
                    </h4>
                </div>

                <div class="modal-body" style="padding:18px;">
                    <p style="font-size:14px;color:#6b7a8d;margin-bottom:16px;line-height:1.5;">
                        La factura quedará disponible para descarga inmediatamente después de emitirse.
                    </p>

                    <div class="form-group">
                        <label class="fac-modal-label">
                            ¿A nombre de quién? <span class="text-danger">*</span>
                        </label>
                        <select id="modal-razon-social" class="form-control fac-modal-control">
                            <option value="">— Público en General (sin RFC) —</option>
                            @foreach ($razonesSociales as $rs)
                                <option value="{{ $rs->id }}"
                                        data-uso="{{ $rs->uso_cfdi_default }}"
                                        {{ $rs->es_principal ? 'selected' : '' }}>
                                    {{ $rs->rfc }} — {{ $rs->razon_social }}
                                    @if ($rs->es_principal) (principal) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="fac-modal-hint">
                            Administra tus RFC en
                            <a href="{{ route('portal.razones-sociales') }}" target="_blank">Datos fiscales</a>.
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="fac-modal-label">
                            Para qué es la factura <span class="text-danger">*</span>
                        </label>
                        <select id="modal-uso-cfdi" class="form-control fac-modal-control">
                            <option value="D10">D10 – Pagos por servicios educativos</option>
                            <option value="S01">S01 – Sin efectos fiscales</option>
                            <option value="G03">G03 – Gastos en general</option>
                            <option value="CP01">CP01 – Pagos</option>
                        </select>
                    </div>

                    <div id="modal-error"
                         class="alert alert-danger"
                         style="display:none;border-radius:8px;font-size:14px;"></div>
                </div>

                <div class="modal-footer"
                     style="padding:14px 18px;background:#f8fafc;border-top:1px solid #eef2f6;
                            display:flex;flex-direction:column;gap:10px;">
                    <button type="button" id="btn-confirmar-factura"
                            class="fac-btn fac-btn-solicitar"
                            style="width:100%;padding:14px;font-size:15px;justify-content:center;border-radius:10px;">
                        <i class="fa fa-send"></i> Emitir factura electrónica
                    </button>
                    <button type="button" class="fac-nav-btn" data-dismiss="modal"
                            style="width:100%;justify-content:center;margin:0;">
                        Cancelar
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div id="fac-toast"></div>

@endsection

@push('scripts')
<script>
(function () {
    var pagoIdActivo = null;
    var urlEmitir    = '{{ url("portal/cfdis/emitir") }}';
    var urlDescargar = '{{ url("portal/cfdis") }}';
    var csrfToken    = '{{ csrf_token() }}';

    // Abrir modal
    $(document).on('click', '.btn-facturar', function () {
        pagoIdActivo = $(this).data('pago-id');
        $('#modal-error').hide().text('');

        var uso = $('#modal-razon-social option:selected').data('uso');
        if (uso) $('#modal-uso-cfdi').val(uso);

        $('#modal-facturar').modal('show');
    });

    // Sincronizar uso CFDI al cambiar RFC
    $('#modal-razon-social').on('change', function () {
        var uso = $(this).find('option:selected').data('uso');
        if (uso) $('#modal-uso-cfdi').val(uso);
    });

    // Emitir CFDI
    $('#btn-confirmar-factura').on('click', function () {
        if (!pagoIdActivo) return;

        var $btn    = $(this);
        var rsId    = $('#modal-razon-social').val();
        var usoCfdi = $('#modal-uso-cfdi').val();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Emitiendo...');
        $('#modal-error').hide();

        $.ajax({
            url    : urlEmitir + '/' + pagoIdActivo,
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data   : { razon_social_id: rsId || null, uso_cfdi: usoCfdi },
            success: function (resp) {
                $('#modal-facturar').modal('hide');
                actualizarAreaFactura(pagoIdActivo, resp.cfdi_id);
                mostrarToast(resp.mensaje);
            },
            error: function (xhr) {
                var msg = 'Error al emitir la factura.';
                try { msg = JSON.parse(xhr.responseText).mensaje || msg; } catch (e) {}
                $('#modal-error').text(msg).show();
            },
            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="fa fa-send"></i> Emitir factura electrónica');
            },
        });
    });

    function actualizarAreaFactura(pagoId, cfdiId) {
        var pdfUrl = urlDescargar + '/' + cfdiId + '/descargar/pdf';
        var xmlUrl = urlDescargar + '/' + cfdiId + '/descargar/xml';

        $('#fac-area-' + pagoId).html(
            '<div class="fac-status fac-status-ok">'
          + '<i class="fa fa-check-circle" style="font-size:18px;"></i> Factura emitida'
          + '</div>'
          + '<div class="fac-acciones">'
          + '<a href="' + pdfUrl + '" class="fac-btn fac-btn-pdf"><i class="fa fa-file-pdf-o"></i> PDF</a>'
          + '<a href="' + xmlUrl + '" class="fac-btn fac-btn-xml"><i class="fa fa-code"></i> XML</a>'
          + '</div>'
        );
    }

    function mostrarToast(msg) {
        var $t = $('#fac-toast');
        $t.text(msg).fadeIn(300);
        setTimeout(function () { $t.fadeOut(400); }, 4500);
    }
})();
</script>
@endpush
