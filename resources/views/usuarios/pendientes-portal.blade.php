@extends('layouts.master') {{-- ESTA LÍNEA ES VITAL --}}
@push('styles')
    <style>
        .content-wrapper {
            background-color: #f4f7f6 !important;
        }

        .con-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        /* Toolbar de Filtros */
        .con-filter-toolbar {
            display: flex;
            gap: 10px;
            background: #fff;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            border: 1px solid #e2e8f0;
            border-bottom: none;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-input {
            height: 35px;
            border-radius: 6px;
            border: 1px solid #d2d6de;
            padding: 0 10px;
            color: #475569;
            font-size: 13px;
            outline: none;
        }

        .filter-select {
            height: 35px;
            border-radius: 6px;
            border: 1px solid #d2d6de;
            font-size: 13px;
            min-width: 130px;
        }

        /* Tabla de Usuarios */
        .con-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }

        .con-table thead th {
            background: #fcfcfc;
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            padding: 15px;
            border-bottom: 2px solid #f0f2f5;
        }

        .con-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f3f7;
            vertical-align: middle;
        }

        /* Avatar y Nombre */
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 700;
            font-size: 14px;
        }

        .user-name {
            display: block;
            font-weight: 700;
            color: #2c3e50;
            font-size: 14px;
        }

        .user-email {
            display: block;
            font-size: 12px;
            color: #94a3b8;
        }

        /* Badges de Rol */
        .badge-rol {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .rol-admin {
            background: #fee2e2;
            color: #dc2626;
        }

        .rol-it {
            background: #e0e7ff;
            color: #4338ca;
        }

        .rol-padre {
            background: #fef3c7;
            color: #d97706;
        }

        .status-pill {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-online {
            background: #10b981;
        }

        .status-offline {
            background: #ef4444;
        }

        /* Botones de Acción */
        .btn-action-flat {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }

        .btn-action-flat:hover {
            transform: translateY(-2px);
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Box Ayuda */
        .box-ayuda {
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .ayuda-header {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f2f5;
            font-weight: 700;
            color: #2c3e50;
        }

        .ayuda-body {
            padding: 15px;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-9">
            {{-- Toolbar de Filtros --}}
            <div class="con-filter-toolbar">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <h4 style="margin:0; font-weight:800; color:#2c3e50;">
                        <i class="fa fa-user-plus text-orange"></i> Pendientes de Acceso
                    </h4>

                    {{-- Selector de Rol con diseño Flat --}}
                    <div style="display: flex; align-items: center; gap: 5px; margin-left: 10px;">
                        <span class="filter-label">Rol:</span>
                        <select id="filtro-rol-pendientes" class="filter-select" style="min-width: 140px;">
                            <option value="">Todos los Roles</option>
                            <option value="padre">Padre de Familia</option>
                            <option value="alumno">Alumno</option>
                        </select>
                    </div>
                </div>

                <div style="margin-left:auto;">
                    {{-- Botón siempre presente: Inactivo por defecto --}}
                    <button id="btn-generar-masivo" class="btn" disabled
                        style="border-radius:20px; font-weight:700; padding: 7px 25px; transition: all 0.3s ease; 
                           background-color: #cbd5e1; color: #64748b; cursor: not-allowed; border: none;">
                        <i class="fa fa-magic"></i> Dar de Alta Cuentas (<span id="count-select">0</span>)
                    </button>
                </div>
            </div>

            <div class="box" style="border:none; border-radius:0 0 8px 8px; box-shadow:0 2px 12px rgba(0,0,0,0.03);">
                <div class="box-body no-padding">
                    <table class="con-table" id="tabla-pendientes">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="check-all"></th>
                                <th>Nombre Completo</th>
                                <th>Familia</th>
                                <th>Correo Electrónico</th>
                                <th class="text-center">Rol</th>
                                <th width="100" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendientes as $p)
                                <tr data-id="{{ $p->id }}">
                                    <td><input type="checkbox" class="check-user" value="{{ $p->id }}"></td>
                                    <td><b class="user-name">{{ $p->nombre }}</b></td>
                                    <td><span class="con-badge-nivel"
                                            style="background:#f1f5f9; color:#475569;">{{ $p->familia->apellidos }}</span>
                                    </td>
                                    <td class="user-email" style="font-family:monospace;">{{ $p->email }}</td>
                                    <td class="text-center">
                                        <span class="badge-rol rol-padre">padre</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn-action-flat btn-individual" title="Generar ahora">
                                            <i class="fa fa-flash text-orange"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Módulo de Ayuda --}}
        <div class="col-md-3">
            <div class="box-ayuda">
                <div class="ayuda-header"><i class="fa fa-shield text-blue"></i> Gestión de Accesos</div>
                <div class="ayuda-body">
                    <p style="font-size:12px; color:#64748b;">Selecciona los contactos para habilitar el botón de alta
                        masiva.</p>
                    <ul style="padding-left:15px; font-size:11px; color:#64748b;">
                        <li>Contraseña alfanumérica (8 caracteres).</li>
                        <li>Generación de ficha imprimible.</li>
                        <li>Vinculación automática de perfil.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE RESULTADOS --}}
    <x-modal id="modal-credenciales" title="Credenciales de Acceso Generadas">
        <div id="contenedor-credenciales" style="max-height: 450px; overflow-y: auto; padding: 5px;">
            {{-- Aquí se inyectan las tarjetas con nombre, correo y contraseña --}}
        </div>
        <div class="modal-footer">
            <button class="btn btn-default pull-left" data-dismiss="modal">Cerrar y Actualizar</button>
            <button class="btn btn-danger" onclick="imprimirCredenciales()">
                <i class="fa fa-print"></i> Imprimir Credenciales
            </button>
        </div>
    </x-modal>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            // 1. LÓGICA DE HABILITACIÓN DEL BOTÓN (CORREGIDA)
            // Usamos $(document).on para que funcione aunque la tabla cambie
            $(document).on('change', '.check-user, #check-all', function() {

                // Si se marca el checkbox del encabezado, marcar todos los demás
                if ($(this).attr('id') === 'check-all') {
                    $('.check-user').prop('checked', $(this).prop('checked'));
                }

                // Contar cuántos están seleccionados actualmente
                let seleccionados = $('.check-user:checked').length;

                // Actualizar el número en el botón
                $('#count-select').text(seleccionados);

                let btn = $('#btn-generar-masivo');

                if (seleccionados > 0) {
                    // ESTADO ACTIVO: Azul SaaS y cursor de click
                    btn.prop('disabled', false).css({
                        'background-color': '#3c8dbc',
                        'color': '#fff',
                        'cursor': 'pointer',
                        'opacity': '1',
                        'box-shadow': '0 4px 10px rgba(60, 141, 188, 0.4)'
                    });
                } else {
                    // ESTADO INACTIVO: Gris tenue y cursor prohibido
                    btn.prop('disabled', true).css({
                        'background-color': '#cbd5e1',
                        'color': '#64748b',
                        'cursor': 'not-allowed',
                        'opacity': '0.7',
                        'box-shadow': 'none'
                    });
                }
            });

            // 2. FILTRO VISUAL POR ROL
            $('#filtro-rol-pendientes').on('change', function() {
                let valor = $(this).val().toLowerCase();
                $('#tabla-pendientes tbody tr').each(function() {
                    let texto = $(this).text().toLowerCase();
                    $(this).toggle(texto.indexOf(valor) > -1);
                });
            });

            // 3. EJECUCIÓN DEL PROCESO (MASIVO)
            $('#btn-generar-masivo').on('click', function() {
                let ids = $('.check-user:checked').map(function() {
                    return $(this).val();
                }).get();
                lanzarProceso(ids);
            });

            // 4. EJECUCIÓN INDIVIDUAL (ICONO DE RAYO)
            $(document).on('click', '.btn-individual', function() {
                let id = $(this).closest('tr').data('id');
                lanzarProceso([id]);
            });
        });

        // Función que conecta con el Controlador
        function lanzarProceso(ids) {
            if (!confirm("¿Desea crear las cuentas para los " + ids.length + " seleccionados?")) return;

            fetch("{{ route('usuarios.generarMasivos') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        contacto_ids: ids
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        let html = '';
                        res.data.forEach(u => {
                            html += `
                    <div class="credencial-ficha" style="border: 2px solid #e2e8f0; padding: 15px; margin-bottom: 15px; border-radius: 10px; background: #fff;">
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:8px; margin-bottom:10px;">
                            <span style="font-weight:800; color:#3c8dbc; font-size:13px;">DATOS DE ACCESO</span>
                            <i class="fa fa-lock text-muted"></i>
                        </div>
                        <p style="margin:4px 0; font-size:14px;"><strong>Nombre:</strong> ${u.nombre}</p>
                        <p style="margin:4px 0; font-size:14px;"><strong>Usuario:</strong> ${u.email}</p>
                        <p style="margin:4px 0; font-size:14px;"><strong>Contraseña:</strong> 
                            <span style="font-family:monospace; font-weight:bold; background:#f1f5f9; padding:2px 6px; border-radius:4px; color:#1e293b; border:1px solid #cbd5e1;">${u.password}</span>
                        </p>
                    </div>`;
                        });
                        $('#contenedor-credenciales').html(html);
                        $('#modal-credenciales').modal('show');

                        // Recargar al cerrar el modal para limpiar la lista
                        $('#modal-credenciales').on('hidden.bs.modal', function() {
                            location.reload();
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud.');
                });
        }

        function imprimirCredenciales() {
            let contenido = document.getElementById('contenedor-credenciales').innerHTML;
            let ventana = window.open('', 'PRINT', 'height=600,width=800');
            ventana.document.write('<html><head><title>Fichas de Acceso</title>');
            ventana.document.write(
                '<style>body{font-family:sans-serif; padding:20px;} .credencial-ficha{page-break-inside:avoid; border:1px solid #ccc; margin-bottom:20px; padding:20px; border-radius:10px;}</style>'
            );
            ventana.document.write('</head><body><h2 style="text-align:center;">Credenciales de Usuario</h2>');
            ventana.document.write(contenido);
            ventana.document.write('</body></html>');
            ventana.document.close();
            ventana.focus();
            setTimeout(() => {
                ventana.print();
                ventana.close();
            }, 500);
        }
    </script>
@endpush
