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

        // Sólo alumnos que tienen al menos una condición médica activa
        $conCondicion = $inscripciones->filter(fn($i) => $i->alumno->condicionesMedicas->isNotEmpty());
    @endphp

    <title>Expediente Médico — {{ $grupo->grado->nivel->nombre }} {{ $grupo->grado->numero }}° {{ $grupo->nombre }}</title>

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
            border-bottom: 3px solid #c0392b;
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
            color: #c0392b;
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
            background: #fdecea;
            color: #b91c1c;
            font-size: 14px;
            font-weight: bold;
            padding: 1px 10px;
            border-radius: 4px;
            border: 1px solid #fca5a5;
        }

        /* ── Título de sección ── */
        .section-title {
            background: #1e4d7b;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 5px 10px;
            margin-top: 14px;
            margin-bottom: 0;
        }
        .section-title-red {
            background: #c0392b;
        }

        /* ── Tabla principal ── */
        .main-table              { width: 100%; border-collapse: collapse; margin-bottom: 0; }
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

        .num-td { text-align: center; width: 20px; color: #94a3b8; font-size: 9px; }
        .text-center { text-align: center; }

        /* ── Badges de riesgo ── */
        .badge-leve     { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; padding:1px 6px; border-radius:3px; font-size:9px; font-weight:bold; }
        .badge-moderado { background:#fff8e1; color:#b45309; border:1px solid #fde68a; padding:1px 6px; border-radius:3px; font-size:9px; font-weight:bold; }
        .badge-grave    { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; padding:1px 6px; border-radius:3px; font-size:9px; font-weight:bold; }
        .badge-critico  { background:#fdecea; color:#b91c1c; border:1px solid #fca5a5; padding:1px 6px; border-radius:3px; font-size:9px; font-weight:bold; }

        /* ── Acción requerida ── */
        .accion-box {
            background: #fdecea;
            border-left: 3px solid #c0392b;
            padding: 3px 7px;
            margin-top: 3px;
            font-size: 9px;
            color: #7f1d1d;
        }
        .accion-lbl { font-weight: bold; font-size: 8px; text-transform: uppercase; color: #b91c1c; display: block; margin-bottom: 1px; }

        /* ── Sin datos ── */
        .sin-datos {
            text-align: center;
            color: #b0bec5;
            font-style: italic;
            padding: 12px;
            font-size: 10px;
        }

        /* ── Firmas ── */
        .firma-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .firma-table td {
            width: 33%;
            text-align: center;
            font-size: 10px;
            color: #555;
            padding-top: 14px;
            border-top: 1px solid #999;
        }
        .firma-table .firma-lbl { font-size: 9px; color: #8a9ab0; text-transform: uppercase; letter-spacing: .05em; }

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

        /* ── Alerta confidencialidad ── */
        .confidencial {
            background: #fff8e1;
            border: 1px solid #fde68a;
            color: #856404;
            font-size: 9px;
            padding: 4px 10px;
            margin-bottom: 10px;
            text-align: center;
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
                <div class="school-sub">Expediente Médico y Padecimientos</div>
            </td>
            <td class="report-title" style="width:32%;">
                <div class="report-title-main">Expediente Médico</div>
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
                <!-- Usamos un contenedor para mantenerlos juntos -->
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

    {{-- ── Aviso de confidencialidad ── --}}
    <div class="confidencial">
        &#9888; DOCUMENTO CONFIDENCIAL — Uso exclusivo del personal autorizado de la institución. Protegido bajo legislación de datos personales.
    </div>

    {{-- ══════════════════════════════════════════════════════
         SECCIÓN 1 — EXPEDIENTE MÉDICO GENERAL
    ══════════════════════════════════════════════════════ --}}
    <div class="section-title">1. Expediente médico general</div>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width:20px;">#</th>
                <th style="width:70px;">Matrícula</th>
                <th>Nombre del alumno</th>
                <th style="width:45px;text-align:center;">Tipo sangre</th>
                <th style="width:40px;text-align:center;">Peso (kg)</th>
                <th style="width:45px;text-align:center;">Talla (cm)</th>
                <th>Médico de cabecera</th>
                <th>Hospital preferente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($inscripciones as $index => $ins)
                @php $fm = $ins->alumno->fichaMedica; @endphp
                <tr>
                    <td class="num-td">{{ $index + 1 }}</td>
                    <td style="font-family:monospace;font-size:9px;color:#4a5568;text-align:center;">
                        {{ $ins->alumno->matricula ?? '—' }}
                    </td>
                    <td style="font-weight:bold;text-transform:uppercase;font-size:10px;">
                        {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }}, {{ $ins->alumno->nombre }}
                    </td>
                    <td style="text-align:center;font-weight:bold;color:#c0392b;">
                        {{ $fm?->tipo_sangre ?? '—' }}
                    </td>
                    <td style="text-align:center;">
                        {{ $fm?->peso_kg ? number_format($fm->peso_kg, 1) : '—' }}
                    </td>
                    <td style="text-align:center;">
                        {{ $fm?->talla_cm ? number_format($fm->talla_cm, 1) : '—' }}
                    </td>
                    <td style="font-size:10px;">
                        @if ($fm?->medico_nombre)
                            {{ $fm->medico_nombre }}
                            @if ($fm->medico_telefono)
                                <br><span style="color:#8a9ab0;font-size:9px;">{{ $fm->medico_telefono }}</span>
                            @endif
                        @else
                            <span style="color:#b0bec5;">—</span>
                        @endif
                    </td>
                    <td style="font-size:10px;">{{ $fm?->hospital_preferente ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════
         SECCIÓN 2 — PADECIMIENTOS, ALERGIAS Y CONDICIONES
    ══════════════════════════════════════════════════════ --}}
    <div class="section-title section-title-red" style="margin-top:16px;">
        2. Padecimientos, alergias y condiciones médicas
    </div>

    @if ($conCondicion->isEmpty())
        <div class="sin-datos">
            Ningún alumno del grupo tiene condiciones médicas registradas en el sistema.
        </div>
    @else
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:20px;">#</th>
                    <th style="width:130px;">Alumno</th>
                    <th style="width:80px;">Tipo</th>
                    <th>Condición / Diagnóstico</th>
                    <th style="width:55px;text-align:center;">Nivel riesgo</th>
                    <th>Acción requerida por el personal</th>
                </tr>
            </thead>
            <tbody>
                @php $contador = 0; @endphp
                @foreach ($conCondicion as $ins)
                    @foreach ($ins->alumno->condicionesMedicas as $condicion)
                        @php $contador++; @endphp
                        <tr>
                            <td class="num-td">{{ $contador }}</td>
                            <td style="font-weight:bold;font-size:10px;text-transform:uppercase;">
                                {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }}<br>
                                <span style="font-weight:normal;text-transform:none;font-size:9px;color:#555;">{{ $ins->alumno->nombre }}</span>
                            </td>
                            <td style="font-size:9px;color:#5a6a7a;">
                                {{ $condicion->tipoEtiqueta() }}
                            </td>
                            <td style="font-size:10px;">
                                <strong>{{ $condicion->nombre }}</strong>
                                @if ($condicion->descripcion)
                                    <br><span style="font-size:9px;color:#6b7a8d;">{{ $condicion->descripcion }}</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @switch($condicion->nivel_riesgo)
                                    @case('leve')     <span class="badge-leve">Leve</span>     @break
                                    @case('moderado') <span class="badge-moderado">Moderado</span> @break
                                    @case('grave')    <span class="badge-grave">Grave</span>    @break
                                    @case('critico')  <span class="badge-critico">Crítico</span> @break
                                @endswitch
                            </td>
                            <td style="font-size:10px;">
                                @if ($condicion->requiere_accion && $condicion->accion_requerida)
                                    <div class="accion-box">
                                        <span class="accion-lbl">&#9889; Acción inmediata:</span>
                                        {{ $condicion->accion_requerida }}
                                    </div>
                                @else
                                    <span style="color:#b0bec5;font-size:9px;">Sin protocolo de emergencia registrado</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══════════════════════════════════════════════════════
         SECCIÓN 3 — MEDICAMENTOS AUTORIZADOS
    ══════════════════════════════════════════════════════ --}}
    @php
        $conMedicamentos = $inscripciones->filter(fn($i) => $i->alumno->medicamentosAutorizados->isNotEmpty());
    @endphp

    @if ($conMedicamentos->isNotEmpty())
        <div class="section-title" style="margin-top:16px;background:#6b21a8;">
            3. Medicamentos autorizados para administrar en escuela
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:20px;">#</th>
                    <th style="width:115px;">Alumno</th>
                    <th>Medicamento</th>
                    <th style="width:80px;">Dosis</th>
                    <th style="width:80px;">Frecuencia</th>
                    <th style="width:70px;">Horario</th>
                    <th style="width:110px;">Autorizado por</th>
                    <th style="width:55px;text-align:center;">Refrig.</th>
                </tr>
            </thead>
            <tbody>
                @php $contMed = 0; @endphp
                @foreach ($conMedicamentos as $ins)
                    @foreach ($ins->alumno->medicamentosAutorizados as $med)
                        @php $contMed++; @endphp
                        <tr>
                            <td class="num-td">{{ $contMed }}</td>
                            <td style="font-weight:bold;font-size:10px;text-transform:uppercase;">
                                {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }}<br>
                                <span style="font-weight:normal;text-transform:none;font-size:9px;color:#555;">{{ $ins->alumno->nombre }}</span>
                            </td>
                            <td style="font-size:10px;">
                                <strong>{{ $med->nombre_medicamento }}</strong>
                                @if ($med->instrucciones)
                                    <br><span style="font-size:9px;color:#6b7a8d;">{{ $med->instrucciones }}</span>
                                @endif
                                @if ($med->vigencia_fin)
                                    <br><span style="font-size:8px;color:#b0bec5;">Vigente hasta: {{ $med->vigencia_fin->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td style="font-size:10px;">{{ $med->dosis }}</td>
                            <td style="font-size:10px;">{{ $med->frecuencia }}</td>
                            <td style="font-size:10px;">{{ $med->horario ?? '—' }}</td>
                            <td style="font-size:10px;">
                                {{ $med->contactoAutoriza?->nombre }}
                                {{ $med->contactoAutoriza?->ap_paterno }}
                            </td>
                            <td style="text-align:center;font-size:10px;">
                                @if ($med->requiere_refrigeracion)
                                    <span style="color:#1565c0;font-weight:bold;">Sí</span>
                                @else
                                    <span style="color:#b0bec5;">No</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── Firmas ── --}}
    <table class="firma-table">
        <tr>
            <td><span class="firma-lbl">Elaboró</span></td>
            <td><span class="firma-lbl">Enfermería / Responsable médico</span></td>
            <td><span class="firma-lbl">Vo. Bo. Dirección</span></td>
        </tr>
    </table>

    {{-- ── Pie de página ── --}}
    <div class="pie">
        CONFIDENCIAL — {{ $nombreEscuela }} — Documento de uso interno exclusivo del personal autorizado. No distribuir.
    </div>

</body>
</html>
