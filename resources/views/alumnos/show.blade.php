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
            padding: 14px 20px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            box-shadow: 0 4px 16px rgba(60, 141, 188, .25);
        }

        .alm-hero-foto {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, .55);
            flex-shrink: 0;
        }

        .alm-hero-placeholder {
            width: 62px;
            height: 62px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .18);
            border: 3px solid rgba(255, 255, 255, .35);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .alm-hero-nombre {
            font-size: 12px;
            font-weight: 400;
            color: rgba(255, 255, 255, .75);
            line-height: 1;
            margin-bottom: 2px;
        }

        .alm-hero-apellidos {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }

        .alm-hero-matricula {
            display: inline-block;
            background: rgba(0, 0, 0, .25);
            color: rgba(255, 255, 255, .9);
            font-family: monospace;
            font-size: 11px;
            padding: 1px 8px;
            border-radius: 12px;
            margin-top: 4px;
            letter-spacing: .08em;
        }

        .alm-hero-estado {
            margin-top: 5px;
        }

        .alm-hero-stats {
            display: flex;
            gap: 12px;
            margin-left: auto;
            flex-wrap: wrap;
            align-items: center;
        }

        .alm-hero-stat {
            text-align: center;
        }

        .alm-hero-stat-num {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }

        .alm-hero-stat-lbl {
            font-size: 9px;
            color: rgba(255, 255, 255, .65);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .alm-hero-sep {
            border-left: 1px solid rgba(255, 255, 255, .2);
            padding-left: 18px;
        }

        /* ════════════════════════════════════════════
                                        SECCIÓN TÍTULOS
                                       ════════════════════════════════════════════ */
        .sec-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #6b7a8d;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sec-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e8ecf0;
        }

        /* ════════════════════════════════════════════
                                                INSCRIPCIONES
                                       ════════════════════════════════════════════ */
        .ins-card {
            border: 1px solid #e4eaf0;
            border-radius: 8px;
            margin-bottom: 5px;
            background: #fff;
            display: flex;
            align-items: stretch;
            overflow: hidden;
            transition: box-shadow .15s;
        }

        .ins-card:hover {
            box-shadow: 0 3px 12px rgba(0, 0, 0, .07);
        }

        .ins-card-accent {
            width: 5px;
            flex-shrink: 0;
            background: #dde4eb;
        }

        .ins-card-accent.activa {
            background: #00a65a;
        }

        .ins-card-body {
            flex: 1;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ins-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f3f7;
        }

        .ins-icon.activa {
            background: #eaf3fb;
        }

        .ins-ciclo {
            font-size: 13px;
            font-weight: 700;
            color: #1a2634;
        }

        .ins-detalle {
            font-size: 11px;
            color: #8a9ab0;
            margin-top: 2px;
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .ins-chip {
            background: #f0f3f7;
            color: #5a6a7a;
            border-radius: 6px;
            padding: 1px 8px;
            font-size: 11px;
            font-weight: 600;
        }

        .ins-chip.activa {
            background: #e8f8f0;
            color: #00875a;
        }

        /* ════════════════════════════════════════════
                                            CONTACTOS (mismo estilo familias)
                                       ════════════════════════════════════════════ */
        .ctc-card {
            border: 1px solid #e4eaf0;
            border-radius: 8px;
            margin-bottom: 7px;
            background: #fff;
            overflow: hidden;
            transition: box-shadow .15s, transform .1s;
        }

        .ctc-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
            transform: translateY(-1px);
        }

        .ctc-card.principal {
            border-color: #b8d4ec;
            border-left: 4px solid #3c8dbc;
        }

        .ctc-head {
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
        }

        .ctc-card.principal .ctc-head {
            background: #f0f7ff;
        }

        .ctc-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #3c8dbc, #2c6fad);
            box-shadow: 0 2px 6px rgba(60, 141, 188, .25);
        }

        .ctc-card:not(.principal) .ctc-avatar {
            background: linear-gradient(135deg, #9ab, #6b7a8d);
            box-shadow: none;
        }

        .ctc-nombre {
            font-size: 13px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }

        .ctc-badges {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            margin-top: 3px;
        }

        .ctc-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            letter-spacing: .03em;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .ctc-badge-principal {
            background: #3c8dbc;
            color: #fff;
        }

        .ctc-badge-parentesco {
            background: #f0f3f7;
            color: #5a6a7a;
            border: 1px solid #dde4eb;
        }

        .ctc-badge-recoger {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .ctc-badge-portal {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }

        .ctc-badge-pagos {
            background: #fff8e1;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .ctc-contacto-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            padding: 6px 10px;
            border-top: 1px solid #f0f3f7;
            background: #fafbfc;
        }

        @media (max-width: 480px) {
            .ctc-contacto-grid {
                grid-template-columns: 1fr;
            }
        }

        .ctc-dato {
            display: flex;
            align-items: center;
            gap: 7px;
            border-radius: 6px;
            padding: 5px 8px;
            border: 1px solid #e8ecf0;
            background: #fff;
            text-decoration: none;
            color: inherit;
            transition: background .12s, border-color .12s;
            min-width: 0;
        }

        .ctc-dato:hover {
            background: #eef5fb;
            border-color: #b8d4ec;
            color: inherit;
            text-decoration: none;
        }

        .ctc-dato.no-link:hover {
            background: #fff;
            border-color: #e8ecf0;
            cursor: default;
        }

        .ctc-dato-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ctc-dato-val {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ctc-dato-lbl {
            font-size: 9px;
            color: #9aa;
            margin-top: 1px;
        }

        /* ════════════════════════════════════════════
                                                DOCUMENTOS
                                     ════════════════════════════════════════════ */
        .doc-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid #f5f7fa;
            font-size: 13px;
        }

        .doc-row:last-child {
            border-bottom: none;
        }

        .doc-estado-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ════════════════════════════════════════════
                                            SIDEBAR — INFO CARD
                                     ════════════════════════════════════════════ */
        .info-card {
            border: 1px solid #e4eaf0;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .info-card-header {
            padding: 8px 14px;
            border-bottom: 1px solid #f0f3f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .info-card-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #6b7a8d;
        }

        .info-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            padding: 7px 14px;
            border-bottom: 1px solid #f5f7fa;
            font-size: 12px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row-label {
            color: #8a9ab0;
            font-size: 12px;
            flex-shrink: 0;
            margin-right: 8px;
        }

        .info-row-value {
            font-weight: 600;
            color: #1a2634;
            text-align: right;
        }

        /* Accion btn */
        .accion-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-bottom: 1px solid #f4f6f8;
            text-decoration: none;
            color: #333;
            font-size: 12px;
            font-weight: 500;
            transition: background .12s;
        }

        .accion-btn:hover {
            background: #f0f7ff;
            text-decoration: none;
            color: #3c8dbc;
        }

        .accion-btn:last-child {
            border-bottom: none;
        }

        .accion-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Beca chip */
        .beca-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 14px;
            border-bottom: 1px solid #f5f7fa;
        }

        .beca-row:last-child {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:8px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:8px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @php
        use App\Enums\TipoInscripcion;

        // Inscripción vigente (regular, activa)
        $inscActiva = $alumno->inscripciones
            ->filter(fn($i) => $i->activo && $i->tipo !== TipoInscripcion::Anticipada)
            ->sortByDesc('id')
            ->first();

        $totalInsc = $alumno->inscripciones->count();
        $totalContactos = $alumno->contactos->count();
        $totalDocs = $alumno->documentos->count();
        $entregados = $alumno->documentos->where('estado', 'entregado')->count();
        $pendientesDocs = $alumno->documentos->where('estado', 'pendiente')->count();
        $estado = $alumno->estado;
        $estadoBadge = [
            'activo' => ['bg' => '#e8f8f0', 'color' => '#00875a', 'borde' => '#b3e8d0', 'txt' => 'Activo'],
            'baja_temporal' => [
                'bg' => '#fff8e6',
                'color' => '#b45309',
                'borde' => '#fcd97d',
                'txt' => 'Baja temporal',
            ],
            'baja_definitiva' => [
                'bg' => '#fdecea',
                'color' => '#b91c1c',
                'borde' => '#fca5a5',
                'txt' => 'Baja definitiva',
            ],
            'egresado' => ['bg' => '#f3e8fd', 'color' => '#6b21a8', 'borde' => '#d8b4fe', 'txt' => 'Egresado'],
        ][$estado] ?? ['bg' => '#f0f3f7', 'color' => '#555', 'borde' => '#dde4eb', 'txt' => ucfirst($estado)];
    @endphp

    {{-- ══ HERO ══ --}}
    <div class="alm-hero">
        {{-- Foto --}}
        @if ($alumno->foto_url)
            <img src="{{ asset('storage/' . $alumno->foto_url) }}" class="alm-hero-foto" alt="Foto">
        @else
            <div class="alm-hero-placeholder">
                <i class="fa fa-user" style="font-size:26px;color:rgba(255,255,255,.55);"></i>
            </div>
        @endif

        {{-- Nombre y matrícula --}}
        <div>
            <div class="alm-hero-nombre">{{ $alumno->nombre }}</div>
            <div class="alm-hero-apellidos">{{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}</div>
            <span class="alm-hero-matricula">{{ $alumno->matricula }}</span>
            <div class="alm-hero-estado" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
                <span
                    style="background:{{ $estadoBadge['bg'] }};color:{{ $estadoBadge['color'] }};
                         border:1px solid {{ $estadoBadge['borde'] }};
                         font-size:11px;font-weight:700;padding:3px 12px;border-radius:12px;
                         display:inline-flex;align-items:center;gap:5px;">
                    <i class="fa fa-circle" style="font-size:7px;"></i> {{ $estadoBadge['txt'] }}
                </span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="alm-hero-stats">
            @if ($inscActiva)
                <div class="alm-hero-stat">
                    <div class="alm-hero-stat-num">{{ $inscActiva->grupo?->grado?->nivel?->nombre ?? '—' }}</div>
                    <div class="alm-hero-stat-lbl">Nivel</div>
                </div>
                <div class="alm-hero-stat alm-hero-sep">
                    <div class="alm-hero-stat-num">
                        @if ($inscActiva->grupo_id)
                            {{ ($inscActiva->grupo?->grado?->numero ?? '') . '° ' . ($inscActiva->grupo?->nombre ?? '') }}
                        @else
                            Sin grupo
                        @endif
                    </div>
                    <div class="alm-hero-stat-lbl">Grupo actual</div>
                </div>
            @endif
            @if ($alumno->fecha_nacimiento)
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
                @if ($inscActiva && $inscActiva->grupo_id)
                    <a href="{{ route('grupos.show', $inscActiva->grupo_id) }}" class="btn btn-sm btn-flat"
                        style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:20px; padding: 5px 12px;">
                        <i class="fa fa-users"></i> Ver grupo
                        {{ $inscActiva->grupo->grado->numero }} {{ $inscActiva->grupo->nombre }}
                    </a>
                @endif
                @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                    <a href="{{ route('alumnos.edit', $alumno->id) }}" class="btn btn-sm btn-flat"
                        style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.4);border-radius:6px;">
                        <i class="fa fa-pencil"></i> Editar
                    </a>
                @endif
                @if (auth()->user()->esAdministrador() || auth()->user()->esCajero())
                    <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}" class="btn btn-sm btn-flat"
                        style="background:rgba(255,200,0,.25);color:#fff;border:1px solid rgba(255,200,0,.4);border-radius:6px;">
                        <i class="fa fa-dollar"></i> Cuenta
                    </a>
                @endif
                <a href="{{ route('alumnos.reporte', $alumno->id) }}" target="_blank" class="btn btn-sm btn-flat"
                   style="background:rgba(231,76,60,.35);color:#fff;border:1px solid rgba(231,76,60,.5);border-radius:6px;">
                    <i class="fa fa-file-pdf-o"></i> Ficha PDF
                </a>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ════════════════════════════════════════════════════
                COLUMNA PRINCIPAL (col-md-8)
            ════════════════════════════════════════════════════ --}}
        <div class="col-md-8">

            {{-- ── INSCRIPCIONES ── --}}
            <div style="margin-bottom:14px;">
                <p class="sec-title">
                    <i class="fa fa-graduation-cap" style="color:#3c8dbc;"></i>
                    Inscripciones
                    <span
                        style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">{{ $totalInsc }}</span>
                </p>

                @forelse($alumno->inscripciones->sortByDesc('id') as $inscripcion)
                    @php
                        $activa      = $inscripcion->activo;
                        $esAntic     = $inscripcion->tipo?->value === 'anticipada';
                        $nivel       = $inscripcion->grupo?->grado?->nivel?->nombre ?? '—';
                        $grado       = $inscripcion->grupo?->grado?->numero ?? '—';
                        $grupo       = $inscripcion->grupo?->nombre ?? null;
                        $ciclo       = $inscripcion->ciclo?->nombre ?? '—';

                        // Colores: ámbar para anticipada, verde para activa regular, gris para historial
                        $accentColor = $esAntic ? '#f39c12' : ($activa ? '#00a65a' : '#dde4eb');
                        $iconColor   = $esAntic ? '#f39c12' : ($activa ? '#3c8dbc' : '#b0bec5');
                        $iconBg      = $esAntic ? '#fff8e1' : ($activa ? '#eaf3fb' : '#f0f3f7');
                    @endphp
                    <div class="ins-card">
                        <div class="ins-card-accent {{ $activa && !$esAntic ? 'activa' : '' }}"
                             style="{{ $esAntic ? 'background:#f39c12;' : '' }}"></div>
                        <div class="ins-card-body">
                            <div class="ins-icon" style="background:{{ $iconBg }};">
                                <i class="fa {{ $esAntic ? 'fa-calendar-plus-o' : 'fa-graduation-cap' }}"
                                   style="font-size:18px;color:{{ $iconColor }};"></i>
                            </div>
                            <div style="flex:1;">
                                <div class="ins-ciclo">
                                    {{ $ciclo }}
                                    @if ($esAntic && $activa)
                                        <span class="ins-chip"
                                              style="margin-left:6px;font-size:10px;background:#fff3cd;color:#856404;">
                                            PRÓXIMO CICLO
                                        </span>
                                    @elseif ($activa)
                                        <span class="ins-chip activa" style="margin-left:6px;font-size:10px;">ACTIVA</span>
                                    @endif
                                </div>
                                <div class="ins-detalle">
                                    @if ($nivel !== '—')
                                        <span class="ins-chip">{{ $nivel }}</span>
                                    @endif
                                    @if ($grupo)
                                        <span>{{ $grado }}° Grado</span>
                                        <span>·</span>
                                        <span style="font-weight:700;color:#333;">Grupo {{ $grupo }}</span>
                                    @else
                                        <span style="color:#b0bec5;font-style:italic;">Sin grupo asignado aún</span>
                                    @endif
                                    @if ($inscripcion->fecha)
                                        <span style="color:#b0bec5;">· {{ $inscripcion->fecha->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if (!$activa)
                                <span style="font-size:11px;color:#b0bec5;font-weight:600;">Inactiva</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:22px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:8px;">
                        <i class="fa fa-graduation-cap"
                            style="font-size:28px;color:#dde4ea;display:block;margin-bottom:8px;"></i>
                        <p style="color:#b0bec5;margin:0;">Sin inscripciones registradas.</p>
                    </div>
                @endforelse
            </div>

            {{-- ── CONTACTOS FAMILIARES ── --}}
            <div style="margin-bottom:14px;">
                <p class="sec-title">
                    <i class="fa fa-phone" style="color:#3c8dbc;"></i>
                    Contactos familiares
                    <span
                        style="background:#e8f0fb;color:#3c8dbc;font-size:11px;font-weight:700;
                         padding:2px 9px;border-radius:10px;">{{ $totalContactos }}</span>
                </p>

                @forelse($alumno->contactos->sortBy('pivot.orden') as $contacto)
                    @php
                        $pivot = $contacto->pivot;
                        $esPrincipal = $pivot && $pivot->orden == 1;
                        $inicial = mb_strtoupper(mb_substr($contacto->nombre, 0, 1));
                    @endphp

                    <div class="ctc-card {{ $esPrincipal ? 'principal' : '' }}">
                        <div class="ctc-head">
                            @if ($contacto->foto_url)
                                <img src="{{ asset('storage/' . $contacto->foto_url) }}"
                                    style="width:36px;height:36px;border-radius:50%;object-fit:cover;
                                border:2px solid {{ $esPrincipal ? '#3c8dbc' : '#e0e0e0' }};flex-shrink:0;">
                            @else
                                <div class="ctc-avatar">{{ $inicial }}</div>
                            @endif

                            <div style="flex:1;min-width:0;">
                                <div class="ctc-nombre">
                                    {{ $contacto->nombre }} {{ $contacto->ap_paterno }} {{ $contacto->ap_materno }}
                                </div>
                                <div class="ctc-badges">
                                    @if ($esPrincipal)
                                        <span class="ctc-badge ctc-badge-principal">
                                            <i class="fa fa-star" style="font-size:8px;"></i> Principal
                                        </span>
                                    @endif
                                    @if ($pivot && $pivot->parentesco)
                                        <span
                                            class="ctc-badge ctc-badge-parentesco">{{ ucfirst($pivot->parentesco) }}</span>
                                    @endif
                                    @if ($pivot && $pivot->autorizado_recoger)
                                        <span class="ctc-badge ctc-badge-recoger">
                                            <i class="fa fa-check"></i> Recoger
                                        </span>
                                    @endif
                                    @if ($pivot && $pivot->es_responsable_pago)
                                        <span class="ctc-badge ctc-badge-pagos">
                                            <i class="fa fa-dollar"></i> Pagos
                                        </span>
                                    @endif
                                    @if ($contacto->tiene_acceso_portal)
                                        <span class="ctc-badge ctc-badge-portal">
                                            <i class="fa fa-globe"></i> Portal
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if ($contacto->telefono_celular || $contacto->telefono_trabajo || $contacto->email || $contacto->curp)
                            <div class="ctc-contacto-grid">
                                @if ($contacto->telefono_celular)
                                    <a href="tel:{{ preg_replace('/\D/', '', $contacto->telefono_celular) }}"
                                        class="ctc-dato">
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

                                @if ($contacto->telefono_trabajo)
                                    <a href="tel:{{ preg_replace('/\D/', '', $contacto->telefono_trabajo) }}"
                                        class="ctc-dato">
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

                                @if ($contacto->email)
                                    <a href="mailto:{{ $contacto->email }}" class="ctc-dato">
                                        <div class="ctc-dato-icon" style="background:#f3e8fd;">
                                            <i class="fa fa-envelope-o" style="color:#8e44ad;font-size:13px;"></i>
                                        </div>
                                        <div style="min-width:0;">
                                            <div class="ctc-dato-val" style="font-size:12px;">{{ $contacto->email }}
                                            </div>
                                            <div class="ctc-dato-lbl">Correo</div>
                                        </div>
                                    </a>
                                @endif

                                @if ($contacto->curp)
                                    <div class="ctc-dato no-link">
                                        <div class="ctc-dato-icon" style="background:#e8f5e9;">
                                            <i class="fa fa-id-card-o" style="color:#2e7d32;font-size:12px;"></i>
                                        </div>
                                        <div style="min-width:0;">
                                            <div class="ctc-dato-val" style="font-size:11px;font-family:monospace;">
                                                {{ $contacto->curp }}</div>
                                            <div class="ctc-dato-lbl">CURP</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="padding:22px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:8px;">
                        <i class="fa fa-phone" style="font-size:28px;color:#dde4ea;display:block;margin-bottom:8px;"></i>
                        <p style="color:#b0bec5;margin:0;">Sin contactos registrados.</p>
                    </div>
                @endforelse
            </div>

            {{-- ── DATOS FISCALES ── --}}
            @if($alumno->familia)
            <div style="margin-bottom:14px;">
                @include('familias._razon_social', ['familia' => $alumno->familia])
            </div>
            @endif

            {{-- ── EXPEDIENTE MÉDICO ── --}}
            <div style="margin-bottom:14px;">

                {{-- Ficha médica general --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <p class="sec-title" style="flex:1;margin:0;">
                        <i class="fa fa-heartbeat" style="color:#e74c3c;"></i>
                        Expediente médico
                    </p>
                    @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <button type="button" class="btn btn-xs btn-flat"
                                style="margin-left:12px;background:#fdecea;color:#c0392b;border:1px solid #f5b7b1;border-radius:6px;"
                                data-toggle="modal" data-target="#modalFichaMedica">
                            <i class="fa fa-pencil"></i>
                            {{ $alumno->fichaMedica ? 'Editar ficha' : 'Completar ficha' }}
                        </button>
                    @endif
                </div>

                @if ($alumno->fichaMedica)
                    @php $fm = $alumno->fichaMedica; @endphp
                    <div class="info-card" style="margin-bottom:14px;">
                        <div class="info-card-header">
                            <span class="info-card-title">
                                <i class="fa fa-medkit" style="margin-right:5px;color:#e74c3c;"></i>Datos generales
                            </span>
                            @if ($fm->actualizado_at)
                                <span style="font-size:10px;color:#b0bec5;">
                                    Actualizado {{ $fm->actualizado_at->format('d/m/Y') }}
                                </span>
                            @endif
                        </div>

                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid #f5f7fa;">
                            @if ($fm->tipo_sangre)
                                <div style="padding:8px 12px;border-right:1px solid #f5f7fa;text-align:center;">
                                    <div style="font-size:16px;font-weight:800;color:#e74c3c;">{{ $fm->tipo_sangre }}</div>
                                    <div style="font-size:9px;color:#9aa5b4;text-transform:uppercase;letter-spacing:.05em;margin-top:1px;">Tipo sangre</div>
                                </div>
                            @endif
                            @if ($fm->peso_kg)
                                <div style="padding:8px 12px;border-right:1px solid #f5f7fa;text-align:center;">
                                    <div style="font-size:16px;font-weight:800;color:#2c3e50;">{{ $fm->peso_kg }} <span style="font-size:10px;font-weight:400;color:#9aa5b4;">kg</span></div>
                                    <div style="font-size:9px;color:#9aa5b4;text-transform:uppercase;letter-spacing:.05em;margin-top:1px;">Peso</div>
                                </div>
                            @endif
                            @if ($fm->talla_cm)
                                <div style="padding:8px 12px;text-align:center;">
                                    <div style="font-size:16px;font-weight:800;color:#2c3e50;">{{ $fm->talla_cm }} <span style="font-size:10px;font-weight:400;color:#9aa5b4;">cm</span></div>
                                    <div style="font-size:9px;color:#9aa5b4;text-transform:uppercase;letter-spacing:.05em;margin-top:1px;">Talla</div>
                                </div>
                            @endif
                        </div>

                        @if ($fm->medico_nombre || $fm->hospital_preferente)
                            @if ($fm->medico_nombre)
                                <div class="info-row">
                                    <span class="info-row-label"><i class="fa fa-user-md" style="margin-right:4px;"></i>Médico</span>
                                    <span class="info-row-value">
                                        {{ $fm->medico_nombre }}
                                        @if ($fm->medico_telefono)
                                            <small style="color:#8a9ab0;font-weight:400;"> · {{ $fm->medico_telefono }}</small>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            @if ($fm->hospital_preferente)
                                <div class="info-row">
                                    <span class="info-row-label"><i class="fa fa-hospital-o" style="margin-right:4px;"></i>Hospital</span>
                                    <span class="info-row-value">{{ $fm->hospital_preferente }}</span>
                                </div>
                            @endif
                        @endif

                        @if ($fm->discapacidad)
                            <div style="padding:10px 16px;font-size:12px;color:#6b7a8d;border-top:1px solid #f5f7fa;background:#fafbfc;line-height:1.5;">
                                <i class="fa fa-wheelchair" style="margin-right:5px;color:#7f8c8d;"></i>
                                <strong>Discapacidad:</strong> {{ $fm->discapacidad }}
                            </div>
                        @endif

                        @if ($fm->observaciones_generales)
                            <div style="padding:10px 16px;font-size:12px;color:#6b7a8d;border-top:1px solid #f5f7fa;background:#fafbfc;line-height:1.5;">
                                <i class="fa fa-sticky-note-o" style="margin-right:5px;"></i>{{ $fm->observaciones_generales }}
                            </div>
                        @endif
                    </div>
                @else
                    <div style="padding:28px 20px;text-align:center;border:2px dashed #f5b7b1;border-radius:10px;background:#fef9f9;margin-bottom:14px;">
                        <i class="fa fa-heartbeat" style="font-size:32px;color:#f5b7b1;display:block;margin-bottom:10px;"></i>
                        <p style="color:#c0392b;margin:0 0 10px;font-size:13px;">Ficha médica no registrada.</p>
                        @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                            <button type="button" class="btn btn-xs btn-flat"
                                    style="background:#e74c3c;color:#fff;border-radius:6px;padding:5px 14px;"
                                    data-toggle="modal" data-target="#modalFichaMedica">
                                <i class="fa fa-plus"></i> Completar ficha médica
                            </button>
                        @endif
                    </div>
                @endif

                {{-- Condiciones médicas --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7a8d;">
                        <i class="fa fa-exclamation-triangle" style="color:#e67e22;margin-right:5px;"></i>
                        Condiciones médicas
                        @if ($alumno->condicionesMedicas->count() > 0)
                            <span style="background:#fff3cd;color:#856404;font-size:10px;font-weight:700;
                                         padding:1px 7px;border-radius:8px;margin-left:5px;">
                                {{ $alumno->condicionesMedicas->count() }}
                            </span>
                        @endif
                    </span>
                    @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <button type="button" class="btn btn-xs btn-flat"
                                style="background:#fff3cd;color:#856404;border:1px solid #fde68a;border-radius:6px;"
                                data-toggle="modal" data-target="#modalCondicion">
                            <i class="fa fa-plus"></i> Agregar
                        </button>
                    @endif
                </div>

                @if ($alumno->condicionesMedicas->count())
                    <div class="info-card" style="margin-bottom:14px;">
                        @foreach ($alumno->condicionesMedicas as $condicion)
                            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 12px;
                                         border-bottom:1px solid #f5f7fa;">
                                <div style="width:28px;height:28px;border-radius:6px;flex-shrink:0;
                                             display:flex;align-items:center;justify-content:center;
                                             background:{{ $condicion->nivel_riesgo === 'leve' ? '#e8f8f0' : ($condicion->nivel_riesgo === 'moderado' ? '#fff8e1' : '#fdecea') }};">
                                    <i class="fa fa-exclamation"
                                       style="color:{{ $condicion->colorRiesgo() }};font-size:13px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:700;color:#1a2634;">
                                        {{ $condicion->nombre }}
                                    </div>
                                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:4px;">
                                        <span style="font-size:10px;font-weight:600;padding:1px 7px;border-radius:8px;
                                                      background:#f0f3f7;color:#5a6a7a;">
                                            {{ $condicion->tipoEtiqueta() }}
                                        </span>
                                        <span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:8px;
                                                      color:#fff;background:{{ $condicion->colorRiesgo() }};">
                                            {{ ucfirst($condicion->nivel_riesgo) }}
                                        </span>
                                    </div>
                                    @if ($condicion->descripcion)
                                        <div style="font-size:11px;color:#6b7a8d;margin-top:4px;">{{ $condicion->descripcion }}</div>
                                    @endif
                                    @if ($condicion->requiere_accion && $condicion->accion_requerida)
                                        <div style="margin-top:6px;padding:6px 10px;border-radius:6px;
                                                     background:#fdecea;border-left:3px solid #dd4b39;">
                                            <div style="font-size:10px;font-weight:700;color:#b91c1c;margin-bottom:2px;">
                                                <i class="fa fa-bolt"></i> ACCIÓN REQUERIDA
                                            </div>
                                            <div style="font-size:11px;color:#7f1d1d;">{{ $condicion->accion_requerida }}</div>
                                        </div>
                                    @endif
                                </div>
                                @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                                    <form method="POST"
                                          action="{{ route('condiciones-medicas.destroy', $condicion->id) }}"
                                          onsubmit="return confirm('¿Eliminar esta condición médica?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-flat"
                                                style="color:#dd4b39;background:none;border:none;padding:4px 6px;"
                                                title="Eliminar">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:16px 20px;text-align:center;border:1px dashed #e8ecf0;border-radius:8px;
                                 background:#fafbfc;margin-bottom:14px;">
                        <p style="color:#b0bec5;margin:0;font-size:12px;">Sin condiciones registradas.</p>
                    </div>
                @endif

                {{-- Medicamentos autorizados --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7a8d;">
                        <i class="fa fa-pills" style="color:#8e44ad;margin-right:5px;"></i>
                        Medicamentos autorizados
                        @if ($alumno->medicamentosAutorizados->count() > 0)
                            <span style="background:#f3e8fd;color:#6b21a8;font-size:10px;font-weight:700;
                                         padding:1px 7px;border-radius:8px;margin-left:5px;">
                                {{ $alumno->medicamentosAutorizados->count() }}
                            </span>
                        @endif
                    </span>
                    @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <button type="button" class="btn btn-xs btn-flat"
                                style="background:#f3e8fd;color:#6b21a8;border:1px solid #d8b4fe;border-radius:6px;"
                                data-toggle="modal" data-target="#modalMedicamento">
                            <i class="fa fa-plus"></i> Agregar
                        </button>
                    @endif
                </div>

                @if ($alumno->medicamentosAutorizados->count())
                    <div class="info-card">
                        @foreach ($alumno->medicamentosAutorizados as $med)
                            <div style="display:flex;align-items:flex-start;gap:10px;padding:8px 12px;
                                         border-bottom:1px solid #f5f7fa;">
                                <div style="width:28px;height:28px;border-radius:6px;flex-shrink:0;
                                             background:#f3e8fd;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa fa-medkit" style="color:#8e44ad;font-size:13px;"></i>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:13px;font-weight:700;color:#1a2634;">
                                        {{ $med->nombre_medicamento }}
                                    </div>
                                    <div style="font-size:11px;color:#6b7a8d;margin-top:3px;">
                                        <i class="fa fa-tint" style="color:#8e44ad;font-size:10px;"></i>
                                        {{ $med->dosis }} &nbsp;·&nbsp;
                                        <i class="fa fa-clock-o" style="font-size:10px;"></i>
                                        {{ $med->frecuencia }}
                                        @if ($med->horario)
                                            &nbsp;·&nbsp; {{ $med->horario }}
                                        @endif
                                    </div>
                                    @if ($med->requiere_refrigeracion)
                                        <span style="font-size:10px;font-weight:600;color:#1565c0;
                                                      background:#e3f2fd;border:1px solid #bbdefb;
                                                      padding:1px 7px;border-radius:8px;display:inline-block;margin-top:4px;">
                                            <i class="fa fa-snowflake-o"></i> Requiere refrigeración
                                        </span>
                                    @endif
                                    @if ($med->instrucciones)
                                        <div style="font-size:11px;color:#6b7a8d;margin-top:4px;font-style:italic;">
                                            {{ $med->instrucciones }}
                                        </div>
                                    @endif
                                    <div style="font-size:10px;color:#9aa5b4;margin-top:4px;">
                                        Autorizado por: <strong>{{ $med->contactoAutoriza?->nombre ?? '—' }}</strong>
                                        @if ($med->vigencia_fin)
                                            &nbsp;·&nbsp; Vigente hasta {{ $med->vigencia_fin->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                                @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                                    <form method="POST"
                                          action="{{ route('medicamentos-autorizados.destroy', $med->id) }}"
                                          onsubmit="return confirm('¿Eliminar este medicamento?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-flat"
                                                style="color:#dd4b39;background:none;border:none;padding:4px 6px;"
                                                title="Eliminar">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:16px 20px;text-align:center;border:1px dashed #e8ecf0;border-radius:8px;background:#fafbfc;">
                        <p style="color:#b0bec5;margin:0;font-size:12px;">Sin medicamentos autorizados registrados.</p>
                    </div>
                @endif

            </div>{{-- /expediente médico --}}

            {{-- ── DOCUMENTOS ── --}}
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <p class="sec-title" style="flex:1;margin:0;">
                        <i class="fa fa-file-text-o" style="color:#3c8dbc;"></i>
                        Documentos
                        @if ($totalDocs > 0)
                            <span
                                style="background:{{ $pendientesDocs > 0 ? '#fdecea' : '#e8f8f0' }};
                             color:{{ $pendientesDocs > 0 ? '#b91c1c' : '#00875a' }};
                             font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px;">
                                {{ $entregados }}/{{ $totalDocs }}
                            </span>
                        @endif
                    </p>
                </div>

                @if ($alumno->documentos->count())
                    <div class="info-card" style="margin-bottom:0;">
                        @foreach ($alumno->documentos as $doc)
                            <div class="doc-row">
                                @switch($doc->estado)
                                    @case('entregado')
                                        <div class="doc-estado-icon" style="background:#e8f8f0;">
                                            <i class="fa fa-check" style="color:#00a65a;font-size:12px;"></i>
                                        </div>
                                    @break

                                    @case('no_aplica')
                                        <div class="doc-estado-icon" style="background:#f0f3f7;">
                                            <i class="fa fa-minus" style="color:#b0bec5;font-size:12px;"></i>
                                        </div>
                                    @break

                                    @default
                                        <div class="doc-estado-icon" style="background:#fdecea;">
                                            <i class="fa fa-clock-o" style="color:#dd4b39;font-size:12px;"></i>
                                        </div>
                                @endswitch

                                <span style="flex:1;color:#333;">{{ $doc->tipo_documento }}</span>

                                <span
                                    style="font-size:11px;margin-right:10px;
                             font-weight:600;
                             color:{{ $doc->estado === 'entregado' ? '#00a65a' : ($doc->estado === 'no_aplica' ? '#b0bec5' : '#dd4b39') }};">
                                    @switch($doc->estado)
                                        @case('entregado')
                                            Entregado
                                        @break

                                        @case('no_aplica')
                                            No aplica
                                        @break

                                        @default
                                            Pendiente
                                    @endswitch
                                </span>

                                @if ($doc->archivo_url)
                                    <a href="{{ asset('storage/' . $doc->archivo_url) }}" target="_blank"
                                        class="btn btn-default btn-xs btn-flat" style="border-radius:5px;"
                                        title="Descargar">
                                        <i class="fa fa-download"></i>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:22px 20px;text-align:center;border:2px dashed #e8ecf0;border-radius:8px;">
                        <i class="fa fa-folder-open-o"
                            style="font-size:28px;color:#dde4ea;display:block;margin-bottom:8px;"></i>
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
                        @if ($alumno->fecha_nacimiento)
                            <small style="color:#8a9ab0;font-weight:400;"> · {{ $alumno->fecha_nacimiento->age }}
                                años</small>
                        @endif
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-row-label">Género</span>
                    <span class="info-row-value">
                        @switch($alumno->genero)
                            @case('M')
                                <i class="fa fa-mars" style="color:#3c8dbc;"></i> Masculino
                            @break

                            @case('F')
                                <i class="fa fa-venus" style="color:#e91e8c;"></i> Femenino
                            @break

                            @case('Otro')
                                Otro
                            @break

                            @default
                                <span style="color:#ccc;">—</span>
                        @endswitch
                    </span>
                </div>

                @if ($alumno->curp)
                    <div class="info-row">
                        <span class="info-row-label">CURP</span>
                        <span class="info-row-value">
                            <code
                                style="font-size:11px;background:#f0f3f7;padding:2px 6px;border-radius:4px;color:#4a5568;">
                                {{ $alumno->curp }}
                            </code>
                        </span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-row-label">Inscripción</span>
                    <span class="info-row-value">{{ $alumno->fecha_inscripcion?->format('d/m/Y') ?? '—' }}</span>
                </div>

                @if ($alumno->fecha_baja)
                    <div class="info-row">
                        <span class="info-row-label">Baja</span>
                        <span class="info-row-value" style="color:#dd4b39;">
                            {{ $alumno->fecha_baja->format('d/m/Y') }}
                        </span>
                    </div>
                @endif

                @if ($alumno->observaciones)
                    <div
                        style="padding:10px 16px;font-size:12px;color:#6b7a8d;border-top:1px solid #f5f7fa;
                    background:#fafbfc;line-height:1.5;">
                        <i class="fa fa-sticky-note-o" style="margin-right:5px;"></i>{{ $alumno->observaciones }}
                    </div>
                @endif
            </div>

            {{-- Familia --}}
            @if ($alumno->familia)
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
                    <div style="padding:10px 14px;display:flex;align-items:center;gap:10px;">
                        <div
                            style="width:32px;height:32px;border-radius:50%;background:#e8f5e9;
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


            @if (auth()->user()->esAdministrador())
                <a href="{{ route('becas.create', ['alumno_id' => $alumno->id]) }}" class="accion-btn"
                    style="background:#fff; border: 1px solid #e4eaf0; border-radius: 10px; margin-bottom: 18px;">
                    <div class="accion-icon" style="background:#fff8e1;">
                        <i class="fa fa-star" style="color:#f39c12;font-size:14px;"></i>
                    </div>
                    Asignar beca
                    <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                </a>
            @endif

            {{-- Becas activas --}}
            @if ($alumno->becas->where('activo', true)->count() > 0)
                <div class="info-card">
                    <div class="info-card-header">
                        <span class="info-card-title">
                            <i class="fa fa-star" style="margin-right:5px;color:#f39c12;"></i>Becas
                            activas
                        </span>
                        <span
                            style="background:#fff8e1;color:#b45309;font-size:11px;font-weight:700;
                                        padding:2px 9px;border-radius:10px;">
                            {{ $alumno->becas->where('activo', true)->count() }}
                        </span>
                    </div>
                    @foreach ($alumno->becas->where('activo', true) as $beca)
                        <div class="beca-row">
                            <div
                                style="width:28px;height:28px;border-radius:6px;background:#fff8e1;
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
                            <span
                                style="background:#fff3cd;color:#856404;font-size:12px;font-weight:700;
                         padding:2px 10px;border-radius:10px;white-space:nowrap;">
                                @if ($beca->catalogoBeca->tipo === 'porcentaje')
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

                    @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <a href="{{ route('alumnos.edit', $alumno->id) }}" class="accion-btn">
                            <div class="accion-icon" style="background:#e8f0fb;">
                                <i class="fa fa-pencil" style="color:#3c8dbc;font-size:13px;"></i>
                            </div>
                            Editar datos
                            <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                        </a>
                    @endif

                    @if (auth()->user()->esAdministrador() || auth()->user()->esCajero())
                        <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}" class="accion-btn">
                            <div class="accion-icon" style="background:#fff8e1;">
                                <i class="fa fa-dollar" style="color:#f39c12;font-size:14px;"></i>
                            </div>
                            Estado de cuenta
                            <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                        </a>
                    @endif

                    @if ($alumno->familia)
                        <a href="{{ route('familias.show', $alumno->familia->id) }}" class="accion-btn">
                            <div class="accion-icon" style="background:#e8f5e9;">
                                <i class="fa fa-home" style="color:#4caf50;font-size:14px;"></i>
                            </div>
                            Ver familia
                            <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                        </a>
                    @endif

                    @if (auth()->user()->esAdministrador())
                        <a href="{{ route('planes.asignar.form', ['alumno_id' => $alumno->id]) }}" class="accion-btn">
                            <div class="accion-icon" style="background:#f0fdf4;">
                                <i class="fa fa-file-text-o" style="color:#16a34a;font-size:13px;"></i>
                            </div>
                            Asignar plan de pagos
                            <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                        </a>
                    @endif

                    @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                        <button type="button" class="accion-btn"
                                style="width:100%;text-align:left;background:none;border:none;cursor:pointer;"
                                data-toggle="modal" data-target="#modalFichaMedica">
                            <div class="accion-icon" style="background:#fdecea;">
                                <i class="fa fa-heartbeat" style="color:#e74c3c;font-size:13px;"></i>
                            </div>
                            Expediente médico
                            <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                        </button>
                    @endif

                    @if ((auth()->user()->esAdministrador() || auth()->user()->esRecepcion()) && $alumno->estado === 'activo')
                        <button type="button" class="accion-btn" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;"
                                data-toggle="modal" data-target="#modalBaja">
                            <div class="accion-icon" style="background:#fdecea;">
                                <i class="fa fa-user-times" style="color:#e74c3c;font-size:13px;"></i>
                            </div>
                            Dar de baja
                            <i class="fa fa-chevron-right" style="margin-left:auto;color:#dde4eb;font-size:11px;"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Historial de bajas --}}
            @if ($alumno->historialBajas->isNotEmpty())
                <div class="info-card" style="border-color:#fca5a5;">
                    <div class="info-card-header" style="background:#fff5f5;">
                        <span class="info-card-title" style="color:#b91c1c;">
                            <i class="fa fa-history" style="margin-right:5px;"></i>Historial de bajas
                        </span>
                    </div>
                    @foreach ($alumno->historialBajas as $baja)
                        <div style="padding:10px 16px;border-bottom:1px solid #f5f7fa;font-size:12px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                <span style="font-weight:700;color:{{ $baja->tipo === 'baja_definitiva' ? '#b91c1c' : '#b45309' }};">
                                    {{ $baja->tipoEtiqueta() }}
                                </span>
                                <span style="color:#9aa5b4;">{{ $baja->fecha_baja->format('d/m/Y') }}</span>
                            </div>
                            <div style="color:#4a5568;margin-top:2px;">
                                <i class="fa fa-tag" style="font-size:9px;"></i>
                                {{ $baja->motivo_categoria->etiqueta() }}
                            </div>
                            @if ($baja->motivo_detalle)
                                <div style="color:#6b7a8d;margin-top:3px;font-style:italic;">
                                    "{{ $baja->motivo_detalle }}"
                                </div>
                            @endif
                            @if ($baja->registradoPor)
                                <div style="color:#b0bec5;margin-top:2px;">
                                    Registrado por {{ $baja->registradoPor->nombre }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>{{-- /col-md-4 --}}

    </div>{{-- /row --}}

    {{-- ══ MODAL DAR DE BAJA ══ --}}
    @if ((auth()->user()->esAdministrador() || auth()->user()->esRecepcion()) && $alumno->estado === 'activo')
    <div class="modal fade" id="modalBaja" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#fff5f5;border-bottom:1px solid #fca5a5;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#b91c1c;">
                        <i class="fa fa-user-times"></i> Dar de baja al alumno
                    </h4>
                </div>
                <form method="POST" action="{{ route('alumnos.darBaja', $alumno->id) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <p style="font-size:13px;color:#6b7a8d;margin-bottom:16px;">
                            Esta acción registrará la baja de
                            <strong>{{ $alumno->nombre }} {{ $alumno->ap_paterno }}</strong>
                            y desactivará su inscripción actual.
                        </p>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Tipo de baja <span style="color:#e74c3c;">*</span></label>
                            <select name="tipo_baja" class="form-control" required>
                                <option value="">— Selecciona —</option>
                                <option value="baja_temporal">Temporal (puede reingresar)</option>
                                <option value="baja_definitiva">Definitiva</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Motivo de baja <span style="color:#e74c3c;">*</span></label>
                            <select name="motivo_categoria" class="form-control" required>
                                <option value="">— Selecciona el motivo —</option>
                                <option value="cambio_escuela">Cambio de escuela</option>
                                <option value="traslado">Traslado de ciudad/estado</option>
                                <option value="economico">Motivos económicos</option>
                                <option value="familiar">Situación familiar</option>
                                <option value="salud">Problemas de salud</option>
                                <option value="conducta">Problemas de conducta</option>
                                <option value="rendimiento">Bajo rendimiento académico</option>
                                <option value="otro">Otro motivo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Observaciones adicionales</label>
                            <textarea name="motivo_detalle" class="form-control" rows="3"
                                      placeholder="Detalles adicionales sobre la baja (opcional)"
                                      maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f3f7;">
                        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-flat"
                                onclick="return confirm('¿Confirmas la baja de este alumno?');">
                            <i class="fa fa-user-times"></i> Confirmar baja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL FICHA MÉDICA ══ --}}
    @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
    <div class="modal fade" id="modalFichaMedica" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#fdecea;border-bottom:1px solid #f5b7b1;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#c0392b;">
                        <i class="fa fa-heartbeat"></i>
                        {{ $alumno->fichaMedica ? 'Editar ficha médica' : 'Completar ficha médica' }}
                        — {{ $alumno->nombre }} {{ $alumno->ap_paterno }}
                    </h4>
                </div>
                <form method="POST" action="{{ route('ficha-medica.storeOrUpdate', $alumno->id) }}">
                    @csrf
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Tipo de sangre</label>
                                    <select name="tipo_sangre" class="form-control">
                                        <option value="">— Sin registrar —</option>
                                        @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $ts)
                                            <option value="{{ $ts }}"
                                                {{ old('tipo_sangre', $alumno->fichaMedica?->tipo_sangre) === $ts ? 'selected' : '' }}>
                                                {{ $ts }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Peso (kg)</label>
                                    <input type="number" name="peso_kg" step="0.1" min="1" max="300"
                                           class="form-control"
                                           value="{{ old('peso_kg', $alumno->fichaMedica?->peso_kg) }}"
                                           placeholder="Ej: 35.5">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Talla (cm)</label>
                                    <input type="number" name="talla_cm" step="0.1" min="30" max="250"
                                           class="form-control"
                                           value="{{ old('talla_cm', $alumno->fichaMedica?->talla_cm) }}"
                                           placeholder="Ej: 130.0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Médico de cabecera</label>
                                    <input type="text" name="medico_nombre" class="form-control" maxlength="255"
                                           value="{{ old('medico_nombre', $alumno->fichaMedica?->medico_nombre) }}"
                                           placeholder="Dr. Juan Pérez López">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Teléfono del médico</label>
                                    <input type="text" name="medico_telefono" class="form-control" maxlength="20"
                                           value="{{ old('medico_telefono', $alumno->fichaMedica?->medico_telefono) }}"
                                           placeholder="Ej: 667 234 5678">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Hospital de preferencia en emergencias</label>
                            <input type="text" name="hospital_preferente" class="form-control" maxlength="255"
                                   value="{{ old('hospital_preferente', $alumno->fichaMedica?->hospital_preferente) }}"
                                   placeholder="Ej: Hospital General, IMSS Clínica 4">
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Discapacidad (si aplica)</label>
                            <textarea name="discapacidad" class="form-control" rows="2" maxlength="1000"
                                      placeholder="Describe la discapacidad si el alumno tiene alguna">{{ old('discapacidad', $alumno->fichaMedica?->discapacidad) }}</textarea>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:12px;font-weight:700;color:#555;">Observaciones generales</label>
                            <textarea name="observaciones_generales" class="form-control" rows="3" maxlength="2000"
                                      placeholder="Información adicional relevante para el personal escolar">{{ old('observaciones_generales', $alumno->fichaMedica?->observaciones_generales) }}</textarea>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f3f7;">
                        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-flat" style="background:#e74c3c;color:#fff;">
                            <i class="fa fa-save"></i> Guardar ficha médica
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MODAL CONDICIÓN MÉDICA ══ --}}
    <div class="modal fade" id="modalCondicion" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#fff8e1;border-bottom:1px solid #fde68a;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#856404;">
                        <i class="fa fa-exclamation-triangle"></i> Agregar condición médica
                    </h4>
                </div>
                <form method="POST" action="{{ route('condiciones-medicas.store', $alumno->id) }}">
                    @csrf
                    <div class="modal-body">

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Tipo <span style="color:#e74c3c;">*</span></label>
                                    <select name="tipo" class="form-control" required>
                                        <option value="">— Selecciona —</option>
                                        <option value="padecimiento">Padecimiento</option>
                                        <option value="alergia_alimento">Alergia alimentaria</option>
                                        <option value="alergia_medicamento">Alergia a medicamento</option>
                                        <option value="alergia_ambiental">Alergia ambiental</option>
                                        <option value="discapacidad">Discapacidad</option>
                                        <option value="neurodivergencia">Neurodivergencia</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Nivel de riesgo <span style="color:#e74c3c;">*</span></label>
                                    <select name="nivel_riesgo" class="form-control" required>
                                        <option value="leve">Leve</option>
                                        <option value="moderado">Moderado</option>
                                        <option value="grave">Grave</option>
                                        <option value="critico">Crítico</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Nombre / diagnóstico <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="nombre" class="form-control" required maxlength="255"
                                   placeholder="Ej: Asma, Alergia a la penicilina, Cacahuates">
                        </div>

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Descripción / detalles clínicos</label>
                            <textarea name="descripcion" class="form-control" rows="2" maxlength="1000"
                                      placeholder="Información clínica adicional (opcional)"></textarea>
                        </div>

                        <div class="form-group">
                            <div class="checkbox" style="margin:0 0 10px;">
                                <label style="font-size:13px;font-weight:600;color:#555;">
                                    <input type="checkbox" name="requiere_accion" value="1" id="chkAccion">
                                    El personal escolar debe intervenir si se presenta
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="grupoAccion" style="display:none;margin-bottom:0;">
                            <label style="font-size:12px;font-weight:700;color:#b91c1c;">
                                <i class="fa fa-bolt"></i> Acción requerida <span style="color:#e74c3c;">*</span>
                            </label>
                            <textarea name="accion_requerida" class="form-control" rows="3" maxlength="1000"
                                      placeholder="Ej: Aplicar EpiPen y llamar al 911 inmediatamente. El EpiPen está en la mochila del alumno."></textarea>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f3f7;">
                        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-flat" style="background:#e67e22;color:#fff;">
                            <i class="fa fa-save"></i> Guardar condición
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ══ MODAL MEDICAMENTO AUTORIZADO ══ --}}
    <div class="modal fade" id="modalMedicamento" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#f3e8fd;border-bottom:1px solid #d8b4fe;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" style="color:#6b21a8;">
                        <i class="fa fa-medkit"></i> Agregar medicamento autorizado
                    </h4>
                </div>
                <form method="POST" action="{{ route('medicamentos-autorizados.store', $alumno->id) }}">
                    @csrf
                    <div class="modal-body">

                        <div class="form-group">
                            <label style="font-size:12px;font-weight:700;color:#555;">Contacto que autoriza <span style="color:#e74c3c;">*</span></label>
                            <select name="autorizado_por_contacto" class="form-control" required>
                                <option value="">— Selecciona el padre o tutor —</option>
                                @foreach ($alumno->contactos as $contacto)
                                    <option value="{{ $contacto->id }}">
                                        {{ $contacto->nombre }}
                                        {{ $contacto->ap_paterno }}
                                        {{ $contacto->ap_materno }}
                                        @if ($contacto->pivot?->parentesco)
                                            ({{ ucfirst($contacto->pivot->parentesco) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Nombre del medicamento <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" name="nombre_medicamento" class="form-control" required maxlength="255"
                                           placeholder="Ej: Salbutamol inhalador, Ritalin 10mg">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Dosis <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" name="dosis" class="form-control" required maxlength="255"
                                           placeholder="Ej: 2 inhalaciones, 1 tableta">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Frecuencia <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" name="frecuencia" class="form-control" required maxlength="255"
                                           placeholder="Ej: En caso de crisis, Diario, Cada 8 horas">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Horario</label>
                                    <input type="text" name="horario" class="form-control" maxlength="255"
                                           placeholder="Ej: 12:00 pm con el almuerzo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label style="font-size:12px;font-weight:700;color:#555;">Vigencia de autorización</label>
                                    <input type="date" name="vigencia_fin" class="form-control"
                                           min="{{ now()->addDay()->toDateString() }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group" style="padding-top:26px;">
                                    <div class="checkbox" style="margin:0;">
                                        <label style="font-size:13px;font-weight:600;color:#555;">
                                            <input type="checkbox" name="requiere_refrigeracion" value="1">
                                            Requiere refrigeración
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:12px;font-weight:700;color:#555;">Instrucciones especiales para el personal</label>
                            <textarea name="instrucciones" class="form-control" rows="3" maxlength="1000"
                                      placeholder="Instrucciones adicionales sobre cómo administrar el medicamento (opcional)"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer" style="border-top:1px solid #f0f3f7;">
                        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-flat" style="background:#8e44ad;color:#fff;">
                            <i class="fa fa-save"></i> Guardar medicamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@push('scripts')
<script>
    document.getElementById('chkAccion')?.addEventListener('change', function () {
        document.getElementById('grupoAccion').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endpush

@endsection
