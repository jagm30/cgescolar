@extends('layouts.educativo')

@section('page_title', 'Asignaciones docentes')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
    <li class="active">Asignaciones docentes</li>
@endsection

@section('content')
<div class="row">
    {{-- Filtro de ciclo --}}
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-body">
                <form method="GET" action="{{ route('educativo.sicap.asignaciones.index') }}"
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

                <a href="{{ route('educativo.sicap.asignaciones.create', ['ciclo_id' => $cicloSeleccionado]) }}"
                   class="btn btn-primary btn-sm" style="margin-left:10px;">
                    <i class="fa fa-plus"></i> Nueva asignación
                </a>
            </div>
        </div>
    </div>
</div>

@forelse($asignaciones as $docenteNombre => $asigs)
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user"></i> {{ $docenteNombre }}
                </h3>
            </div>
            <div class="box-body no-padding">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Nivel</th>
                            <th>Materia / Campo formativo</th>
                            <th>Tipo</th>
                            <th>Activa</th>
                            <th style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asigs as $asignacion)
                        <tr data-id="{{ $asignacion->id }}">
                            <td>{{ $asignacion->grupo->nombre_completo ?? $asignacion->grupo->nombre }}</td>
                            <td>{{ $asignacion->grupo->grado->nivel->nombre ?? '—' }}</td>
                            <td>{{ $asignacion->etiquetaContenido() }}</td>
                            <td>
                                @if($asignacion->esDePreescolar())
                                    <span class="label label-warning">Campo formativo</span>
                                @else
                                    <span class="label label-primary">Materia</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-toggle-activa
                                               {{ $asignacion->activa ? 'btn-success' : 'btn-default' }}"
                                        data-id="{{ $asignacion->id }}"
                                        title="{{ $asignacion->activa ? 'Desactivar' : 'Activar' }}">
                                    <i class="fa fa-{{ $asignacion->activa ? 'check' : 'times' }}"></i>
                                    {{ $asignacion->activa ? 'Sí' : 'No' }}
                                </button>
                            </td>
                            <td>
                                <a href="{{ route('educativo.sicap.asignaciones.edit', $asignacion) }}"
                                   class="btn btn-xs btn-default" title="Editar">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <button class="btn btn-xs btn-danger btn-eliminar"
                                        data-id="{{ $asignacion->id }}"
                                        data-nombre="{{ $asignacion->etiquetaContenido() }}"
                                        title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
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
            No hay asignaciones para este ciclo.
            <a href="{{ route('educativo.sicap.asignaciones.create', ['ciclo_id' => $cicloSeleccionado]) }}">
                Crear la primera asignación
            </a>.
        </div>
    </div>
</div>
@endforelse

@endsection

@push('scripts')
<script>
$(function () {

    // ── Activar / desactivar asignación ──────────────────
    $(document).on('click', '.btn-toggle-activa', function () {
        const id  = $(this).data('id');
        const btn = $(this);

        $.ajax({
            url: '/educativo/sicap/asignaciones/' + id + '/toggle',
            method: 'PATCH',
            success: function (res) {
                const activa = res.data.activa;
                btn.toggleClass('btn-success', activa)
                   .toggleClass('btn-default', !activa)
                   .attr('title', activa ? 'Desactivar' : 'Activar')
                   .html('<i class="fa fa-' + (activa ? 'check' : 'times') + '"></i> ' + (activa ? 'Sí' : 'No'));
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'No se pudo cambiar el estado.');
            }
        });
    });

    // ── Eliminar asignación ──────────────────────────────
    $(document).on('click', '.btn-eliminar', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');
        const fila   = $(this).closest('tr');

        if (!confirm('¿Eliminar la asignación de "' + nombre + '"?')) return;

        $.ajax({
            url: '/educativo/sicap/asignaciones/' + id,
            method: 'DELETE',
            success: function () {
                fila.fadeOut(300, () => fila.remove());
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'No se pudo eliminar la asignación.');
            }
        });
    });
});
</script>
@endpush
