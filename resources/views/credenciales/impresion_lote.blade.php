@extends('layouts.print_master')

@section('content')
    <style>
        body {
            background-color: #525659;
            margin: 0;
            padding: 0;
        }

        @media print {
            @page {
                size: 85.6mm 53.98mm landscape;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                background-color: white;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            #contenedor-lote {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                background: white !important;
            }

            .hoja-pvc {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                page-break-after: always;
                break-after: page;
            }
        }

        #contenedor-lote {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        .hoja-pvc {
            width: 500px !important;
            height: 320px !important;
            transform: scale(0.645) !important;
            transform-origin: top left !important;
            position: relative;
            overflow: hidden;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .hoja-pvc:last-child {
            page-break-after: auto !important;
            break-after: auto !important;
        }

        .img-fondo-pvc {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            object-fit: fill;
        }

        .elemento-lote {
            position: absolute;
            z-index: 10;
            white-space: normal !important;
            word-wrap: break-word;
            line-height: 1.1;
            padding: 2px 4px;
            box-sizing: border-box;
            display: flex;
            align-items: center;
        }

        .content-span {
            display: block;
            width: 100%;
            height: 100%;
        }
    </style>

    <div class="no-print text-center" style="padding: 20px; background: #333; color: white;">
        <h4>Impresión Lote: {{ count($alumnos) }} alumnos</h4>
        <button onclick="window.print()" class="btn btn-lg btn-success"><i class="fa fa-print"></i> MANDAR A IMPRESORA</button>
    </div>

    <div id="contenedor-lote"></div>

    @php
        $cicloSeguro = $cicloActual->nombre ?? 'SIN CICLO';
        $alumnosData = $alumnos->map(function ($a) use ($cicloSeguro) {
            $gradoNombre = 'Sin Grado';
            $nivelNombre = 'Sin Nivel';
            if (isset($a->inscripciones) && $a->inscripciones->last() && $a->inscripciones->last()->grupo) {
                $grupo = $a->inscripciones->last()->grupo;
                $gradoNombre = $grupo->grado->nombre ?? 'Sin Grado';
                $nivelNombre = $grupo->grado->nivel->nombre ?? 'Sin Nivel';
            }
            $rutaFoto = null;
            if (!empty($a->foto_url)) {
                $nombreLimpio = ltrim($a->foto_url, '/');
                $rutaFoto = str_contains($nombreLimpio, 'alumnos/fotos')
                    ? asset('storage/' . $nombreLimpio)
                    : asset('storage/alumnos/fotos/' . $nombreLimpio);
            }
            return [
                'nombre' => $a->nombre_completo ?? $a->nombre,
                'matricula' => $a->matricula,
                'sangre' => $a->tipo_sangre,
                'grado' => $gradoNombre,
                'nivel' => $nivelNombre,
                'ciclo' => $cicloSeguro,
                'foto' => $rutaFoto,
            ];
        });
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const config = @json(is_string($credencial->config_anverso) ? json_decode($credencial->config_anverso) : $credencial->config_anverso) || [];
            const alumnos = @json($alumnosData);
            const contenedor = document.getElementById('contenedor-lote');

            alumnos.forEach(alumno => {
                const tarjeta = document.createElement('div');
                tarjeta.className = 'hoja-pvc';

                const fondo = document.createElement('img');
                fondo.src = "{{ asset('storage/' . $credencial->fondo_anverso) }}";
                fondo.className = 'img-fondo-pvc';
                tarjeta.appendChild(fondo);

                config.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'elemento-lote';
                    el.style.transform = `translate(${item.x}px, ${item.y}px)`;

                    if (item.width && item.width !== 'auto') el.style.width = item.width;
                    if (item.height && item.height !== 'auto') el.style.height = item.height;

                    const colorFinal = (item.color && item.color.trim() !== '') ? item.color :
                        '#000000';

                    el.style.fontSize = item.fontSize || '14px';
                    el.style.fontFamily = item.fontFamily || 'Roboto, sans-serif';
                    el.style.fontWeight = item.fontWeight || 'normal';
                    el.style.textAlign = item.textAlign || 'left';

                    const span = document.createElement('span');
                    span.className = 'content-span';

                    // Aplicamos el color y los estilos de fuente directamente al SPAN
                    span.style.setProperty('color', colorFinal, 'important');
                    span.style.fontWeight = item.fontWeight || 'normal';
                    span.style.fontStyle = item.fontStyle || 'normal';
                    span.style.fontSize = item.fontSize || '14px';
                    span.style.fontFamily = item.fontFamily || 'Roboto, sans-serif';

                    span.style.whiteSpace = 'normal';
                    span.style.display = 'block';
                    span.style.width = '100%';
                    span.style.textAlign = item.textAlign || 'left';

                    if (item.type === 'foto') {
                        el.style.padding = '0';
                        if (alumno.foto) {
                            span.innerHTML =
                                `<img src="${alumno.foto}" style="width:100%; height:100%; object-fit:cover;">`;
                        } else {
                            span.innerHTML =
                                `<div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; border:1px solid #999;"><i class="fa fa-user fa-3x" style="color:#ccc"></i></div>`;
                        }
                    } else {
                        // Mapeo del texto
                        let texto = item.text || '';
                        if (!item.isLabel) {
                            if (item.type === 'nombre') texto = alumno.nombre || texto;
                            if (item.type === 'matricula') texto = alumno.matricula || texto;
                            if (item.type === 'grado') texto = alumno.grado || texto;
                            if (item.type === 'nivel') texto = alumno.nivel || texto;
                            if (item.type === 'sangre') texto = alumno.sangre || texto;
                            if (item.type === 'ciclo') texto = alumno.ciclo || texto;
                        }

                        // CAMBIO CLAVE: Usamos innerHTML en lugar de innerText para tomar Bold e Italic
                        // Y envolvemos en un span con el color para asegurar que no se pierda el estilo
                        span.innerHTML = texto;
                    }

                    el.appendChild(span);
                    tarjeta.appendChild(el);
                });
                contenedor.appendChild(tarjeta);
            });
        });
    </script>
@endsection
