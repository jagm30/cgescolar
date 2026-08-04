<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 10mm 12mm; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── ENCABEZADO ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 8px;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 270px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub { color: #777; font-size: 9pt; margin-top: 2px; text-transform: uppercase; }
        .report-title { text-align: right; }
        .report-title-main { color: #1e4d7b; font-size: 11pt; font-weight: bold; text-transform: uppercase; }
        .report-title-sub  { color: #666; font-size: 9pt; margin-top: 3px; line-height: 1.6; }

        /* ── CAJA RESUMEN PROSPECTO ── */
        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #dde4eb;
        }
        .info-box td {
            padding: 10px 14px;
            border-right: 1px solid #dde4eb;
            vertical-align: top;
        }
        .info-box td:last-child { border-right: none; }
        .info-lbl {
            font-size: 5pt;
            font-weight: bold;
            color: #8a9ab0;
            text-transform: uppercase;
            letter-spacing: .05em;
            display: block;
            margin-bottom: 3px;
        }
        .info-val {
            font-size: 9pt;
            font-weight: bold;
            color: #1a2634;
        }
        .info-badge {
            display: inline-block;
            font-size: 5pt;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 20px;
        }

        /* ── TÍTULO DE SECCIÓN ── */
        .section-title {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        .section-title td {
            background: #1e4d7b;
            color: #fff;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 10px;
            letter-spacing: .06em;
        }

        /* ── TABLAS ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        .data-table th {
            background: #f2f5f9;
            color: #1e4d7b;
            font-size: 5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 10px;
            border: 1px solid #dde4eb;
            text-align: left;
        }
        .data-table td {
            padding: 8px 10px;
            border: 1px solid #dde4eb;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) td { background: #f9fafb; }

        /* ── TIMELINE SEGUIMIENTOS ── */
        .seg-row td { padding: 10px 12px; border: 1px solid #dde4eb; vertical-align: top; }
        .seg-row:nth-child(even) td { background: #f9fafb; }
        .seg-tipo {
            font-size: 5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #1e4d7b;
            margin-bottom: 3px;
        }
        .seg-meta { font-size: 5pt; color: #8a9ab0; margin-bottom: 4px; }
        .seg-nota { font-size: 9pt; color: #2d3a4a; line-height: 1.5; }

        /* ── BADGES ── */
        .e-prospecto     { background:#dbeafe; color:#1e40af; }
        .e-cita          { background:#cffafe; color:#0e7490; }
        .e-visita        { background:#d1fae5; color:#065f46; }
        .e-documentacion { background:#fef9c3; color:#854d0e; }
        .e-aceptado      { background:#bbf7d0; color:#14532d; }
        .e-en_espera     { background:#fde68a; color:#78350f; }
        .e-no_aceptado   { background:#fecaca; color:#7f1d1d; }
        .e-inscrito      { background:#e0e7ef; color:#1e3a5f; }
        .e-no_concretado { background:#fee2e2; color:#991b1b; }

        .doc-pendiente  { background:#fef9c3; color:#854d0e; }
        .doc-entregado  { background:#d1fae5; color:#065f46; }
        .doc-no_aplica  { background:#e0e7ef; color:#6b7a8d; }

        /* ── PIE FIJO ── */
        .pie {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 4px;
            font-size: 5pt;
            color: #bbb;
        }

        .tc { text-align: center; }
        .tm { color: #8a9ab0; }
        .tb { font-weight: bold; }
        .no-data { color: #8a9ab0; font-style: italic; font-size: 9pt; padding: 8px; }
    </style>
</head>
<body>

@php
    $etapas = [
        'prospecto'     => 'Prospecto',    'cita'          => 'Cita',
        'visita'        => 'Visita',       'documentacion' => 'Documentación',
        'aceptado'      => 'Aceptado',     'en_espera'     => 'En espera',
        'no_aceptado'   => 'No aceptado',  'inscrito'      => 'Inscrito',
        'no_concretado' => 'No concretado',
    ];
    $tiposSeguimiento = [
        'llamada'      => 'Llamada',
        'visita'       => 'Visita',
        'email'        => 'Correo electrónico',
        'cambio_etapa' => 'Cambio de etapa',
        'nota'         => 'Nota',
    ];
    $canales = [
        'referido'       => 'Referido',
        'redes'          => 'Redes sociales',
        'visita_directa' => 'Visita directa',
        'web'            => 'Sitio web',
        'otro'           => 'Otro',
    ];
    $esc           = \App\Models\Setting::find(1);
    $nombreEscuela = $esc?->nombre_escuela ?? config('app.school_name');
@endphp

<div class="pie">
    {{ $nombreEscuela }} &mdash; Seguimiento de Prospecto &mdash; {{ $prospecto->nombre_completo }} &mdash;
    Generado el {{ now()->format('d/m/Y H:i') }}
</div>

{{-- ENCABEZADO --}}
<table class="header">
    <tr>
        <td style="width:28%;">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="school-logo" alt="Logo">
            @else
                <div style="width:210px;height:210px;background:#e0e0e0;text-align:center;line-height:210px;color:#888;font-size:18px;">LOGO</div>
            @endif
        </td>
        <td style="width:40%; padding-left:10px;">
            <div class="school-name">{{ $nombreEscuela }}</div>
            <div class="school-sub">Expediente de seguimiento de admisión</div>
        </td>
        <td class="report-title" style="width:33%;">
            <div class="report-title-main">Seguimiento de Prospecto</div>
            <div class="report-title-sub">
                <b>Folio:</b> #{{ str_pad($prospecto->id, 5, '0', STR_PAD_LEFT) }}<br>
                <b>Ciclo:</b> {{ $prospecto->ciclo?->nombre ?? 'N/A' }}<br>
                <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
            </div>
        </td>
    </tr>
</table>

{{-- CAJA RESUMEN --}}
<table class="info-box">
    <tr>
        <td style="width:30%;">
            <span class="info-lbl">Nombre completo</span>
            <span class="info-val">{{ $prospecto->nombre_completo }}</span>
        </td>
        <td style="width:18%; text-align:center;">
            <span class="info-lbl">Etapa actual</span>
            <span class="info-badge e-{{ $prospecto->etapa }}">
                {{ $etapas[$prospecto->etapa] ?? $prospecto->etapa }}
            </span>
        </td>
        <td style="width:22%;">
            <span class="info-lbl">Nivel / Grado de interés</span>
            <span class="info-val">
                {{ $prospecto->nivelInteres?->nombre ?? '—' }}
                @if ($prospecto->gradoInteres) / {{ $prospecto->gradoInteres->numero }}° @endif
            </span>
        </td>
        <td style="width:15%;">
            <span class="info-lbl">Primer contacto</span>
            <span class="info-val">{{ $prospecto->fecha_primer_contacto?->format('d/m/Y') ?? '—' }}</span>
        </td>
        <td style="width:15%;">
            <span class="info-lbl">Responsable</span>
            <span class="info-val">{{ $prospecto->responsable?->nombre ?? '—' }}</span>
        </td>
    </tr>
</table>

{{-- DATOS DE CONTACTO --}}
<table class="section-title"><tr><td>Datos de contacto</td></tr></table>
<table class="data-table">
    <tr>
        <th style="width:1%; white-space:nowrap;">Contacto principal</th>
        <td>{{ $prospecto->contacto_nombre }}</td>
        <th style="width:1%; white-space:nowrap;">Teléfono</th>
        <td>{{ $prospecto->contacto_telefono ?: '—' }}</td>
        <th style="width:1%; white-space:nowrap;">Correo</th>
        <td>{{ $prospecto->contacto_email ?: '—' }}</td>
    </tr>
    <tr>
        <th>Canal de contacto</th>
        <td>{{ $canales[$prospecto->canal_contacto] ?? ucfirst($prospecto->canal_contacto ?? '—') }}</td>
        <th>Fecha de nacimiento</th>
        <td>{{ $prospecto->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
        <th>Ciclo escolar</th>
        <td>{{ $prospecto->ciclo?->nombre ?? '—' }}</td>
    </tr>
    @if ($prospecto->motivo_no_concrecion)
    <tr>
        <th>Motivo no concreción</th>
        <td colspan="5" style="color:#991b1b;">{{ $prospecto->motivo_no_concrecion }}</td>
    </tr>
    @endif
</table>

{{-- SEGUIMIENTOS --}}
<table class="section-title">
    <tr>
        <td>
            Historial de seguimiento
            &nbsp;&mdash;&nbsp;
            {{ $prospecto->seguimientos->count() }} {{ $prospecto->seguimientos->count() === 1 ? 'registro' : 'registros' }}
        </td>
    </tr>
</table>

@if ($prospecto->seguimientos->count())
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%;" class="tc">#</th>
            <th style="width:14%;">Fecha</th>
            <th style="width:16%;">Tipo</th>
            <th style="width:18%;">Usuario</th>
            <th>Notas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($prospecto->seguimientos->sortBy('fecha') as $i => $seg)
        <tr class="seg-row">
            <td class="tc tm">{{ $i + 1 }}</td>
            <td>{{ $seg->fecha?->format('d/m/Y') ?? '—' }}</td>
            <td>
                <div class="seg-tipo">{{ $tiposSeguimiento[$seg->tipo_accion] ?? ucfirst($seg->tipo_accion) }}</div>
            </td>
            <td>{{ $seg->usuario?->nombre ?? 'Sistema' }}</td>
            <td class="seg-nota">{{ $seg->notas }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
    <p class="no-data">No hay seguimientos registrados para este prospecto.</p>
@endif

{{-- DOCUMENTOS --}}
@if ($prospecto->documentos->count())
<table class="section-title"><tr><td>Documentos entregados</td></tr></table>
<table class="data-table">
    <thead>
        <tr>
            <th style="width:4%;" class="tc">#</th>
            <th>Tipo de documento</th>
            <th style="width:20%;" class="tc">Estado</th>
            <th style="width:30%;">Archivo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($prospecto->documentos as $i => $doc)
        <tr>
            <td class="tc tm">{{ $i + 1 }}</td>
            <td class="tb">{{ $doc->tipo_documento }}</td>
            <td class="tc">
                <span class="info-badge doc-{{ $doc->estado }}">
                    {{ str_replace('_', ' ', ucfirst($doc->estado)) }}
                </span>
            </td>
            <td class="tm" style="font-size:9px;">{{ $doc->archivo_nombre ?: '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- FIRMAS --}}
<table style="width:100%; border-collapse:collapse; margin-top:30px;">
    <tr>
        <td style="width:33%; text-align:center; padding-top:30px; border-top:1px solid #999; font-size:9pt; color:#555;">
            <div style="font-size:6pt; color:#8a9ab0; text-transform:uppercase; letter-spacing:.05em;">Responsable de admisiones</div>
            {{ $prospecto->responsable?->nombre ?? '—' }}
        </td>
        <td style="width:33%;"></td>
        <td style="width:33%; text-align:center; padding-top:30px; border-top:1px solid #999; font-size:9pt; color:#555;">
            <div style="font-size:6pt; color:#8a9ab0; text-transform:uppercase; letter-spacing:.05em;">Sello institucional</div>
        </td>
    </tr>
</table>

</body>
</html>
