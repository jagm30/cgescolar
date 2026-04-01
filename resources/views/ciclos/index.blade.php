@extends('layouts.master')
@section('content')
    <!-- SELECT2 EXAMPLE -->
    <div class="box box-default">

        <div class="box-header with-border">
            <h3 class="box-title">Gestion de Ciclos Escolares</h3>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Ciclos Escolares Existentes</h3>
                </div>
                <div class="box-body">
                    <table id="ciclos" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nombre del Ciclo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                                <th>Editar</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ciclos as $ciclo)
                                <tr>
                                    <td>{{ $ciclo->nombre }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ciclo->fecha_fin)->format('d/m/Y') }}</td>
                                    <td>
                                        <span
                                            class="label {{ $ciclo->estado == 'activo' ? 'label-success' : 'label-default' }}">
                                            {{ ucfirst($ciclo->estado) }}
                                        </span>
                                    </td>
                                    <!-- BOTÓN EDITAR (Abre el Modal) -->
                                    <td>
                                        <button type="button" class="btn btn-block btn-primary btn-editar"
                                            data-toggle="modal" data-target="#modal-editar" data-id="{{ $ciclo->id }}"
                                            data-nombre="{{ $ciclo->nombre }}" data-cupo="{{ $ciclo->cupo_maximo ?? 100 }}"
                                            data-estado="{{ $ciclo->estado }}">
                                            Editar
                                        </button>
                                    </td>
                                    <!-- BOTÓN SELECCIONAR (Envía el Formulario) -->
                                    <td>
                                        <form action="{{ route('ciclos.seleccionar', $ciclo->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-block btn-info">
                                                Seleccionar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endsection

        @push('scripts')
            <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

            <script>
                $(document).ready(function() {
                    $('#ciclos').DataTable();
                });
            </script>
            <script>
                $('#ciclos').DataTable({
                    "language": {
                        "url": "{{ asset('/bower_components/idioma/datatables_spanish.json') }}"
                    }
                });
            </script>
        @endpush
