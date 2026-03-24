<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoAlumno extends Model
{
    protected $table = 'documento_alumno';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'tipo_documento',
        'estado',
        'archivo_url',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
