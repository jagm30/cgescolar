<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicamentoAutorizado extends Model
{
    protected $table = 'medicamento_autorizado';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'autorizado_por_contacto',
        'nombre_medicamento',
        'dosis',
        'frecuencia',
        'horario',
        'requiere_refrigeracion',
        'instrucciones',
        'vigencia_fin',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'requiere_refrigeracion' => 'boolean',
            'activo'                 => 'boolean',
            'vigencia_fin'           => 'date',
            'creado_at'              => 'datetime',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function contactoAutoriza(): BelongsTo
    {
        return $this->belongsTo(ContactoFamiliar::class, 'autorizado_por_contacto');
    }
}
