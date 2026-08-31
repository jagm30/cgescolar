<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Módulo Educativo | @yield('page_title', 'Calificaciones')</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="icon" type="image/png" href="{{ asset('dist/img/Kontan2.png') }}?v=2">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- AdminLTE + assets base (mismos que el control escolar) --}}
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/skins/skin-blue.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kotan-theme.css') }}?v={{ filemtime(public_path('css/kotan-theme.css')) }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    @stack('styles')
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    {{-- Navbar compartido (muestra usuario, ciclo activo y logout) --}}
    @include('partials.navbar')

    {{-- Sidebar específico del módulo educativo --}}
    @include('partials.educativo-sidebar')

    <div class="content-wrapper">

        {{-- Cabecera de página --}}
        <section class="content-header">
            <h1>
                @yield('page_title', 'Dashboard')
                <small>@yield('page_subtitle', '')</small>
            </h1>
            <ol class="breadcrumb">
                <li>
                    <a href="{{ route('educativo.dashboard') }}">
                        <i class="fa fa-book"></i> Módulo Educativo
                    </a>
                </li>
                @yield('breadcrumb')
                <li class="active">@yield('page_title', 'Dashboard')</li>
            </ol>
        </section>

        <section class="content">
            @yield('content')
        </section>

    </div>

    <x-toast />
    @include('partials.footer')

</div>

{{-- Scripts base AdminLTE --}}
<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
<script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        }
    });

    // Redirigir al login si la sesión expira
    $(document).ajaxError(function (event, xhr) {
        if (xhr.status === 401) window.location.href = '/login';
        if (xhr.status === 403) alert('No tienes permisos para realizar esta acción.');
    });

    // Cambiar ciclo escolar desde el navbar (heredado del módulo principal)
    $(document).on('click', '.cambiar-ciclo', function (e) {
        e.preventDefault();
        $.ajax({
            url: '/ciclos/' + $(this).data('id') + '/seleccionar',
            method: 'POST',
            success: function () { location.reload(); },
            error: function (xhr) { alert(xhr.responseJSON?.message ?? 'Error al cambiar de ciclo.'); }
        });
    });
</script>

@stack('scripts')

</body>
</html>
