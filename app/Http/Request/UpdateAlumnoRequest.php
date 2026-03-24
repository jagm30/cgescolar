<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlumnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->rol, ['administrador', 'recepcion']);
    }

    public function rules(): array
    {
        $alumnoId = $this->route('alumno');

        return [
            'nombre'           => ['sometimes', 'required', 'string', 'max:100'],
            'ap_paterno'       => ['sometimes', 'required', 'string', 'max:100'],
            'ap_materno'       => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today'],
            'curp'             => ['nullable', 'string', 'size:18', 'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2}$/',
                                   Rule::unique('alumno', 'curp')->ignore($alumnoId)],
            'genero'           => ['nullable', 'in:M,F,Otro'],
            'estado'           => ['sometimes', 'required', 'in:activo,baja_temporal,baja_definitiva,egresado'],
            'foto_url'         => ['nullable', 'string', 'max:500'],
            'observaciones'    => ['nullable', 'string', 'max:1000'],
            'fecha_baja'       => ['nullable', 'date', 'required_if:estado,baja_definitiva'],
            'familia_id'       => ['nullable', 'exists:familia,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'curp.unique'               => 'Ya existe otro alumno registrado con esta CURP.',
            'curp.size'                 => 'La CURP debe tener exactamente 18 caracteres.',
            'estado.in'                 => 'El estado debe ser: activo, baja_temporal, baja_definitiva o egresado.',
            'fecha_baja.required_if'    => 'La fecha de baja es obligatoria cuando el estado es baja definitiva.',
        ];
    }
}
