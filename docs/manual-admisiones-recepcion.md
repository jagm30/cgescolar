# Manual de Usuario — Admisiones y Recepción
## CGEscolar · Sistema de Gestión Escolar

**Versión:** 2.0  
**Fecha:** {{ now()->format('F Y') }}  
**Dirigido a:** Usuarios con rol Recepción y Admisiones

---

## Contenido

1. [Introducción](#1-introducción)
2. [Acceso al sistema](#2-acceso-al-sistema)
3. [Módulo de Admisiones — Prospectos](#3-módulo-de-admisiones--prospectos)
   - 3.1 [Lista de prospectos](#31-lista-de-prospectos)
   - 3.2 [Registrar un nuevo prospecto](#32-registrar-un-nuevo-prospecto)
   - 3.3 [Ficha del prospecto](#33-ficha-del-prospecto)
   - 3.4 [Avanzar etapa en el pipeline](#34-avanzar-etapa-en-el-pipeline)
   - 3.5 [Registrar un seguimiento](#35-registrar-un-seguimiento)
   - 3.6 [Subir documentos del prospecto](#36-subir-documentos-del-prospecto)
   - 3.7 [Convertir prospecto a alumno](#37-convertir-prospecto-a-alumno)
4. [Módulo de Alumnos](#4-módulo-de-alumnos)
   - 4.1 [Lista de alumnos](#41-lista-de-alumnos)
   - 4.2 [Registrar un nuevo alumno](#42-registrar-un-nuevo-alumno)
   - 4.3 [Ficha del alumno](#43-ficha-del-alumno)
   - 4.4 [Editar datos del alumno](#44-editar-datos-del-alumno)
   - 4.5 [Cambiar estado del alumno](#45-cambiar-estado-del-alumno)
   - 4.6 [Contactos familiares](#46-contactos-familiares)
5. [Módulo de Grupos](#5-módulo-de-grupos)
   - 5.1 [Lista de grupos](#51-lista-de-grupos)
   - 5.2 [Ver alumnos de un grupo](#52-ver-alumnos-de-un-grupo)
6. [Preguntas frecuentes](#6-preguntas-frecuentes)

---

## 1. Introducción

Este manual describe las operaciones del día a día que realizan los usuarios con rol **Recepción** y **Admisiones** dentro de CGEscolar.

Con estos roles podrás:

- Registrar y dar seguimiento a prospectos (familias interesadas en la escuela).
- Convertir un prospecto aceptado en alumno inscrito.
- Registrar nuevos alumnos de forma manual mediante un asistente de 4 pasos.
- Consultar y actualizar la información de alumnos existentes.
- Consultar la distribución de grupos y los alumnos asignados a cada uno.

> **Nota:** Algunas acciones están reservadas al rol **Administrador** (eliminar registros, configurar ciclos, gestionar becas, crear/editar grupos, etc.). Si necesitas realizar una de esas acciones, contacta al administrador del sistema.

> **Convención del sistema:** Los campos de texto (nombres, apellidos) se convierten a **MAYÚSCULAS** automáticamente mientras escribes. No es necesario activar Bloq Mayús.

---

## 2. Acceso al sistema

1. Abre el navegador (Chrome o Firefox recomendados).
2. Escribe la dirección del sistema que te proporcionó el administrador.
3. Ingresa tu **correo electrónico** y **contraseña**.
4. Haz clic en **Iniciar sesión**.

Si olvidaste tu contraseña, solicita al administrador que la restablezca.

Una vez dentro verás el **Panel principal** con el resumen del ciclo escolar activo.

---

## 3. Módulo de Admisiones — Prospectos

### 3.1 Lista de prospectos

Accede desde el menú lateral: **Admisiones → Prospectos**.

| Columna | Descripción |
|---|---|
| Nombre | Nombre del prospecto (futuro alumno) |
| Contacto | Nombre y teléfono del padre/tutor |
| Nivel | Nivel educativo de interés |
| Etapa | Paso actual dentro del pipeline |
| Primer contacto | Fecha en que se registró |
| Canal | Cómo se enteró de la escuela |

**Filtros disponibles:**
- **Búsqueda**: Escribe parte del nombre del prospecto, nombre del contacto o teléfono.
- **Etapa**: Filtra por una etapa específica del pipeline.
- **Ciclo**: Muestra prospectos de un ciclo escolar determinado.
- **En proceso**: Muestra únicamente los prospectos activos (excluye inscritos y no concretados).

---

### 3.2 Registrar un nuevo prospecto

El formulario de registro es una **sola página** dividida en dos secciones: datos del prospecto (niño/a) y datos del contacto (padre/tutor). No hay pasos ni botón "Siguiente".

1. Haz clic en el botón **Nuevo prospecto** (esquina superior derecha de la lista).
2. Completa los datos del prospecto.
3. Completa los datos del contacto.
4. Haz clic en **Guardar prospecto**.

#### Sección 1 — Datos del prospecto (niño/a)

| Campo | Obligatorio | Notas |
|---|---|---|
| Nombre(s) | **Sí** | Solo letras. Se guarda en mayúsculas. |
| Apellido paterno | **Sí** | Solo letras. Se guarda en mayúsculas. |
| Apellido materno | No | Solo letras. |
| Fecha de nacimiento | No | Ayuda a estimar el nivel educativo. |
| Nivel de interés | No | Maternal, Preescolar, Primaria, Secundaria, Preparatoria. |
| Ciclo escolar | No | Si se deja en blanco, el sistema usa el ciclo activo. |

#### Sección 2 — Datos del contacto (padre/tutor que hace el primer contacto)

| Campo | Obligatorio | Notas |
|---|---|---|
| Nombre del contacto | **Sí** | Solo letras. Se guarda en mayúsculas. |
| Teléfono | **Sí** | Exactamente 10 dígitos numéricos. |
| Correo electrónico | No | — |
| Primer contacto (fecha) | **Sí** | Por defecto es hoy. Ajusta si el contacto fue en días anteriores. |
| Canal de contacto | No | Referido, Redes sociales, Visita directa, Sitio web, Otro. |

> Al guardar, el sistema crea el prospecto en la etapa **Prospecto** y agrega automáticamente un seguimiento con la nota de creación. Desde la ficha podrás avanzar la etapa y registrar más seguimientos.

---

### 3.3 Ficha del prospecto

Haz clic en el nombre de cualquier prospecto en la lista para abrir su ficha. Encontrarás:

- **Datos generales**: Nombre, nivel, ciclo, canal, fechas.
- **Pipeline de etapas**: Visualización del avance dentro del proceso de admisión.
- **Historial de seguimientos**: Línea de tiempo con todas las interacciones registradas (llamadas, visitas, notas).
- **Documentos**: Archivos subidos por la familia (acta de nacimiento, CURP, etc.).

---

### 3.4 Avanzar etapa en el pipeline

El proceso de admisión sigue estas etapas en orden:

```
Prospecto → Cita → Visita → Documentación → Aceptado → Inscrito
                                          ↘
                                       No concretado
```

**Para cambiar de etapa:**

1. Abre la ficha del prospecto.
2. Haz clic en el botón **Cambiar etapa**.
3. Selecciona la nueva etapa en el desplegable.
4. Escribe una nota explicando el motivo del cambio (mínimo 5 caracteres).
5. Si seleccionas **No concretado**, también debes indicar el **motivo de no concreción**.
6. Haz clic en **Guardar**.

El cambio queda registrado automáticamente en el historial de seguimientos.

> **Importante:** No es posible retroceder etapas desde el sistema. Si cometiste un error en la etapa, contacta al administrador.

---

### 3.5 Registrar un seguimiento

Un seguimiento registra cada interacción con la familia (llamada telefónica, correo enviado, visita, nota interna, etc.).

1. En la ficha del prospecto, localiza la sección **Seguimientos**.
2. Haz clic en **Agregar seguimiento**.
3. Completa el formulario:

| Campo | Descripción |
|---|---|
| Tipo de acción | Llamada, Visita, Correo, Nota interna, Cambio de etapa |
| Notas | Descripción de lo conversado o acordado |
| Fecha | Fecha en que ocurrió la interacción |

4. Haz clic en **Guardar**.

El seguimiento aparece en la línea de tiempo de la ficha, ordenado por fecha.

---

### 3.6 Subir documentos del prospecto

Durante el proceso de admisión puedes guardar los documentos que la familia va entregando.

1. En la ficha del prospecto, localiza la sección **Documentos**.
2. Haz clic en **Subir documento**.
3. Elige el **tipo de documento** en el desplegable:
   - Acta de nacimiento, CURP, Calificaciones, Certificado, Comprobante de domicilio, Vacunas, Fotografías, INE del padre/tutor, Comprobante de pago, Otro.
4. Selecciona el archivo desde tu computadora (PDF o imagen).
5. Haz clic en **Guardar**.

> Si subes un documento del mismo tipo dos veces, el nuevo **reemplaza** al anterior. No se duplica.

Para descargar un documento ya subido, haz clic en el ícono de descarga junto al nombre del archivo.

---

### 3.7 Convertir prospecto a alumno

Una vez que el prospecto está en etapa **Aceptado**:

1. En la ficha del prospecto, haz clic en **Convertir a alumno** o en el enlace de inscripción.
2. El sistema abre el **asistente de registro de alumno** con los datos del prospecto pre-cargados:
   - Nombre, apellidos, fecha de nacimiento.
   - Datos del contacto del padre/tutor.
   - Nivel y ciclo del prospecto como sugerencia.
3. Revisa los datos pre-cargados, completa los campos faltantes y finaliza el asistente (ver sección [4.2](#42-registrar-un-nuevo-alumno)).
4. Al guardar, el prospecto cambia automáticamente a etapa **Inscrito** y queda vinculado al alumno creado.

---

## 4. Módulo de Alumnos

### 4.1 Lista de alumnos

Accede desde el menú lateral: **Alumnos → Lista de alumnos**.

| Columna | Descripción |
|---|---|
| Matrícula | Número único del alumno, generado automáticamente al guardar |
| Nombre | Nombre completo |
| Nivel / Grupo | Nivel educativo y grupo asignado en el ciclo actual |
| Estado | Activo, Baja temporal, Baja definitiva, Egresado |
| Plan de pago | Plan asignado en el ciclo actual |

**Filtros disponibles:**
- **Búsqueda**: Por nombre, matrícula o CURP.
- **Nivel**: Filtra por nivel educativo.
- **Grupo**: Filtra por grupo dentro del nivel seleccionado.
- **Estado**: Activo / Baja temporal / Baja definitiva / Egresado.

Haz clic en cualquier fila para abrir la ficha del alumno.

---

### 4.2 Registrar un nuevo alumno

El registro se realiza en un **asistente de 4 pasos**. Accede desde el botón **Nuevo alumno** en la lista.

> La barra de progreso en la parte superior muestra en qué paso te encuentras. Puedes hacer clic en cualquier tarjeta de paso para navegar libremente, pero el sistema **validará el paso actual** antes de dejarte avanzar al siguiente. El botón **Registrar alumno** solo aparece en el Paso 4.

---

#### Paso 1 — Datos personales

| Campo | Obligatorio | Notas |
|---|---|---|
| Nombre(s) | **Sí** | Mínimo 2 caracteres. Se guarda en mayúsculas. |
| Apellido paterno | **Sí** | Mínimo 2 caracteres. Se guarda en mayúsculas. |
| Apellido materno | No | Se guarda en mayúsculas. |
| Fecha de nacimiento | **Sí** | El alumno debe tener entre 2 y 25 años. |
| Género | **Sí** | Masculino / Femenino / Otro. |
| Fecha de inscripción | **Sí** | Por defecto es hoy. Ajusta si la inscripción fue en otra fecha. |
| CURP | No | Exactamente 18 caracteres con formato oficial. El sistema valida el formato. |
| Foto del alumno | No | JPG, PNG o WEBP. Máximo 2 MB. |
| Observaciones | No | Notas internas visibles solo para el personal. |

**Domicilio (todos opcionales):** Calle y número · Colonia · Código postal · Ciudad · Estado · Religión.

Haz clic en **Siguiente** para continuar.

---

#### Paso 2 — Inscripción

| Campo | Obligatorio | Notas |
|---|---|---|
| Ciclo escolar | **Sí** | Se muestra el ciclo activo por defecto. |
| Nivel educativo | **Sí** | Maternal, Preescolar, Primaria, Secundaria, Preparatoria. |
| Grupo | **Sí** | Se carga automáticamente al elegir ciclo y nivel. Muestra cuántos inscritos/cupo tiene cada grupo. |

> Si un grupo muestra **[LLENO]**, ya alcanzó su cupo máximo. Elige otro grupo o consulta al administrador para ampliar la capacidad.

Haz clic en **Siguiente** para continuar.

---

#### Paso 3 — Familia y vínculo con admisiones

Este paso tiene dos partes: la vinculación familiar (obligatoria) y el vínculo con un prospecto (opcional).

**Parte A — Familia**

El sistema pregunta: *"¿El alumno tiene hermanos inscritos?"*

| Opción | Cuándo usarla | Qué hace |
|---|---|---|
| **No, es familia nueva** | El alumno no tiene hermanos en la escuela. | El sistema sugiere el nombre de la familia con los apellidos del alumno. Puedes modificarlo. |
| **Sí, vincular a familia existente** | Hay un hermano u otro familiar ya inscrito. | Aparece un buscador de familias. Al seleccionar, los contactos se cargan automáticamente en el Paso 4. |

> El número entre paréntesis en el buscador indica cuántos alumnos ya pertenecen a esa familia. Ej: *García López (2)* = 2 alumnos ya inscritos.

**Parte B — Vínculo con prospecto (opcional)**

Si el alumno fue registrado antes como prospecto en el módulo de Admisiones, puedes vincularlo aquí usando el buscador. Al guardar, el prospecto cambiará automáticamente a etapa **Inscrito**. Si el alumno es un ingreso directo sin prospecto previo, deja este campo vacío.

Haz clic en **Siguiente** para continuar.

---

#### Paso 4 — Contactos familiares

Debes registrar **al menos 1 contacto** y puedes agregar hasta **3 contactos**. Usa el botón **+ Agregar** para añadir más contactos.

> Si vinculaste una **familia existente** en el Paso 3, los contactos se cargan automáticamente en modo solo lectura. Solo podrás configurar sus **permisos** para este nuevo alumno. Los datos personales del contacto no se duplican ni se pueden editar aquí.

**Datos del contacto (cuando es familia nueva):**

| Campo | Obligatorio | Notas |
|---|---|---|
| Nombre(s) | **Sí** | Se guarda en mayúsculas. |
| Apellido paterno | No | — |
| Apellido materno | No | — |
| Teléfono celular | **Sí** | 10 dígitos. |
| Correo electrónico | No | — |
| CURP del contacto | No | 18 caracteres. |
| Parentesco | **Sí** | Padre, Madre, Abuelo/a, Tío/a, Otro. |
| Tipo | **Sí** | Padre/Madre · Tutor · Tercero autorizado. |
| Orden | **Sí** | 1 = Principal, 2 = Secundario, 3 = Tercero. El primero se preselecciona como Principal. |

**Permisos del contacto para este alumno:**

| Permiso | Descripción | Predeterminado |
|---|---|---|
| Autorizado para recoger | Puede retirar al alumno de la escuela. | Activo en el 1er contacto |
| Responsable de pagos | Recibe estados de cuenta y notificaciones de cobro. | Activo en el 1er contacto |
| Acceso al portal | Puede consultar información del alumno en el portal de padres. | Sin activar |

> La sección **Datos adicionales** del contacto (teléfono 2, nivel de estudios, profesión, lugar de trabajo, foto del contacto) es completamente opcional.

Cuando termines, haz clic en **Registrar alumno**. El sistema genera la matrícula automáticamente.

---

### 4.3 Ficha del alumno

Haz clic en cualquier alumno de la lista para ver su ficha. Contiene:

- **Encabezado**: Foto, nombre completo, matrícula, estado y grupo actual.
- **Datos personales**: Fecha de nacimiento, género, CURP, domicilio, religión, observaciones.
- **Historial de inscripciones**: Ciclos, niveles y grupos en los que ha estado.
- **Contactos familiares**: Lista de contactos con sus permisos.
- **Expediente médico**: Tipo de sangre, alergias, condiciones, medicamentos autorizados.
- **Documentos**: Lista de documentos requeridos con estatus (entregado / pendiente).
- **Becas**: Becas aplicadas en el ciclo actual.

Desde la ficha puedes acceder a **Editar** datos, descargar la **ficha PDF** y gestionar el estado del alumno.

---

### 4.4 Editar datos del alumno

1. Abre la ficha del alumno (búscalo en la lista o desde la vista de su grupo).
2. Haz clic en el botón **Editar**.
3. El sistema abre el mismo asistente de 4 pasos con los datos actuales pre-cargados.
4. Navega hasta el paso que contiene el campo que deseas modificar.
5. Realiza los cambios necesarios.
6. Avanza hasta el Paso 4 y haz clic en **Guardar cambios**.

> Puedes navegar libremente entre los pasos (hacia atrás y adelante) antes de guardar. **Los cambios no se aplican hasta hacer clic en Guardar cambios en el Paso 4.**

> **Atención:** Si solo necesitas modificar un campo del Paso 1 (por ejemplo, corregir un apellido), igualmente deberás llegar hasta el Paso 4 y hacer clic en **Guardar cambios** para que la modificación se aplique.

**Campos que NO se pueden cambiar desde la edición:**
- **Matrícula**: Generada automáticamente por el sistema, no es editable.
- **Grupo activo**: Se gestiona desde la vista del grupo o la ficha del alumno en la sección de inscripciones (acción exclusiva del Administrador).

---

### 4.5 Cambiar estado del alumno

| Estado | Descripción |
|---|---|
| **Activo** | Alumno con inscripción vigente en el ciclo actual. |
| **Baja temporal** | Ausencia temporal; puede reactivarse cuando regrese. |
| **Baja definitiva** | El alumno abandona la institución de forma permanente. |
| **Egresado** | Completó el nivel o ciclo. Se aplica al finalizar el ciclo escolar. |

**Para dar de baja (temporal o definitiva):**

1. En la ficha del alumno, haz clic en el botón de baja correspondiente.
2. Selecciona el tipo de baja.
3. Escribe una **observación** indicando el motivo.
4. Confirma la acción.

**Para reactivar un alumno en baja temporal:**

1. Abre la ficha del alumno.
2. Haz clic en **Reactivar**.
3. Confirma. El estado regresa a **Activo**.

> **Importante:** La baja definitiva no puede deshacerse desde el sistema. Asegúrate de que sea correcta antes de confirmarla.

---

### 4.6 Contactos familiares

Los contactos familiares se gestionan desde la ficha del alumno, en la sección **Contactos**.

- Puedes ver el teléfono, correo y permisos de cada contacto.
- Puedes editar los **permisos** del contacto para ese alumno específico (autorizado recoger, responsable pagos, acceso portal).

> Para agregar un nuevo contacto a un alumno ya inscrito, ve a **Familias**, busca la familia del alumno y agrega el contacto desde ahí. Los cambios en la familia se reflejan en todos los alumnos vinculados a ella.

---

## 5. Módulo de Grupos

### 5.1 Lista de grupos

Accede desde el menú lateral: **Académico → Grupos**.

| Columna | Descripción |
|---|---|
| Icono | Imagen representativa del grupo (si fue configurada) |
| Nivel / Grado | Nivel educativo y número de grado |
| Identificador | Nombre del grupo (ej. A, B, ÚNICO) |
| Cupo | Máximo de alumnos permitidos (∞ si no hay límite) |
| Inscritos | Alumnos actualmente en el grupo (azul = con cupo, rojo = lleno) |
| Disponibles | Lugares restantes (se muestra en rojo si quedan 5 o menos) |

**Filtros disponibles:** Nivel · Grado · Estado (Activo / Inactivo / Todos).

> Con el rol Admisiones o Recepción, la vista de grupos es de **solo consulta**. No aparecen los botones de editar, desactivar ni eliminar grupos.

---

### 5.2 Ver alumnos de un grupo

1. En la lista de grupos, haz clic en el ícono **Ver alumnos** (icono de personas azul) de la fila del grupo.
2. Se abre la vista del grupo con la lista de alumnos inscritos y el historial de movimientos.
3. Desde esta vista puedes:
   - Ver el nombre y matrícula de cada alumno.
   - Hacer clic en el menú de opciones (**⋮**) de cada alumno para ver su perfil, ficha PDF o estado de cuenta.
   - Generar el reporte PDF de alumnos activos del grupo desde **Formatos impresos (Grupales) → Reporte (Activos)**.

> Las opciones de Promocionar, Expediente Médico y Álbum fotográfico no están disponibles para los roles Admisiones y Recepción.

---

## 6. Preguntas frecuentes

**¿Puedo inscribir a un alumno sin registrarlo como prospecto antes?**  
Sí. El vínculo con un prospecto es completamente opcional. Puedes ir directo a **Alumnos → Nuevo alumno**. El campo de prospecto en el Paso 3 se puede dejar vacío.

---

**¿Qué hago si el grupo que necesito no aparece en el Paso 2 del registro?**  
El grupo puede estar inactivo, no existir para ese nivel/ciclo, o no haberse creado aún. Informa al administrador para que lo cree o reactive.

---

**¿Puedo cambiar a un alumno de grupo después de inscribirlo?**  
Esa acción la realiza el **Administrador** desde la vista del grupo (opción "Cambiar de grupo") o desde la ficha del alumno.

---

**¿Qué pasa si selecciono una familia existente en el Paso 3?**  
En el Paso 4 verás los contactos de esa familia pre-cargados en modo solo lectura. Solo podrás configurar sus **permisos** para este nuevo alumno (autorizado recoger, responsable de pagos, acceso al portal). Los datos personales del contacto no se duplican ni se modifican.

---

**¿Cómo sé si ya existe una familia para el alumno que voy a inscribir?**  
En el Paso 3, selecciona "Sí, vincular a familia existente" y busca por apellido en el buscador. Si el apellido no aparece, la familia no existe y debes crear una nueva.

---

**¿Qué significan los colores del contador de inscritos en la lista de grupos?**  
**Gris** = sin alumnos. **Azul** = con cupo disponible. **Rojo** = cupo agotado.

---

**¿Cómo sé si un alumno ya tiene adeudos?**  
La sección Estado de cuenta está disponible para el área de Caja. Si solo tienes rol Recepción o Admisiones, consulta directamente al área de caja para confirmar el estatus financiero del alumno antes de cualquier gestión.

---

**¿Puedo eliminar un prospecto registrado por error?**  
La eliminación de prospectos no está disponible para estos roles. Cambia la etapa del prospecto a **No concretado** y agrega una nota indicando que fue un registro duplicado o erróneo. El administrador puede borrarlo definitivamente si es necesario.

---

**¿Qué significa el número entre paréntesis en el buscador de familias?**  
Es el número de alumnos que ya pertenecen a esa familia. Por ejemplo: *Familia García López (2)* = esa familia ya tiene 2 alumnos inscritos en el sistema.

---

**¿Se puede deshacer una baja definitiva?**  
No directamente. Para reintegrar al alumno es necesario crear una nueva inscripción. Asegúrate siempre de que la baja sea correcta antes de confirmarla.

---

**Guardé el alumno pero me regresó al Paso 1 con campos en rojo. ¿Qué hago?**  
El sistema detectó un error de validación y te lleva directamente al paso que lo contiene. Lee el mensaje en rojo debajo del campo, corrígelo y vuelve a llegar al Paso 4 para hacer clic en **Guardar cambios**.

---

*Documento generado para capacitación interna. Para reportar errores o solicitar actualizaciones, contacta al administrador del sistema.*
