@extends('layouts.educativo')

@section('page_title', 'Períodos evaluativos')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li class="active">Períodos evaluativos</li>
@endsection

@section('content')
<div class="row">
    {{-- Filtro de ciclo + botón generar --}}
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-body">
                <form method="GET" action="{{ route('educativo.sicap.periodos.index') }}"
                      class="form-inline">
                    <div class="form-group">
                        <label class="sr-only" for="ciclo_id">Ciclo escolar</label>
                        <select name="ciclo_id" id="ciclo_id" class="form-control input-sm"
                                onchange="this.form.submit()">
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}"
                                    {{ $ciclo->id == $cicloSeleccionado ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Generación automática --}}
                <form method="POST"
                      action="{{ route('educativo.sicap.periodos.generar') }}"
                      class="form-inline"
                      style="display:inline; margin-left:10px;"
                      id="form-generar">
                    @csrf
                    <input type="hidden" name="ciclo_id" value="{{ $cicloSeleccionado }}">
                    <button type="button" class="btn btn-success btn-sm" id="btn-generar">
                        <i class="fa fa-magic"></i> Generar períodos automáticamente
                    </button>
                </form>

                <a href="{{ route('educativo.sicap.periodos.create') }}"
                   class="btn btn-primary btn-sm" style="margin-left:5px;">
                    <i class="fa fa-plus"></i> Nuevo período
                </a>
            </div>
        </div>
    </div>
</div>

@forelse($periodos as $nivelNombre => $grupo)
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-calendar"></i> {{ $nivelNombre }}
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Plan de estudios</th>
                            <th>Fechas</th>
                            <th>Estado</th>
                            <th style="width:160px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupo as $periodo)
                        <tr data-id="{{ $periodo->id }}">
                            <td>{{ $periodo->nombre }}</td>
                            <td>{{ $periodo->plan->nombre }}</td>
                            <td>
                                @if($periodo->fecha_inicio && $periodo->fecha_fin)
                                    {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }}
                                    –
                                    {{ \Carbon\Carbon::parse($periodo->fecha_fin)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Sin fechas</span>
                                @endif
                            </td>
                            <td>
                                <span class="label label-{{ $periodo->estado->badgeClass() }} lbl-estado">
                                    {{ $periodo->estado->etiqueta() }}
                                </span>
                            </td>
                            <td>
                                @if($periodo->estaPendiente())
                                    <button class="btn btn-xs btn-success btn-abrir"
                                            data-id="{{ $periodo->id }}"
                                            title="Abrir para captura">
                                        <i class="fa fa-unlock"></i> Abrir
                                    </button>
                                @endif

                                @if($periodo->estaAbierto())
                                    <button class="btn btn-xs btn-warning btn-cerrar"
                                            data-id="{{ $periodo->id }}"
                                            title="Cerrar captura">
                                        <i class="fa fa-lock"></i> Cerrar
                                    </button>
                                @endif

                                <a href="{{ route('educativo.sicap.periodos.edit', $periodo) }}"
                                   class="btn btn-xs btn-default" title="Editar">
                                    <i class="fa fa-pencil"></i>
                                </a>

                                @if($periodo->estaPendiente())
                                    <button class="btn btn-xs btn-danger btn-eliminar"
                                            data-id="{{ $periodo->id }}"
                                            data-nombre="{{ $periodo->nombre }}"
                                            title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@empty
<div class="row">
    <div class="col-xs-12">
        <div class="callout callout-info">
            No hay períodos evaluativos para este ciclo.
            Usa <strong>"Generar períodos automáticamente"</strong> o crea uno manualmente.
        </div>
    </div>
</div>
@endforelse

@endsection

@push('scripts')
<script>
$(function () {

    // ── Generar períodos ─────────────────────────────────
    $('#btn-generar').on('click', function () {
        if (!confirm('¿Generar períodos para todos los planes activos de este ciclo?\nLos que ya existan no serán duplicados.')) return;
        $('#form-generar').submit();
    });

    // ── Abrir período ────────────────────────────────────
    $(document).on('click', '.btn-abrir', function () {
        const id  = $(this).data('id');
        const fila = $(this).closest('tr');

        if (!confirm('¿Abrir este período? Los docentes podrán capturar calificaciones.')) return;

        $.ajax({
            url: '/educativo/sicap/periodos/' + id + '/abrir',
            method: 'PATCH',
            success: function (res) {
                fila.find('.lbl-estado')
                    .removeClass()
                    .addClass('label lbl-estado label-' + res.data.estado_badge)
                    .text(res.data.estado_etiqueta);
                location.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'No se pudo abrir el período.');
            }
        });
    });

    // ── Cerrar período ───────────────────────────────────
    $(document).on('click', '.btn-cerrar', function () {
        const id   = $(this).data('id');

        if (!confirm('¿Cerrar este período? La captura de calificaciones quedará bloqueada.')) return;

        $.ajax({
            url: '/educativo/sicap/periodos/' + id + '/cerrar',
            method: 'PATCH',
            success: function () {
                location.reload();
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'No se pudo cerrar el período.');
            }
        });
    });

    // ── Eliminar período ─────────────────────────────────
    $(document).on('click', '.btn-eliminar', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');
        const fila   = $(this).closest('tr');

        if (!confirm('¿Eliminar el período "' + nombre + '"?')) return;

        $.ajax({
            url: '/educativo/sicap/periodos/' + id,
            method: 'DELETE',
            success: function () {
                fila.fadeOut(300, () => fila.remove());
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'No se pudo eliminar el período.');
            }
        });
    });
});
</script>
@endpush
