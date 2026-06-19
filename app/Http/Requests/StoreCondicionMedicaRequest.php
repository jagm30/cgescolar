<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCondicionMedicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->esAdministrador() || auth()->user()->esRecepcion();
    }

    public function rules(): array
    {
        return [
            'tipo'             => ['required', 'string', 'in:padecimiento,alergia_alimento,alergia_medicamento,alergia_ambiental,discapacidad,otro'],
            'nombre'           => ['required', 'string', 'max:255'],
            'descripcion'      => ['nullable', 'string', 'max:1000'],
            'nivel_riesgo'     => ['required', 'string', 'in:leve,moderado,grave,critico'],
            'requiere_accion'  => ['nullable', 'boolean'],
            'accion_requerida' => ['nullable', 'string', 'max:1000', 'required_if:requiere_accion,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required'                    => 'El tipo de condición es obligatorio.',
            'tipo.in'                          => 'Selecciona un tipo de condición válido.',
            'nombre.required'                  => 'El nombre de la condición es obligatorio.',
            'nivel_riesgo.required'            => 'El nivel de riesgo es obligatorio.',
            'nivel_riesgo.in'                  => 'Selecciona un nivel de riesgo válido.',
            'accion_requerida.required_if'     => 'Describe la acción a tomar si marcas que requiere intervención.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requiere_accion' => filter_var($this->requiere_accion, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
