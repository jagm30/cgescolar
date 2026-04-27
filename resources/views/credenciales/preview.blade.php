@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <style>
            /* Reset global para el canvas de Preview */
            #credencial-canvas * {
                box-sizing: border-box !important;
            }

            #credencial-canvas {
                background-color: #ffffff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
                margin: 20px auto;
                overflow: hidden;
                border: 1px solid #333;
                position: relative;
                /* Asegura que el (0,0) sea el borde del canvas */
            }

            .element-preview {
                position: absolute !important;
                /* Quitamos el flex para evitar que el navegador intente "acomodar" el texto */
                display: block !important;
                z-index: 1000;
                pointer-events: none;

                /* Quitamos cualquier padding o borde que sume píxeles */
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;

                /* Ajuste de texto fiel al editor */
                white-space: normal;
                word-wrap: break-word;
                overflow: hidden;
                line-height: 1.2;
                /* Debe ser igual al del editor */
            }

            .content-span {
                display: block;
                width: 100%;
                margin: 0;
                padding: 0;
                vertical-align: top;
            }

            @media print {
                @page {
                    /* Tamaño exacto de tarjeta bancaria */
                    size: 85.6mm 53.98mm landscape;
                    margin: 0;
                }

                body {
                    margin: 0;
                    padding: 0;
                    visibility: hidden !important;
                }

                #credencial-canvas,
                #credencial-canvas * {
                    visibility: visible !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                #credencial-canvas {
                    position: absolute !important;
                    left: 0 !important;
                    top: 0 !important;
                    margin: 0 !important;
                    border: none !important;
                    box-shadow: none !important;

                    /* Mantenemos tus 500px originales para que la fuente se vea BIEN */
                    width: 500px !important;
                    height: 320px !important;

                    /* AJUSTE QUIRÚRGICO:
                               Escalamos el ancho (X) y el alto (Y) por separado.
                               0.647 para que el ancho de 500px dé exactamente 85.6mm
                               0.642 para que el alto de 320px dé exactamente 54mm
                            */
                    transform: scale(0.647, 0.642) !important;
                    transform-origin: top left !important;
                }
            }
        </style>

        <section class="content-header no-print">
            <h1>Previsualización Fiel: {{ $credencial->nombre }}</h1>
        </section>

        <section class="content">
            <div class="box box-solid">
                <div class="box-header with-border no-print">
                    <a href="{{ route('credenciales.index') }}" class="btn btn-default"><i class="fa fa-arrow-left"></i>
                        Volver</a>
                    <button onclick="window.print()" class="btn btn-success pull-right"><i class="fa fa-print"></i> Imprimir
                        Prueba</button>
                </div>

                <div class="box-body" style="background-color: #f4f4f4;">
                    <div id="credencial-canvas" style="position: relative; width: 500px; height: 320px; overflow: hidden;">

                        <img src="{{ asset('storage/' . $credencial->fondo_anverso) }}"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1;">

                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Recuperamos la configuración desde la variable que manda Laravel
            const config = @json($credencial->config_anverso) || [];
            const canvas = document.getElementById('credencial-canvas');

            if (!canvas) return;

            // Limpiamos el canvas por si acaso
            canvas.innerHTML = '';

            // 2. AGREGAR LA IMAGEN DE FONDO (Para que la Evolis la imprima)
            const imgFondo = document.createElement('img');
            imgFondo.src = "{{ asset('storage/' . $credencial->fondo_anverso) }}";
            imgFondo.style.position = 'absolute';
            imgFondo.style.top = '0';
            imgFondo.style.left = '0';
            imgFondo.style.width = '100%';
            imgFondo.style.height = '100%';
            imgFondo.style.zIndex = '1';
            imgFondo.style.objectFit = 'fill';
            canvas.appendChild(imgFondo);

            // 3. RENDERIZAR LOS ELEMENTOS DEL JSON
            config.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'element-preview';

                // Posicionamiento absoluto basado en los datos guardados
                div.style.position = 'absolute';
                div.style.left = parseFloat(item.x) + 'px';
                div.style.top = parseFloat(item.y) + 'px';

                // Z-Index alto para que el texto siempre flote sobre el fondo
                div.style.zIndex = (10 + index).toString();

                // Dimensiones (si existen)
                if (item.width) div.style.width = item.width;
                if (item.height) div.style.height = item.height;

                if (item.type === 'photo' || item.type === 'foto') {
                    // Placeholder para la foto del alumno (puedes inyectar la foto real aquí luego)
                    div.innerHTML = `
                <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; border:1px solid #333;">
                    <i class="fa fa-user fa-3x" style="color:#ccc"></i>
                </div>`;
                } else {
                    // ESTILOS DINÁMICOS DEL JSON (Lo que acabamos de agregar)
                    div.style.fontSize = item.fontSize || '14px';
                    div.style.color = item.color || '#000000';
                    div.style.textAlign = item.textAlign || 'left';
                    div.style.fontWeight = item.fontWeight || 'normal';
                    div.style.fontStyle = item.fontStyle || 'normal';
                    div.style.fontFamily = item.fontFamily || 'Roboto, sans-serif';

                    // Aseguramos que el texto no se corte feo
                    div.style.whiteSpace = 'normal';
                    div.style.wordWrap = 'break-word';
                    div.style.lineHeight = '1.2';

                    // Inyectamos el texto
                    div.innerHTML = `<span class="content-span">${item.text}</span>`;
                }

                canvas.appendChild(div);
            });
        });
    </script>
@endsection
