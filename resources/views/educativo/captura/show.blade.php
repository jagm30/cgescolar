@extends('layouts.educativo')

@section('page_title', 'Capturar calificaciones')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.captura.index') }}">Captura</a></li>
    <li class="active">{{ $asignacion->etiquetaContenido() }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">

        {{-- Encabezado informativo --}}
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-pencil-square-o"></i>
                    {{ $asignacion->etiquetaContenido() }}
                    &mdash;
                    {{ $asignacion->grupo->nombre_completo ?? $asignacion->grupo->nombre }}
                </h3>
                <div class="box-tools">
                    <a href="{{ route('educativo.captura.index') }}"
                       class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Docente:</strong>
                        {{ $asignacion->docente->nombre_completo }}
                    </div>
                    <div class="col-sm-3">
                        <strong>Nivel:</strong>
                        {{ $asignacion->grupo->grado->nivel->nombre ?? '—' }}
                    </div>
                    @if($escala)
                    <div class="col-sm-3">
                        <strong>Escala:</strong>
                        {{ $escala->nombre }}
                        ({{ $escala->tipo->etiqueta() }})
                        @if($escala->esNumerica())
                            {{ $escala->valor_minimo }}–{{ $escala->valor_maximo }}
                        @endif
                    </div>
                    @endif
                    <div class="col-sm-3">
                        <strong>Alumnos:</strong>
                        {{ $inscripciones->count() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Leyenda de períodos --}}
        @if($periodos->isEmpty())
            <div class="callout callout-warning">
                <i class="fa fa-warning"></i>
                No hay períodos evaluativos configurados para este plan en el ciclo actual.
                El administrador debe generarlos desde
                <a href="{{ route('educativo.sicap.periodos.index') }}">Períodos evaluativos</a>.
            </div>
        @elseif($inscripciones->isEmpty())
            <div class="callout callout-warning">
                <i class="fa fa-warning"></i>
                No hay alumnos inscritos activos en este grupo.
            </div>
        @else

        {{-- ── Cuadrícula de captura ─────────────────────────── --}}
        <form id="form-captura">
            @csrf

            @php
                $esPreescolar = $asignacion->esDePreescolar();
                $esLiteral    = $escala?->esLiteral() ?? false;
                $esNumerica   = ! $esPreescolar && ! $esLiteral;
                $minVal       = $escala?->valor_minimo;
                $maxVal       = $escala?->valor_maximo;
            @endphp

            @foreach($periodos as $periodo)
            <div class="box {{ $periodo->estaAbierto() ? 'box-success' : 'box-default' }}">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-calendar-o"></i>
                        {{ $periodo->nombre }}
                        <span class="label label-{{ $periodo->estado->badgeClass() }} ml-5">
                            {{ $periodo->estado->etiqueta() }}
                        </span>
                    </h3>
                    @if($periodo->estaAbierto())
                        <div class="box-tools">
                            <button type="button"
                                    class="btn btn-primary btn-sm btn-guardar-periodo"
                                    data-periodo="{{ $periodo->id }}">
                                <i class="fa fa-save"></i> Guardar este período
                            </button>
                        </div>
                    @endif
                </div>
                <div class="box-body no-padding">
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Alumno</th>
                                @if($esPreescolar)
                                    <th>Descripción (campo formativo)</th>
                                @elseif($esLiteral)
                                    <th style="width:160px;">Calificación</th>
                                @else
                                    <th style="width:120px;">Calificación</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inscripciones as $i => $inscripcion)
                            @php
                                $alumno = $inscripcion->alumno;
                                $cal    = $calificacionesExistentes[$periodo->id][$alumno->id] ?? null;
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td>
                                    {{ $alumno->ap_paterno }}
                                    {{ $alumno->ap_materno }}
                                    {{ $alumno->nombre }}
                                </td>
                                <td>
                                    @if($esPreescolar)
                                        {{-- Campo formativo: textarea con límite de caracteres --}}
                                        @php $maxChars = $asignacion->campoFormativo->max_caracteres ?? 500; @endphp
                                        <textarea
                                            name="calificaciones[{{ $periodo->id }}][{{ $alumno->id }}]"
                                            class="form-control input-cal"
                                            rows="3"
                                            maxlength="{{ $maxChars }}"
                                            data-max="{{ $maxChars }}"
                                            {{ $periodo->estaAbierto() ? '' : 'disabled' }}
                                            placeholder="Descripción (máx. {{ $maxChars }} caracteres)"
                                        >{{ old("calificaciones.{$periodo->id}.{$alumno->id}", $cal?->texto_descriptivo) }}</textarea>
                                        <small class="text-muted contador-chars">
                                            {{ strlen($cal?->texto_descriptivo ?? '') }} / {{ $maxChars }}
                                        </small>

                                    @elseif($esLiteral)
                                        {{-- Escala literal: select con criterios --}}
                                        <select
                                            name="calificaciones[{{ $periodo->id }}][{{ $alumno->id }}]"
                                            class="form-control input-cal"
                                            {{ $periodo->estaAbierto() ? '' : 'disabled' }}>
                                            <option value="">— Sin calificación —</option>
                                            @foreach($criterios as $criterio)
                                                <option value="{{ $criterio->id }}"
                                                    {{ old("calificaciones.{$periodo->id}.{$alumno->id}", $cal?->criterio_id) == $criterio->id ? 'selected' : '' }}>
                                                    {{ $criterio->etiqueta }}
                                                    @if($criterio->descripcion)
                                                        — {{ $criterio->descripcion }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>

                                    @else
                                        {{-- Escala numérica --}}
                                        <input
                                            type="number"
                                            name="calificaciones[{{ $periodo->id }}][{{ $alumno->id }}]"
                                            class="form-control input-cal"
                                            value="{{ old("calificaciones.{$periodo->id}.{$alumno->id}", $cal?->valor_numerico !== null ? number_format($cal->valor_numerico, 1) : '') }}"
                                            min="{{ $minVal }}"
                                            max="{{ $maxVal }}"
                                            step="0.1"
                                            {{ $periodo->estaAbierto() ? '' : 'disabled' }}
                                            placeholder="{{ $minVal }}–{{ $maxVal }}"
                                        >
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            {{-- Botón guardar todo --}}
            @if($periodos->where(fn($p) => $p->estaAbierto())->isNotEmpty())
            <div class="text-right" style="margin-bottom:20px;">
                <button type="button" id="btn-guardar-todo" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Guardar todas las calificaciones
                </button>
            </div>
            @endif

        </form>

        @endif {{-- periodos e inscripciones no vacíos --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    const urlGuardar = "{{ route('educativo.captura.store', $asignacion) }}";

    // ── Contador de caracteres en textareas ───────────────
    $(document).on('input', 'textarea.input-cal', function () {
        const max = parseInt($(this).data('max'));
        const len = $(this).val().length;
        $(this).closest('td').find('.contador-chars').text(len + ' / ' + max);
    });

    // ── Recolectar calificaciones de un período específico ─
    function recolectarPeriodo(periodoId) {
        const calificaciones = {};
        calificaciones[periodoId] = {};

        $('[name^="calificaciones[' + periodoId + ']"]').each(function () {
            const match = $(this).attr('name').match(/calificaciones\[(\d+)\]\[(\d+)\]/);
            if (match) {
                calificaciones[match[1]][match[2]] = $(this).val();
            }
        });
        return calificaciones;
    }

    // ── Recolectar TODAS las calificaciones ───────────────
    function recolectarTodo() {
        const calificaciones = {};

        $('[name^="calificaciones["]').each(function () {
            const match = $(this).attr('name').match(/calificaciones\[(\d+)\]\[(\d+)\]/);
            if (match) {
                const pid = match[1], aid = match[2];
                if (!calificaciones[pid]) calificaciones[pid] = {};
                calificaciones[pid][aid] = $(this).val();
            }
        });
        return calificaciones;
    }

    // ── Enviar al servidor ────────────────────────────────
    function guardar(calificaciones, btn) {
        btn.prop('disabled', true)
           .html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: urlGuardar,
            method: 'POST',
            data: {
                _token: $('input[name=_token]').val(),
                calificaciones: calificaciones,
            },
            success: function (res) {
                btn.prop('disabled', false)
                   .html('<i class="fa fa-save"></i> ' + btn.data('label-ok'));
                toastr.success(res.message ?? 'Calificaciones guardadas.');
            },
            error: function (xhr) {
                btn.prop('disabled', false)
                   .html('<i class="fa fa-save"></i> ' + btn.data('label-ok'));
                toastr.error(xhr.responseJSON?.message ?? 'Error al guardar.');
            },
        });
    }

    // ── Guardar período individual ────────────────────────
    $(document).on('click', '.btn-guardar-periodo', function () {
        const periodoId = $(this).data('periodo');
        const btn       = $(this)
            .data('label-ok', $(this).text().trim());

        guardar(recolectarPeriodo(periodoId), btn);
    });

    // ── Guardar todo ──────────────────────────────────────
    $('#btn-guardar-todo').on('click', function () {
        const btn = $(this).data('label-ok', $(this).text().trim());
        guardar(recolectarTodo(), btn);
    });

});
</script>
@endpush
