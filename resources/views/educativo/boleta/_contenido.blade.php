{{--
    Contenido de la boleta de calificaciones.
    Compartido entre la vista HTML (show) y la plantilla PDF.
    Variables esperadas: $alumno, $ciclo, $grupo, $plan, $periodos,
                        $esPreescolar, $filas, $promedio_sep, $promedio_institucional
--}}

{{-- ── Encabezado institucional ───────────────────────────── --}}
<table style="width:100%; margin-bottom:20px;">
    <tr>
        <td style="width:80px; vertical-align:middle;">
            <img src="{{ public_path('imgs_escuela/reportes/logo_reportes.png') }}"
                 alt="Logo" style="max-width:70px; max-height:70px;">
        </td>
        <td style="text-align:center; vertical-align:middle;">
            <h2 style="margin:0 0 4px 0; font-size:16px;">{{ config('app.name') }}</h2>
            <p style="margin:0; font-size:12px; color:#555;">
                Boleta de calificaciones &mdash; {{ $ciclo?->nombre ?? 'Ciclo no definido' }}
            </p>
        </td>
        <td style="width:80px;"></td>
    </tr>
</table>

<hr style="border-top:2px solid #3c8dbc; margin-bottom:15px;">

{{-- ── Datos del alumno ────────────────────────────────────── --}}
<table style="width:100%; margin-bottom:15px; font-size:13px;">
    <tr>
        <td style="width:50%; padding:3px 0;">
            <strong>Alumno:</strong>
            {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }} {{ $alumno->nombre }}
        </td>
        <td style="width:50%; padding:3px 0;">
            <strong>Matrícula:</strong> {{ $alumno->matricula ?? '—' }}
        </td>
    </tr>
    <tr>
        <td style="padding:3px 0;">
            <strong>Nivel:</strong> {{ $grupo?->grado?->nivel?->nombre ?? '—' }}
            &nbsp;&nbsp;
            <strong>Grado:</strong> {{ $grupo?->grado?->nombre ?? '—' }}
        </td>
        <td style="padding:3px 0;">
            <strong>Grupo:</strong> {{ $grupo?->nombre ?? '—' }}
        </td>
    </tr>
    @if($plan)
    <tr>
        <td colspan="2" style="padding:3px 0;">
            <strong>Plan de estudios:</strong> {{ $plan->nombre }}
            &nbsp;&nbsp;
            <strong>Período:</strong> {{ $plan->tipo_periodo->etiqueta() }}
        </td>
    </tr>
    @endif
</table>

{{-- ── Sin datos ───────────────────────────────────────────── --}}
@if($filas->isEmpty() || $periodos->isEmpty())
    <div style="padding:20px; text-align:center; color:#888; border:1px dashed #ccc; border-radius:4px;">
        No hay calificaciones registradas para este alumno en el ciclo seleccionado.
    </div>

{{-- ── Boleta Preescolar (campos formativos descriptivos) ─── --}}
@elseif($esPreescolar)
    <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
            <tr style="background:#3c8dbc; color:#fff;">
                <th style="padding:7px 10px; text-align:left; border:1px solid #2e7aab;">
                    Campo formativo
                </th>
                @foreach($periodos as $periodo)
                    <th style="padding:7px 10px; text-align:center; border:1px solid #2e7aab; width:{{ max(120, intdiv(500, $periodos->count())) }}px;">
                        {{ $periodo->nombre }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $i => $fila)
            <tr style="{{ $i % 2 === 0 ? 'background:#f9f9f9;' : 'background:#fff;' }}">
                <td style="padding:8px 10px; border:1px solid #ddd; font-weight:600; vertical-align:top;">
                    {{ $fila['nombre'] }}
                </td>
                @foreach($periodos as $periodo)
                @php $cal = $fila['calificaciones'][$periodo->id] ?? null; @endphp
                <td style="padding:8px 10px; border:1px solid #ddd; vertical-align:top; font-size:11px;">
                    {{ $cal?->texto_descriptivo ?? '—' }}
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

{{-- ── Boleta Numérica / Literal (Primaria, Sec, Prep) ────── --}}
@else
    <table style="width:100%; border-collapse:collapse; font-size:12px;">
        <thead>
            <tr style="background:#3c8dbc; color:#fff;">
                <th style="padding:7px 10px; text-align:left; border:1px solid #2e7aab;">
                    Materia
                </th>
                <th style="padding:7px 8px; text-align:center; border:1px solid #2e7aab; width:60px;">
                    Tipo
                </th>
                @foreach($periodos as $periodo)
                    <th style="padding:7px 8px; text-align:center; border:1px solid #2e7aab; width:70px;">
                        {{ $periodo->nombre }}
                    </th>
                @endforeach
                <th style="padding:7px 8px; text-align:center; border:1px solid #2e7aab; width:75px;">
                    Promedio
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $i => $fila)
            <tr style="{{ $i % 2 === 0 ? 'background:#f9f9f9;' : 'background:#fff;' }}">
                <td style="padding:6px 10px; border:1px solid #ddd;">
                    {{ $fila['nombre'] }}
                </td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:center; font-size:10px; color:#666;">
                    {{ $fila['tipo']?->etiqueta() ?? '—' }}
                </td>
                @foreach($periodos as $periodo)
                @php $cal = $fila['calificaciones'][$periodo->id] ?? null; @endphp
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:center;">
                    @if($cal && $cal->tieneValor())
                        {{ $cal->valorMostrable() }}
                    @else
                        <span style="color:#bbb;">—</span>
                    @endif
                </td>
                @endforeach
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:center; font-weight:700;
                           {{ $fila['promedio'] !== null && $plan?->escala?->valor_aprobatorio && $fila['promedio'] < $plan->escala->valor_aprobatorio ? 'color:#c0392b;' : 'color:#27ae60;' }}">
                    {{ $fila['promedio'] !== null ? number_format($fila['promedio'], 1) : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>

        {{-- Pie con promedios SEP e institucional --}}
        <tfoot>
            @if($promedio_sep !== null)
            <tr style="background:#eaf3fb;">
                <td colspan="{{ 2 + $periodos->count() }}"
                    style="padding:6px 10px; border:1px solid #ddd; text-align:right; font-weight:600;">
                    Promedio SEP
                </td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:center; font-weight:700; font-size:14px;">
                    {{ number_format($promedio_sep, 1) }}
                </td>
            </tr>
            @endif
            @if($promedio_institucional !== null)
            <tr style="background:#eafbea;">
                <td colspan="{{ 2 + $periodos->count() }}"
                    style="padding:6px 10px; border:1px solid #ddd; text-align:right; font-weight:600;">
                    Promedio Institucional
                </td>
                <td style="padding:6px 8px; border:1px solid #ddd; text-align:center; font-weight:700; font-size:14px;">
                    {{ number_format($promedio_institucional, 1) }}
                </td>
            </tr>
            @endif
        </tfoot>
    </table>
@endif

{{-- ── Firmas ───────────────────────────────────────────────── --}}
<table style="width:100%; margin-top:40px; font-size:12px;">
    <tr>
        <td style="width:33%; text-align:center; padding-top:30px; border-top:1px solid #333;">
            Director(a)
        </td>
        <td style="width:33%; text-align:center; padding-top:30px; border-top:1px solid #333;">
            Docente
        </td>
        <td style="width:33%; text-align:center; padding-top:30px; border-top:1px solid #333;">
            Padre / Tutor
        </td>
    </tr>
</table>

<p style="font-size:10px; color:#999; margin-top:20px; text-align:right;">
    Generado el {{ now()->format('d/m/Y H:i') }}
</p>
