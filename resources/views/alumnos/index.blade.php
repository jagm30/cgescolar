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
        <h4 style="margin:0;font-weight:700;color:#1e4d7b;">
            <i class="fa fa-users text-blue"></i> Alumnos
        </h4>
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <a href="{{ route('alumnos.reporte-inscritos') }}" target="_blank"
               class="btn btn-default btn-sm btn-flat"
               style="border-radius:20px;white-space:nowrap;">
                <i class="fa fa-file-pdf-o"></i> Reporte inscritos
            </a>
            <a id="btn-cumpleaneros"
               href="{{ route('alumnos.reporte-cumpleaneros', array_merge(request()->only(['buscar','nivel_id','grupo_id','estado']), ['mes' => now()->month])) }}"
               target="_blank"
               class="btn btn-warning btn-sm btn-flat"
               style="border-radius:20px;white-space:nowrap;background:#e67e22;border-color:#ca6f1e;color:#fff;">
                <i class="fa fa-birthday-cake"></i> Cumpleañeros
                <select id="sel-mes-cumple" onclick="event.preventDefault(); event.stopPropagation();"
                        onchange="actualizarUrlCumple(this.value)"
                        style="margin-left:5px;font-size:11px;padding:1px 3px;border-radius:4px;
                               border:1px solid rgba(255,255,255,.5);background:rgba(255,255,255,.2);
                               color:#fff;cursor:pointer;vertical-align:middle;">
                    @php $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']; @endphp
                    @foreach($meses as $i => $nombre)
                        <option value="{{ $i + 1 }}" {{ now()->month == $i + 1 ? 'selected' : '' }}
                                style="color:#333;background:#fff;">
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </a>
            @if (auth()->user()->esAdministrador() || auth()->user()->esDirectorSeccion() || auth()->user()->esCajero())
                <a href="{{ route('alumnos.exportar-excel', request()->only(['buscar','nivel_id','grupo_id','estado'])) }}"
                   class="btn btn-success btn-sm btn-flat"
                   style="border-radius:20px;white-space:nowrap;background:#217346;border-color:#1a5e38;">
                    <i class="fa fa-file-excel-o"></i> Exportar Excel
                </a>
            @endif
            @if (auth()->user()->esAdministrador())
                <a href="{{ route('reportes.directorio-familiar.pdf') }}" target="_blank"
                   class="btn btn-info btn-sm btn-flat"
                   style="border-radius:20px;white-space:nowrap;background:#2471a3;border-color:#1a5f8a;color:#fff;">
                    <i class="fa fa-address-book-o"></i> Directorio familiar
                </a>
            @endif
            @if (auth()->user()->esAdministrador() || auth()->user()->esRecepcion())
                <a href="{{ route('alumnos.create') }}" class="btn btn-success btn-sm btn-flat"
                   style="border-radius:20px;white-space:nowrap;">
                    <i class="fa fa-plus"></i> Registrar alumno
                </a>
            @endif
        </div>
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
                <select name="nivel_id" class="alm-select" title="Filtrar por nivel"
                        onchange="document.getElementById('filtroGrupo').value=''; this.form.submit()">
                    <option value="">Todos los niveles</option>
                    @foreach ($niveles as $nivel)
                        <option value="{{ $nivel->id }}" {{ request('nivel_id') == $nivel->id ? 'selected' : '' }}>
                            {{ $nivel->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Filtro grupo --}}
                <select name="grupo_id" id="filtroGrupo" class="alm-select" onchange="this.form.submit()" title="Filtrar por grupo">
                    <option value="">Todos los grupos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ request('grupo_id') == $grupo->id ? 'selected' : '' }}>
                            {{ $grupo->grado->numero }}° {{ $grupo->nombre }}
                        </option>
                    @endforeach
                </select>

                {{-- Filtro estado --}}
                <div class="btn-group alm-btn-estado-group">
                    {{-- Activos: primer botón, resaltado por defecto (sin parámetro = activos) --}}
                    <a href="{{ route('alumnos.index', request()->except('estado', 'page')) }}"
                        class="btn btn-sm btn-flat alm-btn-estado {{ !request()->filled('estado') || request('estado') === 'activo' ? 'btn-success' : 'btn-default' }}">
                        Activos
                    </a>
                    <a href="{{ route('alumnos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'todos'])) }}"
                        class="btn btn-sm btn-flat alm-btn-estado {{ request('estado') === 'todos' ? 'btn-primary' : 'btn-default' }}">
                        Todos
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
                @php
                    $sortActual = request('sort', 'nombre');
                    $dirActual  = request('dir', 'asc');
                    $sortUrl = fn(string $col) => route('alumnos.index', array_merge(
                        request()->except(['sort', 'dir', 'page']),
                        ['sort' => $col, 'dir' => ($sortActual === $col && $dirActual === 'asc') ? 'desc' : 'asc']
                    ));
                    $sortIcon = fn(string $col) => $sortActual === $col
                        ? ($dirActual === 'asc' ? ' <i class="fa fa-sort-asc"></i>' : ' <i class="fa fa-sort-desc"></i>')
                        : ' <i class="fa fa-sort" style="opacity:.3;"></i>';
                @endphp
                <thead>
                    <tr>
                        <th style="width:42px;"></th>
                        <th style="width:30%;">
                            <a href="{{ $sortUrl('nombre') }}" class="alm-sort-link">
                                Nombre {!! $sortIcon('nombre') !!}
                            </a>
                        </th>
                        <th style="width:14%;">
                            <a href="{{ $sortUrl('nivel_grupo') }}" class="alm-sort-link">
                                Nivel / Grupo {!! $sortIcon('nivel_grupo') !!}
                            </a>
                        </th>
                        <th style="width:18%;font-size:11px;">
                            <a href="{{ $sortUrl('plan') }}" class="alm-sort-link">
                                Plan de pagos {!! $sortIcon('plan') !!}
                            </a>
                        </th>
                        <th style="width:13%;">Familia</th>
                        <th style="width:7%;" class="text-center">Portal</th>
                        <th style="width:9%;">
                            <a href="{{ $sortUrl('estado') }}" class="alm-sort-link">
                                Estado {!! $sortIcon('estado') !!}
                            </a>
                        </th>
                        <th style="width:9%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        @php
                            // Solo muestra la inscripción del ciclo seleccionado; prefiere la que tenga grupo asignado
                            $inscsDelCiclo = $alumno->inscripciones->where('ciclo_id', $cicloId);
                            $inscripcion   = $inscsDelCiclo->whereNotNull('grupo_id')->first()
                                ?? $inscsDelCiclo->first();
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


                            {{-- NOMBRE --}}
                            <td>
                                <div class="alm-nombre">
                                    {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }} {{ $alumno->nombre }}
                                </div>
                                @if($alumno->curp)
                                    <div style="font-size:11px;color:#c0c8d0;margin-top:2px;">{{ $alumno->curp }}</div>
                                @endif
                            </td>

                            {{-- NIVEL / GRUPO --}}
                            <td>
                                @if ($inscripcion)
                                    <div class="alm-nivel-tag">{{ $inscripcion->grupo->grado->nivel->nombre ?? '' }}</div>
                                    <div class="alm-grupo-txt">
                                        <span class="alm-grupo-grado">{{ $inscripcion->grupo->grado->numero ?? '' }}°</span>
                                        <span class="alm-grupo-nombre">{{ $inscripcion->grupo->nombre ?? '' }}</span>
                                    </div>
                                @else
                                    <span class="alm-badge alm-badge-sin-grupo">
                                        <i class="fa fa-exclamation-triangle"></i> Sin Grupo
                                    </span>
                                @endif
                            </td>

                            {{-- PLAN DE PAGOS — plan efectivo según cargos generados en el ciclo actual --}}
                            <td>
                                @php $plan = $planPorAlumno->get($alumno->id); @endphp
                                @if ($plan)
                                    <span style="font-size:11px;color:#7a90a8;">
                                        {{ $plan->nombre }}
                                    </span>
                                @else
                                    <span style="font-size:11px;color:#c0c8d0;">—</span>
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


                            {{-- PORTAL --}}
                            <td class="text-center">
                                @if($alumno->contactos->isNotEmpty())
                                    <span title="Tiene acceso al portal de padres"
                                        style="color: #27ae60; font-size: 16px;">
                                        <i class="fa fa-check-circle"></i>
                                    </span>
                                @else
                                    <span title="Sin usuario en el portal"
                                        style="color: #bdc3c7; font-size: 16px;">
                                        <i class="fa fa-times-circle"></i>
                                    </span>
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

                                        {{-- 6. ELIMINAR — solo administrador --}}
                                        @if (auth()->user()->esAdministrador())
                                        <li role="separator" class="divider"></li>
                                        <li>
                                            <a href="javascript:void(0)"
                                               class="alm-dropdown-item text-danger btn-eliminar-alumno"
                                               data-id="{{ $alumno->id }}"
                                               data-nombre="{{ $alumno->nombre_completo }}">
                                                <i class="fa fa-trash alm-dropdown-icon" style="color:#c0392b;"></i> Eliminar alumno
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
                // ── REPORTE CUMPLEAÑEROS: actualizar mes en la URL ──
                var urlBaseCumple = '{{ route('alumnos.reporte-cumpleaneros', request()->only(['buscar','nivel_id','grupo_id','estado'])) }}';

                function actualizarUrlCumple(mes) {
                    var sep = urlBaseCumple.indexOf('?') === -1 ? '?' : '&';
                    document.getElementById('btn-cumpleaneros').href = urlBaseCumple + sep + 'mes=' + mes;
                }

                // ── 1. TU LÓGICA EXISTENTE PARA FILAS CLICKEABLES (Vanilla JS) ──
                document.querySelectorAll('.alm-table tbody tr[data-href]').forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        // Si hace clic en un botón o enlace, ignoramos para que no interfiera
                        if (e.target.closest('a, button, input, select')) return;
                        window.location.href = row.dataset.href;
                    });
                });

                // ── 2. ELIMINAR ALUMNO ──
                $(document).on('click', '.btn-eliminar-alumno', function(e) {
                    e.stopPropagation();
                    var id = $(this).data('id');
                    var nombre = $(this).data('nombre');

                    if (!confirm('¿Eliminar a ' + nombre + '?\n\nEsta acción es irreversible. Solo se permite si el alumno no tiene cargos o pagos registrados.')) {
                        return;
                    }

                    $.ajax({
                        url: '/alumnos/' + id,
                        method: 'POST',
                        data: { _method: 'DELETE', _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function(res) {
                            window.location.reload();
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'No se pudo eliminar al alumno.';
                            alert(msg);
                        }
                    });
                });

                // ── 3. LÓGICA PARA EL MODAL DE CREDENCIALES (jQuery) ──
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
