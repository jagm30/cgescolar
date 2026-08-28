<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta      ?? 'logo-escuela.png';
        $totalAlumnos  = $grupo->inscripciones->count();

        // Alumnos ordenados por apellido
        $inscripciones = $grupo->inscripciones->sortBy(fn($i) => $i->alumno->ap_paterno);
    @endphp

    <title>Contactos Familiares — {{ $grupo->grado->nivel->nombre }} {{ $grupo->grado->numero }}° {{ $grupo->nombre }}</title>

    <style>
        @page { margin: 8mm 10mm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado institucional ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 6px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo  { width: 100px; height: auto; display: block; }
        .school-name  {
            color: #1e4d7b;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub {
            color: #777;
            font-size: 10px;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .report-title      { text-align: right; }
        .report-title-main {
            color: #1e4d7b;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title-sub  { color: #666; font-size: 10px; margin-top: 3px; }

        /* ── Caja de datos del grupo ── */
        .info-box    { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1px solid #dde4eb; }
        .info-box td { padding: 7px 14px; border-right: 1px solid #dde4eb; vertical-align: middle; }
        .info-box td:last-child { border-right: none; }
        .info-lbl {
            font-size: 9px;
            font-weight: bold;
            color: #8a9ab0;
            text-transform: uppercase;
            letter-spacing: .05em;
            display: block;
            margin-bottom: 2px;
        }
        .info-val   { font-size: 12px; font-weight: bold; color: #1a2634; }
        .info-badge {
            display: inline-block;
            background: #e8f0fb;
            color: #2e6da4;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 4px;
            border: 1px solid #b3d0f0;
        }
        .info-count {
            display: inline-block;
            background: #eaf6ee;
            color: #1a7a3c;
            font-size: 14px;
            font-weight: bold;
            padding: 1px 10px;
            border-radius: 4px;
            border: 1px solid #a9dcb9;
        }

        /* ── Título de sección (alumno) ── */
        .alumno-title {
            background: #1e4d7b;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 5px 10px;
            margin-top: 12px;
            margin-bottom: 0;
        }
        .alumno-title .matricula {
            float: right;
            font-weight: normal;
            font-size: 9px;
            color: #cfe0f2;
        }

        /* ── Tabla de contactos ── */
        .main-table              { width: 100%; border-collapse: collapse; margin-bottom: 0; page-break-inside: avoid; }
        .main-table thead th     {
            background: #2e6da4;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 7px;
            border: 1px solid #245a8f;
            letter-spacing: .04em;
        }
        .main-table tbody td     {
            padding: 5px 7px;
            border: 1px solid #e0e6ed;
            vertical-align: middle;
            font-size: 10px;
        }
        .main-table tbody tr:nth-child(even) td { background: #f9fafb; }

        .text-center { text-align: center; }

        .foto-contacto {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #ccc;
            display: block;
        }
        .foto-vacia {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f0f3f7;
            color: #b0bec5;
            font-size: 12px;
            text-align: center;
            line-height: 34px;
        }

        .icono-si  { color: #1a7a3c; font-weight: bold; }
        .icono-no  { color: #b0bec5; }

        /* ── Sin contactos ── */
        .sin-datos {
            text-align: center;
            color: #b0bec5;
            font-style: italic;
            padding: 8px;
            font-size: 10px;
            border: 1px solid #e0e6ed;
            border-top: none;
        }

        /* ── Pie ── */
        .pie {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
            font-size: 9px;
            color: #bbb;
        }
    </style>
</head>
<body>

    {{-- ── Encabezado institucional ── --}}
    <table class="header">
        <tr>
            <td style="width:18%;">
                @if (file_exists(public_path('imgs_escuela/reportes/' . $logoRuta)))
                    <img src="{{ public_path('imgs_escuela/reportes/' . $logoRuta) }}" class="school-logo" alt="Logo">
                @else
                    <div style="width:100px;height:60px;background:#e0e0e0;text-align:center;line-height:60px;color:#888;font-size:9px;">LOGO</div>
                @endif
            </td>
            <td style="width:50%;padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Directorio de Contactos Familiares</div>
            </td>
            <td class="report-title" style="width:32%;">
                <div class="report-title-main">Contactos Familiares</div>
                <div class="report-title-sub">
                    Ciclo: {{ $grupo->ciclo->nombre }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Datos del grupo ── --}}
    <table class="info-box">
        <tr>
            <td style="width:22%;">
                <span class="info-lbl">Nivel</span>
                <span class="info-val">{{ $grupo->grado->nivel->nombre }}</span>
            </td>
            <td style="width:22%;">
                <span class="info-lbl">Grado y Grupo</span>
                <div style="display: flex; align-items: center; white-space: nowrap;">
                    <span class="info-badge">{{ $grupo->grado->numero }}° {{ $grupo->nombre }}</span>

                    @if (!empty($grupo->icono) && \Illuminate\Support\Facades\Storage::disk('public')->exists($grupo->icono))
                        <img src="{{ public_path('storage/' . $grupo->icono) }}" alt="Icono"
                            style="width:28px; height:28px; border-radius:50%; border:1px solid #ccc; margin-left:6px; flex-shrink: 0;">
                    @endif
                </div>
            </td>
            <td style="width:30%;">
                <span class="info-lbl">Docente</span>
                <span class="info-val">{{ $grupo->docente ? $grupo->docente->nombre_completo : 'Sin asignar' }}</span>
            </td>
            <td style="width:14%;">
                <span class="info-lbl">Ciclo escolar</span>
                <span class="info-val">{{ $grupo->ciclo->nombre }}</span>
            </td>
            <td style="width:12%;text-align:center;">
                <span class="info-lbl">Total alumnos</span>
                <span class="info-count">{{ $totalAlumnos }}</span>
            </td>
        </tr>
    </table>

    @foreach ($inscripciones as $ins)
        @php $alumno = $ins->alumno; @endphp

        <div class="alumno-title">
            {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
            <span class="matricula">Matrícula: {{ $alumno->matricula ?? '—' }}</span>
        </div>

        @if ($alumno->contactos->isEmpty())
            <div class="sin-datos">Sin contactos familiares registrados.</div>
        @else
            <table class="main-table">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">Foto</th>
                        <th>Nombre del contacto</th>
                        <th style="width:100px;">Parentesco</th>
                        <th style="width:90px;">Teléfono</th>
                        <th>Correo electrónico</th>
                        <th style="width:65px;text-align:center;">Acceso portal</th>
                        <th style="width:75px;text-align:center;">Autorizado recoger</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($alumno->contactos as $contacto)
                        @php
                            $rutaFoto = $contacto->foto_url ? public_path('storage/' . $contacto->foto_url) : null;
                            $telefono = $contacto->telefono_celular ?? $contacto->telefono_trabajo ?? $contacto->telefono_2;
                        @endphp
                        <tr>
                            <td class="text-center">
                                @if ($rutaFoto && file_exists($rutaFoto))
                                    <img src="{{ $rutaFoto }}" class="foto-contacto">
                                @else
                                    <div class="foto-vacia">—</div>
                                @endif
                            </td>
                            <td style="font-weight:bold;">
                                {{ $contacto->nombre }} {{ $contacto->ap_paterno }} {{ $contacto->ap_materno }}
                            </td>
                            <td style="text-transform:capitalize;">{{ $contacto->pivot->parentesco ?? '—' }}</td>
                            <td>{{ $telefono ?? '—' }}</td>
                            <td>{{ $contacto->email ?? '—' }}</td>
                            <td class="text-center">
                                @if ($contacto->tiene_acceso_portal)
                                    <span class="icono-si">Sí</span>
                                @else
                                    <span class="icono-no">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($contacto->pivot->autorizado_recoger)
                                    <span class="icono-si">Sí</span>
                                @else
                                    <span class="icono-no">No</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    {{-- ── Pie de página ── --}}
    <div class="pie">
        {{ $nombreEscuela }} — Directorio de contactos familiares. Documento de uso interno.
    </div>

</body>
</html>
