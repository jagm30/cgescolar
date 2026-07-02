@extends('layouts.master')

@section('page_title', 'Fotos')
@section('page_subtitle', 'Carga de fotografías')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Fotos</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        .foto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
            gap: 16px;
            padding: 16px;
        }
        .foto-card {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 10px;
            padding: 16px 12px 12px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            transition: box-shadow .15s;
        }
        .foto-card:hover { box-shadow: 0 4px 14px rgba(60,141,188,.13); }
        .foto-avatar {
            width: 100px; height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e4eaf0;
            margin: 0 auto 10px;
            display: block;
            background: #f0f3f7;
        }
        .foto-avatar-placeholder {
            width: 100px; height: 100px;
            border-radius: 50%;
            background: #e8f0fb;
            color: #3c8dbc;
            display: flex; align-items: center; justify-content: center;
            font-size: 38px;
            margin: 0 auto 10px;
            border: 3px solid #e4eaf0;
        }
        .foto-nombre {
            font-size: 13px; font-weight: 700;
            color: #172b3a;
            margin: 0 0 4px;
            line-height: 1.3;
        }
        .foto-sub {
            font-size: 11px; color: #7b8794;
            margin-bottom: 10px;
        }
        .foto-btn-upload {
            display: block; width: 100%;
            background: #3c8dbc; color: #fff;
            border: none; border-radius: 6px;
            padding: 6px 0; font-size: 12px;
            cursor: pointer; transition: background .15s;
        }
        .foto-btn-upload:hover { background: #2a6e9e; }
        .foto-btn-upload:disabled { background: #aaa; cursor: default; }
        .foto-input { display: none; }
        .foto-progress {
            height: 4px; border-radius: 2px;
            background: #e4eaf0; margin-top: 6px; overflow: hidden; display: none;
        }
        .foto-progress-bar {
            height: 100%; width: 0; background: #3c8dbc;
            transition: width .2s;
        }
        .foto-ok { color: #00875a; font-size: 11px; margin-top: 4px; display: none; }
        .foto-err { color: #b91c1c; font-size: 11px; margin-top: 4px; display: none; word-break: break-word; }
    </style>
@endpush

@section('content')

{{-- ══ ALUMNOS ══ --}}
<div class="portal-card" style="margin-bottom:20px;">
    <div class="portal-card-header">
        <h4 class="portal-card-title"><i class="fa fa-graduation-cap"></i> Alumnos</h4>
        <span class="portal-pill portal-pill-ok">{{ $alumnos->count() }}</span>
    </div>

    @if($alumnos->isEmpty())
        <div style="padding:16px;">
            <div class="portal-empty">
                <i class="fa fa-users" style="font-size:34px;margin-bottom:10px;"></i>
                <div>No hay alumnos vinculados a tu familia.</div>
            </div>
        </div>
    @else
        <div class="foto-grid">
            @foreach($alumnos as $alumno)
                <div class="foto-card" id="alumno-card-{{ $alumno->id }}">
                    @if($alumno->foto_url)
                        <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                             alt="{{ $alumno->nombre }}"
                             class="foto-avatar foto-img">
                    @else
                        <div class="foto-avatar-placeholder foto-placeholder">
                            <i class="fa fa-user"></i>
                        </div>
                        <img src="" alt="" class="foto-avatar foto-img" style="display:none;">
                    @endif
                    <p class="foto-nombre">{{ trim($alumno->nombre . ' ' . $alumno->ap_paterno) }}</p>
                    <p class="foto-sub">Matrícula {{ $alumno->matricula }}</p>

                    <input type="file" class="foto-input" accept="image/jpeg,image/png,image/webp"
                           style="display:none;"
                           id="file-alumno-{{ $alumno->id }}"
                           data-tipo="alumno"
                           data-id="{{ $alumno->id }}"
                           data-url="{{ route('portal.fotos.alumno', $alumno->id) }}">
                    <label for="file-alumno-{{ $alumno->id }}" class="foto-btn-upload">
                        <i class="fa fa-camera"></i> {{ $alumno->foto_url ? 'Cambiar foto' : 'Subir foto' }}
                    </label>
                    <div class="foto-progress"><div class="foto-progress-bar"></div></div>
                    <div class="foto-ok"><i class="fa fa-check"></i> Foto actualizada</div>
                    <div class="foto-err"></div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ══ CONTACTOS FAMILIARES ══ --}}
<div class="portal-card">
    <div class="portal-card-header">
        <h4 class="portal-card-title"><i class="fa fa-users"></i> Contactos familiares</h4>
        <span class="portal-pill portal-pill-ok">{{ $contactos->count() }}</span>
    </div>

    @if($contactos->isEmpty())
        <div style="padding:16px;">
            <div class="portal-empty">
                <i class="fa fa-user-o" style="font-size:34px;margin-bottom:10px;"></i>
                <div>No hay contactos registrados en tu familia.</div>
            </div>
        </div>
    @else
        <div class="foto-grid">
            @foreach($contactos as $contacto)
                <div class="foto-card" id="contacto-card-{{ $contacto->id }}">
                    @if($contacto->foto_url)
                        <img src="{{ asset('storage/' . $contacto->foto_url) }}"
                             alt="{{ $contacto->nombre }}"
                             class="foto-avatar foto-img">
                    @else
                        <div class="foto-avatar-placeholder foto-placeholder">
                            <i class="fa fa-user"></i>
                        </div>
                        <img src="" alt="" class="foto-avatar foto-img" style="display:none;">
                    @endif
                    <p class="foto-nombre">{{ trim($contacto->nombre . ' ' . $contacto->ap_paterno) }}</p>
                    <p class="foto-sub">Contacto familiar</p>

                    <input type="file" class="foto-input" accept="image/jpeg,image/png,image/webp"
                           style="display:none;"
                           id="file-contacto-{{ $contacto->id }}"
                           data-tipo="contacto"
                           data-id="{{ $contacto->id }}"
                           data-url="{{ route('portal.fotos.contacto', $contacto->id) }}">
                    <label for="file-contacto-{{ $contacto->id }}" class="foto-btn-upload">
                        <i class="fa fa-camera"></i> {{ $contacto->foto_url ? 'Cambiar foto' : 'Subir foto' }}
                    </label>
                    <div class="foto-progress"><div class="foto-progress-bar"></div></div>
                    <div class="foto-ok"><i class="fa fa-check"></i> Foto actualizada</div>
                    <div class="foto-err"></div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
$(function () {

    var token = '{{ csrf_token() }}';

    $(document).on('change', '.foto-input', function () {
        var $input  = $(this);
        var file    = this.files[0];
        var url     = $input.data('url');
        var $card   = $input.closest('.foto-card');

        if (!file) return;

        // Validación previa en cliente
        var permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!permitidos.includes(file.type)) {
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

        $card.find('.foto-ok, .foto-err').hide();
        var $progress = $card.find('.foto-progress').show();
        var $bar      = $card.find('.foto-progress-bar');
        var $label    = $card.find('.foto-btn-upload');
        $label.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Subiendo...');

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
                // Actualizar imagen en la tarjeta
                var $img         = $card.find('.foto-img');
                var $placeholder = $card.find('.foto-placeholder');

                $img.attr('src', resp.foto_url + '?t=' + Date.now()).show();
                $placeholder.hide();

                $progress.hide();
                $bar.css('width', '0');
                $label.prop('disabled', false).html('<i class="fa fa-camera"></i> Cambiar foto');
                $card.find('.foto-ok').show().delay(3000).fadeOut(400);
                $input.val('');
            },
            error: function (xhr) {
                $progress.hide();
                $bar.css('width', '0');
                $label.prop('disabled', false).html('<i class="fa fa-camera"></i> Subir foto');
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
        $card.find('.foto-err').text(msg).show().delay(4000).fadeOut(400);
    }

    function primerError(errors) {
        if (!errors) return null;
        var vals = Object.values(errors);
        return vals.length ? vals[0][0] : null;
    }

});
</script>
@endpush
