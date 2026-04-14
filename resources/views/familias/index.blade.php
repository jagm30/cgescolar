@extends('layouts.master')

@section('page_title', 'Familias')
@section('page_subtitle', 'Gestión de familias')

@section('breadcrumb')
    <li class="active">Familias</li>
@endsection

@push('styles')
<style>
.fila-familia { cursor:pointer; }
.fila-familia:hover td { background:#f0f7ff !important; }

#modal-familia .modal-dialog { width:520px; max-width:95vw; }
#modal-familia .modal-header { background:#3c8dbc; color:#fff; border-radius:3px 3px 0 0; }
#modal-familia .modal-title  { font-size:15px; }
#modal-familia .modal-header .close { color:#fff; opacity:.8; }
#modal-body { max-height:70vh; overflow-y:auto; }

/* Skeleton */
.skel { height:13px; border-radius:4px; margin-bottom:9px;
        background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
        background-size:200% 100%; animation:shimmer 1.2s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="box box-primary">

    <div class="box-header with-border">
        <div class="row">
            <div class="col-md-8">
                <form method="GET" action="{{ route('familias.index') }}">
                    <div class="input-group" style="max-width:380px;">
                        <input type="text" name="q" class="form-control"
                               placeholder="Buscar por nombre, alumno o teléfono..."
                               value="{{ request('q') }}">
                        <span class="input-group-btn">
                            <button class="btn btn-primary btn-flat" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                            @if(request('q'))
                            <a href="{{ route('familias.index') }}"
                               class="btn btn-default btn-flat" title="Limpiar">
                                <i class="fa fa-times"></i>
                            </a>
                            @endif
                        </span>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-right">
                @can('administrador')
                <a href="{{ route('familias.create') }}" class="btn btn-success btn-flat">
                    <i class="fa fa-plus"></i> Nueva familia
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="box-body no-padding">
        <table class="table table-striped" style="margin:0;">
            <thead>
                <tr>
                    <th style="width:30%;">Familia</th>
                    <th style="width:22%;">Alumnos</th>
                    <th style="width:23%;">Contacto principal</th>
                    <th style="width:10%;">Estado</th>
                    <th style="width:15%;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($familias as $familia)
            <tr class="fila-familia" data-id="{{ $familia->id }}"
                data-nombre="{{ $familia->apellido_familia }}"
                title="Click para ver detalle">

                <td>
                    <strong style="font-size:13px;">{{ $familia->apellido_familia }}</strong>
                    @if($familia->observaciones)
                    <br><small class="text-muted">{{ Str::limit($familia->observaciones, 55) }}</small>
                    @endif
                </td>

                <td>
                    @php $activos = $familia->alumnos_activos_count ?? 0; $total = $familia->alumnos_count ?? 0; @endphp
                    @if($total > 0)
                        <span class="badge bg-blue">{{ $activos }}</span>
                        @if($activos < $total)<small class="text-muted"> / {{ $total }}</small>@endif
                        <br>
                        @foreach($familia->alumnos->take(2) as $a)
                            <small class="text-muted">{{ $a->nombre }} {{ $a->ap_paterno }}</small><br>
                        @endforeach
                        @if($familia->alumnos->count() > 2)
                            <small class="text-muted">+{{ $familia->alumnos->count()-2 }} más</small>
                        @endif
                    @else
                        <span class="text-muted" style="font-size:12px;">Sin alumnos</span>
                    @endif
                </td>

                <td>
                    @php $ctc = $familia->contactos->sortBy('pivot.orden')->first(); @endphp
                    @if($ctc)
                        <strong style="font-size:13px;">{{ $ctc->nombre }} {{ $ctc->ap_paterno }}</strong>
                        @if($ctc->telefono_celular)
                        <br><small><i class="fa fa-mobile text-muted"></i> {{ $ctc->telefono_celular }}</small>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                <td>
                    <span class="label label-{{ $familia->activo ? 'success' : 'default' }}">
                        {{ $familia->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>

                {{-- Detener clic en acciones para no abrir modal --}}
                <td class="text-center" onclick="event.stopPropagation()">
                    <a href="{{ route('familias.show', $familia->id) }}"
                       class="btn btn-default btn-xs btn-flat" title="Ver página completa">
                        <i class="fa fa-external-link"></i>
                    </a>
                    @can('administrador')
                    <a href="{{ route('familias.edit', $familia->id) }}"
                       class="btn btn-primary btn-xs btn-flat" title="Editar">
                        <i class="fa fa-pencil"></i>
                    </a>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding:40px;">
                    <i class="fa fa-home fa-3x text-muted" style="display:block;margin-bottom:10px;"></i>
                    <strong class="text-muted">No hay familias registradas</strong>
                    @if(request('q'))
                    <br><small class="text-muted">Sin resultados para "{{ request('q') }}"</small>
                    <br><br>
                    <a href="{{ route('familias.index') }}" class="btn btn-default btn-sm">Ver todas</a>
                    @else
                    <br><br>
                    @can('administrador')
                    <a href="{{ route('familias.create') }}" class="btn btn-success btn-sm">
                        <i class="fa fa-plus"></i> Registrar primera familia
                    </a>
                    @endcan
                    @endif
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($familias->total() > 0)
    <div class="box-footer clearfix">
        <small class="text-muted">
            {{ $familias->firstItem() }}–{{ $familias->lastItem() }}
            de {{ $familias->total() }} familia(s)
            @if(request('q')) para "<strong>{{ request('q') }}</strong>" @endif
        </small>
        <div class="pull-right">
            {{ $familias->appends(request()->query())->links() }}
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════
     MODAL DE DETALLE
══════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-familia" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-home"></i>
                    <span id="modal-titulo">—</span>
                </h4>
            </div>

            <div class="modal-body" id="modal-body" style="padding:0;">
            </div>

            <div class="modal-footer">
                <a href="#" id="modal-btn-ver" class="btn btn-default btn-sm">
                    <i class="fa fa-external-link"></i> Página completa
                </a>
                @can('administrador')
                <a href="#" id="modal-btn-editar" class="btn btn-primary btn-sm">
                    <i class="fa fa-pencil"></i> Editar
                </a>
                @endcan
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function() {

    // ── Abrir modal al clic en fila ───────────────────────
    $(document).on('click', '.fila-familia', function() {
        var id     = $(this).data('id');
        var nombre = $(this).data('nombre');

        $('#modal-titulo').text(nombre);
        $('#modal-btn-ver').attr('href', '/familias/' + id);
        $('#modal-btn-editar').attr('href', '/familias/' + id + '/edit');
        $('#modal-body').html(skeleton());
        $('#modal-familia').modal('show');

        $.get('/familias/' + id, { _modal: 1 })
            .done(function(html) { $('#modal-body').html(html); })
            .fail(function(xhr) {
                var detalle = xhr.responseText
                    ? xhr.responseText.substring(0, 800)
                    : 'Sin respuesta del servidor. Código: ' + xhr.status;
                $('#modal-body').html(
                    '<div style="margin:16px;">' +
                    '<div class="alert alert-danger">' +
                    '<i class="fa fa-exclamation-circle"></i> ' +
                    '<strong>Error ' + xhr.status + '</strong> al cargar los datos.' +
                    '</div>' +
                    '<pre style="font-size:11px;max-height:300px;overflow:auto;background:#f9f9f9;padding:10px;border:1px solid #ddd;">' +
                    $('<div>').text(detalle).html() +
                    '</pre>' +
                    '</div>'
                );
            });
    });

    $('#modal-familia').on('hidden.bs.modal', function() {
        $('#modal-body').html('');
    });

    function skeleton() {
        var s = '<div style="padding:20px;">';
        s += '<div class="skel" style="width:45%;height:18px;"></div>';
        s += '<div class="skel" style="width:70%;"></div>';
        s += '<div class="skel" style="width:50%;"></div>';
        s += '<div style="height:14px;"></div>';
        s += '<div class="skel" style="width:35%;height:16px;"></div>';
        s += '<div class="skel" style="width:80%;"></div>';
        s += '<div class="skel" style="width:65%;"></div>';
        s += '<div class="skel" style="width:75%;"></div>';
        s += '</div>';
        return s;
    }

});
</script>
@endpush
