<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BecaAlumno extends Model
{
    protected $table = 'beca_alumno';

    public $timestamps = false;

    protected $fillable = [
        'catalogo_beca_id',
        'alumno_id',
        'ciclo_id',
        'plan_id',
        'concepto_id',
        'vigencia_inicio',
        'vigencia_fin',
        'motivo',
        'activo',
        'creado_por',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fin' => 'date',
        'activo' => 'boolean',
        'creado_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeVigenteHoy(Builder $query): Builder
    {
        return $query->where('activo', true)
            ->where('vigencia_inicio', '<=', now())
            ->where(function ($q) {
                $q->whereNull('vigencia_fin')
                    ->orWhere('vigencia_fin', '>=', now());
            });
    }

    // ── Helpers ──────────────────────────────────────────

    /**
     * Calcula el descuento usando el valor del catálogo.
     */
    public function calcularDescuento(float $montoBase): float
    {
        return $this->catalogoBeca->calcularDescuento($montoBase);
    }

    // ── Relaciones ───────────────────────────────────────

    public function catalogoBeca(): BelongsTo
    {
        return $this->belongsTo(CatalogoBeca::class, 'catalogo_beca_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoCobro::class, 'concepto_id')
            ->withDefault(function () {
                return new ConceptoCobro([
                    'nombre' => $this->plan?->nombre ?? '—',
                ]);
            });
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function getDestinoBecaAttribute(): string
    {
        return $this->plan?->nombre
            ?? $this->concepto?->nombre
            ?? '—';
    }
}
