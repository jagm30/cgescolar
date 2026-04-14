@extends('layouts.master')

@section('page_title', $alumno->nombre . ' ' . $alumno->ap_paterno)
@section('page_subtitle', 'Ficha del alumno · ' . $alumno->matricula)

@section('breadcrumb')
    <li><a href="{{ route('alumnos.index') }}">Alumnos</a></li>
    <li class="active">{{ $alumno->ap_paterno }}</li>
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
.perfil-foto {
    width: 100px; height: 100px; border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,.6);
    margin-bottom: 12px;
}
.perfil-foto-placeholder {
    width: 100px; height: 100px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    border: 4px solid rgba(255,255,255,.4);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.perfil-nombre {
    color: #fff;
    font-size: 20px;
    font-weight: 300;
    line-height: 1.3;
    margin: 0 0 2px;
}
.perfil-apellidos {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    display: block;
}
.perfil-matricula {
    display: inline-block;
    background: rgba(0,0,0,.25);
    color: rgba(255,255,255,.9);
    font-family: monospace;
    font-size: 13px;
    padding: 2px 10px;
    border-radius: 12px;
    margin-top: 6px;
    letter-spacing: .08em;
}
.perfil-estado {
    margin-top: 10px;
}

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
    width: 130px;
    flex-shrink: 0;
    padding-top: 1px;
}
.dato-valor {
    color: #222;
    font-size: 14px;
    font-weight: 500;
    flex: 1;
}

