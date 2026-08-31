<?php

namespace App\Http\Requests\Educativo;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodoEvaluativoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ciclo_id'     => ['required', 'exists:ciclo_escolar,id'],
            'plan_id'      => ['required', 'exists:plan_estudios,id'],
            'nombre'       => ['required', 'string', 'max:50'],
            'numero'       => ['required', 'integer', 'min:1', 'max:6'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin'    => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function messages(): array
    {
        return [
            'ciclo_id.required' => 'El ciclo escolar es obligatorio.',
            'plan_id.required'  => 'El plan de estudios es obligatorio.',
            'nombre.required'   => 'El nombre del período es obligatorio.',
            'numero.required'   => 'El número del período es obligatorio.',
            'fecha_fin.after_or_equal' => 'La fecha de cierre debe ser igual o posterior a la de inicio.',
        ];
    }
}
