<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    @php
        $escuelaInfo   = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta ?? 'logo-escuela.png';

        $ficha        = $alumno->fichaMedica;
        $condiciones  = $alumno->condicionesMedicas;
        $medicamentos = $alumno->medicamentosAutorizados;
        $inscripcion  = $alumno->inscripciones
                            ->where('activo', true)
                            ->where('ciclo_id', $cicloActualId)
                            ->first()
                        ?? $alumno->inscripciones->where('activo', true)->sortByDesc('id')->first();
        $hermanos     = $alumno->familia?->alumnos->where('id', '!=', $alumno->id) ?? collect();
        $domicilio    = collect([
                            $alumno->calle,
                            $alumno->colonia,
                            $alumno->ciudad,
                            $alumno->estado_residencia,
                        ])->filter()->implode(', ');
        $contactosPares = $alumno->contactos->chunk(2);
    @endphp

    <title>Ficha de Alumno — {{ $alumno->nombre_completo }}</title>

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
        .school-logo { width: 100px; height: auto; display: block; }
        .school-name {
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
        .report-title { text-align: right; }
        .report-title-main {
            color: #1e4d7b;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title-sub { color: #666; font-size: 10px; margin-top: 3px; }

        /* ── Caja resumen del alumno ── */
        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #dde4eb;
        }
        .info-box td {
            padding: 7px 14px;
            border-right: 1px solid #dde4eb;
            vertical-align: middle;
        }
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
        .info-val {
            font-size: 12px;
            font-weight: bold;
            color: #1a2634;
        }
        .info-badge {
            display: inline-block;
            background: #e8f0fb;
            color: #2e6da4;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #b3d0f0;
        }
        .info-badge-green {
            display: inline-block;
            background: #e6f7ee;
            color: #00875a;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #a3d9b8;
        }

        /* ── Título de sección ── */
        .section-title {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .section-title td {
            background: #1e4d7b;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 8px;
            letter-spacing: .06em;
        }

        /* ── Tablas de datos ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table th {
            background: #f2f5f9;
            color: #1e4d7b;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 8px;
            border: 1px solid #dde4eb;
            text-align: left;
            white-space: nowrap;
            width: 1%;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #dde4eb;
            font-size: 10.5px;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) td,
        .data-table tbody tr:nth-child(even) th {
            background: #f9fafb;
        }

        /* ── Tabla de condiciones y medicamentos ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .main-table thead th {
            background: #1e4d7b;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 8px;
            border: 1px solid #1a4570;
            letter-spacing: .04em;
        }
        .main-table tbody td {
            padding: 5px 8px;
            border: 1px solid #e0e6ed;
            vertical-align: top;
            font-size: 10.5px;
        }
        .main-table tbody tr:nth-child(even) td { background: #f9fafb; }

        /* ── Foto ── */
        .foto-cell {
            text-align: center;
            vertical-align: middle;
            padding: 6px;
            border: 1px solid #dde4eb;
            background: #f9fafb;
            width: 90px;
        }
        .foto-img {
            width: 80px;
            height: 96px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
        .foto-ph {
            width: 80px;
            height: 96px;
            border: 1.5px dashed #c5d0dc;
            background: #eef1f5;
        }

        /* ── Badges de riesgo ── */
        .riesgo-leve     { color: #00875a; font-weight: bold; }
        .riesgo-moderado { color: #7d6000; font-weight: bold; }
        .riesgo-grave    { color: #b45309; font-weight: bold; }
        .riesgo-critico  { color: #b91c1c; font-weight: bold; }
        .accion-urgente  { color: #b91c1c; font-size: 9.5px; }

        /* ── Columnas lado a lado ── */
        .two-col { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .col-l   { width: 55%; vertical-align: top; padding-right: 5px; }
        .col-r   { width: 45%; vertical-align: top; padding-left: 5px; }

        /* ── Área de firmas ── */
        .firma-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        .firma-table td {
            width: 33%;
            text-align: center;
            font-size: 10px;
            color: #555;
            padding-top: 12px;
            border-top: 1px solid #999;
        }
        .firma-lbl {
            font-size: 9px;
            color: #8a9ab0;
            text-transform: uppercase;
            letter-spacing: .05em;
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

        .text-center { text-align: center; }
        .text-muted  { color: #8a9ab0; }
        .no-data     { color: #8a9ab0; font-style: italic; font-size: 10px; padding: 8px; }
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
                    <div style="width:80px;height:80px;background:#e0e0e0;text-align:center;line-height:80px;color:#888;font-size:9px;">LOGO</div>
                @endif
            </td>
            <td style="width:50%; padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Ficha de Alumno</div>
            </td>
            <td class="report-title" style="width:32%;">
                <div class="report-title-main">Ficha de Alumno</div>
                <div class="report-title-sub">
                    Matrícula: {{ $alumno->matricula }}<br>
                    Folio: {{ str_pad($alumno->id, 5, '0', STR_PAD_LEFT) }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ── Resumen del alumno ── --}}
    <table class="info-box">
        <tr>
            <td style="width:28%;">
                <span class="info-lbl">Nombre completo</span>
                <span class="info-val">{{ $alumno->nombre_completo }}</span>
            </td>
            <td style="width:16%; text-align:center;">
                <span class="info-lbl">Estado</span>
                @if ($alumno->estado === 'activo')
                    <span class="info-badge-green">{{ strtoupper($alumno->estado) }}</span>
                @else
                    <span class="info-badge">{{ strtoupper($alumno->estado) }}</span>
                @endif
            </td>
            <td style="width:20%;">
                <span class="info-lbl">Nivel / Grado / Grupo</span>
                <span class="info-badge">
                    {{ $inscripcion?->grupo?->grado?->nivel?->nombre ?? '—' }}
                    {{ $inscripcion?->grupo?->grado?->numero ? $inscripcion->grupo->grado->numero.'°' : '' }}
                    {{ $inscripcion?->grupo?->nombre ?? '' }}
                </span>
            </td>
            <td style="width:18%;">
                <span class="info-lbl">Ciclo escolar</span>
                <span class="info-val">{{ $inscripcion?->ciclo?->nombre ?? '—' }}</span>
            </td>
            <td style="width:18%;">
                <span class="info-lbl">Familia</span>
                <span class="info-val">{{ $alumno->familia?->apellido_familia ?? '—' }}</span>
            </td>
        </tr>
    </table>

    {{-- ══ FILA SUPERIOR: Datos personales (izq) + Inscripción (der) ══ --}}
    <table class="two-col">
        <tr>
            {{-- ── 1. Datos personales ── --}}
            <td class="col-l">
                <table class="section-title"><tr><td>1. Datos Personales</td></tr></table>
                <table class="data-table">
                    <tr>
                        <td class="foto-cell" rowspan="7">
                            @if ($alumno->foto_url && file_exists(public_path('storage/'.$alumno->foto_url)))
                                <img src="{{ public_path('storage/'.$alumno->foto_url) }}" class="foto-img">
                            @else
                                <div class="foto-ph"></div>
                                <div style="font-size:8px;color:#8a9ab0;text-align:center;margin-top:3px;">SIN FOTO</div>
                            @endif
                        </td>
                        <th>Nombre(s)</th>
                        <td>{{ $alumno->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Ap. Paterno</th>
                        <td>{{ $alumno->ap_paterno }}</td>
                    </tr>
                    <tr>
                        <th>Ap. Materno</th>
                        <td>{{ $alumno->ap_materno ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Nacimiento</th>
                        <td>
                            {{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                            @if ($alumno->fecha_nacimiento)
                                <span class="text-muted">({{ $alumno->edad }} años)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Género</th>
                        <td>{{ ucfirst($alumno->genero ?? '—') }}</td>
                    </tr>
                    <tr>
                        <th>CURP</th>
                        <td style="font-size:9px;letter-spacing:.5px;">{{ $alumno->curp ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Religión</th>
                        <td>{{ $alumno->religion ?? '—' }}</td>
                    </tr>
                    @if ($domicilio || $alumno->codigo_postal)
                    <tr>
                        <th>Domicilio</th>
                        <td colspan="2">
                            {{ $domicilio ?: '—' }}
                            {{ $alumno->codigo_postal ? ' C.P. '.$alumno->codigo_postal : '' }}
                        </td>
                    </tr>
                    @endif
                    @if ($alumno->observaciones)
                    <tr>
                        <th>Observaciones</th>
                        <td colspan="2" style="font-size:9.5px;">{{ $alumno->observaciones }}</td>
                    </tr>
                    @endif
                </table>
            </td>

            {{-- ── 2. Inscripción + Familia ── --}}
            <td class="col-r">
                <table class="section-title"><tr><td>2. Inscripción Actual</td></tr></table>
                @if ($inscripcion)
                    <table class="data-table">
                        <tr>
                            <th>Ciclo</th>
                            <td><strong>{{ $inscripcion->ciclo->nombre ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Nivel</th>
                            <td>{{ $inscripcion->grupo?->grado?->nivel?->nombre ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Grado</th>
                            <td>{{ $inscripcion->grupo?->grado?->numero ? $inscripcion->grupo->grado->numero.'°' : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Grupo</th>
                            <td><strong>{{ $inscripcion->grupo?->nombre ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tipo</th>
                            <td>{{ ucfirst($inscripcion->tipo->value ?? $inscripcion->tipo ?? '—') }}</td>
                        </tr>
                        <tr>
                            <th>Fecha insc.</th>
                            <td>{{ $alumno->fecha_inscripcion?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                    </table>
                @else
                    <p class="no-data">Sin inscripción registrada en el ciclo actual.</p>
                @endif

                <table class="section-title"><tr><td>3. Familia</td></tr></table>
                <table class="data-table">
                    <tr>
                        <th>Familia</th>
                        <td>{{ $alumno->familia?->apellido_familia ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Hermanos</th>
                        <td>
                            @if ($hermanos->count())
                                {{ $hermanos->map(fn($h) => $h->nombre_completo)->implode(' · ') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ 4. CONTACTOS FAMILIARES ══ --}}
    <table class="section-title"><tr><td>4. Contactos Familiares</td></tr></table>
    @if ($alumno->contactos->count())
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:22%;">Nombre</th>
                    <th style="width:12%;">Parentesco</th>
                    <th style="width:12%;">Tipo</th>
                    <th style="width:14%;">Celular</th>
                    <th style="width:18%;">Correo</th>
                    <th style="width:13%;">Permisos</th>
                    <th style="width:9%; text-align:center;">Orden</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($alumno->contactos as $contacto)
                    <tr>
                        <td><strong>{{ $contacto->nombre_completo }}</strong></td>
                        <td>{{ ucfirst($contacto->pivot->parentesco ?? '—') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $contacto->pivot->tipo ?? '—')) }}</td>
                        <td>{{ $contacto->telefono_celular ?? '—' }}</td>
                        <td style="font-size:9px;">{{ $contacto->email ?? '—' }}</td>
                        <td style="font-size:9px;">
                            @if ($contacto->pivot->autorizado_recoger) Recoger<br> @endif
                            @if ($contacto->pivot->es_responsable_pago) Pagos @endif
                            @if (!$contacto->pivot->autorizado_recoger && !$contacto->pivot->es_responsable_pago) — @endif
                        </td>
                        <td style="text-align:center;">{{ $contacto->pivot->orden ?? '—' }}</td>
                    </tr>
                    @if ($contacto->profesion || $contacto->lugar_trabajo || $contacto->telefono_2)
                    <tr>
                        <td colspan="7" style="font-size:9px;color:#5a6a7e;padding:3px 8px 5px 16px;">
                            @if ($contacto->profesion) Profesión: {{ $contacto->profesion }} @endif
                            @if ($contacto->lugar_trabajo) &nbsp;·&nbsp; Trabajo: {{ $contacto->lugar_trabajo }}{{ $contacto->puesto ? ' ('.$contacto->puesto.')' : '' }} @endif
                            @if ($contacto->telefono_2) &nbsp;·&nbsp; Tel. 2: {{ $contacto->telefono_2 }} @endif
                            @if ($contacto->nivel_estudios) &nbsp;·&nbsp; Estudios: {{ $contacto->nivel_estudios }} @endif
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">Sin contactos familiares registrados.</p>
    @endif

    {{-- ══ FILA INFERIOR: Ficha médica (izq) + Condiciones (der) ══ --}}
    <table class="two-col">
        <tr>
            {{-- ── 5. Ficha médica ── --}}
            <td class="col-l">
                <table class="section-title"><tr><td>5. Ficha Médica</td></tr></table>
                @if ($ficha)
                    <table class="data-table">
                        <tr>
                            <th>Tipo de sangre</th>
                            <td><strong style="font-size:14px;color:#b91c1c;">{{ $ficha->tipo_sangre ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <th>Peso / Talla</th>
                            <td>
                                {{ $ficha->peso_kg ? $ficha->peso_kg.' kg' : '—' }}
                                @if ($ficha->talla_cm) &nbsp;·&nbsp; {{ $ficha->talla_cm }} cm @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Médico</th>
                            <td>
                                {{ $ficha->medico_nombre ?? '—' }}
                                @if ($ficha->medico_telefono)
                                    <span class="text-muted">&nbsp;{{ $ficha->medico_telefono }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Hospital</th>
                            <td>{{ $ficha->hospital_preferente ?? '—' }}</td>
                        </tr>
                        @if ($ficha->discapacidad)
                        <tr>
                            <th>Discapacidad</th>
                            <td>{{ $ficha->discapacidad }}</td>
                        </tr>
                        @endif
                        @if ($ficha->observaciones_generales)
                        <tr>
                            <th>Observaciones</th>
                            <td style="font-size:9.5px;">{{ $ficha->observaciones_generales }}</td>
                        </tr>
                        @endif
                    </table>
                @else
                    <p class="no-data">Sin ficha médica registrada.</p>
                @endif
            </td>

            {{-- ── 6. Condiciones médicas ── --}}
            <td class="col-r">
                <table class="section-title"><tr><td>6. Condiciones / Padecimientos</td></tr></table>
                @if ($condiciones->count())
                    <table class="main-table">
                        <thead>
                            <tr>
                                <th style="width:35%;">Condición</th>
                                <th style="width:20%;">Tipo</th>
                                <th style="width:15%; text-align:center;">Riesgo</th>
                                <th>Descripción / Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($condiciones as $cond)
                                <tr>
                                    <td><strong>{{ $cond->nombre }}</strong></td>
                                    <td style="font-size:9px;">{{ $cond->tipoEtiqueta() }}</td>
                                    <td style="text-align:center;">
                                        <span class="riesgo-{{ $cond->nivel_riesgo }}">
                                            {{ ucfirst($cond->nivel_riesgo) }}
                                        </span>
                                    </td>
                                    <td style="font-size:9.5px;">
                                        {{ $cond->descripcion ?? '' }}
                                        @if ($cond->requiere_accion && $cond->accion_requerida)
                                            <br><span class="accion-urgente">⚠ {{ $cond->accion_requerida }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="no-data">Sin condiciones médicas registradas.</p>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ 7. MEDICAMENTOS AUTORIZADOS ══ --}}
    @if ($medicamentos->count())
        <table class="section-title"><tr><td>7. Medicamentos Autorizados en Escuela</td></tr></table>
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width:20%;">Medicamento</th>
                    <th style="width:13%;">Dosis</th>
                    <th style="width:16%;">Frecuencia</th>
                    <th style="width:10%;">Horario</th>
                    <th style="width:24%;">Instrucciones</th>
                    <th style="width:17%;">Autoriza</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($medicamentos as $med)
                    <tr>
                        <td>
                            <strong>{{ $med->nombre_medicamento }}</strong>
                            @if ($med->requiere_refrigeracion)
                                <br><span style="font-size:8px;color:#2e6da4;">❄ Refrigeración</span>
                            @endif
                            @if ($med->vigencia_fin)
                                <br><span style="font-size:8px;" class="text-muted">Vig: {{ $med->vigencia_fin->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td>{{ $med->dosis }}</td>
                        <td>{{ $med->frecuencia }}</td>
                        <td>{{ $med->horario ?? '—' }}</td>
                        <td style="font-size:9.5px;">{{ $med->instrucciones ?? '—' }}</td>
                        <td style="font-size:9.5px;">{{ $med->contactoAutoriza?->nombre_completo ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══ 8. DATOS DE FACTURACIÓN ══ --}}
    @php
        $regimenesFiscales = [
            '601' => 'General de Ley PM',
            '603' => 'Personas Morales con Fines no Lucrativos',
            '605' => 'Sueldos y Salarios e Ingresos Asimilados',
            '606' => 'Arrendamiento',
            '608' => 'Demás ingresos',
            '609' => 'Consolidación',
            '610' => 'Residentes en el Extranjero',
            '611' => 'Ingresos por Dividendos',
            '612' => 'Personas Físicas con Act. Empresariales y Profesionales',
            '614' => 'Ingresos por intereses',
            '615' => 'Régimen de los ingresos por obtención de premios',
            '616' => 'Sin obligaciones fiscales',
            '620' => 'Sociedades Cooperativas de Producción',
            '621' => 'Incorporación Fiscal',
            '622' => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => 'Opcional para Grupos de Sociedades',
            '624' => 'Coordinados',
            '625' => 'Régimen de las Act. Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626' => 'RESICO - Simplificado de Confianza',
            '628' => 'Hidrocarburos',
            '629' => 'De los Regímenes Fiscales Preferentes y de las Empresas Multinacionales',
            '630' => 'Enajenación de acciones en bolsa de valores',
        ];
        $usosCfdi = [
            'G01' => 'Adquisición de mercancias',
            'G02' => 'Devoluciones, descuentos o bonificaciones',
            'G03' => 'Gastos en general',
            'I01' => 'Construcciones',
            'I02' => 'Mobilario y equipo de oficina por inversiones',
            'I03' => 'Equipo de transporte',
            'I04' => 'Equipo de computo y accesorios',
            'I05' => 'Dados, troqueles, moldes, matrices y herramental',
            'I06' => 'Comunicaciones telefónicas',
            'I07' => 'Comunicaciones satelitales',
            'I08' => 'Otra maquinaria y equipo',
            'D01' => 'Honorarios médicos, dentales y gastos hospitalarios',
            'D02' => 'Gastos médicos por incapacidad o discapacidad',
            'D03' => 'Gastos funerales',
            'D04' => 'Donativos',
            'D05' => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)',
            'D06' => 'Aportaciones voluntarias al SAR',
            'D07' => 'Primas por seguros de gastos médicos',
            'D08' => 'Gastos de transportación escolar obligatoria',
            'D09' => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones',
            'D10' => 'Pagos por servicios educativos (colegiaturas)',
            'S01' => 'Sin efectos fiscales',
            'CP01' => 'Pagos',
            'CN01' => 'Nómina',
        ];

        $contactosConRfc = $alumno->contactos->filter(
            fn ($c) => $c->razonesSociales->isNotEmpty()
        );
    @endphp

    @if ($contactosConRfc->isNotEmpty())
        <table class="section-title"><tr><td>8. Datos de Facturación</td></tr></table>
        @foreach ($contactosConRfc as $contacto)
            <table class="main-table" style="margin-bottom:5px;">
                <thead>
                    <tr>
                        <th colspan="5" style="background:#e8f0f8;color:#1e4d7b;font-size:10px;">
                            {{ $contacto->nombre_completo }}
                            @if ($contacto->pivot->parentesco)
                                · <span style="font-weight:normal;">{{ ucfirst($contacto->pivot->parentesco) }}</span>
                            @endif
                        </th>
                    </tr>
                    <tr>
                        <th style="width:16%;">RFC</th>
                        <th style="width:26%;">Razón Social</th>
                        <th style="width:26%;">Régimen Fiscal</th>
                        <th style="width:10%; text-align:center;">C.P. Fiscal</th>
                        <th style="width:22%;">Uso CFDI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contacto->razonesSociales->where('activo', true) as $rs)
                        <tr>
                            <td>
                                <strong>{{ $rs->rfc }}</strong>
                                @if ($rs->es_principal)
                                    <span style="font-size:8px;color:#2e6da4;"> ★ Principal</span>
                                @endif
                            </td>
                            <td>{{ $rs->razon_social }}</td>
                            <td style="font-size:9px;">
                                {{ $regimenesFiscales[$rs->regimen_fiscal] ?? $rs->regimen_fiscal }}
                                <span style="color:#888;">({{ $rs->regimen_fiscal }})</span>
                            </td>
                            <td style="text-align:center;">{{ $rs->domicilio_fiscal }}</td>
                            <td style="font-size:9px;">
                                {{ $usosCfdi[$rs->uso_cfdi_default] ?? $rs->uso_cfdi_default }}
                                <span style="color:#888;">({{ $rs->uso_cfdi_default }})</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif

    {{-- ── Firmas ── --}}
    <table class="firma-table">
        <tr>
            <td><span class="firma-lbl">Padre / Tutor</span></td>
            <td><span class="firma-lbl">Director(a)</span></td>
            <td><span class="firma-lbl">Sello institucional</span></td>
        </tr>
    </table>

    {{-- ── Pie de página ── --}}
    <div class="pie">
        Documento interno &mdash; {{ $nombreEscuela }} &mdash; Expediente: {{ $alumno->matricula }} &mdash; Este documento no tiene validez oficial fuera de la institución.
    </div>

</body>
</html>
