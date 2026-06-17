<?php

namespace App\Http\Requests;

use App\Models\Cargo;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreAsignacionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'exists:plan_pago,id'],
            'origen' => ['required', 'in:individual,grupo,nivel'],
            'alumno_id' => ['required_if:origen,individual', 'nullable', 'exists:alumno,id'],
            'grupo_id' => ['required_if:origen,grupo', 'nullable', 'exists:grupo,id'],
            'nivel_id' => ['required_if:origen,nivel', 'nullable', 'exists:nivel_escolar,id'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'conceptos' => ['required', 'array', 'min:1'],
            'conceptos.*' => ['exists:plan_pago_concepto,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $origen = $this->input('origen');
            $alumnoId = $this->input('alumno_id');
            $grupoId = $this->input('grupo_id');
            $nivelId = $this->input('nivel_id');
            $plan = PlanPago::with('nivel', 'ciclo')->find($this->input('plan_id'));

            if (! $plan) {
                return;
            }

            if ($origen === 'individual' && ($grupoId || $nivelId)) {
                $validator->errors()->add('origen', 'Para asignación individual solo debe especificar alumno_id.');
            }

            if ($origen === 'grupo' && ($alumnoId || $nivelId)) {
                $validator->errors()->add('origen', 'Para asignación por grupo solo debe especificar grupo_id.');
            }

            if ($origen === 'nivel' && ($alumnoId || $grupoId)) {
                $validator->errors()->add('origen', 'Para asignación por nivel solo debe especificar nivel_id.');
            }

            if ($origen === 'individual') {
                // Inscripción en el ciclo exacto del plan (caso normal o anticipada con grupo).
                $inscripcionEnCiclo = Inscripcion::with('grupo.grado')
                    ->where('alumno_id', $alumnoId)
                    ->where('ciclo_id', $plan->ciclo_id)
                    ->where('activo', true)
                    ->first();

                $inscripcion = $inscripcionEnCiclo;

                // Ciclo en configuración: reinscripción anticipada.
                // Si no existe inscripción en el nuevo ciclo (o existe sin grupo asignado),
                // aceptar al alumno si tiene inscripción activa en el ciclo vigente.
                // En ambos casos se omite la validación de nivel porque el alumno puede
                // estar cambiando de nivel (ej. 6° Primaria → 1° Secundaria).
                if (! $inscripcion && $plan->ciclo?->estado === 'configuracion') {
                    $inscripcion = Inscripcion::with('grupo.grado')
                        ->where('alumno_id', $alumnoId)
                        ->where('activo', true)
                        ->whereHas('ciclo', fn ($q) => $q->where('estado', 'activo'))
                        ->first();
                }

                if (! $inscripcion) {
                    $validator->errors()->add('alumno_id', 'El alumno no tiene inscripción activa en el ciclo del plan.');

                    return;
                }
            }

            if ($origen === 'grupo') {
                $grupo = Grupo::with('grado')->find($grupoId);

                if ((int) $grupo?->grado?->nivel_id !== (int) $plan->nivel_id) {
                    $validator->errors()->add('plan_id', 'El plan seleccionado no corresponde al nivel del grupo.');
                }
            }

            if ($origen === 'nivel') {
                if ((int) $nivelId !== (int) $plan->nivel_id) {
                    $validator->errors()->add('plan_id', 'El plan seleccionado no corresponde al nivel elegido.');
                }
            }

            // Calcular los períodos que generaría esta nueva asignación
            $fi = $this->input('fecha_inicio') ?? $plan->fecha_inicio?->format('Y-m-d');
            $ff = $this->input('fecha_fin')    ?? $plan->fecha_fin?->format('Y-m-d');

            if ($fi && $ff) {
                $nuevosPeriodos = $this->calcularPeriodos($fi, $ff, $plan->periodicidad);

                // Bloquear solo si alguno de los nuevos períodos ya tiene un cargo cobrado
                // (estado distinto a pendiente o con abono) para el mismo alcance y plan
                $conceptoIds = $plan->planPagoConceptos->pluck('concepto_id');

                $conflicto = Cargo::query()
                    ->whereIn('periodo', $nuevosPeriodos)
                    ->whereIn('concepto_id', $conceptoIds)
                    ->whereIn('estado', ['parcial', 'pagado'])
                    ->whereHas('inscripcion', function ($q) use ($plan, $origen, $alumnoId, $grupoId, $nivelId) {
                        $q->where('ciclo_id', $plan->ciclo_id)->where('activo', true);
                        if ($origen === 'individual') {
                            $q->where('alumno_id', $alumnoId);
                        } elseif ($origen === 'grupo') {
                            $q->where('grupo_id', $grupoId);
                        } elseif ($origen === 'nivel') {
                            $q->whereHas('grupo.grado', fn ($g) => $g->where('nivel_id', $nivelId));
                        }
                    })
                    ->exists();

                if ($conflicto) {
                    $validator->errors()->add('plan_id', 'Uno o más períodos del rango seleccionado ya tienen cargos cobrados. Ajusta las fechas para cubrir solo los meses pendientes.');
                }
            }

            // Validar que los conceptos seleccionados pertenezcan al plan
            $planConceptosIds = $plan->planPagoConceptos->pluck('id')->toArray();
            $conceptosSeleccionados = $this->input('conceptos', []);

            if (! empty($conceptosSeleccionados)) {
                $invalidos = array_diff($conceptosSeleccionados, $planConceptosIds);
                if (! empty($invalidos)) {
                    $validator->errors()->add('conceptos', 'Algunos conceptos seleccionados no pertenecen al plan elegido.');
                }
            }
        });
    }

    /** Calcula los identificadores de período (Y-m) entre dos fechas según la periodicidad. */
    private function calcularPeriodos(string $fechaInicio, string $fechaFin, string $periodicidad): array
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin    = Carbon::parse($fechaFin);

        if ($periodicidad === 'unico') {
            return [$inicio->format('Y-m')];
        }

        $intervalo = match ($periodicidad) {
            'mensual'   => '1 month',
            'bimestral' => '2 months',
            'semestral' => '6 months',
            'anual'     => '1 year',
            default     => '1 month',
        };

        $periodos = [];
        $actual   = $inicio->copy();

        while ($actual->lte($fin)) {
            $periodos[] = $actual->format('Y-m');
            $actual->add($intervalo);
        }

        return $periodos;
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'Debe seleccionar el plan de pago.',
            'origen.required' => 'Debe seleccionar el origen de la asignación.',
            'origen.in' => 'El origen debe ser: individual, grupo o nivel.',
            'alumno_id.required_if' => 'Debe seleccionar el alumno para asignación individual.',
            'grupo_id.required_if' => 'Debe seleccionar el grupo para asignación por grupo.',
            'nivel_id.required_if' => 'Debe seleccionar el nivel para asignación por nivel.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
