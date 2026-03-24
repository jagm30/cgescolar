<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoDetalle extends Model
{
    protected $table = 'pago_detalle';
    public $timestamps = false;

    protected $fillable = [
        'pago_id',
        'cargo_id',
        'descuento_beca',
        'descuento_otros',
        'recargo_aplicado',
        'monto_abonado',
        'monto_final',
    ];

    protected $casts = [
        'descuento_beca'   => 'decimal:2',
        'descuento_otros'  => 'decimal:2',
        'recargo_aplicado' => 'decimal:2',
        'monto_abonado'    => 'decimal:2',
        'monto_final'      => 'decimal:2',
    ];

    // ── Relaciones ───────────────────────────────────────

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }
}
