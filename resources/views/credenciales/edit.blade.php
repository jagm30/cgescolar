@extends('layouts.master')
@section('page_title', 'Diseñador: ' . $diseno->nombre)

@section('content')
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&family=Oswald:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        /* Animación para cuando hay cambios sin guardar */
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
            min-height: 800px;
            position: relative;
        }

        #credencial-canvas {
            background-color: white;
            background-image: url('{{ $diseno->fondo_anverso ? asset('storage/' . $diseno->fondo_anverso) : '' }}');

            /* CAMBIO DE INGENIERÍA: */
            /* En lugar de 100%, le damos un 102% para que el azul 'bañe' los bordes */
            background-size: 102% 102%;
            background-position: center;
            /* Centra la imagen para que el exceso sea igual en todos lados */
            background-repeat: no-repeat;

            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            width: {{ $diseno->orientacion == 'vertical' ? '320px' : '500px' }};
            height: {{ $diseno->orientacion == 'vertical' ? '500px' : '320px' }};
            overflow: hidden;
            /* Esto 'corta' el exceso de la imagen para que tú lo veas limpio */
        }

        .draggable-item {
            position: absolute;
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
            border: 1px solid #3498db;
            background: rgba(52, 152, 219, 0.1);
            z-index: 100;
        }

        .group-outline {
            position: absolute;
            border: 1px solid rgba(52, 152, 219, 0.4);
            background: rgba(52, 152, 219, 0.05);
            pointer-events: none;
            display: none;
            z-index: 5;
        }

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
    </style>

    <div class="row">
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Acciones</h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-toggle="modal" data-target="#modalHelp">
                            <i class="fa fa-question-circle"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <a href="{{ route('credenciales.preview', [$diseno->id, 1]) }}" target="_blank"
                        class="btn btn-warning btn-block text-bold" style="margin-bottom: 15px;">
                        <i class="fa fa-eye"></i> Vista Previa
                    </a>

                    <button class="btn btn-primary btn-block text-left" onclick="addElement('label', 'Etiqueta:')">
                        <i class="fa fa-tag"></i> <b>Añadir Etiqueta Fija</b>
                    </button>

                    <hr>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('nombre', 'ALBERTO SAMAYOA')"><i
                            class="fa fa-user"></i> Nombre</button>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('matricula', '2026-0001')"><i
                            class="fa fa-barcode"></i> Matrícula</button>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('nivel', 'PREPARATORIA')"><i
                            class="fa fa-university"></i> Nivel Escolar</button>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('grado', '1° SEMESTRE - A')"><i
                            class="fa fa-graduation-cap"></i> Grado y Grupo</button>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('ciclo', '2025-2026')"><i
                            class="fa fa-calendar"></i> Ciclo Escolar</button>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('sangre', 'O+')"><i
                            class="fa fa-tint"></i> Tipo de Sangre</button>
                    <button class="btn btn-default btn-block text-left" onclick="addElement('foto', 'FOTO')"><i
                            class="fa fa-camera"></i> Foto Alumno</button>
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
                    <label>Texto:</label>
                    <input type="text" id="prop-text" class="form-control" oninput="updateLive()"><br>

                    <label>Tipografía:</label>
                    <select id="prop-font" class="form-control" onchange="updateLive()">
                        <option value="Roboto, sans-serif">Roboto</option>
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
                            <input type="range" id="prop-size" min="6" max="100" oninput="updateLive()">
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
                <div id="credencial-canvas" onclick="deselect(event)">
                    <div id="group-outline" class="group-outline"></div>
                    <div id="guide-v" class="guide-line guide-v"></div>
                    <div id="guide-h" class="guide-line guide-h"></div>
                </div>
            </div>
        </div>
    </div>

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
                            <td>Mover (1px)</td>
                        </tr>
                        <tr>
                            <td><kbd>Shift + Flechas</kbd></td>
                            <td>Mover (10px) / Snap Magnético</td>
                        </tr>
                        <tr>
                            <td><kbd>Supr / Backspace</kbd></td>
                            <td>Eliminar elemento</td>
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
                    <h4>Anclar Dato BD</h4>
                    <button class="btn btn-default btn-block"
                        onclick="confirmAnchor('nombre', 'ALBERTO SAMAYOA')">Nombre</button>
                    <button class="btn btn-default btn-block"
                        onclick="confirmAnchor('matricula', '2026-0001')">Matrícula</button>
                    <button class="btn btn-default btn-block" onclick="confirmAnchor('grado', '1° SEMESTRE - A')">Grado y
                        Grupo</button>
                    <button class="btn btn-default btn-block" onclick="confirmAnchor('sangre', 'O+')">Sangre</button>
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

        // LÓGICA DE ALERTA DE CAMBIOS (A prueba de balas)
        function markAsUnsaved() {
            if (!unsavedChanges) { // Solo ejecutar si no estaba ya marcado
                unsavedChanges = true;
                const btn = document.getElementById('btn-save-design');
                if (btn) {
                    // Forzamos el cambio de clases directo
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
            if (unsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const configInicial = @json($diseno->config_anverso ?? []);
            if (configInicial && configInicial.length > 0) {
                configInicial.forEach(item => restoreElement(item));
            }
        });

        function checkOverflow(el) {
            const span = el.querySelector('.content-span');
            if (!span) return;

            // 1. TRUCO DE INGENIERÍA: Le quitamos el tamaño forzado temporalmente
            span.style.width = 'auto';
            span.style.height = 'auto';

            // 2. Medimos cuánto miden las letras puras (texto REAL)
            const textoAlto = span.offsetHeight;
            const textoAncho = span.offsetWidth;

            // 3. Restauramos los tamaños al 100% para que el arrastre siga funcionando
            span.style.width = '100%';
            span.style.height = '100%';

            // 4. Medimos el espacio interno de la caja azul (el contenedor)
            // Le restamos unos 4px de tolerancia por si las letras rozan el borde
            const cajaAlto = el.clientHeight;
            const cajaAncho = el.clientWidth;

            // 5. El veredicto infalible:
            if (textoAlto > cajaAlto || textoAncho > cajaAncho) {
                el.classList.add('overflow-warning');
            } else {
                el.classList.remove('overflow-warning');
            }
        }

        function restoreElement(data) {
            const id = data.id,
                el = document.createElement('div');
            el.id = id;
            el.className = 'draggable-item ' + (data.isLabel ? 'is-label' : '');
            el.dataset.type = data.type;
            el.dataset.parentId = data.parentId || '';
            el.dataset.x = data.x;
            el.dataset.y = data.y;

            el.style.transform = `translate(${data.x}px, ${data.y}px)`;
            el.style.fontSize = data.fontSize || '14px';
            el.style.color = data.color || '#000000';
            el.style.width = data.width || 'auto';
            el.style.height = data.height || 'auto';
            el.style.textAlign = data.textAlign || 'left';
            el.style.fontWeight = data.fontWeight || 'normal';
            el.style.fontStyle = data.fontStyle || 'normal';
            el.style.fontFamily = data.fontFamily || 'Roboto, sans-serif';

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

            const span = document.createElement('span');
            span.className = 'content-span';
            if (data.type === 'foto') {
                el.style.background = '#eee';
                el.style.border = '1px solid #999';
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.justifyContent = 'center';
                span.innerHTML = '<i class="fa fa-user fa-3x"></i>';
            } else {
                span.innerText = data.text;
            }
            el.appendChild(span);

            el.onclick = (e) => {
                e.stopPropagation();
                selectEl(el);
            };
            document.getElementById('credencial-canvas').appendChild(el);
            initInteract(el);
            setTimeout(() => checkOverflow(el), 100);
        }

        function initInteract(el) {
            let canvasW = document.getElementById('credencial-canvas').offsetWidth;
            let canvasH = document.getElementById('credencial-canvas').offsetHeight;
            let cachedItems = [];

            interact(el).draggable({
                inertia: false,
                autoScroll: true,
                listeners: {
                    start(event) {
                        cachedItems = [];
                        document.querySelectorAll('.draggable-item').forEach(item => {
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

                        const gV = document.getElementById('guide-v');
                        const gH = document.getElementById('guide-h');

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

                        document.querySelectorAll(`[data-parent-id="${target.id}"]`).forEach(c => {
                            updatePos(c, parseFloat(c.dataset.x) + effectiveDx, parseFloat(c.dataset.y) +
                                effectiveDy);
                        });

                        const p = target.dataset.parentId ? document.getElementById(target.dataset.parentId) :
                            target;
                        drawGroupOutline(p);
                        checkOverflow(target);

                        // DISPARA ALERTA AL MOVER
                        markAsUnsaved();
                    },
                    end() {
                        document.getElementById('guide-v').style.display = 'none';
                        document.getElementById('guide-h').style.display = 'none';
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

                        // DISPARA ALERTA AL REDIMENSIONAR
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

        function selectEl(el) {
            deselect();
            selected = el;
            el.classList.add('selected');
            document.getElementById('panel-edicion').style.display = 'block';
            document.getElementById('prop-text').value = el.querySelector('.content-span').innerText;
            document.getElementById('prop-size').value = parseInt(el.style.fontSize) || 14;
            document.getElementById('prop-color').value = rgbToHex(el.style.color) || '#000000';
            updateUIButtons();
        }

        function updateLive() {
            if (!selected) return;
            const span = selected.querySelector('.content-span');
            const inputSize = document.getElementById('prop-size');
            const txtSize = document.getElementById('txt-size');
            const inputFont = document.getElementById('prop-font');

            span.innerText = document.getElementById('prop-text').value;
            selected.style.fontSize = inputSize.value + 'px';
            selected.style.color = document.getElementById('prop-color').value;
            if (inputFont) selected.style.fontFamily = inputFont.value;
            if (txtSize) txtSize.innerText = inputSize.value;

            checkOverflow(selected);
            const p = selected.dataset.parentId ? document.getElementById(selected.dataset.parentId) : selected;
            drawGroupOutline(p);

            // DISPARA ALERTA AL CAMBIAR PROPIEDADES
            markAsUnsaved();
        }

        function deselect(e) {
            if (e && e.target.id !== 'credencial-canvas') return;
            document.querySelectorAll('.draggable-item').forEach(i => i.classList.remove('selected'));
            selected = null;
            document.getElementById('panel-edicion').style.display = 'none';
            document.getElementById('group-outline').style.display = 'none';
        }

        function deleteEl(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
            deselect();
            markAsUnsaved();
        }

        function addElement(type, text) {
            const id = 'el_' + Date.now();
            restoreElement({
                id: id,
                type: type,
                x: 30,
                y: 30,
                text: text,
                fontSize: '14px',
                color: '#000000',
                width: 'auto',
                height: 'auto',
                textAlign: 'left',
                fontWeight: 'normal',
                fontStyle: 'normal',
                fontFamily: 'Roboto, sans-serif',
                isLabel: (type === 'label')
            });
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
            drawGroupOutline(parent);
            markAsUnsaved();
        }

        function drawGroupOutline(parentEl) {
            if (!parentEl) return;
            const children = document.querySelectorAll(`[data-parent-id="${parentEl.id}"]`),
                outline = document.getElementById('group-outline');
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

        function saveAll() {
            const elementos = [];
            document.querySelectorAll('.draggable-item').forEach(el => {
                elementos.push({
                    id: el.id,
                    parentId: el.dataset.parentId || null,
                    type: el.dataset.type,
                    x: el.dataset.x,
                    y: el.dataset.y,
                    width: el.style.width,
                    height: el.style.height,
                    fontSize: el.style.fontSize,
                    color: el.style.color,
                    text: el.querySelector('.content-span').innerText,
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
            }).then(r => r.json()).then(data => {
                if (data.status === 'success') {
                    alert("Guardado con éxito");
                    markAsSaved();
                }
            });
        }

        window.addEventListener('keydown', function(e) {
            if (!selected || e.target.tagName === 'INPUT') return;
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
                    deleteEl(selected.id);
                    break;
                case 'Escape':
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
            drawGroupOutline(p);
            checkOverflow(selected);

            // DISPARA ALERTA CON TECLADO
            markAsUnsaved();
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
                reader.onload = (ev) => document.getElementById('credencial-canvas').style.backgroundImage =
                    `url(${ev.target.result})`;
                reader.readAsDataURL(file);
                // DISPARA ALERTA AL SUBIR FONDO
                markAsUnsaved();
            }
        };
    </script>
@endsection
