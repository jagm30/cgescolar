<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @php
        $escuelaInfo = \App\Models\Setting::find(1);
        $nombreEscuela = $escuelaInfo->nombre_escuela ?? config('app.school_name');
        $logoRuta      = $escuelaInfo->logo_ruta ?? 'logo-escuela.png';
    @endphp
    <title>Manual de Usuario — Admisiones y Recepción</title>
    <style>
        @page { margin: 15mm 18mm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* ── Encabezado institucional ── */
        .header {
            width: 100%;
            border-bottom: 3px solid #1e4d7b;
            padding-bottom: 8px;
            margin-bottom: 16px;
            border-collapse: collapse;
        }
        .header td { vertical-align: middle; }
        .school-logo { width: 90px; height: auto; display: block; }
        .school-name {
            color: #1e4d7b;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .school-sub {
            color: #888;
            font-size: 9px;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .doc-title { text-align: right; }
        .doc-title-main {
            color: #1e4d7b;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .doc-title-sub { color: #666; font-size: 9px; margin-top: 2px; }

        /* ── Portada ── */
        .cover {
            text-align: center;
            padding: 40px 0 30px;
            border-bottom: 2px solid #e0e6ed;
            margin-bottom: 24px;
        }
        .cover-logo { width: 110px; height: auto; margin: 0 auto 14px; display: block; }
        .cover-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e4d7b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .cover-subtitle { font-size: 13px; color: #555; margin-bottom: 4px; }
        .cover-school { font-size: 12px; color: #1e4d7b; font-weight: bold; margin-bottom: 14px; }
        .cover-meta {
            font-size: 9px;
            color: #888;
            border-top: 1px solid #e0e6ed;
            padding-top: 8px;
            margin-top: 10px;
        }
        .cover-badge {
            display: inline-block;
            background: #1e4d7b;
            color: #fff;
            padding: 3px 12px;
            border-radius: 10px;
            font-size: 10px;
            margin: 4px 2px;
        }

        /* ── Tabla de contenido ── */
        .toc-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e4d7b;
            border-bottom: 2px solid #1e4d7b;
            padding-bottom: 4px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .toc-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .toc-table td { padding: 3px 0; font-size: 10px; vertical-align: top; }
        .toc-table .toc-num { width: 28px; color: #1e4d7b; font-weight: bold; }
        .toc-table .toc-dots { border-bottom: 1px dotted #ccc; }
        .toc-sub { padding-left: 16px; color: #555; }

        /* ── Secciones / títulos ── */
        .section-title {
            background: #1e4d7b;
            color: #fff;
            font-size: 13px;
            font-weight: bold;
            padding: 6px 12px;
            margin: 20px 0 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
            page-break-after: avoid;
        }
        .subsection-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e4d7b;
            border-left: 4px solid #1e4d7b;
            padding: 3px 0 3px 8px;
            margin: 14px 0 6px;
            page-break-after: avoid;
        }
        .step-title {
            font-size: 10px;
            font-weight: bold;
            color: #444;
            margin: 10px 0 4px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .step-box {
            border: 1px solid #d0dbe6;
            border-left: 4px solid #3c8dbc;
            border-radius: 4px;
            padding: 8px 12px;
            margin: 10px 0;
            background: #f7fbff;
            page-break-inside: avoid;
        }
        .step-box-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e4d7b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        /* ── Tablas de datos ── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px;
            font-size: 10px;
        }
        table.data th {
            background: #2c6fad;
            color: #fff;
            padding: 5px 8px;
            text-align: left;
            font-weight: bold;
        }
        table.data td {
            padding: 4px 8px;
            border-bottom: 1px solid #e8edf2;
            vertical-align: top;
        }
        table.data tr:nth-child(even) td { background: #f4f7fb; }
        table.data .req { color: #c0392b; font-weight: bold; }
        table.data .opt { color: #888; }

        /* ── Pipeline ── */
        .pipeline {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px;
            font-size: 10px;
        }
        .pipeline td {
            padding: 5px 10px;
            border: 1px solid #d0dbe6;
            vertical-align: middle;
        }
        .pipeline .arrow { text-align: center; color: #888; padding: 0 4px; border: none; }
        .stage-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
        }
        .s-prospecto  { background: #5b7fa6; }
        .s-cita       { background: #0097a7; }
        .s-visita     { background: #00796b; }
        .s-docs       { background: #f57c00; }
        .s-aceptado   { background: #2e7d32; }
        .s-inscrito   { background: #6a1b9a; }
        .s-noconcr    { background: #b71c1c; }

        /* ── Notas y avisos ── */
        .note {
            background: #eaf3fb;
            border-left: 4px solid #2c6fad;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 10px;
            color: #1e3a5f;
        }
        .warning {
            background: #fff8e1;
            border-left: 4px solid #f9a825;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 10px;
            color: #5d4037;
        }
        .tip {
            background: #f0fff4;
            border-left: 4px solid #00a65a;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 10px;
            color: #1a5c37;
        }

        /* ── Listas numeradas / viñetas ── */
        ol, ul { margin: 4px 0 8px 18px; padding: 0; font-size: 10px; }
        li { margin-bottom: 3px; }

        /* ── FAQ ── */
        .faq-q {
            font-weight: bold;
            color: #1e4d7b;
            margin-top: 10px;
            font-size: 10px;
        }
        .faq-a { font-size: 10px; margin-bottom: 6px; color: #333; }

        /* ── Pie de página ── */
        .footer {
            border-top: 1px solid #ccc;
            margin-top: 20px;
            padding-top: 6px;
            font-size: 8px;
            color: #aaa;
            text-align: center;
        }

        /* ── Saltos de página ── */
        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }

        /* ── Etiquetas de campo ── */
        .req-label {
            display: inline-block;
            background: #c0392b;
            color: #fff;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            vertical-align: middle;
        }
        .opt-label {
            display: inline-block;
            background: #95a5a6;
            color: #fff;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            vertical-align: middle;
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════
     PORTADA
══════════════════════════════════════════════════ --}}
<div class="cover">
    <img src="{{ public_path('storage/' . $logoRuta) }}" class="cover-logo" alt="Logo">
    <div class="cover-school">{{ $nombreEscuela }}</div>
    <div class="cover-title">Manual de Usuario</div>
    <div class="cover-subtitle">Admisiones · Recepción</div>
    <div style="margin: 12px 0;">
        <span class="cover-badge">Recepción</span>
        <span class="cover-badge">Admisiones</span>
    </div>
    <div class="cover-meta">
        Versión 2.0 &nbsp;·&nbsp; {{ now()->format('F Y') }} &nbsp;·&nbsp; CGEscolar
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     TABLA DE CONTENIDO
══════════════════════════════════════════════════ --}}
<div class="toc-title">Contenido</div>
<table class="toc-table">
    <tr><td class="toc-num">1</td><td>Introducción</td></tr>
    <tr><td class="toc-num">2</td><td>Acceso al sistema</td></tr>
    <tr><td class="toc-num">3</td><td>Módulo de Admisiones — Prospectos</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.1 Lista de prospectos</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.2 Registrar un nuevo prospecto</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.3 Ficha del prospecto</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.4 Avanzar etapa en el pipeline</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.5 Registrar un seguimiento</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.6 Subir documentos del prospecto</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">3.7 Convertir prospecto a alumno</td></tr>
    <tr><td class="toc-num">4</td><td>Módulo de Alumnos</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">4.1 Lista de alumnos</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">4.2 Registrar un nuevo alumno (asistente 4 pasos)</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">4.3 Ficha del alumno</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">4.4 Editar datos del alumno</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">4.5 Cambiar estado del alumno</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">4.6 Contactos familiares</td></tr>
    <tr><td class="toc-num">5</td><td>Módulo de Grupos</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">5.1 Lista de grupos</td></tr>
    <tr><td class="toc-num"></td><td class="toc-sub">5.2 Ver alumnos de un grupo</td></tr>
    <tr><td class="toc-num">6</td><td>Preguntas frecuentes</td></tr>
</table>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 1 — INTRODUCCIÓN
══════════════════════════════════════════════════ --}}
<div class="section-title">1. Introducción</div>
<p>Este manual describe las operaciones del día a día que realizan los usuarios con rol <strong>Recepción</strong> y <strong>Admisiones</strong> dentro de CGEscolar.</p>
<p>Con estos roles podrás:</p>
<ul>
    <li>Registrar y dar seguimiento a prospectos (familias interesadas en la escuela).</li>
    <li>Convertir un prospecto aceptado en alumno inscrito.</li>
    <li>Registrar nuevos alumnos de forma manual mediante un asistente de 4 pasos.</li>
    <li>Consultar y actualizar la información de alumnos existentes.</li>
    <li>Consultar la distribución de grupos y los alumnos asignados a cada uno.</li>
</ul>
<div class="note">
    <strong>Nota:</strong> Algunas acciones están reservadas al rol <strong>Administrador</strong> (eliminar registros, configurar ciclos, gestionar becas, crear grupos, etc.). Si necesitas realizar una de esas acciones, contacta al administrador del sistema.
</div>
<div class="tip">
    <strong>Convención del sistema:</strong> Los campos de texto (nombres, apellidos) se convierten a <strong>MAYÚSCULAS</strong> automáticamente mientras escribes. No es necesario activar Bloq Mayús.
</div>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 2 — ACCESO
══════════════════════════════════════════════════ --}}
<div class="section-title">2. Acceso al sistema</div>
<ol>
    <li>Abre el navegador (Chrome o Firefox recomendados).</li>
    <li>Escribe la dirección del sistema que te proporcionó el administrador.</li>
    <li>Ingresa tu <strong>correo electrónico</strong> y <strong>contraseña</strong>.</li>
    <li>Haz clic en <strong>Iniciar sesión</strong>.</li>
</ol>
<p>Si olvidaste tu contraseña, solicita al administrador que la restablezca.</p>
<p>Una vez dentro verás el <strong>Panel principal</strong> con el resumen del ciclo escolar activo.</p>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 3 — PROSPECTOS
══════════════════════════════════════════════════ --}}
<div class="page-break"></div>
<div class="section-title">3. Módulo de Admisiones — Prospectos</div>

<div class="subsection-title">3.1 Lista de prospectos</div>
<p>Accede desde el menú lateral: <strong>Admisiones → Prospectos</strong>.</p>
<table class="data">
    <tr><th>Columna</th><th>Descripción</th></tr>
    <tr><td>Nombre</td><td>Nombre del prospecto (futuro alumno)</td></tr>
    <tr><td>Contacto</td><td>Nombre y teléfono del padre/tutor</td></tr>
    <tr><td>Nivel</td><td>Nivel educativo de interés</td></tr>
    <tr><td>Etapa</td><td>Paso actual dentro del pipeline</td></tr>
    <tr><td>Primer contacto</td><td>Fecha en que se registró</td></tr>
    <tr><td>Canal</td><td>Cómo se enteró de la escuela</td></tr>
</table>
<p><strong>Filtros disponibles:</strong></p>
<ul>
    <li><strong>Búsqueda</strong>: Por nombre del prospecto, nombre del contacto o teléfono.</li>
    <li><strong>Etapa</strong>: Filtra por una etapa específica del pipeline.</li>
    <li><strong>Ciclo</strong>: Muestra prospectos de un ciclo escolar determinado.</li>
    <li><strong>En proceso</strong>: Muestra únicamente los prospectos activos (excluye inscritos y no concretados).</li>
</ul>

<div class="subsection-title">3.2 Registrar un nuevo prospecto</div>
<p>El formulario de registro es una sola página dividida en dos secciones: datos del prospecto y datos de contacto.</p>
<ol>
    <li>En la lista de prospectos, haz clic en el botón <strong>Nuevo prospecto</strong> (esquina superior derecha).</li>
    <li>Completa los datos del prospecto (niño/a).</li>
    <li>Completa los datos del contacto (padre o tutor que llama o visita).</li>
    <li>Haz clic en <strong>Guardar prospecto</strong>.</li>
</ol>

<p class="step-title">Sección 1 — Datos del prospecto (niño/a):</p>
<table class="data">
    <tr><th>Campo</th><th>¿Obligatorio?</th><th>Notas</th></tr>
    <tr><td>Nombre(s)</td><td class="req">Sí</td><td>Solo letras. Se guarda en mayúsculas.</td></tr>
    <tr><td>Apellido paterno</td><td class="req">Sí</td><td>Solo letras. Se guarda en mayúsculas.</td></tr>
    <tr><td>Apellido materno</td><td class="opt">No</td><td>Solo letras.</td></tr>
    <tr><td>Fecha de nacimiento</td><td class="opt">No</td><td>Ayuda a estimar el nivel educativo.</td></tr>
    <tr><td>Nivel de interés</td><td class="opt">No</td><td>Maternal, Preescolar, Primaria, Secundaria, Preparatoria.</td></tr>
    <tr><td>Ciclo escolar</td><td class="opt">No</td><td>Si no se elige, el sistema usa el ciclo activo.</td></tr>
</table>

<p class="step-title">Sección 2 — Datos del contacto (padre/tutor que hace el primer contacto):</p>
<table class="data">
    <tr><th>Campo</th><th>¿Obligatorio?</th><th>Notas</th></tr>
    <tr><td>Nombre del contacto</td><td class="req">Sí</td><td>Solo letras. Se guarda en mayúsculas.</td></tr>
    <tr><td>Teléfono</td><td class="req">Sí</td><td>Exactamente 10 dígitos numéricos.</td></tr>
    <tr><td>Correo electrónico</td><td class="opt">No</td><td>—</td></tr>
    <tr><td>Primer contacto (fecha)</td><td class="req">Sí</td><td>Por defecto es la fecha de hoy. Ajusta si el contacto fue en días anteriores.</td></tr>
    <tr><td>Canal de contacto</td><td class="opt">No</td><td>Referido, Redes sociales, Visita directa, Sitio web, Otro.</td></tr>
</table>
<div class="note">Al guardar, el sistema crea el prospecto en la etapa <strong>Prospecto</strong> y agrega automáticamente un seguimiento con la nota de creación. Desde la ficha podrás avanzar la etapa y registrar más seguimientos.</div>

<div class="subsection-title">3.3 Ficha del prospecto</div>
<p>Haz clic en el nombre de cualquier prospecto en la lista para abrir su ficha. Encontrarás:</p>
<ul>
    <li><strong>Datos generales</strong>: Nombre, nivel, ciclo, canal, fechas.</li>
    <li><strong>Pipeline de etapas</strong>: Visualización del avance dentro del proceso.</li>
    <li><strong>Historial de seguimientos</strong>: Línea de tiempo con todas las interacciones registradas.</li>
    <li><strong>Documentos</strong>: Archivos subidos por la familia (acta, CURP, etc.).</li>
</ul>

<div class="subsection-title">3.4 Avanzar etapa en el pipeline</div>
<p>El proceso de admisión sigue estas etapas en orden:</p>
<table class="pipeline">
    <tr>
        <td><span class="stage-badge s-prospecto">Prospecto</span></td>
        <td class="arrow">→</td>
        <td><span class="stage-badge s-cita">Cita</span></td>
        <td class="arrow">→</td>
        <td><span class="stage-badge s-visita">Visita</span></td>
        <td class="arrow">→</td>
        <td><span class="stage-badge s-docs">Documentación</span></td>
        <td class="arrow">→</td>
        <td><span class="stage-badge s-aceptado">Aceptado</span></td>
        <td class="arrow">→</td>
        <td><span class="stage-badge s-inscrito">Inscrito</span></td>
    </tr>
</table>
<p>Desde cualquier etapa también es posible cambiar a <span class="stage-badge s-noconcr">No concretado</span> si el proceso no se lleva a cabo.</p>
<p><strong>Para cambiar de etapa:</strong></p>
<ol>
    <li>Abre la ficha del prospecto.</li>
    <li>Haz clic en <strong>Cambiar etapa</strong>.</li>
    <li>Selecciona la nueva etapa en el desplegable.</li>
    <li>Escribe una nota explicando el motivo del cambio (mínimo 5 caracteres).</li>
    <li>Si seleccionas <strong>No concretado</strong>, indica también el <strong>motivo de no concreción</strong>.</li>
    <li>Haz clic en <strong>Guardar</strong>.</li>
</ol>
<div class="warning"><strong>Importante:</strong> No es posible retroceder etapas desde el sistema. Si cometiste un error en la etapa, contacta al administrador.</div>

<div class="subsection-title">3.5 Registrar un seguimiento</div>
<p>Un seguimiento registra cada interacción con la familia (llamada, correo, visita, nota interna, etc.).</p>
<ol>
    <li>En la ficha del prospecto, localiza la sección <strong>Seguimientos</strong>.</li>
    <li>Haz clic en <strong>Agregar seguimiento</strong>.</li>
    <li>Completa el formulario y haz clic en <strong>Guardar</strong>.</li>
</ol>
<table class="data">
    <tr><th>Campo</th><th>Descripción</th></tr>
    <tr><td>Tipo de acción</td><td>Llamada, Visita, Correo, Nota interna, Cambio de etapa</td></tr>
    <tr><td>Notas</td><td>Descripción de lo conversado o acordado</td></tr>
    <tr><td>Fecha</td><td>Fecha en que ocurrió la interacción</td></tr>
</table>

<div class="subsection-title">3.6 Subir documentos del prospecto</div>
<ol>
    <li>En la ficha del prospecto, localiza la sección <strong>Documentos</strong>.</li>
    <li>Haz clic en <strong>Subir documento</strong>.</li>
    <li>Elige el <strong>tipo de documento</strong>: Acta de nacimiento, CURP, Calificaciones, Certificado, Comprobante de domicilio, Vacunas, Fotografías, INE del padre/tutor, Comprobante de pago, Otro.</li>
    <li>Selecciona el archivo (PDF o imagen) y haz clic en <strong>Guardar</strong>.</li>
</ol>
<div class="note">Si subes un documento del mismo tipo dos veces, el nuevo <strong>reemplaza</strong> al anterior. No se duplica.</div>

<div class="subsection-title">3.7 Convertir prospecto a alumno</div>
<p>Una vez que el prospecto está en etapa <strong>Aceptado</strong>:</p>
<ol>
    <li>En la ficha del prospecto, haz clic en <strong>Convertir a alumno</strong> o en el enlace de inscripción.</li>
    <li>El sistema abre el asistente de registro con los datos del prospecto pre-cargados (nombre, apellidos, fecha de nacimiento y datos del contacto).</li>
    <li>Revisa los datos, completa los campos faltantes y finaliza el asistente (ver sección 4.2).</li>
    <li>Al guardar, el prospecto cambia automáticamente a etapa <strong>Inscrito</strong> y queda vinculado al alumno creado.</li>
</ol>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 4 — ALUMNOS
══════════════════════════════════════════════════ --}}
<div class="page-break"></div>
<div class="section-title">4. Módulo de Alumnos</div>

<div class="subsection-title">4.1 Lista de alumnos</div>
<p>Accede desde el menú lateral: <strong>Alumnos → Lista de alumnos</strong>.</p>
<table class="data">
    <tr><th>Columna</th><th>Descripción</th></tr>
    <tr><td>Matrícula</td><td>Número único del alumno, generado automáticamente al guardar</td></tr>
    <tr><td>Nombre</td><td>Nombre completo</td></tr>
    <tr><td>Nivel / Grupo</td><td>Nivel educativo y grupo asignado en el ciclo actual</td></tr>
    <tr><td>Estado</td><td>Activo, Baja temporal, Baja definitiva, Egresado</td></tr>
    <tr><td>Plan de pago</td><td>Plan asignado en el ciclo actual</td></tr>
</table>
<p><strong>Filtros disponibles:</strong> Por nombre, matrícula o CURP &nbsp;·&nbsp; Nivel &nbsp;·&nbsp; Grupo &nbsp;·&nbsp; Estado.</p>
<p>Haz clic en cualquier fila para abrir la ficha del alumno.</p>

<div class="subsection-title">4.2 Registrar un nuevo alumno</div>
<p>El registro se realiza en un <strong>asistente de 4 pasos</strong>. Accede desde el botón <strong>Nuevo alumno</strong> en la lista.</p>
<div class="note">
    La barra de progreso en la parte superior muestra en qué paso te encuentras. Puedes hacer clic en cualquier tarjeta de paso para navegar directamente, pero el sistema <strong>validará el paso actual</strong> antes de dejarte avanzar. El botón <strong>Registrar alumno</strong> solo aparece en el paso 4.
</div>

<div class="page-break"></div>

<div class="step-box no-break">
<div class="step-box-title">Paso 1 — Datos personales</div>
<table class="data">
    <tr><th>Campo</th><th>¿Obligatorio?</th><th>Notas</th></tr>
    <tr><td>Nombre(s)</td><td class="req">Sí</td><td>Mínimo 2 caracteres. Se guarda en mayúsculas.</td></tr>
    <tr><td>Apellido paterno</td><td class="req">Sí</td><td>Mínimo 2 caracteres. Se guarda en mayúsculas.</td></tr>
    <tr><td>Apellido materno</td><td class="opt">No</td><td>Se guarda en mayúsculas.</td></tr>
    <tr><td>Fecha de nacimiento</td><td class="req">Sí</td><td>El alumno debe tener entre 2 y 25 años.</td></tr>
    <tr><td>Género</td><td class="req">Sí</td><td>Masculino / Femenino / Otro.</td></tr>
    <tr><td>Fecha de inscripción</td><td class="req">Sí</td><td>Por defecto es hoy. Ajusta si la inscripción fue en otra fecha.</td></tr>
    <tr><td>CURP</td><td class="opt">No</td><td>Exactamente 18 caracteres con formato oficial. El sistema lo valida.</td></tr>
    <tr><td>Foto del alumno</td><td class="opt">No</td><td>JPG, PNG o WEBP. Máximo 2 MB.</td></tr>
    <tr><td>Observaciones</td><td class="opt">No</td><td>Notas internas visibles solo para el personal.</td></tr>
</table>
<p style="font-size:10px;font-weight:bold;color:#6b7a8d;text-transform:uppercase;margin:8px 0 4px;">Domicilio (opcional)</p>
<table class="data">
    <tr><th>Campo</th><th>Notas</th></tr>
    <tr><td>Calle y número</td><td>Ej: Av. Reforma 123 Int. 4</td></tr>
    <tr><td>Colonia</td><td>—</td></tr>
    <tr><td>Código postal</td><td>—</td></tr>
    <tr><td>Ciudad</td><td>—</td></tr>
    <tr><td>Estado</td><td>Entidad federativa</td></tr>
    <tr><td>Religión</td><td>Opcional</td></tr>
</table>
<p style="font-size:10px;">Haz clic en <strong>Siguiente</strong> para continuar al paso 2.</p>
</div>

<div class="step-box no-break">
<div class="step-box-title">Paso 2 — Inscripción</div>
<table class="data">
    <tr><th>Campo</th><th>¿Obligatorio?</th><th>Notas</th></tr>
    <tr><td>Ciclo escolar</td><td class="req">Sí</td><td>Se muestra el ciclo activo por defecto.</td></tr>
    <tr><td>Nivel educativo</td><td class="req">Sí</td><td>Maternal, Preescolar, Primaria, Secundaria, Preparatoria.</td></tr>
    <tr><td>Grupo</td><td class="req">Sí</td><td>Se carga automáticamente al elegir ciclo y nivel. Muestra inscritos/cupo.</td></tr>
</table>
<div class="note" style="font-size:9px;">Si un grupo muestra <strong>[LLENO]</strong>, ya alcanzó su cupo máximo. Elige otro grupo o consulta al administrador para ampliar la capacidad.</div>
<p style="font-size:10px;">Haz clic en <strong>Siguiente</strong> para continuar al paso 3.</p>
</div>

<div class="step-box no-break">
<div class="step-box-title">Paso 3 — Familia y vínculo con admisiones</div>
<p style="font-size:10px;">Este paso tiene dos partes: la vinculación familiar (obligatoria) y el vínculo con un prospecto (opcional).</p>
<p style="font-size:10px;font-weight:bold;">Parte A — Familia</p>
<p style="font-size:10px;">El sistema pregunta: <em>"¿El alumno tiene hermanos inscritos?"</em></p>
<table class="data">
    <tr><th>Opción</th><th>Cuándo usarla</th><th>Qué hace</th></tr>
    <tr>
        <td><strong>No, es familia nueva</strong></td>
        <td>El alumno no tiene hermanos en la escuela.</td>
        <td>El sistema sugiere el nombre de la familia con los apellidos del alumno. Puedes modificarlo.</td>
    </tr>
    <tr>
        <td><strong>Sí, vincular a familia existente</strong></td>
        <td>Hay un hermano u otro familiar ya inscrito.</td>
        <td>Aparece un buscador. Al seleccionar la familia, los contactos se cargan automáticamente en el Paso 4.</td>
    </tr>
</table>
<div class="tip" style="font-size:9px;">El número entre paréntesis en el buscador de familias indica cuántos alumnos ya pertenecen a esa familia. Ej: <em>García López (2)</em> = 2 alumnos ya inscritos.</div>
<p style="font-size:10px;font-weight:bold;margin-top:8px;">Parte B — Vínculo con prospecto (opcional)</p>
<p style="font-size:10px;">Si el alumno fue registrado antes como prospecto, puedes vincularlo aquí. Al guardarlo, el prospecto cambiará automáticamente a etapa <strong>Inscrito</strong>. Si el alumno es un ingreso directo, deja este campo vacío.</p>
<p style="font-size:10px;">Haz clic en <strong>Siguiente</strong> para continuar al paso 4.</p>
</div>

<div class="step-box no-break">
<div class="step-box-title">Paso 4 — Contactos familiares</div>
<p style="font-size:10px;">Debes registrar <strong>al menos 1 contacto</strong> y puedes agregar hasta <strong>3 contactos</strong>. Usa el botón <strong>+ Agregar</strong> para añadir más.</p>
<div class="note" style="font-size:9px;">Si vinculaste una familia existente en el Paso 3, los contactos se cargan automáticamente. Solo podrás configurar sus <strong>permisos</strong> para este nuevo alumno (los datos personales del contacto no se duplican ni se pueden editar aquí).</div>

<p style="font-size:10px;font-weight:bold;margin-top:8px;">Datos del contacto (familia nueva):</p>
<table class="data">
    <tr><th>Campo</th><th>¿Obligatorio?</th><th>Notas</th></tr>
    <tr><td>Nombre(s)</td><td class="req">Sí</td><td>Se guarda en mayúsculas.</td></tr>
    <tr><td>Apellido paterno</td><td class="opt">No</td><td>—</td></tr>
    <tr><td>Apellido materno</td><td class="opt">No</td><td>—</td></tr>
    <tr><td>Teléfono celular</td><td class="req">Sí</td><td>10 dígitos.</td></tr>
    <tr><td>Correo electrónico</td><td class="opt">No</td><td>—</td></tr>
    <tr><td>CURP del contacto</td><td class="opt">No</td><td>18 caracteres.</td></tr>
    <tr><td>Parentesco</td><td class="req">Sí</td><td>Padre, Madre, Abuelo/a, Tío/a, Otro.</td></tr>
    <tr><td>Tipo</td><td class="req">Sí</td><td>Padre/Madre · Tutor · Tercero autorizado.</td></tr>
    <tr><td>Orden</td><td class="req">Sí</td><td>1 = Principal, 2 = Secundario, 3 = Tercero. El primero se preselecciona como 1.</td></tr>
</table>

<p style="font-size:10px;font-weight:bold;margin-top:8px;">Permisos del contacto para este alumno:</p>
<table class="data">
    <tr><th>Permiso</th><th>Descripción</th><th>Predeterminado</th></tr>
    <tr><td>Autorizado para recoger</td><td>Puede retirar al alumno de la escuela.</td><td>Activo en el 1er contacto</td></tr>
    <tr><td>Responsable de pagos</td><td>Recibe estados de cuenta y notificaciones de cobro.</td><td>Activo en el 1er contacto</td></tr>
    <tr><td>Acceso al portal</td><td>Puede consultar información del alumno en el portal de padres.</td><td>Sin activar</td></tr>
</table>

<p style="font-size:10px;font-style:italic;color:#888;">La sección "Datos adicionales" del contacto (teléfono 2, nivel de estudios, profesión, lugar de trabajo, foto) es completamente opcional.</p>
<p style="font-size:10px;">Cuando termines, haz clic en <strong>Registrar alumno</strong>. El sistema genera la matrícula automáticamente.</p>
</div>

<div class="subsection-title">4.3 Ficha del alumno</div>
<p>Haz clic en cualquier alumno de la lista para ver su ficha. Contiene:</p>
<ul>
    <li><strong>Encabezado</strong>: Foto, nombre completo, matrícula, estado y grupo actual.</li>
    <li><strong>Datos personales</strong>: Fecha de nacimiento, género, CURP, domicilio, religión, observaciones.</li>
    <li><strong>Historial de inscripciones</strong>: Ciclos, niveles y grupos en los que ha estado.</li>
    <li><strong>Contactos familiares</strong>: Lista de contactos con sus permisos.</li>
    <li><strong>Expediente médico</strong>: Tipo de sangre, alergias, condiciones, medicamentos autorizados.</li>
    <li><strong>Documentos</strong>: Lista de documentos requeridos con estatus (entregado / pendiente).</li>
    <li><strong>Becas</strong>: Becas aplicadas en el ciclo actual.</li>
</ul>
<p>Desde la ficha puedes acceder a <strong>Editar</strong> datos, descargar la <strong>ficha PDF</strong> y gestionar el estado del alumno.</p>

<div class="subsection-title">4.4 Editar datos del alumno</div>
<ol>
    <li>Abre la ficha del alumno (búscalo en la lista o desde la vista de su grupo).</li>
    <li>Haz clic en el botón <strong>Editar</strong>.</li>
    <li>El sistema abre el mismo asistente de 4 pasos con los datos actuales pre-cargados.</li>
    <li>Navega hasta el paso que contiene el campo que deseas modificar.</li>
    <li>Realiza los cambios necesarios.</li>
    <li>Avanza hasta el paso 4 y haz clic en <strong>Guardar cambios</strong>.</li>
</ol>
<div class="note">Puedes navegar libremente entre los pasos (hacia atrás y adelante) antes de guardar. <strong>Los cambios no se aplican hasta hacer clic en Guardar cambios en el paso 4.</strong></div>
<div class="warning">Si solo necesitas modificar un campo del Paso 1 (por ejemplo, corregir un apellido), igualmente deberás llegar hasta el Paso 4 y hacer clic en <strong>Guardar cambios</strong> para que la modificación se aplique.</div>

<p><strong>Campos que NO se pueden cambiar desde la edición:</strong></p>
<ul>
    <li>Matrícula (generada automáticamente por el sistema).</li>
    <li>Grupo activo (se gestiona desde la vista del grupo o la ficha del alumno en la sección de inscripciones).</li>
</ul>

<div class="subsection-title">4.5 Cambiar estado del alumno</div>
<table class="data">
    <tr><th>Estado</th><th>Descripción</th></tr>
    <tr><td><strong>Activo</strong></td><td>Alumno con inscripción vigente en el ciclo actual.</td></tr>
    <tr><td><strong>Baja temporal</strong></td><td>Ausencia temporal; puede reactivarse cuando regrese.</td></tr>
    <tr><td><strong>Baja definitiva</strong></td><td>El alumno abandona la institución de forma permanente.</td></tr>
    <tr><td><strong>Egresado</strong></td><td>Completó el nivel o ciclo. Se aplica al finalizar el ciclo escolar.</td></tr>
</table>
<p><strong>Para dar de baja (temporal o definitiva):</strong></p>
<ol>
    <li>En la ficha del alumno, haz clic en el botón de baja correspondiente.</li>
    <li>Selecciona el tipo de baja.</li>
    <li>Escribe una <strong>observación</strong> indicando el motivo.</li>
    <li>Confirma la acción.</li>
</ol>
<p><strong>Para reactivar un alumno en baja temporal:</strong> Abre la ficha y haz clic en <strong>Reactivar</strong>.</p>
<div class="warning"><strong>Importante:</strong> La baja definitiva no puede deshacerse desde el sistema. Asegúrate de que sea correcta antes de confirmarla.</div>

<div class="subsection-title">4.6 Contactos familiares</div>
<p>Los contactos se gestionan desde la ficha del alumno, sección <strong>Contactos</strong>.</p>
<ul>
    <li>Puedes ver el teléfono, correo y permisos de cada contacto.</li>
    <li>Puedes editar los <strong>permisos</strong> del contacto para ese alumno específico.</li>
</ul>
<div class="note">Para agregar un nuevo contacto a un alumno ya inscrito, ve a <strong>Familias</strong>, busca la familia del alumno y agrega el contacto desde ahí. Los cambios en la familia se reflejan en todos los alumnos vinculados.</div>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 5 — GRUPOS
══════════════════════════════════════════════════ --}}
<div class="page-break"></div>
<div class="section-title">5. Módulo de Grupos</div>

<div class="subsection-title">5.1 Lista de grupos</div>
<p>Accede desde el menú lateral: <strong>Académico → Grupos</strong>.</p>
<table class="data">
    <tr><th>Columna</th><th>Descripción</th></tr>
    <tr><td>Icono / Imagen</td><td>Imagen representativa del grupo (si el administrador la configuró)</td></tr>
    <tr><td>Nivel / Grado</td><td>Nivel educativo y número de grado</td></tr>
    <tr><td>Identificador</td><td>Nombre del grupo (ej. A, B, ÚNICO)</td></tr>
    <tr><td>Cupo</td><td>Máximo de alumnos permitidos (∞ si no hay límite)</td></tr>
    <tr><td>Inscritos</td><td>Alumnos actualmente en el grupo (azul = con cupo, rojo = lleno)</td></tr>
    <tr><td>Disponibles</td><td>Lugares restantes (rojo si quedan 5 o menos)</td></tr>
</table>
<p><strong>Filtros disponibles:</strong> Nivel · Grado · Estado (Activo / Inactivo / Todos).</p>
<div class="note">Con el rol Admisiones o Recepción, la vista de grupos es de <strong>solo consulta</strong>. No aparecen los botones de editar, desactivar ni eliminar grupos.</div>

<div class="subsection-title">5.2 Ver alumnos de un grupo</div>
<ol>
    <li>En la lista de grupos, haz clic en el ícono <strong>Ver alumnos</strong> (icono de personas) o en el nombre del grupo.</li>
    <li>Se abre la vista del grupo con la lista de alumnos inscritos y su historial.</li>
    <li>Desde esta vista puedes:
        <ul>
            <li>Ver el nombre y matrícula de cada alumno.</li>
            <li>Hacer clic en el menú de opciones (<strong>⋮</strong>) de cada alumno para ver su perfil o ficha PDF.</li>
            <li>Generar el reporte PDF de alumnos activos del grupo (<strong>Formatos impresos → Reporte (Activos)</strong>).</li>
        </ul>
    </li>
</ol>
<div class="note">Las opciones de Promocionar, Expediente Médico y Álbum fotográfico no están disponibles para los roles Admisiones y Recepción.</div>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 6 — FAQ
══════════════════════════════════════════════════ --}}
<div class="page-break"></div>
<div class="section-title">6. Preguntas frecuentes</div>

