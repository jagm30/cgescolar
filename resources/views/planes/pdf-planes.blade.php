<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planes de Pago</title>
    <style>
        @page {
            size: letter portrait;
            margin: 22px 28px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }

        /* ── ENCABEZADO ── */
        .header {
            border-bottom: 3px solid #3c8dbc;
            padding-bottom: 10px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .header-title {
            font-size: 18px;
            font-weight: 800;
            color: #1a2634;
            line-height: 1.2;
        }

        .header-sub {
            font-size: 11px;
            color: #7a8898;
            margin-top: 3px;
        }

        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #94a3b8;
        }

        /* ── CARD DE PLAN ── */
        .plan-card {
            border: 1px solid #d0dbe6;
            border-radius: 6px;
            margin-bottom: 16px;
            page-break-inside: avoid;
            overflow: hidden;
        }

        .plan-header {
            background-color: #3c8dbc;
            color: #ffffff;
            padding: 8px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .plan-nombre {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .plan-status {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
        }

        .status-activo   { background: #27ae60; color: #fff; }
        .status-inactivo { background: #95a5a6; color: #fff; }

        /* ── METADATA ── */
        .plan-meta {
            background: #f4f7f9;
            padding: 7px 14px;
            display: flex;
            gap: 24px;
            border-bottom: 1px solid #e0e7ef;
        }

        .meta-item { font-size: 10px; color: #64748b; }
        .meta-label { font-weight: 700; color: #475569; display: block; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; }

        /* ── TABLA CONCEPTOS ── */
        .conceptos-wrap { padding: 10px 14px; }

        .conceptos-title {
            font-size: 10px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .conceptos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .conceptos-table thead th {
            background: #eaf3fb;
            color: #2c6fad;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 5px 10px;
            border-bottom: 1px solid #b3d4f5;
            text-align: left;
        }

        .conceptos-table thead th.text-right { text-align: right; }

        .conceptos-table tbody td {
            padding: 5px 10px;
            border-bottom: 1px solid #f0f3f7;
            font-size: 11px;
            color: #2c3e50;
        }

        .conceptos-table tbody tr:last-child td { border-bottom: none; }

        .conceptos-table tfoot td {
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 800;
            border-top: 2px solid #3c8dbc;
            background: #f0f7ff;
            color: #1a4f7a;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── POLÍTICAS ── */
        .politicas-wrap {
            padding: 6px 14px 10px;
            display: flex;
            gap: 20px;
            border-top: 1px dashed #d0dbe6;
        }

        .politica-box {
            flex: 1;
            background: #fffbeb;
            border: 1px solid #fcd97d;
            border-radius: 4px;
            padding: 6px 10px;
        }

        .politica-box.recargo {
            background: #fff1f2;
            border-color: #fca5a5;
        }

        .politica-box-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #92400e;
            margin-bottom: 4px;
        }

        .politica-box.recargo .politica-box-title { color: #991b1b; }

        .politica-row {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }

        /* ── SIN PLANES ── */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: 13px;
        }

        /* ── FOOTER ── */
        .pdf-footer {
            margin-top: 24px;
            border-top: 1px solid #e0e7ef;
            padding-top: 8px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="header">
        <div>
            <div class="header-title">Planes de Pago</div>
            <div class="header-sub">
                Ciclo escolar: <strong>{{ $cicloActual->nombre ?? '—' }}</strong>
                &nbsp;·&nbsp; {{ $planes->count() }} plan{{ $planes->count() !== 1 ? 'es' : '' }} exportado{{ $planes->count() !== 1 ? 's' : '' }}
            </div>
        </div>
        <div class="header-meta">
            Generado el {{ now()->format('d/m/Y H:i') }} hrs
        </div>
    </div>

    @forelse ($planes as $plan)
        @php
            $total = $plan->planPagoConceptos->sum('monto');
            $tieneDescuentos = $plan->politicasDescuentoActivas->isNotEmpty();
            $tieneRecargo    = $plan->politicaRecargoActiva !== null;
        @endphp

        <div class="plan-card">

            {{-- CABECERA DEL PLAN --}}
            <div class="plan-header">
                <span class="plan-nombre">{{ $plan->nombre }}</span>
                <span class="plan-status {{ $plan->activo ? 'status-activo' : 'status-inactivo' }}">
                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>

            {{-- METADATA --}}
            <div class="plan-meta">
                <div class="meta-item">
                    <span class="meta-label">Nivel</span>
                    {{ $plan->nivel->nombre ?? 'Todas las secciones' }}
                </div>
                <div class="meta-item">
                    <span class="meta-label">Periodicidad</span>
                    {{ ucfirst($plan->periodicidad) }}
                </div>
                <div class="meta-item">
                    <span class="meta-label">Vigencia</span>
                    {{ $plan->fecha_inicio->format('d/m/Y') }} — {{ $plan->fecha_fin->format('d/m/Y') }}
                </div>
                <div class="meta-item">
                    <span class="meta-label">Conceptos</span>
                    {{ $plan->planPagoConceptos->count() }}
                </div>
            </div>

            {{-- CONCEPTOS --}}
            <div class="conceptos-wrap">
                <div class="conceptos-title">Conceptos incluidos</div>

                @if ($plan->planPagoConceptos->isEmpty())
                    <p style="color:#94a3b8; font-size:11px; margin:0;">Sin conceptos asignados.</p>
                @else
                    <table class="conceptos-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Concepto</th>
                                <th class="text-right">Monto por periodo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($plan->planPagoConceptos as $i => $pc)
                                <tr>
                                    <td style="color:#94a3b8; width:24px;">{{ $i + 1 }}</td>
                                    <td>{{ $pc->concepto->nombre ?? '—' }}</td>
                                    <td class="text-right">${{ number_format($pc->monto, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total por periodo</td>
                                <td class="text-right">${{ number_format($total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

            {{-- POLÍTICAS (solo si existen) --}}
            @if ($tieneDescuentos || $tieneRecargo)
                <div class="politicas-wrap">
                    @if ($tieneDescuentos)
                        <div class="politica-box">
                            <div class="politica-box-title">Descuentos por pronto pago</div>
                            @foreach ($plan->politicasDescuentoActivas as $desc)
                                <div class="politica-row">
                                    <strong>{{ $desc->nombre }}</strong>:
                                    {{ $desc->tipo_valor === 'porcentaje' ? $desc->valor . '%' : '$' . number_format($desc->valor, 2) }}
                                    @if ($desc->dia_limite)
                                        — antes del día {{ $desc->dia_limite }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($tieneRecargo)
                        @php $rec = $plan->politicaRecargoActiva; @endphp
                        <div class="politica-box recargo">
                            <div class="politica-box-title">Recargo por mora</div>
                            <div class="politica-row">
                                A partir del día <strong>{{ $rec->dia_limite_pago }}</strong>:
                                {{ $rec->tipo_recargo === 'porcentaje' ? $rec->valor . '%' : '$' . number_format($rec->valor, 2) }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    @empty
        <div class="empty-state">No hay planes registrados para este ciclo.</div>
    @endforelse

    <div class="pdf-footer">
        Reporte generado automáticamente por CGEscolar &nbsp;·&nbsp; {{ now()->format('d/m/Y') }}
    </div>

</body>
</html>
