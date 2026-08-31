<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Asignación de docente.
 *
 * Vincula un docente con una materia (o campo formativo) en un grupo y ciclo.
 * Es la fuente de verdad de qué puede capturar cada docente.
 *
 * Regla: exactamente uno de materia_id / campo_id debe tener valor.
 *   - materia_id  → Primaria, Secundaria, Preparatoria
 *   - campo_id    → Preescolar
 *
 * @property int      $id
 * @property int      $docente_id
 * @property int      $grupo_id
 * @property int      $ciclo_id
 * @property int|null $materia_id
 * @property int|null $campo_id
 * @property bool     $activa
 */
class AsignacionDocente extends Model
{
    protected $table = 'asignacion_docente';

    public $timestamps = false;

    protected $fillable = [
        'docente_id',
        'grupo_id',
        'ciclo_id',
        'materia_id',
        'campo_id',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activa', true);
    }

    public function scopeDelCiclo(Builder $query, int $cicloId): Builder
    {
        return $query->where('ciclo_id', $cicloId);
    }

    public function scopeDelDocente(Builder $query, int $docenteId): Builder
    {
        return $query->where('docente_id', $docenteId);
    }

    // ── Relaciones ────────────────────────────────────────

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'docente_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function campoFormativo(): BelongsTo
    {
        return $this->belongsTo(CampoFormativo::class, 'campo_id');
    }

    // ── Helpers ───────────────────────────────────────────

    public function esDePreescolar(): bool
    {
        return ! is_null($this->campo_id);
    }

    /** Etiqueta legible del contenido asignado (materia o campo formativo). */
    public function etiquetaContenido(): string
    {
        return $this->materia?->nombre
            ?? $this->campoFormativo?->nombre
            ?? '—';
    }
}
