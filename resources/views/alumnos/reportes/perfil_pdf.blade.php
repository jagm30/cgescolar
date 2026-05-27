<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Perfil del Alumno - {{ $alumno->nombre }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header td {
            vertical-align: middle;
        }

        .title {
            color: #1e4d7b;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .subtitle {
            color: #666;
            font-size: 12px;
            margin-top: 4px;
        }

        .section-title {
            background-color: #1e4d7b;
            color: #ffffff;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-radius: 3px;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.info-table th {
            width: 20%;
            text-align: left;
            background-color: #f4f6f9;
            padding: 8px;
            border: 1px solid #d0dde8;
            color: #1e4d7b;
            font-size: 11px;
        }

        table.info-table td {
            padding: 8px;
            border: 1px solid #d0dde8;
            font-size: 12px;
        }

        .photo-box {
            width: 110px;
            height: 130px;
            border: 2px dashed #ccc;
            text-align: center;
            line-height: 130px;
            color: #999;
            background: #f9f9f9;
            margin: 0 auto;
        }

        .photo-img {
            width: 110px;
            height: 130px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            background: #e0e0e0;
            color: #333;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-blue {
            background: #3498db;
            color: #fff;
        }

        .badge-green {
            background: #2ecc71;
            color: #fff;
        }

        .badge-yellow {
            background: #f1c40f;
            color: #333;
        }
    </style>
</head>

<body>

    <table class="header">
        <tr>
            <td style="width: 20%; text-align: left;">
                <img src="{{ $base64 }}" alt="Logo Escuela" style="height: 50px;">
            </td>
            <td style="width: 60%; text-align: center;">
                <div class="title">
                    {{ $setting->nombre_escuela ?? 'CGESCOLAR' }}
                </div>
                <div class="subtitle" style="font-size: 20px; color: #1e4d7b; font-weight: bold;">Ficha de Alumno</div>
                <div class="subtitle">Perfil Académico y Datos de Contacto</div>
            </td>
            <td style="width: 20%; text-align: right; color: #666; font-size: 10px;">
                <b>Fecha de emisión:</b><br>
                {{ date('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- ── 1. DATOS PERSONALES ── --}}
    <div class="section-title">1. Datos Personales</div>
    <table class="info-table">
        <tr>
            <td rowspan="4" style="width: 25%; text-align: center; vertical-align: middle; padding: 10px;">
                @if ($alumno->foto_url && file_exists(public_path('storage/' . $alumno->foto_url)))
                    <img src="{{ public_path('storage/' . $alumno->foto_url) }}" class="photo-img">
                @else
                    <div class="photo-box">SIN FOTO</div>
                @endif
            </td>
            <th>Nombre Completo</th>
            <td colspan="3">
                <b>{{ $alumno->nombre }} {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}</b>
                <span class="badge"
                    style="float: right; background: #e8f8f0; color: #00875a; border: 1px solid #b3e8d0;">
                    {{ strtoupper($alumno->estado) }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Nacimiento</th>
            <td>
                {{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') : 'N/A' }}
                @if ($alumno->fecha_nacimiento)
                    <span
                        style="color: #666; font-size: 11px;">({{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age }}
                        años)</span>
                @endif
            </td>
            <th>Género</th>
            <td>{{ ucfirst($alumno->genero ?? 'N/A') }}</td>
        </tr>
        <tr>
            <th>CURP</th>
            <td>{{ $alumno->curp ?? 'No registrado' }}</td>
            <th>Inscripción</th>
            <td>{{ $alumno->fecha_inscripcion ? \Carbon\Carbon::parse($alumno->fecha_inscripcion)->format('d/m/Y') : 'N/A' }}
            </td>
        </tr>
        <tr>
            <th>Matrícula</th>
            <td colspan="3"><b>{{ $alumno->matricula }}</b></td>
        </tr>
    </table>

    {{-- ── 2. INSCRIPCIONES ── --}}
    <div class="section-title">2. Historial de Inscripciones</div>
    <table class="info-table">
        <thead>
            <tr>
                <th style="width: 20%;">Ciclo Escolar</th>
                <th style="width: 15%;">Estatus</th>
                <th style="width: 25%;">Nivel / Grado</th>
                <th style="width: 20%;">Grupo</th>
                <th style="width: 20%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alumno->inscripciones as $inscripcion)
                <tr>
                    <td style="font-weight: bold;">{{ $inscripcion->ciclo->nombre ?? 'N/A' }}</td>
                    <td>
                        @if ($inscripcion->activo)
                            <span style="color: #2ecc71; font-weight: bold;">ACTIVA</span>
                        @else
                            <span style="color: #95a5a6;">INACTIVA</span>
                        @endif
                    </td>
                    <td>{{ $inscripcion->grupo->grado->nivel->nombre ?? '' }} -
                        {{ $inscripcion->grupo->grado->nombre ?? '' }}°</td>
                    <td>{{ $inscripcion->grupo->nombre ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($inscripcion->created_at)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #e74c3c;">No hay inscripciones registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ── 3. CONTACTOS FAMILIARES ── --}}
    <div class="section-title">3. Contactos Familiares</div>
    <table class="info-table">
        @forelse($alumno->contactos as $contacto)
            <tr>
                <th>
                    {{-- Concatenamos los nombres directamente como están en la BD --}}
                    {{ $contacto->nombre }} {{ $contacto->ap_paterno }} {{ $contacto->ap_materno }}<br>
                    <span
                        style="font-weight: normal; font-size: 10px; color: #666;">({{ ucfirst($contacto->parentesco ?? 'Tutor') }})</span>
                </th>
                <td style="vertical-align: top;">
                    <div style="margin-bottom: 6px;">
                        {{-- Mantenemos los badges, asumiendo que tienes estas banderas en tu BD --}}
                        @if (isset($contacto->es_principal) && $contacto->es_principal)
                            <span class="badge badge-blue">★ Principal</span>
                        @endif
                        @if (isset($contacto->puede_recoger) && $contacto->puede_recoger)
                            <span class="badge badge-green">✔ Recoger</span>
                        @endif
                        @if (isset($contacto->responsable_pago) && $contacto->responsable_pago)
                            <span class="badge badge-yellow">$ Pagos</span>
                        @endif
                    </div>
                    <div>
                        {{-- Usamos las columnas exactas de la tabla contacto_familiar --}}
                        <b style="color: #666;">Celular:</b> {{ $contacto->telefono_celular ?? 'N/A' }} <br>

                        @if ($contacto->telefono_trabajo)
                            <b style="color: #666;">Trabajo:</b> {{ $contacto->telefono_trabajo }} <br>
                        @endif

                        <b style="color: #666;">Correo:</b> {{ $contacto->email ?? 'N/A' }}
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center" style="color:#666;">No hay contactos familiares registrados.</td>
            </tr>
        @endforelse
    </table>

    {{-- ── 4. FAMILIA ── --}}
    <div class="section-title">4. Familia Vinculada</div>
    <table class="info-table">
        <tr>
            <th>Familia Asignada</th>
            <td>
                @if ($alumno->familia)
                    <div style="font-weight: bold; font-size: 13px; margin-bottom: 4px;">
                        {{ $alumno->familia->apellido_familia }}
                    </div>
                @else
                    Sin familia vinculada
                @endif
            </td>
        </tr>
    </table>

</body>

</html>