/* ── Contactos ────────────────────────────── */
.ctc-card {
    border-left: 4px solid #ccc;
    background: #fff;
    padding: 14px 16px;
    border-bottom: 1px solid #f4f4f4;
    transition: background .1s;
}
.ctc-card:hover { background: #fafcff; }
.ctc-card.principal { border-left-color: #3c8dbc; background: #f7fbff; }
.ctc-nombre { font-size: 16px; font-weight: 700; color: #1a1a1a; }
.ctc-tel-btn {
    display: flex; align-items: center; gap: 10px;
    background: #fff; border: 1px solid #d0e8ff;
    border-radius: 6px; padding: 8px 12px;
    text-decoration: none; color: #1a1a1a;
    margin-top: 8px; transition: background .12s;
}
.ctc-tel-btn:hover { background: #e8f3ff; color: #1a1a1a; text-decoration: none; }
.ctc-tel-btn.trabajo { border-color: #e0e0e0; }
.ctc-tel-btn.trabajo:hover { background: #f5f5f5; }
.ctc-tel-icon {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.ctc-tel-num { font-size: 19px; font-weight: 700; letter-spacing: .04em; line-height: 1; }
.ctc-tel-sub { font-size: 10px; color: #999; margin-top: 1px; }

/* ── Inscripciones ────────────────────────── */
.ins-row {
    padding: 14px 18px;
    border-bottom: 1px solid #f4f4f4;
    display: flex; align-items: center; gap: 16px;
}
.ins-row:last-child { border-bottom: none; }
.ins-ciclo { font-size: 13px; font-weight: 700; color: #333; }
.ins-detalle { font-size: 12px; color: #888; margin-top: 3px; }

/* ── Documentos ───────────────────────────── */
.doc-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 16px; border-bottom: 1px solid #f8f8f8;
    font-size: 13px; color: #333;
}
.doc-item:last-child { border-bottom: none; }

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
     COLUMNA IZQUIERDA
════════════════════════════════════════════════════ --}}
<div class="col-md-4">

    {{-- ── PERFIL ── --}}
    <div class="box box-primary" style="overflow:hidden;">

        <div class="perfil-header">
            {{-- Foto --}}
            @if($alumno->foto_url)
                <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                     class="perfil-foto" alt="Foto del alumno">
            @else
                <div class="perfil-foto-placeholder">
                    <i class="fa fa-user" style="font-size:44px;color:rgba(255,255,255,.6);"></i>
                </div>
            @endif

            {{-- Nombre --}}
            <div class="perfil-nombre">
                {{ $alumno->nombre }}
                <span class="perfil-apellidos">
                    {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                </span>
            </div>

            {{-- Matrícula --}}
            <span class="perfil-matricula">{{ $alumno->matricula }}</span>

            {{-- Estado --}}
            <div class="perfil-estado">
                @switch($alumno->estado)
                    @case('activo')
                        <span class="label label-success" style="font-size:13px;padding:4px 14px;">
                            <i class="fa fa-circle"></i> Activo
                        </span>@break
                    @case('baja_temporal')
                        <span class="label label-warning" style="font-size:13px;padding:4px 14px;">
                            <i class="fa fa-pause-circle"></i> Baja temporal
                        </span>@break
                    @case('baja_definitiva')
                        <span class="label label-danger" style="font-size:13px;padding:4px 14px;">
                            <i class="fa fa-times-circle"></i> Baja definitiva
                        </span>@break
                    @case('egresado')
                        <span class="label label-info" style="font-size:13px;padding:4px 14px;">
                            <i class="fa fa-graduation-cap"></i> Egresado
                        </span>@break
                @endswitch
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div style="padding:0;">
            @if(auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
            <a href="{{ route('alumnos.edit', $alumno->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f0fb;">
                    <i class="fa fa-pencil" style="color:#3c8dbc;font-size:15px;"></i>
                </div>
                Editar datos
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#ccc;font-size:11px;"></i>
            </a>
            @endif
            @if(auth()->user()->esAdministrador() || auth()->user()->esCajero())
            <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#fff8e1;">
                    <i class="fa fa-dollar" style="color:#f39c12;font-size:16px;"></i>
                </div>
                Estado de cuenta
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#ccc;font-size:11px;"></i>
            </a>
            @endif
            @if($alumno->familia)
            <a href="{{ route('familias.show', $alumno->familia->id) }}" class="accion-btn">
                <div class="accion-icon" style="background:#e8f5e9;">
                    <i class="fa fa-home" style="color:#4caf50;font-size:15px;"></i>
                </div>
                Ver familia
                <i class="fa fa-chevron-right" style="margin-left:auto;color:#ccc;font-size:11px;"></i>
            </a>
            @endif
        </div>
    </div>

    {{-- ── DATOS PERSONALES ── --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-id-card-o" style="color:#3c8dbc;"></i>
                Datos personales
            </h3>
        </div>
        <div class="box-body no-padding">

            <div class="dato-row">
                <span class="dato-label">Nacimiento</span>
                <span class="dato-valor">
                    {{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                    @if($alumno->fecha_nacimiento)
                        <small style="color:#aaa;font-weight:400;font-size:12px;">
                            ({{ $alumno->fecha_nacimiento->age }} años)
                        </small>
                    @endif
                </span>
            </div>

            <div class="dato-row">
                <span class="dato-label">Género</span>
                <span class="dato-valor">
                    @switch($alumno->genero)
                        @case('M') <i class="fa fa-mars" style="color:#3c8dbc;"></i> Masculino @break
                        @case('F') <i class="fa fa-venus" style="color:#e91e8c;"></i> Femenino  @break
                        @case('Otro') Otro @break
                        @default <span style="color:#ccc;">—</span>
                    @endswitch
                </span>
            </div>

            <div class="dato-row">
                <span class="dato-label">CURP</span>
                <span class="dato-valor">
                    @if($alumno->curp)
                        <code style="font-size:12px;background:#f5f5f5;padding:1px 5px;border-radius:3px;">
                            {{ $alumno->curp }}
                        </code>
                    @else
                        <span style="color:#ccc;">—</span>
                    @endif
                </span>
            </div>

            <div class="dato-row">
                <span class="dato-label">Inscripción</span>
                <span class="dato-valor">
                    {{ $alumno->fecha_inscripcion?->format('d/m/Y') ?? '—' }}
                </span>
            </div>

            @if($alumno->fecha_baja)
            <div class="dato-row">
                <span class="dato-label">Baja</span>
                <span class="dato-valor" style="color:#dd4b39;">
                    {{ $alumno->fecha_baja->format('d/m/Y') }}
                </span>
            </div>
            @endif

            @if($alumno->observaciones)
            <div class="dato-row" style="align-items:flex-start;">
                <span class="dato-label" style="padding-top:2px;">Notas</span>
                <span class="dato-valor" style="font-size:13px;font-weight:400;color:#555;line-height:1.5;">
                    {{ $alumno->observaciones }}
                </span>
            </div>
            @endif

        </div>
    </div>

    {{-- ── BECAS ── --}}
    @if($alumno->becas->where('activo', true)->count() > 0)
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-star" style="color:#f39c12;"></i>
                Becas activas
            </h3>
        </div>
        <div class="box-body no-padding">
            @foreach($alumno->becas->where('activo', true) as $beca)
            <div style="padding:10px 18px;border-bottom:1px solid #f8f8f8;">
                <div style="font-size:14px;font-weight:600;color:#333;">
                    {{ $beca->catalogoBeca->nombre }}
                </div>
                <div style="font-size:12px;color:#888;margin-top:3px;">
                    <span style="background:#fff8e1;color:#e65100;padding:1px 8px;border-radius:10px;font-weight:600;font-size:11px;">
                        @if($beca->catalogoBeca->tipo === 'porcentaje')
                            {{ $beca->catalogoBeca->valor }}%
                        @else
                            ${{ number_format($beca->catalogoBeca->valor, 2) }}
                        @endif
                    </span>
                    &nbsp;{{ $beca->concepto->nombre ?? '—' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /col-md-4 --}}

{{-- ════════════════════════════════════════════════════
     COLUMNA DERECHA (col-md-8)
════════════════════════════════════════════════════ --}}
<div class="col-md-8">

    {{-- ── INSCRIPCIONES ── --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-graduation-cap"></i> Inscripciones
                <span class="badge bg-blue" style="margin-left:6px;">
                    {{ $alumno->inscripciones->count() }}
                </span>
            </h3>
        </div>
        <div class="box-body no-padding">
            @forelse($alumno->inscripciones->sortByDesc('id') as $inscripcion)
            @php
                $nivel = $inscripcion->grupo->grado->nivel->nombre ?? '—';
                $grado = $inscripcion->grupo->grado->nombre ?? '—';
                $grupo = $inscripcion->grupo->nombre ?? '—';
                $ciclo = $inscripcion->ciclo->nombre ?? '—';
            @endphp
            <div class="ins-row">
                {{-- Ícono de nivel --}}
                <div style="
                    width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
                    background: {{ $inscripcion->activo ? '#e8f0fb' : '#f5f5f5' }};
                    display: flex; align-items: center; justify-content: center;
                ">
                    <i class="fa fa-graduation-cap" style="
                        font-size: 20px;
                        color: {{ $inscripcion->activo ? '#3c8dbc' : '#bbb' }};
                    "></i>
                </div>

                {{-- Datos --}}
                <div style="flex:1;">
                    <div class="ins-ciclo">
                        {{ $ciclo }}
                        @if($inscripcion->activo)
                            <span style="background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:700;
                                         padding:1px 8px;border-radius:10px;margin-left:4px;letter-spacing:.03em;">
                                ACTIVA
                            </span>
                        @endif
                    </div>
                    <div class="ins-detalle">
                        <span style="
                            background:#f0f0f0;color:#555;font-size:11px;
                            padding:1px 7px;border-radius:8px;margin-right:4px;
                        ">{{ $nivel }}</span>
                        {{ $grado }}° Grado &nbsp;·&nbsp; Grupo
                        <strong style="color:#333;">{{ $grupo }}</strong>
                        @if($inscripcion->fecha)
                            &nbsp;·&nbsp; {{ $inscripcion->fecha->format('d/m/Y') }}
                        @endif
                    </div>
                </div>

                @if(!$inscripcion->activo)
                    <span class="label label-default">Inactiva</span>
                @endif
            </div>
            @empty
            <div style="padding:40px;text-align:center;color:#ccc;">
                <i class="fa fa-graduation-cap" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                Sin inscripciones registradas.
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── CONTACTOS FAMILIARES ── --}}
    <div class="box box-default">
        <div class="box-header with-border" style="background:linear-gradient(135deg,#2c6fad,#3c8dbc);border-radius:3px 3px 0 0;">
            <h3 class="box-title" style="color:#fff;font-size:15px;">
                <i class="fa fa-phone"></i>
                Contactos familiares
                <span style="background:rgba(255,255,255,.25);color:#fff;border-radius:10px;
                              padding:1px 8px;font-size:12px;margin-left:6px;">
                    {{ $alumno->contactos->count() }}
                </span>
            </h3>
        </div>
        <div class="box-body" style="padding:12px 14px 6px;">

            @forelse($alumno->contactos->sortBy('pivot.orden') as $contacto)
            @php
                $pivot       = $contacto->pivot;
                $esPrincipal = $pivot && $pivot->orden == 1;
            @endphp

            <div class="ctc-card {{ $esPrincipal ? 'principal' : '' }}"
                 style="border-radius:6px;margin-bottom:10px;">

                <div style="display:flex;align-items:flex-start;gap:12px;">

                    {{-- Avatar --}}
                    <div style="
                        width:46px;height:46px;border-radius:50%;flex-shrink:0;
                        background:{{ $esPrincipal ? '#3c8dbc' : '#9e9e9e' }};
                        display:flex;align-items:center;justify-content:center;overflow:hidden;
                    ">
                        @if($contacto->foto_url)
                            <img src="{{ asset('storage/'.$contacto->foto_url) }}"
                                 style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <i class="fa fa-user" style="color:#fff;font-size:19px;"></i>
                        @endif
                    </div>

                    {{-- Nombre y badges --}}
                    <div style="flex:1;min-width:0;">
                        <div class="ctc-nombre">
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
                            @if($pivot && $pivot->parentesco)
                                <span style="background:#f0f0f0;color:#555;font-size:11px;font-weight:600;
                                             padding:2px 9px;border-radius:10px;">
                                    {{ ucfirst($pivot->parentesco) }}
                                </span>
                            @endif
                            @if($pivot && $pivot->autorizado_recoger)
                                <span style="background:#e8f5e9;color:#2e7d32;font-size:10px;font-weight:600;
                                             padding:2px 9px;border-radius:10px;">
                                    <i class="fa fa-check"></i> Autorizado recoger
                                </span>
                            @endif
                            @if($pivot && $pivot->es_responsable_pago)
                                <span style="background:#fff8e1;color:#e65100;font-size:10px;font-weight:600;
                                             padding:2px 9px;border-radius:10px;">
                                    <i class="fa fa-dollar"></i> Responsable pagos
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
                </div>

                {{-- Teléfonos grandes --}}
                <div style="margin-top:10px;">
                    @if($contacto->telefono_celular)
                    <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_celular) }}"
                       class="ctc-tel-btn">
                        <div class="ctc-tel-icon" style="background:#3c8dbc;">
                            <i class="fa fa-mobile" style="color:#fff;font-size:18px;"></i>
                        </div>
                        <div>
                            <div class="ctc-tel-num">{{ $contacto->telefono_celular }}</div>
                            <div class="ctc-tel-sub">Celular</div>
                        </div>
                        <i class="fa fa-phone" style="margin-left:auto;color:#3c8dbc;font-size:13px;"></i>
                    </a>
                    @endif

                    @if($contacto->telefono_trabajo)
                    <a href="tel:{{ preg_replace('/\D/','',$contacto->telefono_trabajo) }}"
                       class="ctc-tel-btn trabajo">
                        <div class="ctc-tel-icon" style="background:#607d8b;">
                            <i class="fa fa-phone" style="color:#fff;font-size:14px;"></i>
                        </div>
                        <div>
                            <div class="ctc-tel-num">{{ $contacto->telefono_trabajo }}</div>
                            <div class="ctc-tel-sub">Trabajo</div>
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

                    @if(!$contacto->telefono_celular && !$contacto->telefono_trabajo && !$contacto->email)
                    <div style="font-size:12px;color:#ccc;padding:6px 4px;">
                        <i class="fa fa-info-circle"></i> Sin datos de contacto registrados
                    </div>
                    @endif
                </div>

            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#ccc;">
                <i class="fa fa-phone" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                Sin contactos registrados.
            </div>
            @endforelse

        </div>
    </div>

    {{-- ── DOCUMENTOS ── --}}
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-file-text-o"></i>
                Documentos
            </h3>
            <div class="box-tools pull-right">
                @php
                    $totalDocs     = $alumno->documentos->count();
                    $entregados    = $alumno->documentos->where('estado','entregado')->count();
                    $pendientesDocs = $alumno->documentos->where('estado','pendiente')->count();
                @endphp
                @if($totalDocs > 0)
                <span style="
                    background: {{ $pendientesDocs > 0 ? '#e74c3c' : '#27ae60' }};
                    color: #fff; border-radius: 10px; padding: 2px 10px;
                    font-size: 12px; font-weight: 700;
                ">{{ $entregados }}/{{ $totalDocs }}</span>
                @endif
            </div>
        </div>
        <div class="box-body no-padding">
            @forelse($alumno->documentos as $doc)
            <div class="doc-item">
                @switch($doc->estado)
                    @case('entregado')
                        <i class="fa fa-check-circle" style="color:#27ae60;font-size:16px;flex-shrink:0;"></i>@break
                    @case('no_aplica')
                        <i class="fa fa-minus-circle" style="color:#bbb;font-size:16px;flex-shrink:0;"></i>@break
                    @default
                        <i class="fa fa-clock-o" style="color:#e74c3c;font-size:16px;flex-shrink:0;"></i>
                @endswitch
                <span style="flex:1;">{{ $doc->tipo_documento }}</span>
                <span style="font-size:11px;color:#aaa;margin-right:8px;">
                    @switch($doc->estado)
                        @case('entregado') Entregado @break
                        @case('no_aplica') No aplica @break
                        @default <span style="color:#e74c3c;font-weight:600;">Pendiente</span>
                    @endswitch
                </span>
                @if($doc->archivo_url)
                    <a href="{{ asset('storage/'.$doc->archivo_url) }}" target="_blank"
                       class="btn btn-default btn-xs btn-flat">
                        <i class="fa fa-download"></i>
                    </a>
                @endif
            </div>
            @empty
            <div style="padding:30px;text-align:center;color:#ccc;">
                <i class="fa fa-folder-open-o" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                Sin documentos registrados.
            </div>
            @endforelse
        </div>
    </div>

</div>{{-- /col-md-8 --}}

</div>{{-- /row --}}
@endsection
