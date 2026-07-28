@extends('layouts.master')

@section('page_title', 'Resumen de Cobro')
@section('page_subtitle', 'Folio: ' . $pago->folio_recibo)

@section('content')
    @php
        $cfdiVigente  = $pago->cfdis->where('estado', 'vigente')->first();
        $cfdiGlobal   = $pago->cfdiGlobal->where('estado', 'vigente')->first();
        $tieneFactura = $cfdiVigente || $cfdiGlobal;
        $esAnulado    = $pago->estado === 'anulado';
        $puedeFacturar = auth()->user()->esAdministrador() || auth()->user()->esCajero();
    @endphp
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            {{-- Tarjeta Principal del Resumen --}}
            <div class="box box-solid" style="border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <div class="box-header with-border" style="padding: 20px; background: #fafafa;">
                    <h3 class="box-title" style="font-weight: 700; color: #2c3e50;">
                        <i class="fa fa-file-text-o text-blue"></i> Operación Exitosa
                    </h3>
                    <div class="box-tools">
                        <a href="{{ route('cobros.pdf', $pago->id) }}" target="_blank" class="btn btn-primary btn-flat">
                            <i class="fa fa-print"></i> Generar Recibo Oficial (PDF)
                        </a>
                        @if ($puedeFacturar && isset($configFiscal) && $configFiscal && !$esAnulado && !$tieneFactura)
                            <button type="button" class="btn btn-flat"
                                    style="background:#7b2d8b;color:#fff;"
                                    data-toggle="collapse" data-target="#panel-factura">
                                <i class="fa fa-file-text-o"></i> Facturar
                            </button>
                        @elseif ($tieneFactura)
                            <span class="btn btn-flat disabled"
                                  style="background:#e8f5ee;color:#00875a;cursor:default;">
                                <i class="fa fa-check-circle"></i> CFDI emitido
                            </span>
                        @endif
                        <a href="{{ route('cobros.index') }}" class="btn btn-success btn-flat">
                            <i class="fa fa-plus"></i> Nuevo Cobro
                        </a>
                    </div>
                </div>

                {{-- Panel colapsable de facturación --}}
                @if ($puedeFacturar && isset($configFiscal) && $configFiscal && !$esAnulado && !$tieneFactura)
                <div id="panel-factura" class="collapse {{ session('error') ? 'in' : '' }}" style="border-bottom:1px solid #e0e7ef; background:#faf5ff;">
                    <div style="padding:18px 25px;">
                        <h4 style="color:#7b2d8b;margin-top:0;margin-bottom:14px;">
                            <i class="fa fa-file-text-o"></i> Emitir CFDI
                        </h4>
                        <form method="POST" action="{{ route('cfdis.emitir', $pago->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-7">
                                    <label style="font-size:11px;font-weight:700;color:#4a5568;display:block;margin-bottom:6px;">
                                        RFC del receptor
                                    </label>
                                    @php
                                    $nombreRegimen = [
                                        '601'=>'General Personas Morales','603'=>'PM Fines no Lucrativos',
                                        '605'=>'Sueldos y Salarios','606'=>'Arrendamiento',
                                        '608'=>'Demás ingresos','611'=>'Dividendos',
                                        '612'=>'Act. Empresariales y Profesionales','614'=>'Intereses',
                                        '615'=>'Premios','616'=>'Sin obligaciones fiscales',
                                        '620'=>'Soc. Cooperativas','621'=>'Incorporación Fiscal',
                                        '622'=>'Act. Agrícolas/Ganaderas','625'=>'Plataformas Tecnológicas',
                                        '626'=>'RESICO',
                                    ];
                                    @endphp
                                    @forelse ($razonesDisponibles as $rs)
                                        <label style="display:flex;align-items:flex-start;gap:8px;padding:7px 9px;
                                                      border:1px solid #e0e7ef;border-radius:6px;margin-bottom:4px;
                                                      cursor:pointer;font-weight:400;background:#fff;">
                                            <input type="radio" name="razon_social_id" value="{{ $rs->id }}"
                                                   data-uso="{{ $rs->uso_cfdi_default }}"
                                                   data-regimen="{{ $rs->regimen_fiscal }}"
                                                   {{ $loop->first ? 'checked' : '' }}
                                                   style="margin-top:3px;flex-shrink:0;">
                                            <span>
                                                <span style="display:block;font-size:12px;font-weight:700;color:#1a2634;">{{ $rs->rfc }}</span>
                                                <span style="display:block;font-size:11px;color:#4a5568;">{{ $rs->razon_social }}</span>
                                                <span style="display:block;font-size:10px;color:#b0bec5;">
                                                    {{ $rs->contacto?->nombre_completo }}
                                                    &nbsp;·&nbsp; Régimen {{ $rs->regimen_fiscal }}
                                                    @if(isset($nombreRegimen[$rs->regimen_fiscal]))
                                                        — {{ $nombreRegimen[$rs->regimen_fiscal] }}
                                                    @endif
                                                </span>
                                            </span>
                                        </label>
                                    @empty
                                    @endforelse
                                    <label style="display:flex;align-items:center;gap:8px;padding:7px 9px;
                                                  border:1px solid #e0e7ef;border-radius:6px;cursor:pointer;
                                                  font-weight:400;background:#fff;">
                                        <input type="radio" name="razon_social_id" value=""
                                               data-uso="S01" data-regimen="616"
                                               {{ $razonesDisponibles->isEmpty() ? 'checked' : '' }}>
                                        <span>
                                            <span style="display:block;font-size:12px;font-weight:700;color:#1a2634;">XAXX010101000</span>
                                            <span style="display:block;font-size:11px;color:#8a9ab0;">Público en general · Régimen 616</span>
                                        </span>
                                    </label>
                                </div>
                                <div class="col-sm-5">
                                    <div class="form-group">
                                        <label style="font-size:11px;font-weight:700;color:#4a5568;">Uso CFDI</label>
                                        <select id="uso-cfdi-select-recibo" name="uso_cfdi" class="form-control input-sm" style="border-radius:5px;">
                                            <option value="D10">D10 — Pagos por servicios educativos</option>
                                            <option value="D08">D08 — Transportación escolar obligatoria</option>
                                            <option value="D01">D01 — Honorarios médicos y gastos hospitalarios</option>
                                            <option value="G03">G03 — Gastos en general</option>
                                            <option value="G01">G01 — Adquisición de mercancias</option>
                                            <option value="CP01">CP01 — Pagos</option>
                                            <option value="S01">S01 — Sin efectos fiscales</option>
                                        </select>
                                        <p id="nota-publico-recibo" style="display:none;margin:4px 0 0;font-size:10px;
                                                                             color:#856404;background:#fff3cd;
                                                                             border-radius:4px;padding:4px 7px;">
                                            <i class="fa fa-info-circle"></i>
                                            El SAT sólo permite <strong>S01</strong> para "Público en General" (régimen 616).
                                        </p>
                                        <p id="nota-regimen-recibo" style="display:none;margin:4px 0 0;font-size:10px;
                                                                             color:#721c24;background:#f8d7da;
                                                                             border-radius:4px;padding:4px 7px;">
                                            <i class="fa fa-exclamation-triangle"></i>
                                            El régimen registrado no permite <strong>D10</strong> ni deducciones personales.
                                            Actualiza el régimen en los datos de facturación, o usa <strong>G03 / S01</strong>.
                                        </p>
                                    </div>
                                    <button type="submit" class="btn btn-block btn-flat"
                                            style="background:#7b2d8b;color:#fff;border-radius:6px;font-weight:600;margin-top:10px;">
                                        <i class="fa fa-file-text-o"></i> Emitir CFDI
                                    </button>
                                </div>
                            </div>
                        </form>
                        <script>
                        (function () {
                            var regimenesDeduccion = ['605','606','608','611','612','614','615','621','625','626'];

                            var $radios          = $('input[name="razon_social_id"]');
                            var $select          = $('#uso-cfdi-select-recibo');
                            var $notaPublico     = $('#nota-publico-recibo');
                            var $notaRegimen     = $('#nota-regimen-recibo');

                            function actualizarUso() {
                                var $checked  = $radios.filter(':checked');
                                var uso       = $checked.data('uso') || 'S01';
                                var regimen   = String($checked.data('regimen') || '');
                                var esPublico = $checked.val() === '';

                                $notaPublico.hide();
                                $notaRegimen.hide();

                                if (esPublico) {
                                    $select.val('S01').prop('disabled', true);
                                    $notaPublico.show();
                                    return;
                                }

                                $select.prop('disabled', false);

                                var admiteDeduccion = regimenesDeduccion.indexOf(regimen) !== -1;

                                $select.find('option[value="D10"], option[value="D08"], option[value="D01"]')
                                       .prop('disabled', !admiteDeduccion);

                                if (!admiteDeduccion) {
                                    $notaRegimen.show();
                                    $select.val(['D10','D08','D01'].indexOf(uso) !== -1 ? 'G03' : uso);
                                } else {
                                    $select.val(uso);
                                }
                            }

                            $radios.on('change', actualizarUso);
                            actualizarUso();
                        }());
                        </script>
                    </div>
                </div>
                @endif

                <div class="box-body" style="padding: 25px;">
                    {{-- Bloque de datos rápidos --}}
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-sm-4">
                            <label style="color:#7f8c8d; font-size:11px; text-transform:uppercase;">Folio de Recibo</label>
                            <p style="font-family: monospace; font-size: 18px; font-weight: bold; color: #1e4d7b;">
                                {{ $pago->folio_recibo }}
                            </p>
                        </div>
                        <div class="col-sm-4">
                            <label style="color:#7f8c8d; font-size:11px; text-transform:uppercase;">Fecha de Pago</label>
                            <p style="font-size: 15px; font-weight: 600;">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-sm-4">
                            <label style="color:#7f8c8d; font-size:11px; text-transform:uppercase;">Método de Pago</label>
                            <p><span class="label label-info"
                                    style="font-size:12px;">{{ strtoupper($pago->forma_pago) }}</span></p>
                        </div>
                    </div>

                    @if ($alumno)
                        <div class="well well-sm"
                            style="background: #f8fafc; border-color: #e2e8f0; padding: 15px; border-radius: 6px;">
                            <label
                                style="color:#94a3b8; font-size:10px; text-transform:uppercase; display:block; margin-bottom:5px;">Alumno
                                Involucrado</label>
                            <span style="font-size: 16px; font-weight: 700; color: #334155;">
                                {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                            </span>
                            <code
                                style="float: right; font-size: 12px; background: #fff; border: 1px solid #e2e8f0;">{{ $alumno->matricula }}</code>
                        </div>
                    @endif

                    {{-- Tabla simple del desglose web --}}
                    <table class="table table-bordered table-striped" style="margin-top: 20px;">
                        <thead>
                            <tr style="background: #f4f6f9; color: #1e4d7b;">
                                <th>Concepto / Periodo</th>
                                <th class="text-right">Monto Base</th>
                                <th class="text-right">Descuentos</th>
                                <th class="text-right">Recargos</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pago->detalles as $detalle)
                                <tr>
                                    <td>
                                        <b>{{ $detalle->cargo->concepto->nombre }}</b>
                                        @if($detalle->cargo->periodo_label)
                                            <small class="text-muted" style="display:block;">
                                                {{ $detalle->cargo->periodo_label }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-right">${{ number_format($detalle->monto_abonado, 2) }}</td>
                                    <td class="text-right text-green">
                                        @php
                                            $totalDesc = (float)$detalle->descuento_beca
                                                       + (float)$detalle->descuento_pronto_pago
                                                       + (float)$detalle->descuento_otros;
                                        @endphp
                                        -${{ number_format($totalDesc, 2) }}
                                        @if($detalle->descuento_beca > 0)
                                            <small class="text-muted" style="display:block;font-size:10px;">
                                                Beca: -${{ number_format($detalle->descuento_beca, 2) }}
                                            </small>
                                        @endif
                                        @if($detalle->descuento_pronto_pago > 0)
                                            <small class="text-muted" style="display:block;font-size:10px;">
                                                Pronto pago: -${{ number_format($detalle->descuento_pronto_pago, 2) }}
                                            </small>
                                        @endif
                                        @if($detalle->descuento_otros > 0)
                                            <small class="text-muted" style="display:block;font-size:10px;">
                                                Otros: -${{ number_format($detalle->descuento_otros, 2) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-right text-red">+${{ number_format($detalle->recargo_aplicado, 2) }}
                                    </td>
                                    <td class="text-right" style="font-weight: 700;">
                                        ${{ number_format($detalle->monto_final, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Gran Total --}}
                    <div class="text-right" style="margin-top: 25px; border-top: 2px solid #f4f6f9; padding-top: 15px;">
                        <span style="font-size: 16px; color: #7f8c8d; font-weight: 600; margin-right: 15px;">Monto Total
                            Pagado:</span>
                        <span
                            style="font-size: 28px; font-weight: bold; color: #1e4d7b;">${{ number_format($pago->monto_total, 2) }}</span>
                    </div>
                </div>

                <div class="box-footer" style="background: #fafafa; padding: 15px 25px;">
                    <a href="{{ route('alumnos.estado-cuenta', $alumno?->id) }}" class="btn btn-default btn-flat btn-sm">
                        <i class="fa fa-list-alt"></i> Ver estado de cuenta del alumno
                    </a>
                </div>

            </div>

        </div>
    </div>
@endsection
