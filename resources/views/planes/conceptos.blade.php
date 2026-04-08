@extends('layouts.master')
@section('page_title', 'Configurar Plan: ' . $plan->nombre)

@section('content')

    {{-- CAJA DE ERRORES / ÉXITO --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> ¡Éxito!</h4>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> ¡Error!</h4>
            {{ session('error') }}
        </div>
    @endif

    {{-- ENCABEZADO DEL PLAN (Resumen) --}}
    <div class="box box-widget widget-user-2" style="margin-bottom: 15px;">
        <div class="widget-user-header bg-blue">
            <div class="pull-right">
                <a href="{{ route('planes.index') }}" class="btn btn-default btn-sm text-black"
                    style="color: black !important;">
                    <i class="fa fa-arrow-left"></i> Volver al Catálogo
                </a>
            </div>
            <h3 class="widget-user-username" style="margin-left: 0; font-weight: bold;">{{ $plan->nombre }}</h3>
            <h5 class="widget-user-desc" style="margin-left: 0;">
                Nivel: <strong>{{ $plan->nivel->nombre ?? 'N/A' }}</strong> |
                Vigencia: <strong>{{ $plan->fecha_inicio->format('d/m/Y') }} al
                    {{ $plan->fecha_fin->format('d/m/Y') }}</strong> |
                Pago: <strong>{{ ucfirst($plan->periodicidad) }}</strong>
            </h5>
        </div>
    </div>

    {{-- PANEL DE PESTAÑAS (TABS) --}}
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            {{-- Pestaña Activa (Conceptos) --}}
            <li class="active">
                <a href="#tab_conceptos" data-toggle="tab">
                    <i class="fa fa-tags text-green"></i> <strong>1. Conceptos de Cobro</strong>
                </a>
            </li>
            {{-- Pestaña Inactiva (Políticas) - OJO: Es un enlace a la otra ruta --}}
            <li>
                <a href="{{ route('planes.politicas.index', $plan->id) }}" class="text-muted">
                    <i class="fa fa-percent"></i> 2. Políticas y Recargos
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="tab_conceptos">

                {{-- BOTÓN AGREGAR CONCEPTO --}}
                <div class="clearfix" style="margin-bottom: 15px;">
                    <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modalAddConcepto">
                        <i class="fa fa-plus"></i> Agregar Concepto al Plan
                    </button>
                    <p class="text-muted" style="margin-top: 10px;">
                        Aquí puedes administrar los cobros base que se le harán a los alumnos inscritos en este plan.
                    </p>
                </div>

                {{-- TABLA DE CONCEPTOS DEL PLAN --}}
                <table class="table table-bordered table-striped table-hover">
                    <thead class="bg-gray">
                        <tr>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Monto Asignado ($)</th>
                            <th class="text-center" style="width: 150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- OJO: Iteramos sobre los registros de la tabla intermedia --}}
                        @forelse ($plan->conceptos as $detalle)
                            <tr>
                                <td>
                                    <strong>{{ $detalle->concepto->nombre ?? ($detalle->nombre ?? 'N/A') }}</strong>
                                </td>
                                <td>
                                    <span
                                        class="label label-default">{{ ucfirst($detalle->concepto->tipo ?? ($detalle->tipo ?? 'N/A')) }}</span>
                                </td>
                                <td>
                                    <b class="text-success"
                                        style="font-size: 16px;">${{ number_format($detalle->monto ?? ($detalle->pivot->monto ?? 0), 2) }}</b>
                                </td>
                                <td class="text-center">
                                    {{-- Botón Editar Monto --}}
                                    <button class="btn btn-warning btn-sm" data-toggle="modal"
                                        data-target="#modalEditConcepto{{ $detalle->id ?? $detalle->pivot->id }}"
                                        title="Editar Monto">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    {{-- Botón Quitar --}}
                                    <form
                                        action="{{ route('planes.conceptos.destroy', [$plan->id, $detalle->id ?? $detalle->pivot->id]) }}"
                                        method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Quitar del Plan"
                                            onclick="return confirm('¿Seguro que deseas quitar este concepto? Si ya tiene cargos generados, el sistema no lo permitirá.');">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted" style="padding: 30px;">
                                    <i class="fa fa-info-circle fa-2x"></i><br>
                                    Este plan aún no tiene conceptos de cobro asignados.<br>
                                    Haz clic en el botón verde para agregar uno.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-modal id="modalAddConcepto" title="Asignar Nuevo Concepto al Plan" size="modal-md">
        <form action="{{ route('planes.conceptos.store', $plan->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Concepto Disponible</label>
                <select name="concepto_id" class="form-control" required>
                    <option value="">Seleccione un concepto...</option>
                    @forelse($conceptosDisponibles as $cd)
                        <option value="{{ $cd->id }}">
                            {{ $cd->nombre }} ({{ ucfirst($cd->tipo) }})
                        </option>
                    @empty
                        <option value="" disabled>Todos los conceptos activos ya están en el plan.</option>
                    @endforelse
                </select>
            </div>

            <div class="form-group">
                <label>Monto a Cobrar ($)</label>
                <input type="number" step="0.01" min="0.01" name="monto" class="form-control"
                    placeholder="Ej: 2500.00" required>
            </div>

            <hr>
            <div class="text-right">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" {{ $conceptosDisponibles->isEmpty() ? 'disabled' : '' }}>
                    <i class="fa fa-save"></i> Asignar Concepto
                </button>
            </div>
        </form>
    </x-modal>

    @foreach ($plan->conceptos as $detalle)
        <x-modal id="modalEditConcepto{{ $detalle->id ?? $detalle->pivot->id }}"
            title="Editar Monto: {{ $detalle->concepto->nombre ?? ($detalle->nombre ?? 'N/A') }}" size="modal-sm">
            <form action="{{ route('planes.conceptos.update', [$plan->id, $detalle->id ?? $detalle->pivot->id]) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Nuevo Monto ($)</label>
                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control"
                        value="{{ $detalle->monto ?? ($detalle->pivot->monto ?? 0) }}" required>
                </div>

                <hr>
                <div class="text-right">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-refresh"></i> Actualizar</button>
                </div>
            </form>
        </x-modal>
    @endforeach

@endsection
