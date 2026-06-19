<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFichaMedicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->esAdministrador() || auth()->user()->esRecepcion();
    }

    public function rules(): array
    {
        return [
            'tipo_sangre'             => ['nullable', 'string', 'max:5'],
            'peso_kg'                 => ['nullable', 'numeric', 'min:1', 'max:300'],
            'talla_cm'                => ['nullable', 'numeric', 'min:30', 'max:250'],
            'medico_nombre'           => ['nullable', 'string', 'max:255'],
            'medico_telefono'         => ['nullable', 'string', 'max:20'],
            'hospital_preferente'     => ['nullable', 'string', 'max:255'],
            'discapacidad'            => ['nullable', 'string', 'max:1000'],
            'observaciones_generales' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'peso_kg.min'    => 'El peso debe ser mayor a 1 kg.',
            'peso_kg.max'    => 'El peso no puede superar 300 kg.',
            'talla_cm.min'   => 'La talla debe ser mayor a 30 cm.',
            'talla_cm.max'   => 'La talla no puede superar 250 cm.',
        ];
    }
}