<div class="faq-q">¿Puedo inscribir a un alumno sin registrarlo como prospecto antes?</div>
<div class="faq-a">Sí. El vínculo con un prospecto es completamente opcional. Puedes ir directo a <strong>Alumnos → Nuevo alumno</strong>. El campo de prospecto en el Paso 3 se puede dejar vacío.</div>

<div class="faq-q">¿Qué hago si el grupo que necesito no aparece en el Paso 2?</div>
<div class="faq-a">El grupo puede estar inactivo, no existir para ese nivel/ciclo, o no haberse creado aún. Informa al administrador para que lo cree o reactive.</div>

<div class="faq-q">¿Puedo cambiar a un alumno de grupo después de inscribirlo?</div>
<div class="faq-a">Esa acción la realiza el <strong>Administrador</strong> desde la vista del grupo (opción "Cambiar de grupo") o desde la ficha del alumno.</div>

<div class="faq-q">¿Qué pasa si selecciono una familia existente en el Paso 3?</div>
<div class="faq-a">En el Paso 4 verás los contactos de esa familia pre-cargados en modo solo lectura. Solo podrás configurar sus <strong>permisos</strong> para este nuevo alumno (autorizado recoger, responsable de pagos, acceso al portal). Los datos personales del contacto no se duplican ni se modifican.</div>

