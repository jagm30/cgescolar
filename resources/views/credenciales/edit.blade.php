@extends('layouts.master')
@section('page_title', 'Diseñador: ' . $diseno->nombre)

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Roboto&family=Montserrat&family=Oswald&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
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
            background-size: 100% 100%;
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            width: {{ $diseno->orientacion == 'vertical' ? '320px' : '500px' }};
            height: {{ $diseno->orientacion == 'vertical' ? '500px' : '320px' }};
            overflow: hidden;
        }

        .draggable-item {
            position: absolute;
            cursor: move;
            user-select: none;
            padding: 4px 8px;
            border: 1px dashed #ccc;
            white-space: normal;
            word-wrap: break-word;
            display: inline-block;
            line-height: 1.2;
            min-width: 40px;
            z-index: 10;
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
            font-size: 10px;
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
                    <div class="pull-right">
                        <a href="{{ route('credenciales.preview', [$diseno->id, 1]) }}" target="_blank"
                            class="btn btn-warning">
                            <i class="fa fa-eye"></i> Vista Previa con Alumno #1
                        </a>
                    </div>
                    <h3 class="box-title">Datos Alumno</h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-toggle="modal" data-target="#modalHelp"
                            title="Atajos de teclado"><i class="fa fa-question-circle"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <button class="btn btn-primary btn-block text-left" onclick="addElement('label', 'Etiqueta:')"><i
                            class="fa fa-tag"></i> <b>Añadir Etiqueta Fija</b></button>
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

            <div id="panel-edicion" class="box box-warning" style="display: none;">
                <div class="box-header with-border">
                    <h3 class="box-title">Propiedades</h3>
                </div>
                <div class="box-body">
                    <label>Texto:</label><input type="text" id="prop-text" class="form-control"
                        oninput="updateLive()"><br>
                    <label>Tamaño: <span id="txt-size">14</span>px</label><input type="range" id="prop-size"
                        min="8" max="70" oninput="updateLive()"><br>
                    <label>Color:</label><input type="color" id="prop-color" class="form-control"
                        onchange="updateLive()"><br>
                    <label>Alineación:</label>
                    <div class="btn-group btn-group-justified">
                        <a class="btn btn-default btn-sm" onclick="setAlign('left')" title="Izquierda"><i
                                class="fa fa-align-left"></i></a>
                        <a class="btn btn-default btn-sm" onclick="setAlign('center')" title="Centro"><i
                                class="fa fa-align-center"></i></a>
                        <a class="btn btn-default btn-sm" onclick="setAlign('right')" title="Derecha"><i
                                class="fa fa-align-right"></i></a>
                    </div>
                </div>
            </div>
            <button class="btn btn-success btn-block btn-lg btn-flat" onclick="saveAll()"><i class="fa fa-save"></i> GUARDAR
                DISEÑO</button>
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
                            <td><kbd>Shift+Flechas</kbd></td>
                            <td>Mover (10px)</td>
                        </tr>
                        <tr>
                            <td><kbd>Supr</kbd></td>
                            <td>Eliminar</td>
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
                        onclick="confirmAnchor('nombre', 'NOMBRE ALUMNO')">Nombre</button>
                    <button class="btn btn-default btn-block"
                        onclick="confirmAnchor('matricula', '2026-0001')">Matrícula</button>
                    <button class="btn btn-default btn-block"
                        onclick="confirmAnchor('nivel', 'PREPARATORIA')">Nivel</button>
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
        let imagenTemporal = null; // Para guardar la imagen antes de subirla

        // 1. CARGA INICIAL (RECUPERAR LO GUARDADO)
        document.addEventListener('DOMContentLoaded', function() {
            const configInicial = @json($diseno->config_anverso ?? []);
            if (configInicial && configInicial.length > 0) {
                configInicial.forEach(item => restoreElement(item));
            }
        });

        function restoreElement(data) {
            const id = data.id,
                el = document.createElement('div');
            el.id = id;
            el.className = 'draggable-item ' + (data.isLabel ? 'is-label' : '');
            if (data.parentId) el.dataset.parentId = data.parentId;
            el.dataset.type = data.type;
            el.dataset.x = data.x;
            el.dataset.y = data.y;
            el.style.transform = `translate(${data.x}px, ${data.y}px)`;
            el.style.fontSize = data.fontSize;
            el.style.color = data.color;
            el.style.width = data.width;
            el.style.height = data.height;
            el.style.textAlign = data.textAlign;

            if (data.type === 'foto') {
                el.style.background = '#eee';
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.justifyContent = 'center';
                el.style.border = '1px solid #999';
            }
            if (data.isLabel) {
                el.innerHTML =
                    `<div class="node node-top" onclick="openAnchor('${id}', 'top')">+</div><div class="node node-bottom" onclick="openAnchor('${id}', 'bottom')">+</div><div class="node node-left" onclick="openAnchor('${id}', 'left')">+</div><div class="node node-right" onclick="openAnchor('${id}', 'right')">+</div>`;
            }
            el.innerHTML += `<div class="btn-del" onclick="deleteEl('${id}')">×</div>`;
            const span = document.createElement('span');
            span.className = 'content-span';
            span.innerHTML = (data.type === 'foto') ? '<i class="fa fa-user fa-3x"></i>' : data.text;
            el.appendChild(span);
            el.onclick = (e) => {
                e.stopPropagation();
                selectEl(el);
            };
            document.getElementById('credencial-canvas').appendChild(el);
            initInteract(el);
        }

        // 2. FILEREADER (TU LÓGICA QUE SÍ FUNCIONA)
        document.getElementById('inputFondo').onchange = (e) => {
            const reader = new FileReader();
            const file = e.target.files[0];
            if (file) {
                imagenTemporal = file; // La guardamos para el saveAll
                reader.onload = (ev) => document.getElementById('credencial-canvas').style.backgroundImage =
                    `url(${ev.target.result})`;
                reader.readAsDataURL(file);
            }
        };

        // 3. FUNCIÓN DE GUARDADO (JSON + FONDO)
        function saveAll() {
            const elementos = [];
            document.querySelectorAll('.draggable-item').forEach(el => {
                // --- LÓGICA DE INGENIERO PARA EL TEXTO ---
                let textoAGuardar = el.querySelector('.content-span').innerText;
                const tipo = el.dataset.type;

                // Si es un campo dinámico, guardamos la "Llave" para que el Preview sepa qué inyectar
                // Esto evita que se quede grabado el nombre de prueba "ALBERTO SAMAYOA"
                if (tipo === 'nombre') textoAGuardar = 'Nombre';
                else if (tipo === 'matricula') textoAGuardar = 'Matrícula';
                else if (tipo === 'tipo_sangre') textoAGuardar = 'Tipo de sangre';
                else if (tipo === 'grado_grupo') textoAGuardar = 'Grado y Grupo';
                // Si no es ninguno de estos, se queda con lo que tenga el innerText (etiquetas fijas)

                elementos.push({
                    id: el.id,
                    parentId: el.dataset.parentId || null,
                    type: tipo,
                    x: el.dataset.x,
                    y: el.dataset.y,
                    width: el.style.width,
                    height: el.style.height,
                    fontSize: el.style.fontSize,
                    color: el.style.color,
                    textAlign: el.style.textAlign,
                    text: textoAGuardar, // <--- Guardamos la etiqueta limpia
                    isLabel: el.classList.contains('is-label')
                });
            });

            if (elementos.length === 0 && !imagenTemporal) {
                alert("No hay nada que guardar, mano.");
                return;
            }

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
                .then(response => response.json())
                .then(data => {
                    console.log("Respuesta:", data);
                    if (data.status === 'success') {
                        alert("¡Configuración de La Salle guardada correctamente!");
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error técnico:", error);
                    alert("Error de red o servidor.");
                });
        }
        // --- ABAJO TODA TU LÓGICA DE MOVIMIENTO, TECLADO E INTERACT (SIN TOCAR) ---
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
            document.querySelectorAll(`[data-parent-id="${selected.id}"]`).forEach(c => updatePos(c, parseFloat(c.dataset
                .x) + dx, parseFloat(c.dataset.y) + dy));
            const p = selected.dataset.parentId ? document.getElementById(selected.dataset.parentId) : selected;
            drawGroupOutline(p);
        }

        function addElement(type, text) {
            const id = 'el_' + Date.now(),
                el = document.createElement('div');
            el.id = id;
            el.className = 'draggable-item';
            el.dataset.type = type;
            el.dataset.x = 30;
            el.dataset.y = 30;
            el.style.transform = 'translate(30px, 30px)';
            el.style.fontSize = '14px';
            if (type === 'label') {
                el.classList.add('is-label');
                el.innerHTML =
                    `<div class="node node-top" onclick="openAnchor('${id}', 'top')">+</div><div class="node node-bottom" onclick="openAnchor('${id}', 'bottom')">+</div><div class="node node-left" onclick="openAnchor('${id}', 'left')">+</div><div class="node node-right" onclick="openAnchor('${id}', 'right')">+</div>`;
            }
            el.innerHTML += `<div class="btn-del" onclick="deleteEl('${id}')">×</div>`;
            const span = document.createElement('span');
            span.className = 'content-span';
            span.innerHTML = (type === 'foto') ? '<i class="fa fa-user fa-3x"></i>' : text;
            el.appendChild(span);
            if (type === 'foto') {
                el.style.width = '100px';
                el.style.height = '120px';
                el.style.background = '#eee';
                el.style.textAlign = 'center';
            }
            el.onclick = (e) => {
                e.stopPropagation();
                selectEl(el);
            };
            document.getElementById('credencial-canvas').appendChild(el);
            initInteract(el);
            selectEl(el);
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
            else if (anchorPending.dir === 'left') nX = pX - 150;
            else if (anchorPending.dir === 'bottom') nY = pY + pH + 5;
            else if (anchorPending.dir === 'top') nY = pY - 35;
            addElement(type, text);
            const child = document.getElementById(selected.id);
            child.dataset.parentId = anchorPending.parentId;
            updatePos(child, nX, nY);
            $('#modalAnclaje').modal('hide');
            drawGroupOutline(parent);
        }

        function initInteract(el) {
            let cachedItems = [],
                canvasW = 0,
                canvasH = 0;
            interact(el).draggable({
                listeners: {
                    start(event) {
                        const canvas = document.getElementById('credencial-canvas');
                        canvasW = canvas.offsetWidth;
                        canvasH = canvas.offsetHeight;
                        cachedItems = [];
                        document.querySelectorAll('.draggable-item').forEach(item => {
                            if (item !== event.target) cachedItems.push({
                                x: parseFloat(item.dataset.x),
                                y: parseFloat(item.dataset.y),
                                w: item.offsetWidth,
                                h: item.offsetHeight,
                                midX: parseFloat(item.dataset.x) + item.offsetWidth / 2,
                                midY: parseFloat(item.dataset.y) + item.offsetHeight / 2
                            });
                        });
                    },
                    move(event) {
                        const target = event.target;
                        let x = (parseFloat(target.dataset.x) || 0) + event.dx,
                            y = (parseFloat(target.dataset.y) || 0) + event.dy;
                        const threshold = 8,
                            tW = target.offsetWidth,
                            tH = target.offsetHeight,
                            tMidX = x + tW / 2,
                            tMidY = y + tH / 2;
                        let showV = false,
                            showH = false,
                            gX = 0,
                            gY = 0;
                        if (Math.abs(tMidX - canvasW / 2) < threshold) {
                            if (event.shiftKey) x = canvasW / 2 - tW / 2;
                            gX = canvasW / 2;
                            showV = true;
                        }
                        if (Math.abs(tMidY - canvasH / 2) < threshold) {
                            if (event.shiftKey) y = canvasH / 2 - tH / 2;
                            gY = canvasH / 2;
                            showH = true;
                        }
                        cachedItems.forEach(item => {
                            if (Math.abs(tMidX - item.midX) < threshold) {
                                if (event.shiftKey) x = item.midX - tW / 2;
                                gX = item.midX;
                                showV = true;
                            }
                            if (Math.abs(x - item.x) < threshold) {
                                if (event.shiftKey) x = item.x;
                                gX = item.x;
                                showV = true;
                            }
                            if (Math.abs((x + tW) - (item.x + item.w)) < threshold) {
                                if (event.shiftKey) x = item.x + item.w - tW;
                                gX = item.x + item.w;
                                showV = true;
                            }
                            if (Math.abs(tMidY - item.midY) < threshold) {
                                if (event.shiftKey) y = item.midY - tH / 2;
                                gY = item.midY;
                                showH = true;
                            }
                            if (Math.abs(y - item.y) < threshold) {
                                if (event.shiftKey) y = item.y;
                                gY = item.y;
                                showH = true;
                            }
                            if (Math.abs((y + tH) - (item.y + item.h)) < threshold) {
                                if (event.shiftKey) y = item.y + item.h - tH;
                                gY = item.y + item.h;
                                showH = true;
                            }
                        });
                        const gv = document.getElementById('guide-v'),
                            gh = document.getElementById('guide-h');
                        gv.style.display = showV ? 'block' : 'none';
                        if (showV) gv.style.left = gX + 'px';
                        gh.style.display = showH ? 'block' : 'none';
                        if (showH) gh.style.top = gY + 'px';
                        const dx = x - parseFloat(target.dataset.x),
                            dy = y - parseFloat(target.dataset.y);
                        updatePos(target, x, y);
                        document.querySelectorAll(`[data-parent-id="${target.id}"]`).forEach(c => updatePos(c,
                            parseFloat(c.dataset.x) + dx, parseFloat(c.dataset.y) + dy));
                        const p = target.dataset.parentId ? document.getElementById(target.dataset.parentId) :
                            target;
                        drawGroupOutline(p);
                    },
                    end() {
                        document.getElementById('guide-v').style.display = 'none';
                        document.getElementById('guide-h').style.display = 'none';
                    }
                }
            }).resizable({
                edges: {
                    left: true,
                    right: true,
                    bottom: true,
                    top: true
                },
                margin: 8,
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
                        const p = event.target.dataset.parentId ? document.getElementById(event.target.dataset
                            .parentId) : event.target;
                        drawGroupOutline(p);
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
            deselect();
            selected = el;
            el.classList.add('selected');
            document.getElementById('panel-edicion').style.display = 'block';
            document.getElementById('prop-text').value = el.querySelector('.content-span').innerText;
            document.getElementById('prop-size').value = parseInt(el.style.fontSize);
            document.getElementById('txt-size').innerText = parseInt(el.style.fontSize);
            const p = el.dataset.parentId ? document.getElementById(el.dataset.parentId) : el;
            drawGroupOutline(p);
        }

        function updateLive() {
            if (!selected) return;
            selected.querySelector('.content-span').innerText = document.getElementById('prop-text').value;
            selected.style.fontSize = document.getElementById('prop-size').value + 'px';
            selected.style.color = document.getElementById('prop-color').value;
            document.getElementById('txt-size').innerText = document.getElementById('prop-size').value;
            const p = selected.dataset.parentId ? document.getElementById(selected.dataset.parentId) : selected;
            drawGroupOutline(p);
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
                const cx = parseFloat(c.dataset.x),
                    cy = parseFloat(c.dataset.y);
                minX = Math.min(minX, cx);
                minY = Math.min(minY, cy);
                maxX = Math.max(maxX, cx + c.offsetWidth);
                maxY = Math.max(maxY, cy + c.offsetHeight);
            });
            outline.style.display = 'block';
            outline.style.width = (maxX - minX + 10) + 'px';
            outline.style.height = (maxY - minY + 10) + 'px';
            outline.style.transform = `translate(${minX - 5}px, ${minY - 5}px)`;
        }

        function setAlign(a) {
            if (selected) selected.style.textAlign = a;
        }

        function deselect(e) {
            if (e && e.target.id !== 'credencial-canvas') return;
            document.querySelectorAll('.draggable-item').forEach(i => i.classList.remove('selected'));
            selected = null;
            document.getElementById('panel-edicion').style.display = 'none';
            document.getElementById('group-outline').style.display = 'none';
        }

        function deleteEl(id) {
            document.getElementById(id).remove();
            deselect();
        }
    </script>
@endsection
