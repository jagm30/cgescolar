@extends('layouts.master')
@section('page_title', 'Diseñador: ' . $diseno->nombre)

@section('content')
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&family=Oswald:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    @if (request()->has('preview') || isset($alumnos))
        <style>
            /* ANIQUILADOR DE ADMINLTE: Esconde todo el cascarón en modo impresión/preview */
            .main-header,
            .main-sidebar,
            .main-footer {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
                background-color: white !important;
            }

            body {
                background-color: white !important;
            }
        </style>
    @endif

    <style>
        /* --- ESTILOS BASE Y ANIMACIONES --- */
        @keyframes pulseWarning {
            0% {
                box-shadow: 0 0 0 0 rgba(243, 156, 18, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(243, 156, 18, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(243, 156, 18, 0);
            }
        }

        .pulse-warning {
            animation: pulseWarning 2s infinite !important;
            transition: all 0.3s ease;
        }

        .btn-group .btn.active {
            background-color: #3c8dbc !important;
            color: white !important;
            border-color: #367fa9;
            box-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.2);
        }

        .content-span {
            pointer-events: none;
            display: block;
            width: 100%;
            height: 100%;
        }

        #canvas-container {
            background: #3c3f41;
            padding: 50px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 40px;
            min-height: 850px;
            position: relative;
        }

        /* LIENZO BASE */
        .credencial-canvas-instance,
        #credencial-canvas,
        #credencial-canvas-reverso {
            background-color: white !important;
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            width: {{ $diseno->orientacion == 'vertical' ? '320px' : '500px' }};
            height: {{ $diseno->orientacion == 'vertical' ? '500px' : '320px' }};
            overflow: hidden;
            flex-shrink: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* BLINDAJE CONTRA CHROME */
        .fondo-credencial {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 1 !important;
            object-fit: cover !important;
            pointer-events: none !important;
        }

        /* ELEMENTOS DRAGGABLE - CORREGIDO PARA QUE NO DESAPAREZCAN NI DEJEN HUECOS */
        .draggable-item {
            position: absolute;
            top: 0 !important;
            left: 0 !important;
            cursor: move;
            touch-action: none;
            user-select: none;
            pointer-events: auto;
            padding: 0px 2px;
            /* <--- AJUSTE FINO: Adiós espacios enormes */
            border: 1px dashed #ccc;
            white-space: normal;
            word-wrap: break-word;
            display: inline-block;
            line-height: 1.2;
            min-width: 40px;
            z-index: 10;
        }

        .draggable-item.overflow-warning {
            border: 2px solid #ff4757 !important;
            background: rgba(255, 71, 87, 0.15) !important;
        }

        .draggable-item.selected {
            border: 1px solid #3498db !important;
            background: rgba(52, 152, 219, 0.1);
            z-index: 100;
        }

        /* NODOS DE ANCLAJE Y ELIMINACIÓN */
        .node {
            position: absolute;
            width: 14px;
            height: 14px;
            background: #27ae60;
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
            z-index: 110;
            border: 1px solid white;
        }

        .node,
        .btn-del {
            pointer-events: auto !important;
        }

        .is-label.selected .node {
            display: flex;
        }

        .node-top {
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
        }

        .node-bottom {
            bottom: -7px;
            left: 50%;
            transform: translateX(-50%);
        }

        .node-left {
            left: -7px;
            top: 50%;
            transform: translateY(-50%);
        }

        .node-right {
            right: -7px;
            top: 50%;
            transform: translateY(-50%);
        }

        .btn-del {
            position: absolute;
            top: -18px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 4px;
            padding: 0px 5px;
            font-size: 12px;
            display: none;
            cursor: pointer;
            z-index: 110;
        }

        .selected .btn-del {
            display: block;
        }

        /* GUÍAS MAGNÉTICAS Y GRUPOS */
        .guide-line {
            position: absolute;
            background-color: #ff4757;
            z-index: 1000;
            pointer-events: none;
            display: none;
        }

        .guide-v {
            width: 1px;
            height: 100%;
            top: 0;
        }

        .guide-h {
            height: 1px;
            width: 100%;
            left: 0;
        }

        .group-outline {
            position: absolute;
            border: 1px solid rgba(52, 152, 219, 0.4);
            background: rgba(52, 152, 219, 0.05);
            pointer-events: none;
            display: none;
            z-index: 5;
        }

        /* ==========================================================================
                MODO VISUALIZACIÓN Y CERO MÁRGENES
            ========================================================================== */
        .modo-visualizacion,
        .modo-visualizacion .content,
        .modo-visualizacion .row,
        .modo-visualizacion .col-md-9 {
            padding: 0 !important;
            margin: 0 !important;
        }

        @media screen {

            .modo-visualizacion #canvas-container,
            .modo-visualizacion .badge-alumno {
                display: none !important;
            }
        }

        .modo-visualizacion #canvas-container {
            background: transparent !important;
            padding: 0 !important;
            min-height: auto !important;
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
        }

        .modo-visualizacion .credencial-canvas-instance {
            box-shadow: none !important;
            margin: 0 auto 30px auto !important;
            page-break-after: always;
            border: 1px solid transparent !important;
        }

        .modo-visualizacion .credencial-canvas-instance:last-of-type {
            page-break-after: auto !important;
        }

        .modo-visualizacion .col-md-3,
        .modo-visualizacion .box-header,
        .modo-visualizacion .btn-del,
        .modo-visualizacion .node,
        .modo-visualizacion #panel-edicion,
        .modo-visualizacion #btn-save-design,
        .modo-visualizacion .guide-line,
        .modo-visualizacion .group-outline {
            display: none !important;
        }

        .modo-visualizacion .draggable-item {
            border-color: transparent !important;
            cursor: default !important;
        }

        .header-impresion {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #222;
            color: white;
            border-radius: 8px;
        }

        .badge-alumno {
            display: inline-block;
            padding: 5px 15px;
            background: #3c8dbc;
            color: white;
            border-radius: 20px;
            margin-bottom: 10px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* ==========================================================================
                REGLA DEFINITIVA PARA LA IMPRESORA EVOLIS
            ========================================================================== */
        @media print {
            @page {
                margin: 0 !important;
                size: {{ $diseno->orientacion == 'vertical' ? '54mm 85.6mm' : '85.6mm 54mm' }};
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                background-color: white !important;
            }

            .no-print {
                display: none !important;
            }

            #wrapper-principal,
            .content-wrapper,
            .content,
            #canvas-container {
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
            }

            .modo-visualizacion .credencial-canvas-instance {
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                position: relative !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                zoom: 0.635 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    {{-- ENVOLTORIO MAESTRO --}}
    <div id="wrapper-principal" class="{{ request()->has('preview') || isset($alumnos) ? 'modo-visualizacion' : '' }}">

        @if (isset($alumnos) && count($alumnos) > 0)
            <div class="header-impresion no-print">
                @if (count($alumnos) == 1)
                    <h2 style="margin:0"><i class="fa fa-id-badge"></i> Impresión Individual</h2>
                    <p style="color: #bbb; margin-top:5px;">
                        Alumno: <b>{{ $alumnos->first()->nombre }} {{ $alumnos->first()->ap_paterno }}</b> | Diseño:
                        {{ $diseno->nombre }}
                    </p>
                @else
                    <h2 style="margin:0"><i class="fa fa-users"></i> Lote de Impresión: {{ count($alumnos) }} alumnos</h2>
                    <p style="color: #bbb; margin-top:5px;">Diseño: {{ $diseno->nombre }}</p>
                @endif
                <button onclick="window.print()" class="btn btn-success btn-lg"
                    style="margin-top:15px; font-weight:bold; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                    <i class="fa fa-print"></i> VER VISTA PREVIA DE IMPRESIÓN
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-3 no-print">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Elementos</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-toggle="modal" data-target="#modalHelp"><i
                                    class="fa fa-question-circle"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <a href="{{ route('credenciales.imprimirLote', [$diseno->id, $loteActual->id ?? 1]) }}"
                            target="_blank" class="btn btn-success btn-block text-bold" style="margin-bottom: 10px;">
                            <i class="fa fa-users"></i> VISTA PREVIA CON DATOS
                        </a>

                        {{-- ── SECCIÓN 0: ETIQUETAS FIJAS ── --}}
                        <hr style="margin: 10px 0;">
                        <label style="color:#555;"><i class="fa fa-tag"></i> Etiquetas Fijas:</label>
                        <div class="input-group input-group-sm" style="margin-bottom: 5px;">
                            <select id="select-etiquetas" class="form-control">
                                <option value="Nombre:">Nombre:</option>
                                <option value="Matrícula:">Matrícula:</option>
                                <option value="Nivel:">Nivel:</option>
                                <option value="Grado:">Grado:</option>
                                <option value="Grupo:">Grupo:</option>
                                <option value="Ciclo Escolar:">Ciclo Escolar:</option>
                                <option value="Tipo de Sangre:">Tipo Sangre:</option>
                                <option value="Tutor:">Tutor:</option>
                                <option value="Tel. Emergencia:">Tel. Emergencia:</option>
                                <option value="Autorizado 1:">Autorizado 1:</option>
                                <option value="Autorizado 2:">Autorizado 2:</option>
                                <option value="Autorizado 3:">Autorizado 3:</option>
                                <option value="Director:">Director:</option>
                                <option value="Firma:">Firma:</option>
                            </select>
                            <span class="input-group-btn">
                                <button class="btn btn-primary btn-flat" onclick="addSelectedLabel()"><i
                                        class="fa fa-plus"></i></button>
                            </span>
                        </div>
                        <button class="btn btn-default btn-block btn-sm text-left"
                            onclick="addElement('label', 'Texto Libre')"><i class="fa fa-pencil"></i> Etiqueta
                            Personalizada</button>

                        {{-- ── SECCIÓN 1: ACADÉMICOS ── --}}
                        <hr style="margin: 10px 0;">
                        <label style="color:#3c8dbc;"><i class="fa fa-graduation-cap"></i> Académicos y Personales:</label>
                        <div class="row">
                            <div class="col-xs-6" style="padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('nombre', 'ALBERTO SAMAYOA')"><i class="fa fa-user"></i>
                                    Nombre</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('matricula', '2026-0001')"><i class="fa fa-barcode"></i>
                                    Matrícula</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-xs-4" style="padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('nivel', 'SECUNDARIA')" style="padding: 5px 2px;"><i
                                        class="fa fa-university"></i> Nivel</button>
                            </div>
                            <div class="col-xs-4" style="padding-left: 2px; padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('grado', '1°')" style="padding: 5px 2px;"><i class="fa fa-book"></i>
                                    Grado</button>
                            </div>
                            <div class="col-xs-4" style="padding-left: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('grupo', 'A')" style="padding: 5px 2px;"><i class="fa fa-users"></i>
                                    Grupo</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-xs-6" style="padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('ciclo', '{{ $cicloActual->nombre ?? '2025-2026' }}')"><i
                                        class="fa fa-calendar"></i> Ciclo</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('sangre', 'O+')"><i class="fa fa-tint"></i> Sangre</button>
                            </div>
                        </div>
                        <button class="btn btn-default btn-block btn-sm text-left" style="margin-top: 5px;"
                            onclick="addElement('foto', 'FOTO')"><i class="fa fa-camera"></i> Foto del Alumno</button>

                        {{-- ── SECCIÓN 2: AUTORIZADOS ── --}}
                        <hr style="margin: 10px 0;">
                        <label style="color:#e67e22;"><i class="fa fa-home"></i> Contactos y Autorizados:</label>
                        <div class="row">
                            <div class="col-xs-6" style="padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('tutor', 'NOMBRE DEL TUTOR')"><i class="fa fa-user-circle"></i>
                                    Tutor</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('tel_emergencia', '961-000-0000')"><i class="fa fa-phone"></i>
                                    Tel. Emergencia</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-xs-4" style="padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('autorizado1', 'AUTORIZADO 1')" style="padding: 5px 2px;"><i
                                        class="fa fa-check-square-o"></i> Aut 1</button>
                            </div>
                            <div class="col-xs-4" style="padding-left: 2px; padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('autorizado2', 'AUTORIZADO 2')" style="padding: 5px 2px;"><i
                                        class="fa fa-check-square-o"></i> Aut 2</button>
                            </div>
                            <div class="col-xs-4" style="padding-left: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('autorizado3', 'AUTORIZADO 3')" style="padding: 5px 2px;"><i
                                        class="fa fa-check-square-o"></i> Aut 3</button>
                            </div>
                        </div>

                        {{-- ── SECCIÓN 3: INSTITUCIÓN ── --}}
                        <hr style="margin: 10px 0;">
                        <label style="color:#27ae60;"><i class="fa fa-institution"></i> Institución:</label>
                        <div class="row">
                            <div class="col-xs-6" style="padding-right: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('director', 'LIC. JUAN PÉREZ')"><i class="fa fa-user-secret"></i>
                                    Director</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 2px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('puesto_director', 'DIRECTOR GENERAL')"><i
                                        class="fa fa-briefcase"></i> Puesto</button>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block btn-sm text-left" style="margin-top: 5px;"
                            onclick="triggerLogoUpload()"><i class="fa fa-picture-o"></i> <b>Añadir Logo /
                                Firma</b></button>
                        <input type="file" id="inputLogo" style="display:none" accept="image/*">

                        <hr>
                        <label>Fondo Anverso</label>
                        <input type="file" id="inputFondo" class="form-control" accept="image/*"
                            style="margin-bottom: 10px;">

                        <label>Fondo Reverso</label>
                        <input type="file" id="inputFondoReverso" class="form-control" accept="image/*">
                    </div>
                </div>

                <div id="panel-edicion" class="box box-warning" style="display: none; border-top: 3px solid #f39c12;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Propiedades</h3>
                    </div>
                    <div class="box-body">
                        <label>Texto Contenido:</label>
                        <input type="text" id="prop-text" class="form-control" oninput="updateLive()"><br>
                        <label>Tipografía:</label>
                        <select id="prop-font" class="form-control" onchange="updateLive()">
                            <option value="'Roboto', sans-serif">Roboto</option>
                            <option value="'Montserrat', sans-serif">Montserrat</option>
                            <option value="'Oswald', sans-serif">Oswald</option>
                        </select><br>
                        <label>Estilo y Alineación:</label>
                        <div class="btn-group btn-group-justified" style="margin-bottom: 10px;">
                            <a class="btn btn-default btn-sm" id="btn-bold" onclick="toggleBold()"><i
                                    class="fa fa-bold"></i></a>
                            <a class="btn btn-default btn-sm" id="btn-italic" onclick="toggleItalic()"><i
                                    class="fa fa-italic"></i></a>
                            <a class="btn btn-default btn-sm" id="align-left" onclick="setAlign('left')"><i
                                    class="fa fa-align-left"></i></a>
                            <a class="btn btn-default btn-sm" id="align-center" onclick="setAlign('center')"><i
                                    class="fa fa-align-center"></i></a>
                            <a class="btn btn-default btn-sm" id="align-right" onclick="setAlign('right')"><i
                                    class="fa fa-align-right"></i></a>
                        </div>
                        <div class="row">
                            <div class="col-xs-8">
                                <label>Tamaño: <span id="txt-size">14</span>px</label>
                                <input type="range" id="prop-size" min="6" max="100"
                                    oninput="updateLive()">
                            </div>
                            <div class="col-xs-4">
                                <label>Color:</label>
                                <input type="color" id="prop-color" class="form-control" onchange="updateLive()">
                            </div>
                        </div>
                    </div>
                </div>

                <button id="btn-save-design" class="btn btn-success btn-block btn-lg btn-flat" onclick="saveAll()">
                    <i class="fa fa-save"></i> GUARDAR DISEÑO
                </button>
            </div>

            <div class="col-md-9">
                @if (!isset($alumnos))
                    <div class="text-center no-print" style="margin-bottom: 15px;">
                        <div class="btn-group">
                            <button type="button" id="btn-show-anverso" class="btn btn-primary active"
                                onclick="switchFace('anverso')">
                                <i class="fa fa-id-card-o"></i> Diseño Frontal
                            </button>
                            <button type="button" id="btn-show-reverso" class="btn btn-default"
                                onclick="switchFace('reverso')">
                                <i class="fa fa-refresh"></i> Diseño Reverso
                            </button>
                        </div>
                    </div>
                @endif

                <div id="canvas-container">
                    @if (isset($alumnos) && count($alumnos) > 0)
                        {{-- MODO IMPRESIÓN (Genera Anverso y Reverso) --}}
                        @foreach ($alumnos as $alumno)
                            @php
                                // Extraemos variables limpias para inyectar en el JS
                                $insc = $alumno->inscripciones->first();
                                $nivelStr = $insc?->grupo?->grado?->nivel?->nombre ?? '';
                                $gradoStr = $insc?->grupo?->grado?->nombre ?? '';
                                $grupoStr = $insc?->grupo?->nombre ?? '';

                                $contactos = $alumno->familia?->contactos ?? collect();
                                $tutorObj = $contactos->where('es_principal', true)->first() ?? $contactos->first();
                                $tutorStr = $tutorObj ? $tutorObj->nombre . ' ' . $tutorObj->ap_paterno : '';

                                $emergenciaStr = $tutorObj->telefono_celular ?? '';

                                $autorizados = $contactos
                                    ->filter(function ($c) {
                                        return $c->puede_recoger ?? true;
                                    })
                                    ->values();
                                $aut1 = isset($autorizados[0])
                                    ? $autorizados[0]->nombre . ' ' . $autorizados[0]->ap_paterno
                                    : '';
                                $aut2 = isset($autorizados[1])
                                    ? $autorizados[1]->nombre . ' ' . $autorizados[1]->ap_paterno
                                    : '';
                                $aut3 = isset($autorizados[2])
                                    ? $autorizados[2]->nombre . ' ' . $autorizados[2]->ap_paterno
                                    : '';

                                $nombreStr =
                                    $alumno->nombre .
                                    ' ' .
                                    ($alumno->ap_paterno ?? '') .
                                    ' ' .
                                    ($alumno->ap_materno ?? '');
                            @endphp

                            <div class="no-print" style="width: 100%; text-align: center;">
                                <span class="badge-alumno">Alumno {{ $loop->iteration }} de {{ $loop->count }}:
                                    {{ $alumno->nombre }} {{ $alumno->ap_paterno }}</span>
                            </div>

                            {{-- CARA 1: ANVERSO --}}
                            <div id="credencial-canvas-anverso-{{ $alumno->id }}"
                                class="credencial-canvas-instance face-anverso" data-nombre="{{ trim($nombreStr) }}"
                                data-matricula="{{ $alumno->matricula ?? '' }}" data-nivel="{{ $nivelStr }}"
                                data-grado="{{ $gradoStr }}" data-grupo="{{ $grupoStr }}"
                                data-ciclo="{{ $cicloActual->nombre ?? '' }}"
                                data-sangre="{{ $alumno->tipo_sangre ?? '' }}"
                                data-foto="{{ $alumno->foto_url ? Storage::url($alumno->foto_url) : '' }}"
                                data-tutor="{{ $tutorStr }}" data-emergencia="{{ $emergenciaStr }}"
                                data-autorizado1="{{ $aut1 }}" data-autorizado2="{{ $aut2 }}"
                                data-autorizado3="{{ $aut3 }}" data-director="" data-puesto_director=""
                                onclick="deselect(event)">
                                @if ($diseno->fondo_anverso)
                                    <img src="{{ asset('storage/' . $diseno->fondo_anverso) }}" class="fondo-credencial">
                                @else
                                    <img src="" class="fondo-credencial" style="display:none;">
                                @endif
                                <div id="group-outline-anverso-{{ $alumno->id }}" class="group-outline"></div>
                            </div>

                            {{-- CARA 2: REVERSO --}}
                            <div id="credencial-canvas-reverso-{{ $alumno->id }}"
                                class="credencial-canvas-instance face-reverso" data-nombre="{{ trim($nombreStr) }}"
                                data-matricula="{{ $alumno->matricula ?? '' }}" data-nivel="{{ $nivelStr }}"
                                data-grado="{{ $gradoStr }}" data-grupo="{{ $grupoStr }}"
                                data-ciclo="{{ $cicloActual->nombre ?? '' }}"
                                data-sangre="{{ $alumno->tipo_sangre ?? '' }}"
                                data-foto="{{ $alumno->foto_url ? Storage::url($alumno->foto_url) : '' }}"
                                data-tutor="{{ $tutorStr }}" data-emergencia="{{ $emergenciaStr }}"
                                data-autorizado1="{{ $aut1 }}" data-autorizado2="{{ $aut2 }}"
                                data-autorizado3="{{ $aut3 }}" data-director="" data-puesto_director=""
                                onclick="deselect(event)">
                                @if ($diseno->fondo_reverso)
                                    <img src="{{ asset('storage/' . $diseno->fondo_reverso) }}" class="fondo-credencial">
                                @else
                                    <img src="" class="fondo-credencial" style="display:none;">
                                @endif
                                <div id="group-outline-reverso-{{ $alumno->id }}" class="group-outline"></div>
                            </div>
                        @endforeach
                    @else
                        {{-- MODO EDITOR --}}
                        <div id="credencial-canvas" class="credencial-canvas-instance face-anverso"
                            onclick="deselect(event)">
                            @if ($diseno->fondo_anverso)
                                <img src="{{ asset('storage/' . $diseno->fondo_anverso) }}" class="fondo-credencial"
                                    id="img-fondo-editor">
                            @else
                                <img src="" class="fondo-credencial" id="img-fondo-editor"
                                    style="display:none;">
                            @endif
                            <div id="group-outline" class="group-outline"></div>
                            <div id="guide-v" class="guide-line guide-v"></div>
                            <div id="guide-h" class="guide-line guide-h"></div>
                        </div>

                        <div id="credencial-canvas-reverso" class="credencial-canvas-instance face-reverso"
                            onclick="deselect(event)" style="display:none;">
                            @if ($diseno->fondo_reverso)
                                <img src="{{ asset('storage/' . $diseno->fondo_reverso) }}" class="fondo-credencial"
                                    id="img-fondo-editor-reverso">
                            @else
                                <img src="" class="fondo-credencial" id="img-fondo-editor-reverso"
                                    style="display:none;">
                            @endif
                            <div id="group-outline-reverso" class="group-outline"></div>
                            <div id="guide-v-reverso" class="guide-line guide-v"></div>
                            <div id="guide-h-reverso" class="guide-line guide-h"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODALES --}}
    <div class="modal fade" id="modalHelp" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Atajos de Teclado</h4>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <td><kbd>Flechas</kbd></td>
                            <td>Mover libremente (1px)</td>
                        </tr>
                        <tr>
                            <td><kbd>Shift + Flechas</kbd></td>
                            <td>Mover rápido (10px)</td>
                        </tr>
                        <tr>
                            <td><kbd>Arrastrar</kbd></td>
                            <td>Mover libremente (Muestra guías)</td>
                        </tr>
                        <tr>
                            <td><kbd>Shift + Arrastrar</kbd></td>
                            <td>Snap Magnético a las guías</td>
                        </tr>
                        <tr>
                            <td><kbd>Supr / Backspace</kbd></td>
                            <td>Eliminar elemento seleccionado</td>
                        </tr>
                        <tr>
                            <td><kbd>Esc</kbd></td>
                            <td>Deseleccionar</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalAnclaje" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #3c8dbc; color: white;">
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-link"></i> Anclar Dato Dinámico</h4>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">

                    <label style="color:#3c8dbc; font-size:12px;"><i class="fa fa-graduation-cap"></i> Académicos:</label>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('nombre', 'ALBERTO SAMAYOA')"><i class="fa fa-user"></i> Nombre</button>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('matricula', '2026-0001')"><i class="fa fa-barcode"></i> Matrícula</button>
                    <button class="btn btn-default btn-block text-left" onclick="confirmAnchor('nivel', 'SECUNDARIA')"><i
                            class="fa fa-university"></i> Nivel</button>
                    <button class="btn btn-default btn-block text-left" onclick="confirmAnchor('grado', '1°')"><i
                            class="fa fa-book"></i> Grado</button>
                    <button class="btn btn-default btn-block text-left" onclick="confirmAnchor('grupo', 'A')"><i
                            class="fa fa-users"></i> Grupo</button>
                    <button class="btn btn-default btn-block text-left" onclick="confirmAnchor('ciclo', '2025-2026')"><i
                            class="fa fa-calendar"></i> Ciclo Escolar</button>
                    <button class="btn btn-default btn-block text-left" onclick="confirmAnchor('sangre', 'O+')"><i
                            class="fa fa-tint"></i> Tipo Sangre</button>

                    <hr style="margin: 10px 0;">
                    <label style="color:#e67e22; font-size:12px;"><i class="fa fa-home"></i> Contactos:</label>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('tutor', 'NOMBRE DEL TUTOR')"><i class="fa fa-user-circle"></i> Tutor
                        Principal</button>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('tel_emergencia', '961-000-0000')"><i class="fa fa-phone"></i> Tel.
                        Emergencia</button>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('autorizado1', 'AUTORIZADO 1')"><i class="fa fa-check-square-o"></i>
                        Autorizado 1</button>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('autorizado2', 'AUTORIZADO 2')"><i class="fa fa-check-square-o"></i>
                        Autorizado 2</button>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('autorizado3', 'AUTORIZADO 3')"><i class="fa fa-check-square-o"></i>
                        Autorizado 3</button>

                    <hr style="margin: 10px 0;">
                    <label style="color:#27ae60; font-size:12px;"><i class="fa fa-institution"></i> Institución:</label>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('director', 'LIC. JUAN PÉREZ')"><i class="fa fa-user-secret"></i>
                        Director</button>
                    <button class="btn btn-default btn-block text-left"
                        onclick="confirmAnchor('puesto_director', 'DIRECTOR GENERAL')"><i class="fa fa-briefcase"></i>
                        Puesto</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <script>
        let selected = null;
        let anchorPending = null;
        let imagenTemporal = null;
        let imagenTemporalReverso = null;
        let unsavedChanges = false;
        let currentFace = 'anverso';

        const wrapperPrincipal = document.getElementById('wrapper-principal');
        const isModeVisual = wrapperPrincipal.classList.contains('modo-visualizacion');
        if (isModeVisual) document.body.classList.add('modo-visualizacion-body');

        function switchFace(face) {
            currentFace = face;
            deselect();
            if (face === 'anverso') {
                document.getElementById('btn-show-anverso').className = 'btn btn-primary active';
                document.getElementById('btn-show-reverso').className = 'btn btn-default';
                document.getElementById('credencial-canvas').style.display = 'block';
                document.getElementById('credencial-canvas-reverso').style.display = 'none';
            } else {
                document.getElementById('btn-show-anverso').className = 'btn btn-default';
                document.getElementById('btn-show-reverso').className = 'btn btn-primary active';
                document.getElementById('credencial-canvas').style.display = 'none';
                document.getElementById('credencial-canvas-reverso').style.display = 'block';
            }
        }

        function markAsUnsaved() {
            if (!unsavedChanges && !isModeVisual) {
                unsavedChanges = true;
                const btn = document.getElementById('btn-save-design');
                if (btn) {
                    btn.className = 'btn btn-warning btn-block btn-lg btn-flat pulse-warning';
                    btn.innerHTML = '<i class="fa fa-exclamation-triangle"></i> CAMBIOS SIN GUARDAR *';
                }
            }
        }

        function markAsSaved() {
            unsavedChanges = false;
            const btn = document.getElementById('btn-save-design');
            if (btn) {
                btn.className = 'btn btn-success btn-block btn-lg btn-flat';
                btn.innerHTML = '<i class="fa fa-save"></i> DISEÑO GUARDADO';
            }
        }

        window.addEventListener('beforeunload', function(e) {
            if (unsavedChanges && !isModeVisual) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const configAnverso = @json($diseno->config_anverso ?? []);
            const configReverso = @json($diseno->config_reverso ?? []);

            if (isModeVisual) {
                document.querySelectorAll('.face-anverso').forEach(lienzo => {
                    if (configAnverso) configAnverso.forEach(item => restoreElement(item, lienzo));
                });
                document.querySelectorAll('.face-reverso').forEach(lienzo => {
                    if (configReverso) configReverso.forEach(item => restoreElement(item, lienzo));
                });
            } else {
                if (configAnverso) configAnverso.forEach(item => restoreElement(item, document.getElementById(
                    'credencial-canvas')));
                if (configReverso) configReverso.forEach(item => restoreElement(item, document.getElementById(
                    'credencial-canvas-reverso')));
            }
        });

        function checkOverflow(el) {
            if (isModeVisual || el.dataset.type === 'foto' || el.dataset.type === 'logo') return;
            const span = el.querySelector('.content-span');
            if (!span) return;

            span.style.width = 'auto';
            span.style.height = 'auto';
            const textoAlto = span.offsetHeight;
            const textoAncho = span.offsetWidth;
            span.style.width = '100%';
            span.style.height = '100%';

            if (textoAlto > el.clientHeight || textoAncho > el.clientWidth) el.classList.add('overflow-warning');
            else el.classList.remove('overflow-warning');
        }

        function restoreElement(data, canvas) {
            const id = isModeVisual && canvas.id !== 'credencial-canvas' && canvas.id !== 'credencial-canvas-reverso' ?
                canvas.id + '_' + data.id : data.id;
            const el = document.createElement('div');
            el.id = id;
            el.className = 'draggable-item ' + (data.isLabel ? 'is-label' : '');
            el.dataset.type = data.type;
            el.dataset.originalId = data.id;
            el.dataset.x = data.x;
            el.dataset.y = data.y;
            el.dataset.parentId = data.parentId ? (isModeVisual && canvas.id !== 'credencial-canvas' && canvas.id !==
                'credencial-canvas-reverso' ? canvas.id + '_' + data.parentId : data.parentId) : '';

            Object.assign(el.style, {
                transform: `translate(${data.x}px, ${data.y}px)`,
                fontSize: data.fontSize || '14px',
                width: data.width || 'auto',
                height: data.height || 'auto',
                textAlign: data.textAlign || 'left',
                fontWeight: data.fontWeight || 'normal',
                fontStyle: data.fontStyle || 'normal',
                fontFamily: data.fontFamily || 'Roboto, sans-serif'
            });

            if (!isModeVisual) {
                const btnDel = document.createElement('div');
                btnDel.className = 'btn-del';
                btnDel.innerHTML = '×';
                btnDel.onclick = (e) => {
                    e.stopPropagation();
                    deleteEl(el.id);
                };
                el.appendChild(btnDel);
                if (data.isLabel) {
                    ['top', 'bottom', 'left', 'right'].forEach(dir => {
                        const node = document.createElement('div');
                        node.className = `node node-${dir}`;
                        node.innerHTML = '+';
                        node.onclick = (e) => {
                            e.stopPropagation();
                            openAnchor(el.id, dir);
                        };
                        el.appendChild(node);
                    });
                }
            }

            const span = document.createElement('span');
            span.className = 'content-span';
            let textoFinal = data.text;
            let fotoUrl = null;

            if (isModeVisual && canvas.id !== 'credencial-canvas' && canvas.id !== 'credencial-canvas-reverso') {
                const mapData = {
                    'nombre': 'nombre',
                    'matricula': 'matricula',
                    'nivel': 'nivel',
                    'grado': 'grado',
                    'grupo': 'grupo',
                    'ciclo': 'ciclo',
                    'sangre': 'sangre',
                    'tutor': 'tutor',
                    'tel_emergencia': 'emergencia',
                    'autorizado1': 'autorizado1',
                    'autorizado2': 'autorizado2',
                    'autorizado3': 'autorizado3',
                    'director': 'director',
                    'puesto_director': 'puesto_director'
                };

                let isEmpty = false;

                if (mapData[data.type]) {
                    let val = canvas.dataset[mapData[data.type]];
                    if (val !== undefined && val !== null && val.trim() !== '') {
                        textoFinal = val;
                    } else {
                        textoFinal = '';
                        isEmpty = true;
                    }
                }

                if (data.type === 'foto') {
                    fotoUrl = canvas.dataset.foto;
                    if (!fotoUrl || fotoUrl.trim() === '') isEmpty = true;
                }

                // ── MAGIA INGENIERIL: Ocultamiento total forzado (!important) ──
                if (isEmpty) {
                    el.style.setProperty('display', 'none', 'important');
                    if (el.dataset.parentId) {
                        setTimeout(() => {
                            const parentEl = document.getElementById(el.dataset.parentId);
                            if (parentEl) parentEl.style.setProperty('display', 'none', 'important');
                        }, 0);
                    }
                }
            }

            if (data.type === 'foto' || data.type === 'logo') {
                el.style.padding = '0';
                el.style.border = isModeVisual ? 'none' : '1px dashed #ccc';
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.justifyContent = 'center';
                el.style.overflow = 'hidden';

                if (data.type === 'logo') {
                    let src = data.logo_src || '';
                    span.innerHTML = src ?
                        `<img src="${src}" style="width:100%; height:100%; object-fit:contain; display:block;">` :
                        `<i class="fa fa-picture-o fa-2x"></i>`;
                } else {
                    if (isModeVisual) {
                        span.innerHTML = fotoUrl ?
                            `<img src="${fotoUrl}" style="width:100%; height:100%; object-fit:cover; display:block;">` :
                            `<div style="width:100%; height:100%; background:transparent;"></div>`;
                    } else {
                        span.innerHTML =
                            `<div style="width:100%; height:100%; background:#f8f9fa; display:flex; align-items:center; justify-content:center;"><i class="fa fa-camera fa-3x" style="color:#bdc3c7"></i></div>`;
                    }
                }
            } else {
                span.innerText = textoFinal;
                span.style.setProperty('color', data.color || '#000000', 'important');
            }

            el.appendChild(span);
            if (!isModeVisual) {
                el.onclick = (e) => {
                    e.stopPropagation();
                    selectEl(el);
                };
                initInteract(el, canvas);
                setTimeout(() => checkOverflow(el), 100);
            }
            canvas.appendChild(el);
        }

        // LÓGICA VITAL DE MOVIMIENTO ESTABLE CON GUÍAS
        function initInteract(el, canvas) {
            let canvasW = canvas.offsetWidth;
            let canvasH = canvas.offsetHeight;
            let cachedItems = [];

            interact(el).draggable({
                inertia: false,
                autoScroll: true,
                listeners: {
                    start(event) {
                        cachedItems = [];
                        canvas.querySelectorAll('.draggable-item').forEach(item => {
                            if (item.id !== event.target.id) {
                                cachedItems.push({
                                    id: item.id,
                                    x: parseFloat(item.dataset.x) || 0,
                                    y: parseFloat(item.dataset.y) || 0,
                                    w: item.offsetWidth,
                                    h: item.offsetHeight
                                });
                            }
                        });
                    },
                    move(event) {
                        const target = event.target;
                        let oldX = parseFloat(target.dataset.x) || 0;
                        let oldY = parseFloat(target.dataset.y) || 0;
                        let x = oldX + event.dx;
                        let y = oldY + event.dy;

                        const w = target.offsetWidth;
                        const h = target.offsetHeight;
                        const threshold = 6;
                        let alignedX = false,
                            alignedY = false;
                        let guideX = 0,
                            guideY = 0;
                        let snapX = x,
                            snapY = y;

                        // GUÍAS MAGNÉTICAS
                        if (Math.abs((x + w / 2) - canvasW / 2) < threshold) {
                            snapX = canvasW / 2 - w / 2;
                            guideX = canvasW / 2;
                            alignedX = true;
                        }
                        if (Math.abs((y + h / 2) - canvasH / 2) < threshold) {
                            snapY = canvasH / 2 - h / 2;
                            guideY = canvasH / 2;
                            alignedY = true;
                        }

                        cachedItems.forEach(item => {
                            if (!alignedX) {
                                if (Math.abs(x - item.x) < threshold) {
                                    snapX = item.x;
                                    guideX = snapX;
                                    alignedX = true;
                                } else if (Math.abs((x + w / 2) - (item.x + item.w / 2)) < threshold) {
                                    snapX = item.x + item.w / 2 - w / 2;
                                    guideX = item.x + item.w / 2;
                                    alignedX = true;
                                } else if (Math.abs((x + w) - (item.x + item.w)) < threshold) {
                                    snapX = item.x + item.w - w;
                                    guideX = item.x + item.w;
                                    alignedX = true;
                                }
                            }
                            if (!alignedY) {
                                if (Math.abs(y - item.y) < threshold) {
                                    snapY = item.y;
                                    guideY = snapY;
                                    alignedY = true;
                                } else if (Math.abs((y + h / 2) - (item.y + item.h / 2)) < threshold) {
                                    snapY = item.y + item.h / 2 - h / 2;
                                    guideY = item.y + item.h / 2;
                                    alignedY = true;
                                } else if (Math.abs((y + h) - (item.y + item.h)) < threshold) {
                                    snapY = item.y + item.h - h;
                                    guideY = item.y + item.h;
                                    alignedY = true;
                                }
                            }
                        });

                        const gV = canvas.querySelector('.guide-v');
                        const gH = canvas.querySelector('.guide-h');
                        if (alignedX) {
                            gV.style.left = guideX + 'px';
                            gV.style.display = 'block';
                        } else {
                            gV.style.display = 'none';
                        }
                        if (alignedY) {
                            gH.style.top = guideY + 'px';
                            gH.style.display = 'block';
                        } else {
                            gH.style.display = 'none';
                        }
                        if (event.shiftKey) {
                            if (alignedX) x = snapX;
                            if (alignedY) y = snapY;
                        }

                        let effectiveDx = x - oldX;
                        let effectiveDy = y - oldY;

                        updatePos(target, x, y);

                        canvas.querySelectorAll(`[data-parent-id="${target.id}"]`).forEach(c => {
                            updatePos(c, parseFloat(c.dataset.x) + effectiveDx, parseFloat(c.dataset.y) +
                                effectiveDy);
                        });

                        const p = target.dataset.parentId ? document.getElementById(target.dataset.parentId) :
                            target;
                        drawGroupOutline(p, canvas);
                        checkOverflow(target);
                        markAsUnsaved();
                    },
                    end() {
                        canvas.querySelector('.guide-v').style.display = 'none';
                        canvas.querySelector('.guide-h').style.display = 'none';
                    }
                }
            }).resizable({
                margin: 4,
                edges: {
                    left: false,
                    top: false,
                    right: true,
                    bottom: true
                },
                listeners: {
                    move(event) {
                        let {
                            x,
                            y
                        } = event.target.dataset;
                        x = (parseFloat(x) || 0) + event.deltaRect.left;
                        y = (parseFloat(y) || 0) + event.deltaRect.top;

                        Object.assign(event.target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`
                        });

                        updatePos(event.target, x, y);
                        checkOverflow(event.target);
                        markAsUnsaved();
                    }
                }
            });
        }

        function updatePos(el, x, y) {
            el.style.transform = `translate(${x}px, ${y}px)`;
            el.dataset.x = x;
            el.dataset.y = y;
        }

        function selectEl(el) {
            if (isModeVisual) return;
            deselect();
            selected = el;
            el.classList.add('selected');
            document.getElementById('panel-edicion').style.display = 'block';
            const isMedia = (el.dataset.type === 'foto' || el.dataset.type === 'logo');
            const propText = document.getElementById('prop-text');
            propText.value = isMedia ? 'Elemento Gráfico' : el.querySelector('.content-span').innerText;
            propText.disabled = isMedia;
            document.getElementById('prop-size').value = parseInt(el.style.fontSize) || 14;
            document.getElementById('prop-color').value = rgbToHex(el.style.color) || '#000000';
            document.getElementById('txt-size').innerText = parseInt(el.style.fontSize) || 14;
            updateUIButtons();
        }

        function deselect(e) {
            if (isModeVisual) return;
            if (e && (!e.target.closest || !e.target.closest('.credencial-canvas-instance'))) return;
            document.querySelectorAll('.draggable-item').forEach(i => i.classList.remove('selected'));
            selected = null;
            document.getElementById('panel-edicion').style.display = 'none';
            document.querySelectorAll('.group-outline').forEach(o => o.style.display = 'none');
        }

        function updateLive() {
            if (!selected) return;
            const span = selected.querySelector('.content-span');
            if (selected.dataset.type !== 'foto' && selected.dataset.type !== 'logo') span.innerText = document
                .getElementById('prop-text').value;
            selected.style.fontSize = document.getElementById('prop-size').value + 'px';
            document.getElementById('txt-size').innerText = document.getElementById('prop-size').value;
            const colorHex = document.getElementById('prop-color').value;
            selected.style.color = colorHex;
            span.style.setProperty('color', colorHex, 'important');
            selected.style.fontFamily = document.getElementById('prop-font').value;
            checkOverflow(selected);
            markAsUnsaved();
            const p = selected.dataset.parentId ? document.getElementById(selected.dataset.parentId) : selected;
            const canvasTarget = (currentFace === 'anverso') ? document.getElementById('credencial-canvas') : document
                .getElementById('credencial-canvas-reverso');
            drawGroupOutline(p, canvasTarget);
        }

        function updateUIButtons() {
            if (!selected) return;
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            if (selected.style.fontWeight === 'bold' || selected.style.fontWeight === '700') document.getElementById(
                'btn-bold').classList.add('active');
            if (selected.style.fontStyle === 'italic') document.getElementById('btn-italic').classList.add('active');
            const btnAlign = document.getElementById('align-' + (selected.style.textAlign || 'left'));
            if (btnAlign) btnAlign.classList.add('active');
            if (selected.style.fontFamily) document.getElementById('prop-font').value = selected.style.fontFamily.replace(
                /"/g, "'");
        }

        function toggleBold() {
            if (!selected) return;
            selected.style.fontWeight = (selected.style.fontWeight === 'bold' || selected.style.fontWeight === '700') ?
                'normal' : 'bold';
            updateUIButtons();
            updateLive();
        }

        function toggleItalic() {
            if (!selected) return;
            selected.style.fontStyle = (selected.style.fontStyle === 'italic') ? 'normal' : 'italic';
            updateUIButtons();
            updateLive();
        }

        function setAlign(a) {
            if (!selected) return;
            selected.style.textAlign = a;
            updateUIButtons();
            updateLive();
        }

        // ── DESTRUCCIÓN EN CASCADA ──
        function deleteEl(id) {
            const el = document.getElementById(id);
            if (el) {
                // Si el elemento es un padre, busca y aniquila a sus hijos anclados primero
                document.querySelectorAll(`[data-parent-id="${id}"]`).forEach(hijo => hijo.remove());
                el.remove(); // Luego se destruye a sí mismo
            }
            deselect();
            markAsUnsaved();
        }

        function addElement(type, text) {
            if (isModeVisual) return;
            const id = 'el_' + Date.now();
            let defaultW = 'auto';
            let defaultH = 'auto';
            if (type === 'foto') {
                defaultW = '100px';
                defaultH = '130px';
            }
            if (type === 'logo') {
                defaultW = '80px';
                defaultH = '80px';
            }

            const targetCanvasId = (currentFace === 'anverso') ? 'credencial-canvas' : 'credencial-canvas-reverso';
            restoreElement({
                id: id,
                type: type,
                x: 30,
                y: 30,
                text: text,
                fontSize: '14px',
                color: (type === 'sangre') ? '#e74c3c' : '#033b8a',
                width: defaultW,
                height: defaultH,
                textAlign: 'center',
                fontWeight: 'bold',
                fontStyle: 'normal',
                fontFamily: "'Montserrat', sans-serif",
                isLabel: (type === 'label')
            }, document.getElementById(targetCanvasId));
            selectEl(document.getElementById(id));
            markAsUnsaved();
        }

        function addSelectedLabel() {
            const text = document.getElementById('select-etiquetas').value;
            addElement('label', text);
        }

        function openAnchor(id, dir) {
            anchorPending = {
                parentId: id,
                dir: dir
            };
            $('#modalAnclaje').modal('show');
        }

        function confirmAnchor(type, text) {
            const parent = document.getElementById(anchorPending.parentId);
            const pX = parseFloat(parent.dataset.x),
                pY = parseFloat(parent.dataset.y),
                pW = parent.offsetWidth,
                pH = parent.offsetHeight;
            let nX = pX,
                nY = pY;

            // ── AJUSTE FINO: Eliminamos espacios al anclar a la derecha (+0 en vez de +10) ──
            if (anchorPending.dir === 'right') nX = pX + pW;
            // ── AJUSTE FINO: Eliminamos espacios al anclar abajo (+0 en vez de +5) ──
            else if (anchorPending.dir === 'bottom') nY = pY + pH;

            addElement(type, text);
            const child = selected;
            child.dataset.parentId = anchorPending.parentId;

            // ── ALINEACIÓN FORZADA A LA IZQUIERDA ──
            child.style.textAlign = 'left';

            updatePos(child, nX, nY);
            $('#modalAnclaje').modal('hide');
            const canvasTarget = (currentFace === 'anverso') ? document.getElementById('credencial-canvas') : document
                .getElementById('credencial-canvas-reverso');
            drawGroupOutline(parent, canvasTarget);
            markAsUnsaved();
        }

        function drawGroupOutline(parentEl, canvas) {
            if (!parentEl || isModeVisual) return;
            const children = canvas.querySelectorAll(`[data-parent-id="${parentEl.id}"]`);
            const outline = canvas.querySelector('.group-outline');
            if (!outline) return;
            if (children.length === 0) {
                outline.style.display = 'none';
                return;
            }
            let minX = parseFloat(parentEl.dataset.x),
                minY = parseFloat(parentEl.dataset.y),
                maxX = minX + parentEl.offsetWidth,
                maxY = minY + parentEl.offsetHeight;
            children.forEach(c => {
                minX = Math.min(minX, parseFloat(c.dataset.x));
                minY = Math.min(minY, parseFloat(c.dataset.y));
                maxX = Math.max(maxX, parseFloat(c.dataset.x) + c.offsetWidth);
                maxY = Math.max(maxY, parseFloat(c.dataset.y) + c.offsetHeight);
            });
            outline.style.display = 'block';
            outline.style.width = (maxX - minX + 10) + 'px';
            outline.style.height = (maxY - minY + 10) + 'px';
            outline.style.transform = `translate(${minX - 5}px, ${minY - 5}px)`;
        }

        // ATAJOS DE TECLADO
        window.addEventListener('keydown', function(e) {
            if (isModeVisual || !selected || e.target.tagName === 'INPUT') return;
            const step = e.shiftKey ? 10 : 1;
            let x = parseFloat(selected.dataset.x),
                y = parseFloat(selected.dataset.y);
            switch (e.key) {
                case 'ArrowUp':
                    e.preventDefault();
                    handleKeyMove(x, y - step);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    handleKeyMove(x, y + step);
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    handleKeyMove(x - step, y);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    handleKeyMove(x + step, y);
                    break;
                case 'Delete':
                case 'Backspace':
                    e.preventDefault();
                    deleteEl(selected.id);
                    break;
                case 'Escape':
                    e.preventDefault();
                    deselect();
                    break;
            }
        });

        function handleKeyMove(nX, nY) {
            const dx = nX - parseFloat(selected.dataset.x),
                dy = nY - parseFloat(selected.dataset.y);
            updatePos(selected, nX, nY);
            document.querySelectorAll(`[data-parent-id="${selected.id}"]`).forEach(c => {
                updatePos(c, parseFloat(c.dataset.x) + dx, parseFloat(c.dataset.y) + dy);
            });
            const p = selected.dataset.parentId ? document.getElementById(selected.dataset.parentId) : selected;
            const canvasTarget = (currentFace === 'anverso') ? document.getElementById('credencial-canvas') : document
                .getElementById('credencial-canvas-reverso');
            drawGroupOutline(p, canvasTarget);
            checkOverflow(selected);
            markAsUnsaved();
        }

        // MANEJO DE LOGO
        function triggerLogoUpload() {
            document.getElementById('inputLogo').click();
        }

        document.getElementById('inputLogo').onchange = function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const id = 'logo_' + Date.now();
                    const targetCanvasId = (currentFace === 'anverso') ? 'credencial-canvas' :
                        'credencial-canvas-reverso';
                    restoreElement({
                        id: id,
                        type: 'logo',
                        x: 50,
                        y: 50,
                        width: '80px',
                        height: '80px',
                        logo_src: event.target.result
                    }, document.getElementById(targetCanvasId));
                    markAsUnsaved();
                };
                reader.readAsDataURL(file);
            }
        };

        // GUARDADO DE DATOS
        function getElementsFromCanvas(canvasId) {
            const elementos = [];
            document.querySelectorAll(`#${canvasId} .draggable-item`).forEach(el => {
                const span = el.querySelector('.content-span');
                let itemData = {
                    id: el.id,
                    parentId: el.dataset.parentId || null,
                    type: el.dataset.type,
                    x: el.dataset.x,
                    y: el.dataset.y,
                    width: el.style.width,
                    height: el.style.height,
                    fontSize: el.style.fontSize,
                    color: span.style.color,
                    text: el.classList.contains('is-label') ? span.innerText : el.dataset.type.toUpperCase(),
                    isLabel: el.classList.contains('is-label'),
                    textAlign: el.style.textAlign || 'left',
                    fontWeight: el.style.fontWeight || 'normal',
                    fontStyle: el.style.fontStyle || 'normal',
                    fontFamily: el.style.fontFamily || 'Roboto, sans-serif'
                };
                if (el.dataset.type === 'logo') {
                    const img = span.querySelector('img');
                    if (img) itemData.logo_src = img.src;
                }
                elementos.push(itemData);
            });
            return elementos;
        }

        function saveAll() {
            if (isModeVisual) return;
            const fData = new FormData();
            fData.append('config_anverso', JSON.stringify(getElementsFromCanvas('credencial-canvas')));
            fData.append('config_reverso', JSON.stringify(getElementsFromCanvas('credencial-canvas-reverso')));
            fData.append('_token', "{{ csrf_token() }}");
            if (imagenTemporal) fData.append('fondo_anverso', imagenTemporal);
            if (imagenTemporalReverso) fData.append('fondo_reverso', imagenTemporalReverso);

            fetch("{{ route('credenciales.updateConfig', $diseno->id) }}", {
                    method: "POST",
                    body: fData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("¡Guardado con éxito!");
                        markAsSaved();
                    }
                });
        }

        function rgbToHex(rgb) {
            if (!rgb || rgb.startsWith('#')) return rgb;
            const vals = rgb.match(/\d+/g);
            return vals ? "#" + vals.map(x => parseInt(x).toString(16).padStart(2, '0')).join('') : '#000000';
        }

        document.getElementById('inputFondo').onchange = (e) => {
            const f = e.target.files[0];
            if (f) {
                imagenTemporal = f;
                const r = new FileReader();
                r.onload = (ev) => {
                    const i = document.getElementById('img-fondo-editor');
                    if (i) {
                        i.src = ev.target.result;
                        i.style.display = 'block';
                    }
                };
                r.readAsDataURL(f);
                markAsUnsaved();
            }
        };
        document.getElementById('inputFondoReverso').onchange = (e) => {
            const f = e.target.files[0];
            if (f) {
                imagenTemporalReverso = f;
                const r = new FileReader();
                r.onload = (ev) => {
                    const i = document.getElementById('img-fondo-editor-reverso');
                    if (i) {
                        i.src = ev.target.result;
                        i.style.display = 'block';
                    }
                };
                r.readAsDataURL(f);
                markAsUnsaved();
            }
        };
    </script>
@endsection
