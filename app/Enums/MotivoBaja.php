<?php

namespace App\Enums;

enum MotivoBaja: string
{
    case CambioEscuela  = 'cambio_escuela';
    case Traslado       = 'traslado';
    case Economico      = 'economico';
    case Familiar       = 'familiar';
    case Salud          = 'salud';
    case Conducta       = 'conducta';
    case Rendimiento    = 'rendimiento';
    case Otro           = 'otro';

    public function etiqueta(): string
    {
        return match ($this) {
            self::CambioEscuela => 'Cambio de escuela',
            self::Traslado      => 'Traslado de ciudad/estado',
            self::Economico     => 'Motivos económicos',
            self::Familiar      => 'Situación familiar',
            self::Salud         => 'Problemas de salud',
            self::Conducta      => 'Problemas de conducta',
            self::Rendimiento   => 'Bajo rendimiento académico',
            self::Otro          => 'Otro motivo',
        };
    }
}
