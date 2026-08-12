@extends('layouts.master')

@section('page_title', 'Mis hijos')
@section('page_subtitle', 'Información y accesos rápidos')

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li class="active">Mis hijos</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Tarjeta de alumno ── */
        .hijo-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e4eaf0;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            margin-bottom: 20px;
            overflow: hidden;
        }

        /* ── Cabecera de la tarjeta ── */
        .hijo-card-header {
            background: linear-gradient(135deg, #1f4e78 0%, #3c8dbc 100%);
            padding: 18px 16px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        /* Contenedor del avatar — proporción 3:4 igual que el recortador */
        .hijo-avatar {
            width: 42px;
            height: 56px;
            border-radius: 8px;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,.45);
            position: relative;
        }
        /* Foto real */
        .hijo-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        /* Placeholder cuando no hay foto */
        .hijo-avatar-placeholder {
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
        }
        .hijo-nombre {
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 4px;
            line-height: 1.2;
        }
        .hijo-matricula {
            color: rgba(255,255,255,.78);
            font-size: 13px;
            margin: 0;
        }
        .hijo-status {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .hijo-status-activo  { background: #d1fae5; color: #065f46; }
        .hijo-status-otro    { background: #fef3c7; color: #92400e; }

        /* ── Bloque de grupo ── */
        .hijo-grupo {
            background: #f0f7ff;
            border-bottom: 1px solid #dbeafe;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #1e40af;
            font-weight: 600;
        }
        .hijo-grupo i { font-size: 16px; }
        .hijo-grupo-ciclo {
            margin-left: auto;
            font-size: 12px;
            font-weight: 400;
            color: #60a5fa;
        }

        /* ── Datos del alumno ── */
        .hijo-datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-bottom: 1px solid #f0f3f7;
        }
        .hijo-dato {
            padding: 12px 16px;
            border-right: 1px solid #f0f3f7;
            border-bottom: 1px solid #f0f3f7;
        }
        .hijo-dato:nth-child(even) { border-right: none; }
        .hijo-dato:nth-last-child(-n+2) { border-bottom: none; }
        .hijo-dato-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9aa5b4;
            margin-bottom: 3px;
        }
        .hijo-dato-valor {
            font-size: 14px;
            font-weight: 600;
            color: #1a2634;
        }

        /* ── Acciones ── */
        .hijo-acciones {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0;
        }
        .hijo-accion {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 16px 8px;
            border-right: 1px solid #f0f3f7;
            text-decoration: none !important;
            font-size: 12px;
            font-weight: 700;
            color: #4a5568;
            transition: background .15s;
            text-align: center;
            line-height: 1.3;
        }
        .hijo-accion:last-child { border-right: none; }
        .hijo-accion:hover { background: #f8fafc; text-decoration: none; }
        .hijo-accion-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .hijo-accion-azul  .hijo-accion-icon { background: #dbeafe; color: #1d4ed8; }
        .hijo-accion-verde .hijo-accion-icon { background: #d1fae5; color: #065f46; }
        .hijo-accion-rojo  .hijo-accion-icon { background: #fdecea; color: #b91c1c; }
        .hijo-accion-azul  { color: #1d4ed8; }
        .hijo-accion-verde { color: #065f46; }
        .hijo-accion-rojo  { color: #b91c1c; }

        /* ── Vacío ── */
        .hijo-empty {
            text-align: center;
            padding: 48px 20px;
            color: #9aa5b4;
        }
        .hijo-empty-icon { font-size: 52px; margin-bottom: 14px; color: #d1d9e0; }
        .hijo-empty-text { font-size: 16px; }

        /* ── Sin grupo ── */
        .hijo-sin-grupo {
            background: #fffbeb;
            border-bottom: 1px solid #fde68a;
            padding: 10px 16px;
            font-size: 13px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 480px) {
            .hijo-nombre { font-size: 16px; }
            .hijo-accion { padding: 14px 4px; font-size: 11px; }
            .hijo-accion-icon { width: 40px; height: 40px; font-size: 18px; border-radius: 10px; }
        }
    </style>
@endpush

@section('content')

    {{-- Subtítulo con conteo --}}
    <div style="margin-bottom:16px;font-size:14px;color:#7b8794;">
        @if ($alumnos->count() > 0)
            {{ $alumnos->count() }} alumno(s) vinculado(s) a tu cuenta.
        @else
            No tienes alumnos vinculados todavía.
        @endif
    </div>

    @forelse ($alumnos as $alumno)
        @php
            $inscripcion = $alumno->inscripciones->where('activo', true)->first();
            $grupo       = $inscripcion?->grupo;
            $esActivo    = $alumno->estado === 'activo';
        @endphp

        <div class="hijo-card">

            {{-- ── Cabecera con nombre ── --}}
            <div class="hijo-card-header">
                <div class="hijo-avatar">
                    @if ($alumno->foto_url)
                        <img src="{{ asset('storage/' . $alumno->foto_url) }}"
                             alt="{{ $alumno->nombre_completo }}">
                    @else
                        <div class="hijo-avatar-placeholder">
                            <i class="fa fa-user"></i>
                        </div>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="hijo-nombre">{{ $alumno->nombre_completo }}</div>
                    <div class="hijo-matricula">Matrícula: {{ $alumno->matricula }}</div>
                </div>
                <span class="hijo-status {{ $esActivo ? 'hijo-status-activo' : 'hijo-status-otro' }}">
                    {{ $esActivo ? 'Activo' : ucfirst(str_replace('_', ' ', $alumno->estado)) }}
                </span>
            </div>

            {{-- ── Grupo inscrito ── --}}
            @if ($grupo)
                <div class="hijo-grupo">
                    <i class="fa fa-graduation-cap"></i>
                    <span>
                        {{ $grupo->grado->nivel->nombre ?? '' }}
                        {{ $grupo->grado->nombre ?? '' }}
                        {{ $grupo->nombre }}
                    </span>
                    <span class="hijo-grupo-ciclo">{{ $inscripcion->ciclo->nombre ?? '' }}</span>
                </div>
            @else
                <div class="hijo-sin-grupo">
                    <i class="fa fa-info-circle"></i>
                    Sin grupo activo registrado.
                </div>
            @endif

            {{-- ── Datos del alumno ── --}}
            <div class="hijo-datos">
                <div class="hijo-dato">
                    <div class="hijo-dato-label">Fecha de nacimiento</div>
                    <div class="hijo-dato-valor">
                        {{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                    </div>
                </div>
                <div class="hijo-dato">
                    <div class="hijo-dato-label">Género</div>
                    <div class="hijo-dato-valor">{{ $alumno->genero ?: '—' }}</div>
                </div>
                <div class="hijo-dato">
                    <div class="hijo-dato-label">CURP</div>
                    <div class="hijo-dato-valor" style="font-size:12px;word-break:break-all;">
                        {{ $alumno->curp ?: 'No registrada' }}
                    </div>
                </div>
                <div class="hijo-dato">
                    <div class="hijo-dato-label">Estado</div>
                    <div class="hijo-dato-valor">
                        {{ ucfirst(str_replace('_', ' ', $alumno->estado)) }}
                    </div>
                </div>
            </div>

            {{-- ── Botones de acción ── --}}
            <div class="hijo-acciones">
                <a href="{{ route('portal.estado-cuenta', $alumno->id) }}"
                   class="hijo-accion hijo-accion-azul">
                    <div class="hijo-accion-icon">
                        <i class="fa fa-file-text-o"></i>
                    </div>
                    Estado de cuenta
                </a>
                <a href="{{ route('portal.historial-pagos', $alumno->id) }}"
                   class="hijo-accion hijo-accion-verde">
                    <div class="hijo-accion-icon">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    Historial de pagos
                </a>
                <a href="{{ route('portal.hijos.expediente', $alumno->id) }}"
                   class="hijo-accion hijo-accion-rojo">
                    <div class="hijo-accion-icon">
                        <i class="fa fa-heartbeat"></i>
                    </div>
                    Expediente médico
                </a>
            </div>

        </div>

    @empty
        <div class="hijo-empty">
            <div class="hijo-empty-icon"><i class="fa fa-users"></i></div>
            <div class="hijo-empty-text">No hay alumnos vinculados a tu familia.</div>
            <div style="margin-top:8px;font-size:13px;">
                Contacta a la escuela para que vinculen a tus hijos.
            </div>
        </div>
    @endforelse

@endsection
