<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanPago extends Model
{
    protected $table = 'plan_pago';
    public $timestamps = false;

    protected $fillable = [
        'ciclo_id',
        'nivel_id',
        'nombre',
        'periodicidad',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'activo'       => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // ── Helpers ──────────────────────────────────────────

    /** Política de recargo activa del plan */
    public function politicaRecargoActiva(): ?PoliticaRecargo
    {
        return $this->politicasRecargo()->where('activo', true)->first();
    }

    // ── Relaciones ───────────────────────────────────────

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscolar::class, 'nivel_id');
    }

    public function conceptos(): BelongsToMany
    {
        return $this->belongsToMany(
            ConceptoCobro::class,
            'plan_pago_concepto',
            'plan_id',
            'concepto_id'
        )->withPivot('monto');
    }

    public function planPagoConceptos(): HasMany
    {
        return $this->hasMany(PlanPagoConcepto::class, 'plan_id');
    }

    public function politicasDescuento(): HasMany
    {
        return $this->hasMany(PoliticaDescuento::class, 'plan_id');
    }

    public function politicasDescuentoActivas(): HasMany
    {
        return $this->hasMany(PoliticaDescuento::class, 'plan_id')
                    ->where('activo', true);
    }

    public function politicasRecargo(): HasMany
    {
        return $this->hasMany(PoliticaRecargo::class, 'plan_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionPlan::class, 'plan_id');
    }
}
