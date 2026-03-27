{{--
    Snippets jQuery para resources/views/familias/show.blade.php
    Incluir dentro de @push('scripts')
--}}

@push('scripts')
<script>
const familiaId = {{ $familia->id }};

$(document).ready(function () {
    cargarContactos();
});

// ── Cargar contactos con estado de portal ────────────────
function cargarContactos() {
    $.ajax({
        url: '/familias/' + familiaId + '/contactos',
        method: 'GET',
        success: function (contactos) {
            const tbody = $('#tabla-contactos tbody').empty();

            if (!contactos.length) {
                tbody.append('<tr><td colspan="5">Sin contactos registrados.</td></tr>');
                return;
            }

            contactos.forEach(function (c) {
                const estadoBadge = {
                    'activo':      '<span class="badge-verde">Activo</span>',
                    'pendiente':   '<span class="badge-amarillo">Pendiente crear usuario</span>',
                    'desactivado': '<span class="badge-rojo">Desactivado</span>',
                    'sin_acceso':  '<span class="badge-gris">Sin acceso</span>',
                }[c.estado_portal] ?? '';

                const acciones = construirAcciones(c);

                tbody.append(`
                    <tr>
                        <td>${c.nombre_completo}</td>
                        <td>${c.telefono_celular ?? '—'}</td>
                        <td>${c.email ?? '—'}</td>
                        <td>${estadoBadge}</td>
                        <td>${acciones}</td>
                    </tr>
                `);
            });
        }
    });
}

function construirAcciones(c) {
    @if(auth()->user()->esAdministrador())
    if (c.estado_portal === 'sin_acceso') {
        return `<button onclick="habilitarPortal(${c.id}, '${c.nombre_completo}')">Habilitar acceso</button>`;
    }
    if (c.estado_portal === 'pendiente') {
        return `
            <button onclick="crearUsuario(${c.id}, '${c.nombre_completo}', '${c.email ?? ''}')">Crear usuario</button>
            <button onclick="deshabilitarPortal(${c.id}, '${c.nombre_completo}')">Deshabilitar</button>
        `;
    }
    if (c.estado_portal === 'activo') {
        return `
            <button onclick="resetearPassword(${c.id}, '${c.nombre_completo}')">Resetear contraseña</button>
            <button onclick="deshabilitarPortal(${c.id}, '${c.nombre_completo}')">Deshabilitar</button>
        `;
    }
    if (c.estado_portal === 'desactivado') {
        return `<button onclick="habilitarPortal(${c.id}, '${c.nombre_completo}')">Reactivar acceso</button>`;
    }
    @endif
    return '—';
}

// ── Habilitar acceso al portal ───────────────────────────
function habilitarPortal(contactoId, nombre) {
    if (!confirm(`¿Habilitar acceso al portal para ${nombre}?`)) return;

    $.ajax({
        url: '/familias/contactos/' + contactoId + '/habilitar-portal',
        method: 'POST',
        success: function (res) {
            alert(res.message);
            cargarContactos();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Error al habilitar acceso.');
        }
    });
}

// ── Deshabilitar acceso al portal ────────────────────────
function deshabilitarPortal(contactoId, nombre) {
    if (!confirm(`¿Deshabilitar acceso al portal para ${nombre}?\nSi tiene usuario, también será desactivado.`)) return;

    $.ajax({
        url: '/familias/contactos/' + contactoId + '/deshabilitar-portal',
        method: 'POST',
        success: function (res) {
            alert(res.message);
            cargarContactos();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Error al deshabilitar acceso.');
        }
    });
}

// ── Crear usuario del portal ─────────────────────────────
function crearUsuario(contactoId, nombre, emailSugerido) {
    const email = prompt(`Correo electrónico para ${nombre}:`, emailSugerido);
    if (email === null) return; // Canceló

    const password = prompt('Contraseña inicial (mínimo 8 caracteres).\nDeja vacío para generar automáticamente:');
    if (password === null) return;

    $.ajax({
        url: '/familias/contactos/' + contactoId + '/crear-usuario',
        method: 'POST',
        data: JSON.stringify({
            email:    email    || null,
            password: password || null,
        }),
        success: function (res) {
            // Mostrar contraseña inicial al admin para entregarla al padre
            alert(
                res.message + '\n\n' +
                'Email: ' + res.usuario.email + '\n' +
                'Contraseña inicial: ' + res.password_inicial + '\n\n' +
                'Entrega estas credenciales al padre de familia.'
            );
            cargarContactos();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Error al crear el usuario.');
        }
    });
}

// ── Resetear contraseña ──────────────────────────────────
function resetearPassword(contactoId, nombre) {
    if (!confirm(`¿Resetear la contraseña de ${nombre}?\nSe generará una contraseña temporal.`)) return;

    $.ajax({
        url: '/familias/contactos/' + contactoId + '/resetear-password',
        method: 'POST',
        success: function (res) {
            alert(
                res.message + '\n\n' +
                'Email: ' + res.email + '\n' +
                'Nueva contraseña: ' + res.nueva_password + '\n\n' +
                'Entrega estas credenciales al padre de familia.'
            );
            cargarContactos();
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message ?? 'Error al resetear la contraseña.');
        }
    });
}
</script>
@endpush
