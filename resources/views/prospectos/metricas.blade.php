@extends('layouts.master')

@section('page_title', 'Métricas de prospectos')
@section('page_subtitle', 'Resumen del ciclo activo')

@push('styles')
<style>
    .metrics-chart-box {
        border-radius: 8px;
        border: 1px solid #e0e7ef;
        background: #fff;
        margin-bottom: 20px;
        overflow: hidden;
    }
    .metrics-chart-box .chart-header {
        background: #f4f6f8;
        border-bottom: 1px solid #e0e7ef;
        padding: 10px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .metrics-chart-box .chart-header span {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #6b7a8d;
    }
    .metrics-chart-box .chart-body {
        padding: 16px;
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }
    .chart-canvas-wrap {
        flex: 0 0 220px;
        height: 220px;
        position: relative;
    }
    .chart-legend {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        flex-shrink: 0;
    }
    .legend-label { color: #4a5568; flex: 1; }
    .legend-count { font-weight: 700; color: #2d3748; min-width: 28px; text-align: right; }
    .legend-pct   { color: #a0aec0; font-size: 11px; min-width: 40px; text-align: right; }

    .small-box .inner h3 { font-size: 36px; }
</style>
@endpush

@section('content')
    {{-- ── Filtro de ciclo ── --}}
    <div class="box box-primary">
        <form method="GET" action="{{ route('prospectos.metricas') }}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ciclo_id">Ciclo</label>
                            <select class="form-control" id="ciclo_id" name="ciclo_id">
                                @foreach ($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ (string) $cicloId === (string) $ciclo->id ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-default btn-block">Ver</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" onclick="exportarPdf()" class="btn btn-primary btn-block">
                                <i class="fa fa-file-pdf-o"></i> Exportar a PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ── KPIs ── --}}
    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $datos['total_prospectos'] }}</h3>
                    <p>Total de prospectos</p>
                </div>
                <div class="icon"><i class="fa fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $datos['total_inscritos'] }}</h3>
                    <p>Prospectos inscritos</p>
                </div>
                <div class="icon"><i class="fa fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $datos['tasa_conversion'] }}</h3>
                    <p>Tasa de conversión</p>
                </div>
                <div class="icon"><i class="fa fa-line-chart"></i></div>
            </div>
        </div>
    </div>

    {{-- ── Gráficas ── --}}
    <div class="row">

        {{-- Donut: por etapa --}}
        <div class="col-md-6">
            <div class="metrics-chart-box">
                <div class="chart-header">
                    <i class="fa fa-pie-chart" style="color:#3c8dbc;"></i>
                    <span>Prospectos por etapa</span>
                </div>
                <div class="chart-body">
                    <div class="chart-canvas-wrap">
                        <canvas id="chartEtapa"></canvas>
                    </div>
                    <div class="chart-legend" id="legendEtapa"></div>
                </div>
            </div>
        </div>

        {{-- Barras: por canal --}}
        <div class="col-md-6">
            <div class="metrics-chart-box">
                <div class="chart-header">
                    <i class="fa fa-bar-chart" style="color:#3c8dbc;"></i>
                    <span>Prospectos por canal</span>
                </div>
                <div class="chart-body" style="flex-direction: column; padding-bottom: 20px;">
                    <canvas id="chartCanal" style="max-height:220px;"></canvas>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12" style="text-align:right; margin-bottom:10px;">
            <a href="{{ route('prospectos.index', ['ciclo_id' => $cicloId]) }}" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Volver a prospectos
            </a>
        </div>
    </div>

    <form id="formPdf" method="POST" action="{{ route('prospectos.metricas.pdf') }}" style="display:none;">
        @csrf
        <input type="hidden" name="ciclo_id" value="{{ $cicloId }}">
        <input type="hidden" name="chart_etapa" id="inputChartEtapa">
        <input type="hidden" name="chart_canal" id="inputChartCanal">
        <input type="hidden" name="chart_responsable" id="inputChartResponsable">
    </form>

    {{-- Canvases ocultos de alta resolución exclusivos para PDF --}}
    <div style="position:absolute; left:-9999px; top:0; visibility:hidden;">
        <canvas id="pdfChartEtapa"      width="700" height="800"></canvas>
        <canvas id="pdfChartCanal"      width="900" height="520"></canvas>
        <canvas id="pdfChartSeguimiento" width="900" height="480"></canvas>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {

    /* ── Paleta de colores por etapa ── */
    var colorEtapa = {
        prospecto:      '#93c5fd',
        cita:           '#67e8f9',
        visita:         '#6ee7b7',
        documentacion:  '#fde68a',
        aceptado:       '#86efac',
        en_espera:      '#fcd34d',
        no_aceptado:    '#fca5a5',
        inscrito:       '#c7d2fe',
        no_concretado:  '#fca5a5',
    };

    var canalColors = [
        '#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6',
    ];

    var etiquetasEtapa = {
        prospecto: 'Prospecto', cita: 'Cita', visita: 'Visita',
        documentacion: 'Documentación', aceptado: 'Aceptado',
        en_espera: 'En espera', no_aceptado: 'No aceptado',
        inscrito: 'Inscrito', no_concretado: 'No concretado',
    };

    /* ── Datos desde PHP ── */
    var porEtapa  = @json($datos['por_etapa']);
    var porCanal  = @json($datos['por_canal']);
    var totalPros = {{ $datos['total_prospectos'] }};

    /* ── Donut: etapa ── */
    var etapaKeys    = Object.keys(porEtapa);
    var etapaValues  = etapaKeys.map(function(k) { return porEtapa[k]; });
    var etapaLabels  = etapaKeys.map(function(k) { return etiquetasEtapa[k] || k; });
    var etapaColors  = etapaKeys.map(function(k) { return colorEtapa[k] || '#cbd5e1'; });

    new Chart(document.getElementById('chartEtapa'), {
        type: 'doughnut',
        data: {
            labels: etapaLabels,
            datasets: [{ data: etapaValues, backgroundColor: etapaColors, borderWidth: 2, borderColor: '#fff' }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: function(ctx) {
                    var pct = totalPros > 0 ? ((ctx.parsed / totalPros) * 100).toFixed(1) : 0;
                    return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                }
            }}},
        },
    });

    /* Leyenda manual etapa */
    var legendEl = document.getElementById('legendEtapa');
    etapaKeys.forEach(function(k, i) {
        var pct = totalPros > 0 ? ((etapaValues[i] / totalPros) * 100).toFixed(1) : '0.0';
        legendEl.innerHTML +=
            '<div class="legend-item">' +
            '<div class="legend-dot" style="background:' + etapaColors[i] + ';"></div>' +
            '<span class="legend-label">' + etapaLabels[i] + '</span>' +
            '<span class="legend-count">' + etapaValues[i] + '</span>' +
            '<span class="legend-pct">' + pct + '%</span>' +
            '</div>';
    });

    /* ── Barras: canal ── */
    var canalKeys   = Object.keys(porCanal);
    var canalValues = canalKeys.map(function(k) { return porCanal[k]; });
    var canalLabels = canalKeys.map(function(k) {
        var m = { referido:'Referido', redes:'Redes sociales', visita_directa:'Visita directa', web:'Sitio web', otro:'Otro' };
        return m[k] || (k ? k.charAt(0).toUpperCase() + k.slice(1).replace('_',' ') : 'Sin canal');
    });

    new Chart(document.getElementById('chartCanal'), {
        type: 'bar',
        data: {
            labels: canalLabels,
            datasets: [{
                label: 'Prospectos',
                data: canalValues,
                backgroundColor: canalKeys.map(function(_, i) { return canalColors[i % canalColors.length]; }),
                borderRadius: 5,
                borderSkipped: false,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#f0f0f0' } },
                x: { grid: { display: false } },
            },
        },
    });

    /* ── Paleta ── */
    var palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316'];

    /* ── Datos para gráficas PDF ── */
    var porSeguimiento = @json($porSeguimientoUsuario);

    /* ── Gráfica PDF: Donut etapa (alta resolución) ── */
    new Chart(document.getElementById('pdfChartEtapa'), {
        type: 'doughnut',
        data: {
            labels: etapaLabels,
            datasets: [{ data: etapaValues, backgroundColor: etapaColors, borderWidth: 3, borderColor: '#fff' }],
        },
        options: {
            responsive: false,
            devicePixelRatio: 2,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 16 }, padding: 14, boxWidth: 16, boxHeight: 16 } },
            },
        },
    });

    /* ── Gráfica PDF: Barras canal (alta resolución) ── */
    new Chart(document.getElementById('pdfChartCanal'), {
        type: 'bar',
        data: {
            labels: canalLabels,
            datasets: [{
                data: canalValues,
                backgroundColor: canalKeys.map(function(_, i) { return canalColors[i % canalColors.length]; }),
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            responsive: false,
            devicePixelRatio: 2,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 16 } }, grid: { color: '#f0f0f0' } },
                x: { ticks: { font: { size: 16 } }, grid: { display: false } },
            },
        },
    });

    /* ── Gráfica PDF: Barras seguimientos por usuario (alta resolución) ── */
    var segKeys   = Object.keys(porSeguimiento);
    var segValues = segKeys.map(function(k) { return porSeguimiento[k]; });
    new Chart(document.getElementById('pdfChartSeguimiento'), {
        type: 'bar',
        data: {
            labels: segKeys,
            datasets: [{
                data: segValues,
                backgroundColor: segKeys.map(function(_, i) { return palette[(i + 3) % palette.length]; }),
                borderRadius: 6, borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: false,
            devicePixelRatio: 2,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 16 } }, grid: { color: '#f0f0f0' } },
                y: { ticks: { font: { size: 16 } }, grid: { display: false } },
            },
        },
    });

    window.exportarPdf = function () {
        var btn = document.querySelector('[onclick="exportarPdf()"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando...';

        document.getElementById('inputChartEtapa').value      = document.getElementById('pdfChartEtapa').toDataURL('image/png');
        document.getElementById('inputChartCanal').value      = document.getElementById('pdfChartCanal').toDataURL('image/png');
        document.getElementById('inputChartResponsable').value = document.getElementById('pdfChartSeguimiento').toDataURL('image/png');

        document.getElementById('formPdf').submit();

        setTimeout(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-file-pdf-o"></i> Exportar a PDF';
        }, 4000);
    };

})();
</script>
@endpush
