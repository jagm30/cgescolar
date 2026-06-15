<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demasiados intentos | KotanEscolar</title>

    <!-- Icono de la pestaña -->
    <link rel="icon" type="image/png" href="{{ asset('dist/img/credit/Kotan2.png') }}">

    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7f6;
            color: #2c3e50;
            font-family: 'Source Sans Pro', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 450px;
            width: 90%;
            border-top: 5px solid #0b3c50;
            /* Azul oscuro Kotan */
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 20px auto;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 10px 0;
            color: #0b3c50;
        }

        p {
            font-size: 15px;
            color: #64748b;
            margin: 0 0 25px 0;
            line-height: 1.5;
        }

        .timer-box {
            background: #0b3c50;
            color: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            display: inline-block;
            border-left: 4px solid #48c4a1;
            /* Acento menta */
        }

        #seconds {
            font-size: 24px;
            color: #48c4a1;
            font-weight: 700;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #48c4a1;
            animation: spin 1s ease-in-out infinite;
            vertical-align: middle;
            margin-right: 10px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    @php
        // Laravel envía el tiempo de espera en los headers de la excepción.
        // Si por alguna razón no está, ponemos 60 segundos por defecto.
        $retryAfter =
            isset($exception) && method_exists($exception, 'getHeaders')
                ? $exception->getHeaders()['Retry-After'] ?? 60
                : 60;
    @endphp

    <div class="error-container">
        <div class="icon-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                style="width:40px; height:40px;">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                </path>
            </svg>
        </div>

        <h1>Acceso temporalmente bloqueado</h1>
        <p>Por motivos de seguridad, hemos detectado demasiados intentos de inicio de sesión fallidos. Por favor, espera
            antes de intentar nuevamente.</p>

        <div class="timer-box">
            <div id="wait-message">
                <span class="spinner"></span>
                Intentar de nuevo en <span id="seconds">{{ $retryAfter }}</span> seg.
            </div>
            <div id="ready-message" style="display: none; color: #48c4a1;">
                ¡Listo! Redirigiendo...
            </div>
        </div>
    </div>

    <script>
        // Capturamos los segundos enviados por el servidor de Laravel
        let timeLeft = {{ $retryAfter }};
        const secondsElement = document.getElementById('seconds');
        const waitMessage = document.getElementById('wait-message');
        const readyMessage = document.getElementById('ready-message');

        // Iniciamos el cronómetro
        const countdown = setInterval(() => {
            timeLeft--;
            secondsElement.innerText = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(countdown);

                // Cambiar el texto a "Redirigiendo..."
                waitMessage.style.display = 'none';
                readyMessage.style.display = 'block';

                // Recargar la página para volver al login automáticamente
                window.location.href = "{{ route('login') }}";
            }
        }, 1000); // 1000 milisegundos = 1 segundo
    </script>
</body>

</html>
