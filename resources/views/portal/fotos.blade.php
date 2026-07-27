@extends('layouts.master')

@section('page_title', 'Fotografías')
@section('page_subtitle', 'Carga de fotografías')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Fotografías</li>
@endsection

@push('styles')
    @include('portal._styles')
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

        /* ── Grid de tarjetas ── */
        .ft-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        @media (min-width: 500px) {
            .ft-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (min-width: 768px) {
            .ft-grid { grid-template-columns: repeat(4, 1fr); }
        }

        /* ── Tarjeta de foto ── */
        .ft-card {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 14px;
            padding: 18px 12px 14px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
            transition: box-shadow .15s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .ft-card:hover { box-shadow: 0 4px 14px rgba(60,141,188,.12); }

        /* ── Avatar ── */
        .ft-avatar-wrap {
            position: relative;
            width: 90px;
            height: 90px;
            margin: 0 auto 12px;
        }
        .ft-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e4eaf0;
            display: block;
            background: #f0f3f7;
        }
        .ft-avatar-placeholder {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #e8f0fb;
            color: #3c8dbc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            border: 3px solid #e4eaf0;
        }
        /* Badge de foto cargada */
        .ft-avatar-badge {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #00875a;
            border: 2px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #fff;
        }

        /* ── Nombre y subtítulo ── */
        .ft-nombre {
            font-size: 13px;
            font-weight: 700;
            color: #1a2634;
            margin: 0 0 3px;
            line-height: 1.3;
            word-break: break-word;
        }
        .ft-sub {
            font-size: 11px;
            color: #9aa5b4;
            margin: 0 0 12px;
        }

        /* ── Botón subir ── */
        .ft-label-upload {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 10px 8px;
            background: #3c8dbc;
            color: #fff;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
            border: none;
            line-height: 1.2;
            text-align: center;
        }
        .ft-label-upload:hover { background: #2a6e9e; }
        .ft-label-upload.subiendo {
            background: #7b8794;
            cursor: not-allowed;
            pointer-events: none;
        }
        .ft-input { display: none; }

        /* ── Barra de progreso ── */
        .ft-progress {
            width: 100%;
            height: 6px;
            background: #e4eaf0;
            border-radius: 99px;
            margin-top: 8px;
            overflow: hidden;
            display: none;
        }
        .ft-progress-bar {
            height: 100%;
            width: 0;
            background: #3c8dbc;
            border-radius: 99px;
            transition: width .2s;
        }

        /* ── Mensajes ── */
        .ft-ok {
            display: none;
            margin-top: 7px;
            font-size: 12px;
            font-weight: 600;
            color: #00875a;
            display: none;
            align-items: center;
            gap: 4px;
            justify-content: center;
        }
        .ft-err {
            display: none;
            margin-top: 7px;
            font-size: 12px;
            color: #b91c1c;
            word-break: break-word;
            text-align: center;
        }

        /* ── Vacío ── */
        .ft-empty {
            text-align: center;
            padding: 36px 20px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            color: #9aa5b4;
            margin-bottom: 20px;
        }
        .ft-empty i { font-size: 42px; color: #d1d9e0; display: block; margin-bottom: 10px; }
        .ft-empty p { font-size: 14px; margin: 0; }
    </style>
@endpush

@section('content')

    {{-- ── Cabecera ── --}}
    <div class="ft-hero">
        <div class="ft-hero-titulo"><i class="fa fa-camera" style="margin-right:8px;"></i>Fotografías</div>
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
            <strong>máximo 2 MB</strong>. Se recomienda una foto reciente con buena iluminación.
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

                    <p class="ft-nombre">{{ trim($alumno->nombre . ' ' . $alumno->ap_paterno) }}</p>
                    <p class="ft-sub">Matrícula {{ $alumno->matricula }}</p>

                    <input type="file" class="ft-input"
                           accept="image/jpeg,image/png,image/webp"
                           id="file-alumno-{{ $alumno->id }}"
                           data-tipo="alumno"
                           data-id="{{ $alumno->id }}"
                           data-url="{{ route('portal.fotos.alumno', $alumno->id) }}">

                    <label for="file-alumno-{{ $alumno->id }}" class="ft-label-upload">
                        <i class="fa fa-camera"></i>
                        {{ $alumno->foto_url ? 'Cambiar foto' : 'Subir foto' }}
                    </label>

                    <div class="ft-progress"><div class="ft-progress-bar"></div></div>
                    <div class="ft-ok"><i class="fa fa-check-circle"></i> ¡Foto actualizada!</div>
                    <div class="ft-err"></div>
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

                    <p class="ft-nombre">{{ trim($contacto->nombre . ' ' . $contacto->ap_paterno) }}</p>
                    <p class="ft-sub">Contacto familiar</p>

                    <input type="file" class="ft-input"
                           accept="image/jpeg,image/png,image/webp"
                           id="file-contacto-{{ $contacto->id }}"
                           data-tipo="contacto"
                           data-id="{{ $contacto->id }}"
                           data-url="{{ route('portal.fotos.contacto', $contacto->id) }}">

                    <label for="file-contacto-{{ $contacto->id }}" class="ft-label-upload"
                           style="background:#7c3aed;">
                        <i class="fa fa-camera"></i>
                        {{ $contacto->foto_url ? 'Cambiar foto' : 'Subir foto' }}
                    </label>

                    <div class="ft-progress"><div class="ft-progress-bar" style="background:#7c3aed;"></div></div>
                    <div class="ft-ok"><i class="fa fa-check-circle"></i> ¡Foto actualizada!</div>
                    <div class="ft-err"></div>
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

@endsection

@push('scripts')
<script>
$(function () {
    var token = '{{ csrf_token() }}';

    $(document).on('change', '.ft-input', function () {
        var $input = $(this);
        var file   = this.files[0];
        var url    = $input.data('url');
        var $card  = $input.closest('.ft-card');

        if (!file) return;

        // Validación en cliente
        if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
            mostrarError($card, 'Solo se permiten imágenes JPG, PNG o WEBP.');
            $input.val('');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            mostrarError($card, 'La imagen no debe superar 2 MB.');
            $input.val('');
            return;
        }

        var fd = new FormData();
        fd.append('_token', token);
        fd.append('foto', file);

        $card.find('.ft-ok, .ft-err').hide();
        var $progress = $card.find('.ft-progress').show();
        var $bar      = $card.find('.ft-progress-bar');
        var $label    = $card.find('.ft-label-upload');
        $label.addClass('subiendo').html('<i class="fa fa-spinner fa-spin"></i> Subiendo...');

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

                // Agregar badge de completado si no existe
                if (!$wrap.find('.ft-avatar-badge').length) {
                    $wrap.append('<div class="ft-avatar-badge"><i class="fa fa-check"></i></div>');
                }

                $progress.hide();
                $bar.css('width', '0');
                $label.removeClass('subiendo').html('<i class="fa fa-camera"></i> Cambiar foto');
                $card.find('.ft-ok').show().delay(3500).fadeOut(400);
                $input.val('');
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
                $input.val('');
            },
        });
    });

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
