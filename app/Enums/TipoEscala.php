<?php

namespace App\Enums;

/**
 * Tipo de escala de evaluación.
 *
 * - numerica  → valor decimal (6.0 – 10.0)
 * - literal   → etiqueta de texto con equivalente numérico (MA, A, EnP, I)
 */
enum TipoEscala: string
{
    case Numerica = 'numerica';
    case Literal  = 'literal';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Numerica => 'Numérica',
            self::Literal  => 'Literal (descriptiva)',
        };
    }
}
