@extends('layouts.master')

@section('page_title', 'Conceptos')
@section('page_subtitle', 'Conceptos de cobro')

@section('breadcrumb')
    <li class="active">Conceptos</li>
@endsection

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
    <div class="con-stats">
        <div style="display: flex; gap: 15px;">
            <div
                style="background: #fff; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 12px;">
                <i class="fa fa-users" style="color: #3498db; font-size: 20px;"></i>
                <div>
                    <span
                        style="display: block; font-size: 18px; font-weight: 800; line-height: 1;">{{ $usuarios->count() }}</span>
                    <span style="font-size: 10px; color: #94a3b8; text-transform: uppercase;">Usuarios Activos</span>
                </div>
            </div>
            {{-- Acceso a Pendientes de Portal (Funcionalidad del controlador) --}}
            <a href="{{ route('usuarios.pendientes-portal') }}"
                style="background: #fff; padding: 10px 20px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                <i class="fa fa-user-plus text-orange" style="font-size: 18px;"></i>
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Cuentas
                    Pendientes</span>
            </a>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-success btn-sm"
            style="border-radius: 6px; font-weight: 600;">
            <i class="fa fa-plus"></i> Crear Usuario
        </a>
    </div>

    <div class="row">
        <div class="col-md-9">
            {{-- Toolbar de Filtros (Submit automático al cambiar) --}}
            <div class="con-filter-toolbar">
                <form method="GET" action="{{ route('usuarios.index') }}"
                    style="display: flex; gap: 10px; width: 100%; align-items: center;">
                    <input type="text" name="buscar" class="filter-input" placeholder="Buscar por nombre o email..."
                        value="{{ request('buscar') }}" style="flex-grow: 1;">

                    <select name="rol" class="filter-select" onchange="this.form.submit()">
                        <option value="">Cualquier Rol</option>
                        <option value="administrador" {{ request('rol') == 'administrador' ? 'selected' : '' }}>Admin
                        </option>
                        <option value="it" {{ request('rol') == 'it' ? 'selected' : '' }}>IT Tech</option>
                        <option value="padre" {{ request('rol') == 'padre' ? 'selected' : '' }}>Padre de Familia</option>
                    </select>

                    <select name="activo" class="filter-select" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="1" {{ request('activo') == '1' ? 'selected' : '' }}>Solo Activos</option>
                        <option value="0" {{ request('activo') == '0' ? 'selected' : '' }}>Solo Inactivos</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm" style="height: 35px; border-radius: 6px;">
                        <i class="fa fa-search"></i>
                    </button>
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
                                <th width="120" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $usuario)
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
                                            class="badge-rol 
                                            {{ $usuario->rol == 'administrador' ? 'rol-admin' : ($usuario->rol == 'it' ? 'rol-it' : 'rol-padre') }}">
                                            {{ $usuario->rol }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; color: #64748b;">
                                            {{ $usuario->cicloSeleccionado->nombre ?? 'Sin seleccionar' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="status-pill {{ $usuario->activo ? 'status-online' : 'status-offline' }}"></span>
                                        <span style="font-size: 11px; font-weight: 600; color: #64748b;">
                                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div style="display: flex; gap: 5px; justify-content: center;">
                                            <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn-action-flat"
                                                title="Editar">
                                                <i class="fa fa-pencil text-blue"></i>
                                            </a>

                                            {{-- Lógica destroy: no permitir desactivar propia cuenta --}}
                                            @if ($usuario->id !== auth()->id())
                                                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-action-flat" title="Desactivar"
                                                        onclick="return confirm('¿Desactivar acceso?')">
                                                        <i class="fa fa-ban text-red"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna de Ayuda --}}
        <div class="col-md-3">
            <div class="box-ayuda">
                <div class="ayuda-header"><i class="fa fa-info-circle text-blue"></i> Ayuda de Seguridad</div>
                <div class="ayuda-body">
                    <div style="font-size: 13px; color: #475569;">
                        <p><b>Administradores:</b> Acceso total a finanzas y configuración académica.</p>
                        <p><b>IT Tech:</b> Soporte técnico y gestión de infraestructura.</p>
                        <p><b>Padres:</b> Acceso limitado al portal de consulta de sus hijos.</p>

                        <div style="border-top: 1px solid #f1f5f9; margin: 15px 0;"></div>

                        <div style="background: #fef2f2; border: 1px solid #fee2e2; padding: 10px; border-radius: 6px;">
                            <span style="color: #991b1b; font-size: 12px; font-weight: 700;">
                                <i class="fa fa-shield"></i> Protección:
                            </span>
                            <p style="font-size: 11px; color: #991b1b; margin: 5px 0 0;">
                                Por seguridad, el sistema <b>bloquea la autodesactivación</b> para prevenir que el
                                administrador principal pierda el acceso accidentalmente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
