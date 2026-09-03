<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Directorio Familiar — {{ $ciclo?->nombre }}</title>

    @php
        $nombreEscuela = $setting?->nombre_escuela ?? config('app.school_name', 'Escuela');
        $logoRuta      = $setting?->logo_ruta ?? 'logo-escuela.png';
        $logoPath      = public_path('imgs_escuela/reportes/' . $logoRuta);

        function rutaFoto(?string $fotoUrl): ?string
        {
            if (! $fotoUrl) return null;
            $path = public_path('storage/' . $fotoUrl);
            if (! file_exists($path)) return null;

            if (filesize($path) > 60000 && function_exists('imagecreatefromstring')) {
                $original = @imagecreatefromstring(file_get_contents($path));
                if ($original) {
                    $thumb = imagecreatetruecolor(120, 150);
                    imagecopyresampled($thumb, $original, 0, 0, 0, 0, 120, 150,
                        imagesx($original), imagesy($original));
                    ob_start();
                    imagejpeg($thumb, null, 60);
                    $data = ob_get_clean();
                    imagedestroy($original);
                    imagedestroy($thumb);
                    return 'data:image/jpeg;base64,' . base64_encode($data);
                }
            }
            return $path;
        }
    @endphp

    <style>
        @page { margin: 18mm 14mm; size: letter portrait; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        /* ── Encabezado de documento (primera página) ── */
        .doc-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2px solid #1a3a5c;
            padding-bottom: 6px;
        }
        .doc-header td { vertical-align: middle; }
        .school-logo { width: 64px; height: auto; }
        .school-name { font-size: 13px; font-weight: bold; color: #1a3a5c; text-transform: uppercase; }
        .school-sub  { font-size: 8px; color: #555; margin-top: 2px; }
        .doc-title   { text-align: right; font-size: 12px; font-weight: bold; color: #1a3a5c; text-transform: uppercase; }
        .doc-meta    { text-align: right; font-size: 8px; color: #555; margin-top: 3px; }

        /* ── Encabezado de grupo ── */
        .grupo-header {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            margin-bottom: 0;
            page-break-before: auto;
        }
        .grupo-header-td {
            background: #1a3a5c;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            padding: 5px 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .grupo-count-td {
            background: #1a3a5c;
            color: #e8c46a;
            font-size: 10px;
            font-weight: bold;
            text-align: right;
            padding: 5px 10px;
            width: 20%;
        }

        /* ── Tarjeta de alumno ── */
        .alumno-card {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #b0c8e0;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .alumno-nombre-td {
            background: #2a5380;
            color: #fff;
            font-size: 10.5px;
            font-weight: bold;
            padding: 4px 8px;
        }
        .alumno-mat-td {
            background: #2a5380;
            color: #b8d4f0;
            font-size: 8px;
            padding: 4px 8px;
            text-align: right;
            width: 25%;
        }
        .alumno-foto-td {
            width: 72px;
            text-align: center;
            vertical-align: top;
            padding: 8px;
            border-right: 1px solid #b0c8e0;
            background: #f2f7fc;
        }
        .alumno-foto { width: 56px; height: 70px; border: 1px solid #90b4cc; }
        .alumno-foto-ph {
            width: 56px; height: 70px;
            background: #d8e8f4;
            font-size: 7px; color: #5a82a0;
            text-align: center; line-height: 70px;
            border: 1px solid #90b4cc;
        }
        .alumno-datos-td { padding: 6px 10px; vertical-align: top; }

        /* ── Campos de datos ── */
        .campo { margin-bottom: 4px; }
        .lbl {
            font-size: 7px; font-weight: bold; color: #555;
            text-transform: uppercase; letter-spacing: .06em;
            display: block; margin-bottom: 1px;
        }
        .val { font-size: 9px; color: #1a1a1a; display: block; }
        .val-mono { font-family: 'Courier New', monospace; font-size: 8px; }

        .datos-row { width: 100%; border-collapse: collapse; }
        .datos-row td { vertical-align: top; padding: 0 8px 4px 0; }

        hr.sep { border: none; border-top: 1px solid #d0e0f0; margin: 5px 0; }

        /* ── Sección contactos ── */
        .contactos-ttl {
            font-size: 7.5px; font-weight: bold; color: #1a3a5c;
            text-transform: uppercase; letter-spacing: .1em;
            border-bottom: 1px solid #c8a020;
            padding-bottom: 2px; margin: 6px 0 5px;
        }
        .contactos-tabla { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .contacto-td {
            width: 50%; vertical-align: top;
            border: 1px solid #d8e8f4;
            padding: 5px 6px;
            background: #f9fbfe;
        }
        .contacto-td + .contacto-td { border-left: none; }

        .contacto-inner { width: 100%; border-collapse: collapse; }
        .contacto-foto-td { width: 44px; vertical-align: top; padding-right: 6px; }
        .contacto-foto    { width: 36px; height: 46px; border: 1px solid #90b4cc; }
        .contacto-foto-ph {
            width: 36px; height: 46px;
            background: #d8e8f4; font-size: 7px; color: #5a82a0;
            text-align: center; line-height: 46px;
            border: 1px solid #90b4cc;
        }
        .contacto-datos-td { vertical-align: top; }
        .contacto-nombre {
            font-size: 9px; font-weight: bold; color: #1a3a5c;
            margin-bottom: 2px; line-height: 1.3;
        }
        .contacto-parentesco {
            font-size: 7.5px; font-weight: bold;
            background: #e8f0f8; color: #2a5380;
            border: 1px solid #a0c0dc;
            padding: 1px 5px; display: inline-block;
            margin-bottom: 4px; text-transform: capitalize;
        }
        .cf { width: 100%; border-collapse: collapse; margin-bottom: 1px; }
        .cf td { font-size: 8px; vertical-align: top; padding: 0; }
        .cf .cl { color: #555; font-weight: bold; width: 58px; font-size: 7px; text-transform: uppercase; }
        .cf .cv { color: #1a1a1a; }
        .empresa { font-weight: bold; color: #1a3a5c; }

        .permisos { margin-top: 4px; }
        .permiso {
            display: inline-block; font-size: 7px; font-weight: bold;
            background: #e6f4ec; color: #256340;
            border: 1px solid #aad4bb; padding: 1px 5px;
            margin-right: 3px; text-transform: uppercase; letter-spacing: .03em;
        }
        .permiso-pago { background: #e8f0fb; color: #1a3a5c; border-color: #a0c0dc; }

        .sin-datos { text-align: center; padding: 30px; color: #888; font-size: 11px; }
        .vacio { background: #fafcff; }
    </style>
</head>
<body>

    {{-- ══ ENCABEZADO DE DOCUMENTO ══ --}}
    <table class="doc-header">
        <tr>
            <td style="width:10%;">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="school-logo" alt="Logo">
                @endif
            </td>
            <td style="padding-left:10px;">
                <div class="school-name">{{ $nombreEscuela }}</div>
                <div class="school-sub">Documento de uso exclusivo de la Dirección</div>
            </td>
            <td style="width:35%;">
                <div class="doc-title">Directorio Familiar</div>
                <div class="doc-meta">
                    Ciclo: {{ $ciclo?->nombre ?? '—' }}&nbsp;&nbsp;|&nbsp;&nbsp;{{ now()->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    {{-- ══ GRUPOS ══ --}}
    @forelse ($grupos as $grupo)
        @php
            $inscripciones = $grupo->inscripciones->sortBy(fn($i) => $i->alumno?->ap_paterno ?? '');
            $nivel = $grupo->grado?->nivel?->nombre ?? '—';
            $grado = $grupo->grado?->nombre ?? '';
        @endphp

        <table class="grupo-header">
            <tr>
                <td class="grupo-header-td">
                    {{ $nivel }}@if($grado) — {{ $grado }}@endif — {{ $grupo->nombre }}
                </td>
                <td class="grupo-count-td">{{ $inscripciones->count() }} alumno(s)</td>
            </tr>
        </table>

        @foreach ($inscripciones as $inscripcion)
            @php
                $alumno    = $inscripcion->alumno;
                $contactos = $alumno->contactos;
                $foto      = rutaFoto($alumno->foto_url ?? null);
                $domicilio = collect([
                    $alumno->calle, $alumno->colonia,
                    $alumno->ciudad, $alumno->estado_residencia,
                ])->filter()->implode(', ');
            @endphp

            <table class="alumno-card">
                {{-- Nombre del alumno --}}
                <tr>
                    <td class="alumno-nombre-td">
                        {{ $alumno->ap_paterno }} {{ $alumno->ap_materno }}, {{ $alumno->nombre }}
                    </td>
                    <td class="alumno-mat-td">
                        Matrícula: <strong style="color:#fff;">{{ $alumno->matricula ?? '—' }}</strong>
                    </td>
                </tr>

                {{-- Foto + datos --}}
                <tr>
                    <td class="alumno-foto-td" colspan="1">
                        @if ($foto)
                            <img src="{{ $foto }}" class="alumno-foto" alt="Foto">
                        @else
                            <div class="alumno-foto-ph">Sin foto</div>
                        @endif
                    </td>
                    <td class="alumno-datos-td" colspan="1">

                        <table class="datos-row">
                            <tr>
                                <td style="width:28%;">
                                    <span class="lbl">Fecha de nacimiento</span>
                                    <span class="val">
                                        {{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                                        @if ($alumno->fecha_nacimiento)
                                            <span style="color:#555;">({{ $alumno->edad }} años)</span>
                                        @endif
                                    </span>
                                </td>
                                <td style="width:16%;">
                                    <span class="lbl">Género</span>
                                    <span class="val">{{ ucfirst($alumno->genero ?? '—') }}</span>
                                </td>
                                <td style="width:36%;">
                                    <span class="lbl">CURP</span>
                                    <span class="val val-mono">{{ $alumno->curp ?? '—' }}</span>
                                </td>
                                <td style="width:20%;">
                                    <span class="lbl">Religión</span>
                                    <span class="val">{{ $alumno->religion ?? '—' }}</span>
                                </td>
                            </tr>
                        </table>

                        @if ($domicilio)
                            <hr class="sep">
                            <span class="lbl">Domicilio</span>
                            <span class="val">{{ $domicilio }}</span>
                        @endif

                        {{-- ── Contactos ── --}}
                        @if ($contactos->isNotEmpty())
                            <div class="contactos-ttl">
                                Contactos familiares ({{ $contactos->count() }})
                            </div>

                            @foreach ($contactos->chunk(2) as $fila)
                                <table class="contactos-tabla">
                                    <tr>
                                        @foreach ($fila as $contacto)
                                            @php
                                                $fotoC = rutaFoto($contacto->foto_url ?? null);
                                                $pv    = $contacto->pivot;
                                            @endphp
                                            <td class="contacto-td">
                                                <table class="contacto-inner">
                                                    <tr>
                                                        <td class="contacto-foto-td">
                                                            @if ($fotoC)
                                                                <img src="{{ $fotoC }}" class="contacto-foto" alt="Foto">
                                                            @else
                                                                <div class="contacto-foto-ph">—</div>
                                                            @endif
                                                        </td>
                                                        <td class="contacto-datos-td">
                                                            <div class="contacto-nombre">{{ $contacto->nombre_completo }}</div>
                                                            <span class="contacto-parentesco">{{ ucfirst($pv->parentesco ?? '—') }}</span>

                                                            @if ($contacto->telefono_celular)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Cel.</td>
                                                                    <td class="cv">{{ $contacto->telefono_celular }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->telefono_trabajo)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Tel. trabajo</td>
                                                                    <td class="cv">{{ $contacto->telefono_trabajo }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->telefono_2)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Tel. alterno</td>
                                                                    <td class="cv">{{ $contacto->telefono_2 }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->email)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Correo</td>
                                                                    <td class="cv">{{ $contacto->email }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->lugar_trabajo)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Empresa</td>
                                                                    <td class="cv empresa">{{ $contacto->lugar_trabajo }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->puesto)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Puesto</td>
                                                                    <td class="cv">{{ $contacto->puesto }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->profesion)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Profesión</td>
                                                                    <td class="cv empresa">{{ $contacto->profesion }}</td>
                                                                </tr></table>
                                                            @endif
                                                            @if ($contacto->nivel_estudios)
                                                                <table class="cf"><tr>
                                                                    <td class="cl">Estudios</td>
                                                                    <td class="cv">{{ $contacto->nivel_estudios }}</td>
                                                                </tr></table>
                                                            @endif

                                                            @if ($pv->autorizado_recoger || $pv->es_responsable_pago)
                                                                <div class="permisos">
                                                                    @if ($pv->autorizado_recoger)
                                                                        <span class="permiso">&#10003; Autorizado recoger</span>
                                                                    @endif
                                                                    @if ($pv->es_responsable_pago)
                                                                        <span class="permiso permiso-pago">&#10003; Resp. pago</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        @endforeach

                                        @if ($fila->count() === 1)
                                            <td class="contacto-td vacio"></td>
                                        @endif
                                    </tr>
                                </table>
                            @endforeach
                        @else
                            <div style="font-size:8px;color:#888;margin-top:5px;font-style:italic;">
                                No hay contactos registrados.
                            </div>
                        @endif

                    </td>
                </tr>
            </table>

        @endforeach

    @empty
        <div class="sin-datos">No hay alumnos inscritos en el ciclo seleccionado.</div>
    @endforelse

</body>
</html>
