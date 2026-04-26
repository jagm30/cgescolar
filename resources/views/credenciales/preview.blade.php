@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <style>
            #credencial-canvas {
                background-color: #ffffff;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
                margin: 20px auto;
                overflow: hidden;
                border: 1px solid #333;
            }

            .element-preview {
                position: absolute !important;
                display: flex;
                align-items: center;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            }

            .content-span {
                width: 100%;
                line-height: 1;
            }

            @media print {

                .main-header,
                .main-sidebar,
                .btn,
                .main-footer,
                .content-header {
                    display: none !important;
                }

                .content-wrapper {
                    margin-left: 0 !important;
                    padding: 0 !important;
                    margin-top: 0 !important;
                }

                body {
                    background: white !important;
                }

                #credencial-canvas {
                    margin: 0 !important;
                    border: none !important;
                    box-shadow: none !important;
                }
            }
        </style>

        <section class="content-header">
            <h1>Previsualización: {{ $credencial->nombre }}</h1>
        </section>

        <section class="content">
            <div class="box box-solid">
                <div class="box-header with-border no-print">
                    <a href="{{ route('credenciales.index') }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Volver al listado
                    </a>
                    <button onclick="window.print()" class="btn btn-success pull-right">
                        <i class="fa fa-print"></i> Imprimir Credencial
                    </button>
                </div>

                <div class="box-body" style="background-color: #f4f4f4;">
                    <div id="credencial-canvas"
                        style="
                        width: {{ $credencial->orientacion == 'vertical' ? '320px' : '500px' }}; 
                        height: {{ $credencial->orientacion == 'vertical' ? '500px' : '320px' }}; 
                        background-image: url('{{ $credencial->fondo_anverso ? asset('storage/' . $credencial->fondo_anverso) : '' }}');
                        background-size: 100% 100%;
                        background-repeat: no-repeat;
                        position: relative;
                     ">
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('credencial-canvas');

            // Inyección de datos desde Laravel (Blindada)
            const config = {!! json_encode($credencial->config_anverso ?? []) !!};

            console.log("Configuración cargada:", config);

            // Diccionario de datos del alumno
            const datosAlumno = {
                'Nombre': "{{ $alumno->nombre_render }}",
                'Matrícula': "{{ $alumno->matricula }}",

                // Cambia estas llaves si en el Editor les pusiste otro nombre
                'Grado y Grupo': "{{ $alumno->grado_render }} - {{ $alumno->grupo_render }}",
                'Ciclo': "{{ $ciclo_escolar->nombre ?? '2025-2026' }}",

                'Foto Alumno': "{{ $alumno->foto_url ? asset('storage/' . $alumno->foto_url) : asset('dist/img/avatar5.png') }}"
            };

            if (config.length === 0) {
                alert("La plantilla no tiene elementos guardados. Ve al diseñador y guarda los cambios.");
            }

            config.forEach(item => {
                const div = document.createElement('div');
                div.className = 'element-preview';

                // Coordenadas con parseFloat para evitar errores de string
                div.style.left = parseFloat(item.x) + 'px';
                div.style.top = parseFloat(item.y) + 'px';

                // Dimensiones
                if (item.width) div.style.width = item.width.toString().includes('px') ? item.width : item
                    .width + 'px';
                if (item.height) div.style.height = item.height.toString().includes('px') ? item.height :
                    item.height + 'px';

                if (item.type === 'text') {
                    div.style.fontSize = item.fontSize || '14px';
                    div.style.color = item.color || '#000000';
                    div.style.textAlign = item.textAlign || 'left';
                    div.style.fontWeight = item.isLabel ? 'bold' : 'normal';

                    // Lógica de reemplazo de etiquetas dinámicas
                    let textoFinal = item.text;
                    if (datosAlumno[item.text]) {
                        textoFinal = datosAlumno[item.text];
                    }

                    div.innerHTML = `<span class="content-span">${textoFinal}</span>`;

                } else if (item.type === 'photo') {
                    const imgUrl = datosAlumno['Foto Alumno'];
                    div.innerHTML = `
                <img src="${imgUrl}" 
                     style="width:100%; height:100%; object-fit:cover; border:1px solid #333;">
            `;
                }

                canvas.appendChild(div);
            });
        });
    </script>
@endsection
