<?php

namespace App\Enums;

/**
 * Roles del sistema.
 *
 * Fuente de verdad para todos los valores de la columna `usuario.rol`.
 * Usar siempre Rol::X->value en lugar de strings literales.
 */
enum Rol: string
{
    // ── Control escolar ──────────────────────────────────
    case Administrador         = 'administrador';
    case Caja                  = 'caja';
    case Recepcion             = 'recepcion';
    case Admisiones            = 'admisiones';
    case InformacionAdmisiones = 'informacion_admisiones';
    case DirectorSeccion       = 'director_seccion';

    // ── Módulo educativo ─────────────────────────────────
    case Docente               = 'docente';

    // ── Portal de padres ─────────────────────────────────
    case Padre                 = 'padre';

    // ─────────────────────────────────────────────────────

    /**
     * Roles que tienen acceso al sistema interno
     * (excluyendo el portal de padres).
     *
     * @return list<string>
     */
    public static function internos(): array
    {
        return [
            self::Administrador->value,
            self::Caja->value,
            self::Recepcion->value,
            self::Admisiones->value,
            self::InformacionAdmisiones->value,
            self::DirectorSeccion->value,
            self::Docente->value,
        ];
    }

    /**
     * Roles con acceso al módulo de control escolar
     * (sin incluir docentes ni padres).
     *
     * @return list<string>
     */
    public static function controlEscolar(): array
    {
        return [
            self::Administrador->value,
            self::Caja->value,
            self::Recepcion->value,
            self::Admisiones->value,
            self::InformacionAdmisiones->value,
            self::DirectorSeccion->value,
        ];
    }
}
