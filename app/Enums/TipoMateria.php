<?php

namespace App\Enums;

/**
 * Clasificación de una materia según su origen curricular.
 *
 * - sep           → materia oficial del plan SEP (impacta promedio SEP)
 * - institucional → materia propia de la institución (impacta promedio institucional)
 */
enum TipoMateria: string
{
    case Sep           = 'sep';
    case Institucional = 'institucional';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Sep           => 'SEP',
            self::Institucional => 'Institucional',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Sep           => 'label-primary',
            self::Institucional => 'label-success',
        };
    }
}
