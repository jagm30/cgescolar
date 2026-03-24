<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RazonSocialContacto extends Model
{
    protected $table = 'razon_social_contacto';
    public $timestamps = false;

    protected $fillable = [
        'contacto_id',
        'rfc',
        'razon_social',
        'regimen_fiscal',
        'domicilio_fiscal',
        'uso_cfdi_default',
        'es_principal',
        'registrado_por',
        'activo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activo'       => 'boolean',
        'creado_at'    => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    // ── Relaciones ───────────────────────────────────────

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(ContactoFamiliar::class, 'contacto_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    public function cfdis(): HasMany
    {
        return $this->hasMany(Cfdi::class, 'razon_social_id');
    }
}
