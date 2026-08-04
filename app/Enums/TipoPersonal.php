<?php

namespace App\Enums;

enum TipoPersonal: string
{
    case Docente        = 'docente';
    case Administrativo = 'administrativo';
    case Mantenimiento  = 'mantenimiento';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Docente        => 'Docente',
            self::Administrativo => 'Administrativo',
            self::Mantenimiento  => 'Mantenimiento',
        };
    }

    public function colorBadge(): string
    {
        return match ($this) {
            self::Docente        => 'badge-info',
            self::Administrativo => 'badge-primary',
            self::Mantenimiento  => 'badge-warning',
        };
    }
}
