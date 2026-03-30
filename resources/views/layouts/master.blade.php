<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CGesEscolar | @yield('page_title', 'Admin')</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/skins/skin-blue.min.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    @stack('styles')
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        @include('partials.navbar')

        @include('partials.sidebar')

        <div class="content-wrapper">

            <section class="content-header">
                <h1>
                    @yield('page_title', 'Dashboard')
                    <small>@yield('page_subtitle', '')</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
                    <li class="active">@yield('page_title', 'Dashboard')</li>
                </ol>
            </section>

            <section class="content">
                @yield('content')
            </section>

        </div>
        @include('partials.footer')


    </div>
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>

    @stack('scripts')

</body>

</html>
