<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondonacionDetalle extends Model
{
    protected $table = 'condonacion_detalle';

    public $timestamps = false;

    protected $fillable = [
        'condonacion_id',
        'cargo_id',
        'descuento_cargo_id',
        'monto_aplicado',
    ];

    protected $casts = [
        'monto_aplicado' => 'decimal:2',
        'creado_at' => 'datetime',
    ];

    // ── Relaciones ───────────────────────────────────────

    public function condonacion(): BelongsTo
    {
        return $this->belongsTo(Condonacion::class, 'condonacion_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function descuentoCargo(): BelongsTo
    {
        return $this->belongsTo(DescuentoCargo::class, 'descuento_cargo_id');
    }
}
