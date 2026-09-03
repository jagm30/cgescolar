<?php

namespace App\Http\Traits;

use App\Models\Pago;

trait ValidaFacturabilidadConcepto
{
    /**
     * Devuelve true si el pago contiene al menos un concepto con facturable = false
     * en concepto_cobro. Requiere que detalles->cargo->concepto esté eager-loaded.
     */
    private function tieneConceptosNoFacturables(Pago $pago): bool
    {
        return $pago->detalles->contains(
            fn ($d) => $d->cargo?->concepto && ! $d->cargo->concepto->facturable
        );
    }
}
