@extends('layouts.master')

@section('page_title', 'Prospectos')
@section('page_subtitle', 'Control de admisiones')

@push('styles')
<style>
/* ── Toolbar ─────────────────────────────────────────── */
.pro-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-bottom: 1px solid #e8ecf0;
    background: #f9fafb;
    border-radius: 4px 4px 0 0;
    flex-wrap: wrap;
}
.pro-search-wrap {
    flex: 1;
    min-width: 200px;
    max-width: 340px;
    position: relative;
}
.pro-search-wrap .form-control {
    padding-left: 36px;
    border-radius: 20px !important;
    border: 1px solid #d0dbe6;
    height: 34px;
    font-size: 13px;
    box-shadow: none;
}
.pro-search-wrap .form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 3px rgba(60,141,188,.12);
}
.pro-search-icon {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #aab; font-size: 13px;
    pointer-events: none;
}
.pro-search-clear {
    position: absolute;
    right: 11px; top: 50%;
    transform: translateY(-50%);
    color: #aab; cursor: pointer;
    font-size: 13px; text-decoration: none;
}
.pro-search-clear:hover { color: #dd4b39; }
.pro-select {
    height: 34px !important;
    border-radius: 6px !important;
    border: 1px solid #d0dbe6 !important;
    font-size: 12px !important;
    padding: 0 8px !important;
    background: #fff !important;
    min-width: 120px;
}

/* ── Tabla ───────────────────────────────────────────── */
.pro-table {
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.pro-table thead th {
    background: #f4f6f8;
    color: #6b7a8d;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 9px 14px;
    border-bottom: 2px solid #e0e6ed;
    border-top: none;
    white-space: nowrap;
}
.pro-table tbody tr {
    cursor: pointer;
    border-bottom: 1px solid #f0f3f7;
    transition: background .1s;
}
.pro-table tbody tr:last-child { border-bottom: none; }
.pro-table tbody tr:hover td { background: #f0f7ff !important; }
.pro-table td {
    padding: 9px 14px;
    vertical-align: middle;
    font-size: 13px;
}
.pro-nombre { font-size: 13px; font-weight: 700; color: #1a2634; }
.pro-sub    { font-size: 11px; color: #aab; margin-top: 1px; }

/* Etapa badges */
.pro-etapa {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 700;
    padding: 2px 9px; border-radius: 12px;
    white-space: nowrap;
}
.pro-etapa-prospecto    { background:#e8f3ff;color:#2c6fad;border:1px solid #b3d4f5; }
.pro-etapa-cita         { background:#e0f7fa;color:#00838f;border:1px solid #b2ebf2; }
.pro-etapa-visita       { background:#e0f2f1;color:#00695c;border:1px solid #b2dfdb; }
.pro-etapa-documentacion{ background:#fff8e6;color:#b45309;border:1px solid #fcd97d; }
.pro-etapa-aceptado     { background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0; }
.pro-etapa-inscrito     { background:#ede7f6;color:#4527a0;border:1px solid #d1c4e9; }
.pro-etapa-no_concretado{ background:#fdecea;color:#b91c1c;border:1px solid #fca5a5; }

/* Footer */
.pro-footer {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-top: 1px solid #edf1f5;
    background: #f9fafb;
    flex-wrap: wrap; gap: 8px;
}
.pro-footer-info { font-size: 12px; color: #aaa; }
.pro-footer .pagination { margin: 0; }
.pro-footer .pagination > li > a,
.pro-footer .pagination > li > span {
    border-color: #dde4eb; color: #3c8dbc;
    font-size: 12px; padding: 4px 10px;
}
.pro-footer .pagination > .active > a,
.pro-footer .pagination > .active > span {
    background: #3c8dbc; border-color: #3c8dbc;
}
</style>
@endpush

@section('content')
@php
    $etapas = [
        'prospecto'      => 'Prospecto',
        'cita'           => 'Cita',
        'visita'         => 'Visita',
        'documentacion'  => 'Documentación',
        'aceptado'       => 'Aceptado',
        'inscrito'       => 'Inscrito',
        'no_concretado'  => 'No concretado',
    ];
@endphp

@if (session('success'))
    <div class="alert alert-success alert-dismissible" style="border-radius:6px;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Hay errores en el formulario.</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ══ ENCABEZADO + STATS ══ --}}
<div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
            box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-user-plus text-blue"></i> Prospectos
        </h4>
        <div style="display:flex;gap:7px;flex-wrap:wrap;">
            <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                         padding:2px 10px;font-size:12px;font-weight:600;">
                <i class="fa fa-users"></i> {{ $prospectos->total() }} total
            </span>
            @if (request('etapa'))
                <span style="background:#fff8e6;color:#b45309;border:1px solid #fcd97d;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-filter"></i> {{ $etapas[request('etapa')] ?? request('etapa') }}
                </span>
            @endif
            <a href="{{ route('prospectos.metricas', ['ciclo_id' => $cicloId]) }}"
               style="background:#f5eef8;color:#7d3c98;border:1px solid #ebdef0;border-radius:20px;
                      padding:2px 10px;font-size:12px;font-weight:600;text-decoration:none;">
                <i class="fa fa-bar-chart"></i> Ver métricas
            </a>
        </div>
    </div>
    <a href="{{ route('prospectos.create') }}" class="btn btn-success btn-sm btn-flat"
       style="border-radius:20px;white-space:nowrap;flex-shrink:0;">
        <i class="fa fa-plus"></i> Nuevo prospecto
    </a>
</div>

{{-- ══ PANEL PRINCIPAL ══ --}}
<div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">

    {{-- Toolbar ─────────────────────────────────────── --}}
    <form method="GET" action="{{ route('prospectos.index') }}" id="form-filtros">
        <div class="pro-toolbar">

            {{-- Búsqueda --}}
            <div class="pro-search-wrap">
                <i class="fa fa-search pro-search-icon"></i>
                <input type="text" name="buscar" class="form-control"
                       placeholder="Nombre, contacto o teléfono…"
                       value="{{ request('buscar') }}" autocomplete="off">
                @if (request('buscar'))
                    <a href="{{ route('prospectos.index', request()->except('buscar','page')) }}"
                       class="pro-search-clear" title="Limpiar">
                        <i class="fa fa-times-circle"></i>
                    </a>
                @endif
            </div>

            {{-- Ciclo --}}
            <select name="ciclo_id" class="pro-select" onchange="this.form.submit()" title="Ciclo">
                @foreach ($ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}"
                        {{ (string) $cicloId === (string) $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->nombre }}
                    </option>
                @endforeach
            </select>

            {{-- Etapa --}}
            <select name="etapa" class="pro-select" onchange="this.form.submit()" title="Etapa">
                <option value="">Todas las etapas</option>
                @foreach ($etapas as $valor => $etiqueta)
                    <option value="{{ $valor }}" {{ request('etapa') === $valor ? 'selected' : '' }}>
                        {{ $etiqueta }}
                    </option>
                @endforeach
            </select>

            {{-- Estado --}}
            <select name="en_proceso" class="pro-select" onchange="this.form.submit()" title="Estado">
                <option value="">Todos</option>
                <option value="1" {{ request('en_proceso') ? 'selected' : '' }}>En proceso</option>
            </select>

            {{-- Por página --}}
            <select name="per_page" class="pro-select" onchange="this.form.submit()" title="Por página" style="min-width:90px;">
                @foreach ([10, 20, 50] as $size)
                    <option value="{{ $size }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>
                        {{ $size }} / pág.
                    </option>
                @endforeach
            </select>

            {{-- Buscar --}}
            <button type="submit" class="btn btn-primary btn-flat btn-sm" style="border-radius:20px;padding:4px 14px;">
                <i class="fa fa-search"></i>
            </button>

            {{-- Limpiar --}}
            @if (request()->anyFilled(['buscar', 'etapa', 'en_proceso']))
                <a href="{{ route('prospectos.index', ['ciclo_id' => $cicloId]) }}"
                   class="btn btn-default btn-flat btn-sm" style="border-radius:20px;padding:4px 10px;"
                   title="Quitar filtros">
                    <i class="fa fa-times"></i>
                </a>
            @endif

            {{-- Contador --}}
            @if ($prospectos->total() > 0)
                <span style="background:#e8f0fb;color:#3c8dbc;font-size:12px;font-weight:600;
                             padding:3px 12px;border-radius:12px;white-space:nowrap;margin-left:auto;">
                    <i class="fa fa-user"></i>
                    {{ $prospectos->total() }} prospecto{{ $prospectos->total() != 1 ? 's' : '' }}
                </span>
            @endif

        </div>
    </form>

    {{-- Tabla ───────────────────────────────────────── --}}
    <div class="box-body no-padding">
        <table class="pro-table">
            <thead>
                <tr>
                    <th>Prospecto</th>
                    <th>Contacto</th>
                    <th>Nivel</th>
                    <th>Canal</th>
                    <th>Ciclo</th>
                    <th>Etapa</th>
                    <th>Fecha</th>
                    <th>Responsable</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($prospectos as $prospecto)
                    <tr onclick="window.location='{{ route('prospectos.show', $prospecto->id) }}'">

                        {{-- PROSPECTO --}}
                        <td>
                            <div class="pro-nombre">{{ $prospecto->nombre_completo }}</div>
                            <div class="pro-sub">
                                {{ optional($prospecto->fecha_nacimiento)->format('d/m/Y') ?: 'Sin fecha nac.' }}
                            </div>
                        </td>

                        {{-- CONTACTO --}}
                        <td>
                            <div style="font-size:13px;font-weight:600;color:#2a3542;">
                                {{ $prospecto->contacto_nombre }}
                            </div>
                            @if ($prospecto->contacto_telefono)
                                <div class="pro-sub">
                                    <i class="fa fa-mobile" style="color:#3c8dbc;"></i>
                                    {{ $prospecto->contacto_telefono }}
                                </div>
                            @endif
                        </td>

                        {{-- NIVEL --}}
                        <td>
                            <span style="font-size:12px;color:#555;">
                                {{ optional($prospecto->nivelInteres)->nombre ?: '—' }}
                            </span>
                        </td>

                        {{-- CANAL --}}
                        <td>
                            <span style="font-size:12px;color:#555;">
                                {{ $prospecto->canal_contacto ? ucfirst(str_replace('_', ' ', $prospecto->canal_contacto)) : '—' }}
                            </span>
                        </td>

                        {{-- CICLO --}}
                        <td>
                            <span style="font-size:12px;color:#555;">
                                {{ optional($prospecto->ciclo)->nombre ?: '—' }}
                            </span>
                        </td>

                        {{-- ETAPA --}}
                        <td>
                            <span class="pro-etapa pro-etapa-{{ $prospecto->etapa }}">
                                <i class="fa fa-circle" style="font-size:6px;"></i>
                                {{ $etapas[$prospecto->etapa] ?? $prospecto->etapa }}
                            </span>
                        </td>

                        {{-- FECHA --}}
                        <td>
                            <span style="font-size:12px;color:#6b7a8d;">
                                {{ optional($prospecto->fecha_primer_contacto)->format('d/m/Y') ?: '—' }}
                            </span>
                        </td>

                        {{-- RESPONSABLE --}}
                        <td>
                            <span style="font-size:12px;color:#555;">
                                {{ optional($prospecto->responsable)->nombre ?: '—' }}
                            </span>
                        </td>

                        {{-- ACCIONES --}}
                        <td class="text-center" onclick="event.stopPropagation()">
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <a href="{{ route('prospectos.show', $prospecto->id) }}"
                                   class="btn btn-default btn-xs btn-flat" style="border-radius:4px;" title="Ver">
                                    <i class="fa fa-eye text-blue"></i>
                                </a>
                                <button type="button" class="btn btn-default btn-xs btn-flat"
                                        style="border-radius:4px;" title="Cambiar etapa"
                                        data-toggle="modal" data-target="#modalEtapa"
                                        data-id="{{ $prospecto->id }}"
                                        data-nombre="{{ $prospecto->nombre_completo }}"
                                        data-etapa="{{ $prospecto->etapa }}"
                                        data-motivo="{{ $prospecto->motivo_no_concrecion }}">
                                    <i class="fa fa-exchange text-orange"></i>
                                </button>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div style="text-align:center;padding:50px 20px;color:#ccc;">
                                <i class="fa fa-user-plus" style="font-size:48px;display:block;margin-bottom:14px;color:#dde4ea;"></i>
                                @if (request()->anyFilled(['buscar','etapa','en_proceso']))
                                    <h4 style="color:#999;margin:0 0 8px;">Sin resultados</h4>
                                    <p style="font-size:13px;color:#bbb;margin:0 0 16px;">No hay prospectos con los filtros aplicados.</p>
                                    <a href="{{ route('prospectos.index', ['ciclo_id' => $cicloId]) }}"
                                       class="btn btn-default btn-sm" style="border-radius:20px;">
                                        <i class="fa fa-times"></i> Quitar filtros
                                    </a>
                                @else
                                    <h4 style="color:#999;margin:0 0 8px;">No hay prospectos registrados</h4>
                                    <p style="font-size:13px;color:#bbb;margin:0 0 16px;">Registra el primer prospecto del ciclo.</p>
                                    <a href="{{ route('prospectos.create') }}" class="btn btn-success btn-sm" style="border-radius:20px;">
                                        <i class="fa fa-plus"></i> Nuevo prospecto
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación ───────────────────────────────────── --}}
    @if ($prospectos->hasPages())
        <div class="pro-footer">
            <span class="pro-footer-info">
                Mostrando <strong>{{ $prospectos->firstItem() }}</strong>–<strong>{{ $prospectos->lastItem() }}</strong>
                de <strong>{{ $prospectos->total() }}</strong> prospecto(s)
            </span>
            <div>{{ $prospectos->appends(request()->query())->links() }}</div>
        </div>
    @endif

</div>

{{-- ══ MODAL CAMBIAR ETAPA ══ --}}
<div class="modal fade" id="modalEtapa" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" id="formCambiarEtapa">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-exchange"></i> Cambiar etapa</h4>
                </div>
                <div class="modal-body">
                    <p style="font-size:13px;color:#666;margin-bottom:16px;">
                        Prospecto: <strong id="modalProspectoNombre">—</strong>
                    </p>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:700;color:#555;">Nueva etapa</label>
                        <select class="form-control" id="modal_etapa" name="etapa" required>
                            @foreach ($etapas as $valor => $etiqueta)
                                <option value="{{ $valor }}">{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size:12px;font-weight:700;color:#555;">Notas</label>
                        <textarea class="form-control" id="modal_notas" name="notas" rows="3" required
                                  placeholder="Describe el motivo del cambio"></textarea>
                    </div>
                    <div class="form-group" id="contenedorMotivoNoConcrecion" style="display:none;">
                        <label style="font-size:12px;font-weight:700;color:#555;">Motivo no concreción</label>
                        <textarea class="form-control" id="modal_motivo_no_concrecion"
                                  name="motivo_no_concrecion" rows="2"
                                  placeholder="Indica por qué no se concretó"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f9fafb;">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-flat">
                        <i class="fa fa-check"></i> Guardar cambio
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    var modal       = $('#modalEtapa');
    var form        = $('#formCambiarEtapa');
    var etapaSelect = $('#modal_etapa');
    var motivoGroup = $('#contenedorMotivoNoConcrecion');
    var motivoInput = $('#modal_motivo_no_concrecion');

    function toggleMotivo() {
        var mostrar = etapaSelect.val() === 'no_concretado';
        motivoGroup.toggle(mostrar);
        motivoInput.prop('required', mostrar);
    }

    modal.on('show.bs.modal', function (event) {
        var btn = $(event.relatedTarget);
        form.attr('action', '{{ url('prospectos') }}/' + btn.data('id') + '/etapa');
        $('#modalProspectoNombre').text(btn.data('nombre'));
        etapaSelect.val(btn.data('etapa'));
        motivoInput.val(btn.data('motivo') || '');
        $('#modal_notas').val('');
        toggleMotivo();
    });

    etapaSelect.on('change', toggleMotivo);
});
</script>
@endpush
