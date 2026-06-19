<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaMedica extends Model
{
    protected $table = 'ficha_medica';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'tipo_sangre',
        'peso_kg',
        'talla_cm',
        'medico_nombre',
        'medico_telefono',
        'hospital_preferente',
        'discapacidad',
        'observaciones_generales',
        'actualizado_por',
        'actualizado_at',
    ];

    protected function casts(): array
    {
        return [
            'peso_kg'        => 'decimal:2',
            'talla_cm'       => 'decimal:2',
            'actualizado_at' => 'datetime',
            'creado_at'      => 'datetime',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function actualizadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por');
    }
}
