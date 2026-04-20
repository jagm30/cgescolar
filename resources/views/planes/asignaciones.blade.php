@extends('layouts.master')

@section('page_title', 'Asignaciones de planes')
@section('page_subtitle', 'Listado de asignaciones')

@section('breadcrumb')
    <li><a href="{{ route('planes.index') }}">Planes de pago</a></li>
    <li class="active">Asignaciones</li>
@endsection

@push('styles')
<style>
    /* ── CABECERA ──────────── */
    .plan-header {
        background: linear-gradient(135deg, #1e4d7b 0%, #3c8dbc 100%);
        border-radius: 4px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        color: #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .plan-header-nombre {
        font-size: 20px;
        font-weight: 700;
    }

    .plan-header-sub {
        font-size: 12px;
        opacity: .75;
    }

    /* ── BADGES MODERNOS ──────────── */
    .badge-tipo {
        display: inline-block;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 20px;
        color: #fff;
    }

    .badge-grupo {
        background: #3498db;
    }

    .badge-individual {
        background: #27ae60;
    }

    .badge-nivel {
        background: #f39c12;
    }
</style>
@endpush

@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">×</button>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── CABECERA ── --}}
    <div class="plan-header">
        <div style="width:48px;height:48px;border-radius:12px;
            background:rgba(255,255,255,.15);
            display:flex;align-items:center;justify-content:center;">
            <i class="fa fa-random" style="font-size:22px;"></i>
        </div>

        <div style="flex:1;">
            <div class="plan-header-nombre">Asignaciones de planes</div>
            <div class="plan-header-sub">
                Gestión de asignaciones a alumnos, grupos o niveles
            </div>
        </div>

        {{-- BOTÓN MEJORADO --}}
        <a href="{{ route('planes.asignar.form') }}" class="btn btn-sm"
            style="background:#ffffff; color:#1e4d7b; font-weight:600;
            border-radius:6px; padding:6px 12px;">
            <i class="fa fa-plus"></i> Asignar plan
        </a>
    </div>

    {{-- ── TABLA ── --}}
    <div class="box box-primary" style="border-top: 3px solid #3c8dbc; border-radius: 6px;">

        <div class="box-header with-border" style="padding:16px;">
            <h3 class="box-title" style="font-weight:700; color:#1e4d7b;">
                <i class="fa fa-list"></i> Lista de Asignaciones
            </h3>
        </div>


        {{-- CONTENIDO --}}
        <div class="box-body table-responsive no-padding">
            <div id="asignaciones-content">
                <div class="text-center" style="padding: 40px">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <div class="text-muted" style="margin-top: 12px;">
                        Cargando asignaciones...
                    </div>
                </div>
            </div>
        </div>
        {{-- TOOLBAR --}}
        <div id="asignaciones-toolbar" class="box-body"
            style="background:#f4f6f9; border-bottom:1px solid #d0dde8; padding:12px 16px;">
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const asignacionesIndexUrl = '{{ route('planes.asignar.index') }}';

    function renderAsignacionesTable(response) {

        /* ── TOOLBAR ── */
        let toolbarHtml = `
            <div class="row">
                <div class="col-sm-6 text-muted">
                    Mostrando ${response.pagination.from || 0} - ${response.pagination.to || 0}
                    de ${response.pagination.total} asignaciones
                </div>
                <div class="col-sm-6 text-right">
                    <ul class="pagination pagination-sm no-margin">
        `;

        if (response.pagination.current_page > 1) {
            toolbarHtml += `<li>
                <a href="${asignacionesIndexUrl}?page=${response.pagination.current_page - 1}">&laquo;</a>
            </li>`;
        }

        for (let page = 1; page <= response.pagination.last_page; page++) {
            toolbarHtml += `
                <li class="${page === response.pagination.current_page ? 'active' : ''}">
                    <a href="${asignacionesIndexUrl}?page=${page}">${page}</a>
                </li>`;
        }

        if (response.pagination.current_page < response.pagination.last_page) {
            toolbarHtml += `<li>
                <a href="${asignacionesIndexUrl}?page=${response.pagination.current_page + 1}">&raquo;</a>
            </li>`;
        }

        toolbarHtml += `</ul></div></div>`;

        $('#asignaciones-toolbar').html(toolbarHtml);

        /* ── TABLA ── */
        let html = `
            <table class="table table-bordered table-striped table-hover" style="font-size:13px;">
                <thead style="background:#f4f6f9;">
                    <tr>
                        <th>Plan</th>
                        <th>Asignado a</th>
                        <th>Tipo</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (response.data.length === 0) {
            html += `
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding:40px;">
                        <i class="fa fa-random fa-3x" style="color:#e0e0e0; display:block; margin-bottom:10px;"></i>
                        No hay asignaciones registradas.
                    </td>
                </tr>`;
        } else {
            response.data.forEach(function(item) {

                let tipoClass = 'badge-tipo';

                if(item.origen === 'Grupo') tipoClass += ' badge-grupo';
                if(item.origen === 'Individual') tipoClass += ' badge-individual';
                if(item.origen === 'Nivel') tipoClass += ' badge-nivel';

                html += `
                    <tr>
                        <td><strong>${item.plan}</strong></td>
                        <td>${item.asignado_a}</td>
                        <td>
                            <span class="${tipoClass}">
                                ${item.origen}
                            </span>
                        </td>
                        <td>${item.fecha_inicio}</td>
                        <td>${item.fecha_fin}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;

        $('#asignaciones-content').html(html);
    }

    function cargarAsignaciones(url = asignacionesIndexUrl) {

        $('#asignaciones-content').html(`
            <div class="text-center" style="padding: 40px">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <div class="text-muted" style="margin-top: 12px;">
                    Cargando asignaciones...
                </div>
            </div>
        `);

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                renderAsignacionesTable(response);
            },
            error: function() {
                $('#asignaciones-content').html(`
                    <div class="alert alert-danger" style="border-radius:6px;">
                        Error al cargar las asignaciones.
                    </div>
                `);
            }
        });
    }

    $(function() {

        $(document).on('click',
            '#asignaciones-toolbar .pagination a',
            function(e) {
                e.preventDefault();
                cargarAsignaciones($(this).attr('href'));
            }
        );

        cargarAsignaciones();
    });
</script>
@endpush
