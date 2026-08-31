@extends('layouts.educativo')

@section('page_title', 'Escalas de evaluación')
@section('page_subtitle', 'SICAP')

@section('breadcrumb')
    <li>SICAP</li>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sliders"></i> Escalas de evaluación</h3>
                <div class="box-tools">
                    <a href="{{ route('educativo.sicap.escalas.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Nueva escala
                    </a>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-bordered table-hover table-escalas">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Rango</th>
                            <th>Aprobatorio</th>
                            <th>Planes</th>
                            <th>Estado</th>
                            <th style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($escalas as $escala)
                        <tr>
                            <td>
                                <a href="{{ route('educativo.sicap.escalas.show', $escala) }}">
                                    {{ $escala->nombre }}
                                </a>
                            </td>
                            <td>
                                <span class="label label-{{ $escala->esLiteral() ? 'warning' : 'primary' }}">
                                    {{ $escala->tipo->etiqueta() }}
                                </span>
                            </td>
                            <td>
                                @if($escala->esNumerica())
                                    {{ $escala->valor_minimo }} – {{ $escala->valor_maximo }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($escala->valor_aprobatorio)
                                    {{ $escala->valor_aprobatorio }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge">{{ $escala->planes_estudios_count }}</span>
                            </td>
                            <td>
                                <span class="label label-{{ $escala->activa ? 'success' : 'default' }}">
                                    {{ $escala->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('educativo.sicap.escalas.show', $escala) }}"
                                   class="btn btn-xs btn-info" title="Ver detalle">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="{{ route('educativo.sicap.escalas.edit', $escala) }}"
                                   class="btn btn-xs btn-default" title="Editar">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-xs btn-danger btn-eliminar"
                                        data-id="{{ $escala->id }}"
                                        data-nombre="{{ $escala->nombre }}"
                                        title="Eliminar">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No hay escalas registradas.
                                <a href="{{ route('educativo.sicap.escalas.create') }}">Crear la primera</a>.
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
    // Eliminar escala
    $(document).on('click', '.btn-eliminar', function () {
        const id     = $(this).data('id');
        const nombre = $(this).data('nombre');
        const fila   = $(this).closest('tr');

        if (!confirm('¿Eliminar la escala "' + nombre + '"? Esta acción no se puede deshacer.')) return;

        $.ajax({
            url: '/educativo/sicap/escalas/' + id,
            method: 'DELETE',
            success: function (res) {
                fila.fadeOut(300, () => fila.remove());
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.message ?? 'No se pudo eliminar la escala.');
            }
        });
    });
});
</script>
@endpush
