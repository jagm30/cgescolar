@extends('layouts.master')

@section('page_title', $familia->apellido_familia)
@section('page_subtitle', 'Ficha de familia')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li class="active">{{ $familia->apellido_familia }}</li>
@endsection

@section('content')

{{-- Alertas --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="row">

    {{-- ══════════════════════════════════════════════════════
         COLUMNA PRINCIPAL
    ══════════════════════════════════════════════════════ --}}
    <div class="col-md-8">

        {{-- ── Alumnos ────────────────────────────────────── --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-graduation-cap"></i>
                    Alumnos
                    <span class="badge bg-blue" style="margin-left:6px;">
                        {{ $familia->alumnos->count() }}
                    </span>
                </h3>
                <div class="box-tools pull-right">
                    @can('administrador')
                    <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}"
                       class="btn btn-success btn-xs btn-flat">
                        <i class="fa fa-plus"></i> Inscribir alumno
                    </a>
                    @endcan
                </div>
            </div>
            <div class="box-body no-padding">

                @forelse($familia->alumnos->sortBy('ap_paterno') as $alumno)
                @php
                    $inscripcion = $alumno->inscripciones
                        ->sortByDesc('id')
                        ->first();
                    $estadoColor = match($alumno->estado) {
                        'activo'          => 'success',
                        'baja_temporal'   => 'warning',
                        'baja_definitiva' => 'danger',
                        'egresado'        => 'default',
                        default           => 'default',
                    };
                @endphp

                <div class="alumno-row" style="
                    padding: 14px 16px;
                    border-bottom: 1px solid #f4f4f4;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                ">
                    {{-- Foto --}}
                    <div style="flex-shrink:0;">
                        @if($alumno->foto_url)
                            <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                                 alt="{{ $alumno->nombre }}"
                                 style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #e8e8e8;">
                        @else
                            <div style="
                                width:52px;height:52px;border-radius:50%;
                                background:#e8e8e8;display:flex;
                                align-items:center;justify-content:center;
                            ">
                                <i class="fa fa-user" style="font-size:22px;color:#aaa;"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Datos del alumno --}}
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <strong style="font-size:14px;">
                                <a href="{{ route('alumnos.show', $alumno->id) }}"
                                   style="color:#333;">
                                    {{ $alumno->nombre }}
                                    {{ $alumno->ap_paterno }}
                                    {{ $alumno->ap_materno }}
                                </a>
                            </strong>
                            <span class="label label-{{ $estadoColor }}">
                                {{ ucfirst(str_replace('_',' ', $alumno->estado)) }}
                            </span>
                        </div>

                        <div style="margin-top:4px; font-size:12px; color:#888; display:flex; gap:16px; flex-wrap:wrap;">
                            <span>
                                <i class="fa fa-id-badge"></i>
                                <code style="font-size:11px;">{{ $alumno->matricula }}</code>
                            </span>
                            @if($alumno->fecha_nacimiento)
                            <span>
                                <i class="fa fa-birthday-cake"></i>
                                {{ $alumno->fecha_nacimiento->format('d/m/Y') }}
                                <small>({{ $alumno->fecha_nacimiento->age }} años)</small>
                            </span>
                            @endif
                            @if($inscripcion)
                            <span>
                                <i class="fa fa-graduation-cap"></i>
                                {{ $inscripcion->grupo->grado->nivel->nombre ?? '' }}
                                — {{ $inscripcion->grupo->grado->nombre }}°
                                {{ $inscripcion->grupo->nombre }}
                                <small class="text-muted">({{ $inscripcion->ciclo->nombre ?? '' }})</small>
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div style="flex-shrink:0;">
                        <a href="{{ route('alumnos.show', $alumno->id) }}"
                           class="btn btn-default btn-xs btn-flat" title="Ver alumno">
                            <i class="fa fa-eye"></i>
                        </a>
                        @can('administrador')
                        <a href="{{ route('alumnos.edit', $alumno->id) }}"
                           class="btn btn-primary btn-xs btn-flat" title="Editar alumno">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan
                    </div>
                </div>

                @empty
                <div style="padding:40px; text-align:center;">
                    <i class="fa fa-graduation-cap fa-3x text-muted" style="display:block;margin-bottom:10px;"></i>
                    <p class="text-muted">Esta familia no tiene alumnos inscritos.</p>
                    @can('administrador')
                    <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}"
                       class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Inscribir primer alumno
                    </a>
                    @endcan
                </div>
                @endforelse

            </div>
        </div>

        {{-- ── Contactos familiares ───────────────────────── --}}
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-phone"></i>
                    Contactos familiares
                    <span class="badge" style="margin-left:6px;background:#777;">
                        {{ $familia->contactos->count() }}
                    </span>
                </h3>
                <div class="box-tools pull-right">
                    @can('administrador', 'recepcion')
                    <button type="button" class="btn btn-success btn-xs btn-flat"
                            id="btn-toggle-nuevo-ctc"
                            onclick="(function(){
                                var f=document.getElementById('form-nuevo-ctc');
                                var v=f.style.display!=='none';
                                f.style.display=v?'none':'block';
                                document.getElementById('ico-nuevo-ctc').className=v?'fa fa-plus':'fa fa-minus';
                            })()">
                        <i class="fa fa-plus" id="ico-nuevo-ctc"></i> Agregar contacto
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Formulario nuevo contacto (colapsado) --}}
            <div id="form-nuevo-ctc" style="display:none;border-bottom:1px solid #f4f4f4;">
                <div style="padding:16px; background:#fafffe;">
                    <h4 style="margin:0 0 12px; font-size:13px; color:#00a65a;">
                        <i class="fa fa-plus-circle"></i> Nuevo contacto
                    </h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>
                                <input type="text" id="nctc-nombre" class="form-control input-sm" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Apellido paterno</label>
                                <input type="text" id="nctc-ap-paterno" class="form-control input-sm" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Apellido materno</label>
                                <input type="text" id="nctc-ap-materno" class="form-control input-sm" maxlength="100">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px;">Teléfono celular <span class="text-red">*</span></label>
                                <input type="tel" id="nctc-celular" class="form-control input-sm" maxlength="20" placeholder="10 dígitos">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px;">Teléfono trabajo</label>
                                <input type="tel" id="nctc-trabajo" class="form-control input-sm" maxlength="20">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px;">Correo</label>
                                <input type="email" id="nctc-email" class="form-control input-sm" maxlength="200">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size:12px;">CURP</label>
                                <input type="text" id="nctc-curp" class="form-control input-sm" maxlength="18" style="text-transform:uppercase">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label style="font-size:12px;">Acceso al portal</label>
                                <div>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="nctc-portal">
                                        Habilitar acceso al portal familiar
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 text-right" style="padding-top:20px;">
                            <button type="button" class="btn btn-default btn-sm" id="btn-cancelar-nctc">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-success btn-sm" id="btn-guardar-nctc">
                                <i class="fa fa-plus"></i> Agregar contacto
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alerta AJAX --}}
            <div id="ctc-alerta" style="display:none;margin:10px 16px 0;" class="alert alert-dismissible">
                <button type="button" class="close" onclick="$('#ctc-alerta').hide()">&times;</button>
                <span id="ctc-alerta-msg"></span>
            </div>

            <div class="box-body no-padding" id="contenedor-contactos">

                @forelse($familia->contactos->sortBy('pivot.orden') as $contacto)
                @php
                    $pivot = $contacto->pivot;
                @endphp

                <div class="ctc-panel" data-id="{{ $contacto->id }}"
                     style="padding:14px 16px; border-bottom:1px solid #f4f4f4;">

                    <div style="display:flex; align-items:flex-start; gap:12px;">

                        {{-- Foto del contacto --}}
                        <div style="flex-shrink:0;">
                            @if($contacto->foto_url)
                                <img src="{{ asset('storage/' . $contacto->foto_url) }}"
                                     style="width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid #e8e8e8;">
                            @else
                                <div style="width:46px;height:46px;border-radius:50%;background:#f0f0f0;
                                            display:flex;align-items:center;justify-content:center;">
                                    <i class="fa fa-user" style="color:#bbb;font-size:18px;"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Datos --}}
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <strong style="font-size:14px;">
                                    {{ $contacto->nombre }}
                                    {{ $contacto->ap_paterno }}
                                    {{ $contacto->ap_materno }}
                                </strong>
                                @if($pivot && $pivot->orden == 1)
                                    <span class="label label-primary" style="font-size:10px;">Principal</span>
                                @endif
                                @if($contacto->tiene_acceso_portal)
                                    <span class="label label-info" style="font-size:10px;">
                                        <i class="fa fa-globe"></i> Portal
                                    </span>
                                @endif
                            </div>

                            <div style="margin-top:5px;font-size:12px;color:#777;display:flex;gap:16px;flex-wrap:wrap;">
                                @if($contacto->telefono_celular)
                                <span><i class="fa fa-mobile"></i> {{ $contacto->telefono_celular }}</span>
                                @endif
                                @if($contacto->telefono_trabajo)
                                <span><i class="fa fa-phone"></i> {{ $contacto->telefono_trabajo }}</span>
                                @endif
                                @if($contacto->email)
                                <span><i class="fa fa-envelope-o"></i> {{ $contacto->email }}</span>
                                @endif
                                @if($contacto->curp)
                                <span><i class="fa fa-id-card-o"></i> <code style="font-size:11px;">{{ $contacto->curp }}</code></span>
                                @endif
                            </div>

                            {{-- Alumnos vinculados a este contacto --}}
                            @if($contacto->alumnoContactos && $contacto->alumnoContactos->count())
                            <div style="margin-top:6px;">
                                @foreach($contacto->alumnoContactos as $ac)
                                    <span class="label label-default" style="font-size:10px;margin-right:4px;">
                                        {{ ucfirst($ac->parentesco) }}
                                        — {{ $ac->alumno->nombre ?? '' }} {{ $ac->alumno->ap_paterno ?? '' }}
                                        @if($ac->autorizado_recoger)
                                            <i class="fa fa-check" title="Autorizado para recoger"></i>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        @can('administrador', 'recepcion')
                        <div style="flex-shrink:0;display:flex;gap:4px;">
                            <button type="button"
                                    class="btn btn-primary btn-xs btn-flat btn-editar-ctc"
                                    data-id="{{ $contacto->id }}"
                                    title="Editar contacto">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-danger btn-xs btn-flat btn-eliminar-ctc"
                                    data-id="{{ $contacto->id }}"
                                    data-nombre="{{ $contacto->nombre }} {{ $contacto->ap_paterno }}"
                                    title="Eliminar contacto">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        @endcan
                    </div>

                    {{-- Panel de edición inline (oculto por defecto) --}}
                    <div class="panel-edicion" id="editar-ctc-{{ $contacto->id }}" style="display:none;margin-top:12px;padding-top:12px;border-top:1px dashed #e0e0e0;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px;">Nombre(s) <span class="text-red">*</span></label>
                                    <input type="text" class="form-control input-sm ctc-nombre"
                                           value="{{ $contacto->nombre }}" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px;">Apellido paterno</label>
                                    <input type="text" class="form-control input-sm ctc-ap-paterno"
                                           value="{{ $contacto->ap_paterno }}" maxlength="100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="font-size:12px;">Apellido materno</label>
                                    <input type="text" class="form-control input-sm ctc-ap-materno"
                                           value="{{ $contacto->ap_materno }}" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Teléfono celular</label>
                                    <input type="tel" class="form-control input-sm ctc-celular"
                                           value="{{ $contacto->telefono_celular }}" maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Teléfono trabajo</label>
                                    <input type="tel" class="form-control input-sm ctc-trabajo"
                                           value="{{ $contacto->telefono_trabajo }}" maxlength="20">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">Correo</label>
                                    <input type="email" class="form-control input-sm ctc-email"
                                           value="{{ $contacto->email }}" maxlength="200">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-size:12px;">CURP</label>
                                    <input type="text" class="form-control input-sm ctc-curp"
                                           value="{{ $contacto->curp }}" maxlength="18"
                                           style="text-transform:uppercase">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="checkbox-inline">
                                    <input type="checkbox" class="ctc-portal"
                                        {{ $contacto->tiene_acceso_portal ? 'checked' : '' }}>
                                    Acceso al portal familiar
                                </label>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-default btn-xs btn-cancelar-edicion"
                                        data-id="{{ $contacto->id }}">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-success btn-xs btn-guardar-ctc"
                                        data-id="{{ $contacto->id }}">
                                    <i class="fa fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                @empty
                <div style="padding:40px;text-align:center;">
                    <i class="fa fa-phone fa-3x text-muted" style="display:block;margin-bottom:10px;"></i>
                    <p class="text-muted">Sin contactos registrados.</p>
                </div>
                @endforelse

            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
         COLUMNA LATERAL
    ══════════════════════════════════════════════════════ --}}
    <div class="col-md-4">

        {{-- Info de la familia --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-home"></i> {{ $familia->apellido_familia }}
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table" style="font-size:13px;margin:0;">
                    <tr>
                        <th style="color:#999;font-weight:400;width:45%;padding:10px 16px;">Estado</th>
                        <td style="padding:10px 16px;">
                            @if($familia->activo)
                                <span class="label label-success">Activa</span>
                            @else
                                <span class="label label-default">Inactiva</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th style="color:#999;font-weight:400;padding:10px 16px;">Alumnos</th>
                        <td style="padding:10px 16px;">
                            {{ $familia->alumnos->count() }} registrado(s),
                            {{ $familia->alumnos->where('estado','activo')->count() }} activo(s)
                        </td>
                    </tr>
                    <tr>
                        <th style="color:#999;font-weight:400;padding:10px 16px;">Contactos</th>
                        <td style="padding:10px 16px;">
                            {{ $familia->contactos->count() }} registrado(s)
                        </td>
                    </tr>
                    @if($familia->observaciones)
                    <tr>
                        <th style="color:#999;font-weight:400;padding:10px 16px;">Notas</th>
                        <td style="padding:10px 16px;font-size:12px;">
                            {{ $familia->observaciones }}
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
            @can('administrador')
            <div class="box-footer">
                <a href="{{ route('familias.edit', $familia->id) }}"
                   class="btn btn-primary btn-sm btn-flat btn-block">
                    <i class="fa fa-pencil"></i> Editar familia
                </a>
            </div>
            @endcan
        </div>

        {{-- Estado de cuenta resumido --}}
        @if($familia->alumnos->count() > 0)
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size:13px;">
                    <i class="fa fa-dollar"></i> Estado de cuenta
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table" style="font-size:12px;margin:0;">
                    @foreach($familia->alumnos->where('estado','activo') as $alumno)
                    @php
                        $cargosAlumno = $alumno->inscripciones
                            ->flatMap(fn($i) => $i->cargos ?? collect())
                            ->whereIn('estado', ['pendiente','parcial']);
                        $deuda = $cargosAlumno->sum('monto_original');
                    @endphp
                    <tr>
                        <td style="padding:8px 16px;">
                            {{ $alumno->nombre }} {{ $alumno->ap_paterno }}
                        </td>
                        <td style="padding:8px 16px;text-align:right;">
                            @if($deuda > 0)
                                <span class="text-red">
                                    ${{ number_format($deuda, 2) }}
                                </span>
                            @else
                                <span class="text-green">Al corriente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection

@push('scripts')
<script>
$(function() {

    var FAMILIA_ID = {{ $familia->id }};

    // ── Alerta helper ─────────────────────────────────────
    function mostrarAlerta(msg, tipo) {
        $('#ctc-alerta-msg').text(msg);
        $('#ctc-alerta')
            .removeClass('alert-success alert-danger alert-warning')
            .addClass('alert-' + tipo)
            .show();
        if (tipo === 'success') setTimeout(function(){ $('#ctc-alerta').hide(); }, 4000);
    }

    // ── Nuevo contacto: cancelar ──────────────────────────
    $('#btn-cancelar-nctc').on('click', function() {
        $('#form-nuevo-ctc').hide();
        $('#ico-nuevo-ctc').removeClass('fa-minus').addClass('fa-plus');
        limpiarNuevo();
    });

    function limpiarNuevo() {
        $('#nctc-nombre,#nctc-ap-paterno,#nctc-ap-materno,' +
          '#nctc-celular,#nctc-trabajo,#nctc-email,#nctc-curp').val('');
        $('#nctc-portal').prop('checked', false);
    }

    // ── Nuevo contacto: guardar ───────────────────────────
    $('#btn-guardar-nctc').on('click', function() {
        var btn = $(this);
        var datos = {
            familia_id:          FAMILIA_ID,
            nombre:              $('#nctc-nombre').val().trim(),
            ap_paterno:          $('#nctc-ap-paterno').val().trim(),
            ap_materno:          $('#nctc-ap-materno').val().trim(),
            telefono_celular:    $('#nctc-celular').val().trim(),
            telefono_trabajo:    $('#nctc-trabajo').val().trim(),
            email:               $('#nctc-email').val().trim(),
            curp:                $('#nctc-curp').val().trim().toUpperCase(),
            tiene_acceso_portal: $('#nctc-portal').is(':checked'),
        };

        if (!datos.nombre) {
            mostrarAlerta('El nombre del contacto es obligatorio.', 'danger');
            $('#nctc-nombre').focus();
            return;
        }
        if (!datos.telefono_celular) {
            mostrarAlerta('El teléfono celular es obligatorio.', 'danger');
            $('#nctc-celular').focus();
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '/familias/contactos',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');
                limpiarNuevo();
                $('#form-nuevo-ctc').hide();
                $('#ico-nuevo-ctc').removeClass('fa-minus').addClass('fa-plus');
                mostrarAlerta(res.message || 'Contacto agregado.', 'success');
                // Recargar para mostrar el nuevo contacto
                setTimeout(function(){ location.reload(); }, 1200);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');
                var msg = xhr.responseJSON?.message || 'Error al agregar.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                mostrarAlerta(msg, 'danger');
            }
        });
    });

    // ── Editar contacto: abrir/cerrar inline ─────────────
    $(document).on('click', '.btn-editar-ctc', function() {
        var id = $(this).data('id');
        // Cerrar cualquier otro panel abierto
        $('.panel-edicion').not('#editar-ctc-' + id).hide();
        $('#editar-ctc-' + id).toggle();
    });

    $(document).on('click', '.btn-cancelar-edicion', function() {
        var id = $(this).data('id');
        $('#editar-ctc-' + id).hide();
    });

    // ── Editar contacto: guardar ──────────────────────────
    $(document).on('click', '.btn-guardar-ctc', function() {
        var id    = $(this).data('id');
        var panel = $('#editar-ctc-' + id);
        var btn   = $(this);
        var orig  = btn.html();

        var datos = {
            nombre:              panel.find('.ctc-nombre').val().trim(),
            ap_paterno:          panel.find('.ctc-ap-paterno').val().trim(),
            ap_materno:          panel.find('.ctc-ap-materno').val().trim(),
            telefono_celular:    panel.find('.ctc-celular').val().trim(),
            telefono_trabajo:    panel.find('.ctc-trabajo').val().trim(),
            email:               panel.find('.ctc-email').val().trim(),
            curp:                panel.find('.ctc-curp').val().trim().toUpperCase(),
            tiene_acceso_portal: panel.find('.ctc-portal').is(':checked'),
        };

        if (!datos.nombre) {
            mostrarAlerta('El nombre es obligatorio.', 'danger');
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/familias/contactos/' + id,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(datos),
            success: function(res) {
                btn.prop('disabled', false).html(orig);
                panel.hide();
                mostrarAlerta(res.message || 'Contacto actualizado.', 'success');
                setTimeout(function(){ location.reload(); }, 1200);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(orig);
                mostrarAlerta(xhr.responseJSON?.message || 'Error al guardar.', 'danger');
            }
        });
    });

    // ── Eliminar contacto ─────────────────────────────────
    $(document).on('click', '.btn-eliminar-ctc', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');
        var total  = $('.ctc-panel').length;

        if (total <= 1) {
            mostrarAlerta('Debe haber al menos un contacto familiar.', 'danger');
            return;
        }

        if (!confirm('¿Eliminar el contacto "' + nombre + '"?\nEsta acción no se puede deshacer.')) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/familias/contactos/' + id,
            method: 'DELETE',
            success: function(res) {
                mostrarAlerta(res.message || 'Contacto eliminado.', 'success');
                btn.closest('.ctc-panel').fadeOut(300, function(){ $(this).remove(); });
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
                mostrarAlerta(xhr.responseJSON?.message || 'Error al eliminar.', 'danger');
            }
        });
    });

});
</script>
@endpush
