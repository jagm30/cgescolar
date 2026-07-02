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
                        <tr id="fila-pago-{{ $pago['id'] }}">
                            <td><code>{{ $pago['folio_recibo'] ?: 'N/A' }}</code></td>
                            <td>{{ $pago['fecha_pago']?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td>{{ $pago['conceptos'] ?: 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $pago['forma_pago'] ?? 'N/A')) }}</td>
                            <td class="text-right">${{ number_format($pago['monto_total'], 2) }}</td>
                            <td class="text-center" id="celda-cfdi-{{ $pago['id'] }}">
                                @if ($pago['tiene_factura'])
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                        <span class="portal-pill portal-pill-ok" style="font-size:11px;">
                                            <i class="fa fa-check"></i> Emitida
                                        </span>
                                        <div style="display:flex;gap:4px;">
                                            <a href="{{ route('portal.cfdis.descargar', [$pago['cfdi_id'], 'pdf']) }}"
                                               class="btn btn-xs btn-danger btn-flat"
                                               title="Descargar PDF">
                                                <i class="fa fa-file-pdf-o"></i> PDF
                                            </a>
                                            <a href="{{ route('portal.cfdis.descargar', [$pago['cfdi_id'], 'xml']) }}"
                                               class="btn btn-xs btn-default btn-flat"
                                               title="Descargar XML">
                                                <i class="fa fa-code"></i> XML
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                        <span class="portal-pill portal-pill-warn" style="font-size:11px;">Sin factura</span>
                                        @if ($pago['puede_facturar'] && $razonesSociales->isNotEmpty())
                                            <button type="button"
                                                    class="btn btn-xs btn-primary btn-flat btn-facturar"
                                                    data-pago-id="{{ $pago['id'] }}"
                                                    title="Solicitar factura electrónica">
                                                <i class="fa fa-file-text-o"></i> Facturar
                                            </button>
                                        @else
                                            <span style="font-size:10px;color:#999;" title="Solo se puede facturar dentro del mismo mes y en un plazo máximo de 72 horas.">
                                                <i class="fa fa-lock"></i> Plazo vencido
                                            </span>
                                        @endif
                                    </div>
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

    @if ($razonesSociales->isEmpty())
        <div class="portal-card" style="padding:16px;">
            <div class="portal-empty">
                <i class="fa fa-building-o" style="font-size:32px;margin-bottom:8px;"></i>
                <div>Para solicitar facturas, primero registra tus datos fiscales en
                    <a href="{{ route('portal.razones-sociales') }}">Datos fiscales</a>.
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Solicitar factura electrónica --}}
    <div class="modal fade" id="modal-facturar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-file-text-o"></i> Solicitar factura electrónica</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>RFC / Razón social <span class="text-danger">*</span></label>
                        <select id="modal-razon-social" class="form-control">
                            <option value="">— Sin datos fiscales (Público en General) —</option>
                            @foreach ($razonesSociales as $rs)
                                <option value="{{ $rs->id }}"
                                        data-uso="{{ $rs->uso_cfdi_default }}"
                                        {{ $rs->es_principal ? 'selected' : '' }}>
                                    {{ $rs->rfc }} — {{ $rs->razon_social }}
                                    @if ($rs->es_principal) (principal) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Uso del CFDI <span class="text-danger">*</span></label>
                        <select id="modal-uso-cfdi" class="form-control">
                            <option value="D10">D10 – Pagos por servicios educativos (colegiaturas)</option>
                            <option value="S01">S01 – Sin efectos fiscales</option>
                            <option value="G03">G03 – Gastos en general</option>
                            <option value="CP01">CP01 – Pagos</option>
                        </select>
                    </div>
                    <div id="modal-facturar-error" class="alert alert-danger" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn-confirmar-factura" class="btn btn-primary btn-flat">
                        <i class="fa fa-send"></i> Emitir CFDI
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-factura" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;
        background:#1a7f4b;color:#fff;padding:12px 20px;border-radius:6px;
        font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.25);max-width:360px;">
    </div>
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
        $('#modal-facturar-error').hide();

        // Pre-seleccionar uso CFDI según la razón social principal
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
        if (! pagoIdActivo) return;

        var $btn      = $(this);
        var rsId      = $('#modal-razon-social').val();
        var usoCfdi   = $('#modal-uso-cfdi').val();

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
                actualizarCeldaCfdi(pagoIdActivo, resp.cfdi_id);
            },
            error: function (xhr) {
                var msg = 'Error al emitir el CFDI.';
                try { msg = JSON.parse(xhr.responseText).mensaje || msg; } catch (e) {}
                $('#modal-facturar-error').text(msg).show();
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fa fa-send"></i> Emitir CFDI');
            },
        });
    });

    function actualizarCeldaCfdi(pagoId, cfdiId) {
        var pdfUrl = urlDescargar + '/' + cfdiId + '/descargar/pdf';
        var xmlUrl = urlDescargar + '/' + cfdiId + '/descargar/xml';

        $('#celda-cfdi-' + pagoId).html(
            '<div style="display:flex;flex-direction:column;align-items:center;gap:4px;">' +
                '<span class="portal-pill portal-pill-ok" style="font-size:11px;"><i class="fa fa-check"></i> Emitida</span>' +
                '<div style="display:flex;gap:4px;">' +
                    '<a href="' + pdfUrl + '" class="btn btn-xs btn-danger btn-flat" title="PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>' +
                    '<a href="' + xmlUrl + '" class="btn btn-xs btn-default btn-flat" title="XML"><i class="fa fa-code"></i> XML</a>' +
                '</div>' +
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
