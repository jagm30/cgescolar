@extends('layouts.master')

@section('page_title', 'Nuevo prospecto')
@section('page_subtitle', 'Registro de admision')

@push('styles')
<style>
    /* ── Header compacto ── */
    .pro-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }
    .pro-header h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #2d3a4a;
    }

    /* ── Panel principal ── */
    .pro-panel {
        border: 1px solid #e0e7ef;
        border-radius: 8px;
        background: #fff;
        margin-bottom: 18px;
        overflow: hidden;
    }
    .pro-panel-header {
        background: #f4f6f8;
        border-bottom: 1px solid #e0e7ef;
        padding: 9px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pro-panel-header span {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7a8d;
    }
    .pro-panel-body {
        padding: 16px;
    }
    .pro-panel-footer {
        background: #f4f6f8;
        border-top: 1px solid #e0e7ef;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* ── Panel lateral ── */
    .pro-side-panel {
        border: 1px solid #e0e7ef;
        border-radius: 8px;
        background: #f9fbfd;
        padding: 14px 16px;
    }
    .pro-side-panel .side-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7a8d;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pro-side-panel p {
        font-size: 13px;
        color: #5a6474;
        line-height: 1.5;
        margin-bottom: 8px;
    }

    /* ── Separador de sección ── */
    .pro-section-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: #6b7a8d;
        margin: 14px 0 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pro-section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e0e7ef;
    }

    /* ── Formulario compacto ── */
    .pro-panel-body .form-group {
        margin-bottom: 10px;
    }
    .pro-panel-body label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #6b7a8d;
        margin-bottom: 3px;
    }
    .pro-panel-body .form-control {
        height: 32px;
        font-size: 13px;
        border-radius: 5px;
        border-color: #d0d7e2;
        padding: 4px 9px;
    }

    /* ── Botones ── */
    .pro-panel-footer .btn,
    .pro-header .btn {
        border-radius: 20px;
        font-size: 13px;
        padding: 5px 16px;
    }

    /* ── Alerta de errores ── */
    .pro-errors {
        border-left: 3px solid #e74c3c;
        border-radius: 6px;
        background: #fff5f5;
        padding: 10px 14px;
        margin-bottom: 16px;
        font-size: 13px;
    }
    .pro-errors strong {
        color: #c0392b;
        display: block;
        margin-bottom: 4px;
    }
    .pro-errors ul {
        margin: 0;
        padding-left: 18px;
        color: #a94442;
    }
</style>
@endpush

@section('content')
    @php
        $canales = [
            'referido'       => 'Referido',
            'redes'          => 'Redes sociales',
            'visita_directa' => 'Visita directa',
            'web'            => 'Sitio web',
            'otro'           => 'Otro',
        ];
    @endphp

    {{-- Header compacto --}}
    <div class="pro-header">
        <h2><i class="fa fa-user-plus" style="color:#3c8dbc;margin-right:6px;"></i>Nuevo prospecto</h2>
        <a href="{{ route('prospectos.index') }}" class="btn btn-default btn-sm">
            <i class="fa fa-arrow-left"></i> Admisiones
        </a>
    </div>

    @if ($errors->any())
        <div class="pro-errors">
            <strong><i class="fa fa-exclamation-circle"></i> Revisa los campos marcados.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('prospectos.store') }}">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="pro-panel">
                    <div class="pro-panel-header">
                        <i class="fa fa-child" style="color:#3c8dbc;"></i>
                        <span>Datos del prospecto</span>
                    </div>
                    <div class="pro-panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nombre">Nombre(s)</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                        value="{{ old('nombre') }}" required maxlength="100"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ'\-\s]+" placeholder="Solo letras">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ap_paterno">Apellido paterno</label>
                                    <input type="text" class="form-control" id="ap_paterno" name="ap_paterno"
                                        value="{{ old('ap_paterno') }}" required maxlength="100"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ'\-\s]+" placeholder="Solo letras">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ap_materno">Apellido materno</label>
                                    <input type="text" class="form-control" id="ap_materno" name="ap_materno"
                                        value="{{ old('ap_materno') }}" maxlength="100"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ'\-\s]+" placeholder="Solo letras">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                        value="{{ old('fecha_nacimiento') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nivel_interes_id">Nivel de interés</label>
                                    <select class="form-control" id="nivel_interes_id" name="nivel_interes_id">
                                        <option value="">Selecciona un nivel</option>
                                        @foreach ($niveles as $nivel)
                                            <option value="{{ $nivel->id }}"
                                                {{ (string) old('nivel_interes_id') === (string) $nivel->id ? 'selected' : '' }}>
                                                {{ $nivel->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ciclo_id">Ciclo escolar</label>
                                    <select class="form-control" id="ciclo_id" name="ciclo_id">
                                        <option value="">Usar ciclo activo</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}"
                                                {{ (string) old('ciclo_id') === (string) $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="pro-section-title">Datos de contacto</div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contacto_nombre">Nombre del contacto</label>
                                    <input type="text" class="form-control" id="contacto_nombre" name="contacto_nombre"
                                        value="{{ old('contacto_nombre') }}" required maxlength="200"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ'\-\s]+" placeholder="Solo letras">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="contacto_telefono">Teléfono</label>
                                    <input type="tel" class="form-control" id="contacto_telefono"
                                        name="contacto_telefono" value="{{ old('contacto_telefono') }}" required
                                        maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="10 dígitos">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_primer_contacto">Primer contacto</label>
                                    <input type="date" class="form-control" id="fecha_primer_contacto"
                                        name="fecha_primer_contacto"
                                        value="{{ old('fecha_primer_contacto', now()->toDateString()) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contacto_email">Correo electrónico</label>
                                    <input type="email" class="form-control" id="contacto_email" name="contacto_email"
                                        value="{{ old('contacto_email') }}" maxlength="200">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="canal_contacto">Canal de contacto</label>
                                    <select class="form-control" id="canal_contacto" name="canal_contacto">
                                        <option value="">Selecciona un canal</option>
                                        @foreach ($canales as $valor => $etiqueta)
                                            <option value="{{ $valor }}"
                                                {{ old('canal_contacto') === $valor ? 'selected' : '' }}>
                                                {{ $etiqueta }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pro-panel-footer">
                        <a href="{{ route('prospectos.index') }}" class="btn btn-default btn-sm">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-save"></i> Guardar prospecto
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="pro-side-panel">
                    <div class="side-title">
                        <i class="fa fa-info-circle" style="color:#3c8dbc;"></i> ¿Qué se registra?
                    </div>
                    <p>Al guardar se crea el prospecto en etapa inicial y se agrega un seguimiento automático con la nota de registro.</p>
                    <p>Desde la vista de detalle podrás agregar seguimientos, revisar documentos y cambiar la etapa del proceso.</p>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $(function() {
            function sanitizeName(value) {
                return value
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^A-Za-zÑñ\s'-]/g, '');
            }

            $('#nombre, #ap_paterno, #ap_materno, #contacto_nombre').on('input', function() {
                this.value = sanitizeName(this.value);
            });

            $('#contacto_telefono').on('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);
            });
        });
    </script>
@endpush
