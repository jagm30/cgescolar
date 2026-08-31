<?php

namespace App\Http\Requests\Educativo;

use App\Enums\TipoPeriodo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanEstudiosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nivel_id'     => ['required', 'exists:nivel_escolar,id'],
            'escala_id'    => ['required', 'exists:escala_evaluacion,id'],
            'ciclo_id'     => ['nullable', 'exists:ciclo_escolar,id'],
            'nombre'       => ['required', 'string', 'max:150'],
            'tipo_periodo' => ['required', Rule::enum(TipoPeriodo::class)],
            'activo'       => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nivel_id.required'     => 'El nivel académico es obligatorio.',
            'nivel_id.exists'       => 'El nivel seleccionado no existe.',
            'escala_id.required'    => 'La escala de evaluación es obligatoria.',
            'escala_id.exists'      => 'La escala seleccionada no existe.',
            'nombre.required'       => 'El nombre del plan es obligatorio.',
            'tipo_periodo.required' => 'La periodicidad de evaluación es obligatoria.',
        ];
    }
}
