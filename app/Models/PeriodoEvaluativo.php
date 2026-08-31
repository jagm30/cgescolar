<?php

namespace App\Models;

use App\Enums\EstadoPeriodo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Período evaluativo.
 *
 * Representa un corte de evaluación (trimestre o bimestre) dentro de un ciclo.
 * El administrador controla su ciclo de vida: pendiente → abierto → cerrado.
 *
 * @property int              $id
 * @property int              $ciclo_id
 * @property int              $plan_id
 * @property string           $nombre
 * @property int              $numero
 * @property string|null      $fecha_inicio
 * @property string|null      $fecha_fin
 * @property EstadoPeriodo    $estado
 * @property \Carbon\Carbon|null $fecha_apertura_captura
 * @property \Carbon\Carbon|null $fecha_cierre_captura
 */
class PeriodoEvaluativo extends Model
{
    protected $table = 'periodo_evaluativo';

    public $timestamps = false;

    protected $fillable = [
        'ciclo_id',
        'plan_id',
        'nombre',
        'numero',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'fecha_apertura_captura',
        'fecha_cierre_captura',
    ];

    protected function casts(): array
    {
        return [
            'estado'                 => EstadoPeriodo::class,
            'numero'                 => 'integer',
            'fecha_apertura_captura' => 'datetime',
            'fecha_cierre_captura'   => 'datetime',
        ];
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeAbierto(Builder $query): Builder
    {
        return $query->where('estado', EstadoPeriodo::Abierto->value);
    }

    public function scopePendiente(Builder $query): Builder
    {
        return $query->where('estado', EstadoPeriodo::Pendiente->value);
    }

    public function scopeDelCiclo(Builder $query, int $cicloId): Builder
    {
        return $query->where('ciclo_id', $cicloId);
    }

    // ── Relaciones ────────────────────────────────────────

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudios::class, 'plan_id');
    }

    // ── Helpers ───────────────────────────────────────────

    public function estaAbierto(): bool
    {
        return $this->estado === EstadoPeriodo::Abierto;
    }

    public function estaCerrado(): bool
    {
        return $this->estado === EstadoPeriodo::Cerrado;
    }

    public function estaPendiente(): bool
    {
        return $this->estado === EstadoPeriodo::Pendiente;
    }

    /** Abre el período para captura de calificaciones. */
    public function abrir(): void
    {
        $this->update([
            'estado'                 => EstadoPeriodo::Abierto->value,
            'fecha_apertura_captura' => now(),
        ]);
    }

    /** Cierra el período e impide nuevas capturas. */
    public function cerrar(): void
    {
        $this->update([
            'estado'               => EstadoPeriodo::Cerrado->value,
            'fecha_cierre_captura' => now(),
        ]);
    }
}
