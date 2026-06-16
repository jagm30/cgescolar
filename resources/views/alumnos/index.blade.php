@extends('layouts.master')

@section('page_title', 'Alumnos')
@section('page_subtitle', 'Alumnos inscritos')

@section('breadcrumb')
    <li class="active">Alumnos</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alumnos.css') }}">
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible alm-alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- ══ ENCABEZADO + STATS ══ --}}
    <div style="background:#fff;border:1px solid #e0e7ef;border-radius:8px;padding:12px 18px;margin-bottom:12px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
                box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
                <i class="fa fa-users text-blue"></i> Alumnos
            </h4>
            <div style="display:flex;gap:7px;flex-wrap:wrap;">
                <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-users"></i> {{ $statsTotal ?? $alumnos->total() }} total
                </span>
                <span style="background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-check-circle"></i> {{ $statsActivos ?? '—' }} activos
                </span>
                <span style="background:#fef6e7;color:#b45309;border:1px solid #fcd97d;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-graduation-cap"></i> {{ $statsInscritos ?? '—' }} inscritos
                </span>
                <span style="background:#f5eef8;color:#7d3c98;border:1px solid #ebdef0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-th-large"></i> {{ $grupos->count() }} grupos
                </span>
            </div>
        </div>
        @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
            <a href="{{ route('alumnos.create') }}" class="btn btn-success btn-sm btn-flat"
               style="border-radius:20px;white-space:nowrap;flex-shrink:0;">
                <i class="fa fa-plus"></i> Registrar alumno
            </a>
        @endif
    </div>

    {{-- ══ PANEL PRINCIPAL ══ --}}
    <div class="box alm-box">

        {{-- Toolbar ─────────────────────────────────── --}}
        <form method="GET" action="{{ route('alumnos.index') }}" id="form-filtros">
            <div class="alm-toolbar">

                {{-- Búsqueda --}}
                <div class="alm-search-wrap">
                    <i class="fa fa-search alm-search-icon"></i>
                    <input type="text" name="buscar" class="form-control" placeholder="Nombre, matrícula o CURP…"
                        value="{{ request('buscar') }}" autocomplete="off">
                    @if (request('buscar'))
                        <a href="{{ route('alumnos.index', request()->except('buscar', 'page')) }}" class="alm-search-clear"
                            title="Limpiar">
                            <i class="fa fa-times-circle"></i>
                        </a>
                    @endif
                </div>

                {{-- Filtro nivel --}}
                <select name="nivel_id" class="alm-select" onchange="this.form.submit()" title="Filtrar por nivel">
                    <option value="">Todos los niveles</option>
                    @foreach ($niveles as $nivel)
                        <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Filtro grupo --}}
                <select name="grupo_id" class="alm-select" onchange="this.form.submit()" title="Filtrar por grupo">
                    <option value="">Todos los grupos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                            {{ $grupo->grado->numero }}° {{ $grupo->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Filtro estado --}}
                <div class="btn-group alm-btn-estado-group">
                    <a href="{{ route('alumnos.index', array_merge(request()->except('estado', 'page'), [])) }}"
                        class="btn btn-sm btn-flat alm-btn-estado {{ !request()->filled('estado') ? 'btn-primary' : 'btn-default' }}">
                        Todos
                    </a>
                    <a href="{{ route('alumnos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'activo'])) }}"
                        class="btn btn-sm btn-flat alm-btn-estado {{ request('estado') === 'activo' ? 'btn-success' : 'btn-default' }}">
                        Activos
                    </a>
                    <a href="{{ route('alumnos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'baja_temporal'])) }}"
                        class="btn btn-sm btn-flat alm-btn-estado {{ request('estado') === 'baja_temporal' ? 'btn-warning' : 'btn-default' }}">
                        Baja temporal
                    </a>
                    <a href="{{ route('alumnos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'egresado'])) }}"
                        class="btn btn-sm btn-flat alm-btn-estado {{ request('estado') === 'egresado' ? 'btn-default active' : 'btn-default' }}">
                        Egresados
                    </a>
                </div>

                {{-- Botón buscar (si escribe y da Enter o clic) --}}
                <button type="submit" class="btn btn-primary btn-flat btn-sm alm-btn-pill">
                    <i class="fa fa-search"></i> Buscar
                </button>

                {{-- Limpiar filtros --}}
                @if (request()->anyFilled(['buscar', 'nivel_id', 'grupo_id', 'estado']))
                    <a href="{{ route('alumnos.index') }}" class="btn btn-default btn-flat btn-sm alm-btn-pill"
                        title="Quitar todos los filtros">
                        <i class="fa fa-times"></i>
                    </a>
                @endif

                {{-- Contador --}}
                @if ($alumnos->total() > 0)
                    <span class="alm-count-badge">
                        <i class="fa fa-user"></i>
                        {{ $alumnos->total() }} alumno{{ $alumnos->total() != 1 ? 's' : '' }}
                    </span>
                @endif

            </div>
        </form>

        {{-- Tabla ───────────────────────────────────── --}}
        <div class="box-body no-padding">
            <table class="alm-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Matrícula</th>
                        <th>Nombre</th>
                        <th>Nivel / Grupo</th>
                        <th>Plan de pagos</th>
                        <th>Familia</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        @php
                            // Filtramos para que solo tome la inscripción activa
                            $inscripcion = $alumno->inscripciones->where('activo', true)->first();
                            $inicial = mb_strtoupper(mb_substr($alumno->ap_paterno, 0, 1));
                            $estado = $alumno->estado;
                        @endphp
                        <tr data-href="{{ route('alumnos.show', $alumno->id) }}">
                            {{-- AVATAR --}}
                            <td>
                                @if ($alumno->foto_url)
                                    <img src="{{ asset('storage/' . $alumno->foto_url) }}" class="alm-avatar"
                                        alt="{{ $alumno->nombre }}">
                                @else
                                    <div class="alm-avatar-placeholder {{ $estado }}">
                                        {{ $inicial }}
                                    </div>
                                @endif
                            </td>

                            {{-- MATRÍCULA --}}
                            <td>
                                <span class="alm-matricula">{{ $alumno->matricula }}</span>
                            </td>

                            {{-- NOMBRE --}}
                            <td>
                                <div class="alm-nombre">
                                    {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }} {{ $alumno->nombre }}
                                </div>
                            </td>

                            {{-- NIVEL / GRUPO --}}
                            <td>
                                @if ($inscripcion)
                                    <div class="alm-nivel-tag">{{ $inscripcion->grupo->grado->nivel->nombre ?? '' }}</div>
                                    <div class="alm-grupo-txt">
                                        {{ $inscripcion->grupo->grado->numero ?? ''}}°
                                        <strong>{{ $inscripcion->grupo->nombre ?? '' }}</strong>
                                    </div>
                                @else
                                    <span class="alm-badge alm-badge-sin-grupo">
                                        <i class="fa fa-exclamation-triangle"></i> Sin Grupo
                                    </span>
                                @endif
                            </td>

                            {{-- PLAN DE PAGOS --}}
                            <td>
                                @php $plan = $alumno->asignacionesPlanes->first()?->plan; @endphp
                                @if ($plan)
                                    <span style="font-size:12px;font-weight:600;color:#2c5282;">
                                        <i class="fa fa-file-text-o" style="color:#3c8dbc;margin-right:4px;"></i>
                                        {{ $plan->nombre }}
                                    </span>
                                @else
                                    <span style="font-size:12px;color:#b0bec5;">—</span>
                                @endif
                            </td>

                            {{-- FAMILIA --}}
                            <td>
                                @if ($alumno->familia)
                                    <a href="{{ route('familias.show', $alumno->familia->id) }}" class="alm-familia-lnk"
                                        title="Ver familia">
                                        <i class="fa fa-home alm-familia-ico"></i>
                                        {{ $alumno->familia->apellido_familia }}
                                    </a>
                                @else
                                    <span class="alm-familia-none">—</span>
                                @endif
                            </td>

                            {{-- ESTADO --}}
                            <td>
                                <span class="alm-badge alm-badge-{{ $estado }}">
                                    <i class="fa fa-circle alm-estado-dot"></i>
                                    @switch($estado)
                                        @case('activo')
                                            Activo
                                        @break

                                        @case('baja_temporal')
                                            Baja temporal
                                        @break

                                        @case('baja_definitiva')
                                            Baja definitiva
                                        @break

                                        @case('egresado')
                                            Egresado
                                        @break

                                        @default
                                            {{ ucfirst($estado) }}
                                    @endswitch
                                </span>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn-action-flat btn-dropdown-manual" type="button"
                                        data-toggle="dropdown">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right alm-dropdown-menu-actions">
                                        <li class="dropdown-header">Opciones</li>

                                        {{-- 1. VER PERFIL --}}
                                        <li>
                                            <a href="{{ route('alumnos.show', $alumno->id) }}" class="alm-dropdown-item">
                                                <i class="fa fa-eye alm-dropdown-icon alm-dropdown-icon-blue"></i> Ver perfil
                                            </a>
                                        </li>

                                        {{-- 2. EDITAR (Condicionado a Admin o Recepción) --}}
                                        @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                                            <li>
                                                <a href="{{ route('alumnos.edit', $alumno->id) }}" class="alm-dropdown-item">
                                                    <i class="fa fa-pencil alm-dropdown-icon alm-dropdown-icon-orange"></i> Editar alumno
                                                </a>
                                            </li>
                                        @endif

                                        {{-- 3. ESTADO DE CUENTA (Condicionado a Admin o Cajero) --}}
                                        @if (auth()->user()->esAdministrador() || auth()->user()->esCajero())
                                            <li>
                                                <a href="{{ route('alumnos.estado-cuenta', $alumno->id) }}" class="alm-dropdown-item">
                                                    <i class="fa fa-money alm-dropdown-icon alm-dropdown-icon-green"></i> Estado de cuenta
                                                </a>
                                            </li>
                                        @endif

                                        @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                                        <li role="separator" class="divider"></li>

                                        {{-- 4. DESCARGAR FICHA PDF --}}
                                        <li>
                                            <a href="{{ route('alumnos.reporte', $alumno->id) }}" target="_blank"
                                                class="alm-dropdown-item">
                                                <i class="fa fa-file-pdf-o alm-dropdown-icon alm-dropdown-icon-red"></i> Ficha del alumno
                                            </a>
                                        </li>

                                        {{-- 5. IMPRIMIR CREDENCIAL (Abre el Modal) --}}
                                        <li>
                                            <a href="javascript:void(0)" class="btn-abrir-modal-credencial alm-dropdown-item"
                                                data-id="{{ $alumno->id }}" data-tipo="individual">
                                                <i class="fa fa-id-card alm-dropdown-icon alm-dropdown-icon-teal"></i> Imprimir credencial
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="alm-empty">
                                        <i class="fa fa-users"></i>
                                        @if (request()->anyFilled(['buscar', 'nivel_id', 'grupo_id', 'estado']))
                                            <h4>Sin resultados</h4>
                                            <p>No se encontraron alumnos con los filtros aplicados.</p>
                                            <a href="{{ route('alumnos.index') }}" class="btn btn-default btn-sm alm-btn-pill">
                                                <i class="fa fa-times"></i> Quitar filtros
                                            </a>
                                        @else
                                            <h4>No hay alumnos registrados</h4>
                                            <p>Registra el primer alumno del ciclo escolar.</p>
                                            @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                                                <a href="{{ route('alumnos.create') }}" class="btn btn-success btn-sm alm-btn-pill">
                                                    <i class="fa fa-plus"></i> Registrar alumno
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación ───────────────────────────────── --}}
            @if ($alumnos->hasPages())
                <div class="alm-footer">
                    <span class="alm-footer-info">
                        Mostrando <strong>{{ $alumnos->firstItem() }}</strong>–<strong>{{ $alumnos->lastItem() }}</strong>
                        de <strong>{{ $alumnos->total() }}</strong> alumno(s)
                        @if (request()->anyFilled(['buscar', 'nivel_id', 'grupo_id', 'estado']))
                            <span class="alm-filtro-label"> · filtrado</span>
                        @endif
                    </span>
                    <div>
                        {{ $alumnos->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif

        </div>
        <div class="modal fade" id="modalElegirDiseno" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-id-badge text-primary"></i> Elegir Diseño</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Selecciona el diseño a utilizar:</label>
                            <select id="select-diseno-credencial" class="form-control">
                                <option value="">-- Seleccione un diseño --</option>
                                @foreach ($disenos as $diseno)
                                    <option value="{{ $diseno->id }}">{{ $diseno->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" id="btn-procesar-impresion">
                            <i class="fa fa-print"></i> Generar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                // ── 1. TU LÓGICA EXISTENTE PARA FILAS CLICKEABLES (Vanilla JS) ──
                document.querySelectorAll('.alm-table tbody tr[data-href]').forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        // Si hace clic en un botón o enlace, ignoramos para que no interfiera
                        if (e.target.closest('a, button, input, select')) return;
                        window.location.href = row.dataset.href;
                    });
                });

                // ── 2. LÓGICA PARA EL MODAL DE CREDENCIALES (jQuery) ──
                $(document).ready(function() {
                    let printId = null;
                    let printTipo = null;

                    // Al hacer clic en el botón de la tabla
                    $(document).on('click', '.btn-abrir-modal-credencial', function() {
                        printId = $(this).data('id');
                        printTipo = $(this).data('tipo'); // Aquí llegará como "individual"

                        // Reseteamos el select por si acaso
                        $('#select-diseno-credencial').val('');

                        // Abrimos el modal
                        $('#modalElegirDiseno').modal('show');
                    });

                    // Al darle al botón verde de Generar dentro del modal
                    $('#btn-procesar-impresion').click(function() {
                        let disenoId = $('#select-diseno-credencial').val();

                        if (!disenoId) {
                            alert("Por favor, selecciona un diseño válido.");
                            return;
                        }
                        // Plantillas de rutas (Corregidas definitivamente)
                        let urlLote =
                            "{{ route('credenciales.imprimirLote', ['credencial_id' => 'DISENO_ID', 'grupo_id' => 'TARGET_ID']) }}";
                        let urlIndividual =
                            "{{ route('credenciales.imprimirIndividual', ['credencial' => 'DISENO_ID', 'alumno' => 'TARGET_ID']) }}";

                        // Construimos la ruta final dependiendo de si es lote o individual
                        let urlFinal = (printTipo === 'lote') ? urlLote : urlIndividual;
                        urlFinal = urlFinal.replace('DISENO_ID', disenoId).replace('TARGET_ID', printId);

                        // Abrimos la credencial en pestaña nueva
                        window.open(urlFinal, '_blank');

                        // Escondemos el modal
                        $('#modalElegirDiseno').modal('hide');
                    });
                });
            </script>
        @endpush

    @endsection
