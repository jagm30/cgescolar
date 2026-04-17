@extends('layouts.master')

@section('page_title', $alumno->ap_paterno . ' ' . $alumno->ap_materno . ', ' . $alumno->nombre)
@section('page_subtitle', 'Ficha del alumno · ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li class="active">{{ $alumno->ap_paterno }}</li>
@endsection

@push('styles')
<style>
/* ════════════════════════════════════════════
   HERO
════════════════════════════════════════════ */
.alm-hero {
    background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
    border-radius: 8px;
    padding: 22px 28px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 4px 16px rgba(60,141,188,.25);
}
.alm-hero-foto {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,.55);
    flex-shrink: 0;
}
.alm-hero-placeholder {
    width: 80px; height: 80px; border-radius: 50%;
    background: rgba(255,255,255,.18);
    border: 3px solid rgba(255,255,255,.35);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.alm-hero-nombre    { font-size: 13px; font-weight: 400; color: rgba(255,255,255,.75); line-height: 1; margin-bottom: 4px; }
.alm-hero-apellidos { font-size: 22px; font-weight: 800; color: #fff; line-height: 1.1; }
.alm-hero-matricula {
    display: inline-block;
    background: rgba(0,0,0,.25); color: rgba(255,255,255,.9);
    font-family: monospace; font-size: 12px;
    padding: 2px 10px; border-radius: 12px;
    margin-top: 7px; letter-spacing: .08em;
}
.alm-hero-estado {
    margin-top: 8px;
}
.alm-hero-stats { display: flex; gap: 18px; margin-left: auto; flex-wrap: wrap; align-items: center; }
.alm-hero-stat  { text-align: center; }
.alm-hero-stat-num { font-size: 22px; font-weight: 800; color: #fff; line-height: 1; }
.alm-hero-stat-lbl { font-size: 10px; color: rgba(255,255,255,.65); margin-top: 2px;
                     text-transform: uppercase; letter-spacing: .06em; }
.alm-hero-sep { border-left: 1px solid rgba(255,255,255,.2); padding-left: 18px; }

/* ════════════════════════════════════════════
   SECCIÓN TÍTULOS
════════════════════════════════════════════ */
.sec-title {
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #6b7a8d;
    margin: 0 0 14px;
    display: flex; align-items: center; gap: 8px;
}
.sec-title::after { content: ''; flex: 1; height: 1px; background: #e8ecf0; }

/* ════════════════════════════════════════════
   INSCRIPCIONES
════════════════════════════════════════════ */
.ins-card {
    border: 1px solid #e4eaf0;
    border-radius: 10px;
    margin-bottom: 10px;
    background: #fff;
    display: flex;
    align-items: stretch;
    overflow: hidden;
    transition: box-shadow .15s;
}
.ins-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.07); }
.ins-card-accent { width: 5px; flex-shrink: 0; background: #dde4eb; }
.ins-card-accent.activa { background: #00a65a; }
.ins-card-body { flex: 1; padding: 13px 16px; display: flex; align-items: center; gap: 14px; }
.ins-icon {
    width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: #f0f3f7;
}
.ins-icon.activa { background: #eaf3fb; }
.ins-ciclo  { font-size: 14px; font-weight: 700; color: #1a2634; }
.ins-detalle { font-size: 12px; color: #8a9ab0; margin-top: 4px; display: flex; gap: 8px; flex-wrap: wrap; }
.ins-chip {
    background: #f0f3f7; color: #5a6a7a; border-radius: 6px;
    padding: 1px 8px; font-size: 11px; font-weight: 600;
}
.ins-chip.activa { background: #e8f8f0; color: #00875a; }

/* ════════════════════════════════════════════
   CONTACTOS (mismo estilo familias)
════════════════════════════════════════════ */
.ctc-card {
    border: 1px solid #e4eaf0;
    border-radius: 10px;
    margin-bottom: 12px;
    background: #fff;
    overflow: hidden;
    transition: box-shadow .15s, transform .1s;
}
.ctc-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); transform: translateY(-1px); }
.ctc-card.principal { border-color: #b8d4ec; border-left: 4px solid #3c8dbc; }
.ctc-head {
    padding: 13px 16px;
    display: flex; align-items: center; gap: 13px;
    background: #fff;
}
.ctc-card.principal .ctc-head { background: #f0f7ff; }
.ctc-avatar {
    width: 46px; height: 46px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 17px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, #3c8dbc, #2c6fad);
    box-shadow: 0 2px 6px rgba(60,141,188,.25);
}
.ctc-card:not(.principal) .ctc-avatar { background: linear-gradient(135deg, #9ab, #6b7a8d); box-shadow: none; }
.ctc-nombre { font-size: 15px; font-weight: 700; color: #1a2634; line-height: 1.2; }
.ctc-badges { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; }
.ctc-badge  { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px;
              letter-spacing: .03em; display: inline-flex; align-items: center; gap: 3px; }
.ctc-badge-principal  { background: #3c8dbc; color: #fff; }
.ctc-badge-parentesco { background: #f0f3f7; color: #5a6a7a; border: 1px solid #dde4eb; }
.ctc-badge-recoger    { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
.ctc-badge-portal     { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
.ctc-badge-pagos      { background: #fff8e1; color: #b45309; border: 1px solid #fde68a; }

.ctc-contacto-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
    padding: 10px 14px; border-top: 1px solid #f0f3f7; background: #fafbfc;
}
@media (max-width: 480px) { .ctc-contacto-grid { grid-template-columns: 1fr; } }
.ctc-dato {
    display: flex; align-items: center; gap: 10px;
    border-radius: 8px; padding: 8px 12px;
    border: 1px solid #e8ecf0; background: #fff;
    text-decoration: none; color: inherit;
    transition: background .12s, border-color .12s; min-width: 0;
}
.ctc-dato:hover { background: #eef5fb; border-color: #b8d4ec; color: inherit; text-decoration: none; }
.ctc-dato.no-link:hover { background: #fff; border-color: #e8ecf0; cursor: default; }
.ctc-dato-icon { width: 30px; height: 30px; border-radius: 50%;
                 display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ctc-dato-val  { font-size: 13px; font-weight: 700; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ctc-dato-lbl  { font-size: 10px; color: #9aa; margin-top: 1px; }

/* ════════════════════════════════════════════
   DOCUMENTOS
════════════════════════════════════════════ */
.doc-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 16px; border-bottom: 1px solid #f5f7fa; font-size: 13px;
}
.doc-row:last-child { border-bottom: none; }
.doc-estado-icon { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
                   display: flex; align-items: center; justify-content: center; }

/* ════════════════════════════════════════════
   SIDEBAR — INFO CARD
════════════════════════════════════════════ */
.info-card {
    border: 1px solid #e4eaf0; border-radius: 10px; background: #fff;
    margin-bottom: 18px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.info-card-header {
    padding: 11px 16px; border-bottom: 1px solid #f0f3f7;
    display: flex; align-items: center; justify-content: space-between;
    background: #f8fafc;
}
.info-card-title { font-size: 11px; font-weight: 700; text-transform: uppercase;
                   letter-spacing: .07em; color: #6b7a8d; }
.info-row { display: flex; align-items: baseline; justify-content: space-between;
            padding: 10px 16px; border-bottom: 1px solid #f5f7fa; font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-row-label { color: #8a9ab0; font-size: 12px; flex-shrink: 0; margin-right: 8px; }
.info-row-value { font-weight: 600; color: #1a2634; text-align: right; }

/* Accion btn */
.accion-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; border-bottom: 1px solid #f4f6f8;
    text-decoration: none; color: #333;
    font-size: 13px; font-weight: 500;
    transition: background .12s;
}
.accion-btn:hover { background: #f0f7ff; text-decoration: none; color: #3c8dbc; }
.accion-btn:last-child { border-bottom: none; }
.accion-icon { width: 32px; height: 32px; border-radius: 8px;
               display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Beca chip */
.beca-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 16px; border-bottom: 1px solid #f5f7fa;
}
.beca-row:last-child { border-bottom: none; }
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
    $inscActiva    = $alumno->inscripciones->where('activo', true)->first()
                     ?? $alumno->inscripciones->sortByDesc('id')->first();
    $totalInsc     = $alumno->inscripciones->count();
    $totalContactos = $alumno->contactos->count();
    $totalDocs     = $alumno->documentos->count();
    $entregados    = $alumno->documentos->where('estado','entregado')->count();
    $pendientesDocs = $alumno->documentos->where('estado','pendiente')->count();
    $estado        = $alumno->estado;
    $estadoBadge   = [
        'activo'          => ['bg'=>'#e8f8f0','color'=>'#00875a','borde'=>'#b3e8d0','txt'=>'Activo'],
        'baja_temporal'   => ['bg'=>'#fff8e6','color'=>'#b45309','borde'=>'#fcd97d','txt'=>'Baja temporal'],
        'baja_definitiva' => ['bg'=>'#fdecea','color'=>'#b91c1c','borde'=>'#fca5a5','txt'=>'Baja definitiva'],
        'egresado'        => ['bg'=>'#f3e8fd','color'=>'#6b21a8','borde'=>'#d8b4fe','txt'=>'Egresado'],
    ][$estado] ?? ['bg'=>'#f0f3f7','color'=>'#555','borde'=>'#dde4eb','txt'=>ucfirst($estado)];
@endphp

{{-- ══ HERO ══ --}}
<div class="alm-hero">
    {{-- Foto --}}
    @if($alumno->foto_url)
        <img src="{{ asset('storage/'.$alumno->foto_url) }}" class="alm-hero-foto" alt="Foto">
    @else
        <div class="alm-hero-placeholder">
            <i class="fa fa-user" style="font-size:36px;color:rgba(255,255,255,.55);"></i>
        </div>
    @endif

    {{-- Nombre y matrícula --}}
    <div>
        <div class="alm-hero-nombre">{{ $alumno->nombre }}</div>
        <div class="alm-hero-apellidos">{{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}</div>
        <span class="alm-hero-matricula">{{ $alumno->matricula }}</span>
        <div class="alm-hero-estado">
            <span style="background:{{ $estadoBadge['bg'] }};color:{{ $estadoBadge['color'] }};
                         border:1px solid {{ $estadoBadge['borde'] }};
                         font-size:11px;font-weight:700;padding:3px 12px;border-radius:12px;
                         display:inline-flex;align-items:center;gap:5px;">
                <i class="fa fa-circle" style="font-size:7px;"></i> {{ $estadoBadge['txt'] }}
            </span>
        </div>
    </div>

    {{-- Stats --}}
    <div class="alm-hero-stats">
        @if($inscActiva)
        <div class="alm-hero-stat">
            <div class="alm-hero-stat-num">{{ $inscActiva->grupo->grado->nivel->nombre ?? '—' }}</div>
            <div class="alm-hero-stat-lbl">Nivel</div>
        </div>
        <div class="alm-hero-stat alm-hero-sep">
            <div class="alm-hero-stat-num">{{ ($inscActiva->grupo->grado->nombre ?? '').'° '.($inscActiva->grupo->nombre ?? '') }}</div>
            <div class="alm-hero-stat-lbl">Grupo actual</div>
        </div>
        @endif
        @if($alumno->fecha_nacimiento)
        <div class="alm-hero-stat alm-hero-sep">
            <div class="alm-hero-stat-num">{{ $alumno->fecha_nacimiento->age }}</div>
            <div class="alm-hero-stat-lbl">Años</div>
        </div>
        @endif
        <div class="alm-hero-stat alm-hero-sep">
            <div class="alm-hero-stat-num">{{ $totalInsc }}</div>
            <div class="alm-hero-stat-lbl">Ciclo{{ $totalInsc != 1 ? 's' : '' }}</div>
        </div>
        <div class="alm-hero-stat alm-hero-sep" style="align-self:center;display:flex;gap:6px;">
            @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
            <a href="{{ route('alumnos.edit', $alumno->id) }}"
               class="btn btn-sm btn-flat"
               style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;">
                <i class="fa fa-pencil"></i> Editar
            </a>
            @endif
            @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
            <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}"
               class="btn btn-sm btn-flat"
               style="background:rgba(255,200,0,.25);color:#fff;border:1px solid rgba(255,200,0,.4);border-radius:6px;">
                <i class="fa fa-dollar"></i> Cuenta
            </a>
            @endif
        </div>
    </div>
</div>

<div class="row">

{{-- ════════════════════════════════════════════════════
     COLUMNA PRINCIPAL (col-md-8)
════════════════════════════════════════════════════ --}}
<div class="col-md-8">

    {{-- ── INSCRIPCIONES ── --}}
    <div style="margin-bottom:24px;">
        <p class="sec-title">
            <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
            Inscripciones
            <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">{{ $totalInsc }}</span>
        </p>

        @forelse($alumno->inscripciones->sortByDesc('id') as $inscripcion)
        @php
            $activa = $inscripcion->activo;
            $nivel  = $inscripcion->grupo->grado->nivel->nombre ?? '—';
            $grado  = $inscripcion->grupo->grado->nombre ?? '—';
            $grupo  = $inscripcion->grupo->nombre ?? '—';
            $ciclo  = $inscripcion->ciclo->nombre ?? '—';
        @endphp
        <div class="ins-card">
            <div class="ins-card-accent {{ $activa ? 'activa' : '' }}"></div>
            <div class="ins-card-body">
                <div class="ins-icon {{ $activa ? 'activa' : '' }}">
                    <i class="fa fa-graduation-cap"
                       style="font-size:18px;color:{{ $activa ? '#3c8dbc' : '#b0bec5' }};"></i>
                </div>
                <div style="flex:1;">
                    <div class="ins-ciclo">
                        {{ $ciclo }}
                        @if($activa)
                        <span class="ins-chip activa" style="margin-left:6px;font-size:10px;">ACTIVA</span>
                        @endif
                    </div>
                    <div class="ins-detalle">
                        <span class="ins-chip">{{ $nivel }}</span>
                        <span>{{ $grado }}° Grado</span>
                        <span>·</span>
                        <span style="font-weight:700;color:#333;">Grupo {{ $grupo }}</span>
                        @if($inscripcion->fecha)
                        <span style="color:#b0bec5;">· {{ $inscripcion->fecha->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>
                @if(!$activa)
                <span style="font-size:11px;color:#b0bec5;font-weight:600;">Inactiva</span>
                @endif
            </div>
        </div>
        @empty
        <div style="padding:48px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:10px;">
            <i class="fa fa-graduation-cap" style="font-size:40px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
            <p style="color:#b0bec5;margin:0;">Sin inscripciones registradas.</p>
        </div>
        @endforelse
    </div>

    {{-- ── CONTACTOS FAMILIARES ── --}}
    <div style="margin-bottom:24px;">
        <p class="sec-title">
            <i class="fa fa-phone" style="color:#3c8dbc;"></i>
            Contactos familiares
            <span style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">{{ $totalContactos }}</span>
        </p>

        @forelse($alumno->contactos->sortBy('pivot.orden') as $contacto)
        @php
            $pivot       = $contacto->pivot;
            $esPrincipal = $pivot && $pivot->orden == 1;
            $inicial     = mb_strtoupper(mb_substr($contacto->nombre, 0, 1));
        @endphp

        <div class="ctc-card {{ $esPrincipal ? 'principal' : '' }}">
            <div class="ctc-head">
                @if($contacto->foto_url)
                    <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                         style="width:46px;height:46px;border-radius:50%;object-fit:cover;
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
                        @if($pivot && $pivot->parentesco)
                            <span class="ctc-badge ctc-badge-parentesco">{{ ucfirst($pivot->parentesco) }}</span>
                        @endif
                        @if($pivot && $pivot->autorizado_recoger)
                            <span class="ctc-badge ctc-badge-recoger">
                                <i class="fa fa-check"></i> Recoger
                            </span>
                        @endif
                        @if($pivot && $pivot->es_responsable_pago)
                            <span class="ctc-badge ctc-badge-pagos">
                                <i class="fa fa-dollar"></i> Pagos
                            </span>
                        @endif
                        @if($contacto->tiene_acceso_portal)
                            <span class="ctc-badge ctc-badge-portal">
                                <i class="fa fa-globe"></i> Portal
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($contacto->telefono_celular || $contacto->telefono_trabajo || $contacto->email || $contacto->curp)
            <div class="ctc-contacto-grid">
                @if($contacto->telefono_celular)
                <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_celular) }}" class="ctc-dato">
                    <div class="ctc-dato-icon" style="background:#eaf3fb;">
                        <i class="fa fa-mobile" style="color:#3c8dbc;font-size:17px;"></i>
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
                        <i class="fa fa-phone" style="color:#607d8b;font-size:14px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val">{{ $contacto->telefono_trabajo }}</div>
                        <div class="ctc-dato-lbl">Trabajo</div>
                    </div>
                    <i class="fa fa-angle-right" style="margin-left:auto;color:#b0bec5;"></i>
                </a>
                @endif

                @if($contacto->email)
                <a href="mailto:{{ $contacto->email }}" class="ctc-dato">
                    <div class="ctc-dato-icon" style="background:#f3e8fd;">
                        <i class="fa fa-envelope-o" style="color:#8e44ad;font-size:13px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val" style="font-size:12px;">{{ $contacto->email }}</div>
                        <div class="ctc-dato-lbl">Correo</div>
                    </div>
                </a>
                @endif

                @if($contacto->curp)
                <div class="ctc-dato no-link">
                    <div class="ctc-dato-icon" style="background:#e8f5e9;">
                        <i class="fa fa-id-card-o" style="color:#2e7d32;font-size:12px;"></i>
                    </div>
                    <div style="min-width:0;">
                        <div class="ctc-dato-val" style="font-size:11px;font-family:monospace;">{{ $contacto->curp }}</div>
                        <div class="ctc-dato-lbl">CURP</div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div style="padding:48px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:10px;">
            <i class="fa fa-phone" style="font-size:40px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
            <p style="color:#b0bec5;margin:0;">Sin contactos registrados.</p>
        </div>
        @endforelse
    </div>

    {{-- ── DOCUMENTOS ── --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <p class="sec-title" style="flex:1;margin:0;">
                <i class="fa fa-file-text-o" style="color:#3c8dbc;"></i>
                Documentos
                @if($totalDocs > 0)
                <span style="background:{{ $pendientesDocs > 0 ? '#fdecea' : '#e8f8f0' }};
                             color:{{ $pendientesDocs > 0 ? '#b91c1c' : '#00875a' }};
                             font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px;">
                    {{ $entregados }}/{{ $totalDocs }}
                </span>
                @endif
            </p>
        </div>

        @if($alumno->documentos->count())
        <div class="info-card" style="margin-bottom:0;">
            @foreach($alumno->documentos as $doc)
            <div class="doc-row">
                @switch($doc->estado)
                    @case('entregado')
                        <div class="doc-estado-icon" style="background:#e8f8f0;">
                            <i class="fa fa-check" style="color:#00a65a;font-size:12px;"></i>
                        </div>@break
                    @case('no_aplica')
                        <div class="doc-estado-icon" style="background:#f0f3f7;">
                            <i class="fa fa-minus" style="color:#b0bec5;font-size:12px;"></i>
                        </div>@break
                    @default
                        <div class="doc-estado-icon" style="background:#fdecea;">
                            <i class="fa fa-clock-o" style="color:#dd4b39;font-size:12px;"></i>
                        </div>
                @endswitch

                <span style="flex:1;color:#333;">{{ $doc->tipo_documento }}</span>

                <span style="font-size:11px;margin-right:10px;
                             font-weight:600;
                             color:{{ $doc->estado === 'entregado' ? '#00a65a' : ($doc->estado === 'no_aplica' ? '#b0bec5' : '#dd4b39') }};">
                    @switch($doc->estado)
                        @case('entregado') Entregado @break
                        @case('no_aplica') No aplica @break
                        @default Pendiente
                    @endswitch
                </span>

                @if($doc->archivo_url)
                <a href="{{ asset('storage/'.$doc->archivo_url) }}" target="_blank"
                   class="btn btn-default btn-xs btn-flat" style="border-radius:5px;" title="Descargar">
                    <i class="fa fa-download"></i>
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:48px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:10px;">
            <i class="fa fa-folder-open-o" style="font-size:40px;color:#dde4ea;display:block;margin-bottom:12px;"></i>
            <p style="color:#b0bec5;margin:0;">Sin documentos registrados.</p>
        </div>
        @endif
    </div>

</div>{{-- /col-md-8 --}}

{{-- ════════════════════════════════════════════════════
     COLUMNA LATERAL (col-md-4)
════════════════════════════════════════════════════ --}}
<div class="col-md-4">

    {{-- Datos personales --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-id-card-o" style="margin-right:5px;color:#3c8dbc;"></i>Datos personales
            </span>
        </div>

        <div class="info-row">
            <span class="info-row-label">Nacimiento</span>
            <span class="info-row-value">
                {{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                @if($alumno->fecha_nacimiento)
                    <small style="color:#8a9ab0;font-weight:400;"> · {{ $alumno->fecha_nacimiento->age }} años</small>
                @endif
            </span>
        </div>

        <div class="info-row">
            <span class="info-row-label">Género</span>
            <span class="info-row-value">
                @switch($alumno->genero)
                    @case('M') <i class="fa fa-mars" style="color:#3c8dbc;"></i> Masculino @break
                    @case('F') <i class="fa fa-venus" style="color:#e91e8c;"></i> Femenino  @break
                    @case('Otro') Otro @break
                    @default <span style="color:#ccc;">—</span>
                @endswitch
            </span>
        </div>

        @if($alumno->curp)
        <div class="info-row">
            <span class="info-row-label">CURP</span>
            <span class="info-row-value">
                <code style="font-size:11px;background:#f0f3f7;padding:2px 6px;border-radius:4px;color:#4a5568;">
                    {{ $alumno->curp }}
                </code>
            </span>
        </div>
        @endif

        <div class="info-row">
            <span class="info-row-label">Inscripción</span>
            <span class="info-row-value">{{ $alumno->fecha_inscripcion?->format('d/m/Y') ?? '—' }}</span>
        </div>

        @if($alumno->fecha_baja)
        <div class="info-row">
            <span class="info-row-label">Baja</span>
            <span class="info-row-value" style="color:#dd4b39;">
                {{ $alumno->fecha_baja->format('d/m/Y') }}
            </span>
        </div>
        @endif

        @if($alumno->observaciones)
        <div style="padding:10px 16px;font-size:12px;color:#6b7a8d;border-top:1px solid #f5f7fa;
                    background:#fafbfc;line-height:1.5;">
            <i class="fa fa-sticky-note-o" style="margin-right:5px;"></i>{{ $alumno->observaciones }}
        </div>
        @endif
    </div>

    {{-- Familia --}}
    @if($alumno->familia)
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-home" style="margin-right:5px;color:#4caf50;"></i>Familia
            </span>
            <a href="{{ route('familias.show', $alumno->familia->id) }}"
               class="btn btn-xs btn-flat btn-default" style="border-radius:4px;">
                <i class="fa fa-eye"></i> Ver
            </a>
        </div>
        <div style="padding:14px 16px;display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:50%;background:#e8f5e9;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa fa-home" style="color:#4caf50;font-size:16px;"></i>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#1a2634;">
                    Familia {{ $alumno->familia->apellido_familia }}
                </div>
                <div style="font-size:11px;color:#8a9ab0;margin-top:3px;">
                    {{ $alumno->familia->alumnos->count() }} alumno(s) ·
                    {{ $alumno->familia->contactos->count() }} contacto(s)
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Becas activas --}}
    @if($alumno->becas->where('activo', true)->count() > 0)
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-star" style="margin-right:5px;color:#f39c12;"></i>Becas activas
            </span>
            <span style="background:#fff8e1;color:#b45309;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">
                {{ $alumno->becas->where('activo', true)->count() }}
            </span>
        </div>
        @foreach($alumno->becas->where('activo', true) as $beca)
        <div class="beca-row">
            <div style="width:34px;height:34px;border-radius:8px;background:#fff8e1;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa fa-percent" style="color:#f39c12;font-size:13px;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;color:#1a2634;">
                    {{ $beca->catalogoBeca->nombre }}
                </div>
                <div style="font-size:11px;color:#8a9ab0;margin-top:2px;">
                    {{ $beca->concepto->nombre ?? '—' }}
                </div>
            </div>
            <span style="background:#fff3cd;color:#856404;font-size:12px;font-weight:700;
                         padding:2px 10px;border-radius:10px;white-space:nowrap;">
                @if($beca->catalogoBeca->tipo === 'porcentaje')
                    {{ $beca->catalogoBeca->valor }}%
                @else
                    ${{ number_format($beca->catalogoBeca->valor, 2) }}
                @endif
            </span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Acciones rápidas --}}
    <div class="info-card">
        <div class="info-card-header">
            <span class="info-card-title">
                <i class="fa fa-bolt" style="margin-right:5px;color:#f39c12;"></i>Acciones rápidas
            </span>
        </div>
        <div>
            <a href="{{ route('alumnos.index') }}" class="accion-btn">
                <div class="accion-icon" style="background:#f0f3f7;">
                    <i class="fa fa-arrow-left" style="color:#6b7a8d;font-size:13px;"></i>
                </div>
                Volver a alumnos
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>

            @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
            <a href="{{ route('alumnos.edit', $alumno->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f0fb;">
                    <i class="fa fa-pencil" style="color:#3c8dbc;font-size:13px;"></i>
                </div>
                Editar datos
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endif

            @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
            <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#fff8e1;">
                    <i class="fa fa-dollar" style="color:#f39c12;font-size:14px;"></i>
                </div>
                Estado de cuenta
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endif

            @if($alumno->familia)
            <a href="{{ route('familias.show', $alumno->familia->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f5e9;">
                    <i class="fa fa-home" style="color:#4caf50;font-size:14px;"></i>
                </div>
                Ver familia
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
            </a>
            @endif
        </div>
    </div>

</div>{{-- /col-md-4 --}}

</div>{{-- /row --}}
@endsection
