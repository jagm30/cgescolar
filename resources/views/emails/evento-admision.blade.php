<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 20px; margin: 0; }
    .container { background: #fff; padding: 30px; border-radius: 8px; max-width: 560px; margin: 0 auto; border-top: 4px solid #1a6eaa; }
    .badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
    .badge-nuevo     { background: #d1fae5; color: #065f46; }
    .badge-etapa     { background: #dbeafe; color: #1e40af; }
    .badge-seguimiento { background: #fef3c7; color: #92400e; }
    h2 { color: #1e293b; margin: 14px 0 4px; font-size: 20px; }
    .meta { color: #94a3b8; font-size: 13px; margin: 0 0 22px; }
    .row { padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .row:last-child { border-bottom: none; }
    .lbl { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .val { color: #1e293b; font-size: 14px; margin-top: 3px; }
    .pill { display: inline-block; padding: 2px 12px; border-radius: 12px; font-size: 13px; font-weight: 600; }
    .pill-gris  { background: #f1f5f9; color: #475569; }
    .pill-azul  { background: #dbeafe; color: #1e40af; }
    .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 26px; border-top: 1px solid #f1f5f9; padding-top: 16px; }
</style>
</head>
<body>
<div class="container">

    {{-- Badge de tipo de evento --}}
    @if($evento === 'nuevo_prospecto')
        <span class="badge badge-nuevo">&#x2795; Nuevo prospecto</span>
    @elseif($evento === 'cambio_etapa')
        <span class="badge badge-etapa">&#x21c4; Cambio de etapa</span>
    @elseif($evento === 'seguimiento')
        <span class="badge badge-seguimiento">&#x1F4CB; Seguimiento</span>
    @endif

    <h2>{{ $datos['prospecto_nombre'] }}</h2>
    <p class="meta">
        Por <strong>{{ $datos['responsable'] }}</strong> &nbsp;·&nbsp; {{ $datos['fecha'] }}
    </p>

    {{-- ── Detalle según evento ── --}}
    @if($evento === 'nuevo_prospecto')

        <div class="row">
            <div class="lbl">Nivel de interés</div>
            <div class="val">{{ $datos['nivel'] ?? '—' }}</div>
        </div>
        <div class="row">
            <div class="lbl">Canal de contacto</div>
            <div class="val">{{ ucfirst($datos['canal'] ?? '—') }}</div>
        </div>
        <div class="row">
            <div class="lbl">Nombre del contacto</div>
            <div class="val">{{ $datos['contacto'] ?? '—' }}</div>
        </div>
        <div class="row">
            <div class="lbl">Teléfono</div>
            <div class="val">{{ $datos['telefono'] ?? '—' }}</div>
        </div>
        @if(!empty($datos['email_contacto']))
        <div class="row">
            <div class="lbl">Correo del contacto</div>
            <div class="val">{{ $datos['email_contacto'] }}</div>
        </div>
        @endif

    @elseif($evento === 'cambio_etapa')

        <div class="row">
            <div class="lbl">Etapa</div>
            <div class="val" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:6px;">
                <span class="pill pill-gris">{{ ucfirst(str_replace('_', ' ', $datos['etapa_anterior'])) }}</span>
                <span style="color:#94a3b8;font-size:18px;">&#x2192;</span>
                <span class="pill pill-azul">{{ ucfirst(str_replace('_', ' ', $datos['etapa_nueva'])) }}</span>
            </div>
        </div>
        @if(!empty($datos['notas']))
        <div class="row">
            <div class="lbl">Notas</div>
            <div class="val">{{ $datos['notas'] }}</div>
        </div>
        @endif

    @elseif($evento === 'seguimiento')

        <div class="row">
            <div class="lbl">Tipo de acción</div>
            <div class="val">{{ ucfirst(str_replace('_', ' ', $datos['tipo_accion'])) }}</div>
        </div>
        <div class="row">
            <div class="lbl">Fecha del seguimiento</div>
            <div class="val">{{ \Carbon\Carbon::parse($datos['fecha_seguimiento'])->translatedFormat('d \d\e F \d\e Y') }}</div>
        </div>
        <div class="row">
            <div class="lbl">Notas</div>
            <div class="val">{{ $datos['notas'] }}</div>
        </div>

    @endif

    <div class="footer">
        Atentamente,<br>
        <strong>{{ $datos['responsable'] }}</strong>
    </div>
</div>
</body>
</html>
