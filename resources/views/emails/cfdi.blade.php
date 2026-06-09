<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 20px; color: #333; }
        .container { background: #fff; padding: 30px; border-radius: 8px; max-width: 520px;
                     margin: 0 auto; border-top: 4px solid #7b2d8b; }
        .folio { display: inline-block; background: #f3e8fd; color: #6a1a7b; font-family: monospace;
                 font-size: 16px; font-weight: bold; padding: 4px 12px; border-radius: 5px;
                 border: 1px solid #d8b4fe; margin: 8px 0 16px; }
        .aviso { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;
                 padding: 12px 16px; font-size: 13px; color: #475569; margin-top: 20px; }
        .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color:#2c3e50;margin-top:0;">Factura electrónica</h2>

        <p style="color:#475569;line-height:1.6;">
            Adjunto a este correo encontrarás tu factura electrónica (CFDI) en formato
            <strong>PDF</strong> y <strong>XML</strong>.
        </p>

        <div class="folio">{{ $folio }}</div>

        <div class="aviso">
            <strong>Archivos adjuntos:</strong><br>
            📄 <strong>{{ $folio }}.pdf</strong> — Representación imprimible del CFDI<br>
            📁 <strong>{{ $folio }}.xml</strong> — Archivo XML timbrado ante el SAT
        </div>

        <p style="color:#475569;font-size:13px;margin-top:20px;line-height:1.6;">
            Si tienes alguna duda sobre este comprobante, comunícate con nosotros
            respondiendo a este correo o acudiendo a la institución.
        </p>

        <div class="footer">
            Atentamente,<br>
            <strong>{{ $nombreEscuela }}</strong>
        </div>
    </div>
</body>
</html>
