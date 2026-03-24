<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocAdmision extends Model
{
    protected $table = 'doc_admision';
    public $timestamps = false;

    protected $fillable = [
        'prospecto_id',
        'tipo_documento',
        'estado',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEntregado($query)
    {
        return $query->where('estado', 'entregado');
    }

    // ── Relaciones ───────────────────────────────────────

    public function prospecto(): BelongsTo
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }
}
