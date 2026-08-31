@extends('layouts.educativo')

@section('page_title', $plan->nombre)
@section('page_subtitle', 'Plan de estudio · ' . $plan->nivel->nombre)

@section('breadcrumb')
    <li>SICAP</li>
    <li><a href="{{ route('educativo.sicap.planes.index') }}">Planes</a></li>
@endsection

@section('content')
<div class="row">

    {{-- Datos del plan --}}
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-book"></i> Datos del plan</h3>
                <div class="box-tools">
                    <a href="{{ route('educativo.sicap.planes.edit', $plan) }}"
                       class="btn btn-xs btn-default">
                        <i class="fa fa-pencil"></i> Editar
                    </a>
                </div>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Nivel</dt>
                    <dd>{{ $plan->nivel->nombre }}</dd>
                    <dt>Escala</dt>
                    <dd>{{ $plan->escala->nombre }}</dd>
                    <dt>Periodicidad</dt>
                    <dd>
                        <span class="label label-info">
                            {{ $plan->tipo_periodo->etiqueta() }}
                            ({{ $plan->tipo_periodo->totalPeriodos() }} {{ $plan->tipo_periodo->nombrePeriodo() }}s)
                        </span>
                    </dd>
                    <dt>Ciclo</dt>
                    <dd>
                        @if($plan->ciclo)
                            <span class="label label-warning">{{ $plan->ciclo->nombre }}</span>
                        @else
                            <em class="text-muted">Genérico</em>
                        @endif
                    </dd>
                    <dt>Estado</dt>
                    <dd>
                        <span class="label label-{{ $plan->activo ? 'success' : 'default' }}">
                            {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Materias (Primaria, Secundaria, Preparatoria) --}}
    @if($plan->materias->isNotEmpty() || !$plan->escala->esLiteral())
    <div class="col-md-8">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-list"></i>
                    Materias
                    <small class="text-muted">({{ $plan->materias->count() }})</small>
                </h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-success btn-sm btn-nueva-materia">
                        <i class="fa fa-plus"></i> Agregar materia
                    </button>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table table-bordered table-hover" id="tabla-materias">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Nombre</th>
                            <th>Clave SEP</th>
                            <th>Tipo</th>
                            <th style="width:60px;">Hrs/sem</th>
                            <th>Estado</th>
                            <th style="width:80px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plan->materias as $materia)
                        <tr data-id="{{ $materia->id }}">
                            <td>{{ $materia->orden }}</td>
                            <td>{{ $materia->nombre }}</td>
                            <td>{{ $materia->clave_sep ?? '—' }}</td>
                            <td>
                                <span class="label {{ $materia->tipo->badgeClass() }}">
                                    {{ $materia->tipo->etiqueta() }}
                                </span>
                            </td>
                            <td>{{ $materia->horas_semanales }}</td>
                            <td>
                                <span class="label label-{{ $materia->activa ? 'success' : 'default' }}">
                                    {{ $materia->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-default btn-editar-materia"
                                        data-materia='@json($materia)'>
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-xs btn-danger btn-eliminar-materia"
                                        data-id="{{ $materia->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="fila-sin-materias">
                            <td colspan="7" class="text-center text-muted">
                                Sin materias. Agrega la primera.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Campos Formativos (Preescolar / escala literal) --}}
    @if($plan->escala->esLiteral())
    <div class="col-md-8">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-file-text-o"></i>
                    Campos Formativos
                    <small class="text-muted">({{ $plan->camposFormativos->count() }})</small>
                </h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-warning btn-sm btn-nuevo-campo">
                        <i class="fa fa-plus"></i> Agregar campo
                    </button>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table table-bordered table-hover" id="tabla-campos">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Nombre</th>
                            <th>Máx. caracteres</th>
                            <th>Estado</th>
                            <th style="width:80px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plan->camposFormativos as $campo)
                        <tr data-id="{{ $campo->id }}">
                            <td>{{ $campo->orden }}</td>
                            <td>{{ $campo->nombre }}</td>
                            <td>{{ number_format($campo->max_caracteres) }}</td>
                            <td>
                                <span class="label label-{{ $campo->activo ? 'success' : 'default' }}">
                                    {{ $campo->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-default btn-editar-campo"
                                        data-campo='@json($campo)'>
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button class="btn btn-xs btn-danger btn-eliminar-campo"
                                        data-id="{{ $campo->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="fila-sin-campos">
                            <td colspan="5" class="text-center text-muted">
                                Sin campos formativos. Agrega el primero.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Modal: Materia --}}
<div class="modal fade" id="modal-materia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-materia-titulo">Agregar materia</h4>
            </div>
            <form id="form-materia">
                <div class="modal-body">
                    <input type="hidden" id="materia-id">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="materia-nombre" class="form-control"
                               placeholder="Ej: Matemáticas" maxlength="150" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select id="materia-tipo" class="form-control" required>
                                    @foreach(\App\Enums\TipoMateria::cases() as $tipo)
                                    <option value="{{ $tipo->value }}">{{ $tipo->etiqueta() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Clave SEP</label>
                                <input type="text" id="materia-clave" class="form-control"
                                       placeholder="Opcional" maxlength="30">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Horas semanales</label>
                                <input type="number" id="materia-horas" class="form-control"
                                       min="0" max="40" value="0">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="materia-activa" checked> Activa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Campo Formativo --}}
