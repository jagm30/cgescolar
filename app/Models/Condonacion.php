<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Condonacion extends Model
{
    protected $table = 'condonacion';

    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'ciclo_id',
        'monto_total',
        'motivo',
        'estado',
        'creado_por',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'creado_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActiva($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopeCancelada($query)
    {
        return $query->where('estado', 'cancelada');
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CondonacionDetalle::class, 'condonacion_id');
    }
}
