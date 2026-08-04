@extends('layouts.master')

@section('page_title', 'Pendientes de Acceso')

@push('styles')
    <style>
        .content-wrapper {
            background-color: #f4f7f6 !important;
        }

        .con-filter-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px 8px 0 0;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-select {
            height: 35px;
            border-radius: 6px;
            border: 1px solid #d2d6de;
            padding: 0 10px;
            color: #475569;
            outline: none;
        }

        .con-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            margin: 0;
        }

        .con-table thead th {
            background: #fcfcfc;
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            padding: 15px 20px;
            border-bottom: 2px solid #f0f2f5;
            text-align: left;
        }

        .con-table tbody td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f3f7;
            vertical-align: middle;
        }

        .con-badge-familia {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        .con-badge-personal {
            background: #e0e7ff;
            color: #4338ca;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
        }

        /* Botón Inactivo */
        #btn-generar-masivo:disabled {
            background-color: #cbd5e1 !important;
            color: #64748b !important;
            cursor: not-allowed !important;
            box-shadow: none !important;
        }

        /* Botón Activo */
        #btn-generar-masivo {
            background-color: #3c8dbc;
            color: white;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 6px rgba(60, 141, 188, 0.3);
        }

        .btn-action-flat {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .btn-action-flat:hover {
            transform: translateY(-2px);
            background: #fff;
        }
    </style>
@endpush

@section('content')

    @php
        $mensajeMostrar = session('mensaje') ?? session('mensaje_persistente');
        session()->forget('mensaje_persistente');
        $hayPdf = session()->has('credenciales_nuevas');
    @endphp

    {{-- TARJETA DE NOTIFICACIÓN PERSISTENTE --}}
    @if ($mensajeMostrar)
        <div class="alert alert-dismissible"
            style="background: #ffffff !important; color: #2c3e50 !important; border: 1px solid #e2e8f0 !important; border-left: 5px solid #28a745 !important; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin-bottom: 25px; padding: 15px 20px; position: relative;">

            <button type="button" class="close" data-dismiss="alert" aria-hidden="true"
                style="color: #94a3b8; opacity: 1; font-size: 20px; top: 15px; right: 20px; background: none; border: none; cursor: pointer;">&times;</button>

            <div style="display: flex; align-items: flex-start; gap: 15px;">
                <div
                    style="width: 38px; height: 38px; background: #e8f5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2e7d32; font-size: 16px; flex-shrink: 0; margin-top: 2px;">
                    <i class="fa fa-check"></i>
                </div>

                <div style="padding-right: 20px;">
                    <span
                        style="display: block; font-weight: 700; font-size: 14px; color: #1e293b; line-height: 1.2; margin-bottom: 3px;">Acción
                        procesada con éxito</span>
                    <span
                        style="font-size: 13px; color: #64748b; font-weight: 500; line-height: 1.4;">{{ $mensajeMostrar }}</span>

                    @if ($hayPdf)
                        <div style="margin-top: 12px;">
                            <a href="{{ route('usuarios.credencialesPdf') }}" target="_blank"
                                style="display: inline-block; background: #28a745; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; text-decoration: none;">
                                <i class="fa fa-file-pdf-o" style="margin-right: 4px;"></i> Descargar PDF de Credenciales
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-md-9">
            <div class="con-filter-toolbar">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <h4 style="margin:0; font-weight:800; color:#2c3e50;">
                        <i class="fa fa-user-plus text-orange"></i> Pendientes
                        <span style="display:inline-block; background:#e67e22; color:#fff; font-size:12px;
                                     font-weight:700; padding:2px 10px; border-radius:20px; margin-left:6px;
                                     vertical-align:middle;">
                            {{ $pendientes->count() }}
                        </span>
                    </h4>

                    {{-- FILTRO POR TIPO (oculto para director: solo ve padres) --}}
                    @if (!$esDirector)
                    <select id="filtro-tipo" class="filter-select" style="min-width: 180px;">
                        <option value="todos">Todos los pendientes</option>
                        <option value="contacto">Solo Padres de Familia</option>
                        <option value="personal">Solo Personal (Empleados)</option>
                    </select>
                    @endif

                    {{-- FILTRO POR SECCIÓN ESCOLAR --}}
                    <select id="filtro-seccion" class="filter-select" style="min-width: 170px;">
                        <option value="">Todas las Secciones</option>
                        @foreach ($niveles as $nivel)
                            <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-left:auto; display:flex; align-items:center; gap:18px;">
                    <label id="lbl-enviar-correo"
                        style="margin:0; display:flex; align-items:center; gap:7px; font-size:13px; font-weight:600; color:#475569; cursor:pointer; white-space:nowrap;">
                        <input type="checkbox" id="chk-enviar-correo" checked
                            style="width:16px; height:16px; cursor:pointer; accent-color:#3c8dbc;">
                        <i class="fa fa-envelope" style="color:#3c8dbc;"></i>
                        Enviar correo con credenciales
                    </label>
                    <button id="btn-generar-masivo" class="btn" disabled
                        style="border-radius:20px; font-weight:700; padding: 7px 25px; transition: 0.3s;">
                        <i class="fa fa-magic"></i> Dar de Alta Cuentas (<span id="count-select">0</span>)
                    </button>
                </div>
            </div>

            <div class="box" style="border:none; border-radius:0 0 8px 8px; box-shadow:0 2px 12px rgba(0,0,0,0.03);">
                <div class="box-body no-padding">
                    <table class="con-table" id="tabla-pendientes">
                        <thead>
                            <tr>
                                <th width="40" class="text-center"><input type="checkbox" id="check-all"></th>
                                <th>Nombre Completo</th>
                                <th>Origen / Referencia</th>
                                <th>Alumno(s)</th>
                                <th>Correo Electrónico</th>
                                <th>Rol en el Sistema</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendientes as $p)
                                <tr class="row-pendiente" data-id="{{ $p->id }}" data-tipo="{{ $p->tipo }}"
                                    data-nivel-ids="{{ $p->nivel_ids ?? '' }}">
                                    <td class="text-center">
                                        <input type="checkbox" class="check-user" value="{{ $p->id }}">
                                    </td>
                                    <td><b style="color: #2c3e50;">{{ $p->nombre_completo }}</b></td>
                                    <td>
                                        <span
                                            class="{{ $p->tipo === 'personal' ? 'con-badge-personal' : 'con-badge-familia' }}">
                                            {{ $p->referencia }}
                                        </span>
                                    </td>
                                    <td style="font-size:12px; color:#475569;">
                                        {{ $p->alumnos ?? '—' }}
                                    </td>
                                    <td style="font-family:monospace; color: #64748b;">{{ $p->email }}</td>
                                    <td>
                                        @if ($p->tipo === 'contacto')
                                            <span style="font-size: 12px; font-weight: 600; color: #64748b;">Padre de Familia</span>
                                        @else
                                            <select class="form-control select-rol"
                                                style="height: 30px; font-size: 12px; padding: 2px 10px;">
                                                @foreach ($rolesDisponibles as $valor => $etiqueta)
                                                    @if ($valor !== 'padre')
                                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn-action-flat btn-individual" title="Generar Alta">
                                            <i class="fa fa-flash text-orange"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 30px; color: #94a3b8;">
                                        No hay usuarios pendientes de creación.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="box-ayuda" style="background:#fff; border-radius:8px; border:1px solid #e2e8f0;">
                <div style="padding:12px 15px; border-bottom:1px solid #f0f2f5; font-weight:700;">
                    <i class="fa fa-shield text-blue"></i> Gestión de Accesos
                </div>
                <div style="padding:15px; font-size:12px; color:#64748b;">
                    <p>Al procesar los usuarios podrás descargar un <b>archivo PDF</b> con las credenciales.
                        Usa la casilla <b>"Enviar correo"</b> para controlar si se notifica a cada usuario por correo electrónico.</p>
                    @if (!$esDirector)
                    <p style="margin-top: 10px; color: #d97706;"><i class="fa fa-info-circle"></i> <b>Nota:</b> Para el
                        personal administrativo, asegúrate de seleccionar el rol correcto antes de procesar su alta.</p>
                    @else
                    <p style="margin-top: 10px; color: #d97706;"><i class="fa fa-info-circle"></i> <b>Nota:</b> Como
                        director de sección, únicamente puedes activar cuentas de <b>Padres de Familia</b>.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // ── FILTROS COMBINADOS (tipo + sección) ──────────────────
            function aplicarFiltros() {
                let tipo    = $('#filtro-tipo').val() || 'todos';
                let nivelId = String($('#filtro-seccion').val() || '');

                $('.row-pendiente').each(function() {
                    let rowTipo    = $(this).data('tipo');
                    let nivelIds   = String($(this).data('nivel-ids') || '').split(',').filter(Boolean);

                    let pasaTipo = (tipo === 'todos') || (rowTipo === tipo);

                    let pasaSeccion = true;
                    if (nivelId) {
                        if (rowTipo === 'contacto') {
                            pasaSeccion = nivelIds.includes(nivelId);
                        } else {
                            // Personal no tiene sección escolar: se oculta al filtrar por sección
                            pasaSeccion = false;
                        }
                    }

                    $(this).toggle(pasaTipo && pasaSeccion);
                });

                $('.check-user').prop('checked', false);
                $('#check-all').prop('checked', false);
                actualizarBotonMasivo();
            }

            $('#filtro-tipo').on('change', aplicarFiltros);
            $('#filtro-seccion').on('change', aplicarFiltros);

            // Lógica de Checkboxes
            $(document).on('change', '.check-user, #check-all', function() {
                if ($(this).attr('id') === 'check-all') {
                    $('.row-pendiente:visible .check-user').prop('checked', $(this).prop('checked'));
                }
                actualizarBotonMasivo();
            });

            function actualizarBotonMasivo() {
                let seleccionados = $('.check-user:checked').length;
                $('#count-select').text(seleccionados);
                $('#btn-generar-masivo').prop('disabled', seleccionados === 0);
            }

            // Disparadores
            $('#btn-generar-masivo').on('click', function() {
                recolectarYEnviar($('.check-user:checked').closest('tr'));
            });

            $(document).on('click', '.btn-individual', function() {
                recolectarYEnviar($(this).closest('tr'));
            });

            // Lógica recolectora unificada (Apunta a la ruta original)
            function recolectarYEnviar(filasDOM) {
                let contactos = [];
                let personal = [];
                let enviarCorreo = $('#chk-enviar-correo').is(':checked');

                filasDOM.each(function() {
                    let tipo = $(this).data('tipo');
                    let id = $(this).data('id');

                    if (tipo === 'contacto') {
                        contactos.push(id);
                    } else if (tipo === 'personal') {
                        let rol = $(this).find('.select-rol').val();
                        personal.push({ id: id, rol: rol });
                    }
                });

                let total = contactos.length + personal.length;
                let msgCorreo = enviarCorreo
                    ? 'Se enviará correo con credenciales a cada usuario.'
                    : '⚠ No se enviará correo. Descarga el PDF para entregar las credenciales manualmente.';

                if (!confirm("¿Dar de alta " + total + " cuenta(s)?\n\n" + msgCorreo)) return;

                fetch("{{ route('usuarios.generarMasivos') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            contacto_ids: contactos,
                            personal_datos: personal,
                            enviar_correo: enviarCorreo
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            location.reload();
                        } else {
                            alert("Hubo un problema:\n" + (res.mensaje || res.message || 'Error desconocido. Revisa el log del servidor.'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Ocurrió un error en el servidor. Verifica tu conexión o contacta a soporte.');
                    });
            }
        });

        // Auto-descarga del PDF al recargar la página si hay credenciales en sesión
        @if ($hayPdf)
            window.location.href = "{{ route('usuarios.credencialesPdf') }}";
        @endif
    </script>
@endpush
