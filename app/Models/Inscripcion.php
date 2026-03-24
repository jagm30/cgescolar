<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inscripcion extends Model
{
    protected $table = 'inscripcion';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'ciclo_id',
        'grupo_id',
        'fecha',
        'activo',
    ];

    protected $casts = [
        'fecha'  => 'date',
        'activo' => 'boolean',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    // ── Relaciones ───────────────────────────────────────

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'inscripcion_id');
    }

    /** Cargos pendientes o parciales */
    public function cargosPendientes(): HasMany
    {
        return $this->hasMany(Cargo::class, 'inscripcion_id')
                    ->whereIn('estado', ['pendiente', 'parcial']);
    }
}
