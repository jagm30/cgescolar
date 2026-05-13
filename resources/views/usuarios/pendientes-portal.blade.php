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
        }

        .filter-select {
            height: 35px;
            border-radius: 6px;
            border: 1px solid #d2d6de;
            padding: 0 10px;
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
    <div class="row">
        <div class="col-md-9">
            <div class="con-filter-toolbar">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <h4 style="margin:0; font-weight:800; color:#2c3e50;">
                        <i class="fa fa-user-plus text-orange"></i> Pendientes de Acceso
                    </h4>
                    <select id="filtro-rol-pendientes" class="filter-select">
                        <option value="">Todos los Roles</option>
                        <option value="padre">Padre de Familia</option>
                        <option value="alumno">Alumno</option>
                    </select>
                </div>
                <div style="margin-left:auto;">
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
                                <th>Familia</th>
                                <th>Correo Electrónico</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendientes as $p)
                                <tr data-id="{{ $p->id }}">
                                    <td class="text-center"><input type="checkbox" class="check-user"
                                            value="{{ $p->id }}"></td>
                                    <td><b style="color: #2c3e50;">{{ $p->nombre }}</b></td>
                                    <td><span class="con-badge-familia">{{ $p->familia->apellidos ?? 'Sin Familia' }}</span>
                                    </td>
                                    <td style="font-family:monospace; color: #64748b;">{{ $p->email }}</td>
                                    <td class="text-center">
                                        <button class="btn-action-flat btn-individual" title="Generar Alta"><i
                                                class="fa fa-flash text-orange"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="box-ayuda" style="background:#fff; border-radius:8px; border:1px solid #e2e8f0;">
                <div style="padding:12px 15px; border-bottom:1px solid #f0f2f5; font-weight:700;"><i
                        class="fa fa-shield text-blue"></i> Gestión de Accesos</div>
                <div style="padding:15px; font-size:12px; color:#64748b;">
                    <p>Al procesar los usuarios, se generará y abrirá automáticamente un <b>archivo PDF</b> con las
                        credenciales.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Lógica para habilitar el botón
            $(document).on('change', '.check-user, #check-all', function() {
                if ($(this).attr('id') === 'check-all') {
                    $('.check-user').prop('checked', $(this).prop('checked'));
                }

                let seleccionados = $('.check-user:checked').length;
                $('#count-select').text(seleccionados);

                if (seleccionados > 0) {
                    $('#btn-generar-masivo').prop('disabled', false);
                } else {
                    $('#btn-generar-masivo').prop('disabled', true);
                }
            });

            // Disparadores
            $('#btn-generar-masivo').on('click', function() {
                let ids = $('.check-user:checked').map(function() {
                    return $(this).val();
                }).get();
                procesarPeticion(ids);
            });

            $(document).on('click', '.btn-individual', function() {
                let id = $(this).closest('tr').data('id');
                procesarPeticion([id]);
            });
        });

        // Función AJAX Limpia
        function procesarPeticion(ids) {
            if (!confirm("¿Generar cuenta para " + ids.length + " contactos? Se abrirá un PDF automáticamente.")) return;

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
                        // Abrir PDF en nueva pestaña
                        window.open("{{ route('usuarios.credencialesPdf') }}", '_blank');

                        // Recargar esta página para actualizar la tabla (1 segundo de delay)
                        setTimeout(() => location.reload(), 1000);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Ocurrió un error en el servidor.');
                });
        }
    </script>
@endpush
