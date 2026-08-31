<?php

namespace App\Models;

use App\Enums\TipoMateria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Materia de un plan de estudios.
 *
 * Aplica a Primaria, Secundaria y Preparatoria.
 * Preescolar usa CampoFormativo en su lugar.
 *
 * @property int        $id
 * @property int        $plan_id
 * @property string     $nombre
 * @property string|null $clave_sep
 * @property TipoMateria $tipo
 * @property int        $horas_semanales
 * @property int        $orden
 * @property bool       $activa
 */
class Materia extends Model
{
    protected $table = 'materia';

    public $timestamps = false;

    protected $fillable = [
        'plan_id',
        'nombre',
        'clave_sep',
        'tipo',
        'horas_semanales',
        'orden',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'tipo'            => TipoMateria::class,
            'horas_semanales' => 'integer',
            'orden'           => 'integer',
            'activa'          => 'boolean',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeSep(Builder $query): Builder
    {
        return $query->where('tipo', TipoMateria::Sep->value);
    }

    public function scopeInstitucional(Builder $query): Builder
    {
        return $query->where('tipo', TipoMateria::Institucional->value);
    }

    // ── Relaciones ────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudios::class, 'plan_id');
    }

    // ── Helpers ───────────────────────────────────────────

    public function esSep(): bool
    {
        return $this->tipo === TipoMateria::Sep;
    }
}
