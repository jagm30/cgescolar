<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConceptoCobro extends Model
{
    protected $table = 'concepto_cobro';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'aplica_beca',
        'aplica_recargo',
        'clave_sat',
        'activo',
    ];

    protected $casts = [
        'aplica_beca'    => 'boolean',
        'aplica_recargo' => 'boolean',
        'activo'         => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeColegiatura($query)
    {
        return $query->where('tipo', 'colegiatura');
    }

    public function scopeAplicaBeca($query)
    {
        return $query->where('aplica_beca', true);
    }

    // ── Relaciones ───────────────────────────────────────

    public function planesPago(): BelongsToMany
    {
        return $this->belongsToMany(
            PlanPago::class,
            'plan_pago_concepto',
            'concepto_id',
            'plan_id'
        )->withPivot('monto');
    }

    public function politicasRecargo(): HasMany
    {
        return $this->hasMany(PoliticaRecargo::class, 'concepto_id');
    }

    public function becasAlumno(): HasMany
    {
        return $this->hasMany(BecaAlumno::class, 'concepto_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'concepto_id');
    }
}
