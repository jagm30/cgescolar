<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospecto extends Model
{
    /** Nota que store() crea automáticamente al registrar el prospecto — no cuenta como seguimiento manual. */
    public const NOTA_REGISTRO_INICIAL = 'Registro inicial del prospecto.';

    protected $table = 'prospecto';

    public $timestamps = false;

    protected $appends = [
        'nombre_completo',
    ];

    protected $fillable = [
        'ciclo_id',
        'nombre',
        'ap_paterno',
        'ap_materno',
        'fecha_nacimiento',
        'nivel_interes_id',
        'grado_interes_id',
        'contacto_nombre',
        'contacto_telefono',
        'contacto_email',
        'canal_contacto',
        'etapa',
        'responsable_id',
        'fecha_primer_contacto',
        'motivo_no_concrecion',
        'alumno_id',
        'familia_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_primer_contacto' => 'date',
        'creado_at' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────

    public function scopeEnProceso($query)
    {
        return $query->whereNotIn('etapa', ['inscrito', 'no_concretado', 'no_aceptado']);
    }

    public function scopePorEtapa($query, string $etapa)
    {
        return $query->where('etapa', $etapa);
    }

    // ── Helpers ──────────────────────────────────────────

    /** Filtra seguimientos que no sean la nota automática de registro inicial. */
    public static function filtroSeguimientoManual(Builder $query): Builder
    {
        return $query->where('tipo_accion', '!=', 'nota')
            ->orWhere('notas', '!=', self::NOTA_REGISTRO_INICIAL);
    }

    /** True si el prospecto tiene algún seguimiento además de la nota automática de registro. */
    public function tieneSeguimientosManuales(): bool
    {
        return $this->seguimientos()
            ->where(fn (Builder $q) => self::filtroSeguimientoManual($q))
            ->exists();
    }

    public function estaInscrito(): bool
    {
        return $this->etapa === 'inscrito' && ! is_null($this->alumno_id);
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim(preg_replace('/\s+/', ' ', "{$this->nombre} {$this->ap_paterno} {$this->ap_materno}"));
    }

    /**
     * Convierte el prospecto en alumno formal.
     * Actualiza etapa y vincula el alumno creado.
     */
    public function convertirAAlumno(Alumno $alumno): void
    {
        $this->alumno_id = $alumno->id;
        $this->etapa = 'inscrito';
        $this->save();
    }

    // ── Relaciones ───────────────────────────────────────

    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_id');
    }

    public function nivelInteres(): BelongsTo
    {
        return $this->belongsTo(NivelEscolar::class, 'nivel_interes_id');
    }

    public function gradoInteres(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_interes_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsable_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(Familia::class, 'familia_id');
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoAdmision::class, 'prospecto_id')
            ->orderByDesc('fecha')
            ->orderByDesc('id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocAdmision::class, 'prospecto_id');
    }

    public function documentosPendientes(): HasMany
    {
        return $this->hasMany(DocAdmision::class, 'prospecto_id')
            ->where('estado', 'pendiente');
    }
}
