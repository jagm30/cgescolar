<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumnoContacto extends Model
{
    protected $table = 'alumno_contacto';
    public $timestamps = false;

    protected $fillable = [
        'alumno_id',
        'contacto_id',
        'parentesco',
        'tipo',
        'orden',
        'autorizado_recoger',
        'es_responsable_pago',
        'activo',
    ];

    protected $casts = [
        'autorizado_recoger'  => 'boolean',
        'es_responsable_pago' => 'boolean',
        'activo'              => 'boolean',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(ContactoFamiliar::class, 'contacto_id');
    }
}
