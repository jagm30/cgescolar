<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    protected $table = 'grado';
    public $timestamps = false;

    protected $fillable = [
        'nivel_id',
        'nombre',
        'numero',
    ];

    // ── Relaciones ───────────────────────────────────────

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscolar::class, 'nivel_id');
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'grado_id');
    }
}