<div class="faq-q">¿Cómo sé si ya existe una familia para el alumno que voy a inscribir?</div>
<div class="faq-a">En el Paso 3, selecciona "Sí, vincular a familia existente" y busca por apellido en el buscador. Si el apellido no aparece, la familia no existe y debes crear una nueva.</div>

<div class="faq-q">¿Qué significa que el grupo aparece en azul, gris o rojo en la lista?</div>
<div class="faq-a">El color del contador de inscritos indica: <strong>Gris</strong> = sin alumnos. <strong>Azul</strong> = con cupo disponible. <strong>Rojo</strong> = cupo agotado.</div>

<div class="faq-q">¿Cómo sé si un alumno ya tiene adeudos?</div>
<div class="faq-a">La sección Estado de cuenta está disponible para el área de Caja. Si solo tienes rol Recepción o Admisiones, consulta directamente al área de caja para confirmar el estatus financiero del alumno.</div>

<div class="faq-q">¿Puedo eliminar un prospecto registrado por error?</div>
<div class="faq-a">La eliminación de prospectos no está disponible para estos roles. Cambia la etapa del prospecto a <strong>No concretado</strong> y agrega una nota indicando que fue un registro duplicado o erróneo. El administrador puede borrarlo definitivamente si es necesario.</div>

<div class="faq-q">¿Qué significa el número entre paréntesis en el buscador de familias?</div>
<div class="faq-a">Es el número de alumnos que ya pertenecen a esa familia. Por ejemplo: <em>Familia García López (2)</em> = esa familia ya tiene 2 alumnos inscritos en el sistema.</div>

<div class="faq-q">¿Se puede deshacer una baja definitiva?</div>
<div class="faq-a">No directamente. Para reintegrar al alumno es necesario crear una nueva inscripción. Asegúrate siempre de que la baja sea correcta antes de confirmarla.</div>

<div class="faq-q">Guardé el alumno pero me regresó al paso 1 con errores en rojo. ¿Qué hago?</div>
<div class="faq-a">El sistema detectó un error de validación y te lleva directamente al paso que lo contiene. Lee el mensaje en rojo debajo del campo, corrígelo y vuelve a llegar al Paso 4 para guardar.</div>

<div class="footer">
    {{ $nombreEscuela }} &nbsp;·&nbsp; Manual de Usuario — Admisiones y Recepción &nbsp;·&nbsp; CGEscolar v2.0 &nbsp;·&nbsp; {{ now()->format('Y') }}
</div>

</body>
</html>
