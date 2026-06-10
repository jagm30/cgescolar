<?php

namespace App\Enums;

enum TipoInscripcion: string
{
    /** Inscripción en el ciclo escolar vigente */
    case Regular = 'regular';

    /** Inscripción con anticipación al ciclo próximo (en configuración) */
    case Anticipada = 'anticipada';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Regular    => 'Regular',
            self::Anticipada => 'Anticipada',
        };
    }
}
