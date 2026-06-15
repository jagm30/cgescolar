<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>KotanEscolar | Iniciar sesión</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    {{-- 1. SCRIPT DE CLOUDFLARE (En el head) --}}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    {{-- ... resto de tus links CSS ... --}}
    <link rel="icon" type="image/png" href="{{ asset('dist/img/credit/Kontan2.png') }}">
    <link rel="stylesheet" href="../../bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../dist/css/AdminLTE.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    {{-- ESTILOS DE LA PÁGINA --}}
    <style>
        /* Fondo general y estructura Flexbox para el scroll */
        .login-page {
            background-color: #f4f7f6 !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Mantiene Navbar arriba, Footer abajo, y Caja en medio */
            margin: 0;
        }

        /* =========================================
           1. NAVBAR DEL CLIENTE (GENKI SCHOOL)
           ========================================= */
        .client-navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            border-bottom: 2px solid #0b3c50;
        }

        .client-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .client-brand img {
            height: 35px;
            width: auto;
        }

        .client-brand span {
            color: #0b3c50;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        .client-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .client-links a {
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .client-links a:hover {
            color: #0b3c50;
        }

        .btn-wa-client {
            background-color: #25D366;
            color: #ffffff !important;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 700 !important;
            box-shadow: 0 4px 6px rgba(37, 211, 102, 0.3);
        }

        .btn-wa-client:hover {
            background-color: #1ebc59;
            transform: translateY(-2px);
        }

        /* =========================================
           2. CONTENEDOR DEL LOGIN CENTRAL
           ========================================= */
        .main-login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            /* Ocupa todo el espacio restante */
            padding: 50px 20px;
        }

        .login-box {
            margin: 0 !important;
            width: 540px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
        }

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

        /* =========================================
           3. FOOTER CORPORATIVO (KOTAN ESCOLAR)
           ========================================= */
        .kotan-footer {
            background-color: #0b3c50;
            /* Mismo azul oscuro corporativo */
            color: #8aa4af;
            padding: 50px 30px 20px 30px;
            border-top: 5px solid #48c4a1;
            /* Acento menta */
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-col h4 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .footer-col p {
            font-size: 13px;
            line-height: 1.6;
        }

        .footer-wa-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #48c4a1;
            /* Menta de Kotan */
            color: #0b3c50 !important;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 700;
            margin-top: 10px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .footer-wa-btn:hover {
            background-color: #3cb08f;
            color: #ffffff !important;
            transform: translateY(-2px);
        }

        .footer-bottom {
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 40px;
            padding-top: 20px;
            font-size: 12px;
        }

        /* Responsividad básica */
        @media (max-width: 768px) {
            .client-navbar {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .client-links {
                justify-content: center;
                flex-wrap: wrap;
            }

            .login-box {
                width: 95%;
            }

            .login-box-header div {
                flex-direction: column;
                gap: 15px;
            }

            .login-box-header div>div {
                height: 2px !important;
                width: 80px !important;
            }
        }
    </style>
</head>

<body class="hold-transition login-page">

    {{-- NAVBAR DEL CLIENTE (GENKI SCHOOL) --}}
    <nav class="client-navbar">
        <div class="client-brand">
            {{-- Logo en base64 proporcionado --}}
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAMAAABMOI/cAAAAWlBMVEVHcExILHEGKZ/ILT/BLUTzLiAbK4DbLjPNLTzSLToHJNjULTfrLiaELGB+LGK8LUezLUvFLUEaK3oaK3qiLVOPLVuQLFsaK3kHK4IZK3oVK3zjLi0YK3oZK3r4bBaNAAAAHnRSTlMAORPWtv8h/+z0Bf3/UHalisjH5nugaf8tW0f/rIrca4iqAAABM0lEQVR4AXXNBYKEMAxA0d9CaoRFt2GmzP2Pue7y6pKEL5znb13/z73IXy8hSsqSCj8NOsKddHzjcTIBdOrxH7fzoHGRkXVjliVq2gHuB5W8RJGK2TGJpD5LTHd4lR2YZMHMJ60vhwjkBMeFKbJZ0Z3rFdoCdB2YbTjO52k188QJmJrnZmaHN3N+M1upsgNO80F4frpcn6bzoLbmAXbV3AfsAsGK6wdpIwAuSssjq4Ni1NwkB4DQdIfjtA1Oe36eNRZglop7qnl7KnO+lGKXCiwRno5XODcIZlZoE5AGuK2+DqxW4sh5QlygNBmBqh1m5PZykAHuo+i0Lyo7ZmESXfZeNVbA9U00yYgZsySVOAVeUJx3MuMDvfjg+CY2D04TP7nWpl5j4BefRbrCX4aOv42BT4+SPhJws58WdwAAAABJRU5ErkJggg=="
                alt="Logo Genki Pequeño">
            <span>Inscripciones Abiertas | Conoce más</span>
        </div>
        <div class="client-links">
            <a href="https://genkischool.mx/" target="_blank" title="Visitar Sitio Web"><i class="fa fa-globe"></i>
                Sitio Web</a>
            <a href="https://www.facebook.com/ColegioJaponesGenkiSchoolTuxtla/?locale=es_LA" target="_blank"
                title="Facebook"><i class="fa fa-facebook-official"></i> Facebook</a>
            <a href="https://www.instagram.com/colegiojaponesgenki/" target="_blank" title="Instagram"><i
                    class="fa fa-instagram"></i> Instagram</a>
            {{-- Enlace API de WhatsApp (52 es código de México) --}}
            <a href="https://wa.me/529611364088" target="_blank" class="btn-wa-client"><i class="fa fa-whatsapp"></i>
                Chat Directo</a>
        </div>
    </nav>

    {{-- CONTENEDOR CENTRAL (LOGIN) --}}
    <div class="main-login-container">
        <div class="login-box">
            {{-- ENCABEZADO OSCURO --}}
            <div class="login-box-header">
                <div
                    style="display: flex; justify-content: center; align-items: center; gap: 30px; margin-bottom: 25px;">
                    {{-- Logo del Sistema --}}
                    <img src="{{ asset('dist/img/Kotan3.png') }}" alt="Kotan Logo" style="width: 170px; height: auto;">

                    {{-- Línea separadora --}}
                    <div
                        style="height: 80px; width: 2px; background-color: rgba(255, 255, 255, 0.15); border-radius: 2px;">
                    </div>

                    {{-- Placa Blanca para el Logo del Cliente --}}
                    <div
                        style="background-color: #ffffff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <img src="{{ asset('dist/img/genki1.png') }}" alt="Colegio Logo"
                            style="width: 220px; height: auto;">
                    </div>
                </div>

                {{-- Textos (Kotan en menta, Escolar en blanco) --}}
                <a href="{{ route('login') }}" class="logo-text"><b>Kotan</b>Escolar</a>
                <span class="school-name">GENKI SCHOOL TUXTLA</span>
            </div>

            {{-- CUERPO DEL FORMULARIO --}}
            {{-- CUERPO DEL FORMULARIO --}}
            <div class="login-box-body">
                <p class="login-box-msg">Ingresa tus credenciales para acceder</p>

                <form action="{{ route('login.submit') }}" method="post">
                    @csrf

                    {{-- CAMPO DE CORREO (Corregido, sin duplicar) --}}
                    <div class="form-group has-feedback">
                        <input type="email" name="email" class="form-control" placeholder="Correo electrónico"
                            value="{{ old('email') }}" required autocomplete="off">
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        @error('email')
                            <span class="text-danger"
                                style="font-size: 12px; margin-top: 4px; display: block; font-weight: 600;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- CAMPO DE CONTRASEÑA (Restauré el ícono del "ojito" y el ID) --}}
                    <div class="form-group has-feedback" style="margin-bottom: 20px;">
                        <input type="password" name="password" class="form-control" id="password"
                            placeholder="Contraseña" required>
                        <span class="glyphicon glyphicon-eye-open form-control-feedback toggle-password"
                            style="cursor: pointer; pointer-events: auto;" title="Mostrar/Ocultar contraseña"></span>
                    </div>

                    {{-- WIDGET DE TURNSTILE (Corregido, alineado y sin divs rotos) --}}
                    <div class="form-group text-center"
                        style="margin-bottom: 20px; min-height: 65px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY') }}" data-theme="light">
                        </div>

                        @error('cf-turnstile-response')
                            <span class="text-danger"
                                style="font-size: 12px; display: block; font-weight: 600; margin-top: 5px;">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- BOTÓN --}}
                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- FOOTER CORPORATIVO (KOTAN ESCOLAR) --}}
    <footer class="kotan-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Acerca de KotanEscolar</h4>
                <p>Somos la plataforma integral líder en gestión educativa, diseñada para simplificar y optimizar los
                    procesos administrativos, financieros y académicos de tu institución, integrando la tecnología en el
                    aprendizaje diario.</p>
            </div>

            <div class="footer-col">
                <h4>Nuestra Misión</h4>
                <p>Brindar herramientas tecnológicas innovadoras y accesibles que faciliten la comunicación y el control
                    escolar, empoderando a las instituciones, docentes y familias para mejorar la calidad educativa.</p>
            </div>

            <div class="footer-col">
                <h4>Nuestra Visión</h4>
                <p>Ser el estándar de excelencia en software de gestión educativa, impulsando la transformación digital
                    de las escuelas y creando ecosistemas escolares conectados y eficientes en todo el país.</p>
            </div>

            <div class="footer-col">
                <h4>¿Te interesa nuestro sistema?</h4>
                <p>Para contrataciones, cotizaciones o demostraciones del sistema, comunícate directamente con nuestro
                    equipo comercial.</p>
                {{-- Enlace API de WhatsApp de Kotan --}}
                <a href="https://wa.me/529191123147" target="_blank" class="footer-wa-btn">
                    <i class="fa fa-whatsapp" style="font-size: 18px;"></i> 919 112 3147
                </a>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; {{ date('Y') }} KotanEscolar. Todos los derechos reservados.
        </div>
    </footer>

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
