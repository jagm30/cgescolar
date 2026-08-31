@extends('layouts.educativo')

@section('page_title', 'Planes de estudio')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-book"></i> Planes de estudio</h3>
                <div class="box-tools">
                    <a href="{{ route('educativo.sicap.planes.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Nuevo plan
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Nivel</th>
                            <th>Escala</th>
                            <th>Periodicidad</th>
                            <th>Ciclo específico</th>
                            <th>Materias / Campos</th>
                            <th>Estado</th>
                            <th style="width:100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($planes as $plan)
                        <tr>
                            <td>
                                <a href="{{ route('educativo.sicap.planes.show', $plan) }}">
                                    {{ $plan->nombre }}
                                </a>
                            </td>
                            <td>{{ $plan->nivel->nombre }}</td>
                            <td>{{ $plan->escala->nombre }}</td>
                            <td>
                                <span class="label label-info">
                                    {{ $plan->tipo_periodo->etiqueta() }}
                                    ({{ $plan->tipo_periodo->totalPeriodos() }} períodos)
                                </span>
                            </td>
                            <td>
                                @if($plan->ciclo)
                                    <span class="label label-warning">{{ $plan->ciclo->nombre }}</span>
                                @else
                                    <span class="text-muted">Genérico</span>
                                @endif
                            </td>
                            <td>
                                @if($plan->materias_count > 0)
                                    <span class="badge bg-blue">{{ $plan->materias_count }} mat.</span>
                                @endif
                                @if($plan->campos_formativos_count > 0)
                                    <span class="badge bg-green">{{ $plan->campos_formativos_count }} campos</span>
                                @endif
                                @if($plan->materias_count === 0 && $plan->campos_formativos_count === 0)
                                    <span class="text-muted">Sin contenido</span>
                                @endif
                            </td>
                            <td>
                                <span class="label label-{{ $plan->activo ? 'success' : 'default' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('educativo.sicap.planes.show', $plan) }}"
                                   class="btn btn-xs btn-info" title="Ver y configurar">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('educativo.sicap.planes.edit', $plan) }}"
                                   class="btn btn-xs btn-default" title="Editar">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-xs btn-danger btn-eliminar"
                                        data-id="{{ $plan->id }}"
                                        data-nombre="{{ $plan->nombre }}"
                                        title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay planes registrados.
                                <a href="{{ route('educativo.sicap.planes.create') }}">Crear el primero</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $(document).on('click', '.btn-eliminar', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');
        const fila   = $(this).closest('tr');

        if (!confirm('¿Eliminar el plan "' + nombre + '"?')) return;

        $.ajax({
            url   : '/educativo/sicap/planes/' + id,
            method: 'DELETE',
            success: () => fila.fadeOut(300, () => fila.remove()),
            error  : xhr => alert(xhr.responseJSON?.message ?? 'No se pudo eliminar el plan.')
        });
    });
});
</script>
@endpush
