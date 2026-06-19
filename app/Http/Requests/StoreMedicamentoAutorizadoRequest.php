<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicamentoAutorizadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->esAdministrador() || auth()->user()->esRecepcion();
    }

    public function rules(): array
    {
        return [
            'autorizado_por_contacto' => ['required', 'exists:contacto_familiar,id'],
            'nombre_medicamento'       => ['required', 'string', 'max:255'],
            'dosis'                    => ['required', 'string', 'max:255'],
            'frecuencia'               => ['required', 'string', 'max:255'],
            'horario'                  => ['nullable', 'string', 'max:255'],
            'requiere_refrigeracion'   => ['nullable', 'boolean'],
            'instrucciones'            => ['nullable', 'string', 'max:1000'],
            'vigencia_fin'             => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'autorizado_por_contacto.required' => 'Selecciona el contacto que autoriza el medicamento.',
            'autorizado_por_contacto.exists'   => 'El contacto seleccionado no existe.',
            'nombre_medicamento.required'       => 'El nombre del medicamento es obligatorio.',
            'dosis.required'                    => 'La dosis es obligatoria.',
            'frecuencia.required'               => 'La frecuencia de administración es obligatoria.',
            'vigencia_fin.after'                => 'La fecha de vigencia debe ser posterior a hoy.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requiere_refrigeracion' => filter_var($this->requiere_refrigeracion, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
