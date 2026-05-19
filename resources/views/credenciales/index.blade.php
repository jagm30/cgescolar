@extends('layouts.master')
@section('page_title', 'Diseñador de Credenciales')

@section('content')
    <div class="row" style="margin-bottom: 20px; display: flex; align-items: center;">
        <div class="col-md-6">
            <h2 style="margin: 0; font-weight: bold; color: #1e4d7b;">
                <i class="fa fa-id-card-o text-blue"></i> Plantillas de Credenciales
            </h2>
            <p class="text-muted">Gestiona y personaliza los formatos de identificación escolar.</p>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalNuevoDiseno">
                <i class="fa fa-plus"></i> Crear Nuevo Diseño
            </button>
        </div>
    </div>

    <div class="row">
        @forelse($disenos as $diseno)
            <div class="col-md-4">
                <div style="background: #fff; border-radius: 8px; border: 1px solid #d0dde8; overflow: hidden; margin-bottom: 25px; transition: transform 0.3s;"
                    onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    {{-- Vista Previa --}}
                    <div
                        style="height: 180px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid #edf1f2;">
                        @if ($diseno->fondo_anverso)
                            <img src="{{ asset('storage/' . $diseno->fondo_anverso) }}"
                                style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8;">
                        @else
                            <i class="fa fa-picture-o" style="font-size: 50px; color: #d0dde8;"></i>
                        @endif
                        <div style="position: absolute; top: 10px; right: 10px;">
                            <span class="label label-info">{{ strtoupper($diseno->tipo ?? 'Estándar') }}</span>
                        </div>
                    </div>

                    {{-- Info del Diseño --}}
                    <div style="padding: 15px;">
                        <h4 style="margin: 0 0 5px 0; font-weight: bold; color: #2c3e50;">{{ $diseno->nombre }}</h4>
                        <small class="text-muted"><i class="fa fa-calendar"></i> Última edición:
                            {{ $diseno->updated_at->format('d/m/Y') }}</small>

                        <div style="margin-top: 15px; display: flex; gap: 8px;">
                            <a href="{{ route('credenciales.edit', $diseno->id) }}" class="btn btn-primary btn-sm btn-flat"
                                style="flex: 1;">
                                <i class="fa fa-edit"></i> Diseñar
                            </a>
                            <form action="{{ route('credenciales.destroy', $diseno->id) }}" method="POST"
                                style="flex: 0 0 40px;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-default btn-sm btn-flat"
                                    onclick="return confirm('¿Eliminar este diseño?')">
                                    <i class="fa fa-trash text-red"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div
                    style="background: #fff; padding: 60px; text-align: center; border-radius: 8px; border: 2px dashed #d0dde8;">
                    <i class="fa fa-id-card-o" style="font-size: 60px; color: #d0dde8; margin-bottom: 20px;"></i>
                    <h3 class="text-muted">No hay plantillas creadas</h3>
                    <p>Comienza creando un diseño para imprimir las credenciales de tus alumnos.</p>
                    <button class="btn btn-success btn-flat" data-toggle="modal" data-target="#modalNuevoDiseno"
                        style="margin-top: 10px;">
                        Crear mi primera plantilla
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- MODAL NUEVO DISEÑO --}}
    <div class="modal fade" id="modalNuevoDiseno" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content" style="border-radius: 12px;">
                <form action="{{ route('credenciales.store') }}" method="POST">
                    @csrf
                    <div class="modal-header" style="background: #1e4d7b; color: white; border-radius: 12px 12px 0 0;">
                        <h4 class="modal-title">Nueva Plantilla</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre del Diseño</label>
                            <input type="text" name="nombre" class="form-control"
                                placeholder="Ej: Credencial Primaria 2026" required>
                        </div>
                        <div class="form-group">
                            <label>Orientación</label>
                            <select name="orientacion" class="form-control">
                                <option value="vertical">Vertical (Estilo ID)</option>
                                <option value="horizontal">Horizontal</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-flat">Continuar al Editor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
