<?php

namespace App\Enums;

/**
 * Periodicidad de evaluación de un plan de estudios.
 *
 * - trimestral  → 3 períodos por ciclo (Preescolar, Primaria, Secundaria)
 * - bimestral   → 2 períodos por ciclo (Preparatoria)
 */
enum TipoPeriodo: string
{
    case Trimestral = 'trimestral';
    case Bimestral  = 'bimestral';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Trimestral => 'Trimestral',
            self::Bimestral  => 'Bimestral',
        };
    }

    /** Número de períodos que genera este tipo en un ciclo escolar. */
    public function totalPeriodos(): int
    {
        return match ($this) {
            self::Trimestral => 3,
            self::Bimestral  => 2,
        };
    }

    /** Nombre del período (Trimestre / Bimestre). */
    public function nombrePeriodo(): string
    {
        return match ($this) {
            self::Trimestral => 'Trimestre',
            self::Bimestral  => 'Bimestre',
        };
    }
}
