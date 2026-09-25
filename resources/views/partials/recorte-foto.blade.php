{{--
    Recortador de fotografías reutilizable.

    Uso: añade el atributo `data-recorte` a cualquier <input type="file"> e incluye
    este parcial una sola vez dentro de la vista. Al seleccionar una imagen se abre
    el modal de recorte y, al confirmar, el archivo original del input se sustituye
    por el JPG recortado (600x800) antes de que corran los demás manejadores.

    Atributos opcionales en el input:
      data-recorte-ancho / data-recorte-alto  → tamaño de salida (por defecto 600x800)
--}}

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
        <style>
            #modal-recorte {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                z-index: 10500;
                background: rgba(0,0,0,.92);
                flex-direction: column;
                -webkit-overflow-scrolling: touch;
            }
            #modal-recorte.visible { display: flex; }

            .rf-header {
                padding: 12px 16px;
                background: #1a2634;
                color: #fff;
                flex-shrink: 0;
            }
            .rf-header-title { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
            .rf-header-hint  { font-size: 11px; color: #94a3b8; line-height: 1.4; }

            .rf-body {
                flex: 1;
                min-height: 0;
                overflow: hidden;
                position: relative;
                background: #111;
            }
            .rf-body img { display: block; max-width: 100%; max-height: 100%; }

            .rf-footer {
                padding: 12px 16px;
                background: #1a2634;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                flex-shrink: 0;
                padding-bottom: max(12px, env(safe-area-inset-bottom));
            }
            .rf-btn {
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 700;
                border: none;
                cursor: pointer;
                min-height: 44px;
                -webkit-tap-highlight-color: transparent;
            }
            .rf-btn-cancel  { background: #374151; color: #d1d5db; }
            .rf-btn-cancel:hover  { background: #4b5563; }
            .rf-btn-confirm { background: #16a34a; color: #fff; }
            .rf-btn-confirm:hover { background: #15803d; }

            @media (max-width: 400px) {
                .rf-footer { flex-direction: column-reverse; }
                .rf-btn    { width: 100%; text-align: center; }
            }
        </style>
    @endpush

    <div id="modal-recorte">
        <div class="rf-header">
            <div class="rf-header-title"><i class="fa fa-crop"></i> Recorta la fotografía</div>
            <div class="rf-header-hint">
                Ajusta el recuadro para encuadrar el rostro. Usa los bordes para ampliar o reducir el área.
            </div>
        </div>
        <div class="rf-body">
            <img id="recorte-img" src="" alt="Imagen a recortar">
        </div>
        <div class="rf-footer">
            <button type="button" class="rf-btn rf-btn-cancel" id="btn-recorte-cancelar">
                <i class="fa fa-times"></i> Cancelar
            </button>
            <button type="button" class="rf-btn rf-btn-confirm" id="btn-recorte-confirmar">
                <i class="fa fa-check"></i> Recortar y usar
            </button>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
        <script>
            (function () {
                var TIPOS_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];
                var MAX_BYTES = 2 * 1024 * 1024;

                var cropper = null;
                var inputActivo = null;
                var nombreOriginal = '';

                // Fase de captura: corre antes que los manejadores de vista previa,
                // de modo que esos reciban ya el archivo recortado.
                document.addEventListener('change', function (ev) {
                    var input = ev.target;

                    if (!input || input.type !== 'file' || !input.hasAttribute('data-recorte')) return;

                    if (input.dataset.recorteListo === '1') {
                        delete input.dataset.recorteListo;
                        return;
                    }

                    var archivo = input.files && input.files[0];
                    if (!archivo) return;

                    ev.stopImmediatePropagation();
                    ev.preventDefault();

                    if (TIPOS_PERMITIDOS.indexOf(archivo.type) === -1) {
                        input.value = '';
                        alert('Solo se permiten imágenes JPG, PNG o WEBP.');
                        return;
                    }

                    if (archivo.size > MAX_BYTES) {
                        input.value = '';
                        alert('El archivo pesa ' + (archivo.size / 1024 / 1024).toFixed(2) +
                            ' MB.\nEl máximo permitido es 2 MB.');
                        return;
                    }

                    inputActivo = input;
                    nombreOriginal = archivo.name;
                    abrirRecorte(archivo);
                }, true);

                function abrirRecorte(archivo) {
                    var lector = new FileReader();

                    lector.onload = function (e) {
                        var img = document.getElementById('recorte-img');

                        destruirCropper();
                        img.src = e.target.result;

                        document.body.style.overflow = 'hidden';
                        document.getElementById('modal-recorte').classList.add('visible');

                        // Un frame de espera para que el modal tenga dimensiones reales.
                        requestAnimationFrame(function () {
                            cropper = new Cropper(img, {
                                aspectRatio: 3 / 4,
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 0.85,
                                responsive: true,
                                restore: false,
                                guides: true,
                                center: true,
                                highlight: false,
                                cropBoxMovable: true,
                                cropBoxResizable: true,
                                toggleDragModeOnDblclick: false,
                            });
                        });
                    };

                    lector.readAsDataURL(archivo);
                }

                document.getElementById('btn-recorte-cancelar').addEventListener('click', function () {
                    if (inputActivo) inputActivo.value = '';
                    cerrarRecorte();
                });

                document.getElementById('btn-recorte-confirmar').addEventListener('click', function () {
                    if (!cropper || !inputActivo) return;

                    var input = inputActivo;
                    var base = nombreOriginal.replace(/\.[^.]+$/, '') || 'foto';
                    var canvas = cropper.getCroppedCanvas({
                        width: Number(input.dataset.recorteAncho) || 600,
                        height: Number(input.dataset.recorteAlto) || 800,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob(function (blob) {
                        var transferencia = new DataTransfer();

                        transferencia.items.add(new File([blob], base + '.jpg', { type: 'image/jpeg' }));

                        input.dataset.recorteListo = '1';
                        input.files = transferencia.files;
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }, 'image/jpeg', 0.92);

                    cerrarRecorte();
                });

                function cerrarRecorte() {
                    document.getElementById('modal-recorte').classList.remove('visible');
                    document.body.style.overflow = '';
                    destruirCropper();
                    inputActivo = null;
                    nombreOriginal = '';
                }

                function destruirCropper() {
                    if (!cropper) return;
                    cropper.destroy();
                    cropper = null;
                }
            })();
        </script>
    @endpush
@endonce
