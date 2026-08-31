<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Calificación capturada por un docente.
 *
 * El tipo de valor almacenado depende del plan de estudios de la asignación:
 *   - valor_numerico     → escala numérica  (Primaria, Secundaria, Preparatoria)
 *   - criterio_id        → escala literal   (criterios A/B/C definidos en EscalaEvaluacion)
 *   - texto_descriptivo  → campo formativo  (Preescolar, párrafo descriptivo)
 *
 * @property int             $id
 * @property int             $alumno_id
 * @property int             $periodo_id
 * @property int             $asignacion_id
 * @property float|null      $valor_numerico
 * @property int|null        $criterio_id
 * @property string|null     $texto_descriptivo
 * @property int             $capturado_por
 * @property \Carbon\Carbon  $fecha_captura
 */
class Calificacion extends Model
{
    protected $table = 'calificacion';

    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'periodo_id',
        'asignacion_id',
        'valor_numerico',
        'criterio_id',
        'texto_descriptivo',
        'capturado_por',
        'fecha_captura',
    ];

    protected function casts(): array
    {
        return [
            'valor_numerico' => 'float',
            'fecha_captura'  => 'datetime',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeDelPeriodo(Builder $query, int $periodoId): Builder
    {
        return $query->where('periodo_id', $periodoId);
    }

    public function scopeDelAlumno(Builder $query, int $alumnoId): Builder
    {
        return $query->where('alumno_id', $alumnoId);
    }

    public function scopeDeAsignacion(Builder $query, int $asignacionId): Builder
    {
        return $query->where('asignacion_id', $asignacionId);
    }

    // ── Relaciones ────────────────────────────────────────

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluativo::class, 'periodo_id');
    }

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(AsignacionDocente::class, 'asignacion_id');
    }

    public function criterio(): BelongsTo
    {
        return $this->belongsTo(CriterioEvaluacion::class, 'criterio_id');
    }

    public function capturadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'capturado_por');
    }

    // ── Helpers ───────────────────────────────────────────

    /** Indica si esta calificación tiene algún valor registrado. */
    public function tieneValor(): bool
    {
        return ! is_null($this->valor_numerico)
            || ! is_null($this->criterio_id)
            || ! is_null($this->texto_descriptivo);
    }

    /**
     * Devuelve la representación legible del valor capturado.
     * Útil para mostrar en boleta o resumen.
     */
    public function valorMostrable(): string
    {
        if (! is_null($this->valor_numerico)) {
            return number_format($this->valor_numerico, 1);
        }

        if ($this->criterio_id && $this->criterio) {
            return $this->criterio->etiqueta;
        }

        return $this->texto_descriptivo ?? '—';
    }
}
