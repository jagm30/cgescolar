@extends('layouts.master')

@section('page_title', 'Catálogo de becas')

@section('breadcrumb')
    <li><a href="{{ route('becas.index') }}">Becas</a></li>
    <li class="active">Catálogo</li>
@endsection

@push('styles')
    <style>
        /* ══ TABLA ══ */
        .bec-table {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .bec-table thead tr th {
            background: #f4f6f8;
            color: #6b7a8d;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 10px 14px;
            border-bottom: 2px solid #e0e6ed;
            border-top: none;
            white-space: nowrap;
        }

        .bec-table tbody tr { border-bottom: 1px solid #f0f3f7; }
        .bec-table tbody tr:last-child { border-bottom: none; }
        .bec-table tbody tr:hover td { background: #f0f7ff !important; }

        .bec-table td {
            padding: 10px 14px;
            vertical-align: middle;
            font-size: 13px;
        }

        .bec-nombre { font-size: 14px; font-weight: 700; color: #1a2634; line-height: 1.2; }
        .bec-sub    { font-size: 11px; color: #aab; margin-top: 2px; }

        .bec-discount-tag,
        .bec-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 12px;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .bec-discount-tag   { background: #f3e8fd; color: #6b21a8; border: 1px solid #d8b4fe; }
        .bec-badge-activa   { background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0; }
        .bec-badge-inactiva { background: #f4f6f8; color: #7a8898; border: 1px solid #d0d9e2; }
        .bec-badge-porcentaje { background: #e8f3ff; color: #2c6fad; border: 1px solid #c9e3ff; }
        .bec-badge-monto    { background: #fff8e6; color: #b45309; border: 1px solid #fcd97d; }

        /* Acciones */
        .bec-acciones {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .bec-acciones form { display: contents; }

        /* Empty state */
        .bec-empty     { text-align: center; padding: 40px 20px; }
        .bec-empty i   { font-size: 40px; display: block; margin-bottom: 12px; color: #dde4ea; }
        .bec-empty p   { font-size: 13px; color: #bbb; margin: 0; }
    </style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $mensaje)
                    <li>{{ $mensaje }}</li>
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
                <i class="fa fa-list text-blue"></i> Catálogo de becas
            </h4>
            <div style="display:flex;gap:7px;flex-wrap:wrap;">
                <span style="background:#eaf3fb;color:#2980b9;border:1px solid #d6eaf8;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-list"></i> {{ $catalogo->count() }} tipos
                </span>
                <span style="background:#e8f8f0;color:#00875a;border:1px solid #b3e8d0;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-check-circle"></i> {{ $catalogo->where('activo', true)->count() }} activos
                </span>
                <span style="background:#fdecea;color:#b91c1c;border:1px solid #fca5a5;border-radius:20px;
                             padding:2px 10px;font-size:12px;font-weight:600;">
                    <i class="fa fa-ban"></i> {{ $catalogo->where('activo', false)->count() }} inactivos
                </span>
            </div>
        </div>
        <a href="{{ route('becas.index') }}" class="btn btn-default btn-sm btn-flat"
           style="border-radius:20px;flex-shrink:0;">
            <i class="fa fa-arrow-left"></i> Asignaciones
        </a>
    </div>

    {{-- ══ CONTENIDO DOS COLUMNAS ══ --}}
    <div class="row">

        {{-- ── FORMULARIO NUEVO ── --}}
        <div class="col-md-4">
            <div class="box"
                 style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">
                <div style="padding:12px 18px;background:#f9fafb;border-bottom:1px solid #e8ecf0;">
                    <h4 style="margin:0;font-size:14px;font-weight:700;color:#1e4d7b;">
                        <i class="fa fa-plus-circle text-blue"></i> Nueva beca
                    </h4>
                </div>
                <div class="box-body">
                    <form action="{{ route('becas.catalogo.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="control-label" style="font-size:12px;color:#6b7a8d;text-transform:uppercase;letter-spacing:.04em;">
                                Nombre <span class="text-red">*</span>
                            </label>
                            <input type="text" name="nombre" class="form-control input-sm"
                                   value="{{ old('nombre') }}" placeholder="Ej: Beca de excelencia" required>
                        </div>

                        <div class="form-group">
                            <label class="control-label" style="font-size:12px;color:#6b7a8d;text-transform:uppercase;letter-spacing:.04em;">
                                Descripción
                            </label>
                            <textarea name="descripcion" class="form-control input-sm" rows="3"
                                      placeholder="Descripción opcional…">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="control-label" style="font-size:12px;color:#6b7a8d;text-transform:uppercase;letter-spacing:.04em;">
                                Tipo <span class="text-red">*</span>
                            </label>
                            <select name="tipo" class="form-control input-sm" required>
                                <option value="">Selecciona un tipo…</option>
                                <option value="porcentaje"  {{ old('tipo') === 'porcentaje'  ? 'selected' : '' }}>Porcentaje (%)</option>
                                <option value="monto_fijo"  {{ old('tipo') === 'monto_fijo'  ? 'selected' : '' }}>Monto fijo ($)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label" style="font-size:12px;color:#6b7a8d;text-transform:uppercase;letter-spacing:.04em;">
                                Valor <span class="text-red">*</span>
                            </label>
                            <input type="number" name="valor" class="form-control input-sm"
                                   value="{{ old('valor') }}" step="0.01" min="0.01"
                                   placeholder="Ej: 25.00" required>
                        </div>

                        <div class="text-right" style="margin-top:16px;">
                            <button type="submit" class="btn btn-success btn-sm btn-flat" style="border-radius:20px;">
                                <i class="fa fa-save"></i> Guardar beca
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── TABLA CATÁLOGO ── --}}
        <div class="col-md-8">
            <div class="box"
                 style="border-radius:8px;border:1px solid #e0e7ef;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;">
                <div style="padding:12px 18px;background:#f9fafb;border-bottom:1px solid #e8ecf0;">
                    <h4 style="margin:0;font-size:14px;font-weight:700;color:#1e4d7b;">
                        <i class="fa fa-table text-blue"></i> Becas registradas
                    </h4>
                </div>
                <div class="box-body no-padding">
                    <table class="bec-table">
                        <thead>
                            <tr>
                                <th style="width:35%;">Nombre</th>
                                <th style="width:15%;">Tipo</th>
                                <th style="width:15%;">Valor</th>
                                <th style="width:12%;">Estado</th>
                                <th style="width:23%;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($catalogo as $item)
                                <tr>
                                    <td>
                                        <div class="bec-nombre">{{ $item->nombre }}</div>
                                        @if ($item->descripcion)
                                            <div class="bec-sub">{{ $item->descripcion }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="bec-badge {{ $item->tipo === 'porcentaje' ? 'bec-badge-porcentaje' : 'bec-badge-monto' }}">
                                            <i class="fa fa-{{ $item->tipo === 'porcentaje' ? 'percent' : 'dollar' }}"></i>
                                            {{ $item->tipo === 'porcentaje' ? 'Porcentaje' : 'Monto fijo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="bec-discount-tag">
                                            @if ($item->tipo === 'porcentaje')
                                                {{ number_format($item->valor, 2) }}%
                                            @else
                                                ${{ number_format($item->valor, 2) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <span class="bec-badge {{ $item->activo ? 'bec-badge-activa' : 'bec-badge-inactiva' }}">
                                            <i class="fa fa-circle" style="font-size:7px;"></i>
                                            {{ $item->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="bec-acciones" style="justify-content:center;">
                                            <a href="{{ route('becas.catalogo.edit', $item->id) }}"
                                               class="btn btn-primary btn-xs btn-flat"
                                               style="border-radius:4px;" title="Editar">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            @if ($item->activo)
                                                <form action="{{ route('becas.catalogo.destroy', $item->id) }}" method="POST"
                                                      onsubmit="return confirm('¿Desactivar esta beca del catálogo?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs btn-flat"
                                                            style="border-radius:4px;" title="Desactivar">
                                                        <i class="fa fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="bec-empty">
                                            <i class="fa fa-list"></i>
                                            <p>No hay becas en el catálogo. Crea la primera usando el formulario.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
