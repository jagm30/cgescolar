<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Criterio de una escala de evaluación literal.
 *
 * Cada criterio representa un nivel descriptivo (ej: MA = Muy Avanzado)
 * con su equivalente numérico para el cálculo de promedios.
 *
 * @property int    $id
 * @property int    $escala_id
 * @property string $etiqueta         Código corto: "MA", "A", "EnP", "I"
 * @property string $descripcion      Texto completo: "Muy Avanzado"
 * @property float  $valor_numerico   Equivalente para promedios
 * @property int    $orden
 */
class CriterioEvaluacion extends Model
{
    protected $table = 'criterio_evaluacion';

    public $timestamps = false;

    protected $fillable = [
        'escala_id',
        'etiqueta',
        'descripcion',
        'valor_numerico',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'valor_numerico' => 'decimal:2',
            'orden'          => 'integer',
        ];
    }

    // ── Relaciones ────────────────────────────────────────

    public function escala(): BelongsTo
    {
        return $this->belongsTo(EscalaEvaluacion::class, 'escala_id');
    }
}
