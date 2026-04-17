@extends('layouts.master')

@section('page_title', $familia->apellido_familia)
@section('page_subtitle', 'Ficha de familia')

@section('breadcrumb')
    <li><a href="{{ route('familias.index') }}">Familias</a></li>
    <li class="active">{{ $familia->apellido_familia }}</li>
@endsection

@push('styles')
<style>
/* ════════════════════════════════════════════
   HERO DE FAMILIA
════════════════════════════════════════════ */
.fam-hero {
    background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
    border-radius: 8px;
    padding: 24px 28px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 22px;
    flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(60,141,188,.25);
}
.fam-hero-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.fam-hero-nombre { font-size: 24px; font-weight: 800; color: #fff; line-height: 1.1; }
.fam-hero-sub    { font-size: 13px; color: rgba(255,255,255,.7); margin-top: 4px; }
.fam-hero-stats  { display: flex; gap: 18px; margin-left: auto; flex-wrap: wrap; }
.fam-hero-stat   { text-align: center; }
.fam-hero-stat-num { font-size: 26px; font-weight: 800; color: #fff; line-height: 1; }
.fam-hero-stat-lbl { font-size: 10px; color: rgba(255,255,255,.65); margin-top: 2px;
                     text-transform: uppercase; letter-spacing: .06em; }

/* ════════════════════════════════════════════
   SECCIÓN TÍTULOS
════════════════════════════════════════════ */
.sec-title {
    font-size: 13px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7a8d;
    margin: 0 0 14px;
    display: flex; align-items: center; gap: 8px;
}
.sec-title::after {
    content: ''; flex: 1; height: 1px; background: #e8ecf0;
}

/* ════════════════════════════════════════════
   CONTACTOS
════════════════════════════════════════════ */
.ctc-card {
    border: 1px solid #e4eaf0;
    border-radius: 10px;
    margin-bottom: 14px;
    background: #fff;
    overflow: hidden;
    transition: box-shadow .15s, transform .1s;
}
.ctc-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); transform: translateY(-1px); }
.ctc-card.principal { border-color: #b8d4ec; border-left: 4px solid #3c8dbc; }

.ctc-head {
    padding: 14px 16px;
    display: flex; align-items: center; gap: 14px;
    background: #fff;
}
.ctc-card.principal .ctc-head { background: #f0f7ff; }

.ctc-avatar {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 18px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, #3c8dbc, #2c6fad);
    box-shadow: 0 2px 6px rgba(60,141,188,.25);
}
.ctc-card:not(.principal) .ctc-avatar {
    background: linear-gradient(135deg, #9ab, #6b7a8d);
    box-shadow: none;
}

.ctc-nombre { font-size: 16px; font-weight: 700; color: #1a2634; line-height: 1.2; }
.ctc-badges { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
.ctc-badge  {
    font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px;
    letter-spacing: .03em; display: inline-flex; align-items: center; gap: 3px;
}
.ctc-badge-principal  { background: #3c8dbc; color: #fff; }
.ctc-badge-parentesco { background: #f0f3f7; color: #5a6a7a; border: 1px solid #dde4eb; }
.ctc-badge-recoger    { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.ctc-badge-portal     { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
.ctc-badge-pagos      { background: #fff8e1; color: #b45309; border: 1px solid #fde68a; }

.ctc-contacto-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
    padding: 12px 16px; border-top: 1px solid #f0f3f7;
    background: #fafbfc;
}
@media (max-width: 480px) { .ctc-contacto-grid { grid-template-columns: 1fr; } }

.ctc-dato {
    display: flex; align-items: center; gap: 10px;
    border-radius: 8px; padding: 8px 12px;
    border: 1px solid #e8ecf0; background: #fff;
    text-decoration: none; color: inherit;
    transition: background .12s, border-color .12s;
    min-width: 0;
}
.ctc-dato:hover { background: #eef5fb; border-color: #b8d4ec; color: inherit; text-decoration: none; }
.ctc-dato.no-link:hover { background: #fff; border-color: #e8ecf0; cursor: default; }
.ctc-dato-icon {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ctc-dato-val  { font-size: 14px; font-weight: 700; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ctc-dato-lbl  { font-size: 10px; color: #9aa; margin-top: 1px; }

.ctc-alumnos-row {
    padding: 8px 16px; border-top: 1px solid #f0f3f7;
    font-size: 11px; color: #7a8a9a; background: #fafbfc;
    display: flex; gap: 6px; flex-wrap: wrap; align-items: center;
}
.ctc-alumno-chip {
    background: #f0f3f7; border: 1px solid #dde4eb;
    border-radius: 12px; padding: 2px 10px;
    font-size: 11px; color: #4a5568;
}

/* ════════════════════════════════════════════
   ALUMNOS
════════════════════════════════════════════ */
.alm-card {
    border: 1px solid #e4eaf0;
    border-radius: 10px;
    margin-bottom: 12px;
    background: #fff;
    overflow: hidden;
    transition: box-shadow .15s;
    display: flex;
    align-items: stretch;
}
.alm-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); }

.alm-card-accent {
    width: 5px; flex-shrink: 0;
    background: #e0e0e0;
}
.alm-card-accent.activo          { background: #00a65a; }
.alm-card-accent.baja_temporal   { background: #f39c12; }
.alm-card-accent.baja_definitiva { background: #dd4b39; }
.alm-card-accent.egresado        { background: #8e44ad; }

.alm-card-body {
    flex: 1; padding: 14px 16px;
    display: flex; align-items: center; gap: 14px;
}

.alm-foto {
    width: 54px; height: 54px; border-radius: 50%;
    object-fit: cover; border: 2px solid #e8ecf0;
    flex-shrink: 0;
}
.alm-foto-placeholder {
    width: 54px; height: 54px; border-radius: 50%;
    background: linear-gradient(135deg, #e0e7ef, #c8d6e5);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.alm-nombre { font-size: 15px; font-weight: 700; color: #1a2634; }
.alm-info   { font-size: 12px; color: #8a9ab0; margin-top: 4px; display: flex; gap: 14px; flex-wrap: wrap; }
.alm-info i { margin-right: 3px; }

.alm-estado-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; padding: 2px 9px; border-radius: 10px;
    margin-left: 8px; vertical-align: middle;
}
.alm-badge-activo          { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; }
.alm-badge-baja_temporal   { background:#fff8e6; color:#b45309; border:1px solid #fcd97d; }
.alm-badge-baja_definitiva { background:#fdecea; color:#b91c1c; border:1px solid #fca5a5; }
.alm-badge-egresado        { background:#f3e8fd; color:#6b21a8; border:1px solid #d8b4fe; }

.alm-acciones { flex-shrink: 0; display: flex; gap: 4px; align-items: center; }

/* ════════════════════════════════════════════
   SIDEBAR CARDS
════════════════════════════════════════════ */
.info-card {
    border: 1px solid #e4eaf0;
    border-radius: 10px;
    background: #fff;
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.info-card-header {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f3f7;
    display: flex; align-items: center; justify-content: space-between;
    background: #f8fafc;
}
.info-card-title { font-size: 12px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .07em; color: #6b7a8d; }
.info-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 11px 16px; border-bottom: 1px solid #f5f7fa; font-size: 13px;
}
.info-row:last-child { border-bottom: none; }
.info-row-label { color: #8a9ab0; font-size: 12px; }
.info-row-value { font-weight: 600; color: #1a2634; text-align: right; }

/* Estado de cuenta */
.cuenta-alumno {
    padding: 13px 16px; border-bottom: 1px solid #f0f3f7;
    display: flex; align-items: center; justify-content: space-between;
}
.cuenta-alumno:last-child { border-bottom: none; }
.cuenta-deuda { color: #dd4b39; font-weight: 800; font-size: 15px; }
.cuenta-ok    { color: #00a65a; font-weight: 700; font-size: 13px; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" style="border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible" style="border-radius:8px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

@php
    $totalAlumnos  = $familia->alumnos->count();
    $activos       = $familia->alumnos->where('estado','activo')->count();
    $totalContactos = $familia->contactos->count();
    $deudaTotal    = $familia->alumnos->where('estado','activo')->sum(function($a) {
        return $a->inscripciones->flatMap(fn($i) => $i->cargos ?? collect())
            ->whereIn('estado', ['pendiente','parcial'])->sum('monto_original');
    });
@endphp

{{-- ══ HERO ══ --}}
<div class="fam-hero">
    <div class="fam-hero-icon">
        <i class="fa fa-home" style="color:#fff;font-size:28px;"></i>
    </div>
    <div>
        <div class="fam-hero-nombre">Familia {{ $familia->apellido_familia }}</div>
        <div class="fam-hero-sub">
            <span class="label" style="background:rgba(255,255,255,.2);color:#fff;border-radius:10px;font-size:11px;">
                {{ $familia->activo ? 'Activa' : 'Inactiva' }}
            </span>
            @if($familia->observaciones)
                &nbsp;·&nbsp; <span>{{ $familia->observaciones }}</span>
            @endif
        </div>
    </div>
    <div class="fam-hero-stats">
        <div class="fam-hero-stat">
            <div class="fam-hero-stat-num">{{ $totalAlumnos }}</div>
            <div class="fam-hero-stat-lbl">Alumno{{ $totalAlumnos != 1 ? 's' : '' }}</div>
        </div>
        <div class="fam-hero-stat" style="border-left:1px solid rgba(255,255,255,.2);padding-left:18px;">
            <div class="fam-hero-stat-num">{{ $activos }}</div>
            <div class="fam-hero-stat-lbl">Activos</div>
        </div>
        <div class="fam-hero-stat" style="border-left:1px solid rgba(255,255,255,.2);padding-left:18px;">
            <div class="fam-hero-stat-num">{{ $totalContactos }}</div>
            <div class="fam-hero-stat-lbl">Contacto{{ $totalContactos != 1 ? 's' : '' }}</div>
        </div>
        @if($deudaTotal > 0)
        <div class="fam-hero-stat" style="border-left:1px solid rgba(255,255,255,.2);padding-left:18px;">
            <div class="fam-hero-stat-num" style="color:#ffcdd2;">${{ number_format($deudaTotal,0) }}</div>
            <div class="fam-hero-stat-lbl">Saldo pendiente</div>
        </div>
        @else
        <div class="fam-hero-stat" style="border-left:1px solid rgba(255,255,255,.2);padding-left:18px;">
            <div class="fam-hero-stat-num" style="color:#c8e6c9;"><i class="fa fa-check"></i></div>
            <div class="fam-hero-stat-lbl">Al corriente</div>
        </div>
        @endif
        @can('administrador')
        <div style="border-left:1px solid rgba(255,255,255,.2);padding-left:18px;align-self:center;">
            <a href="{{ route('familias.edit', $familia->id) }}"
               class="btn btn-sm btn-flat"
               style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;">
                <i class="fa fa-pencil"></i> Editar
            </a>
        </div>
        @endcan
    </div>
</div>

<div class="row">

{{-- ════════════════════════════════════════════════════
     COLUMNA PRINCIPAL (col-md-8)
════════════════════════════════════════════════════ --}}
<div class="col-md-8">

    {{-- ── CONTACTOS ── --}}
    <div style="margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <p class="sec-title" style="flex:1;margin:0;">
                <i class="fa fa-phone" style="color:#3c8dbc;"></i>
                Contactos familiares
                <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                             padding:2px 9px;border-radius:10px;">{{ $totalContactos }}</span>
            </p>
            @can('administrador', 'recepcion')
            <button type="button" class="btn btn-success btn-xs btn-flat"
                    id="btn-toggle-nuevo-ctc"
                    style="border-radius:6px;margin-left:12px;"
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

        {{-- Formulario nuevo contacto --}}
        <div id="form-nuevo-ctc"
             style="display:none;border:1px solid #b8d4ec;border-radius:10px;
                    background:#f0f7ff;padding:18px;margin-bottom:16px;">
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
                        <input type="text" id="nctc-curp" class="form-control input-sm"
                               maxlength="18" style="text-transform:uppercase">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="checkbox-inline" style="font-size:12px;">
                        <input type="checkbox" id="nctc-portal"> Habilitar acceso al portal familiar
                    </label>
                </div>
                <div class="col-md-6 text-right">
                    <button type="button" class="btn btn-default btn-sm" id="btn-cancelar-nctc">Cancelar</button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-guardar-nctc">
                        <i class="fa fa-plus"></i> Agregar contacto
                    </button>
                </div>
            </div>
        </div>

        {{-- Alerta AJAX --}}
        <div id="ctc-alerta" style="display:none;" class="alert alert-dismissible" style="border-radius:8px;">
            <button type="button" class="close" onclick="$('#ctc-alerta').hide()">&times;</button>
            <span id="ctc-alerta-msg"></span>
        </div>

        {{-- Lista de contactos --}}
        <div id="contenedor-contactos">
        @forelse($familia->contactos->sortBy('pivot.orden') as $contacto)
        @php
            $pivot       = $contacto->pivot;
            $esPrincipal = $pivot && $pivot->orden == 1;
            $inicial     = mb_strtoupper(mb_substr($contacto->nombre, 0, 1));
        @endphp

        <div class="ctc-panel ctc-card {{ $esPrincipal ? 'principal' : '' }}"
             data-id="{{ $contacto->id }}">

            {{-- Cabecera del contacto --}}
            <div class="ctc-head">
                @if($contacto->foto_url)
                    <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                         style="width:48px;height:48px;border-radius:50%;object-fit:cover;
                                border:2px solid {{ $esPrincipal ? '#3c8dbc' : '#e0e0e0' }};flex-shrink:0;">
                @else
                    <div class="ctc-avatar">{{ $inicial }}</div>
                @endif

                <div style="flex:1;min-width:0;">
                    <div class="ctc-nombre">
                        {{ $contacto->nombre }} {{ $contacto->ap_paterno }} {{ $contacto->ap_materno }}
                    </div>
                    <div class="ctc-badges">
                        @if($esPrincipal)
                            <span class="ctc-badge ctc-badge-principal">
                                <i class="fa fa-star" style="font-size:8px;"></i> Principal
                            </span>
                        @endif
                        @foreach($contacto->alumnoContactos as $ac)
                            @if($ac->parentesco)
                            <span class="ctc-badge ctc-badge-parentesco">{{ ucfirst($ac->parentesco) }}</span>
                            @endif
                            @if($ac->autorizado_recoger)
                            <span class="ctc-badge ctc-badge-recoger">
                                <i class="fa fa-check"></i> Recoger
                            </span>
                            @endif
                            @if($ac->es_responsable_pago)
                            <span class="ctc-badge ctc-badge-pagos">
                                <i class="fa fa-dollar"></i> Pagos
                            </span>
                            @endif
                        @endforeach
                        @if($contacto->tiene_acceso_portal)
                            <span class="ctc-badge ctc-badge-portal">
                                <i class="fa fa-globe"></i> Portal
                            </span>
                        @endif
                    </div>
                </div>

                @can('administrador', 'recepcion')
                <div style="flex-shrink:0;display:flex;gap:4px;">
                    <button type="button"
                            class="btn btn-default btn-xs btn-flat btn-editar-ctc"
                            data-id="{{ $contacto->id }}" title="Editar contacto">
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

            {{-- Datos de contacto en grid --}}
            @if($contacto->telefono_celular || $contacto->telefono_trabajo || $contacto->email || $contacto->curp)
            <div class="ctc-contacto-grid">
                @if($contacto->telefono_celular)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_celular) }}" class="ctc-dato">
                    <div class="ctc-dato-icon" style="background:#eaf3fb;">
                        <i class="fa fa-mobile" style="color:#3c8dbc;font-size:18px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val">{{ $contacto->telefono_celular }}</div>
                        <div class="ctc-dato-lbl">Celular</div>
                    </div>
                    <i class="fa fa-angle-right" style="margin-left:auto;color:#b0bec5;"></i>
                </a>
                @endif

                @if($contacto->telefono_trabajo)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_trabajo) }}" class="ctc-dato">
                    <div class="ctc-dato-icon" style="background:#eceff1;">
                        <i class="fa fa-phone" style="color:#607d8b;font-size:15px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val">{{ $contacto->telefono_trabajo }}</div>
                        <div class="ctc-dato-lbl">Trabajo</div>
                    </div>
                    <i class="fa fa-angle-right" style="margin-left:auto;color:#b0bec5;"></i>
                </a>
                @endif

                @if($contacto->email)
                <a href="mailto:{{ $contacto->email }}" class="ctc-dato" style="grid-column: {{ ($contacto->telefono_celular && $contacto->telefono_trabajo) ? 'span 2' : 'auto' }};">
                    <div class="ctc-dato-icon" style="background:#f3e8fd;">
                        <i class="fa fa-envelope-o" style="color:#8e44ad;font-size:14px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val" style="font-size:13px;">{{ $contacto->email }}</div>
                        <div class="ctc-dato-lbl">Correo electrónico</div>
                    </div>
                </a>
                @endif

                @if($contacto->curp)
                <div class="ctc-dato no-link">
                    <div class="ctc-dato-icon" style="background:#e8f5e9;">
                        <i class="fa fa-id-card-o" style="color:#2e7d32;font-size:13px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val" style="font-size:12px;font-family:monospace;">{{ $contacto->curp }}</div>
                        <div class="ctc-dato-lbl">CURP</div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Alumnos vinculados --}}
            @if($contacto->alumnoContactos && $contacto->alumnoContactos->count())
            <div class="ctc-alumnos-row">
                <i class="fa fa-graduation-cap" style="color:#9ab;"></i>
                @foreach($contacto->alumnoContactos as $ac)
                <span class="ctc-alumno-chip">
                    {{ $ac->alumno->nombre ?? '' }} {{ $ac->alumno->ap_paterno ?? '' }}
                </span>
                @endforeach
            </div>
            @endif

            {{-- Panel edición inline --}}
            <div class="panel-edicion" id="editar-ctc-{{ $contacto->id }}"
                 style="display:none;margin:0 14px 14px;padding:14px;
                        background:#f8f9fa;border-radius:8px;border:1px solid #dde4eb;">
                <h5 style="margin:0 0 12px;font-size:11px;color:#8a9ab0;text-transform:uppercase;letter-spacing:.06em;">
                    <i class="fa fa-pencil"></i> Editar datos del contacto
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
                            <label style="font-size:12px;">Celular</label>
                            <input type="tel" class="form-control input-sm ctc-celular"
                                   value="{{ $contacto->telefono_celular }}" maxlength="20">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label style="font-size:12px;">Trabajo</label>
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
                                   value="{{ $contacto->curp }}" maxlength="18" style="text-transform:uppercase">
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
                            <i class="fa fa-save"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div style="padding:48px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:10px;">
            <i class="fa fa-phone" style="font-size:40px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
            <p style="color:#b0bec5;margin:0;">Sin contactos registrados.</p>
        </div>
        @endforelse
        </div>
    </div>

    {{-- ── ALUMNOS ── --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <p class="sec-title" style="flex:1;margin:0;">
                <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
                Alumnos inscritos
                <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                             padding:2px 9px;border-radius:10px;">{{ $totalAlumnos }}</span>
            </p>
            @can('administrador')
            <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}"
               class="btn btn-success btn-xs btn-flat"
               style="border-radius:6px;margin-left:12px;">
                <i class="fa fa-plus"></i> Inscribir alumno
            </a>
            @endcan
        </div>

        @forelse($familia->alumnos->sortBy('ap_paterno') as $alumno)
        @php
            $inscripcion = $alumno->inscripciones->sortByDesc('id')->first();
            $estado      = $alumno->estado;
        @endphp

        <div class="alm-card">
            <div class="alm-card-accent {{ $estado }}"></div>
            <div class="alm-card-body">
                {{-- Foto --}}
                @if($alumno->foto_url)
                    <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                         alt="{{ $alumno->nombre }}" class="alm-foto">
                @else
                    <div class="alm-foto-placeholder">
                        <i class="fa fa-user" style="font-size:22px;color:#9ab;"></i>
                    </div>
                @endif

                {{-- Info --}}
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:4px;">
                        <a href="{{ route('alumnos.show', $alumno->id) }}"
                           class="alm-nombre" style="text-decoration:none;">
                            {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
                        </a>
                        <span class="alm-estado-badge alm-badge-{{ $estado }}">
                            <i class="fa fa-circle" style="font-size:7px;"></i>
                            @switch($estado)
                                @case('activo')          Activo          @break
                                @case('baja_temporal')   Baja temporal   @break
                                @case('baja_definitiva') Baja definitiva @break
                                @case('egresado')        Egresado        @break
                                @default {{ ucfirst($estado) }}
                            @endswitch
                        </span>
                    </div>
                    <div class="alm-info">
                        <span title="Matrícula">
                            <i class="fa fa-id-badge"></i>
                            <code style="font-size:11px;background:#f0f3f7;padding:0 5px;border-radius:3px;color:#4a5568;">
                                {{ $alumno->matricula }}
                            </code>
                        </span>
                        @if($alumno->fecha_nacimiento)
                        <span title="Edad">
                            <i class="fa fa-birthday-cake"></i>
                            {{ $alumno->fecha_nacimiento->age }} años
                        </span>
                        @endif
                        @if($inscripcion)
                        <span title="Grupo">
                            <i class="fa fa-graduation-cap"></i>
                            {{ $inscripcion->grupo->grado->nivel->nombre ?? '' }}
                            · {{ $inscripcion->grupo->grado->nombre }}°
                            {{ $inscripcion->grupo->nombre }}
                        </span>
                        @else
                        <span style="color:#e0e0e0;font-style:italic;">Sin inscripción activa</span>
                        @endif
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="alm-acciones">
                    <a href="{{ route('alumnos.show', $alumno->id) }}"
                       class="btn btn-default btn-xs btn-flat" title="Ver ficha">
                        <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                       class="btn btn-info btn-xs btn-flat" title="Estado de cuenta">
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
        </div>

        @empty
        <div style="padding:48px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:10px;">
            <i class="fa fa-graduation-cap" style="font-size:40px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
            <p style="color:#b0bec5;margin:0 0 14px;">Esta familia no tiene alumnos inscritos.</p>
            @can('administrador')
            <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}"
               class="btn btn-success btn-sm" style="border-radius:20px;">
                <i class="fa fa-plus"></i> Inscribir primer alumno
            </a>
            @endcan
        </div>
        @endforelse
    </div>

</div>{{-- /col-md-8 --}}

{{-- ════════════════════════════════════════════════════
     COLUMNA LATERAL (col-md-4)
════════════════════════════════════════════════════ --}}
<div class="col-md-4">

    {{-- Info rápida de la familia --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title"><i class="fa fa-home" style="margin-right:6px;color:#3c8dbc;"></i>Datos de familia</span>
            @can('administrador')
            <a href="{{ route('familias.edit', $familia->id) }}"
               class="btn btn-xs btn-flat btn-default" style="border-radius:4px;">
                <i class="fa fa-pencil"></i> Editar
            </a>
            @endcan
        </div>
        <div class="info-row">
            <span class="info-row-label">Estado</span>
            <span class="info-row-value">
                <span class="label label-{{ $familia->activo ? 'success' : 'default' }}" style="border-radius:8px;">
                    {{ $familia->activo ? 'Activa' : 'Inactiva' }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-row-label">Total alumnos</span>
            <span class="info-row-value">{{ $totalAlumnos }}</span>
        </div>
        <div class="info-row">
            <span class="info-row-label">Alumnos activos</span>
            <span class="info-row-value" style="color:#00a65a;">{{ $activos }}</span>
        </div>
        <div class="info-row">
            <span class="info-row-label">Contactos</span>
            <span class="info-row-value">{{ $totalContactos }}</span>
        </div>
        @if($familia->observaciones)
        <div style="padding:11px 16px;font-size:12px;color:#6b7a8d;border-top:1px solid #f5f7fa;
                    background:#fafbfc;border-radius:0 0 10px 10px;">
            <i class="fa fa-sticky-note-o" style="margin-right:5px;"></i>{{ $familia->observaciones }}
        </div>
        @endif
    </div>

    {{-- Estado de cuenta --}}
    @if($activos > 0)
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-dollar" style="margin-right:6px;color:#3c8dbc;"></i>Estado de cuenta
            </span>
            @if($deudaTotal > 0)
            <span style="background:#fdecea;color:#b91c1c;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">
                ${{ number_format($deudaTotal,2) }}
            </span>
            @else
            <span style="background:#e8f8f0;color:#00875a;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">
                <i class="fa fa-check"></i> Al corriente
            </span>
            @endif
        </div>
        @foreach($familia->alumnos->where('estado','activo') as $alumno)
        @php
            $cargos = $alumno->inscripciones
                ->flatMap(fn($i) => $i->cargos ?? collect())
                ->whereIn('estado', ['pendiente','parcial']);
            $deuda  = $cargos->sum('monto_original');
        @endphp
        <div class="cuenta-alumno">
            <div>
                <div style="font-size:13px;font-weight:600;color:#1a2634;">
                    {{ $alumno->nombre }} {{ $alumno->ap_paterno }}
                </div>
                <code style="font-size:10px;color:#9ab;background:#f0f3f7;padding:1px 5px;border-radius:3px;">
                    {{ $alumno->matricula }}
                </code>
            </div>
            <div style="text-align:right;">
                @if($deuda > 0)
                    <div class="cuenta-deuda">${{ number_format($deuda,2) }}</div>
                    <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                       style="font-size:10px;color:#3c8dbc;">
                        Ver detalle <i class="fa fa-arrow-right"></i>
                    </a>
                @else
                    <div class="cuenta-ok"><i class="fa fa-check-circle"></i> Al corriente</div>
                    <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
                       style="font-size:10px;color:#aab;">
                        Historial <i class="fa fa-arrow-right"></i>
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Accesos rápidos --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title"><i class="fa fa-bolt" style="margin-right:6px;color:#f39c12;"></i>Acciones rápidas</span>
        </div>
        <div style="padding:12px;">
            <a href="{{ route('familias.index') }}"
               class="btn btn-default btn-block btn-sm btn-flat" style="border-radius:6px;margin-bottom:6px;text-align:left;">
                <i class="fa fa-arrow-left" style="margin-right:6px;"></i> Volver a familias
            </a>
            @can('administrador', 'recepcion')
            <a href="{{ route('alumnos.create') }}?familia_id={{ $familia->id }}"
               class="btn btn-success btn-block btn-sm btn-flat" style="border-radius:6px;margin-bottom:6px;text-align:left;">
                <i class="fa fa-user-plus" style="margin-right:6px;"></i> Inscribir alumno
            </a>
            @endcan
            @can('administrador')
            <a href="{{ route('familias.edit', $familia->id) }}"
               class="btn btn-primary btn-block btn-sm btn-flat" style="border-radius:6px;text-align:left;">
                <i class="fa fa-pencil" style="margin-right:6px;"></i> Editar familia
            </a>
            @endcan
        </div>
    </div>

</div>{{-- /col-md-4 --}}

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
