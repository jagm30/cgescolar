<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Álbum Fotográfico — {{ $grupo->nombre }}</title>
    <style>
        @page {
            margin-top: 45mm;
            /* Ajustado para el encabezado repetido */
            margin-bottom: 10mm;
            margin-left: 10mm;
            margin-right: 10mm;
        }

        body {
            font-family: sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            position: fixed;
            top: -40mm;
            left: 0;
            right: 0;
            height: 40mm;
            text-align: center;
        }

        .title {
            font-size: 18px;
            color: #1e4d7b;
            font-weight: bold;
            text-transform: uppercase;
        }

        .sub {
            font-size: 12px;
            color: #666;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .card-cell {
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }

        .card {
            border: none;
            /* Quitamos el borde */
            padding: 8px;
            border-radius: 8px;
            background: #fff;
            margin: 0 auto;
        }

        .foto {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            background: #f4f4f4;
            display: block;
            margin: 0 auto 8px auto;
        }

        .nombre {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .matricula {
            font-size: 7px;
            color: #888;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <header>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 20%; text-align: left;">
                    @php $logoRuta = \App\Models\Setting::find(1)->logo_ruta ?? 'logo-escuela.png'; @endphp
                    @if (file_exists(public_path('imgs_escuela/reportes/' . $logoRuta)))
                        <img src="{{ public_path('imgs_escuela/reportes/' . $logoRuta) }}" style="width: 120px;">
                    @endif
                </td>
                <td style="width: 60%; text-align: center;">
                    <div class="title">Álbum Fotográfico — {{ $grupo->grado->nivel->nombre }}
                        {{ $grupo->grado->numero }}° "{{ $grupo->nombre }}"</div>
                    <div class="sub">Ciclo Escolar: {{ $grupo->ciclo->nombre }} | Total alumnos:
                        {{ $grupo->inscripciones->count() }}</div>
                </td>
                <td style="width: 20%; text-align: right;">
                    @if (!empty($grupo->icono) && file_exists(public_path('storage/' . $grupo->icono)))
                        <img src="{{ public_path('storage/' . $grupo->icono) }}" alt="Icono"
                            style="width:50px; height:50px; border-radius:50%; border:1px solid #ccc;">
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table class="grid">
            @foreach ($grupo->inscripciones->sortBy(fn($i) => $i->alumno->ap_paterno)->chunk(5) as $chunk)
                <tr>
                    @foreach ($chunk as $ins)
                        <td class="card-cell">
                            <div class="card">
                                @php
                                    $rutaFoto = $ins->alumno->foto_url
                                        ? public_path('storage/' . $ins->alumno->foto_url)
                                        : null;
                                @endphp

                                @if ($rutaFoto && file_exists($rutaFoto))
                                    <img src="{{ $rutaFoto }}" class="foto">
                                @else
                                    <div class="foto" style="line-height:80px; color:#ccc; font-size: 10px;">Sin foto
                                    </div>
                                @endif

                                <div class="nombre">{{ $ins->alumno->nombre }} {{ $ins->alumno->ap_paterno }} {{ $ins->alumno->ap_materno }}</div>
                                <div class="matricula">{{ $ins->alumno->matricula }}</div>
                            </div>
                        </td>
                    @endforeach
                    @for ($i = count($chunk); $i < 5; $i++)
                        <td class="card-cell"></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </main>
</body>

</html>
