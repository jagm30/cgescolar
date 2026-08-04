<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondicionMedica extends Model
{
    protected $table = 'condicion_medica';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'tipo',
        'nombre',
        'descripcion',
        'nivel_riesgo',
        'requiere_accion',
        'accion_requerida',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'requiere_accion' => 'boolean',
            'activo'          => 'boolean',
            'creado_at'       => 'datetime',
        ];
    }

    /** Etiqueta legible del tipo de condición */
    public function tipoEtiqueta(): string
    {
        return match ($this->tipo) {
            'padecimiento'         => 'Padecimiento',
            'alergia_alimento'     => 'Alergia alimentaria',
            'alergia_medicamento'  => 'Alergia a medicamento',
            'alergia_ambiental'    => 'Alergia ambiental',
            'discapacidad'         => 'Discapacidad',
            'neurodivergencia'     => 'Neurodivergencia',
            default                => 'Otro',
        };
    }

    /** Color de badge según nivel de riesgo */
    public function colorRiesgo(): string
    {
        return match ($this->nivel_riesgo) {
            'moderado' => '#f39c12',
            'grave'    => '#e67e22',
            'critico'  => '#dd4b39',
            default    => '#00a65a',
        };
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
