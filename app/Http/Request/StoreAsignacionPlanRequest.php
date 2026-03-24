<?php

namespace App\Http\Requests;

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
            'plan_id'      => ['required', 'exists:plan_pago,id'],
            'origen'       => ['required', 'in:individual,grupo,nivel'],
            'alumno_id'    => ['required_if:origen,individual', 'nullable', 'exists:alumno,id'],
            'grupo_id'     => ['required_if:origen,grupo',      'nullable', 'exists:grupo,id'],
            'nivel_id'     => ['required_if:origen,nivel',      'nullable', 'exists:nivel_escolar,id'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que solo uno de los tres campos tenga valor según origen
            $origen    = $this->origen;
            $alumnoId  = $this->alumno_id;
            $grupoId   = $this->grupo_id;
            $nivelId   = $this->nivel_id;

            if ($origen === 'individual' && ($grupoId || $nivelId)) {
                $validator->errors()->add('origen', 'Para asignación individual solo debe especificar alumno_id.');
            }
            if ($origen === 'grupo' && ($alumnoId || $nivelId)) {
                $validator->errors()->add('origen', 'Para asignación por grupo solo debe especificar grupo_id.');
            }
            if ($origen === 'nivel' && ($alumnoId || $grupoId)) {
                $validator->errors()->add('origen', 'Para asignación por nivel solo debe especificar nivel_id.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'plan_id.required'              => 'Debe seleccionar el plan de pago.',
            'origen.required'               => 'Debe seleccionar el origen de la asignación.',
            'origen.in'                     => 'El origen debe ser: individual, grupo o nivel.',
            'alumno_id.required_if'         => 'Debe seleccionar el alumno para asignación individual.',
            'grupo_id.required_if'          => 'Debe seleccionar el grupo para asignación por grupo.',
            'nivel_id.required_if'          => 'Debe seleccionar el nivel para asignación por nivel.',
            'fecha_fin.after_or_equal'      => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
