<!DOCTYPE html>
<html lang="es">

<head>
    <head>
        <meta charset="UTF-8">

        @php
            $escuelaInfo   = \App\Models\Setting::find(1);
            $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
            $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';

            $mesesEs = [
                1  => 'Enero',   2  => 'Febrero',  3  => 'Marzo',
                4  => 'Abril',   5  => 'Mayo',      6  => 'Junio',
                7  => 'Julio',   8  => 'Agosto',    9  => 'Septiembre',
                10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
            ];
        @endphp

        <title>Recibo de Pago - Folio {{ $pago->folio_recibo }}</title>
        <style>
            body {
                font-family: 'Helvetica', 'Arial', sans-serif;
                font-size: 12px;
                color: #333;
                margin: 0;
                padding: 0;
            }

            /* ── ENCABEZADO ── */
            .header-table {
                width: 100%;
                border-bottom: 3px solid #1e4d7b;
                padding-bottom: 6px;
                margin-bottom: 10px;
            }

            .header-table td {
                vertical-align: middle;
            }

            .school-title {
                color: #1e4d7b;
                font-size: 20px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .school-subtitle {
                color: #666;
                font-size: 11px;
                margin-top: 2px;
                text-transform: uppercase;
            }

            /* ── TÍTULOS DE SECCIÓN ── */
            .section-title {
                background-color: #1e4d7b;
                color: #ffffff;
                padding: 4px 10px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
                margin-bottom: 4px;
                border-radius: 3px;
            }

            /* ── TABLAS DE INFORMACIÓN ── */
            table.info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 8px;
            }

            table.info-table th {
                text-align: left;
                background-color: #f4f6f9;
                padding: 3px 8px;
                border: 1px solid #d0dde8;
                color: #1e4d7b;
                font-size: 11px;
                text-transform: uppercase;
            }

            table.info-table td {
                padding: 3px 8px;
                border: 1px solid #d0dde8;
                font-size: 12px;
            }

            /* ── TABLA DE CONCEPTOS (DESGLOSE) ── */
            table.concepts-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 10px;
            }

            table.concepts-table th {
                background-color: #f4f6f9;
                color: #1e4d7b;
                padding: 3px 8px;
                font-size: 11px;
                border-bottom: 2px solid #d0dde8;
                text-transform: uppercase;
            }

            table.concepts-table td {
                padding: 3px 8px;
                border-bottom: 1px solid #eee;
                font-size: 12px;
            }

            .total-row td {
                background-color: #f4f6f9;
                font-size: 14px;
                font-weight: bold;
                color: #1e4d7b;
                border-top: 2px solid #1e4d7b;
                border-bottom: none;
                padding: 3px 8px;
            }

            /* ── UTILIDADES ── */
            .text-center { text-align: center; }
            .text-right  { text-align: right; }
            .text-green  { color: #27ae60; }
            .text-red    { color: #e74c3c; }

            .text-muted {
                color: #888;
                font-size: 10px;
            }

            .badge {
                background: #e0e0e0;
                color: #333;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .badge-green {
                background: #2ecc71;
                color: #fff;
            }

            .school-logo {
                width: 80px;
                height: auto;
                display: block;
                margin: 0 auto;
            }

            /* ── LÍNEA DE CORTE ── */
            .linea-corte {
                border: none;
                border-top: 1.5px dashed #aaa;
                margin: 12px 0;
                text-align: center;
            }
            .linea-corte span {
                display: inline-block;
                background: #fff;
                padding: 0 10px;
                font-size: 9px;
                color: #bbb;
                position: relative;
                top: -7px;
                letter-spacing: 1px;
                text-transform: uppercase;
            }

            /* ── PIE ── */
            .pie-pagina {
                text-align: center;
                border-top: 1px solid #eee;
                padding-top: 6px;
                margin-top: 14px;
                font-size: 10px;
                color: #999;
            }
        </style>
    </head>

<body>

@foreach (['COPIA PADRE DE FAMILIA', 'COPIA ARCHIVO'] as $tipoCopia)

    {{-- ── 1. ENCABEZADO Y FOLIO ── --}}
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: left;">
                @if (file_exists(public_path('imgs_escuela/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @elseif (file_exists(public_path('imgs_escuela/reportes/logo-escuela.png')))
                    <img src="{{ public_path('imgs_escuela/reportes/logo-escuela.png') }}" class="school-logo" alt="Logo">
                @else
                    <div style="width: 80px; height: 80px; background: #e0e0e0; text-align:center; line-height:80px; color:#666; margin: 0 auto;">
                        LOGO
                    </div>
                @endif
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="school-title">{{ $nombreEscuela }}</div>
                <div class="school-subtitle">
                    Recibo Oficial de Cobro &nbsp;&mdash;&nbsp; {{ $tipoCopia }}
                </div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="margin-bottom: 6px;">
                    <b style="color: #1e4d7b; font-size: 16px;">FOLIO: {{ $pago->folio_recibo }}</b>
                </div>
                <div style="color: #666; font-size: 11px;">
                    <b>Fecha de Emisión:</b><br>
                    {{ $pago->fecha_pago->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── 2. DATOS DEL ALUMNO Y OPERACIÓN ── --}}
    <div class="section-title">1. Datos del Pago</div>
    <table class="info-table">
        <tr>
            <th style="width: 18%;">Alumno</th>
            <td style="width: 40%;">
                <b>{{ $alumno->nombre ?? 'N/A' }} {{ $alumno->ap_paterno ?? '' }} {{ $alumno->ap_materno ?? '' }}</b>
            </td>
            <th style="width: 13%;">Matrícula</th>
            <td style="width: 29%; text-align: center;">
                <b>{{ $alumno->matricula ?? 'N/A' }}</b>
            </td>
        </tr>
        <tr>
            <th>Forma de Pago</th>
            <td>{{ strtoupper($pago->forma_pago) }}
                @if($pago->referencia) &nbsp;<span style="color:#888;">— Ref: {{ $pago->referencia }}</span>@endif
            </td>
            <th>Cajero / Estado</th>
            <td>
                {{ $pago->cajero->nombre ?? 'Sistema' }}
                &nbsp;<span class="badge badge-green">PAGADO</span>
            </td>
        </tr>
    </table>

    {{-- ── 4. DESGLOSE DEL COBRO ── --}}
    <div class="section-title">2. Desglose de Conceptos</div>
    <table class="concepts-table">
        <thead>
            <tr>
                <th style="width: 35%; text-align: left;">Concepto</th>
                <th style="width: 15%; text-align: center;">Periodo</th>
                <th style="width: 15%;" class="text-right">Monto Base</th>
                <th style="width: 10%;" class="text-right">Desc.</th>
                <th style="width: 10%;" class="text-right">Recargo</th>
                <th style="width: 15%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pago->detalles as $detalle)
                @php
                    $periodoLabel = null;
                    if ($detalle->cargo->periodo) {
                        [$anio, $mes] = explode('-', $detalle->cargo->periodo);
                        $periodoLabel = ($mesesEs[(int) $mes] ?? '') . ' ' . $anio;
                    }
                    $total_descuento = $detalle->descuento_beca + $detalle->descuento_otros;
                @endphp
                <tr>
                    <td>
                        <b>{{ $detalle->cargo->concepto->nombre }}</b>
                        @if ($periodoLabel)
                            <br><span class="text-muted">{{ $periodoLabel }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $periodoLabel ?? '—' }}</td>
                    <td class="text-right">${{ number_format($detalle->monto_abonado, 2) }}</td>
                    <td class="text-right text-green">
                        @if ($total_descuento > 0)
                            -${{ number_format($total_descuento, 2) }}
                        @else
                            $0.00
                        @endif
                    </td>
                    <td class="text-right text-red">
                        @if ($detalle->recargo_aplicado > 0)
                            +${{ number_format($detalle->recargo_aplicado, 2) }}
                        @else
                            $0.00
                        @endif
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        ${{ number_format($detalle->monto_final, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL PAGADO:</td>
                <td class="text-right" style="font-size: 16px;">
                    ${{ number_format($pago->monto_total, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- ── 5. FIRMA / SELLO ── --}}
    <table style="width: 100%; margin-top: 20px;">
        <tr>
            <td style="text-align: center;">
                _______________________________________<br>
                <b>Firma / Sello de Cajero</b><br>
                <span class="text-muted">{{ $pago->cajero->nombre ?? config('app.name') }}</span>
            </td>
        </tr>
    </table>

    {{-- Pie de página ── --}}
    <div class="pie-pagina">
        Este documento es un comprobante de pago interno. No tiene validez como comprobante fiscal (CFDI) a menos que se indique lo contrario.
    </div>

    {{-- Línea de corte solo entre las dos copias --}}
    @if ($loop->first)
        <div class="linea-corte">
            <span>-- recortar aqui --</span>
        </div>
    @endif

@endforeach

</body>

</html>
