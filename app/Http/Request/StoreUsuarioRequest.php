<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'nombre'       => ['required', 'string', 'max:200'],
            'email'        => ['required', 'email', 'max:200', 'unique:usuario,email'],
            'password'     => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'rol'          => ['required', 'in:administrador,caja,recepcion,padre'],
            'activo'       => ['boolean'],
            // Solo si es padre — debe vincularse a un contacto existente
            'contacto_id'  => ['required_if:rol,padre', 'nullable', 'exists:contacto_familiar,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->rol === 'padre' && $this->contacto_id) {
                $contacto = \App\Models\ContactoFamiliar::find($this->contacto_id);

                // Verificar que el contacto tenga acceso_portal habilitado
                if ($contacto && !$contacto->tiene_acceso_portal) {
                    $validator->errors()->add('contacto_id', 'El contacto seleccionado no tiene habilitado el acceso al portal. Active el acceso desde el perfil del contacto.');
                }

                // Verificar que el contacto no tenga ya un usuario
                if ($contacto && $contacto->usuario_id) {
                    $validator->errors()->add('contacto_id', 'Este contacto ya tiene un usuario asignado.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nombre.required'       => 'El nombre del usuario es obligatorio.',
            'email.required'        => 'El correo electrónico es obligatorio.',
            'email.unique'          => 'Ya existe un usuario registrado con este correo electrónico.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.confirmed'    => 'La confirmación de contraseña no coincide.',
            'rol.required'          => 'Debe seleccionar el rol del usuario.',
            'rol.in'                => 'El rol debe ser: administrador, caja, recepcion o padre.',
            'contacto_id.required_if' => 'Para usuarios con rol padre debe seleccionar el contacto familiar.',
        ];
    }
}
