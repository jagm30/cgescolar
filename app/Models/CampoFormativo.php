<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Campo Formativo (exclusivo Preescolar).
 *
 * Preescolar no usa calificaciones numéricas sino evaluaciones
 * descriptivas por campo formativo. El docente captura un párrafo
 * de texto con el límite definido en max_caracteres.
 *
 * @property int    $id
 * @property int    $plan_id
 * @property string $nombre
 * @property int    $max_caracteres
 * @property int    $orden
 * @property bool   $activo
 */
class CampoFormativo extends Model
{
    protected $table = 'campo_formativo';

    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'nombre',
        'max_caracteres',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'max_caracteres' => 'integer',
            'orden'          => 'integer',
            'activo'         => 'boolean',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    // ── Relaciones ────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudios::class, 'plan_id');
    }
}
