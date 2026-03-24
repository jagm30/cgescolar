<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogoBeca extends Model
{
    protected $table = 'catalogo_beca';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'activo',
    ];

    protected $casts = [
        'valor'     => 'decimal:2',
        'activo'    => 'boolean',
        'creado_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // ── Helpers ──────────────────────────────────────────

    /**
     * Calcula el monto de descuento dado un monto base de colegiatura.
     */
    public function calcularDescuento(float $montoBase): float
    {
        return $this->tipo === 'porcentaje'
            ? round($montoBase * ((float) $this->valor / 100), 2)
            : (float) $this->valor;
    }

    // ── Relaciones ───────────────────────────────────────

    public function asignaciones(): HasMany
    {
        return $this->hasMany(BecaAlumno::class, 'catalogo_beca_id');
    }
}
