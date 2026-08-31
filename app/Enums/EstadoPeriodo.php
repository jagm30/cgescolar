<?php

namespace App\Enums;

/**
 * Estado de un período evaluativo.
 *
 * - pendiente → creado pero no abierto para captura
 * - abierto   → docentes pueden capturar calificaciones
 * - cerrado   → captura finalizada, calificaciones publicadas
 */
enum EstadoPeriodo: string
{
    case Pendiente = 'pendiente';
    case Abierto   = 'abierto';
    case Cerrado   = 'cerrado';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Abierto   => 'Abierto',
            self::Cerrado   => 'Cerrado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pendiente => 'label-default',
            self::Abierto   => 'label-success',
            self::Cerrado   => 'label-danger',
        };
    }

    /** Indica si el período acepta captura de calificaciones. */
    public function aceptaCaptura(): bool
    {
        return $this === self::Abierto;
    }
}
