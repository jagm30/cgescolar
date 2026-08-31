@extends('layouts.educativo')

@section('page_title', $escala->nombre)
@section('page_subtitle', 'Escala de evaluación')

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.sicap.escalas.index') }}">Escalas</a></li>
@endsection

@section('content')
<div class="row">

    {{-- Datos de la escala --}}
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sliders"></i> Datos generales</h3>
                <div class="box-tools">
                    <a href="{{ route('educativo.sicap.escalas.edit', $escala) }}"
                       class="btn btn-xs btn-default">
                        <i class="fa fa-pencil"></i> Editar
                    </a>
                </div>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Tipo</dt>
                    <dd>
                        <span class="label label-{{ $escala->esLiteral() ? 'warning' : 'primary' }}">
                            {{ $escala->tipo->etiqueta() }}
                        </span>
                    </dd>

                    @if($escala->esNumerica())
                    <dt>Rango</dt>
                    <dd>{{ $escala->valor_minimo }} – {{ $escala->valor_maximo }}</dd>
                    <dt>Aprobatorio</dt>
                    <dd>{{ $escala->valor_aprobatorio }}</dd>
                    @endif

                    <dt>Estado</dt>
                    <dd>
                        <span class="label label-{{ $escala->activa ? 'success' : 'default' }}">
                            {{ $escala->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Planes que usan esta escala --}}
        @if($escala->planesEstudios->isNotEmpty())
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-book"></i> Planes que la usan</h3>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-stacked">
                    @foreach($escala->planesEstudios as $plan)
                    <li>
                        <a href="{{ route('educativo.sicap.planes.show', $plan) }}">
                            <i class="fa fa-angle-right"></i>
                            {{ $plan->nombre }}
                            <small class="text-muted">({{ $plan->nivel->nombre }})</small>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    {{-- Criterios (solo escalas literales) --}}
    @if($escala->esLiteral())
    <div class="col-md-8">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list-ol"></i> Criterios de evaluación
                </h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-criterio">
                        <i class="fa fa-plus"></i> Agregar criterio
                    </button>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table table-bordered table-hover" id="tabla-criterios">
                    <thead>
                        <tr>
                            <th style="width:80px;">Orden</th>
                            <th style="width:80px;">Etiqueta</th>
                            <th>Descripción</th>
                            <th style="width:120px;">Valor numérico</th>
                            <th style="width:80px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($escala->criterios as $criterio)
                        <tr data-id="{{ $criterio->id }}">
                            <td>{{ $criterio->orden }}</td>
                            <td><strong>{{ $criterio->etiqueta }}</strong></td>
                            <td>{{ $criterio->descripcion }}</td>
                            <td>{{ $criterio->valor_numerico }}</td>
                            <td>
                                <button class="btn btn-xs btn-default btn-editar-criterio"
                                        data-criterio='@json($criterio)'>
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-xs btn-danger btn-eliminar-criterio"
                                        data-id="{{ $criterio->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="fila-vacia">
                            <td colspan="5" class="text-center text-muted">
                                Agrega los criterios de esta escala descriptiva.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal criterio --}}
    <div class="modal fade" id="modal-criterio" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal-criterio-titulo">Agregar criterio</h4>
                </div>
                <form id="form-criterio">
                    <div class="modal-body">
                        <input type="hidden" id="criterio-id">

                        <div class="form-group">
                            <label>Etiqueta <span class="text-danger">*</span></label>
                            <input type="text" id="criterio-etiqueta" class="form-control"
                                   placeholder="MA" maxlength="20" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción <span class="text-danger">*</span></label>
                            <input type="text" id="criterio-descripcion" class="form-control"
                                   placeholder="Muy Avanzado" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label>Valor numérico <span class="text-danger">*</span></label>
                            <input type="number" id="criterio-valor" class="form-control"
                                   step="0.01" min="0" max="100" required>
                            <small class="text-muted">Equivalente para cálculo de promedios.</small>
                        </div>
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" id="criterio-orden" class="form-control"
                                   min="0" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-guardar-criterio">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@if($escala->esLiteral())
@push('scripts')
<script>
$(function () {
    const escalaId = {{ $escala->id }};
    let criterioEditando = null;

    // ── Abrir modal para nuevo criterio ──────────────────
    $('#modal-criterio').on('show.bs.modal', function (e) {
        if (!e.relatedTarget) return; // se abrió programáticamente (editar)
        criterioEditando = null;
        $('#modal-criterio-titulo').text('Agregar criterio');
        $('#form-criterio')[0].reset();
        $('#criterio-id').val('');
    });

    // ── Editar criterio ──────────────────────────────────
    $(document).on('click', '.btn-editar-criterio', function () {
        const c = $(this).data('criterio');
        criterioEditando = c.id;
        $('#modal-criterio-titulo').text('Editar criterio');
        $('#criterio-id').val(c.id);
        $('#criterio-etiqueta').val(c.etiqueta);
        $('#criterio-descripcion').val(c.descripcion);
        $('#criterio-valor').val(c.valor_numerico);
        $('#criterio-orden').val(c.orden);
        $('#modal-criterio').modal('show');
    });

    // ── Guardar criterio (crear o actualizar) ────────────
    $('#form-criterio').on('submit', function (e) {
        e.preventDefault();

        const payload = {
            etiqueta      : $('#criterio-etiqueta').val(),
            descripcion   : $('#criterio-descripcion').val(),
            valor_numerico: $('#criterio-valor').val(),
            orden         : $('#criterio-orden').val(),
        };

        const url    = criterioEditando
            ? `/educativo/sicap/escalas/${escalaId}/criterios/${criterioEditando}`
            : `/educativo/sicap/escalas/${escalaId}/criterios`;
        const method = criterioEditando ? 'PUT' : 'POST';

        $.ajax({ url, method, data: payload,
            success: () => location.reload(),
            error: xhr => alert(xhr.responseJSON?.message ?? 'Error al guardar.')
        });
    });

    // ── Eliminar criterio ────────────────────────────────
    $(document).on('click', '.btn-eliminar-criterio', function () {
        const id   = $(this).data('id');
        const fila = $(this).closest('tr');

        if (!confirm('¿Eliminar este criterio?')) return;

        $.ajax({
            url   : `/educativo/sicap/escalas/${escalaId}/criterios/${id}`,
            method: 'DELETE',
            success: () => fila.fadeOut(300, () => fila.remove()),
            error  : xhr => alert(xhr.responseJSON?.message ?? 'Error al eliminar.')
        });
    });
});
</script>
@endpush
@endif
