<?php

namespace App\Http\Requests;

use App\Models\AsignacionPlan;
use App\Models\Grupo;
use App\Models\Inscripcion;
use App\Models\PlanPago;
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
            $plan = PlanPago::with('nivel')->find($this->input('plan_id'));

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

            $query = AsignacionPlan::query()
                ->where('origen', $origen)
                ->whereHas('plan', fn ($query) => $query->where('ciclo_id', $plan->ciclo_id));

            if ($origen === 'individual') {
                $inscripcion = Inscripcion::with('grupo.grado')
                    ->where('alumno_id', $alumnoId)
                    ->where('ciclo_id', $plan->ciclo_id)
                    ->where('activo', true)
                    ->first();

                if (! $inscripcion) {
                    $validator->errors()->add('alumno_id', 'El alumno no tiene inscripción activa en el ciclo del plan.');

                    return;
                }

                if ((int) $inscripcion->grupo?->grado?->nivel_id !== (int) $plan->nivel_id) {
                    $validator->errors()->add('plan_id', 'El plan seleccionado no corresponde al nivel del alumno.');
                }

                $query->where('alumno_id', $alumnoId);
            }

            if ($origen === 'grupo') {
                $grupo = Grupo::with('grado')->find($grupoId);

                if ((int) $grupo?->grado?->nivel_id !== (int) $plan->nivel_id) {
                    $validator->errors()->add('plan_id', 'El plan seleccionado no corresponde al nivel del grupo.');
                }

                $query->where('grupo_id', $grupoId);
            }

            if ($origen === 'nivel') {
                if ((int) $nivelId !== (int) $plan->nivel_id) {
                    $validator->errors()->add('plan_id', 'El plan seleccionado no corresponde al nivel elegido.');
                }

                $query->where('nivel_id', $nivelId);
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
