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
    <style>
        /* ── Cabecera ── */
        .hp-hero {
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            border-radius: 12px;
            padding: 18px 16px;
            margin-bottom: 16px;
            color: #fff;
        }
        .hp-hero-nombre {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px;
            line-height: 1.2;
        }
        .hp-hero-sub {
            font-size: 13px;
            color: rgba(255,255,255,.78);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ── Navegación ── */
        .hp-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .hp-nav-btn {
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
        }
        .hp-nav-btn:hover { background: #f0f3f7; color: #1a2634; }
        .hp-nav-btn-primary {
            background: #3c8dbc;
            border-color: #3c8dbc;
            color: #fff !important;
        }
        .hp-nav-btn-primary:hover { background: #1f4e78; border-color: #1f4e78; }

        /* ── Título sección ── */
        .hp-section-title {
            font-size: 16px;
            font-weight: 800;
            color: #1a2634;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Tarjeta de pago ── */
        .hp-pago {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }

        /* Cabecera del pago: fecha + monto */
        .hp-pago-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f0f3f7;
        }
        .hp-pago-fecha {
            font-size: 15px;
            font-weight: 700;
            color: #1a2634;
        }
        .hp-pago-folio {
            font-size: 11px;
            color: #9aa5b4;
            margin-top: 2px;
        }
        .hp-pago-monto {
            font-size: 22px;
            font-weight: 800;
            color: #065f46;
            white-space: nowrap;
        }

        /* Cuerpo: conceptos y forma de pago */
        .hp-pago-body {
            padding: 12px 16px;
        }
        .hp-pago-conceptos {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .hp-pago-forma {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7a8d;
            background: #f0f3f7;
            padding: 4px 10px;
            border-radius: 20px;
        }

        /* Pie: factura */
        .hp-pago-factura {
            border-top: 1px solid #f0f3f7;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .hp-factura-status {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
        }
        .hp-factura-emitida { color: #065f46; }
        .hp-factura-pendiente { color: #9aa5b4; }
        .hp-factura-bloqueada { color: #b0bec5; }

        .hp-factura-acciones {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .hp-btn {
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
        .hp-btn:hover { opacity: .85; }
        .hp-btn-pdf    { background: #fee2e2; color: #b91c1c; }
        .hp-btn-xml    { background: #f0f3f7; color: #4a5568; }
        .hp-btn-cfdi   { background: #3c8dbc; color: #fff; }
        .hp-btn-cfdi:hover { color: #fff; }

        /* ── Aviso datos fiscales ── */
        .hp-aviso-fiscal {
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
        }
        .hp-aviso-fiscal i { font-size: 20px; flex-shrink: 0; margin-top: 1px; }
        .hp-aviso-fiscal a { color: #b45309; font-weight: 700; text-decoration: underline; }

        /* ── Vacío ── */
        .hp-empty {
            text-align: center;
            padding: 48px 20px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            color: #9aa5b4;
        }
        .hp-empty-icon { font-size: 48px; margin-bottom: 12px; color: #d1d9e0; }
        .hp-empty-text { font-size: 15px; }

        /* ── Modal factura ── */
        .hp-modal-label {
            font-size: 14px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            display: block;
        }
        .hp-modal-control {
            font-size: 15px !important;
            height: 46px !important;
            border-radius: 8px !important;
        }
        .hp-modal-hint {
            font-size: 12px;
            color: #9aa5b4;
            margin-top: 4px;
        }

        @media (max-width: 420px) {
            .hp-pago-monto { font-size: 18px; }
            .hp-btn { padding: 8px 10px; font-size: 12px; }
        }
    </style>
@endpush

@section('content')

    {{-- ── Cabecera ── --}}
    <div class="hp-hero">
        <div class="hp-hero-nombre">{{ $alumno->nombre_completo }}</div>
        <div class="hp-hero-sub">
            @if ($pagos->count() > 0)
                <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:12px;">
                    {{ $pagos->count() }} pago(s) registrado(s)
                </span>
            @endif
        </div>
    </div>

    {{-- ── Navegación ── --}}
    <div class="hp-nav">
        <a href="{{ route('portal.hijos') }}" class="hp-nav-btn">
            <i class="fa fa-arrow-left"></i> Regresar
        </a>
        <a href="{{ route('portal.estado-cuenta', $alumno->id) }}" class="hp-nav-btn hp-nav-btn-primary">
            <i class="fa fa-file-text-o"></i> Ver estado de cuenta
        </a>
    </div>

    {{-- ── Aviso si no hay datos fiscales ── --}}
    @if ($razonesSociales->isEmpty())
        <div class="hp-aviso-fiscal">
            <i class="fa fa-exclamation-triangle"></i>
            <div>
                Para solicitar facturas electrónicas, primero registra tus datos fiscales (RFC)
                en <a href="{{ route('portal.razones-sociales') }}">Datos fiscales</a>.
            </div>
        </div>
    @endif

    {{-- ── Título ── --}}
    <div class="hp-section-title">
        <i class="fa fa-history" style="color:#059669;"></i>
        Pagos realizados
    </div>

    {{-- ── Tarjetas de pago ── --}}
    @forelse ($pagos as $pago)
        <div class="hp-pago" id="pago-card-{{ $pago['id'] }}">

            {{-- Fecha y monto --}}
            <div class="hp-pago-header">
                <div>
                    <div class="hp-pago-fecha">
                        <i class="fa fa-calendar-check-o" style="color:#059669;margin-right:5px;"></i>
                        {{ $pago['fecha_pago']?->format('d \d\e F \d\e Y') ?? 'Fecha no registrada' }}
                    </div>
                    @if ($pago['folio_recibo'])
                        <div class="hp-pago-folio">Folio: {{ $pago['folio_recibo'] }}</div>
                    @endif
                </div>
                <div class="hp-pago-monto">${{ number_format($pago['monto_total'], 2) }}</div>
            </div>

            {{-- Conceptos y forma de pago --}}
            <div class="hp-pago-body">
                <div class="hp-pago-conceptos">
                    <i class="fa fa-tag" style="color:#9aa5b4;margin-right:5px;"></i>
                    {{ $pago['conceptos'] ?: 'Sin concepto registrado' }}
                </div>
                <span class="hp-pago-forma">
                    <i class="fa fa-money"></i>
                    {{ ucfirst(str_replace('_', ' ', $pago['forma_pago'] ?? 'No especificada')) }}
                </span>
            </div>

            {{-- Sección de factura --}}
            <div class="hp-pago-factura" id="factura-area-{{ $pago['id'] }}">
                @if ($pago['tiene_factura'])
                    <div class="hp-factura-status hp-factura-emitida">
                        <i class="fa fa-check-circle" style="font-size:18px;"></i>
                        Factura electrónica emitida
                    </div>
                    <div class="hp-factura-acciones">
                        <a href="{{ route('portal.cfdis.descargar', [$pago['cfdi_id'], 'pdf']) }}"
                           class="hp-btn hp-btn-pdf">
                            <i class="fa fa-file-pdf-o"></i> Descargar PDF
                        </a>
                        <a href="{{ route('portal.cfdis.descargar', [$pago['cfdi_id'], 'xml']) }}"
                           class="hp-btn hp-btn-xml">
                            <i class="fa fa-code"></i> XML
                        </a>
                    </div>
                @elseif ($pago['puede_facturar'] && $razonesSociales->isNotEmpty())
                    <div class="hp-factura-status hp-factura-pendiente">
                        <i class="fa fa-file-o" style="font-size:18px;"></i>
                        Sin factura electrónica
                    </div>
                    <button type="button"
                            class="hp-btn hp-btn-cfdi btn-facturar"
                            data-pago-id="{{ $pago['id'] }}">
                        <i class="fa fa-file-text-o"></i> Solicitar factura
                    </button>
                @else
                    <div class="hp-factura-status hp-factura-bloqueada">
                        <i class="fa fa-lock" style="font-size:16px;"></i>
                        @if (!$pago['puede_facturar'])
                            Plazo para facturar vencido
                        @else
                            Registra tus datos fiscales para facturar
                        @endif
                    </div>
                @endif
            </div>

        </div>
    @empty
        <div class="hp-empty">
            <div class="hp-empty-icon"><i class="fa fa-credit-card"></i></div>
            <div class="hp-empty-text">No hay pagos registrados para este alumno.</div>
        </div>
    @endforelse

    {{-- ── Botón regresar final ── --}}
    @if ($pagos->count() > 2)
        <div style="margin: 4px 0 20px;">
            <a href="{{ route('portal.hijos') }}" class="hp-nav-btn">
                <i class="fa fa-arrow-left"></i> Regresar a Mis hijos
            </a>
        </div>
    @endif

    {{-- ══════════════════════════════
         Modal: Solicitar factura
    ══════════════════════════════ --}}
    <div class="modal fade" id="modal-facturar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none;">

                <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #eef2f6;padding:16px 18px;">
                    <button type="button" class="close" data-dismiss="modal" style="font-size:22px;">&times;</button>
                    <h4 class="modal-title" style="font-size:17px;font-weight:800;color:#1a2634;">
                        <i class="fa fa-file-text-o" style="color:#3c8dbc;margin-right:7px;"></i>
                        Solicitar factura electrónica
                    </h4>
                </div>

                <div class="modal-body" style="padding:18px;">
                    <p style="font-size:14px;color:#6b7a8d;margin-bottom:16px;">
                        La factura se enviará al correo registrado y quedará disponible para descarga.
                    </p>
                    <div class="form-group">
                        <label class="hp-modal-label">¿A nombre de quién? <span class="text-danger">*</span></label>
                        <select id="modal-razon-social" class="form-control hp-modal-control">
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
                        <div class="hp-modal-hint">Puedes administrar tus datos fiscales en la sección "Datos fiscales".</div>
                    </div>
                    <div class="form-group">
                        <label class="hp-modal-label">Uso del CFDI <span class="text-danger">*</span></label>
                        <select id="modal-uso-cfdi" class="form-control hp-modal-control">
                            <option value="D10">D10 – Pagos por servicios educativos (colegiaturas)</option>
                            <option value="S01">S01 – Sin efectos fiscales</option>
                            <option value="G03">G03 – Gastos en general</option>
                            <option value="CP01">CP01 – Pagos</option>
                        </select>
                    </div>
                    <div id="modal-facturar-error" class="alert alert-danger" style="display:none;border-radius:8px;font-size:14px;"></div>
                </div>

                <div class="modal-footer" style="padding:14px 18px;background:#f8fafc;border-top:1px solid #eef2f6;gap:10px;display:flex;flex-direction:column;">
                    <button type="button" id="btn-confirmar-factura"
                            class="hp-btn hp-btn-cfdi"
                            style="width:100%;padding:13px;font-size:15px;justify-content:center;border-radius:10px;">
                        <i class="fa fa-send"></i> Emitir factura electrónica
                    </button>
                    <button type="button" class="hp-nav-btn" data-dismiss="modal"
                            style="width:100%;justify-content:center;">
                        Cancelar
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Toast de confirmación --}}
    <div id="toast-factura"
         style="display:none;position:fixed;bottom:24px;right:16px;left:16px;z-index:9999;
                background:#065f46;color:#fff;padding:14px 18px;border-radius:10px;
                font-size:14px;font-weight:600;text-align:center;
                box-shadow:0 4px 20px rgba(0,0,0,.22);max-width:420px;margin:0 auto;">
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var pagoIdActivo = null;
    var urlEmitir    = '{{ url("portal/cfdis/emitir") }}';
    var urlDescargar = '{{ url("portal/cfdis") }}';
    var csrfToken    = '{{ csrf_token() }}';

    // Abrir modal al presionar "Solicitar factura"
    $(document).on('click', '.btn-facturar', function () {
        pagoIdActivo = $(this).data('pago-id');
        $('#modal-facturar-error').hide();

        var uso = $('#modal-razon-social option:selected').data('uso');
        if (uso) $('#modal-uso-cfdi').val(uso);

        $('#modal-facturar').modal('show');
    });

    // Actualizar uso CFDI al cambiar razón social
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
        $('#modal-facturar-error').hide();

        $.ajax({
            url    : urlEmitir + '/' + pagoIdActivo,
            method : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data   : { razon_social_id: rsId || null, uso_cfdi: usoCfdi },
            success: function (resp) {
                $('#modal-facturar').modal('hide');
                mostrarToast(resp.mensaje);
                actualizarAreaFactura(pagoIdActivo, resp.cfdi_id);
            },
            error: function (xhr) {
                var msg = 'Error al emitir el CFDI.';
                try { msg = JSON.parse(xhr.responseText).mensaje || msg; } catch (e) {}
                $('#modal-facturar-error').text(msg).show();
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-send"></i> Emitir factura electrónica');
            },
        });
    });

    function actualizarAreaFactura(pagoId, cfdiId) {
        var pdfUrl = urlDescargar + '/' + cfdiId + '/descargar/pdf';
        var xmlUrl = urlDescargar + '/' + cfdiId + '/descargar/xml';

        $('#factura-area-' + pagoId).html(
            '<div class="hp-factura-status hp-factura-emitida">' +
                '<i class="fa fa-check-circle" style="font-size:18px;"></i> Factura electrónica emitida' +
            '</div>' +
            '<div class="hp-factura-acciones">' +
                '<a href="' + pdfUrl + '" class="hp-btn hp-btn-pdf"><i class="fa fa-file-pdf-o"></i> Descargar PDF</a>' +
                '<a href="' + xmlUrl + '" class="hp-btn hp-btn-xml"><i class="fa fa-code"></i> XML</a>' +
            '</div>'
        );
    }

    function mostrarToast(mensaje) {
        var $t = $('#toast-factura');
        $t.text(mensaje).fadeIn(300);
        setTimeout(function () { $t.fadeOut(400); }, 4500);
    }
})();
</script>
@endpush
