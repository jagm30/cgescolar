<?php

namespace App\Http\Requests\Educativo;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampoFormativoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'         => ['required', 'string', 'max:150'],
            'max_caracteres' => ['required', 'integer', 'min:50', 'max:2000'],
            'orden'          => ['integer', 'min:0'],
            'activo'         => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'         => 'El nombre del campo formativo es obligatorio.',
            'max_caracteres.required' => 'El límite de caracteres es obligatorio.',
            'max_caracteres.min'      => 'El límite mínimo es de 50 caracteres.',
            'max_caracteres.max'      => 'El límite máximo es de 2000 caracteres.',
        ];
    }
}
