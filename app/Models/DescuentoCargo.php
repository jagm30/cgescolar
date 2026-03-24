<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescuentoCargo extends Model
{
    protected $table = 'descuento_cargo';
    public $timestamps = false;

    protected $fillable = [
        'cargo_id',
        'tipo',
        'valor',
        'monto_aplicado',
        'motivo',
        'autorizado_por',
        'creado_por',
    ];

    protected $casts = [
        'valor'          => 'decimal:2',
        'monto_aplicado' => 'decimal:2',
        'creado_at'      => 'datetime',
    ];

    // ── Relaciones ───────────────────────────────────────

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
}
