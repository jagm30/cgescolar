<?php

namespace App\Http\Requests\Educativo;

use Illuminate\Foundation\Http\FormRequest;

class StoreCriterioEvaluacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'etiqueta'       => ['required', 'string', 'max:20'],
            'descripcion'    => ['required', 'string', 'max:100'],
            'valor_numerico' => ['required', 'numeric', 'min:0', 'max:100'],
            'orden'          => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'etiqueta.required'       => 'La etiqueta del criterio es obligatoria.',
            'descripcion.required'    => 'La descripción del criterio es obligatoria.',
            'valor_numerico.required' => 'El valor numérico equivalente es obligatorio.',
        ];
    }
}
