<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPagoConcepto extends Model
{
    protected $table = 'plan_pago_concepto';
    public $timestamps = false;

    protected $fillable = ['plan_id', 'concepto_id', 'monto'];

    protected $casts = ['monto' => 'decimal:2'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanPago::class, 'plan_id');
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(ConceptoCobro::class, 'concepto_id');
    }
}
