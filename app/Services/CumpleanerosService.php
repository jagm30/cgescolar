<?php

namespace App\Services;

use App\Models\ContactoFamiliar;
use App\Models\Grupo;
use App\Models\Inscripcion;
use Illuminate\Support\Collection;

/**
 * Reporte de cumpleaños de contactos familiares,
 * agrupado por el grupo que cursa cada alumno en el ciclo.
 */
class CumpleanerosService
{
    /**
     * Grupos (ordenados por nivel → grado → grupo) con los contactos que cumplen años.
     *
     * @return Collection<int, array{grupo: Grupo, contactos: Collection}>
     */
    public function porGrupo(int $cicloId, ?int $mes, ?int $grupoId = null): Collection
    {
        $filtroContactos = fn ($q) => $q->where('alumno_contacto.activo', true)->cumpleanerosDelMes($mes);

        return Inscripcion::query()
            ->with(['grupo.grado.nivel', 'alumno.contactos' => $filtroContactos])
            ->where('ciclo_id', $cicloId)
            ->activa()
            ->when($grupoId, fn ($q) => $q->where('grupo_id', $grupoId))
            ->whereHas('alumno.contactos', $filtroContactos)
            ->get()
            ->groupBy('grupo_id')
            ->map(fn (Collection $inscripciones) => [
                'grupo' => $inscripciones->first()->grupo,
                'contactos' => $this->contactosDelGrupo($inscripciones),
            ])
            ->sortBy(fn (array $fila) => $this->claveOrdenGrupo($fila['grupo']))
            ->values();
    }

    /**
     * Grupos activos del ciclo para el filtro, en el mismo orden que el reporte.
     *
     * @return Collection<int, Grupo>
     */
    public function gruposDelCiclo(int $cicloId): Collection
    {
        return Grupo::query()
            ->with('grado.nivel')
            ->delCiclo($cicloId)
            ->activo()
            ->get()
            ->sortBy(fn (Grupo $grupo) => $this->claveOrdenGrupo($grupo))
            ->values();
    }

    /**
     * Une los contactos de los alumnos del grupo (un contacto con dos hijos en el
     * mismo grupo aparece una sola vez) y los ordena por día de cumpleaños.
     *
     * @return Collection<int, array{contacto: ContactoFamiliar, alumnos: Collection}>
     */
    private function contactosDelGrupo(Collection $inscripciones): Collection
    {
        return $inscripciones
            ->flatMap(fn (Inscripcion $ins) => $ins->alumno->contactos->map(fn (ContactoFamiliar $contacto) => [
                'contacto' => $contacto,
                'alumno' => $ins->alumno,
                'parentesco' => $contacto->pivot->parentesco,
            ]))
            ->groupBy(fn (array $fila) => $fila['contacto']->id)
            ->map(fn (Collection $filas) => [
                'contacto' => $filas->first()['contacto'],
                'alumnos' => $filas->map(fn (array $f) => ['alumno' => $f['alumno'], 'parentesco' => $f['parentesco']])->values(),
            ])
            ->sortBy(fn (array $fila) => $fila['contacto']->fecha_nacimiento->format('md').$fila['contacto']->nombre_completo)
            ->values();
    }

    /** Clave de orden: nivel (orden) → grado (número) → nombre del grupo. */
    private function claveOrdenGrupo(?Grupo $grupo): string
    {
        return sprintf(
            '%03d-%03d-%s',
            $grupo?->grado?->nivel?->orden ?? 999,
            $grupo?->grado?->numero ?? 999,
            $grupo?->nombre ?? ''
        );
    }
}
