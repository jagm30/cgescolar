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

        /* LIENZO BASE: Se elimina el background-image para usar el blindaje de la etiqueta <img> */
        .credencial-canvas-instance,
        #credencial-canvas {
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

        /* BLINDAJE CONTRA CHROME: Etiqueta física para el fondo */
        .fondo-credencial {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 1 !important;
            /* Siempre al fondo */
            object-fit: cover !important;
            pointer-events: none !important;
            /* Permite clickear los textos a través de la imagen */
        }

        /* ELEMENTOS DRAGGABLE (Textos y Fotos) */
        .draggable-item {
            position: absolute;
            top: 0 !important;
            left: 0 !important;
            cursor: move;
            touch-action: none;
            user-select: none;
            pointer-events: auto;
            padding: 4px 8px;
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

        /* 1. Reglas generales de posición y fondo */
        .modo-visualizacion #canvas-container {
            background: transparent !important;
            padding: 0 !important;
            min-height: auto !important;
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
        }

        /* 2. MAGIA: Ocultar SOLO en el monitor (Debe ir DESPUÉS de la regla general) */
        @media screen {
            .modo-visualizacion #canvas-container {
                display: none !important;
            }

            .modo-visualizacion .badge-alumno {
                display: none !important;
            }
        }

        .modo-visualizacion .credencial-canvas-instance {
            box-shadow: none !important;
            margin: 0 auto 30px auto !important;
            page-break-after: always;
            border: 1px solid transparent !important;
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
                                                       REGLA DEFINITIVA PARA LA IMPRESORA EVOLIS (VUELTA AL ZOOM ESTABLE)
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
                /* El zoom que sí funciona sin romper el renderizado de Chrome */
                zoom: 0.635 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    {{-- ENVOLTORIO MAESTRO QUE DETECTA EL MODO (EDITOR / PREVIEW / LOTE) --}}
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
                        <h3 class="box-title">Acciones</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-toggle="modal" data-target="#modalHelp"><i
                                    class="fa fa-question-circle"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <a href="{{ route('credenciales.imprimirLote', [$diseno->id, $loteActual->id ?? 1]) }}"
                            target="_blank" class="btn btn-success btn-block text-bold" style="margin-bottom: 10px;">
                            <i class="fa fa-users"></i> VISTA PREVIA CON DATOS REALES
                        </a>

                        <button class="btn btn-primary btn-block text-left" onclick="addElement('label', 'Etiqueta Fija:')">
                            <i class="fa fa-tag"></i> <b>Añadir Etiqueta Fija</b>
                        </button>
                        <hr>

                        <label>Datos Dinámicos BD:</label>
                        <div class="row">
                            <div class="col-xs-6" style="padding-right: 5px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('nombre', 'ALBERTO SAMAYOA RAMOS JIMENEZ LOPEZ')"><i
                                        class="fa fa-user"></i> Nombre</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 5px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('matricula', '2026-0001')"><i class="fa fa-barcode"></i>
                                    Matrícula</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-xs-6" style="padding-right: 5px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('nivel', 'NIVEL ESCOLAR')"><i class="fa fa-university"></i> Nivel
                                    Escolar</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 5px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('grado', '1° SEMESTRE - A')"><i class="fa fa-graduation-cap"></i>
                                    Grado/Grupo</button>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 5px;">
                            <div class="col-xs-6" style="padding-right: 5px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('ciclo', '{{ $cicloActual->nombre ?? 'Sin ciclo' }}')"><i
                                        class="fa fa-calendar"></i> Ciclo Escolar</button>
                            </div>
                            <div class="col-xs-6" style="padding-left: 5px;">
                                <button class="btn btn-default btn-block btn-sm text-left"
                                    onclick="addElement('sangre', 'O+')"><i class="fa fa-tint"></i> Tipo Sangre</button>
                            </div>
                        </div>
                        <button class="btn btn-default btn-block btn-sm text-left" style="margin-top: 5px;"
                            onclick="addElement('foto', 'FOTO')"><i class="fa fa-camera"></i> Foto del Alumno</button>
                        <hr>

                        <label>Fondo de Imagen</label>
                        <input type="file" id="inputFondo" class="form-control" accept="image/*">
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
                <div id="canvas-container">
                    @if (isset($alumnos) && count($alumnos) > 0)
                        @foreach ($alumnos as $alumno)
                            <div class="no-print" style="width: 100%; text-align: center;">
                                <span class="badge-alumno">Alumno {{ $loop->iteration }} de {{ $loop->count }}:
                                    {{ $alumno->nombre }} {{ $alumno->ap_paterno }}</span>
                            </div>

                            <div id="credencial-canvas-{{ $alumno->id }}" class="credencial-canvas-instance"
                                data-nombre="{{ $alumno->nombre }} {{ $alumno->ap_paterno ?? '' }} {{ $alumno->ap_materno ?? '' }}"
                                data-matricula="{{ $alumno->matricula ?? 'S/N' }}"
                                data-nivel="{{ $alumno->inscripciones->first()?->grupo?->grado?->nivel?->nombre ?? 'SIN NIVEL' }}"
                                data-grado="{{ $alumno->inscripciones->first()?->grupo?->grado?->nombre ?? '' }} {{ $alumno->inscripciones->first()?->grupo?->nombre ?? '' }}"
                                data-ciclo="{{ $cicloActual->nombre ?? 'Sin ciclo' }}"
                                data-sangre="{{ $alumno->tipo_sangre ?? 'O+' }}"
                                data-foto="{{ $alumno->foto_url ? Storage::url($alumno->foto_url) : '' }}"
                                onclick="deselect(event)">

                                @if ($diseno->fondo_anverso)
                                    <img src="{{ asset('storage/' . $diseno->fondo_anverso) }}" class="fondo-credencial">
                                @else
                                    <img src="" class="fondo-credencial" style="display:none;">
                                @endif

                                <div id="group-outline-{{ $alumno->id }}" class="group-outline"></div>
                                <div id="guide-v-{{ $alumno->id }}" class="guide-line guide-v"></div>
                                <div id="guide-h-{{ $alumno->id }}" class="guide-line guide-h"></div>
                            </div>
                        @endforeach
                    @else
                        <div id="credencial-canvas" class="credencial-canvas-instance" onclick="deselect(event)">

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
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHelp" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4>Atajos de Teclado y Comandos</h4>
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
                <div class="modal-body">
                    <h4>Anclar Dato Dinámico</h4>
                    <button class="btn btn-default btn-block"
                        onclick="confirmAnchor('nombre', 'ALBERTO SAMAYOA')">Nombre</button>
                    <button class="btn btn-default btn-block"
                        onclick="confirmAnchor('matricula', '2026-0001')">Matrícula</button>
                    <button class="btn btn-default btn-block" onclick="confirmAnchor('grado', '1° SEMESTRE - A')">Grado y
                        Grupo</button>
                    <button class="btn btn-default btn-block" onclick="confirmAnchor('sangre', 'O+')">Tipo Sangre</button>
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
        let unsavedChanges = false;
        const wrapperPrincipal = document.getElementById('wrapper-principal');
        const isModeVisual = wrapperPrincipal.classList.contains('modo-visualizacion');
        if (isModeVisual) {
            document.body.classList.add('modo-visualizacion-body');
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
            const configInicial = @json($diseno->config_anverso ?? []);
            if (configInicial && configInicial.length > 0) {
                document.querySelectorAll('.credencial-canvas-instance').forEach(lienzo => {
                    configInicial.forEach(item => restoreElement(item, lienzo));
                });
            }
        });

        function checkOverflow(el) {
            if (isModeVisual || el.dataset.type === 'foto') return;
            const span = el.querySelector('.content-span');
            if (!span) return;
            span.style.width = 'auto';
            span.style.height = 'auto';
            const textoAlto = span.offsetHeight;
            const textoAncho = span.offsetWidth;
            span.style.width = '100%';
            span.style.height = '100%';
            const cajaAlto = el.clientHeight;
            const cajaAncho = el.clientWidth;
            if (textoAlto > cajaAlto || textoAncho > cajaAncho) el.classList.add('overflow-warning');
            else el.classList.remove('overflow-warning');
        }

        function restoreElement(data, canvas) {
            const id = isModeVisual && canvas.id !== 'credencial-canvas' ? canvas.id + '_' + data.id : data.id;
            const el = document.createElement('div');
            el.id = id;
            el.className = 'draggable-item ' + (data.isLabel ? 'is-label' : '');
            el.dataset.type = data.type;
            el.dataset.originalId = data.id;
            el.dataset.x = data.x;
            el.dataset.y = data.y;
            el.dataset.parentId = data.parentId ? (isModeVisual && canvas.id !== 'credencial-canvas' ? canvas.id + '_' +
                data.parentId : data.parentId) : '';

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

            if (isModeVisual && canvas.id !== 'credencial-canvas') {
                if (data.type === 'nombre') textoFinal = canvas.dataset.nombre || textoFinal;
                if (data.type === 'matricula') textoFinal = canvas.dataset.matricula || textoFinal;
                if (data.type === 'nivel') textoFinal = canvas.dataset.nivel || textoFinal;
                if (data.type === 'grado') textoFinal = canvas.dataset.grado || textoFinal;
                if (data.type === 'ciclo') textoFinal = canvas.dataset.ciclo || textoFinal;
                if (data.type === 'sangre') textoFinal = canvas.dataset.sangre || textoFinal;
                if (data.type === 'foto') fotoUrl = canvas.dataset.foto;
            }

            if (data.type === 'foto') {
                el.style.padding = '0';
                el.style.border = isModeVisual ? 'none' : '1px dashed #ccc';
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.justifyContent = 'center';
                el.style.overflow = 'hidden';

                if (isModeVisual) {
                    // BLINDAJE DE FOTOS: Cover para evitar huecos blancos (bordes cortados)
                    span.innerHTML = fotoUrl ?
                        `<img src="${fotoUrl}" style="width:100%; height:100%; object-fit:cover; display:block; margin:0; padding:0;">` :
                        `<div style="width:100%; height:100%; background:transparent;"></div>`;
                } else {
                    span.innerHTML =
                        `<div style="width:100%; height:100%; background:#f8f9fa; display:flex; align-items:center; justify-content:center;"><i class="fa fa-camera fa-3x" style="color:#bdc3c7"></i></div>`;
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
            const span = el.querySelector('.content-span');
            const isFoto = el.dataset.type === 'foto';
            const propText = document.getElementById('prop-text');
            propText.value = isFoto ? 'Elemento de Imagen' : span.innerText;
            propText.disabled = isFoto;
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
            if (selected.dataset.type !== 'foto') {
                span.innerText = document.getElementById('prop-text').value;
            }
            selected.style.fontSize = document.getElementById('prop-size').value + 'px';
            document.getElementById('txt-size').innerText = document.getElementById('prop-size').value;
            const colorHex = document.getElementById('prop-color').value;
            selected.style.color = colorHex;
            span.style.setProperty('color', colorHex, 'important');
            selected.style.fontFamily = document.getElementById('prop-font').value;
            checkOverflow(selected);
            const p = selected.dataset.parentId ? document.getElementById(selected.dataset.parentId) : selected;
            drawGroupOutline(p, document.getElementById('credencial-canvas'));
            markAsUnsaved();
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

        function deleteEl(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
            deselect();
            markAsUnsaved();
        }

        function addElement(type, text) {
            if (isModeVisual) return;
            const id = 'el_' + Date.now();
            restoreElement({
                id: id,
                type: type,
                x: 30,
                y: 30,
                text: text,
                fontSize: '14px',
                color: (type === 'sangre') ? '#e74c3c' : '#033b8a',
                width: 'auto',
                height: 'auto',
                textAlign: 'center',
                fontWeight: 'bold',
                fontStyle: 'normal',
                fontFamily: "'Montserrat', sans-serif",
                isLabel: (type === 'label')
            }, document.getElementById('credencial-canvas'));
            selectEl(document.getElementById(id));
            markAsUnsaved();
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
            if (anchorPending.dir === 'right') nX = pX + pW + 10;
            else if (anchorPending.dir === 'bottom') nY = pY + pH + 5;
            addElement(type, text);
            const child = selected;
            child.dataset.parentId = anchorPending.parentId;
            updatePos(child, nX, nY);
            $('#modalAnclaje').modal('hide');
            drawGroupOutline(parent, document.getElementById('credencial-canvas'));
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
            drawGroupOutline(p, document.getElementById('credencial-canvas'));
            checkOverflow(selected);
            markAsUnsaved();
        }

        function saveAll() {
            if (isModeVisual) return;
            const elementos = [];

            document.querySelectorAll('#credencial-canvas .draggable-item').forEach(el => {
                const span = el.querySelector('.content-span');
                let textoParaGuardar = '';
                if (el.classList.contains('is-label')) {
                    textoParaGuardar = span.innerText;
                } else {
                    textoParaGuardar = el.dataset.type.toUpperCase();
                }

                elementos.push({
                    id: el.id,
                    parentId: el.dataset.parentId || null,
                    type: el.dataset.type,
                    x: el.dataset.x,
                    y: el.dataset.y,
                    width: el.style.width,
                    height: el.style.height,
                    fontSize: el.style.fontSize,
                    color: span.style.color,
                    text: textoParaGuardar,
                    isLabel: el.classList.contains('is-label'),
                    textAlign: el.style.textAlign || 'left',
                    fontWeight: el.style.fontWeight || 'normal',
                    fontStyle: el.style.fontStyle || 'normal',
                    fontFamily: el.style.fontFamily || 'Roboto, sans-serif'
                });
            });

            const fData = new FormData();
            fData.append('configuracion', JSON.stringify(elementos));
            fData.append('_token', "{{ csrf_token() }}");
            if (imagenTemporal) fData.append('fondo', imagenTemporal);

            fetch("{{ route('credenciales.updateConfig', $diseno->id) }}", {
                    method: "POST",
                    body: fData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("¡Guardado con éxito! Tu lote está listo para imprimir sin perder datos.");
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
            const reader = new FileReader();
            const file = e.target.files[0];
            if (file) {
                imagenTemporal = file;
                reader.onload = (ev) => {
                    const imgEditor = document.getElementById('img-fondo-editor');
                    if (imgEditor) {
                        imgEditor.src = ev.target.result;
                        imgEditor.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
                markAsUnsaved();
            }
        };
    </script>
@endsection
