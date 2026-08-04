@extends('layouts.master')

@section('page_title', 'Estado de cuenta')
@section('page_subtitle', $alumno->nombre_completo)

@section('breadcrumb')
    <li><a href="{{ route('portal.dashboard') }}">Portal</a></li>
    <li><a href="{{ route('portal.hijos') }}">Mis hijos</a></li>
    <li class="active">Estado de cuenta</li>
@endsection

@push('styles')
    @include('portal._styles')
    <style>
        /* ── Cabecera ── */
        .ec-hero {
            background: linear-gradient(135deg, #1f4e78 0%, #3c8dbc 100%);
            border-radius: 12px;
            padding: 18px 16px;
            margin-bottom: 16px;
            color: #fff;
        }
        .ec-hero-nombre {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px;
            line-height: 1.2;
        }
        .ec-hero-sub {
            font-size: 13px;
            color: rgba(255,255,255,.78);
            margin: 0;
        }

        /* ── Tarjetas de resumen ── */
        .ec-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        .ec-stat {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .ec-stat-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9aa5b4;
            margin-bottom: 6px;
        }
        .ec-stat-value {
            font-size: 22px;
            font-weight: 800;
            line-height: 1;
            color: #1a2634;
        }
        .ec-stat-icon {
            float: right;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin-top: -2px;
        }

        /* ── Navegación ── */
        .ec-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .ec-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none !important;
            border: 1px solid #dde3ea;
            background: #fff;
            color: #4a5568;
            transition: background .15s;
        }
        .ec-nav-btn:hover { background: #f0f3f7; color: #1a2634; }
        .ec-nav-btn-primary {
            background: #3c8dbc;
            border-color: #3c8dbc;
            color: #fff !important;
        }
        .ec-nav-btn-primary:hover { background: #1f4e78; border-color: #1f4e78; color: #fff; }

        /* ── Título de sección ── */
        .ec-section-title {
            font-size: 16px;
            font-weight: 800;
            color: #1a2634;
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Tarjeta de cargo ── */
        .ec-cargo {
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            margin-bottom: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .ec-cargo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px 10px;
        }
        .ec-cargo-concepto {
            font-size: 16px;
            font-weight: 700;
            color: #1a2634;
            line-height: 1.2;
        }
        .ec-cargo-periodo {
            font-size: 12px;
            color: #9aa5b4;
            margin-top: 2px;
        }
        .ec-estado {
            flex-shrink: 0;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }
        .ec-estado-pagado    { background: #d1fae5; color: #065f46; }
        .ec-estado-condonado { background: #e0f2fe; color: #0369a1; }
        .ec-estado-vencido   { background: #fee2e2; color: #b91c1c; }
        .ec-estado-pendiente { background: #fef3c7; color: #92400e; }

        /* ── Barra de progreso ── */
        .ec-progress-wrap {
            padding: 0 16px 4px;
        }
        .ec-progress-bar-bg {
            background: #f0f3f7;
            border-radius: 99px;
            height: 8px;
            overflow: hidden;
        }
        .ec-progress-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width .4s ease;
        }

        /* ── Montos ── */
        .ec-cargo-montos {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-top: 1px solid #f0f3f7;
            margin-top: 10px;
        }
        .ec-monto {
            padding: 10px 14px;
            border-right: 1px solid #f0f3f7;
            text-align: center;
        }
        .ec-monto:last-child { border-right: none; }
        .ec-monto-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #9aa5b4;
            margin-bottom: 4px;
        }
        .ec-monto-valor {
            font-size: 15px;
            font-weight: 800;
            color: #1a2634;
        }

        /* ── Vencimiento ── */
        .ec-vencimiento {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-top: 1px solid #f0f3f7;
            font-size: 12px;
            color: #9aa5b4;
        }

        /* ── Vacío ── */
        .ec-empty {
            text-align: center;
            padding: 48px 20px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            color: #9aa5b4;
        }
        .ec-empty-icon { font-size: 48px; margin-bottom: 12px; color: #d1d9e0; }
        .ec-empty-text { font-size: 15px; }

        /* ── Alerta de cargos vencidos ── */
        .ec-alerta {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #b91c1c;
            font-weight: 600;
        }
        .ec-alerta i { font-size: 20px; flex-shrink: 0; }

        /* ── Filtro ── */
        .ec-filtro {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .ec-filtro-btn {
            flex: 1;
            min-width: 90px;
            padding: 10px 12px;
            border-radius: 10px;
            border: 2px solid #e4eaf0;
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            color: #7b8794;
            cursor: pointer;
            text-align: center;
            transition: all .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .ec-filtro-btn:hover { border-color: #b0bec5; color: #1a2634; }
        .ec-filtro-btn.activo-todos    { border-color: #3c8dbc; background: #3c8dbc; color: #fff; }
        .ec-filtro-btn.activo-pendiente { border-color: #f59e0b; background: #fef3c7; color: #92400e; }
        .ec-filtro-btn.activo-pagado   { border-color: #00875a; background: #d1fae5; color: #065f46; }

        .ec-filtro-count {
            background: rgba(0,0,0,.12);
            border-radius: 20px;
            padding: 1px 7px;
            font-size: 11px;
        }

        /* ── Vacío de filtro ── */
        .ec-filtro-empty {
            display: none;
            text-align: center;
            padding: 32px 20px;
            background: #fff;
            border: 1px solid #e4eaf0;
            border-radius: 12px;
            color: #9aa5b4;
            margin-bottom: 10px;
        }
        .ec-filtro-empty i { font-size: 36px; color: #d1d9e0; display: block; margin-bottom: 10px; }
        .ec-filtro-empty p { font-size: 14px; margin: 0; }

        /* ── Mes del cargo ── */
        .ec-mes-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            font-size: 14px;
            font-weight: 800;
            border-bottom: 1px solid rgba(0,0,0,.06);
            letter-spacing: .01em;
        }
        .ec-mes-badge i { font-size: 15px; flex-shrink: 0; }

        /* ── Desglose financiero ── */
        .ec-desglose {
            padding: 10px 16px;
            border-top: 1px solid #f0f3f7;
            background: #fafbfc;
        }
        .ec-desglose-titulo {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9aa5b4;
            margin-bottom: 8px;
        }
        .ec-desglose-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 4px 0;
            font-size: 13px;
        }
        .ec-desglose-label {
            color: #4a5568;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ec-desglose-label i { width: 14px; text-align: center; }
        .ec-desglose-valor   { font-weight: 700; color: #1a2634; }
        .ec-desglose-desc .ec-desglose-valor { color: #065f46; }
        .ec-desglose-rec  .ec-desglose-valor { color: #b91c1c; }
        .ec-desglose-sep  {
            border-top: 1px dashed #e4eaf0;
            margin: 6px 0;
        }
        .ec-desglose-neto .ec-desglose-label { font-weight: 700; color: #1a2634; }
        .ec-desglose-neto .ec-desglose-valor { font-size: 14px; color: #1a2634; }

        @media (max-width: 400px) {
            .ec-stat-value { font-size: 18px; }
            .ec-cargo-concepto { font-size: 15px; }
            .ec-monto-valor { font-size: 14px; }
            .ec-filtro-btn { font-size: 12px; padding: 9px 8px; }
        }
    </style>
@endpush

@section('content')

    {{-- ── Cabecera ── --}}
    <div class="ec-hero">
        <div class="ec-hero-nombre">{{ $alumno->nombre_completo }}</div>
        <div class="ec-hero-sub">
            Matrícula {{ $alumno->matricula }}
            @if ($inscripcion->grupo)
                &nbsp;·&nbsp;
                {{ $inscripcion->grupo->grado->nivel->nombre ?? '' }}
                {{ $inscripcion->grupo->grado->nombre ?? '' }}
                {{ $inscripcion->grupo->nombre }}
            @endif
            @if ($inscripcion->ciclo)
                &nbsp;·&nbsp; {{ $inscripcion->ciclo->nombre }}
            @endif
        </div>
    </div>

    {{-- ── Alerta si hay vencidos ── --}}
    @if ($resumen['cargos_vencidos'] > 0)
        <div class="ec-alerta">
            <i class="fa fa-exclamation-circle"></i>
            Tienes {{ $resumen['cargos_vencidos'] }} cargo(s) vencido(s).
            Comunícate con la escuela para regularizar tu situación.
        </div>
    @endif

    {{-- ── Resumen en 4 tarjetas ── --}}
    <div class="ec-stats">
        <div class="ec-stat">
            <div class="ec-stat-icon" style="background:#dbeafe;color:#1d4ed8;float:right;">
                <i class="fa fa-list-alt"></i>
            </div>
            <div class="ec-stat-label">Total a pagar</div>
            <div class="ec-stat-value">${{ number_format($resumen['total_cargado'], 2) }}</div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-icon" style="background:#d1fae5;color:#065f46;float:right;">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="ec-stat-label">Total cobrado</div>
            <div class="ec-stat-value" style="color:#065f46;">${{ number_format($resumen['total_pagado'], 2) }}</div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-icon" style="background:{{ $resumen['total_pendiente'] > 0 ? '#fee2e2' : '#d1fae5' }};color:{{ $resumen['total_pendiente'] > 0 ? '#b91c1c' : '#065f46' }};float:right;">
                <i class="fa fa-clock-o"></i>
            </div>
            <div class="ec-stat-label">Por pagar</div>
            <div class="ec-stat-value" style="color:{{ $resumen['total_pendiente'] > 0 ? '#b91c1c' : '#065f46' }};">
                ${{ number_format($resumen['total_pendiente'], 2) }}
            </div>
        </div>
        <div class="ec-stat">
            <div class="ec-stat-icon" style="background:{{ $resumen['cargos_vencidos'] > 0 ? '#fee2e2' : '#d1fae5' }};color:{{ $resumen['cargos_vencidos'] > 0 ? '#b91c1c' : '#065f46' }};float:right;">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <div class="ec-stat-label">Vencidos</div>
            <div class="ec-stat-value" style="color:{{ $resumen['cargos_vencidos'] > 0 ? '#b91c1c' : '#065f46' }};">
                {{ $resumen['cargos_vencidos'] }}
            </div>
        </div>
    </div>

    {{-- ── Navegación ── --}}
    <div class="ec-nav">
        <a href="{{ route('portal.hijos') }}" class="ec-nav-btn">
            <i class="fa fa-arrow-left"></i> Regresar
        </a>
        <a href="{{ route('portal.historial-pagos', $alumno->id) }}" class="ec-nav-btn ec-nav-btn-primary">
            <i class="fa fa-credit-card"></i> Ver historial de pagos
        </a>
    </div>

    {{-- ── Lista de cargos ── --}}
    @php
        $totalTodos     = count($cargos);
        $totalPendiente = collect($cargos)->filter(fn($c) => !in_array($c['estado'], ['pagado','condonado']))->count();
        $totalPagado    = collect($cargos)->filter(fn($c) =>  in_array($c['estado'], ['pagado','condonado']))->count();
    @endphp

    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
        <div class="ec-section-title" style="margin:0;">
            <i class="fa fa-file-text-o" style="color:#3c8dbc;"></i>
            Cargos del ciclo activo
        </div>
        <span style="font-size:12px;color:#9aa5b4;">{{ $totalTodos }} concepto(s)</span>
    </div>

    {{-- Filtro --}}
    <div class="ec-filtro">
        <button type="button" class="ec-filtro-btn activo-todos" onclick="filtrarCargos('todos', this)">
            <i class="fa fa-th-list"></i> Todos
            <span class="ec-filtro-count">{{ $totalTodos }}</span>
        </button>
        <button type="button" class="ec-filtro-btn" onclick="filtrarCargos('pendiente', this)">
            <i class="fa fa-clock-o"></i> Pendientes
            <span class="ec-filtro-count">{{ $totalPendiente }}</span>
        </button>
        <button type="button" class="ec-filtro-btn" onclick="filtrarCargos('pagado', this)">
            <i class="fa fa-check-circle"></i> Pagados
            <span class="ec-filtro-count">{{ $totalPagado }}</span>
        </button>
    </div>

    {{-- Mensaje vacío al filtrar --}}
    <div class="ec-filtro-empty" id="ec-filtro-empty">
        <i class="fa fa-filter"></i>
        <p id="ec-filtro-empty-msg">No hay cargos en esta categoría.</p>
    </div>

    @forelse ($cargos as $cargo)
        @php
            $estado = $cargo['estado'];
            $estadoLabel = match ($estado) {
                'pagado'         => 'Pagado',
                'condonado'      => 'Condonado',
                'vencido'        => 'Vencido',
                'parcial_vencido'=> 'Vencido parcial',
                'parcial'        => 'Pago parcial',
                default          => 'Pendiente',
            };
            $estadoClass = match ($estado) {
                'pagado', 'condonado'          => 'ec-estado-pagado',
                'vencido', 'parcial_vencido'   => 'ec-estado-vencido',
                default                        => 'ec-estado-pendiente',
            };
            $porcentajePagado = $cargo['monto_neto'] > 0
                ? min(100, round(($cargo['monto_cobrado'] / $cargo['monto_neto']) * 100))
                : 100;
            $barColor = match ($estado) {
                'pagado', 'condonado'        => '#00875a',
                'vencido', 'parcial_vencido' => '#b91c1c',
                default                      => '#f59e0b',
            };
            $esPagado    = in_array($estado, ['pagado', 'condonado']);
            $grupoCargo  = $esPagado ? 'pagado' : 'pendiente';

            // Mes resaltado: color según estado
            $mesColor = match (true) {
                in_array($estado, ['pagado', 'condonado'])        => ['bg' => '#d1fae5', 'color' => '#065f46'],
                in_array($estado, ['vencido', 'parcial_vencido']) => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
                default                                           => ['bg' => '#fef3c7', 'color' => '#92400e'],
            };

            // Desglose financiero
            $tieneDesglose = ($cargo['descuento_beca'] > 0)
                          || ($cargo['descuento_pronto_pago'] > 0)
                          || ($cargo['descuento_otros'] > 0)
                          || ($cargo['condonacion'] > 0)
                          || ($cargo['recargo_aplicado'] > 0);
            $montoNeto = $cargo['monto_neto'];
        @endphp

        <div class="ec-cargo" data-grupo="{{ $grupoCargo }}">

            {{-- Mes destacado --}}
            @if ($cargo['periodo_label'])
                <div class="ec-mes-badge"
                     style="background:{{ $mesColor['bg'] }};color:{{ $mesColor['color'] }};">
                    <i class="fa fa-calendar"></i>
                    {{ $cargo['periodo_label'] }}
                </div>
            @endif

            {{-- Concepto y estado --}}
            <div class="ec-cargo-header">
                <div>
                    <div class="ec-cargo-concepto">{{ $cargo['concepto'] }}</div>
                </div>
                <span class="ec-estado {{ $estadoClass }}">{{ $estadoLabel }}</span>
            </div>

            {{-- Barra de progreso --}}
            <div class="ec-progress-wrap">
                <div class="ec-progress-bar-bg">
                    <div class="ec-progress-bar-fill"
                         style="width:{{ $porcentajePagado }}%;background:{{ $barColor }};"></div>
                </div>
            </div>

            {{-- Desglose financiero (solo si hay ajustes) --}}
            @if ($tieneDesglose)
                <div class="ec-desglose">
                    <div class="ec-desglose-titulo">
                        <i class="fa fa-tags" style="margin-right:4px;"></i>Ajustes aplicados
                    </div>

                    {{-- Cargo original --}}
                    <div class="ec-desglose-row">
                        <span class="ec-desglose-label">
                            <i class="fa fa-file-text-o"></i> Cargo original
                        </span>
                        <span class="ec-desglose-valor">${{ number_format($cargo['monto_original'], 2) }}</span>
                    </div>

                    @if ($cargo['descuento_beca'] > 0)
                        <div class="ec-desglose-row ec-desglose-desc">
                            <span class="ec-desglose-label">
                                <i class="fa fa-graduation-cap"></i> Descuento beca
                            </span>
                            <span class="ec-desglose-valor">-${{ number_format($cargo['descuento_beca'], 2) }}</span>
                        </div>
                    @endif

                    @if ($cargo['descuento_pronto_pago'] > 0)
                        <div class="ec-desglose-row ec-desglose-desc">
                            <span class="ec-desglose-label">
                                <i class="fa fa-bolt"></i> Pronto pago
                            </span>
                            <span class="ec-desglose-valor">-${{ number_format($cargo['descuento_pronto_pago'], 2) }}</span>
                        </div>
                    @endif

                    @if ($cargo['descuento_otros'] > 0)
                        <div class="ec-desglose-row ec-desglose-desc">
                            <span class="ec-desglose-label">
                                <i class="fa fa-percent"></i> Otros descuentos
                            </span>
                            <span class="ec-desglose-valor">-${{ number_format($cargo['descuento_otros'], 2) }}</span>
                        </div>
                    @endif

                    @if ($cargo['condonacion'] > 0)
                        <div class="ec-desglose-row ec-desglose-desc">
                            <span class="ec-desglose-label">
                                <i class="fa fa-gift"></i> Condonación
                            </span>
                            <span class="ec-desglose-valor">-${{ number_format($cargo['condonacion'], 2) }}</span>
                        </div>
                    @endif

                    @if ($cargo['recargo_aplicado'] > 0)
                        <div class="ec-desglose-row ec-desglose-rec">
                            <span class="ec-desglose-label">
                                <i class="fa fa-plus-circle"></i> Recargo
                            </span>
                            <span class="ec-desglose-valor">+${{ number_format($cargo['recargo_aplicado'], 2) }}</span>
                        </div>
                    @endif

                    <div class="ec-desglose-sep"></div>

                    <div class="ec-desglose-row ec-desglose-neto">
                        <span class="ec-desglose-label">
                            <i class="fa fa-equals"></i> Total a pagar
                        </span>
                        <span class="ec-desglose-valor">${{ number_format($montoNeto, 2) }}</span>
                    </div>
                </div>
            @endif

            {{-- Montos --}}
            <div class="ec-cargo-montos">
                <div class="ec-monto">
                    <div class="ec-monto-label">A pagar</div>
                    <div class="ec-monto-valor">${{ number_format($cargo['monto_neto'], 2) }}</div>
                </div>
                <div class="ec-monto">
                    <div class="ec-monto-label">Cobrado</div>
                    <div class="ec-monto-valor" style="color:#00875a;">${{ number_format($cargo['monto_cobrado'], 2) }}</div>
                </div>
                <div class="ec-monto">
                    <div class="ec-monto-label">Por pagar</div>
                    <div class="ec-monto-valor" style="color:{{ $cargo['saldo_pendiente'] > 0 ? '#b91c1c' : '#00875a' }};">
                        ${{ number_format($cargo['saldo_pendiente'], 2) }}
                    </div>
                </div>
            </div>

            {{-- Fecha de vencimiento --}}
            @if ($cargo['fecha_vencimiento'])
                <div class="ec-vencimiento">
                    <i class="fa fa-calendar{{ $esPagado ? '-check-o' : '-times-o' }}"
                       style="color:{{ $esPagado ? '#00875a' : ($cargo['saldo_pendiente'] > 0 ? '#b91c1c' : '#9aa5b4') }};"></i>
                    @if ($esPagado)
                        Vencimiento: {{ $cargo['fecha_vencimiento']->format('d/m/Y') }} — <strong style="color:#00875a;">Liquidado</strong>
                    @else
                        Vence el <strong>{{ $cargo['fecha_vencimiento']->format('d \d\e F \d\e Y') }}</strong>
                    @endif
                </div>
            @endif

        </div>
    @empty
        <div class="ec-empty">
            <div class="ec-empty-icon"><i class="fa fa-inbox"></i></div>
            <div class="ec-empty-text">No hay cargos registrados para el ciclo activo.</div>
        </div>
    @endforelse

    {{-- ── Botón regresar al final ── --}}
    <div style="margin: 6px 0 20px;">
        <a href="{{ route('portal.hijos') }}" class="ec-nav-btn">
            <i class="fa fa-arrow-left"></i> Regresar a Mis hijos
        </a>
    </div>

@endsection

@push('scripts')
<script>
function filtrarCargos(grupo, btnEl) {
    // Actualizar botones activos
    document.querySelectorAll('.ec-filtro-btn').forEach(function(btn) {
        btn.classList.remove('activo-todos', 'activo-pendiente', 'activo-pagado');
    });
    if (grupo === 'todos')     btnEl.classList.add('activo-todos');
    if (grupo === 'pendiente') btnEl.classList.add('activo-pendiente');
    if (grupo === 'pagado')    btnEl.classList.add('activo-pagado');

    // Mostrar / ocultar tarjetas
    var tarjetas = document.querySelectorAll('.ec-cargo');
    var visibles = 0;
    tarjetas.forEach(function(card) {
        var mostrar = grupo === 'todos' || card.dataset.grupo === grupo;
        card.style.display = mostrar ? '' : 'none';
        if (mostrar) visibles++;
    });

    // Mensaje si no hay resultados en esa categoría
    var empty = document.getElementById('ec-filtro-empty');
    var msg   = document.getElementById('ec-filtro-empty-msg');
    if (visibles === 0) {
        msg.textContent = grupo === 'pagado'
            ? 'No hay cargos pagados todavía.'
            : 'No hay cargos pendientes. ¡Todo al corriente!';
        empty.style.display = 'block';
    } else {
        empty.style.display = 'none';
    }
}
</script>
@endpush
