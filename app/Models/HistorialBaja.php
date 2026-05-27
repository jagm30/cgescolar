<?php

namespace App\Models;

use App\Enums\MotivoBaja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialBaja extends Model
{
    protected $table = 'historial_bajas';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'ciclo_id',
        'registrado_por',
        'tipo',
        'motivo_categoria',
        'motivo_detalle',
        'fecha_baja',
        'fecha_reactivacion',
    ];

    protected function casts(): array
    {
        return [
            'motivo_categoria'  => MotivoBaja::class,
            'fecha_baja'        => 'date',
            'fecha_reactivacion' => 'date',
            'creado_at'         => 'datetime',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'registrado_por');
    }

    /** Devuelve el tipo como etiqueta legible */
    public function tipoEtiqueta(): string
    {
        return match ($this->tipo) {
            'baja_temporal'   => 'Temporal',
            'baja_definitiva' => 'Definitiva',
            default           => ucfirst($this->tipo),
        };
    }
}
