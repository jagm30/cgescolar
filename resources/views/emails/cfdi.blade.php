<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        // $logoBase64 viene preparado desde CfdiController::enviarCorreo()
        $logoUrl = $logoBase64 ?? null;
    @endphp

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding: 24px 12px;
        }
        .wrapper {
            max-width: 560px;
            margin: 0 auto;
        }

        /* ── Cabecera institucional ── */
        .inst-header {
            background: linear-gradient(135deg, #1e4d7b 0%, #2e6da4 100%);
            border-radius: 10px 10px 0 0;
            padding: 22px 28px;
            display: table;
            width: 100%;
        }
        .inst-header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 70px;
        }
        .inst-header-logo img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 6px;
            background: #fff;
            padding: 4px;
        }
        .inst-header-logo .logo-placeholder {
            width: 56px; height: 56px;
            background: rgba(255,255,255,.15);
            border-radius: 6px;
            display: inline-block;
        }
        .inst-header-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }
        .inst-header-text .school-name {
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .inst-header-text .school-sub {
            color: rgba(255,255,255,.70);
            font-size: 11px;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .inst-header-badge {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            white-space: nowrap;
        }
        .inst-header-badge span {
            display: inline-block;
            background: rgba(255,255,255,.15);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.25);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        /* ── Cuerpo ── */
        .body-card {
            background: #fff;
            padding: 28px 32px;
            border-left: 1px solid #dde4eb;
            border-right: 1px solid #dde4eb;
        }
        .greeting {
            font-size: 15px;
            color: #1e4d7b;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .intro-text {
            font-size: 13px;
            color: #475569;
            line-height: 1.65;
            margin-bottom: 20px;
        }

        /* ── Folio destacado ── */
        .folio-box {
            background: #f3e8fd;
            border: 1px solid #d8b4fe;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        .folio-box-label {
            display: table-cell;
            vertical-align: middle;
            font-size: 11px;
            font-weight: 700;
            color: #6a1a7b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .folio-box-value {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-family: monospace;
            font-size: 15px;
            font-weight: 700;
            color: #6a1a7b;
        }

        /* ── Archivos adjuntos ── */
        .adjuntos-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 8px;
        }
        .adjunto-item {
            display: table;
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 6px;
        }
        .adjunto-icon {
            display: table-cell;
            vertical-align: middle;
            width: 32px;
        }
        .adjunto-icon span {
            display: inline-block;
            width: 26px; height: 26px;
            border-radius: 5px;
            text-align: center;
            line-height: 26px;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
        }
        .icon-pdf { background: #e53e3e; }
        .icon-xml { background: #2e86de; }
        .adjunto-info {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }
        .adjunto-nombre {
            font-size: 12px;
            font-weight: 700;
            color: #1a2634;
        }
        .adjunto-desc {
            font-size: 11px;
            color: #8a9ab0;
            margin-top: 1px;
        }

        /* ── Nota ── */
        .nota {
            background: #f0f6ff;
            border-left: 3px solid #1e4d7b;
            border-radius: 0 6px 6px 0;
            padding: 10px 14px;
            font-size: 12px;
            color: #475569;
            line-height: 1.6;
            margin-top: 20px;
        }

        /* ── Atentamente ── */
        .atentamente {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #e8ecf2;
            font-size: 13px;
            color: #64748b;
        }
        .atentamente strong {
            display: block;
            margin-top: 4px;
            font-size: 14px;
            color: #1e4d7b;
        }

        /* ── Pie ── */
        .footer-bar {
            background: #1e4d7b;
            border-radius: 0 0 10px 10px;
            padding: 12px 28px;
            text-align: center;
        }
        .footer-bar p {
            font-size: 11px;
            color: rgba(255,255,255,.60);
            margin: 0;
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ── Cabecera institucional ── --}}
    <div class="inst-header">
        <div class="inst-header-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo">
            @else
                <span class="logo-placeholder"></span>
            @endif
        </div>
        <div class="inst-header-text">
            <div class="school-name">{{ $nombreEscuela }}</div>
            <div class="school-sub">Facturación electrónica</div>
        </div>
        <div class="inst-header-badge">
            <span>CFDI</span>
        </div>
    </div>

    {{-- ── Cuerpo ── --}}
    <div class="body-card">

        <p class="greeting">Estimado(a) padre / tutor de familia,</p>

        <p class="intro-text">
            Adjunto a este correo encontrará la factura electrónica (CFDI) correspondiente
            al pago registrado en <strong>{{ $nombreEscuela }}</strong>, en los formatos
            <strong>PDF</strong> (imprimible) y <strong>XML</strong> (archivo fiscal timbrado ante el SAT).
        </p>

        {{-- Folio --}}
        <div class="folio-box">
            <div class="folio-box-label">Folio fiscal</div>
            <div class="folio-box-value">{{ $folio }}</div>
        </div>

        {{-- Archivos adjuntos --}}
        <p class="adjuntos-title">Archivos adjuntos</p>

        <div class="adjunto-item">
            <div class="adjunto-icon">
                <span class="icon-pdf">PDF</span>
            </div>
            <div class="adjunto-info">
                <div class="adjunto-nombre">{{ $folio }}.pdf</div>
                <div class="adjunto-desc">Representación imprimible del comprobante fiscal</div>
            </div>
        </div>

        <div class="adjunto-item">
            <div class="adjunto-icon">
                <span class="icon-xml">XML</span>
            </div>
            <div class="adjunto-info">
                <div class="adjunto-nombre">{{ $folio }}.xml</div>
                <div class="adjunto-desc">Archivo XML timbrado, válido ante el SAT</div>
            </div>
        </div>

        {{-- Nota --}}
        <div class="nota">
            Si tiene alguna duda sobre este comprobante, responda a este correo
            o acuda a la institución en horario de atención. Conserve ambos archivos
            para sus registros fiscales.
        </div>

        {{-- Atentamente --}}
        <div class="atentamente">
            Atentamente,
            <strong>{{ $nombreEscuela }}</strong>
        </div>

    </div>

    {{-- ── Pie ── --}}
    <div class="footer-bar">
        <p>Este mensaje fue generado automáticamente &mdash; {{ $nombreEscuela }} &mdash; No responder a este correo si no reconoce este comprobante.</p>
    </div>

</div>
</body>
</html>
