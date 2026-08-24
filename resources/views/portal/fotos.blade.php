@extends('layouts.master')

@section('page_title', 'Fotografías')
@section('page_subtitle', 'Carga de fotografías')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Fotografías</li>
@endsection

@push('styles')
    @include('portal._styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        /* ── Cabecera ── */
        .ft-hero {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
            border-radius: 12px;
            padding: 18px 16px;
            margin-bottom: 16px;
            color: #fff;
        }
        .ft-hero-titulo {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .ft-hero-sub {
            font-size: 13px;
            color: rgba(255,255,255,.8);
            margin: 0;
        }

        /* ── Navegación ── */
        .ft-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none !important;
            border: 1px solid #dde3ea;
            background: #fff;
            color: #4a5568;
            margin-bottom: 16px;
            transition: background .15s;
        }
        .ft-nav-btn:hover { background: #f0f3f7; color: #1a2634; }

        /* ── Aviso formato ── */
        .ft-aviso {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #92400e;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .ft-aviso i { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

        /* ── Título de sección ── */
        .ft-section-title {
            font-size: 17px;
            font-weight: 800;
            color: #1a2634;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .ft-section-count {
            font-size: 12px;
            font-weight: 700;
            background: #e8f0fb;
            color: #3c8dbc;
            padding: 2px 10px;
            border-radius: 20px;
        }

        /* ── Lista de tarjetas (1 columna en móvil) ── */
        .ft-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        /* ── Tarjeta horizontal (móvil) ── */
        .ft-card {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .ft-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.08); }

        /* Lado izquierdo: avatar */
        .ft-card-avatar { flex-shrink: 0; }

        /* Lado derecho: nombre + botón */
        .ft-card-body { flex: 1; min-width: 0; }

        /* ── Avatar (proporción 3:4 igual que el recorte) ── */
        .ft-avatar-wrap { position: relative; width: 54px; height: 72px; }
        .ft-avatar {
            width: 54px; height: 72px;
            border-radius: 8px;
            object-fit: cover;
            border: 3px solid #e4eaf0;
            display: block;
            background: #f0f3f7;
        }
        .ft-avatar-placeholder {
            width: 54px; height: 72px;
            border-radius: 8px;
            background: #e8f0fb;
            color: #3c8dbc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            border: 3px solid #e4eaf0;
        }
        .ft-avatar-badge {
            position: absolute; bottom: 1px; right: 1px;
            width: 20px; height: 20px;
            border-radius: 50%;
            background: #00875a;
            border: 2px solid #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; color: #fff;
        }

        /* ── Nombre y subtítulo ── */
        .ft-nombre {
            font-size: 15px; font-weight: 700; color: #1a2634;
            margin: 0 0 2px; line-height: 1.3;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .ft-sub { font-size: 12px; color: #9aa5b4; margin: 0 0 10px; }

        /* ── Botón subir ── */
        .ft-label-upload {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            width: 100%; padding: 10px 12px;
            background: #3c8dbc; color: #fff;
            border-radius: 9px; font-size: 14px; font-weight: 700;
            cursor: pointer; transition: background .15s;
            border: none; line-height: 1.2; text-align: center; box-sizing: border-box;
        }
        .ft-label-upload:hover { background: #2a6e9e; }
        .ft-label-upload.subiendo {
            background: #7b8794; cursor: not-allowed; pointer-events: none;
        }
        .ft-input {
            display: none !important;
            position: absolute; width: 0; height: 0; opacity: 0;
        }

        /* ── Barra de progreso ── */
        .ft-progress {
            width: 100%; height: 6px;
            background: #e4eaf0; border-radius: 99px;
            margin-top: 8px; overflow: hidden; display: none;
        }
        .ft-progress-bar {
            height: 100%; width: 0;
            background: #3c8dbc; border-radius: 99px; transition: width .2s;
        }

        /* ── Mensajes ── */
        .ft-ok {
            display: none; margin-top: 6px;
            font-size: 12px; font-weight: 600; color: #00875a;
        }
        .ft-err {
            display: none; margin-top: 6px;
            font-size: 12px; color: #b91c1c; word-break: break-word;
        }

        /* ── Vacío ── */
        .ft-empty {
            text-align: center; padding: 36px 20px;
            background: #fff; border: 1px solid #e4eaf0;
            border-radius: 12px; color: #9aa5b4; margin-bottom: 20px;
        }
        .ft-empty i { font-size: 42px; color: #d1d9e0; display: block; margin-bottom: 10px; }
        .ft-empty p { font-size: 14px; margin: 0; }

        /* ── En pantallas grandes: volver a grid ── */
        @media (min-width: 600px) {
            .ft-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .ft-card { flex-direction: column; align-items: center; padding: 18px 14px 14px; text-align: center; }
            .ft-card-body { width: 100%; }
            .ft-nombre { white-space: normal; text-align: center; font-size: 13px; }
            .ft-sub { text-align: center; }
            .ft-avatar-wrap { width: 68px; height: 90px; margin: 0 auto 12px; }
            .ft-avatar { width: 68px; height: 90px; }
            .ft-avatar-placeholder { width: 68px; height: 90px; font-size: 32px; }
            .ft-ok { text-align: center; }
        }
        @media (min-width: 900px) {
            .ft-grid { grid-template-columns: repeat(3, 1fr); }
        }

        /* ════════════════════════════
           MODAL DE RECORTE
        ════════════════════════════ */
        #modal-crop {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;  /* inset sin soporte en Safari antiguo */
            z-index: 9999;
            background: rgba(0,0,0,.92);
            flex-direction: column;
            /* Evita que el scroll del body se cuele por debajo en iOS */
            -webkit-overflow-scrolling: touch;
        }
        #modal-crop.visible { display: flex; }

        .crop-header {
            padding: 12px 16px;
            background: #1a2634;
            color: #fff;
            flex-shrink: 0;
        }
        .crop-header-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .crop-header-hint {
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.4;
        }

        .crop-body {
            flex: 1;
            min-height: 0;          /* crítico: sin esto Safari/Firefox no respetan flex en columna */
            overflow: hidden;
            position: relative;
            background: #111;
        }
        /* Cropper.js necesita que la img ocupe el 100 % del contenedor */
        .crop-body img {
            display: block;
            max-width: 100%;
            max-height: 100%;
        }

        .crop-footer {
            padding: 12px 16px;
            background: #1a2634;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            flex-shrink: 0;
            /* Espacio extra para la barra de inicio de iOS (safe area) */
            padding-bottom: max(12px, env(safe-area-inset-bottom));
        }
        .crop-btn {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            /* Área táctil mínima recomendada por Apple/Google: 44px */
            min-height: 44px;
            -webkit-tap-highlight-color: transparent;
        }
        .crop-btn-cancel  { background: #374151; color: #d1d5db; }
        .crop-btn-cancel:hover  { background: #4b5563; }
        .crop-btn-confirm { background: #16a34a; color: #fff; }
        .crop-btn-confirm:hover { background: #15803d; }

        /* En pantallas muy pequeñas los botones ocupan todo el ancho */
        @media (max-width: 400px) {
            .crop-footer { flex-direction: column-reverse; }
            .crop-btn    { width: 100%; text-align: center; }
        }
    </style>
@endpush

@section('content')

    {{-- ── Cabecera ── --}}
    <div class="ft-hero">
        <div class="ft-hero-sub">Sube o actualiza las fotos de tus hijos y contactos familiares.</div>
    </div>

    {{-- ── Navegación ── --}}
    <a href="{{ route('portal.dashboard') }}" class="ft-nav-btn">
        <i class="fa fa-arrow-left"></i> Regresar al inicio
    </a>

    {{-- ── Aviso de formato ── --}}
    <div class="ft-aviso">
        <i class="fa fa-info-circle"></i>
        <div>
            Las fotos deben ser en formato <strong>JPG, PNG o WEBP</strong> y pesar
            <strong>máximo 2 MB</strong>. Después de seleccionar la imagen podrás
            <strong>recortarla para encuadrar el rostro</strong> antes de guardarla.
        </div>
    </div>

    {{-- ══════════════════════════
         ALUMNOS
    ══════════════════════════ --}}
    <div class="ft-section-title">
        <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
        Mis hijos
        <span class="ft-section-count">{{ $alumnos->count() }}</span>
    </div>

    @if ($alumnos->isEmpty())
        <div class="ft-empty">
            <i class="fa fa-users"></i>
            <p>No hay alumnos vinculados a tu familia.</p>
        </div>
    @else
        <div class="ft-grid">
            @foreach ($alumnos as $alumno)
                <div class="ft-card" id="alumno-card-{{ $alumno->id }}">

                    {{-- Avatar --}}
                    <div class="ft-card-avatar">
                        <div class="ft-avatar-wrap">
                            @if ($alumno->foto_url)
                                <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                                     alt="{{ $alumno->nombre }}"
                                     class="ft-avatar ft-img">
                                <div class="ft-avatar-badge"><i class="fa fa-check"></i></div>
                            @else
                                <div class="ft-avatar-placeholder ft-placeholder">
                                    <i class="fa fa-user"></i>
                                </div>
                                <img src="" alt="" class="ft-avatar ft-img" style="display:none;">
                            @endif
                        </div>
                    </div>

                    {{-- Contenido --}}
                    <div class="ft-card-body">
                        <p class="ft-nombre">{{ trim($alumno->nombre . ' ' . $alumno->ap_paterno) }}</p>
                        <p class="ft-sub"><i class="fa fa-id-card-o" style="margin-right:3px;"></i>Matrícula {{ $alumno->matricula }}</p>

                        {{-- TEMPORAL: subida de fotos desactivada --}}
                        <button type="button" class="ft-label-upload" disabled
                                style="background:#9aa5b4;cursor:not-allowed;opacity:.7;">
                            <i class="fa fa-ban"></i> No disponible por el momento
                        </button>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    {{-- ══════════════════════════
         CONTACTOS FAMILIARES
    ══════════════════════════ --}}
    <div class="ft-section-title">
        <i class="fa fa-users" style="color:#7c3aed;"></i>
        Contactos familiares
        <span class="ft-section-count" style="background:#ede9fe;color:#7c3aed;">{{ $contactos->count() }}</span>
    </div>

    @if ($contactos->isEmpty())
        <div class="ft-empty">
            <i class="fa fa-user-o"></i>
            <p>No hay contactos registrados en tu familia.</p>
        </div>
    @else
        <div class="ft-grid">
            @foreach ($contactos as $contacto)
                <div class="ft-card" id="contacto-card-{{ $contacto->id }}">

                    {{-- Avatar --}}
                    <div class="ft-card-avatar">
                        <div class="ft-avatar-wrap">
                            @if ($contacto->foto_url)
                                <img src="{{ asset('storage/' . $contacto->foto_url) }}"
                                     alt="{{ $contacto->nombre }}"
                                     class="ft-avatar ft-img">
                                <div class="ft-avatar-badge"><i class="fa fa-check"></i></div>
                            @else
                                <div class="ft-avatar-placeholder ft-placeholder"
                                     style="background:#ede9fe;color:#7c3aed;">
                                    <i class="fa fa-user"></i>
                                </div>
                                <img src="" alt="" class="ft-avatar ft-img" style="display:none;">
                            @endif
                        </div>
                    </div>

                    {{-- Contenido --}}
                    <div class="ft-card-body">
                        <p class="ft-nombre">{{ trim($contacto->nombre . ' ' . $contacto->ap_paterno) }}</p>
                        <p class="ft-sub"><i class="fa fa-users" style="margin-right:3px;"></i>Contacto familiar</p>

                        {{-- TEMPORAL: subida de fotos desactivada --}}
                        <button type="button" class="ft-label-upload" disabled
                                style="background:#9aa5b4;cursor:not-allowed;opacity:.7;">
                            <i class="fa fa-ban"></i> No disponible por el momento
                        </button>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    {{-- Botón regresar al final --}}
    <div style="margin-bottom:20px;">
        <a href="{{ route('portal.dashboard') }}" class="ft-nav-btn">
            <i class="fa fa-arrow-left"></i> Regresar al inicio
        </a>
    </div>

    {{-- ════════════════════════════
         MODAL DE RECORTE
    ════════════════════════════ --}}
    <div id="modal-crop">
        <div class="crop-header">
            <div>
                <div class="crop-header-title">
                    <i class="fa fa-crop"></i> Recorta la fotografía
                </div>
                <div class="crop-header-hint">
                    Ajusta el recuadro para encuadrar el rostro. Usa los bordes para ampliar o reducir el área.
                </div>
            </div>
        </div>
        <div class="crop-body">
            <img id="crop-img" src="" alt="Imagen a recortar">
        </div>
        <div class="crop-footer">
            <button type="button" class="crop-btn crop-btn-cancel" id="btn-crop-cancel">
                <i class="fa fa-times"></i> Cancelar
            </button>
            <button type="button" class="crop-btn crop-btn-confirm" id="btn-crop-confirm">
                <i class="fa fa-check"></i> Recortar y guardar
            </button>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
$(function () {
    var token          = '{{ csrf_token() }}';
    var cropperInst    = null;
    var $inputActivo   = null;

    /* ── Al seleccionar archivo: validar y abrir el recortador ── */
    $(document).on('change', '.ft-input', function () {
        var $input = $(this);
        var file   = this.files[0];
        if (!file) return;

        var $card = $input.closest('.ft-card');

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            mostrarError($card, 'Solo se permiten imágenes JPG, PNG o WEBP.');
            $input.val('');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            mostrarError($card, 'La imagen no debe superar 2 MB.');
            $input.val('');
            return;
        }

        $inputActivo = $input;

        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.getElementById('crop-img');

            // Destruir instancia anterior antes de cambiar el src
            if (cropperInst) {
                cropperInst.destroy();
                cropperInst = null;
            }

            img.src = e.target.result;

            // Bloquear scroll del body (imprescindible en iOS Safari)
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width    = '100%';

            $('#modal-crop').addClass('visible');

            // Esperar un frame para que el modal tenga dimensiones reales en el DOM
            requestAnimationFrame(function () {
                cropperInst = new Cropper(img, {
                    aspectRatio:              3 / 4,
                    viewMode:                 1,
                    dragMode:                 'move',
                    autoCropArea:             0.85,
                    responsive:               true,
                    restore:                  false,
                    guides:                   true,
                    center:                   true,
                    highlight:                false,
                    cropBoxMovable:           true,
                    cropBoxResizable:         true,
                    toggleDragModeOnDblclick: false,
                });
            });
        };
        reader.readAsDataURL(file);
        $input.val('');   // Permite volver a seleccionar el mismo archivo si cancela
    });

    /* ── Cancelar recorte ── */
    $('#btn-crop-cancel').on('click', function () {
        cerrarCrop();
    });

    /* ── Confirmar recorte y subir ── */
    $('#btn-crop-confirm').on('click', function () {
        if (!cropperInst || !$inputActivo) return;

        var $card = $inputActivo.closest('.ft-card');
        var url   = $inputActivo.data('url');

        // Canvas con resolución adecuada para impresión de credencial (300 px/lado mayor)
        var canvas = cropperInst.getCroppedCanvas({
            width:                   600,
            height:                  800,
            imageSmoothingEnabled:   true,
            imageSmoothingQuality:   'high',
        });

        cerrarCrop();

        // Estado de carga en la tarjeta
        $card.find('.ft-ok, .ft-err').hide();
        var $progress = $card.find('.ft-progress').show();
        var $bar      = $card.find('.ft-progress-bar');
        var $label    = $card.find('.ft-label-upload');
        $label.addClass('subiendo').html('<i class="fa fa-spinner fa-spin"></i> Subiendo...');

        canvas.toBlob(function (blob) {
            var fd = new FormData();
            fd.append('_token', token);
            fd.append('foto', blob, 'foto-recortada.jpg');

            $.ajax({
                url:         url,
                method:      'POST',
                data:        fd,
                processData: false,
                contentType: false,
                xhr: function () {
                    var xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            $bar.css('width', Math.round(e.loaded / e.total * 100) + '%');
                        }
                    });
                    return xhr;
                },
                success: function (resp) {
                    var $img         = $card.find('.ft-img');
                    var $placeholder = $card.find('.ft-placeholder');
                    var $wrap        = $card.find('.ft-avatar-wrap');

                    $img.attr('src', resp.foto_url + '?t=' + Date.now()).show();
                    $placeholder.hide();

                    if (!$wrap.find('.ft-avatar-badge').length) {
                        $wrap.append('<div class="ft-avatar-badge"><i class="fa fa-check"></i></div>');
                    }

                    $progress.hide();
                    $bar.css('width', '0');
                    $label.removeClass('subiendo').html('<i class="fa fa-camera"></i> Cambiar foto');
                    $card.find('.ft-ok').show().delay(3500).fadeOut(400);
                },
                error: function (xhr) {
                    $progress.hide();
                    $bar.css('width', '0');
                    $label.removeClass('subiendo').html('<i class="fa fa-camera"></i> Subir foto');
                    var msg = xhr.responseJSON?.mensaje
                        || xhr.responseJSON?.message
                        || primerError(xhr.responseJSON?.errors)
                        || 'Error al subir la imagen.';
                    mostrarError($card, msg);
                },
            });
        }, 'image/jpeg', 0.92);
    });

    /* ── Helpers ── */
    function cerrarCrop() {
        $('#modal-crop').removeClass('visible');

        // Restaurar scroll del body (bloqueado al abrir el modal en iOS)
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width    = '';

        if (cropperInst) {
            cropperInst.destroy();
            cropperInst = null;
        }
        $inputActivo = null;
    }

    function mostrarError($card, msg) {
        $card.find('.ft-err').text(msg).show().delay(4500).fadeOut(400);
    }

    function primerError(errors) {
        if (!errors) return null;
        var vals = Object.values(errors);
        return vals.length ? vals[0][0] : null;
    }
});
</script>
@endpush
