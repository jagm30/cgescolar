@extends('layouts.master')

@section('page_title', 'Portal de padres')
@section('page_subtitle', 'Resumen familiar')

@section('breadcrumb')
    <li class="active">Portal</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Bienvenida ── */
        .db-hero {
            background: linear-gradient(135deg, #1f4e78 0%, #3c8dbc 100%);
            border-radius: 14px;
            padding: 22px 18px;
            margin-bottom: 16px;
            color: #fff;
        }
        .db-hero-saludo {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,.7);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 4px;
        }
        .db-hero-nombre {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 6px;
            line-height: 1.2;
        }
        .db-hero-sub {
            font-size: 13px;
            color: rgba(255,255,255,.72);
            margin: 0;
        }

        /* ── Alerta vencidos ── */
        .db-alerta {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #b91c1c;
        }
        .db-alerta i { font-size: 22px; flex-shrink: 0; }

        /* ── Resumen financiero ── */
        .db-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        .db-stat {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
            position: relative;
            overflow: hidden;
        }
        .db-stat-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9aa5b4;
            margin-bottom: 6px;
        }
        .db-stat-value {
            font-size: 21px;
            font-weight: 800;
            color: #1a2634;
            line-height: 1;
        }
        .db-stat-icon {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
        }

        /* ── Sección titulo ── */
        .db-section-title {
            font-size: 17px;
            font-weight: 800;
            color: #1a2634;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .db-section-link {
            font-size: 13px;
            font-weight: 600;
            color: #3c8dbc;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .db-section-link:hover { color: #1f4e78; }

        /* ── Tarjeta de hijo (compacta) ── */
        .db-hijo {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .db-hijo-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px 12px;
        }
        .db-hijo-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #e8f0fb;
            color: #3c8dbc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .db-hijo-nombre {
            font-size: 16px;
            font-weight: 700;
            color: #1a2634;
            margin: 0 0 3px;
            line-height: 1.2;
        }
        .db-hijo-grupo {
            font-size: 12px;
            color: #7b8794;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .db-hijo-acciones {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 1px solid #f0f3f7;
        }
        .db-hijo-accion {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 6px;
            border-right: 1px solid #f0f3f7;
            text-decoration: none !important;
            font-size: 11px;
            font-weight: 700;
            color: #4a5568;
            transition: background .15s;
            text-align: center;
            line-height: 1.3;
        }
        .db-hijo-accion:last-child { border-right: none; }
        .db-hijo-accion:hover { background: #f8fafc; }
        .db-hijo-accion-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }
        .db-accion-azul  .db-hijo-accion-icon { background: #dbeafe; color: #1d4ed8; }
        .db-accion-verde .db-hijo-accion-icon { background: #d1fae5; color: #065f46; }
        .db-accion-rojo  .db-hijo-accion-icon { background: #fdecea; color: #b91c1c; }
        .db-accion-azul  { color: #1d4ed8; }
        .db-accion-verde { color: #065f46; }
        .db-accion-rojo  { color: #b91c1c; }

        /* ── Sin hijos ── */
        .db-empty {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            text-align: center;
            padding: 40px 20px;
            color: #9aa5b4;
            margin-bottom: 16px;
        }
        .db-empty i { font-size: 48px; color: #d1d9e0; display: block; margin-bottom: 12px; }
        .db-empty p { font-size: 15px; margin: 0; }

        /* ── Accesos rápidos ── */
        .db-accesos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .db-acceso {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            padding: 16px 14px;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-align: center;
            transition: box-shadow .15s, background .15s;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .db-acceso:hover { background: #f8fafc; box-shadow: 0 3px 10px rgba(0,0,0,.08); }
        .db-acceso-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .db-acceso-titulo {
            font-size: 14px;
            font-weight: 700;
            color: #1a2634;
        }
        .db-acceso-desc {
            font-size: 12px;
            color: #9aa5b4;
            line-height: 1.3;
        }

        @media (max-width: 420px) {
            .db-hero-nombre { font-size: 20px; }
            .db-stat-value  { font-size: 18px; }
        }
    </style>
@endpush

@section('content')

    {{-- ── Bienvenida ── --}}
    <div class="db-hero">
        <div class="db-hero-saludo">Bienvenido(a)</div>
        <div class="db-hero-nombre">{{ auth()->user()->nombre }}</div>
        <div class="db-hero-sub">
            Aquí puedes consultar la información escolar y financiera de tus hijos.
        </div>
    </div>

    {{-- ── Alerta si hay cargos vencidos ── --}}
    @if ($resumen['cargos_vencidos'] > 0)
        <div class="db-alerta">
            <i class="fa fa-exclamation-circle"></i>
            <div>
                Tienes <strong>{{ $resumen['cargos_vencidos'] }} cargo(s) vencido(s)</strong>.
                Revisa el estado de cuenta de tus hijos para más detalles.
            </div>
        </div>
    @endif

    {{-- ── Resumen financiero ── --}}
    <div class="db-stats">
        <div class="db-stat">
            <div class="db-stat-icon" style="background:#e8f0fb;color:#3c8dbc;">
                <i class="fa fa-child"></i>
            </div>
            <div class="db-stat-label">Mis hijos</div>
            <div class="db-stat-value">{{ $resumen['hijos'] }}</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-icon" style="background:#e8f8f0;color:#00875a;">
                <i class="fa fa-graduation-cap"></i>
            </div>
            <div class="db-stat-label">Inscritos</div>
            <div class="db-stat-value">{{ $resumen['inscritos'] }}</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-icon" style="background:#d1fae5;color:#065f46;">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="db-stat-label">Total cobrado</div>
            <div class="db-stat-value" style="color:#065f46;font-size:17px;">
                ${{ number_format($resumen['total_pagado'], 2) }}
            </div>
        </div>
        <div class="db-stat">
            <div class="db-stat-icon"
                 style="background:{{ $resumen['total_pendiente'] > 0 ? '#fee2e2' : '#d1fae5' }};
                        color:{{ $resumen['total_pendiente'] > 0 ? '#b91c1c' : '#065f46' }};">
                <i class="fa fa-clock-o"></i>
            </div>
            <div class="db-stat-label">Por pagar</div>
            <div class="db-stat-value"
                 style="color:{{ $resumen['total_pendiente'] > 0 ? '#b91c1c' : '#065f46' }};font-size:17px;">
                ${{ number_format($resumen['total_pendiente'], 2) }}
            </div>
        </div>
    </div>

    {{-- ── Mis hijos ── --}}
    <div class="db-section-title">
        <span>Mis hijos</span>
        @if ($alumnos->count() > 0)
            <a href="{{ route('portal.hijos') }}" class="db-section-link">
                Ver todos <i class="fa fa-chevron-right"></i>
            </a>
        @endif
    </div>

    @forelse ($alumnos as $alumno)
        @php
            $inscripcion = $alumno->inscripciones->where('activo', true)->first();
            $grupo       = $inscripcion?->grupo;
        @endphp
        <div class="db-hijo">
            <div class="db-hijo-header">
                <div class="db-hijo-avatar">
                    <i class="fa fa-user"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="db-hijo-nombre">{{ $alumno->nombre_completo }}</div>
                    <div class="db-hijo-grupo">
                        <i class="fa fa-graduation-cap"></i>
                        @if ($grupo)
                            {{ $grupo->grado->nivel->nombre ?? '' }}
                            {{ $grupo->grado->nombre ?? '' }}
                            {{ $grupo->nombre }}
                        @else
                            Sin grupo activo
                        @endif
                    </div>
                </div>
            </div>
            <div class="db-hijo-acciones">
                <a href="{{ route('portal.estado-cuenta', $alumno->id) }}"
                   class="db-hijo-accion db-accion-azul">
                    <div class="db-hijo-accion-icon"><i class="fa fa-file-text-o"></i></div>
                    Estado de cuenta
                </a>
                <a href="{{ route('portal.historial-pagos', $alumno->id) }}"
                   class="db-hijo-accion db-accion-verde">
                    <div class="db-hijo-accion-icon"><i class="fa fa-credit-card"></i></div>
                    Historial de pagos
                </a>
                <a href="{{ route('portal.hijos.expediente', $alumno->id) }}"
                   class="db-hijo-accion db-accion-rojo">
                    <div class="db-hijo-accion-icon"><i class="fa fa-heartbeat"></i></div>
                    Expediente médico
                </a>
            </div>
        </div>
    @empty
        <div class="db-empty">
            <i class="fa fa-users"></i>
            <p>No hay alumnos vinculados a tu cuenta.<br>Contacta a la escuela para vincularlos.</p>
        </div>
    @endforelse

    {{-- ── Accesos rápidos ── --}}
    <div class="db-section-title" style="margin-top:6px;">
        <span>Otros accesos</span>
    </div>

    <div class="db-accesos">
        <a href="{{ route('portal.fotos') }}" class="db-acceso">
            <div class="db-acceso-icon" style="background:#fef3c7;color:#d97706;">
                <i class="fa fa-camera"></i>
            </div>
            <div>
                <div class="db-acceso-titulo">Fotografías</div>
                <div class="db-acceso-desc">Sube fotos de tus hijos y contactos</div>
            </div>
        </a>
        <a href="{{ route('portal.razones-sociales') }}" class="db-acceso">
            <div class="db-acceso-icon" style="background:#ede9fe;color:#7c3aed;">
                <i class="fa fa-building-o"></i>
            </div>
            <div>
                <div class="db-acceso-titulo">Datos fiscales</div>
                <div class="db-acceso-desc">Gestiona tu RFC para facturación</div>
            </div>
        </a>
        <a href="{{ route('portal.hijos') }}" class="db-acceso">
            <div class="db-acceso-icon" style="background:#dbeafe;color:#1d4ed8;">
                <i class="fa fa-id-card-o"></i>
            </div>
            <div>
                <div class="db-acceso-titulo">Mis hijos</div>
                <div class="db-acceso-desc">Datos y grupo de cada alumno</div>
            </div>
        </a>
        <a href="#" class="db-acceso"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <div class="db-acceso-icon" style="background:#fee2e2;color:#b91c1c;">
                <i class="fa fa-sign-out"></i>
            </div>
            <div>
                <div class="db-acceso-titulo">Cerrar sesión</div>
                <div class="db-acceso-desc">Salir del portal de padres</div>
            </div>
        </a>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>

    @if ($resumen['cargos_vencidos'] > 0)
        <div class="modal fade" id="modal-pagos-vencidos" tabindex="-1" role="dialog"
            aria-labelledby="modal-pagos-vencidos-titulo" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" style="background:#dc5450;color:#fff;border-radius:4px 4px 0 0;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="color:#fff;opacity:.9;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="modal-pagos-vencidos-titulo" style="font-weight:700;">
                            <i class="fa fa-bell"></i> Aviso de pagos vencidos
                        </h4>
                    </div>
                    <div class="modal-body" style="font-size:16px;line-height:1.6;">
                        <p>
                            Tienes <strong>{{ $resumen['cargos_vencidos'] }} pago(s) vencido(s)</strong>.
                        </p>
                        <p style="margin-bottom:0;">
                            El total pendiente es de <strong>${{ number_format($resumen['total_pendiente'], 2) }}</strong>.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Más tarde</button>
                        <a href="{{ route('portal.hijos') }}" class="btn btn-danger">
                            <i class="fa fa-file-text-o"></i> Ver estados de cuenta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@if ($resumen['cargos_vencidos'] > 0)
    @push('scripts')
        <script>
            $(function() {
                $('#modal-pagos-vencidos').modal('show');
            });
        </script>
    @endpush
@endif
