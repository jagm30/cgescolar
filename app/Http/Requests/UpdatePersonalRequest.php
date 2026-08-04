<?php

namespace App\Http\Requests;

use App\Enums\TipoPersonal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdatePersonalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        $id = $this->route('personal')?->id ?? $this->route('personal');

        return [
            'numero_empleado' => ['required', 'string', 'max:20', Rule::unique('personal', 'numero_empleado')->ignore($id)],
            'nombre'          => ['required', 'string', 'max:100'],
            'ap_paterno'      => ['required', 'string', 'max:100'],
            'ap_materno'      => ['nullable', 'string', 'max:100'],
            'telefono'        => ['required', 'string', 'max:20'],
            'email'           => ['required', 'email', 'max:150', Rule::unique('personal', 'email')->ignore($id)],
            'rfc'             => ['nullable', 'string', 'size:13'],
            'tipo'            => ['required', new Enum(TipoPersonal::class)],
            'domicilio'       => ['required', 'string', 'max:500'],
            'foto'            => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_empleado.required' => 'El número de empleado es obligatorio.',
            'numero_empleado.max'      => 'El número de empleado no puede exceder 20 caracteres.',
            'numero_empleado.unique'   => 'Este número de empleado ya está en otro empleado.',
            'nombre.required'          => 'El nombre es obligatorio.',
            'ap_paterno.required'      => 'El apellido paterno es obligatorio.',
            'telefono.required'        => 'El teléfono es obligatorio.',
            'email.required'           => 'El correo electrónico es obligatorio.',
            'email.email'              => 'El correo electrónico no tiene un formato válido.',
            'email.unique'             => 'Este correo ya está registrado en otro empleado.',
            'rfc.size'                 => 'El RFC debe tener exactamente 13 caracteres.',
            'tipo.required'            => 'El tipo de personal es obligatorio.',
            'domicilio.required'       => 'El domicilio es obligatorio.',
            'foto.image'               => 'El archivo debe ser una imagen.',
            'foto.mimes'               => 'La imagen debe ser JPEG, PNG o WebP.',
            'foto.max'                 => 'La imagen no debe superar 2 MB.',
        ];
    }
}
