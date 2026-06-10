<!DOCTYPE html>
<html lang="es">

<head>

    <head>
        <meta charset="UTF-8">

        {{-- Obtenemos la configuración de la escuela al inicio --}}
        @php
            $escuelaInfo = \App\Models\Setting::find(1);
            $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
            $logoRuta = $escuelaInfo->logo_ruta ?? 'logo-escuela.png';
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
                padding-bottom: 10px;
                margin-bottom: 25px;
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
                margin-top: 4px;
                text-transform: uppercase;
            }

            /* ── TÍTULOS DE SECCIÓN ── */
            .section-title {
                background-color: #1e4d7b;
                color: #ffffff;
                padding: 6px 10px;
                font-size: 12px;
                font-weight: bold;
                text-transform: uppercase;
                margin-bottom: 10px;
                border-radius: 3px;
            }

            /* ── TABLAS DE INFORMACIÓN ── */
            table.info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            table.info-table th {
                text-align: left;
                background-color: #f4f6f9;
                padding: 8px 10px;
                border: 1px solid #d0dde8;
                color: #1e4d7b;
                font-size: 11px;
                text-transform: uppercase;
            }

            table.info-table td {
                padding: 8px 10px;
                border: 1px solid #d0dde8;
                font-size: 12px;
            }

            /* ── TABLA DE CONCEPTOS (DESGLOSE) ── */
            table.concepts-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }

            table.concepts-table th {
                background-color: #f4f6f9;
                color: #1e4d7b;
                padding: 10px;
                font-size: 11px;
                border-bottom: 2px solid #d0dde8;
                text-transform: uppercase;
            }

            table.concepts-table td {
                padding: 10px;
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
            }

            /* ── UTILIDADES ── */
            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .text-green {
                color: #27ae60;
            }

            .text-red {
                color: #e74c3c;
            }

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
        </style>
    </head>

<body>


    {{-- ── 1. ENCABEZADO Y FOLIO ── --}}
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: left;">
                {{-- Busca el logo definido en la base de datos o el fallback --}}
                @if (file_exists(public_path('imgs_escuela/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @elseif (file_exists(public_path('imgs_escuela/reportes/logo-escuela.png')))
                    <img src="{{ public_path('imgs_escuela/reportes/logo-escuela.png') }}" class="school-logo"
                        alt="Logo">
                @else
                    <div
                        style="width: 80px; height: 80px; background: #e0e0e0; text-align:center; line-height:80px; color:#666; margin: 0 auto;">
                        LOGO
                    </div>
                @endif
            </td>
            <td style="width: 50%; text-align: center;">
                {{-- Nombre dinámico de la escuela --}}
                <div class="school-title">{{ $nombreEscuela }}</div>
                <div class="school-subtitle">Recibo Oficial de Cobro</div>
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

    {{-- ── 2. DATOS DEL ALUMNO ── --}}
    <div class="section-title">1. Datos del Alumno</div>
    <table class="info-table">
        <tr>
            <th style="width: 20%;">Nombre Completo</th>
            <td style="width: 50%;">
                <b>{{ $alumno->nombre ?? 'N/A' }} {{ $alumno->ap_paterno ?? '' }} {{ $alumno->ap_materno ?? '' }}</b>
            </td>
            <th style="width: 15%;">Matrícula</th>
            <td style="width: 15%; text-align: center;">
                <b>{{ $alumno->matricula ?? 'N/A' }}</b>
            </td>
        </tr>
    </table>

    {{-- ── 3. DETALLES DE LA OPERACIÓN ── --}}
    <div class="section-title">2. Detalles de la Operación</div>
    <table class="info-table">
        <tr>
            <th style="width: 20%;">Método de Pago</th>
            <td style="width: 30%;">{{ strtoupper($pago->forma_pago) }}</td>
            <th style="width: 20%;">Atendido Por</th>
            <td style="width: 30%;">{{ $pago->cajero->nombre ?? 'Sistema' }}</td>
        </tr>
        <tr>
            <th>Referencia</th>
            <td>{{ $pago->referencia ?? 'N/A' }}</td>
            <th>Estado</th>
            <td>
                <span class="badge badge-green">✔ PAGADO</span>
            </td>
        </tr>
    </table>

    {{-- ── 4. DESGLOSE DEL COBRO ── --}}
    <div class="section-title">3. Desglose de Conceptos</div>
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
                <tr>
                    <td>
                        <b>{{ $detalle->cargo->concepto->nombre }}</b>
                    </td>
                    <td class="text-center">{{ $detalle->cargo->periodo }}</td>

                    <td class="text-right">${{ number_format($detalle->monto_abonado, 2) }}</td>

                    {{-- Descuentos --}}
                    <td class="text-right text-green">
                        @php $total_descuento = $detalle->descuento_beca + $detalle->descuento_otros; @endphp
                        @if ($total_descuento > 0)
                            -${{ number_format($total_descuento, 2) }}
                        @else
                            $0.00
                        @endif
                    </td>

                    {{-- Recargos --}}
                    <td class="text-right text-red">
                        @if ($detalle->recargo_aplicado > 0)
                            +${{ number_format($detalle->recargo_aplicado, 2) }}
                        @else
                            $0.00
                        @endif
                    </td>

                    {{-- Subtotal Final --}}
                    <td class="text-right" style="font-weight: bold;">
                        ${{ number_format($detalle->monto_final, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right" style="padding: 12px 10px;">TOTAL PAGADO:</td>
                <td class="text-right" style="padding: 12px 10px; font-size: 16px;">
                    ${{ number_format($pago->monto_total, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- ── 5. SECCIÓN DE FIRMAS ── --}}
    <table style="width: 100%; margin-top: 60px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                _______________________________________<br>
                <b>Firma de Recepción (Caja)</b><br>
                <span class="text-muted">{{ $pago->cajero->nombre ?? config('app.name') }}</span>
            </td>
            <td style="width: 50%; text-align: center;">
                _______________________________________<br>
                <b>Firma de Conformidad</b><br>
                <span class="text-muted">Alumno / Tutor</span>
            </td>
        </tr>
    </table>

    {{-- Pie de página del documento --}}
    <div
        style="position: absolute; bottom: -20px; width: 100%; text-align: center; border-top: 1px solid #eee; padding-top: 10px; font-size: 10px; color: #999;">
        Este documento es un comprobante de pago interno. No tiene validez como comprobante fiscal (CFDI) a menos que se
        indique lo contrario.<br>
        Impreso el: {{ date('d/m/Y H:i') }}
    </div>

</body>

</html>
