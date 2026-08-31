<?php

namespace App\Http\Requests\Educativo;

use App\Enums\TipoMateria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMateriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'          => ['required', 'string', 'max:150'],
            'clave_sep'       => ['nullable', 'string', 'max:30'],
            'tipo'            => ['required', Rule::enum(TipoMateria::class)],
            'horas_semanales' => ['integer', 'min:0', 'max:40'],
            'orden'           => ['integer', 'min:0'],
            'activa'          => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la materia es obligatorio.',
            'tipo.required'   => 'El tipo de materia (SEP / Institucional) es obligatorio.',
        ];
    }
}
