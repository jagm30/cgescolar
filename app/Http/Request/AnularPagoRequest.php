<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnularPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el administrador puede anular pagos
        return auth()->user()->rol === 'administrador';
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pago = \App\Models\Pago::find($this->route('pago'));

            if (!$pago) return;

            if ($pago->estado === 'anulado') {
                $validator->errors()->add('pago', 'Este pago ya fue anulado anteriormente.');
            }

            // No se puede anular un pago si ya tiene CFDI timbrado
            $tieneCfdi = $pago->cfdis()->where('estado', 'vigente')->exists();
            if ($tieneCfdi) {
                $validator->errors()->add('pago', 'No se puede anular un pago que ya tiene factura electrónica timbrada. Primero cancele el CFDI ante el SAT.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'El motivo de anulación es obligatorio.',
            'motivo.min'      => 'El motivo debe tener al menos 10 caracteres.',
        ];
    }
}
