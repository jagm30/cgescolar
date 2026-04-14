@extends('layouts.master')

@section('page_title', 'Cobros')
@section('page_subtitle', 'Registrar pago')

@section('breadcrumb')
    <li class="active">Cobros</li>
@endsection

@push('styles')
<style>
.buscador-wrap {
    max-width: 680px;
    margin: 60px auto 0;
}
.buscador-titulo {
    text-align: center;
    margin-bottom: 28px;
}
.buscador-titulo h2 {
    font-size: 26px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 6px;
}
.buscador-titulo p {
    color: #999;
    font-size: 14px;
    margin: 0;
}
#input-busqueda {
    font-size: 18px;
    height: 52px;
    padding-left: 20px;
    border-radius: 8px 0 0 8px;
    border: 2px solid #d0dde8;
    border-right: none;
    box-shadow: none;
    transition: border-color .2s;
}
#input-busqueda:focus {
    border-color: #3c8dbc;
    outline: none;
    box-shadow: none;
}
#btn-buscar {
    height: 52px;
    font-size: 16px;
    padding: 0 24px;
    border-radius: 0 8px 8px 0;
    background: #3c8dbc;
    border: 2px solid #3c8dbc;
    color: #fff;
}
#btn-buscar:hover { background: #2c7bab; }

/* Autocomplete dropdown */
#autocomplete-lista {
    position: absolute;
    left: 0; right: 0; top: 100%;
    background: #fff;
    border: 1px solid #d0dde8;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
    z-index: 999;
    display: none;
}
.ac-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f5f5f5;
    text-decoration: none;
    color: #222;
    transition: background .1s;
}
.ac-item:last-child { border-bottom: none; }
.ac-item:hover { background: #f0f7ff; color: #222; text-decoration: none; }
.ac-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: #3c8dbc;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ac-nombre { font-size: 14px; font-weight: 600; }
.ac-detalle { font-size: 11px; color: #999; margin-top: 2px; }
.ac-matricula {
    margin-left: auto;
    font-family: monospace;
    font-size: 11px;
    background: #f0f0f0;
    padding: 2px 8px;
    border-radius: 10px;
    color: #666;
    flex-shrink: 0;
}

/* Resultados POST */
.resultado-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 12px;
    transition: box-shadow .15s;
}
.resultado-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.1); }
.resultado-body {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 18px;
}
.resultado-info { flex: 1; }
.resultado-nombre { font-size: 16px; font-weight: 700; color: #222; }
.resultado-sub { font-size: 12px; color: #999; margin-top: 3px; }
</style>
@endpush

@section('content')

<div class="buscador-wrap">

    {{-- Título --}}
    <div class="buscador-titulo">
        <div style="width:64px;height:64px;border-radius:50%;background:#e8f0fb;
                    display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <i class="fa fa-dollar" style="font-size:28px;color:#3c8dbc;"></i>
        </div>
        <h2>Registro de cobros</h2>
        <p>Busca al alumno por nombre, matrícula o CURP para iniciar el cobro</p>
    </div>

    {{-- Buscador con autocomplete --}}
    <div style="position:relative;">
        <form method="GET" action="{{ route('cobros.index') }}" id="form-busqueda"
              autocomplete="off">
            <div class="input-group">
                <input type="text"
                       id="input-busqueda"
                       name="q"
                       class="form-control"
                       placeholder="Escribe nombre, matrícula o CURP..."
                       value="{{ $busqueda }}"
                       autocomplete="off">
                <span class="input-group-btn">
                    <button type="submit" class="btn" id="btn-buscar">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>
        <div id="autocomplete-lista"></div>
    </div>

    {{-- Resultados de búsqueda --}}
    @if($busqueda)
        @forelse($alumnos as $alumno)
        @php
            $ins = $alumno->inscripciones->first();
        @endphp
        <div class="resultado-card">
            <div class="resultado-body">
                {{-- Avatar --}}
                <div style="width:50px;height:50px;border-radius:50%;flex-shrink:0;
                            background:{{ $alumno->estado==='activo' ? '#3c8dbc' : '#bbb' }};
                            display:flex;align-items:center;justify-content:center;overflow:hidden;">
                    @if($alumno->foto_url)
                        <img src="{{ asset('storage/'.$alumno->foto_url) }}"
                             style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="fa fa-user" style="color:#fff;font-size:20px;"></i>
                    @endif
                </div>

                {{-- Info --}}
                <div class="resultado-info">
                    <div class="resultado-nombre">
                        {{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}
                    </div>
                    <div class="resultado-sub">
                        <code style="font-size:11px;background:#f5f5f5;padding:1px 5px;border-radius:3px;">
                            {{ $alumno->matricula }}
                        </code>
                        @if($ins)
                            &nbsp;·&nbsp;
                            {{ $ins->grupo->grado->nivel->nombre ?? '' }}
                            {{ $ins->grupo->grado->nombre }}° {{ $ins->grupo->nombre }}
                            <small>({{ $ins->ciclo->nombre ?? '' }})</small>
                        @endif
                    </div>
                </div>

                {{-- Acción --}}
                <a href="{{ route('cobros.alumno', $alumno->id) }}"
                   class="btn btn-success btn-flat" style="flex-shrink:0;">
                    <i class="fa fa-dollar"></i> Cobrar
                </a>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:40px 0;color:#ccc;">
            <i class="fa fa-search" style="font-size:32px;display:block;margin-bottom:10px;"></i>
            <strong style="color:#aaa;">Sin resultados para "{{ $busqueda }}"</strong>
            <p style="font-size:13px;margin-top:6px;">
                Verifica el nombre o matrícula e intenta de nuevo.
            </p>
        </div>
        @endforelse
    @endif

</div>

@endsection

@push('scripts')
<script>
$(function() {
    var timer;
    var $input = $('#input-busqueda');
    var $lista = $('#autocomplete-lista');

    $input.on('input', function() {
        clearTimeout(timer);
        var q = $(this).val().trim();

        if (q.length < 2) { $lista.hide().empty(); return; }

        timer = setTimeout(function() {
            $.getJSON('{{ route("cobros.buscar") }}', { q: q }, function(data) {
                $lista.empty();
                if (!data.length) { $lista.hide(); return; }

                data.forEach(function(a) {
                    $lista.append(
                        '<a href="' + a.url + '" class="ac-item">' +
                        '<div class="ac-avatar"><i class="fa fa-user" style="color:#fff;font-size:16px;"></i></div>' +
                        '<div><div class="ac-nombre">' + a.nombre + '</div>' +
                        '<div class="ac-detalle">' + (a.grupo || '') + '</div></div>' +
                        '<span class="ac-matricula">' + a.matricula + '</span>' +
                        '</a>'
                    );
                });
                $lista.show();
            });
        }, 280);
    });

    // Cerrar al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#input-busqueda, #autocomplete-lista').length) {
            $lista.hide();
        }
    });

    // Focus muestra resultados previos
    $input.on('focus', function() {
        if ($lista.children().length) $lista.show();
    });
});
</script>
@endpush
