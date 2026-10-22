<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cumpleañeros — {{ $ciclo?->nombre }}</title>

    @php
        $nombreEscuela = $setting?->nombre_escuela ?? config('app.school_name', 'Escuela');
        $logoPath      = public_path('imgs_escuela/reportes/' . ($setting?->logo_ruta ?? 'logo-escuela.png'));
        $periodo       = $mes ? ucfirst(\Carbon\Carbon::create(null, $mes, 1)->translatedFormat('F')) : 'Todos los meses';
    @endphp

    <style>
        @page { margin: 16mm 14mm; size: letter portrait; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #222; margin: 0; }

        .doc-header { width: 100%; border-collapse: collapse; margin-bottom: 12px; border-bottom: 2px solid #1a3a5c; }
        .doc-header td { vertical-align: middle; padding-bottom: 6px; }
        .school-logo { width: 60px; height: auto; }
        .school-name { font-size: 13px; font-weight: bold; color: #1a3a5c; text-transform: uppercase; }
        .doc-title   { text-align: right; font-size: 12px; font-weight: bold; color: #1a3a5c; text-transform: uppercase; }
        .doc-meta    { text-align: right; font-size: 8px; color: #555; margin-top: 3px; }

        .grupo { width: 100%; border-collapse: collapse; margin-top: 12px; page-break-inside: auto; }
        .grupo-th {
            background: #1a3a5c; color: #fff; font-size: 10px; font-weight: bold;
            padding: 5px 8px; text-transform: uppercase; text-align: left;
        }
        .grupo-count { color: #e8c46a; text-align: right; }
        .col-th {
            background: #e8f0f8; color: #1a3a5c; font-size: 7.5px; font-weight: bold;
            text-transform: uppercase; padding: 4px 8px; text-align: left; border-bottom: 1px solid #a0c0dc;
        }
        .grupo td.dato { padding: 4px 8px; border-bottom: 1px solid #e0e8f0; vertical-align: top; }
        .grupo tr { page-break-inside: avoid; }
        .fecha { font-weight: bold; color: #1a3a5c; white-space: nowrap; }
        .nombre { font-weight: bold; }
        .parentesco { color: #666; font-size: 8px; }
        .sin-datos { text-align: center; padding: 30px; color: #888; font-size: 11px; }
    </style>
</head>
<body>

    <table class="doc-header">
        <tr>
            <td style="width:10%;">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="school-logo" alt="Logo">
                @endif
            </td>
            <td style="padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
            </td>
            <td style="width:40%;">
                <div class="doc-title">Cumpleañeros — Contactos familiares</div>
                <div class="doc-meta">
                    Ciclo: {{ $ciclo?->nombre ?? '—' }}&nbsp;|&nbsp;{{ $periodo }}&nbsp;|&nbsp;{{ now()->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    @forelse ($grupos as $fila)
        @php $grupo = $fila['grupo']; @endphp
        <table class="grupo">
            <thead>
                <tr>
                    <th class="grupo-th" colspan="3">
                        {{ $grupo?->grado?->nivel?->nombre }} — {{ $grupo?->grado?->numero }}° {{ $grupo?->nombre }}
                    </th>
                    <th class="grupo-th grupo-count">{{ $fila['contactos']->count() }} cumpleañero(s)</th>
                </tr>
                <tr>
                    <th class="col-th" style="width:14%;">Fecha</th>
                    <th class="col-th" style="width:34%;">Contacto</th>
                    <th class="col-th" style="width:36%;">Alumno(s) / Parentesco</th>
                    <th class="col-th" style="width:16%;">Teléfono</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fila['contactos'] as $item)
                    @php $contacto = $item['contacto']; @endphp
                    <tr>
                        <td class="dato fecha">{{ $contacto->fecha_nacimiento->translatedFormat('d \d\e F') }}</td>
                        <td class="dato nombre">{{ $contacto->nombre_completo }}</td>
                        <td class="dato">
                            @foreach ($item['alumnos'] as $a)
                                {{ $a['alumno']->nombre }} {{ $a['alumno']->ap_paterno }}
                                <span class="parentesco">({{ ucfirst($a['parentesco'] ?? '—') }})</span>@if (! $loop->last)<br>@endif
                            @endforeach
                        </td>
                        <td class="dato">{{ $contacto->telefono_celular ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div class="sin-datos">No hay contactos familiares con cumpleaños para los filtros seleccionados.</div>
    @endforelse

</body>
</html>
