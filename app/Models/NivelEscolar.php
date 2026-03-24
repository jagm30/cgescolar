<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelEscolar extends Model
{
    protected $table = 'nivel_escolar';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true)->orderBy('orden');
    }

    // ── Relaciones ───────────────────────────────────────

    public function grados(): HasMany
    {
        return $this->hasMany(Grado::class, 'nivel_id');
    }

    public function planesPago(): HasMany
    {
        return $this->hasMany(PlanPago::class, 'nivel_id');
    }

    public function asignacionesPlanes(): HasMany
    {
        return $this->hasMany(AsignacionPlan::class, 'nivel_id');
    }
}
