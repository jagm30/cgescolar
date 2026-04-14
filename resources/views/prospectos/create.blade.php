@extends('layouts.master')

@section('page_title', 'Nuevo prospecto')
@section('page_subtitle', 'Registro de admisión')

@section('content')
    @php
        $canales = [
            'referido' => 'Referido',
            'redes' => 'Redes sociales',
            'visita_directa' => 'Visita directa',
            'web' => 'Sitio web',
            'otro' => 'Otro',
        ];
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Revisa los campos marcados.</strong>
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
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Formulario de alta</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre(s) del prospecto</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                        value="{{ old('nombre') }}" required maxlength="100"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ'\-\s]+" placeholder="Solo letras y espacios">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                        value="{{ old('fecha_nacimiento') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contacto_nombre">Nombre del contacto</label>
                                    <input type="text" class="form-control" id="contacto_nombre" name="contacto_nombre"
                                        value="{{ old('contacto_nombre') }}" required maxlength="200"
                                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ'\-\s]+" placeholder="Solo letras y espacios">
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
                    <div class="box-footer">
                        <a href="{{ route('prospectos.index') }}" class="btn btn-default">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar prospecto</button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Qué se registra</h3>
                    </div>
                    <div class="box-body">
                        <p>Al guardar se crea el prospecto en etapa inicial y se agrega un seguimiento automático con la
                            nota de registro.</p>
                        <p>Desde la vista de detalle podrás agregar seguimientos, revisar documentos y cambiar la etapa del
                            proceso.</p>
                    </div>
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
                    .replace(/[\u0300-\u036f]/g, '') // elimina acentos
                    .replace(/[^A-Za-zÑñ\s'-]/g, '');
            }

            $('#nombre, #contacto_nombre').on('input', function() {
                this.value = sanitizeName(this.value);
            });

            $('#contacto_telefono').on('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);
            });
        });
    </script>
@endpush
