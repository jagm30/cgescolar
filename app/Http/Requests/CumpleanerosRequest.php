<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CumpleanerosRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso por rol lo controla el middleware de la ruta
        return true;
    }

    public function rules(): array
    {
        return [
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'grupo_id' => ['nullable', 'integer', 'exists:grupo,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'mes.integer' => 'El mes seleccionado no es válido.',
            'mes.between' => 'El mes debe estar entre enero y diciembre.',
            'grupo_id.exists' => 'El grupo seleccionado no existe.',
        ];
    }

    /**
     * Mes a consultar: si no viene en la petición se usa el mes actual;
     * si viene vacío significa "todos los meses" (null).
     */
    public function mes(): ?int
    {
        if (! $this->has('mes')) {
            return now()->month;
        }

        return $this->enteroONulo('mes');
    }

    public function grupoId(): ?int
    {
        return $this->enteroONulo('grupo_id');
    }

    private function enteroONulo(string $campo): ?int
    {
        $valor = $this->validated($campo);

        return $valor === null ? null : (int) $valor;
    }
}
