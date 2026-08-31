<?php

namespace App\Models;

use App\Enums\TipoEscala;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Escala de evaluación.
 *
 * Define cómo se califica en cada nivel académico.
 * Las escalas literales tienen criterios asociados en CriterioEvaluacion.
 *
 * @property int        $id
 * @property string     $nombre
 * @property TipoEscala $tipo
 * @property float|null $valor_minimo
 * @property float|null $valor_maximo
 * @property float|null $valor_aprobatorio
 * @property bool       $activa
 */
class EscalaEvaluacion extends Model
{
    protected $table = 'escala_evaluacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'valor_minimo',
        'valor_maximo',
        'valor_aprobatorio',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'tipo'              => TipoEscala::class,
            'valor_minimo'      => 'decimal:2',
            'valor_maximo'      => 'decimal:2',
            'valor_aprobatorio' => 'decimal:2',
            'activa'            => 'boolean',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeLiteral(Builder $query): Builder
    {
        return $query->where('tipo', TipoEscala::Literal->value);
    }

    // ── Relaciones ────────────────────────────────────────

    public function criterios(): HasMany
    {
        return $this->hasMany(CriterioEvaluacion::class, 'escala_id')
            ->orderBy('orden');
    }

    public function planesEstudios(): HasMany
    {
        return $this->hasMany(PlanEstudios::class, 'escala_id');
    }

    // ── Helpers ───────────────────────────────────────────

    public function esLiteral(): bool
    {
        return $this->tipo === TipoEscala::Literal;
    }

    public function esNumerica(): bool
    {
        return $this->tipo === TipoEscala::Numerica;
    }
}
