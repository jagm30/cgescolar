@extends('layouts.master')

@section('page_title', $empleado->nombre_completo)
@section('page_subtitle', 'Perfil de empleado')

@section('breadcrumb')
    <li><a href="{{ route('personal.index') }}">Personal</a></li>
    <li class="active">{{ $empleado->nombre_completo }}</li>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- ── Tarjeta lateral con foto ── --}}
        <div class="col-md-3">
            <div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);text-align:center;padding:24px 16px;">

                @if ($empleado->foto_url)
                    <img src="{{ asset('storage/' . $empleado->foto_url) }}"
                         alt="{{ $empleado->nombre_completo }}"
                         style="width:110px;height:110px;border-radius:50%;object-fit:cover;border:4px solid #e0e7ef;margin-bottom:12px;">
                @else
                    <div style="width:110px;height:110px;border-radius:50%;background:#e8f0fb;color:#3c8dbc;
                                display:inline-flex;align-items:center;justify-content:center;
                                font-size:44px;font-weight:700;border:4px solid #c8dff5;margin-bottom:12px;">
                        {{ strtoupper(substr($empleado->nombre, 0, 1)) }}
                    </div>
                @endif

                <h4 style="margin:0 0 4px;font-weight:700;color:#1a2634;">{{ $empleado->nombre_completo }}</h4>
                <p style="margin:0 0 10px;font-size:13px;color:#7a8898;">No. {{ $empleado->numero_empleado }}</p>

                @if ($empleado->tipo)
                    @php
                        $badgeMap = [
                            'docente'        => 'per-badge-docente',
                            'administrativo' => 'per-badge-administrativo',
                            'mantenimiento'  => 'per-badge-mantenimiento',
                        ];
                    @endphp
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;padding:4px 12px;border-radius:12px;"
                          class="{{ $badgeMap[$empleado->tipo->value] ?? '' }}">
                        {{ $empleado->tipo->etiqueta() }}
                    </span>
                @endif

                <div style="margin-top:12px;">
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:12px;
                                 {{ $empleado->activo ? 'background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;' : 'background:#f4f6f8;color:#7a8898;border:1px solid #d0d9e2;' }}">
                        <i class="fa fa-circle" style="font-size:7px;"></i>
                        {{ $empleado->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                @if (auth()->user()->esAdministrador())
                    <div style="margin-top:18px;display:flex;flex-direction:column;gap:6px;">
                        <a href="{{ route('personal.edit', $empleado) }}" class="btn btn-primary btn-sm btn-flat btn-block">
                            <i class="fa fa-pencil"></i> Editar
                        </a>
                        <form action="{{ route('personal.destroy', $empleado) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm btn-flat btn-block"
                                    onclick="return confirm('¿Eliminar a {{ $empleado->nombre_completo }}? Esta acción no se puede deshacer.')">
                                <i class="fa fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>

        {{-- ── Datos del empleado ── --}}
        <div class="col-md-9">
            <div class="box" style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Información del empleado</h3>
                </div>
                <div class="box-body">
                    <div class="row">

                        <div class="col-md-6">
                            <table class="table table-condensed" style="font-size:13px;">
                                <tr>
                                    <th style="width:40%;color:#7a8898;border-top:none;padding-top:6px;">No. Empleado</th>
                                    <td style="border-top:none;padding-top:6px;">
                                        <span style="font-family:monospace;background:#f0f3f7;padding:2px 8px;border-radius:4px;border:1px solid #e2e8f0;">
                                            {{ $empleado->numero_empleado }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#7a8898;">Nombre completo</th>
                                    <td>{{ $empleado->nombre_completo }}</td>
                                </tr>
                                <tr>
                                    <th style="color:#7a8898;">Tipo</th>
                                    <td>{{ $empleado->tipo?->etiqueta() ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th style="color:#7a8898;">Teléfono</th>
                                    <td>{{ $empleado->telefono }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <table class="table table-condensed" style="font-size:13px;">
                                <tr>
                                    <th style="width:35%;color:#7a8898;border-top:none;padding-top:6px;">Correo</th>
                                    <td style="border-top:none;padding-top:6px;">
                                        <a href="mailto:{{ $empleado->email }}">{{ $empleado->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#7a8898;">RFC</th>
                                    <td>
                                        @if ($empleado->rfc)
                                            <span style="font-family:monospace;background:#f0f3f7;padding:2px 8px;border-radius:4px;border:1px solid #e2e8f0;">
                                                {{ $empleado->rfc }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="color:#7a8898;">Estatus</th>
                                    <td>{{ $empleado->activo ? 'Activo' : 'Inactivo' }}</td>
                                </tr>
                                <tr>
                                    <th style="color:#7a8898;">Alta</th>
                                    <td>{{ \Carbon\Carbon::parse($empleado->creado_at)->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>

                    </div>

                    <div style="margin-top:8px;padding-top:12px;border-top:1px solid #f0f3f7;">
                        <strong style="font-size:12px;color:#7a8898;text-transform:uppercase;letter-spacing:.05em;">
                            <i class="fa fa-map-marker"></i> Domicilio
                        </strong>
                        <p style="margin:6px 0 0;font-size:13px;">{{ $empleado->domicilio }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<style>
    .per-badge-docente        { background:#e8f3ff; color:#2c6fad; border:1px solid #b3d4f5; }
    .per-badge-administrativo { background:#e8f8f0; color:#00875a; border:1px solid #b3e8d0; }
    .per-badge-mantenimiento  { background:#fff8e6; color:#b45309; border:1px solid #fcd97d; }
</style>
