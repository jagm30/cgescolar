<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>KotanEscolar | Iniciar sesión</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="icon" type="image/png" href="{{ asset('dist/img/Kontan2.png') }}">

    <link rel="stylesheet" href="../../bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="../../dist/css/AdminLTE.min.css">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    {{-- ESTILOS REPLICA EXACTA DE LA IMAGEN --}}
    <style>
        /* Fondo general del sistema */
        .login-page {
            background-color: #f4f7f6 !important;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Contenedor principal de la tarjeta */
        .login-box {
            margin: 0 !important;
            width: 580px;
            /* AMPLIADO para que los logos grandes quepan perfectamente */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
        }

        /* Encabezado Oscuro */
        .login-box-header {
            background-color: #0b3c50;
            padding: 45px 30px 35px 30px;
            text-align: center;
            border-bottom: 5px solid #3c8dbc;
        }

        .login-box-header a.logo-text {
            color: #ffffff !important;
            font-size: 38px;
            font-weight: 300;
            text-decoration: none;
            display: block;
            line-height: 1;
            font-family: 'Source Sans Pro', sans-serif;
            margin-top: 5px;
        }

        /* Color Menta para "Kotan" */
        .login-box-header a.logo-text b {
            font-weight: 700;
            color: #48c4a1;
        }

        .school-name {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #8aa4af;
            margin-top: 15px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Cuerpo del formulario (Blanco) */
        .login-box-body {
            background: #ffffff;
            padding: 40px 50px;
            color: #475569;
        }

        .login-box-msg {
            color: #64748b;
            font-weight: 600;
            font-size: 15px;
            padding: 0 0 25px 0;
            text-align: center;
        }

        /* Inputs redondeados con fondo gris/azul */
        .form-control {
            border-radius: 8px !important;
            height: 50px;
            border: 1px solid #d2d6de;
            color: #2c3e50;
            font-size: 14px;
            background-color: #f1f5f9;
        }

        .form-control:focus {
            border-color: #3c8dbc;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(60, 141, 188, 0.1);
        }

        .form-control-feedback {
            line-height: 50px;
            color: #64748b;
        }

        /* Botón estilo píldora */
        .btn-primary {
            border-radius: 25px !important;
            font-weight: 700;
            padding: 12px 0;
            background-color: #3c8dbc;
            border: none;
            box-shadow: 0 4px 8px rgba(60, 141, 188, 0.3);
            transition: all 0.3s ease;
            font-size: 16px;
            margin-top: 15px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            background-color: #367fa9;
            box-shadow: 0 6px 12px rgba(60, 141, 188, 0.4);
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">

        {{-- ENCABEZADO OSCURO --}}
        <div class="login-box-header">
            {{-- Logos lado a lado --}}
            <div style="display: flex; justify-content: center; align-items: center; gap: 30px; margin-bottom: 25px;">

                {{-- Logo del Sistema --}}
                <img src="{{ asset('dist/img/Kotan3.png') }}" alt="Kotan Logo" style="width: 170px; height: auto;">

                {{-- Línea separadora --}}
                <div style="height: 80px; width: 2px; background-color: rgba(255, 255, 255, 0.15); border-radius: 2px;">
                </div>

                {{-- Placa Blanca para el Logo del Cliente --}}
                <div
                    style="background-color: #ffffff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    {{-- IMAGEN HECHA MÁS GRANDE (220px) --}}
                    <img src="{{ asset('dist/img/genki1.png') }}" alt="Colegio Logo"
                        style="width: 220px; height: auto;">
                </div>

            </div>

            {{-- Textos (Kotan en menta, Escolar en blanco) --}}
            <a href="{{ route('login') }}" class="logo-text"><b>Kotan</b>Escolar</a>
            <span class="school-name">GENKI SCHOOL TUXTLA</span>
        </div>

        {{-- CUERPO DEL FORMULARIO --}}
        <div class="login-box-body">
            <p class="login-box-msg">Ingresa tus credenciales para acceder</p>

            <form action="{{ route('login.submit') }}" method="post">
                @csrf
                <div class="form-group has-feedback">
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico"
                        value="{{ old('email') }}" required autocomplete="off">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    @error('email')
                        <span class="text-danger"
                            style="font-size: 12px; margin-top: 4px; display: block; font-weight: 600;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group has-feedback" style="margin-bottom: 20px;">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Contraseña"
                        required>
                    <span class="glyphicon glyphicon-eye-open form-control-feedback toggle-password"
                        style="cursor: pointer; pointer-events: auto;" title="Mostrar/Ocultar contraseña"></span>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Script para ver/ocultar contraseña
            $('.toggle-password').click(function() {
                var input = $(this).prev('input');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
                } else {
                    input.attr('type', 'password');
                    $(this).removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
                }
            });
        });
    </script>
</body>

</html>
