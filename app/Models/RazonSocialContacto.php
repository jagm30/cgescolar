<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RazonSocialContacto extends Model
{
    protected $table = 'razon_social_contacto';

    public $timestamps = false;

    /**
     * Regímenes SAT que tributan sobre ingreso bruto sin admitir deducciones
     * personales. El SAT rechaza cualquier Uso CFDI de deducción personal
     * (D01–D10) para estos regímenes con error CFDI40161/CFDI40154.
     */
    public const REGIMENES_SIN_DEDUCCIONES_PERSONALES = ['626'];

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
        'factura_uid',
        'constancia_path',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activo' => 'boolean',
        'creado_at' => 'datetime',
    ];

    /**
     * Indica si el Uso CFDI es compatible con el régimen fiscal indicado.
     * Los Usos con prefijo "D" (D01–D10) son deducciones personales, que
     * los regímenes de REGIMENES_SIN_DEDUCCIONES_PERSONALES no admiten.
     */
    public static function usoCfdiCompatibleConRegimen(string $regimenFiscal, string $usoCfdi): bool
    {
        $esDeduccionPersonal = str_starts_with(strtoupper($usoCfdi), 'D');

        return ! ($esDeduccionPersonal && in_array($regimenFiscal, self::REGIMENES_SIN_DEDUCCIONES_PERSONALES, true));
    }

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
