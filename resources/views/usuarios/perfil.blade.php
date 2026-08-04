@extends('layouts.master')

@section('page_title', 'Mi perfil')

@push('styles')
<style>
    .perfil-card {
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        max-width: 520px;
        margin: 0 auto;
    }
    .perfil-header {
        background: linear-gradient(135deg, #3c8dbc 0%, #2a6496 100%);
        padding: 32px 24px 20px;
        text-align: center;
        position: relative;
    }
    .perfil-avatar-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 12px;
    }
    .perfil-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        object-position: center;
        border: 4px solid rgba(255,255,255,0.85);
        display: block;
        flex-shrink: 0;
    }
    .perfil-avatar-btn {
        position: absolute;
        bottom: 2px;
        right: 2px;
        background: #fff;
        border: 2px solid #3c8dbc;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s;
        padding: 0;
    }
    .perfil-avatar-btn:hover { background: #eaf3fb; }
    .perfil-avatar-btn i { color: #3c8dbc; font-size: 13px; }
    .perfil-nombre {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }
    .perfil-rol {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        color: #fff;
        border-radius: 12px;
        font-size: 12px;
        padding: 2px 12px;
        margin-top: 6px;
    }
    .perfil-body {
        padding: 24px;
    }
    .perfil-field {
        display: flex;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        gap: 14px;
    }
    .perfil-field:last-child { border-bottom: none; }
    .perfil-field-icon {
        width: 32px;
        text-align: center;
        color: #94a3b8;
        font-size: 15px;
        padding-top: 2px;
        flex-shrink: 0;
    }
    .perfil-field-label {
        font-size: 11px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 2px;
    }
    .perfil-field-value {
        font-size: 14px;
        color: #334155;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="perfil-card">

    {{-- Cabecera con foto --}}
    <div class="perfil-header">
        <div class="perfil-avatar-wrap">
            <img id="perfil-img"
                 src="{{ $usuario->foto_url }}"
                 alt="{{ $usuario->nombre }}"
                 class="perfil-avatar">
            <button type="button"
                    class="perfil-avatar-btn"
                    title="Cambiar foto"
                    onclick="document.getElementById('foto-input').click()">
                <i class="fa fa-camera"></i>
            </button>
        </div>
        <h4 class="perfil-nombre">{{ $usuario->nombre }}</h4>
        <span class="perfil-rol">{{ ucfirst($usuario->rol) }}</span>
    </div>

    {{-- Datos del usuario --}}
    <div class="perfil-body">

        <div class="perfil-field">
            <div class="perfil-field-icon"><i class="fa fa-user"></i></div>
            <div>
                <div class="perfil-field-label">Nombre completo</div>
                <div class="perfil-field-value">{{ $usuario->nombre }}</div>
            </div>
        </div>

        <div class="perfil-field">
            <div class="perfil-field-icon"><i class="fa fa-envelope-o"></i></div>
            <div>
                <div class="perfil-field-label">Correo electrónico</div>
                <div class="perfil-field-value">{{ $usuario->email }}</div>
            </div>
        </div>

        <div class="perfil-field">
            <div class="perfil-field-icon"><i class="fa fa-shield"></i></div>
            <div>
                <div class="perfil-field-label">Rol</div>
                <div class="perfil-field-value">{{ ucfirst($usuario->rol) }}</div>
            </div>
        </div>

        @if ($usuario->ultimo_acceso)
        <div class="perfil-field">
            <div class="perfil-field-icon"><i class="fa fa-clock-o"></i></div>
            <div>
                <div class="perfil-field-label">Último acceso</div>
                <div class="perfil-field-value">{{ $usuario->ultimo_acceso->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        @endif

        <div class="perfil-field">
            <div class="perfil-field-icon"><i class="fa fa-calendar-o"></i></div>
            <div>
                <div class="perfil-field-label">Miembro desde</div>
                <div class="perfil-field-value">{{ $usuario->creado_at->format('d/m/Y') }}</div>
            </div>
        </div>

    </div>
</div>

{{-- Input de archivo oculto --}}
<form id="foto-form" action="{{ route('usuarios.perfil.foto') }}" method="POST" enctype="multipart/form-data" style="display:none;">
    @csrf
    <input type="file" id="foto-input" name="foto" accept="image/jpeg,image/png,image/webp">
</form>
@endsection

@push('scripts')
<script>
document.getElementById('foto-input').addEventListener('change', function () {
    if (! this.files.length) return;

    const file  = this.files[0];
    const maxMB = 2 * 1024 * 1024;

    if (file.size > maxMB) {
        alert('La imagen no debe superar 2 MB.');
        this.value = '';
        return;
    }

    const preview = URL.createObjectURL(file);
    document.getElementById('perfil-img').src = preview;

    const form = document.getElementById('foto-form');
    const data = new FormData(form);

    $.ajax({
        url:         form.action,
        method:      'POST',
        data:        data,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.foto_url) {
                document.getElementById('perfil-img').src = res.foto_url;
                // Actualizar avatares del navbar y sidebar
                document.querySelectorAll('.user-image, .user-header .img-circle, .user-panel .img-circle').forEach(function (img) {
                    img.src = res.foto_url;
                });
            }
            toastr.success(res.message ?? 'Foto actualizada.');
        },
        error: function (xhr) {
            document.getElementById('perfil-img').src = "{{ $usuario->foto_url }}";
            const msg = xhr.responseJSON?.message ?? xhr.responseJSON?.errors?.foto?.[0] ?? 'Error al subir la imagen.';
            toastr.error(msg);
        },
    });
});
</script>
@endpush
