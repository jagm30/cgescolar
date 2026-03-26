<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        $usuarioId = $this->route('usuario');

        return [
            'nombre'   => ['sometimes', 'required', 'string', 'max:200'],
            'email'    => ['sometimes', 'required', 'email', 'max:200',
                           Rule::unique('usuario', 'email')->ignore($usuarioId)],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'rol'      => ['sometimes', 'required', 'in:administrador,caja,recepcion,padre'],
            'activo'   => ['boolean'],
            'ciclo_seleccionado_id' => ['nullable', 'exists:ciclo_escolar,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'           => 'Ya existe otro usuario con este correo electrónico.',
            'password.confirmed'     => 'La confirmación de contraseña no coincide.',
            'rol.in'                 => 'El rol debe ser: administrador, caja, recepcion o padre.',
        ];
    }
}
