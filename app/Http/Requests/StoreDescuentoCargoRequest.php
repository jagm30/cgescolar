<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDescuentoCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'cargo_id'  => ['required', 'exists:cargo,id'],
            'tipo'      => ['required', 'in:porcentaje,monto_fijo'],
            'valor'     => ['required', 'numeric', 'min:0.01'],
            'motivo'    => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $cargo = \App\Models\Cargo::find($this->cargo_id);
            if (!$cargo) return;

            // No se puede aplicar descuento a un cargo ya pagado o condonado
            if (in_array($cargo->estado, ['pagado', 'condonado'])) {
                $validator->errors()->add('cargo_id', "No se puede aplicar un descuento a un cargo con estado '{$cargo->estado}'.");
            }

            // Validar que el descuento no supere el saldo pendiente
            if ($this->tipo === 'monto_fijo' && $this->valor > $cargo->saldo_pendiente_base) {
                $validator->errors()->add('valor', 'El monto del descuento no puede ser mayor al saldo pendiente del cargo ($' . number_format($cargo->saldo_pendiente_base, 2) . ').');
            }

            if ($this->tipo === 'porcentaje' && $this->valor > 100) {
                $validator->errors()->add('valor', 'El porcentaje de descuento no puede ser mayor a 100%.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'cargo_id.required' => 'Debe seleccionar el cargo al que se aplicará el descuento.',
            'tipo.required'     => 'Debe seleccionar el tipo de descuento.',
            'tipo.in'           => 'El tipo debe ser: porcentaje o monto_fijo.',
            'valor.required'    => 'El valor del descuento es obligatorio.',
            'valor.min'         => 'El valor debe ser mayor a cero.',
            'motivo.required'   => 'El motivo del descuento es obligatorio.',
            'motivo.min'        => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}
