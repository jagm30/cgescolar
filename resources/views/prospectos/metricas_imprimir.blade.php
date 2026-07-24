<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Admisiones — {{ $ciclo?->nombre }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #222;
            background: #fff;
            padding: 24px 30px;
        }

        /* ── ENCABEZADO ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .report-header .school { font-size: 17px; font-weight: 700; color: #1e4d7b; text-transform: uppercase; }
        .report-header .sub    { font-size: 11px; color: #666; margin-top: 2px; }
        .report-header .meta   { text-align: right; font-size: 11px; color: #555; line-height: 1.6; }

        /* ── SECCIÓN ── */
        .section-title {
            background: #1e4d7b;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 18px 0 8px;
        }

        /* ── KPIs ── */
        .kpi-row { display: flex; gap: 12px; margin-bottom: 4px; }
        .kpi-box {
            flex: 1;
            border: 1px solid #d0dde8;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
        }
        .kpi-box .kpi-num  { font-size: 26px; font-weight: 700; color: #1e4d7b; line-height: 1; }
        .kpi-box .kpi-label{ font-size: 10px; color: #666; margin-top: 3px; text-transform: uppercase; letter-spacing: .4px; }

        /* ── GRÁFICAS ── */
        .charts-row { display: flex; gap: 16px; margin-bottom: 4px; }
        .chart-wrap { flex: 1; text-align: center; }
        .chart-wrap canvas { max-width: 100%; }

        /* ── TABLAS ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 11px;
        }
        table th {
            background: #f4f6f9;
            color: #1e4d7b;
            padding: 4px 8px;
            border: 1px solid #d0dde8;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        table td {
            padding: 4px 8px;
            border: 1px solid #e8edf3;
            vertical-align: top;
        }
        table tr:nth-child(even) td { background: #f9fbfd; }
        .text-right { text-align: right; }
        .text-center{ text-align: center; }
        .text-muted { color: #888; }

        /* ── BADGE ETAPA ── */
        .badge-etapa {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .e-prospecto     { background:#dbeafe; color:#1e40af; }
        .e-cita          { background:#cffafe; color:#0e7490; }
        .e-visita        { background:#d1fae5; color:#065f46; }
        .e-documentacion { background:#fef9c3; color:#854d0e; }
        .e-aceptado      { background:#bbf7d0; color:#14532d; }
        .e-en_espera     { background:#fde68a; color:#78350f; }
        .e-no_aceptado   { background:#fecaca; color:#7f1d1d; }
        .e-inscrito      { background:#e0e7ef; color:#1e3a5f; }
        .e-no_concretado { background:#fee2e2; color:#991b1b; }

        /* ── PIE ── */
        .report-footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 9px;
            color: #aaa;
            display: flex;
            justify-content: space-between;
        }

        /* ── PRINT ── */
        @media print {
            body { padding: 12px 16px; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    @php
        $etiquetasEtapa = [
            'prospecto' => 'Prospecto', 'cita' => 'Cita', 'visita' => 'Visita',
            'documentacion' => 'Documentación', 'aceptado' => 'Aceptado',
            'en_espera' => 'En espera', 'no_aceptado' => 'No aceptado',
            'inscrito' => 'Inscrito', 'no_concretado' => 'No concretado',
        ];
        $etiquetasCanal = [
            'referido' => 'Referido', 'redes' => 'Redes sociales',
            'visita_directa' => 'Visita directa', 'web' => 'Sitio web', 'otro' => 'Otro',
        ];

        $escuela = \App\Models\Setting::find(1);
        $nombreEscuela = $escuela?->nombre_escuela ?? config('app.school_name');
        $total = $datos['total_prospectos'];
    @endphp

    {{-- ── FORMULARIO OCULTO PARA PDF ── --}}
    <form id="formPdf" method="POST" action="{{ route('prospectos.metricas.pdf') }}" style="display:none;">
        @csrf
        <input type="hidden" name="ciclo_id" value="{{ $cicloId }}">
        <input type="hidden" name="chart_etapa" id="inputChartEtapa">
        <input type="hidden" name="chart_canal" id="inputChartCanal">
        <input type="hidden" name="chart_responsable" id="inputChartResponsable">
    </form>

    {{-- ── BOTONES (sólo en pantalla) ── --}}
    <div class="no-print" style="text-align:right; margin-bottom:16px; display:flex; gap:10px; justify-content:flex-end;">
        <a href="{{ route('prospectos.metricas', ['ciclo_id' => $cicloId]) }}"
           style="padding:7px 18px; border:1px solid #ccc; border-radius:20px; font-size:13px; text-decoration:none; color:#555;">
            ← Volver
        </a>
        <button onclick="imprimirInforme()"
            style="padding:7px 20px; background:#555; color:#fff; border:none; border-radius:20px; font-size:13px; cursor:pointer;">
            🖨 Imprimir
        </button>
        <button onclick="exportarPdf()"
            style="padding:7px 20px; background:#1e4d7b; color:#fff; border:none; border-radius:20px; font-size:13px; cursor:pointer;">
            ⬇ Exportar PDF
        </button>
    </div>

    {{-- ── ENCABEZADO ── --}}
    <div class="report-header">
        <div>
            <div class="school">{{ $nombreEscuela }}</div>
            <div class="sub">Informe de Admisiones · {{ $ciclo?->nombre }}</div>
        </div>
        <div class="meta">
            <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}<br>
            <b>Usuario:</b> {{ auth()->user()->nombre ?? '—' }}<br>
            <b>Ciclo:</b> {{ $ciclo?->nombre ?? 'N/A' }}
        </div>
    </div>

    {{-- ── KPIs ── --}}
    <div class="kpi-row">
        <div class="kpi-box">
            <div class="kpi-num">{{ $datos['total_prospectos'] }}</div>
            <div class="kpi-label">Total prospectos</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-num">{{ $datos['total_inscritos'] }}</div>
            <div class="kpi-label">Inscritos</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-num">{{ $datos['tasa_conversion'] }}</div>
            <div class="kpi-label">Tasa de conversión</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-num">{{ $porResponsable->count() }}</div>
            <div class="kpi-label">Responsables activos</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-num">{{ $porSeguimientoUsuario->sum() }}</div>
            <div class="kpi-label">Total seguimientos</div>
        </div>
    </div>

    {{-- ── GRÁFICAS ── --}}
    <div class="section-title">Distribución visual</div>
    <div class="charts-row">
        <div class="chart-wrap">
            <div style="font-size:10px; font-weight:700; color:#666; text-transform:uppercase; margin-bottom:6px;">Por etapa</div>
            <canvas id="chartEtapa" width="600" height="700"></canvas>
        </div>
        <div class="chart-wrap">
            <div style="font-size:10px; font-weight:700; color:#666; text-transform:uppercase; margin-bottom:6px;">Por canal</div>
            <canvas id="chartCanal" width="800" height="500"></canvas>
        </div>
        <div class="chart-wrap">
            <div style="font-size:10px; font-weight:700; color:#666; text-transform:uppercase; margin-bottom:6px;">Por responsable</div>
            <canvas id="chartResponsable" width="900" height="450"></canvas>
        </div>
    </div>

    {{-- ── TABLAS RESUMEN ── --}}
    <div style="display:flex; gap:16px; margin-top:4px;">

        {{-- Por etapa --}}
        <div style="flex:1;">
            <div class="section-title">Por etapa</div>
            <table>
                <thead><tr><th>Etapa</th><th class="text-right">Total</th><th class="text-right">%</th></tr></thead>
                <tbody>
                    @foreach ($datos['por_etapa'] as $etapa => $cnt)
                    <tr>
                        <td>{{ $etiquetasEtapa[$etapa] ?? ucfirst($etapa) }}</td>
                        <td class="text-right">{{ $cnt }}</td>
                        <td class="text-right text-muted">{{ $total > 0 ? round($cnt/$total*100,1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Por canal --}}
        <div style="flex:1;">
            <div class="section-title">Por canal</div>
            <table>
                <thead><tr><th>Canal</th><th class="text-right">Total</th><th class="text-right">%</th></tr></thead>
                <tbody>
                    @foreach ($datos['por_canal'] as $canal => $cnt)
                    <tr>
                        <td>{{ $etiquetasCanal[$canal] ?? ($canal ? ucfirst($canal) : 'Sin canal') }}</td>
                        <td class="text-right">{{ $cnt }}</td>
                        <td class="text-right text-muted">{{ $total > 0 ? round($cnt/$total*100,1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Por responsable --}}
        <div style="flex:1;">
            <div class="section-title">Atención por responsable</div>
            <table>
                <thead><tr><th>Responsable</th><th class="text-right">Prospectos</th><th class="text-right">%</th></tr></thead>
                <tbody>
                    @foreach ($porResponsable as $nombre => $cnt)
                    <tr>
                        <td>{{ $nombre }}</td>
                        <td class="text-right">{{ $cnt }}</td>
                        <td class="text-right text-muted">{{ $total > 0 ? round($cnt/$total*100,1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Seguimientos por usuario --}}
        <div style="flex:1;">
            <div class="section-title">Seguimientos por usuario</div>
            <table>
                <thead><tr><th>Usuario</th><th class="text-right">Acciones</th></tr></thead>
                <tbody>
                    @foreach ($porSeguimientoUsuario as $nombre => $cnt)
                    <tr>
                        <td>{{ $nombre }}</td>
                        <td class="text-right">{{ $cnt }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- ── LISTADO DE PROSPECTOS ── --}}
    <div class="section-title page-break" style="margin-top:22px;">Listado completo de prospectos</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Nivel / Grado</th>
                <th>Canal</th>
                <th>Etapa</th>
                <th>Responsable</th>
                <th>Primer contacto</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($prospectos as $i => $p)
            <tr>
                <td class="text-center text-muted">{{ $i + 1 }}</td>
                <td><b>{{ $p->nombre_completo }}</b></td>
                <td>
                    {{ $p->nivelInteres?->nombre ?? '—' }}
                    @if ($p->gradoInteres)
                        / {{ $p->gradoInteres->numero }}°
                    @endif
                </td>
                <td>{{ $etiquetasCanal[$p->canal_contacto] ?? ($p->canal_contacto ? ucfirst($p->canal_contacto) : '—') }}</td>
                <td>
                    <span class="badge-etapa e-{{ $p->etapa }}">
                        {{ $etiquetasEtapa[$p->etapa] ?? $p->etapa }}
                    </span>
                </td>
                <td>{{ $p->responsable?->nombre ?? '—' }}</td>
                <td class="text-center">{{ $p->fecha_primer_contacto?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Sin prospectos para este ciclo.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── PIE ── --}}
    <div class="report-footer">
        <span>{{ $nombreEscuela }} · Informe de Admisiones · {{ $ciclo?->nombre }}</span>
        <span>Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()->nombre ?? '—' }}</span>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {

    var colorEtapa = {
        prospecto:'#93c5fd', cita:'#67e8f9', visita:'#6ee7b7',
        documentacion:'#fde68a', aceptado:'#86efac', en_espera:'#fcd34d',
        no_aceptado:'#fca5a5', inscrito:'#c7d2fe', no_concretado:'#fca5a5',
    };
    var palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316'];

    var etiquetasEtapa = {
        prospecto:'Prospecto', cita:'Cita', visita:'Visita',
        documentacion:'Documentación', aceptado:'Aceptado',
        en_espera:'En espera', no_aceptado:'No aceptado',
        inscrito:'Inscrito', no_concretado:'No concretado',
    };

    var etiquetasCanal = {
        referido:'Referido', redes:'Redes sociales',
        visita_directa:'Visita directa', web:'Sitio web', otro:'Otro',
    };

    var porEtapa       = @json($datos['por_etapa']);
    var porCanal       = @json($datos['por_canal']);
    var porResponsable = @json($porResponsable);

    /* ── Donut etapa ── */
    var etapaKeys   = Object.keys(porEtapa);
    new Chart(document.getElementById('chartEtapa'), {
        type: 'doughnut',
        data: {
            labels: etapaKeys.map(function(k){ return etiquetasEtapa[k] || k; }),
            datasets: [{
                data: etapaKeys.map(function(k){ return porEtapa[k]; }),
                backgroundColor: etapaKeys.map(function(k){ return colorEtapa[k] || '#cbd5e1'; }),
                borderWidth: 3, borderColor: '#fff',
            }],
        },
        options: {
            responsive: false,
            devicePixelRatio: 2,
            cutout: '55%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 18 }, padding: 14, boxWidth: 18, boxHeight: 18 },
                },
            },
        },
    });

    /* ── Barras canal ── */
    var canalKeys = Object.keys(porCanal);
    new Chart(document.getElementById('chartCanal'), {
        type: 'bar',
        data: {
            labels: canalKeys.map(function(k){ return etiquetasCanal[k] || (k || 'Sin canal'); }),
            datasets: [{
                data: canalKeys.map(function(k){ return porCanal[k]; }),
                backgroundColor: canalKeys.map(function(_,i){ return palette[i % palette.length]; }),
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            responsive: false,
            devicePixelRatio: 2,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 18 } }, grid: { color: '#f0f0f0' } },
                x: { ticks: { font: { size: 18 } }, grid: { display: false } },
            },
        },
    });

    /* ── Barras responsable ── */
    var respKeys = Object.keys(porResponsable);
    new Chart(document.getElementById('chartResponsable'), {
        type: 'bar',
        data: {
            labels: respKeys,
            datasets: [{
                data: respKeys.map(function(k){ return porResponsable[k]; }),
                backgroundColor: respKeys.map(function(_,i){ return palette[(i + 2) % palette.length]; }),
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: false,
            devicePixelRatio: 2,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 18 } }, grid: { color: '#f0f0f0' } },
                y: { ticks: { font: { size: 18 } }, grid: { display: false } },
            },
        },
    });

    /* ── Obtener base64 de un canvas por id ── */
    function canvasToBase64(id) {
        var el = document.getElementById(id);
        return el ? el.toDataURL('image/png') : '';
    }

    /* ── Imprimir: canvas → img, luego print() ── */
    window.imprimirInforme = function () {
        ['chartEtapa','chartCanal','chartResponsable'].forEach(function(id) {
            var canvas = document.getElementById(id);
            if (!canvas) return;
            var img = document.createElement('img');
            img.src = canvas.toDataURL('image/png');
            img.style.cssText = canvas.style.cssText;
            img.width  = canvas.width;
            img.height = canvas.height;
            canvas.parentNode.replaceChild(img, canvas);
        });
        window.print();
    };

    /* ── Exportar PDF: enviar base64 al servidor ── */
    window.exportarPdf = function () {
        document.getElementById('inputChartEtapa').value       = canvasToBase64('chartEtapa');
        document.getElementById('inputChartCanal').value       = canvasToBase64('chartCanal');
        document.getElementById('inputChartResponsable').value = canvasToBase64('chartResponsable');
        document.getElementById('formPdf').submit();
    };

})();
</script>
</html>
