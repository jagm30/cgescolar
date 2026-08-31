<?php

namespace App\Models;

use App\Enums\TipoPeriodo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Plan de estudios.
 *
 * Currículo de un nivel académico. Puede ser genérico (ciclo_id = null)
 * o versionado para un ciclo específico cuando hay cambio de materias.
 *
 * Regla de resolución: al buscar el plan vigente de un nivel en un ciclo,
 * se prefiere el plan con ciclo_id coincidente; si no existe, se usa el genérico.
 *
 * @property int             $id
 * @property int             $nivel_id
 * @property int             $escala_id
 * @property int|null        $ciclo_id
 * @property string          $nombre
 * @property TipoPeriodo     $tipo_periodo
 * @property bool            $activo
 */
class PlanEstudios extends Model
{
    protected $table = 'plan_estudios';

    public $timestamps = false;

    protected $fillable = [
        'nivel_id',
        'escala_id',
        'ciclo_id',
        'nombre',
        'tipo_periodo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'tipo_periodo' => TipoPeriodo::class,
            'activo'       => 'boolean',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /** Planes genéricos (aplican a cualquier ciclo). */
    public function scopeGenerico(Builder $query): Builder
    {
        return $query->whereNull('ciclo_id');
    }

    // ── Relaciones ────────────────────────────────────────

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscolar::class, 'nivel_id');
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(EscalaEvaluacion::class, 'escala_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class, 'plan_id')
            ->orderBy('orden');
    }

    public function camposFormativos(): HasMany
    {
        return $this->hasMany(CampoFormativo::class, 'plan_id')
            ->orderBy('orden');
    }

    // ── Helpers ───────────────────────────────────────────

    public function esGenerico(): bool
    {
        return is_null($this->ciclo_id);
    }
}
