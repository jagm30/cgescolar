@extends('layouts.master')

@section('page_title', 'Usuarios')
@section('page_subtitle', 'Gestión de usuarios')

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

        .rol-admisiones {
            background: #d1fae5;
            color: #065f46;
        }

        /* Nuevos Estilos para el Estado (Cápsula Ovalada) */
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            /* Píldora */
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .dot-active {
            background: #16a34a;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .dot-inactive {
            background: #dc2626;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

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
            padding: 0;
            margin: 0;
        }

        .btn-action-flat:hover {
            transform: translateY(-2px);
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-action-flat:disabled {
            background: #f1f5f9;
            color: #cbd5e1;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
@endpush

@section('content')

    {{-- LÓGICA PARA ATRAPAR EL MENSAJE Y MANTENER VIVO EL PDF --}}
    @php
        $mensajeMostrar = session('mensaje') ?? session('mensaje_persistente');
        session()->forget('mensaje_persistente');

        $hayPdf = session()->has('credenciales_nuevas');

        // ¡CRÍTICO PARA QUE EL BOTÓN FUNCIONE! Mantiene los datos vivos una petición más
        if ($hayPdf) {
            session()->keep(['credenciales_nuevas']);
        }
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

                    {{-- Botón para descargar el PDF manualmente si hay uno disponible --}}
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
    {{-- FIN DE LA TARJETA --}}

    <div class="con-stats">
        <div style="display: flex; gap: 15px;">
            <div
                style="background: #fff; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 12px;">
                <i class="fa fa-users" style="color: #3498db; font-size: 20px;"></i>
                <div>
                    <span
                        style="display: block; font-size: 18px; font-weight: 800; line-height: 1;">{{ $usuarios->total() }}</span>
                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">Registros</span>
                </div>
            </div>
            <a href="{{ route('usuarios.pendientes-portal') }}"
                style="background: #fff; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                <i class="fa fa-user-plus text-orange" style="font-size: 18px;"></i>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Cuentas
                    Pendientes</span>
            </a>
        </div>

        {{-- Botón Crear Usuario: Forma de píldora (20px) --}}
        <button class="btn btn-success btn-sm"
            style="border-radius: 20px; font-weight: 600; padding: 6px 18px; box-shadow: 0 2px 6px rgba(0, 166, 90, 0.3);"
            data-toggle="modal" data-target="#modal-crear-usuario">
            <i class="fa fa-plus"></i> Crear Usuario
        </button>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="con-filter-toolbar">
                <form method="GET" action="{{ route('usuarios.index') }}"
                    style="display: flex; gap: 10px; width: 100%; align-items: center;" id="form-filtros-usuario">

                    {{-- Botón Ayuda: Forma de píldora (20px) --}}
                    <button type="button" class="btn btn-info btn-sm"
                        style="border-radius: 20px; font-weight: 600; padding: 6px 18px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0, 192, 239, 0.3);"
                        data-toggle="modal" data-target="#modalAyuda" title="Ayuda del Módulo">
                        <i class="fa fa-question-circle" style="margin-right: 3px;"></i> Ayuda
                    </button>

                    <select name="mostrar" class="filter-select" onchange="this.form.submit()"
                        style="min-width: 75px; margin-left:10px;">
                        <option value="10" {{ request('mostrar') == '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('mostrar') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('mostrar') == '50' ? 'selected' : '' }}>50</option>
                    </select>

                    <input type="text" name="buscar" class="filter-input" placeholder="Buscar por nombre o email..."
                        value="{{ request('buscar') }}" style="flex-grow: 1;">

                    <select name="rol" class="filter-select" onchange="this.form.submit()">
                        <option value="">Cualquier Rol</option>
                        <option value="administrador" {{ request('rol') == 'administrador' ? 'selected' : '' }}>
                            Administrador</option>
                        <option value="caja" {{ request('rol') == 'caja' ? 'selected' : '' }}>Caja</option>
                        <option value="recepcion" {{ request('rol') == 'recepcion' ? 'selected' : '' }}>Recepción</option>
                        <option value="admisiones" {{ request('rol') == 'admisiones' ? 'selected' : '' }}>Admisiones</option>
                        <option value="padre" {{ request('rol') == 'padre' ? 'selected' : '' }}>Padre de Familia</option>
                    </select>

                    <select name="activo" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos los Estados</option>
                        <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Solo Activos</option>
                        <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Solo Inactivos</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm" style="height: 35px; border-radius: 6px;">
                        <i class="fa fa-search"></i>
                    </button>

                    <a href="{{ route('usuarios.index') }}" class="btn btn-default btn-sm"
                        style="height: 35px; border-radius: 6px; display: inline-flex; align-items: center;"
                        title="Borrar Filtros">
                        <i class="fa fa-eraser"></i>
                    </a>
                </form>
            </div>

            <div class="box"
                style="border: none; border-radius: 0 0 8px 8px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);">
                <div class="box-body no-padding">
                    <table class="con-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol / Permisos</th>
                                <th>Ciclo Actual</th>
                                <th class="text-center">Estado</th>
                                <th width="160" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($usuarios as $usuario)
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="user-name">{{ $usuario->nombre }}</span>
                                                <span class="user-email">{{ $usuario->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span
                                            class="badge-rol @if($usuario->rol == 'administrador') rol-admin @elseif($usuario->rol == 'admisiones') rol-admisiones @elseif($usuario->rol == 'it') rol-it @else rol-padre @endif">
                                            {{ $usuario->rol }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; color: #64748b;">
                                            {{ $usuario->cicloSeleccionado->nombre ?? 'Sin seleccionar' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- Nueva cápsula ovalada para el ESTADO --}}
                                        @if ($usuario->activo)
                                            <span class="badge-status badge-active">
                                                <span class="dot-active"></span> Activo
                                            </span>
                                        @else
                                            <span class="badge-status badge-inactive">
                                                <span class="dot-inactive"></span> Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    {{-- COLUMNA DE ACCIONES ALINEADA CON FLEXBOX --}}
                                    <td class="text-center">
                                        <div
                                            style="display: flex; gap: 5px; justify-content: center; align-items: center;">

                                            @if ($usuario->id !== auth()->id())
                                                <button class="btn-action-flat btn-modal-edit" title="Editar"
                                                    data-id="{{ $usuario->id }}" data-nombre="{{ $usuario->nombre }}"
                                                    data-rol="{{ $usuario->rol }}">
                                                    <i class="fa fa-pencil text-blue"></i>
                                                </button>
                                            @else
                                                <button class="btn-action-flat" disabled
                                                    title="Por seguridad, no puedes editar tus propios permisos desde aquí. Ve a 'Mi Perfil'.">
                                                    <i class="fa fa-pencil" style="color: #cbd5e1;"></i>
                                                </button>
                                            @endif

                                            @if (!$usuario->activo)
                                                <form action="{{ route('usuarios.reactivar', $usuario->id) }}"
                                                    method="POST" style="margin: 0; display: flex;">
                                                    @csrf
                                                    <button type="submit" class="btn-action-flat"
                                                        title="Reactivar Usuario">
                                                        <i class="fa fa-arrow-up text-green"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn-action-flat" disabled
                                                    title="El usuario ya está activo">
                                                    <i class="fa fa-arrow-up" style="color: #cbd5e1;"></i>
                                                </button>
                                            @endif

                                            @if ($usuario->id !== auth()->id() && $usuario->activo)
                                                <form action="{{ route('usuarios.destroy', $usuario->id) }}"
                                                    method="POST" style="margin: 0; display: flex;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-action-flat" title="Desactivar"
                                                        onclick="return confirm('¿Desactivar acceso al portal?')">
                                                        <i class="fa fa-ban text-red"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($usuario->id !== auth()->id())
                                                <button class="btn-action-flat btn-delete-permanent"
                                                    title="Borrar de forma permanente" data-id="{{ $usuario->id }}"
                                                    data-nombre="{{ $usuario->nombre }}">
                                                    <i class="fa fa-trash text-red" style="font-weight:900;"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center" style="padding:20px; color:#94a3b8;">No se
                                        encontraron usuarios que coincidan con la búsqueda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($usuarios->total() > 0)
                    <div class="box-footer clearfix"
                        style="background: #fff; border-radius: 0 0 8px 8px; padding: 15px; border-top: 1px solid #f0f3f7;">
                        <div class="pull-left" style="color: #64748b; font-size: 12px; margin-top: 8px;">
                            Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} de
                            {{ $usuarios->total() }} usuarios
                        </div>
                        <div class="pull-right" style="margin: 0;">
                            {{ $usuarios->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- ══ MODAL AYUDA ══ --}}
    {{-- ========================================== --}}
    <x-modal id="modalAyuda" title="Guía del Módulo de Usuarios" size="modal-md">
        <div style="line-height:1.7;">

            <p style="font-size:13px;color:#555;margin-bottom:16px;">
                Esta pantalla te permite gestionar los <strong>usuarios del sistema</strong> y sus permisos.
                Puedes dar de alta nuevas cuentas, suspender accesos o modificar credenciales de ingreso.
            </p>

            <div style="display:flex;flex-direction:column;gap:12px;">

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-user-plus text-orange" style="margin-top:4px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Cuentas Pendientes</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Genera credenciales masivas para padres de familia que tienen acceso autorizado pero aún no
                            tienen usuario.
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-plus text-green" style="margin-top:4px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Crear Usuario Manual</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Registra manualmente a personal administrativo (Admin, Recepción, Caja, IT) o a padres
                            específicos.
                        </p>
                    </div>
                </div>

                <hr style="margin:10px 0;">

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-pencil text-blue" style="margin-top:4px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Editar (Modificar credenciales)</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Modifica el rol o genera una nueva contraseña. Al hacerlo, tendrás la opción de generar un PDF
                            con las credenciales actualizadas y enviar un correo automático.
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-ban text-red" style="margin-top:4px;flex-shrink:0;"></i>
                    <div>
                        <strong style="font-size:13px;">Desactivar / Reactivar Accesos</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            <i class="fa fa-ban text-red"></i> <b>Desactivar:</b> Suspende temporalmente el acceso del
                            usuario al sistema.<br>
                            <i class="fa fa-arrow-up text-green"></i> <b>Reactivar:</b> Devuelve el acceso a una cuenta
                            previamente suspendida.
                        </p>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <i class="fa fa-trash text-red" style="margin-top:4px;flex-shrink:0; font-weight:900;"></i>
                    <div>
                        <strong style="font-size:13px;">Borrar Definitivamente</strong>
                        <p style="font-size:12px;color:#777;margin:2px 0 0;">
                            Elimina la cuenta definitivamente del sistema y revoca el acceso en la ficha original del
                            contacto.
                        </p>
                    </div>
                </div>

            </div>

            <hr style="margin:16px 0 10px;">

            <div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 10px; border-radius: 6px;">
                <span style="color: #991b1b; font-size: 12px; font-weight: 700;">
                    <i class="fa fa-shield"></i> Protección de Seguridad:
                </span>
                <p style="font-size: 11px; color: #991b1b; margin: 5px 0 0; line-height: 1.4;">
                    Por seguridad, el sistema bloquea acciones directas sobre tu propia sesión activa para evitar la pérdida
                    accidental de acceso.
                </p>
            </div>

        </div>

        <div class="clearfix" style="padding-top:15px;">
            <button type="button" class="btn btn-default pull-right" data-dismiss="modal">
                <i class="fa fa-times"></i> Cerrar Ayuda
            </button>
        </div>
    </x-modal>

    {{-- MODAL CREAR USUARIO --}}
    <x-modal id="modal-crear-usuario" title="Registrar Nuevo Usuario">
        <form id="form-crear-usuario">
            @csrf
            <div class="form-group">
                <label>Nombre Completo <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan López Ramos" required>
            </div>
            <div class="form-group">
                <label>Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="usuario@correo.com" required>
            </div>
            <div class="form-group">
                <label>Rol del Usuario <span class="text-danger">*</span></label>
                <select name="rol" class="form-control" required>
                    <option value="padre">Padre de Familia</option>
                    <option value="recepcion">Recepción</option>
                    <option value="caja">Caja</option>
                    <option value="admisiones">Admisiones</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>Contraseña <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" id="create-password" name="password" class="form-control" required
                        placeholder="Mínimo 6 caracteres">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-toggle-pass" type="button" data-target="#create-password"><i
                                class="fa fa-eye-slash"></i></button>
                    </span>
                </div>
                <div style="margin-top: 8px;">
                    <label style="font-weight: 500; cursor:pointer;">
                        <input type="checkbox" id="chk-create-auto" checked> Generar contraseña automáticamente
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar Usuario</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDITAR USUARIO --}}
    <x-modal id="modal-editar-usuario" title="Modificar Perfil de Usuario">
        <form id="form-editar-usuario">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id">

            <div class="form-group">
                <label>Usuario Seleccionado:</label>
                <input type="text" id="edit-nombre-lbl" class="form-control" readonly
                    style="background:#f8f9fa; font-weight:700;">
            </div>

            <div class="form-group">
                <label>Rol / Permisos <span class="text-danger">*</span></label>
                <select name="rol" id="edit-rol" class="form-control" required>
                    <option value="padre">Padre de Familia</option>
                    <option value="recepcion">Recepción</option>
                    <option value="caja">Caja</option>
                    <option value="admisiones">Admisiones</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nueva Contraseña <small class="text-muted">(Dejar vacío si no se cambia)</small></label>
                <div class="input-group">
                    <input type="password" id="edit-password" name="password" class="form-control"
                        placeholder="Escribe para cambiar la actual">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-toggle-pass" type="button" data-target="#edit-password"><i
                                class="fa fa-eye"></i></button>
                    </span>
                </div>
                <div style="margin-top: 8px;">
                    <label style="font-weight: 500; cursor:pointer;">
                        <input type="checkbox" id="chk-edit-auto"> Generar contraseña automáticamente
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fa fa-refresh"></i> Guardar Cambios</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function generarPassword() {
                let caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#';
                let pass = '';
                for (let i = 0; i < 8; i++) {
                    pass += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
                }
                return pass;
            }

            if ($('#chk-create-auto').is(':checked')) {
                $('#create-password').val(generarPassword());
            }

            $('#chk-create-auto').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#create-password').val(generarPassword()).attr('type', 'text');
                    $('[data-target="#create-password"] i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $('#create-password').val('').attr('type', 'password').focus();
                    $('[data-target="#create-password"] i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#chk-edit-auto').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#edit-password').val(generarPassword()).attr('type', 'text');
                    $('[data-target="#edit-password"] i').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    $('#edit-password').val('').attr('type', 'password').focus();
                    $('[data-target="#edit-password"] i').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $(document).on('click', '.btn-toggle-pass', function() {
                let targetInput = $($(this).data('target'));
                let icon = $(this).find('i');
                if (targetInput.attr('type') === 'password') {
                    targetInput.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    targetInput.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('.btn-modal-edit').on('click', function() {
                let id = $(this).data('id');
                let nombre = $(this).data('nombre');
                let rol = $(this).data('rol');

                $('#edit-id').val(id);
                $('#edit-nombre-lbl').val(nombre);
                $('#edit-rol').val(rol.toLowerCase());
                $('#edit-password').val('');
                $('#chk-edit-auto').prop('checked', false);
                $('#modal-editar-usuario').modal('show');
            });

            // CREAR USUARIO
            $('#form-crear-usuario').on('submit', function(e) {
                e.preventDefault();
                let datos = $(this).serialize();

                fetch("{{ route('usuarios.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: datos
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            $('#modal-crear-usuario').modal('hide');
                            location.reload(); // Recarga limpia e instantánea
                        } else {
                            alert("No se pudo crear el usuario: \n" + res.mensaje);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Ocurrió un error de red o de servidor.");
                    });
            });

            // EDITAR USUARIO
            $('#form-editar-usuario').on('submit', function(e) {
                e.preventDefault();
                let id = $('#edit-id').val();
                let generarPdf = confirm(
                    "Se actualizarán los datos de este usuario.\n\n¿Deseas generar un archivo PDF con las credenciales actualizadas?"
                );

                let datosForm = $(this).serializeArray();
                datosForm.push({
                    name: 'generar_pdf',
                    value: generarPdf ? 1 : 0
                });

                let url = "{{ route('usuarios.update', ':id') }}".replace(':id', id);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: $.param(datosForm)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            $('#modal-editar-usuario').modal('hide');
                            location.reload(); // Recarga limpia e instantánea
                        }
                    });
            });

            // BORRAR USUARIO
            $('.btn-delete-permanent').on('click', function() {
                let id = $(this).data('id');
                let nombre = $(this).data('nombre');

                if (!confirm(
                        `ADVERTENCIA CRÍTICA:\n¿Está completamente seguro de eliminar definitivamente al usuario '${nombre}'?\nEsta acción lo borrará de la tabla usuarios y desactivará su acceso en contactos familiares.`
                    )) return;

                let url = "{{ route('usuarios.forzarEliminar', ':id') }}".replace(':id', id);

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            location.reload();
                        } else {
                            alert("Error al borrar: \n" + res.mensaje);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Ocurrió un error grave de red o de servidor. Revisa la consola.");
                    });
            });
        });
    </script>
@endpush
