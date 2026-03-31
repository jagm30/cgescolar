<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CGesEscolar | @yield('page_title', 'Sistema')</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{-- CSRF token en el head para que $.ajaxSetup lo lea desde cualquier vista --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/skins/skin-blue.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    @stack('styles')
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    @include('partials.navbar')
    @include('partials.sidebar')

    <div class="content-wrapper">

        {{-- Cabecera de la página --}}
        <section class="content-header">
            <h1>
                @yield('page_title', 'Dashboard')
                <small>@yield('page_subtitle', '')</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
                @yield('breadcrumb')
                <li class="active">@yield('page_title', 'Dashboard')</li>
            </ol>
        </section>

        {{-- Mensajes flash --}}
        <section class="content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-check"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-ban"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </section>

    </div>

    @include('partials.footer')

</div>

{{-- Scripts base de AdminLTE --}}
<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
<script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

{{--
    Configuración global de AJAX.
    Después de cargar jQuery, configurar todos los requests AJAX
    para que incluyan automáticamente el token CSRF y el header
    Accept: application/json (para que Laravel responda JSON).
--}}
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept':       'application/json',
        'Content-Type': 'application/json'
    }
});

// Redirigir al login si la sesión expira (401)
// Mostrar mensaje si el usuario no tiene permisos (403)
$(document).ajaxError(function (event, xhr) {
    if (xhr.status === 401) {
        window.location.href = '/login';
    }
    if (xhr.status === 403) {
        alert('No tienes permisos para realizar esta acción.');
    }
});

// Cambiar ciclo escolar desde el navbar
$(document).on('click', '.cambiar-ciclo', function (e) {
    e.preventDefault();
    const id = $(this).data('id');

    $.ajax({
        url: '/ciclos/' + id + '/seleccionar',
        method: 'POST',
        success: function () {
            location.reload();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Error al cambiar de ciclo.');
        }
    });
});
</script>

@stack('scripts')

</body>
</html>
