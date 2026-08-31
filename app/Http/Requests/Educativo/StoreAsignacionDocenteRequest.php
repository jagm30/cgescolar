<?php

namespace App\Http\Requests\Educativo;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignacionDocenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'docente_id' => ['required', 'exists:personal,id'],
            'grupo_id'   => ['required', 'exists:grupo,id'],
            'ciclo_id'   => ['required', 'exists:ciclo_escolar,id'],
            'materia_id' => ['nullable', 'exists:materia,id', 'required_without:campo_id'],
            'campo_id'   => ['nullable', 'exists:campo_formativo,id', 'required_without:materia_id'],
            'activa'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'docente_id.required'          => 'El docente es obligatorio.',
            'grupo_id.required'            => 'El grupo es obligatorio.',
            'ciclo_id.required'            => 'El ciclo escolar es obligatorio.',
            'materia_id.required_without'  => 'Debes seleccionar una materia o un campo formativo.',
            'campo_id.required_without'    => 'Debes seleccionar una materia o un campo formativo.',
        ];
    }

    /**
     * Garantiza que solo uno de materia_id / campo_id esté presente.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($this->filled('materia_id') && $this->filled('campo_id')) {
                $v->errors()->add('materia_id', 'No puedes asignar materia y campo formativo al mismo tiempo.');
            }
        });
    }
}
