@extends('layouts.master')

@section('page_title', $familia->apellido_familia)
@section('page_subtitle', 'Ficha de familia')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li class="active">{{ $familia->apellido_familia }}</li>
@endsection

@push('styles')
<style>
/* ── Perfil ───────────────────────────────── */
.perfil-header {
    background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
    padding: 28px 20px 20px;
    text-align: center;
    border-radius: 3px 3px 0 0;
}
.perfil-icono {
    width: 100px; height: 100px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    border: 4px solid rgba(255,255,255,.4);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.perfil-nombre {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
    margin: 0 0 2px;
}
.perfil-subtitulo {
    color: rgba(255,255,255,.7);
    font-size: 12px;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.perfil-estado { margin-top: 10px; }

/* ── Datos ────────────────────────────────── */
.dato-row {
    display: flex;
    align-items: baseline;
    padding: 10px 18px;
    border-bottom: 1px solid #f4f4f4;
    font-size: 13px;
}
.dato-row:last-child { border-bottom: none; }
.dato-label {
    color: #aaa;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
    width: 110px;
    flex-shrink: 0;
    padding-top: 1px;
}
.dato-valor {
    color: #222;
    font-size: 14px;
    font-weight: 500;
    flex: 1;
}

/* ── Acciones rápidas ─────────────────────── */
.accion-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; border-bottom: 1px solid #f4f4f4;
    text-decoration: none; color: #333;
    font-size: 14px; font-weight: 500;
    transition: background .12s;
}
.accion-btn:hover { background: #f5f9ff; text-decoration: none; color: #3c8dbc; }
.accion-btn:last-child { border-bottom: none; }
.accion-icon {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── Contactos ────────────────────────────── */
.ctc-card {
    border: 1px solid #e0e0e0;
    border-left: 5px solid #ccc;
    border-radius: 6px;
    margin-bottom: 12px;
    background: #fff;
    transition: box-shadow .15s;
}
.ctc-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.ctc-card.principal {
    border-left-color: #3c8dbc;
    background: #f7fbff;
}
.ctc-nombre-grande {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.2;
}
.ctc-tel {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #d0e8ff;
    background: #fff;
    text-decoration: none;
    color: #1a1a1a;
    margin-top: 6px;
    transition: background .12s;
}
.ctc-tel:hover { background: #e8f3ff; color: #1a1a1a; text-decoration: none; }
.ctc-tel-trabajo { border-color: #e0e0e0; }
.ctc-tel-trabajo:hover { background: #f5f5f5; }
.ctc-tel-icon {
    width: 34px; height: 34px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ctc-tel-num { font-size: 19px; font-weight: 700; letter-spacing: .04em; line-height: 1; }
.ctc-tel-label { font-size: 10px; color: #999; margin-top: 1px; }

/* ── Alumnos ────────────────────────────────── */
.alumno-row {
    padding: 14px 16px;
    border-bottom: 1px solid #f4f4f4;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: background .1s;
}
.alumno-row:hover { background: #fafcff; }
.alumno-nombre {
    font-size: 15px;
    font-weight: 600;
    color: #222;
}

/* ── Estado cuenta ──────────────────────────── */
.cuenta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid #f4f4f4;
    font-size: 13px;
}
.cuenta-row:last-child { border-bottom: none; }
.cuenta-monto-ok  { color: #00a65a; font-weight: 600; font-size: 14px; }
.cuenta-monto-red { color: #dd4b39; font-weight: 700; font-size: 15px; }
</style>
@endpush

@section('content')

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

{{-- ════════════════════════════════════════════════════
     COLUMNA IZQUIERDA (col-md-4)
════════════════════════════════════════════════════ --}}
<div class="col-md-4">

    {{-- ── PERFIL ── --}}
    <div class="box box-primary" style="overflow:hidden;">

        <div class="perfil-header">
            <div class="perfil-icono">
                <i class="fa fa-home" style="font-size:44px;color:rgba(255,255,255,.7);"></i>
            </div>

            <div class="perfil-nombre">{{ $familia->apellido_familia }}</div>
            <div class="perfil-subtitulo">Familia</div>

            <div class="perfil-estado">
                <span class="label label-{{ $familia->activo ? 'success' : 'default' }}"
                      style="font-size:13px;padding:4px 14px;">
                    <i class="fa fa-{{ $familia->activo ? 'circle' : 'circle-o' }}"></i>
                    {{ $familia->activo ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div style="padding:0;">
            @can('administrador')
            <a href="{{ route('familias.edit', $familia->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f0fb;">
                    <i class="fa fa-pencil" style="color:#3c8dbc;font-size:15px;"></i>
                </div>
                Editar familia
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#ccc;font-size:11px;"></i>
            </a>
            @endcan
            @can('administrador')
            <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f5e9;">
                    <i class="fa fa-user-plus" style="color:#4caf50;font-size:14px;"></i>
                </div>
                Inscribir alumno
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#ccc;font-size:11px;"></i>
            </a>
            @endcan
        </div>
    </div>

    {{-- ── DATOS DE LA FAMILIA ── --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-info-circle" style="color:#3c8dbc;"></i>
                Información
            </h3>
        </div>
        <div class="box-body no-padding">

            <div class="dato-row">
                <span class="dato-label">Alumnos</span>
                <span class="dato-valor">
                    {{ $familia->alumnos->count() }} registrado(s)
                    @if($familia->alumnos->where('estado','activo')->count() > 0)
                    <small style="color:#27ae60;font-weight:400;font-size:12px;">
                        · {{ $familia->alumnos->where('estado','activo')->count() }} activo(s)
                    </small>
                    @endif
                </span>
            </div>

            <div class="dato-row">
                <span class="dato-label">Contactos</span>
                <span class="dato-valor">
                    {{ $familia->contactos->count() }} registrado(s)
                </span>
            </div>

            @if($familia->observaciones)
            <div class="dato-row" style="align-items:flex-start;">
                <span class="dato-label" style="padding-top:2px;">Notas</span>
                <span class="dato-valor" style="font-size:13px;font-weight:400;color:#555;line-height:1.5;">
                    {{ $familia->observaciones }}
                </span>
            </div>
            @endif

        </div>
    </div>

    {{-- ── ESTADO DE CUENTA ── --}}
    @if($familia->alumnos->where('estado','activo')->count() > 0)
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size:13px;">
                <i class="fa fa-dollar" style="color:#f39c12;"></i>
                Estado de cuenta
            </h3>
        </div>
        <div class="box-body no-padding">
            @foreach($familia->alumnos->where('estado','activo') as $alumno)
            @php
                $cargosAlumno = $alumno->inscripciones
                    ->flatMap(fn($i) => $i->cargos ?? collect())
                    ->whereIn('estado', ['pendiente','parcial']);
                $deuda = $cargosAlumno->sum('monto_original');
            @endphp
            <div class="cuenta-row">
                <div>
                    <div style="font-size:13px;font-weight:600;color:#333;">
                        {{ $alumno->nombre }} {{ $alumno->ap_paterno }}
                    </div>
                    <div style="font-size:11px;color:#aaa;">
                        <code style="font-size:10px;">{{ $alumno->matricula }}</code>
                    </div>
                </div>
                <div style="text-align:right;">
                    @if($deuda > 0)
                        <div class="cuenta-monto-red">${{ number_format($deuda, 2) }}</div>
                        <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                           style="font-size:10px;color:#3c8dbc;">
                            Ver detalle <i class="fa fa-arrow-right"></i>
                        </a>
                    @else
                        <div class="cuenta-monto-ok">
                            <i class="fa fa-check-circle"></i> Al corriente
                        </div>
                        <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                           style="font-size:10px;color:#aaa;">
                            Ver historial <i class="fa fa-arrow-right"></i>
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /col-md-4 --}}

{{-- ════════════════════════════════════════════════════
     COLUMNA PRINCIPAL (col-md-8)
════════════════════════════════════════════════════ --}}
<div class="col-md-8">

    {{-- ── CONTACTOS FAMILIARES ── --}}
    <div class="box box-primary">
        <div class="box-header with-border"
             style="background:linear-gradient(135deg,#2c6fad,#3c8dbc);border-radius:3px 3px 0 0;">
            <h3 class="box-title" style="color:#fff;font-size:15px;">
                <i class="fa fa-phone"></i>
                Contactos familiares
                <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:10px;
                              padding:1px 8px;font-size:12px;margin-left:6px;">
                    {{ $familia->contactos->count() }}
                </span>
            </h3>
            <div class="box-tools pull-right">
                @can('administrador', 'recepcion')
                <button type="button" class="btn btn-xs btn-flat"
                        style="color:#fff;border:1px solid rgba(255,255,255,.5);background:rgba(255,255,255,.15);"
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

        {{-- Formulario nuevo contacto --}}
        <div id="form-nuevo-ctc" style="display:none;border-bottom:2px solid #e8f3ff;">
            <div style="padding:16px;background:#f0f7ff;">
                <h4 style="margin:0 0 14px;font-size:13px;color:#2c6fad;font-weight:700;">
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
                            <input type="tel" id="nctc-celular" class="form-control input-sm"
                                   maxlength="20" placeholder="10 dígitos">
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
                            <input type="text" id="nctc-curp" class="form-control input-sm"
                                   maxlength="18" style="text-transform:uppercase">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <label class="checkbox-inline" style="font-size:12px;">
                            <input type="checkbox" id="nctc-portal">
                            Habilitar acceso al portal familiar
                        </label>
                    </div>
                    <div class="col-md-7 text-right">
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

        <div class="box-body" style="padding:14px 14px 6px;" id="contenedor-contactos">

            @forelse($familia->contactos->sortBy('pivot.orden') as $contacto)
            @php
                $pivot       = $contacto->pivot;
                $esPrincipal = $pivot && $pivot->orden == 1;
                $ac          = $contacto->alumnoContactos->first();
            @endphp

            <div class="ctc-panel ctc-card {{ $esPrincipal ? 'principal' : '' }}"
                 data-id="{{ $contacto->id }}">

                <div style="padding:14px 16px;">
                    <div style="display:flex;align-items:flex-start;gap:12px;">

                        {{-- Avatar --}}
                        <div style="flex-shrink:0;">
                            @if($contacto->foto_url)
                                <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                                     style="width:50px;height:50px;border-radius:50%;object-fit:cover;
                                            border:2px solid {{ $esPrincipal ? '#3c8dbc' : '#e0e0e0' }};">
                            @else
                                <div style="width:50px;height:50px;border-radius:50%;
                                            background:{{ $esPrincipal ? '#3c8dbc' : '#bdbdbd' }};
                                            display:flex;align-items:center;justify-content:center;">
                                    <i class="fa fa-user" style="color:#fff;font-size:20px;"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Nombre y badges --}}
                        <div style="flex:1;min-width:0;">
                            <div class="ctc-nombre-grande">
                                {{ $contacto->nombre }}
                                {{ $contacto->ap_paterno }}
                                {{ $contacto->ap_materno }}
                            </div>
                            <div style="margin-top:5px;display:flex;gap:5px;flex-wrap:wrap;">
                                @if($esPrincipal)
                                    <span style="background:#3c8dbc;color:#fff;font-size:10px;font-weight:700;
                                                 padding:2px 9px;border-radius:10px;letter-spacing:.03em;">
                                        PRINCIPAL
                                    </span>
                                @endif
                                @if($ac && $ac->parentesco)
                                    <span style="background:#f0f0f0;color:#555;font-size:11px;
                                                 padding:2px 9px;border-radius:10px;font-weight:600;">
                                        {{ ucfirst($ac->parentesco) }}
                                    </span>
                                @endif
                                @if($ac && $ac->autorizado_recoger)
                                    <span style="background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:600;
                                                 padding:2px 9px;border-radius:10px;">
                                        <i class="fa fa-check"></i> Autorizado recoger
                                    </span>
                                @endif
                                @if($contacto->tiene_acceso_portal)
                                    <span style="background:#e3f2fd;color:#1565c0;font-size:10px;font-weight:600;
                                                 padding:2px 9px;border-radius:10px;">
                                        <i class="fa fa-globe"></i> Portal
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Botones editar/eliminar --}}
                        @can('administrador', 'recepcion')
                        <div style="flex-shrink:0;display:flex;gap:4px;">
                            <button type="button"
                                    class="btn btn-default btn-xs btn-flat btn-editar-ctc"
                                    data-id="{{ $contacto->id }}" title="Editar">
                                <i class="fa fa-pencil"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-danger btn-xs btn-flat btn-eliminar-ctc"
                                    data-id="{{ $contacto->id }}"
                                    data-nombre="{{ $contacto->nombre }} {{ $contacto->ap_paterno }}"
                                    title="Eliminar">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        @endcan
                    </div>

                    {{-- Teléfonos grandes y clicables --}}
                    <div style="margin-top:10px;">
                        @if($contacto->telefono_celular)
                        <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_celular) }}"
                           class="ctc-tel">
                            <div class="ctc-tel-icon" style="background:#3c8dbc;">
                                <i class="fa fa-mobile" style="color:#fff;font-size:18px;"></i>
                            </div>
                            <div>
                                <div class="ctc-tel-num">{{ $contacto->telefono_celular }}</div>
                                <div class="ctc-tel-label">Celular</div>
                            </div>
                            <i class="fa fa-phone" style="margin-left:auto;color:#3c8dbc;font-size:13px;"></i>
                        </a>
                        @endif

                        @if($contacto->telefono_trabajo)
                        <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_trabajo) }}"
                           class="ctc-tel ctc-tel-trabajo">
                            <div class="ctc-tel-icon" style="background:#607d8b;">
                                <i class="fa fa-phone" style="color:#fff;font-size:15px;"></i>
                            </div>
                            <div>
                                <div class="ctc-tel-num">{{ $contacto->telefono_trabajo }}</div>
                                <div class="ctc-tel-label">Trabajo</div>
                            </div>
                            <i class="fa fa-phone" style="margin-left:auto;color:#aaa;font-size:12px;"></i>
                        </a>
                        @endif

                        @if($contacto->email)
                        <div style="display:flex;align-items:center;gap:10px;padding:6px 4px;font-size:13px;color:#555;">
                            <div class="ctc-tel-icon" style="background:#f5f5f5;border:1px solid #e0e0e0;">
                                <i class="fa fa-envelope-o" style="color:#888;font-size:14px;"></i>
                            </div>
                            {{ $contacto->email }}
                        </div>
                        @endif

                        @if($contacto->curp)
                        <div style="font-size:11px;color:#aaa;margin-top:4px;padding-left:4px;">
                            <i class="fa fa-id-card-o"></i>
                            <code style="font-size:11px;">{{ $contacto->curp }}</code>
                        </div>
                        @endif

                        @if(!$contacto->telefono_celular && !$contacto->telefono_trabajo && !$contacto->email)
                        <div style="font-size:12px;color:#ccc;padding:6px 4px;">
                            <i class="fa fa-info-circle"></i> Sin datos de contacto registrados
                        </div>
                        @endif
                    </div>

                    {{-- Alumnos vinculados --}}
                    @if($contacto->alumnoContactos && $contacto->alumnoContactos->count())
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #f0f0f0;
                                display:flex;gap:4px;flex-wrap:wrap;">
                        @foreach($contacto->alumnoContactos as $ac)
                            <span style="background:#f5f5f5;color:#666;font-size:11px;
                                         padding:2px 8px;border-radius:10px;border:1px solid #e8e8e8;">
                                {{ ucfirst($ac->parentesco) }}
                                — {{ $ac->alumno->nombre ?? '' }} {{ $ac->alumno->ap_paterno ?? '' }}
                                @if($ac->autorizado_recoger)
                                    <i class="fa fa-check" style="color:#4caf50;"
                                       title="Autorizado para recoger"></i>
                                @endif
                            </span>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Panel edición inline --}}
                <div class="panel-edicion" id="editar-ctc-{{ $contacto->id }}"
                     style="display:none;margin:0 14px 14px;padding:14px;
                            background:#f8f8f8;border-radius:6px;border:1px solid #e0e0e0;">
                    <h5 style="margin:0 0 12px;font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.05em;">
                        <i class="fa fa-pencil"></i> Editando contacto
                    </h5>
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
                            <label class="checkbox-inline" style="font-size:12px;">
                                <input type="checkbox" class="ctc-portal"
                                    {{ $contacto->tiene_acceso_portal ? 'checked' : '' }}>
                                Acceso al portal familiar
                            </label>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-default btn-xs btn-cancelar-edicion"
                                    data-id="{{ $contacto->id }}">Cancelar</button>
                            <button type="button" class="btn btn-success btn-xs btn-guardar-ctc"
                                    data-id="{{ $contacto->id }}">
                                <i class="fa fa-save"></i> Guardar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            @empty
            <div style="padding:40px;text-align:center;color:#ccc;">
                <i class="fa fa-phone" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                Sin contactos registrados.
            </div>
            @endforelse

        </div>
    </div>

    {{-- ── ALUMNOS ── --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
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
                $inscripcion = $alumno->inscripciones->sortByDesc('id')->first();
                $estadoColor = match($alumno->estado) {
                    'activo'          => 'success',
                    'baja_temporal'   => 'warning',
                    'baja_definitiva' => 'danger',
                    'egresado'        => 'default',
                    default           => 'default',
                };
            @endphp

            <div class="alumno-row">
                {{-- Foto --}}
                <div style="flex-shrink:0;">
                    @if($alumno->foto_url)
                        <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                             alt="{{ $alumno->nombre }}"
                             style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #e0e0e0;">
                    @else
                        <div style="width:52px;height:52px;border-radius:50%;background:#e8e8e8;
                                    display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-user" style="font-size:22px;color:#bbb;"></i>
                        </div>
                    @endif
                </div>

                {{-- Datos --}}
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <a href="{{ route('alumnos.show', $alumno->id) }}"
                           class="alumno-nombre" style="text-decoration:none;">
                            {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                        </a>
                        <span class="label label-{{ $estadoColor }}">
                            {{ ucfirst(str_replace('_',' ', $alumno->estado)) }}
                        </span>
                    </div>
                    <div style="margin-top:5px;font-size:12px;color:#999;display:flex;gap:16px;flex-wrap:wrap;">
                        <span>
                            <i class="fa fa-id-badge"></i>
                            <code style="font-size:11px;background:#f5f5f5;padding:0 4px;border-radius:2px;">
                                {{ $alumno->matricula }}
                            </code>
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
                <div style="flex-shrink:0;display:flex;gap:4px;">
                    <a href="{{ route('alumnos.show', $alumno->id) }}"
                       class="btn btn-default btn-xs btn-flat" title="Ver alumno">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                       class="btn btn-warning btn-xs btn-flat" title="Estado de cuenta">
                        <i class="fa fa-dollar"></i>
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
            <div style="padding:40px;text-align:center;color:#ccc;">
                <i class="fa fa-graduation-cap" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                <strong style="color:#bbb;">Sin alumnos inscritos</strong>
                @can('administrador')
                <div style="margin-top:12px;">
                    <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}"
                       class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Inscribir primer alumno
                    </a>
                </div>
                @endcan
            </div>
            @endforelse

        </div>
    </div>

    {{-- ── DATOS DE FACTURACIÓN ── --}}
    @include('familias._razon_social')

</div>{{-- /col-md-8 --}}

</div>{{-- /row --}}
@endsection

@push('scripts')
<script>
$(function() {

    var FAMILIA_ID = {{ $familia->id }};

    function mostrarAlerta(msg, tipo) {
        $('#ctc-alerta-msg').text(msg);
        $('#ctc-alerta')
            .removeClass('alert-success alert-danger alert-warning')
            .addClass('alert-' + tipo)
            .show();
        if (tipo === 'success') setTimeout(function(){ $('#ctc-alerta').hide(); }, 4000);
    }

    // ── Nuevo contacto ────────────────────────────────────
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
            $('#nctc-nombre').focus(); return;
        }
        if (!datos.telefono_celular) {
            mostrarAlerta('El teléfono celular es obligatorio.', 'danger');
            $('#nctc-celular').focus(); return;
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
                setTimeout(function(){ location.reload(); }, 1200);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-plus"></i> Agregar contacto');
                var msg = xhr.responseJSON?.message || 'Error al agregar.';
                if (xhr.responseJSON?.errors) msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                mostrarAlerta(msg, 'danger');
            }
        });
    });

    // ── Editar contacto inline ────────────────────────────
    $(document).on('click', '.btn-editar-ctc', function() {
        var id = $(this).data('id');
        $('.panel-edicion').not('#editar-ctc-' + id).hide();
        $('#editar-ctc-' + id).toggle();
    });

    $(document).on('click', '.btn-cancelar-edicion', function() {
        $('#editar-ctc-' + $(this).data('id')).hide();
    });

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

        if (!datos.nombre) { mostrarAlerta('El nombre es obligatorio.', 'danger'); return; }

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

        if ($('.ctc-panel').length <= 1) {
            mostrarAlerta('Debe haber al menos un contacto familiar.', 'danger'); return;
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
