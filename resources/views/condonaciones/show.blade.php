@extends('layouts.master')

@section('page_title', 'Condonación #' . $condonacion->id)
@section('page_subtitle', 'Detalle de condonación')

@section('breadcrumb')
    <li><a href="{{ route('condonaciones.index') }}">Condonaciones</a></li>
    <li class="active">#{{ $condonacion->id }}</li>
@endsection

@push('styles')
    <style>
        .det-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 18px;
            align-items: start;
        }

        .det-panel {
            background: #fff;
            border: 1px solid #e0e7ef;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .det-panel-header {
            background: #f4f6f8;
            border-bottom: 2px solid #e0e7ef;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .det-panel-title {
            font-size: 12px;
            font-weight: 700;
            color: #6b7a8d;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0;
        }

        .det-panel-body { padding: 16px 18px; }

        .det-table { width:100%; border-collapse:separate; border-spacing:0; }
        .det-table thead th {
            background:#f4f6f8;
            color:#6b7a8d;
            font-size:11px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.05em;
            padding:8px 14px;
            border-bottom:2px solid #e0e6ed;
            border-top:none;
        }
        .det-table td { padding:10px 14px; font-size:13px; vertical-align:middle; border-bottom:1px solid #f0f3f7; }
        .det-table tbody tr:last-child td { border-bottom:none; }

        .badge-activa    { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; border-radius:12px; padding:3px 12px; font-size:12px; font-weight:700; }
        .badge-cancelada { background:#f4f6f8; color:#7a8898; border:1px solid #d0d9e2; border-radius:12px; padding:3px 12px; font-size:12px; font-weight:700; }

        .label-cancelado { background:#f4f6f8; color:#7a8898; border:1px solid #d0d9e2; }
        .label-condonado { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; }
        .label-pendiente { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .label-parcial   { background:#cce5ff; color:#004085; border:1px solid #b8daff; }

        .cargo-label {
            display:inline-block;
            font-size:10px;
            font-weight:700;
            padding:2px 8px;
            border-radius:10px;
            border:1px solid;
        }

        .meta-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f0f3f7; font-size:13px; }
        .meta-row:last-child { border-bottom:none; }
        .meta-label { color:#888; }
        .meta-value { font-weight:600; color:#333; }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:14px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-scissors text-olive"></i> Condonación #{{ $condonacion->id }}
            </h4>
            <span class="badge-{{ $condonacion->estado }}">
                <i class="fa fa-circle" style="font-size:7px;"></i>
                {{ ucfirst($condonacion->estado) }}
            </span>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('condonaciones.index') }}" class="btn btn-default btn-sm btn-flat" style="border-radius:20px;">
                <i class="fa fa-arrow-left"></i> Volver
            </a>
            @if ($condonacion->estado === 'activa')
                <form action="{{ route('condonaciones.destroy', $condonacion->id) }}" method="POST"
                      onsubmit="return confirm('¿Cancelar esta condonación? Se eliminarán los descuentos aplicados a los cargos.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm btn-flat" style="border-radius:20px;">
                        <i class="fa fa-ban"></i> Cancelar condonación
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="det-grid">

        {{-- ── Izquierda: cargos condonados ─────────────── --}}
        <div>
            <div class="det-panel">
                <div class="det-panel-header">
                    <i class="fa fa-list-alt text-blue"></i>
                    <h4 class="det-panel-title">Cargos incluidos ({{ $condonacion->detalles->count() }})</h4>
                </div>
                <div style="overflow-x:auto;">
                    <table class="det-table">
                        <thead>
                            <tr>
                                <th>Cargo / Periodo</th>
                                <th class="text-right">Monto original</th>
                                <th class="text-right">Monto condonado</th>
                                <th>Estado cargo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($condonacion->detalles as $detalle)
                                @php $cargo = $detalle->cargo; @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:700;">{{ $cargo?->etiqueta ?? 'Cargo eliminado' }}</div>
                                        @if($cargo?->periodo)
                                            <div style="font-size:11px;color:#aab;">{{ $cargo->periodo_label }}</div>
                                        @endif
                                    </td>
                                    <td class="text-right" style="color:#555;">
                                        ${{ number_format((float) ($cargo?->monto_original ?? 0), 2) }}
                                    </td>
                                    <td class="text-right" style="font-weight:700;color:#1a6b2e;">
                                        ${{ number_format((float) $detalle->monto_aplicado, 2) }}
                                    </td>
                                    <td>
                                        @if($cargo)
                                            @php
                                                $estadoMap = [
                                                    'pendiente' => 'pendiente',
                                                    'parcial'   => 'parcial',
                                                    'pagado'    => 'success',
                                                    'condonado' => 'condonado',
                                                ];
                                                $cls = $estadoMap[$cargo->estado] ?? 'default';
                                            @endphp
                                            <span class="cargo-label label-{{ $cargo->estado }}" style="border-color:currentColor;">
                                                {{ ucfirst($cargo->estado) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted" style="padding:30px;">
                                        Sin detalles registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($condonacion->detalles->isNotEmpty())
                            <tfoot>
                                <tr style="background:#f4f6f8;">
                                    <th colspan="2" class="text-right" style="padding:10px 14px;font-size:13px;">Total condonado</th>
                                    <th class="text-right" style="padding:10px 14px;font-size:15px;color:#1a6b2e;">
                                        ${{ number_format((float) $condonacion->monto_total, 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Derecha: datos generales ──────────────────── --}}
        <div>
            <div class="det-panel">
                <div class="det-panel-header">
                    <i class="fa fa-info-circle text-blue"></i>
                    <h4 class="det-panel-title">Información general</h4>
                </div>
                <div class="det-panel-body">
                    <div class="meta-row">
                        <span class="meta-label">Alumno</span>
                        <span class="meta-value">{{ $condonacion->alumno?->nombre_completo ?? '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Ciclo escolar</span>
                        <span class="meta-value">{{ $condonacion->ciclo?->nombre ?? '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Estado</span>
                        <span class="meta-value">
                            <span class="badge-{{ $condonacion->estado }}">{{ ucfirst($condonacion->estado) }}</span>
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Fecha de registro</span>
                        <span class="meta-value">{{ $condonacion->creado_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Registrado por</span>
                        <span class="meta-value">{{ $condonacion->creadoPor?->nombre ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="det-panel">
                <div class="det-panel-header">
                    <i class="fa fa-comment text-blue"></i>
                    <h4 class="det-panel-title">Motivo</h4>
                </div>
                <div class="det-panel-body">
                    <p style="font-size:14px;color:#444;margin:0;line-height:1.6;">
                        {{ $condonacion->motivo }}
                    </p>
                </div>
            </div>

            @if ($condonacion->estado === 'activa')
                <div class="det-panel" style="border-color:#f8d7da;">
                    <div class="det-panel-body" style="text-align:center;padding:16px;">
                        <p style="font-size:12px;color:#721c24;margin:0 0 10px;">
                            <i class="fa fa-exclamation-triangle"></i>
                            Cancelar revertirá todos los descuentos aplicados a los cargos.
                        </p>
                        <form action="{{ route('condonaciones.destroy', $condonacion->id) }}" method="POST"
                              onsubmit="return confirm('¿Confirmas la cancelación de esta condonación?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block btn-flat" style="border-radius:6px;">
                                <i class="fa fa-ban"></i> Cancelar condonación
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

    </div>

@endsection
