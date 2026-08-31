<?php

namespace App\Http\Requests\Educativo;

use App\Enums\TipoEscala;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEscalaEvaluacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'            => ['required', 'string', 'max:100'],
            'tipo'              => ['required', Rule::enum(TipoEscala::class)],
            // Solo requeridos cuando la escala es numérica
            'valor_minimo'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valor_maximo'      => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:valor_minimo'],
            'valor_aprobatorio' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:valor_minimo', 'lte:valor_maximo'],
            'activa'            => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'            => 'El nombre de la escala es obligatorio.',
            'tipo.required'              => 'El tipo de escala es obligatorio.',
            'valor_maximo.gte'           => 'El valor máximo debe ser mayor o igual al mínimo.',
            'valor_aprobatorio.gte'      => 'El valor aprobatorio debe ser mayor o igual al mínimo.',
            'valor_aprobatorio.lte'      => 'El valor aprobatorio debe ser menor o igual al máximo.',
        ];
    }
}