<div class="modal fade" id="modal-campo" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-campo-titulo">Agregar campo formativo</h4>
            </div>
            <form id="form-campo">
                <div class="modal-body">
                    <input type="hidden" id="campo-id">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="campo-nombre" class="form-control"
                               placeholder="Ej: Lenguaje y Comunicación" maxlength="150" required>
                    </div>
                    <div class="form-group">
                        <label>Máximo de caracteres <span class="text-danger">*</span></label>
                        <input type="number" id="campo-max" class="form-control"
                               min="50" max="2000" value="500" required>
                        <small class="text-muted">El docente no podrá exceder este límite al capturar.</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="campo-activo" checked> Activo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const planId       = {{ $plan->id }};
    let materiaEditando = null;
    let campoEditando   = null;

    // ════════════════════════════════════════════════════
    // MATERIAS
    // ════════════════════════════════════════════════════

    // Abrir modal nueva materia
    $('.btn-nueva-materia').on('click', function () {
        materiaEditando = null;
        $('#modal-materia-titulo').text('Agregar materia');
        $('#form-materia')[0].reset();
        $('#materia-activa').prop('checked', true);
        $('#modal-materia').modal('show');
    });

    // Abrir modal editar materia
    $(document).on('click', '.btn-editar-materia', function () {
        const m = $(this).data('materia');
        materiaEditando = m.id;
        $('#modal-materia-titulo').text('Editar materia');
        $('#materia-id').val(m.id);
        $('#materia-nombre').val(m.nombre);
        $('#materia-tipo').val(m.tipo);
        $('#materia-clave').val(m.clave_sep ?? '');
        $('#materia-horas').val(m.horas_semanales);
        $('#materia-activa').prop('checked', m.activa == 1);
        $('#modal-materia').modal('show');
    });

    // Guardar materia
    $('#form-materia').on('submit', function (e) {
        e.preventDefault();
        const payload = {
            nombre         : $('#materia-nombre').val(),
            tipo           : $('#materia-tipo').val(),
            clave_sep      : $('#materia-clave').val(),
            horas_semanales: $('#materia-horas').val(),
            activa         : $('#materia-activa').is(':checked') ? 1 : 0,
        };
        const url    = materiaEditando
            ? `/educativo/sicap/planes/${planId}/materias/${materiaEditando}`
            : `/educativo/sicap/planes/${planId}/materias`;
        const method = materiaEditando ? 'PUT' : 'POST';

        $.ajax({ url, method, data: payload,
            success: () => location.reload(),
            error  : xhr => alert(xhr.responseJSON?.message ?? 'Error al guardar.')
        });
    });

    // Eliminar materia
    $(document).on('click', '.btn-eliminar-materia', function () {
        const id   = $(this).data('id');
        const fila = $(this).closest('tr');
        if (!confirm('¿Eliminar esta materia?')) return;
        $.ajax({
            url   : `/educativo/sicap/planes/${planId}/materias/${id}`,
            method: 'DELETE',
            success: () => fila.fadeOut(300, () => fila.remove()),
            error  : xhr => alert(xhr.responseJSON?.message ?? 'Error al eliminar.')
        });
    });

    // ════════════════════════════════════════════════════
    // CAMPOS FORMATIVOS
    // ════════════════════════════════════════════════════

    $('.btn-nuevo-campo').on('click', function () {
        campoEditando = null;
        $('#modal-campo-titulo').text('Agregar campo formativo');
        $('#form-campo')[0].reset();
        $('#campo-max').val(500);
        $('#campo-activo').prop('checked', true);
        $('#modal-campo').modal('show');
    });

    $(document).on('click', '.btn-editar-campo', function () {
        const c = $(this).data('campo');
        campoEditando = c.id;
        $('#modal-campo-titulo').text('Editar campo formativo');
        $('#campo-id').val(c.id);
        $('#campo-nombre').val(c.nombre);
        $('#campo-max').val(c.max_caracteres);
        $('#campo-activo').prop('checked', c.activo == 1);
        $('#modal-campo').modal('show');
    });

    $('#form-campo').on('submit', function (e) {
        e.preventDefault();
        const payload = {
            nombre        : $('#campo-nombre').val(),
            max_caracteres: $('#campo-max').val(),
            activo        : $('#campo-activo').is(':checked') ? 1 : 0,
        };
        const url    = campoEditando
            ? `/educativo/sicap/planes/${planId}/campos/${campoEditando}`
            : `/educativo/sicap/planes/${planId}/campos`;
        const method = campoEditando ? 'PUT' : 'POST';

        $.ajax({ url, method, data: payload,
            success: () => location.reload(),
            error  : xhr => alert(xhr.responseJSON?.message ?? 'Error al guardar.')
        });
    });

    $(document).on('click', '.btn-eliminar-campo', function () {
        const id   = $(this).data('id');
        const fila = $(this).closest('tr');
        if (!confirm('¿Eliminar este campo formativo?')) return;
        $.ajax({
            url   : `/educativo/sicap/planes/${planId}/campos/${id}`,
            method: 'DELETE',
            success: () => fila.fadeOut(300, () => fila.remove()),
            error  : xhr => alert(xhr.responseJSON?.message ?? 'Error al eliminar.')
        });
    });
});
</script>
@endpush
