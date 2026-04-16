@extends('layouts.master')

@section('page_title', 'Familias')
@section('page_subtitle', 'Gestión de familias')

@section('breadcrumb')
    <li class="active">Familias</li>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════
   BARRA DE ESTADÍSTICAS
══════════════════════════════════════════ */
.fam-stats {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.fam-stat-card {
    flex: 1;
    min-width: 130px;
    background: #fff;
    border: 1px solid #e4eaf0;
    border-top: 3px solid #3c8dbc;
    border-radius: 6px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.fam-stat-card.verde  { border-top-color: #00a65a; }
.fam-stat-card.naranja { border-top-color: #f39c12; }
.fam-stat-card.gris   { border-top-color: #b0bec5; }
.fam-stat-icon {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: #eaf3fb; flex-shrink: 0;
}
.fam-stat-icon.verde   { background: #e8f8f0; }
.fam-stat-icon.naranja { background: #fef6e7; }
.fam-stat-icon.gris    { background: #f4f6f7; }
.fam-stat-num  { font-size: 26px; font-weight: 800; line-height: 1; color: #222; }
.fam-stat-lbl  { font-size: 11px; color: #999; margin-top: 2px; text-transform: uppercase; letter-spacing: .04em; }

/* ══════════════════════════════════════════
   BARRA DE BÚSQUEDA / ACCIONES
══════════════════════════════════════════ */
.fam-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-bottom: 1px solid #e8ecf0;
    background: #f9fafb;
    border-radius: 4px 4px 0 0;
    flex-wrap: wrap;
}
.fam-search-wrap {
    flex: 1;
    min-width: 220px;
    max-width: 420px;
    position: relative;
}
.fam-search-wrap .form-control {
    padding-left: 38px;
    border-radius: 20px !important;
    border: 1px solid #d0dbe6;
    height: 36px;
    font-size: 13px;
    background: #fff;
    box-shadow: none;
    transition: border-color .15s, box-shadow .15s;
}
.fam-search-wrap .form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 3px rgba(60,141,188,.12);
}
.fam-search-wrap .fam-search-icon {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: #aab;
    font-size: 14px;
    pointer-events: none;
}
.fam-search-clear {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    color: #aab;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    line-height: 1;
}
.fam-search-clear:hover { color: #dd4b39; }
.fam-count-badge {
    background: #e8f0fb;
    color: #3c8dbc;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 12px;
    white-space: nowrap;
}

/* ══════════════════════════════════════════
   TABLA DE FAMILIAS
══════════════════════════════════════════ */
.fam-table { margin: 0; border-collapse: separate; border-spacing: 0; width: 100%; }
.fam-table thead tr th {
    background: #f4f6f8;
    color: #6b7a8d;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 10px 16px;
    border-bottom: 2px solid #e0e6ed;
    border-top: none;
    white-space: nowrap;
}
.fam-table tbody tr {
    cursor: pointer;
    border-bottom: 1px solid #f0f3f7;
    transition: background .12s;
}
.fam-table tbody tr:last-child { border-bottom: none; }
.fam-table tbody tr:hover td { background: #f0f7ff !important; }
.fam-table td {
    padding: 12px 16px;
    vertical-align: middle;
    font-size: 13px;
}

/* Nombre de familia */
.fam-nombre-wrap { display: flex; align-items: center; gap: 12px; }
.fam-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #3c8dbc, #2c6fad);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 15px; font-weight: 800; color: #fff;
    letter-spacing: -.02em;
    box-shadow: 0 2px 6px rgba(60,141,188,.3);
}
.fam-avatar.inactiva {
    background: linear-gradient(135deg, #bdbdbd, #9e9e9e);
    box-shadow: 0 2px 6px rgba(0,0,0,.1);
}
.fam-nombre     { font-size: 14px; font-weight: 700; color: #1a2634; line-height: 1.2; }
.fam-obs        { font-size: 11px; color: #aaa; margin-top: 2px; }

/* Alumnos */
.fam-alumnos-lista { display: flex; flex-direction: column; gap: 3px; }
.fam-alumno-tag {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12px; color: #555;
}
.fam-alumno-tag .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #00a65a; flex-shrink: 0;
}
.fam-alumno-tag .dot.baja { background: #f39c12; }
.fam-mas        { font-size: 11px; color: #aaa; padding-top: 1px; }

/* Contacto */
.fam-ctc-nombre { font-size: 13px; font-weight: 600; color: #2a3542; }
.fam-ctc-tel    { font-size: 12px; color: #3c8dbc; margin-top: 2px; }
.fam-ctc-tel i  { margin-right: 3px; }

/* Estado */
.fam-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 12px; letter-spacing: .02em; white-space: nowrap;
}
.fam-badge-activa   { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
.fam-badge-inactiva { background: #f5f5f5; color: #888; border: 1px solid #e0e0e0; }

/* Acciones */
.fam-acciones { display: flex; gap: 4px; justify-content: center; }

/* ══════════════════════════════════════════
   EMPTY STATE
══════════════════════════════════════════ */
.fam-empty {
    text-align: center;
    padding: 60px 20px;
    color: #ccc;
}
.fam-empty i  { font-size: 52px; display: block; margin-bottom: 16px; color: #dde4ea; }
.fam-empty h4 { font-size: 16px; color: #999; margin: 0 0 8px; }
.fam-empty p  { font-size: 13px; color: #bbb; margin: 0 0 20px; }

/* ══════════════════════════════════════════
   PAGINACIÓN
══════════════════════════════════════════ */
.fam-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-top: 1px solid #edf1f5;
    background: #f9fafb;
    flex-wrap: wrap;
    gap: 8px;
}
.fam-footer-info { font-size: 12px; color: #aaa; }
.fam-footer .pagination { margin: 0; }
.fam-footer .pagination > li > a,
.fam-footer .pagination > li > span {
    border-color: #dde4eb;
    color: #3c8dbc;
    font-size: 12px;
    padding: 4px 10px;
}
.fam-footer .pagination > .active > a,
.fam-footer .pagination > .active > span {
    background: #3c8dbc;
    border-color: #3c8dbc;
}

/* Skeleton loader */
.skel {
    height: 13px; border-radius: 4px; margin-bottom: 9px;
    background: linear-gradient(90deg,#f0f0f0 25%,#e4e4e4 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.2s infinite;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Modal */
#modal-familia .modal-dialog { width: 540px; max-width: 95vw; }
#modal-familia .modal-header {
    background: linear-gradient(135deg,#2c6fad,#3c8dbc);
    color: #fff; border-radius: 4px 4px 0 0;
}
#modal-familia .modal-title  { font-size: 15px; font-weight: 700; }
#modal-familia .modal-header .close { color: #fff; opacity: .8; }
#modal-body { max-height: 72vh; overflow-y: auto; padding: 0; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible" style="border-radius:6px;">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fa fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- ══ ESTADÍSTICAS ══ --}}
<div class="fam-stats">
    <div class="fam-stat-card">
        <div class="fam-stat-icon">
            <i class="fa fa-home" style="color:#3c8dbc;font-size:18px;"></i>
        </div>
        <div>
            <div class="fam-stat-num">{{ $familias->total() }}</div>
            <div class="fam-stat-lbl">Total familias</div>
        </div>
    </div>
    <div class="fam-stat-card verde">
        <div class="fam-stat-icon verde">
            <i class="fa fa-check-circle" style="color:#00a65a;font-size:18px;"></i>
        </div>
        <div>
            <div class="fam-stat-num">{{ $totalActivas ?? $familias->where('activo', true)->count() }}</div>
            <div class="fam-stat-lbl">Familias activas</div>
        </div>
    </div>
    <div class="fam-stat-card naranja">
        <div class="fam-stat-icon naranja">
            <i class="fa fa-graduation-cap" style="color:#f39c12;font-size:18px;"></i>
        </div>
        <div>
            <div class="fam-stat-num">{{ $totalAlumnos ?? '-' }}</div>
            <div class="fam-stat-lbl">Alumnos activos</div>
        </div>
    </div>
</div>

{{-- ══ PANEL PRINCIPAL ══ --}}
<div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

    {{-- Toolbar --}}
    <div class="fam-toolbar">
        {{-- Búsqueda --}}
        <form method="GET" action="{{ route('familias.index') }}" style="flex:1;min-width:0;display:flex;align-items:center;gap:8px;">
            <div class="fam-search-wrap">
                <i class="fa fa-search fam-search-icon"></i>
                <input type="text" name="q" class="form-control"
                       placeholder="Buscar por nombre, alumno o teléfono…"
                       value="{{ request('q') }}"
                       autocomplete="off">
                @if(request('q'))
                <a href="{{ route('familias.index') }}" class="fam-search-clear" title="Limpiar búsqueda">
                    <i class="fa fa-times-circle"></i>
                </a>
                @endif
            </div>
            <button type="submit" class="btn btn-primary btn-flat btn-sm" style="border-radius:20px;padding:5px 16px;">
                <i class="fa fa-search"></i> Buscar
            </button>
        </form>

        {{-- Filtro estado --}}
        <div class="btn-group" style="flex-shrink:0;">
            <a href="{{ route('familias.index', array_merge(request()->except('activo','page'), [])) }}"
               class="btn btn-sm btn-flat {{ !request()->filled('activo') ? 'btn-primary' : 'btn-default' }}"
               style="border-radius:4px 0 0 4px;">
                Todas
            </a>
            <a href="{{ route('familias.index', array_merge(request()->except('activo','page'), ['activo'=>1])) }}"
               class="btn btn-sm btn-flat {{ request('activo') === '1' ? 'btn-success' : 'btn-default' }}">
                Activas
            </a>
            <a href="{{ route('familias.index', array_merge(request()->except('activo','page'), ['activo'=>0])) }}"
               class="btn btn-sm btn-flat {{ request('activo') === '0' ? 'btn-default active' : 'btn-default' }}"
               style="border-radius:0 4px 4px 0;">
                Inactivas
            </a>
        </div>

        {{-- Contador --}}
        @if($familias->total() > 0)
        <span class="fam-count-badge">
            <i class="fa fa-home"></i>
            {{ $familias->total() }} familia{{ $familias->total() != 1 ? 's' : '' }}
            @if(request('q')) · «{{ request('q') }}» @endif
        </span>
        @endif

        {{-- Nueva familia --}}
        @can('administrador')
        <a href="{{ route('familias.create') }}"
           class="btn btn-success btn-flat btn-sm"
           style="border-radius:20px;padding:5px 16px;white-space:nowrap;">
            <i class="fa fa-plus"></i> Nueva familia
        </a>
        @endcan
    </div>

    {{-- Tabla --}}
    <div class="box-body no-padding">
        <table class="fam-table">
            <thead>
                <tr>
                    <th style="width:28%;">Familia</th>
                    <th style="width:22%;">Alumnos</th>
                    <th style="width:24%;">Contacto principal</th>
                    <th style="width:10%;">Estado</th>
                    <th style="width:16%;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($familias as $familia)
            @php
                $inicial  = mb_strtoupper(mb_substr($familia->apellido_familia, 0, 1));
                $activos  = $familia->alumnos_activos_count ?? 0;
                $total    = $familia->alumnos_count ?? 0;
                $ctc      = $familia->contactos->sortBy('pivot.orden')->first();
            @endphp
            <tr class="fila-familia"
                data-id="{{ $familia->id }}"
                data-nombre="{{ $familia->apellido_familia }}"
                title="Clic para ver detalle">

                {{-- NOMBRE / FAMILIA --}}
                <td>
                    <div class="fam-nombre-wrap">
                        <div class="fam-avatar {{ $familia->activo ? '' : 'inactiva' }}">
                            {{ $inicial }}
                        </div>
                        <div>
                            <div class="fam-nombre">
                                Familia {{ $familia->apellido_familia }}
                            </div>
                            @if($familia->observaciones)
                            <div class="fam-obs">{{ Str::limit($familia->observaciones, 50) }}</div>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- ALUMNOS --}}
                <td>
                    @if($total > 0)
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                        <span style="background:#3c8dbc;color:#fff;font-size:12px;font-weight:700;
                                     padding:2px 9px;border-radius:10px;">
                            {{ $activos }} activo{{ $activos != 1 ? 's' : '' }}
                        </span>
                        @if($activos < $total)
                        <span style="font-size:11px;color:#bbb;">/ {{ $total }} total</span>
                        @endif
                    </div>
                    <div class="fam-alumnos-lista">
                        @foreach($familia->alumnos->take(2) as $a)
                        <div class="fam-alumno-tag">
                            <span class="dot {{ $a->estado !== 'activo' ? 'baja' : '' }}"></span>
                            {{ $a->nombre }} {{ $a->ap_paterno }}
                        </div>
                        @endforeach
                        @if($familia->alumnos->count() > 2)
                        <div class="fam-mas">
                            <i class="fa fa-ellipsis-h"></i>
                            +{{ $familia->alumnos->count() - 2 }} más
                        </div>
                        @endif
                    </div>
                    @else
                    <span style="font-size:12px;color:#ccc;font-style:italic;">
                        <i class="fa fa-user-times"></i> Sin alumnos
                    </span>
                    @endif
                </td>

                {{-- CONTACTO PRINCIPAL --}}
                <td>
                    @if($ctc)
                    <div class="fam-ctc-nombre">
                        {{ $ctc->nombre }} {{ $ctc->ap_paterno }}
                    </div>
                    @if($ctc->telefono_celular)
                    <div class="fam-ctc-tel">
                        <i class="fa fa-mobile"></i>{{ $ctc->telefono_celular }}
                    </div>
                    @endif
                    @else
                    <span style="font-size:12px;color:#ccc;font-style:italic;">—</span>
                    @endif
                </td>

                {{-- ESTADO --}}
                <td>
                    <span class="fam-badge {{ $familia->activo ? 'fam-badge-activa' : 'fam-badge-inactiva' }}">
                        <i class="fa fa-circle" style="font-size:7px;"></i>
                        {{ $familia->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>

                {{-- ACCIONES --}}
                <td onclick="event.stopPropagation()">
                    <div class="fam-acciones">
                        <a href="{{ route('familias.show', $familia->id) }}"
                           class="btn btn-default btn-xs btn-flat"
                           title="Ver ficha completa"
                           style="border-radius:4px;">
                            <i class="fa fa-eye"></i>
                        </a>
                        @can('administrador')
                        <a href="{{ route('familias.edit', $familia->id) }}"
                           class="btn btn-primary btn-xs btn-flat"
                           title="Editar"
                           style="border-radius:4px;">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endcan
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="5">
                    <div class="fam-empty">
                        <i class="fa fa-home"></i>
                        @if(request('q'))
                        <h4>Sin resultados</h4>
                        <p>No se encontraron familias para <strong>«{{ request('q') }}»</strong></p>
                        <a href="{{ route('familias.index') }}" class="btn btn-default btn-sm" style="border-radius:20px;">
                            <i class="fa fa-times"></i> Limpiar búsqueda
                        </a>
                        @else
                        <h4>No hay familias registradas</h4>
                        <p>Comienza registrando la primera familia del sistema.</p>
                        @can('administrador')
                        <a href="{{ route('familias.create') }}" class="btn btn-success btn-sm" style="border-radius:20px;">
                            <i class="fa fa-plus"></i> Registrar primera familia
                        </a>
                        @endcan
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($familias->total() > 0)
    <div class="fam-footer">
        <span class="fam-footer-info">
            Mostrando <strong>{{ $familias->firstItem() }}</strong>–<strong>{{ $familias->lastItem() }}</strong>
            de <strong>{{ $familias->total() }}</strong> familia(s)
            @if(request('q'))
            para <em>«{{ request('q') }}»</em>
            @endif
        </span>
        <div>
            {{ $familias->appends(request()->query())->links() }}
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════
     MODAL DE DETALLE RÁPIDO
══════════════════════════════════════ --}}
<div class="modal fade" id="modal-familia" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border:none;border-radius:8px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.18);">

            <div class="modal-header" style="padding:14px 20px;">
                <button type="button" class="close" data-dismiss="modal"
                        style="color:#fff;opacity:.85;font-size:20px;">&times;</button>
                <h4 class="modal-title">
                    <i class="fa fa-home" style="margin-right:8px;opacity:.85;"></i>
                    Familia&nbsp;<span id="modal-titulo" style="font-weight:800;">—</span>
                </h4>
            </div>

            <div class="modal-body" id="modal-body" style="padding:0;"></div>

            <div class="modal-footer" style="background:#f9fafb;border-top:1px solid #eee;padding:10px 18px;">
                <a href="#" id="modal-btn-ver" class="btn btn-default btn-sm btn-flat" style="border-radius:4px;">
                    <i class="fa fa-external-link"></i> Ficha completa
                </a>
                @can('administrador')
                <a href="#" id="modal-btn-editar" class="btn btn-primary btn-sm btn-flat" style="border-radius:4px;">
                    <i class="fa fa-pencil"></i> Editar
                </a>
                @endcan
                <button type="button" class="btn btn-default btn-sm btn-flat"
                        data-dismiss="modal" style="border-radius:4px;">
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

    // ── Abrir modal al clic en fila ─────────────────────────
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
                    '<pre style="font-size:11px;max-height:300px;overflow:auto;background:#f9f9f9;' +
                    'padding:10px;border:1px solid #ddd;">' +
                    $('<div>').text(detalle).html() +
                    '</pre>' +
                    '</div>'
                );
            });
    });

    $('#modal-familia').on('hidden.bs.modal', function() {
        $('#modal-body').html('');
    });

    // ── Skeleton loader ─────────────────────────────────────
    function skeleton() {
        var s = '<div style="padding:20px 18px;">';
        s += '<div class="skel" style="width:45%;height:18px;"></div>';
        s += '<div class="skel" style="width:72%;"></div>';
        s += '<div class="skel" style="width:55%;"></div>';
        s += '<div style="height:12px;"></div>';
        s += '<div class="skel" style="width:38%;height:16px;"></div>';
        s += '<div class="skel" style="width:82%;"></div>';
        s += '<div class="skel" style="width:68%;"></div>';
        s += '<div class="skel" style="width:78%;"></div>';
        s += '</div>';
        return s;
    }

});
</script>
@endpush
