@extends('layouts.master')
@section('page_title', 'Conceptos') {{-- Esto llenará el <title> y el <h1> --}}
@section('content')
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">Listado de Conceptos</h3>
            <button class="btn btn-success pull-right" data-toggle="modal" data-target="#modalNuevoConcepto">
                <i class="fa fa-plus"></i> Nuevo Concepto
            </button>
        </div>
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Descripcion</th>
                        <th>Tipo</th>
                        <th>Aplica beca</th>
                        <th>Aplica recargo</th>
                        <th>Clave</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conceptos as $concepto)
                        <tr>
                            <td>{{ $concepto->nombre }}</td>
                            <td>{{ $concepto->descripcion }}</td>
                            <td>
                                @php
                                    $badgeClass = match ($concepto->tipo) {
                                        'colegiatura' => 'label-success',
                                        'inscripcion' => 'label-info',
                                        'cargo_unico' => 'label-warning',
                                        'cargo_recurrente' => 'label-danger',
                                        default => 'label-default',
                                    };
                                @endphp
                                <span class="label {{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $concepto->tipo)) }}
                                </span>
                            </td>
                            <td>{{ $concepto->aplica_beca ? 'Sí' : 'No' }}</td>
                            <td>{{ $concepto->aplica_recargo ? 'Sí' : 'No' }}</td>
                            <td>{{ $concepto->clave_sat }}</td>
                            <td>{{ $concepto->ativo ? 'Inactivo' : 'Activo' }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

    <x-modal id="modalNuevoConcepto" title=" Agregar Nuevo Concepto" size="modal-lg">
        <form action="{{ route('conceptos.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-tag"></i> Nombre del Concepto</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Inscripción Semestral"
                            required>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-list"></i> Tipo</label>
                        <select name="tipo" id="select-tipo" class="form-control" required>
                            <option value="">Seleccione un tipo...</option>
                            <option value="colegiatura"> Colegiatura</option>
                            <option value="inscripcion">Inscripción</option>
                            <option value="cargo_unico"> Cargo Unico</option>
                            <option value="cargo_recurrente"> Cargo Recurrente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fa fa-key"></i> Clave SAT</label>
                        <input type="text" name="clave_sat" class="form-control" maxlength="8"
                            placeholder="Clave de producto o servicio">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fa fa-align-left"></i> Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" placeholder="Breve descripción del concepto..."></textarea>
                    </div>

                    <div class="well well-sm">
                        <label style="display: block; margin-bottom: 10px;">
                            <strong>Configuraciones adicionales:</strong>
                        </label>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="aplica_beca" id="check-beca">
                                <span class="text-primary">Aplica para beca</span>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="aplica_recargo">
                                <span class="text-warning">Aplica recargo por mora</span>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="activo" checked>
                                <span class="label label-success">Estatus Activo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Guardar Concepto
                </button>
            </x-slot>
        </form>
    </x-modal>
@endsection
@push('scripts')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // 1. Inicializar DataTable
            $('#example1').DataTable();

            // 2. Lógica del select-tipo
            $('#select-tipo').on('change', function() {
                let valorSeleccionado = $(this).val();

                if (valorSeleccionado === 'colegiatura') {
                    $('#check-beca').prop('checked', true);
                } else {
                    $('#check-beca').prop('checked', false);
                }
            });
        });
    </script>
@endpush
