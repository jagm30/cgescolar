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
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'ap_paterno' => ['sometimes', 'required', 'string', 'max:100'],
            'ap_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['sometimes', 'required', 'date', 'before:today'],
            'curp' => ['nullable', 'string', 'size:18', 'regex:/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2}$/',
                Rule::unique('alumno', 'curp')->ignore($alumnoId)],
            'genero' => ['nullable', 'in:M,F,Otro'],
            'estado' => ['sometimes', 'required', 'in:activo,baja_temporal,baja_definitiva,egresado'],
            'foto_url' => ['nullable', 'string', 'max:500'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            // Domicilio
            'calle' => ['nullable', 'string', 'max:200'],
            'colonia' => ['nullable', 'string', 'max:200'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'estado_residencia' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'fecha_inscripcion' => ['sometimes', 'required', 'date'],
            'fecha_baja' => ['nullable', 'date', 'required_if:estado,baja_definitiva'],
            'familia_id' => ['nullable', 'exists:familia,id'],
            'ciclo_id' => ['nullable', 'exists:ciclo_escolar,id'],
            'nivel_id' => ['nullable', 'exists:nivel_escolar,id'],
            'grupo_id' => ['nullable', 'exists:grupo,id'],
            'fotos_contacto' => ['nullable', 'array'],
            'fotos_contacto.*' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'curp.unique' => 'Ya existe otro alumno registrado con esta CURP.',
            'curp.size' => 'La CURP debe tener exactamente 18 caracteres.',
            'estado.in' => 'El estado debe ser: activo, baja_temporal, baja_definitiva o egresado.',
            'fecha_baja.required_if' => 'La fecha de baja es obligatoria cuando el estado es baja definitiva.',
        ];
    }
}
