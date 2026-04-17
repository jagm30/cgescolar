<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRazonSocialContactoRequest;
use App\Models\ContactoFamiliar;
use App\Models\RazonSocialContacto;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

class RazonSocialController extends Controller
{
    use RespondsWithJson;

    /**
     * POST /familias/razon-social
     * Registra una nueva razón social (RFC) para un contacto familiar.
     */
    public function store(StoreRazonSocialContactoRequest $request)
    {
        $data = $request->validated();
        $data['rfc']             = strtoupper($data['rfc']);
        $data['registrado_por']  = auth()->id();
        $data['es_principal']    = $request->boolean('es_principal', false);

        // Si se marca como principal, quitar el flag de las demás del mismo contacto
        if ($data['es_principal']) {
            RazonSocialContacto::where('contacto_id', $data['contacto_id'])
                ->update(['es_principal' => false]);
        }

        $rs = RazonSocialContacto::create($data);

        $familiaId = ContactoFamiliar::where('id', $data['contacto_id'])
            ->value('familia_id');

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams:   [$familiaId],
            jsonData:      ['razon_social' => $rs],
            mensaje:       "RFC {$rs->rfc} registrado correctamente.",
            jsonStatus:    201
        );
    }

    /**
     * PUT /familias/razon-social/{id}
     * Actualiza los datos de una razón social existente (no se puede cambiar el RFC).
     */
    public function update(Request $request, int $id)
    {
        $this->soloAdminCaja();

        $rs = RazonSocialContacto::with('contacto')->findOrFail($id);

        $data = $request->validate([
            'razon_social'     => ['required', 'string', 'max:300'],
            'regimen_fiscal'   => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal'     => ['boolean'],
        ], [
            'domicilio_fiscal.size'  => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex' => 'El código postal debe contener solo números.',
        ]);

        if (!empty($data['es_principal'])) {
            RazonSocialContacto::where('contacto_id', $rs->contacto_id)
                ->where('id', '!=', $id)
                ->update(['es_principal' => false]);
        }

        $rs->update($data);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams:   [$rs->contacto->familia_id],
            jsonData:      ['razon_social' => $rs->fresh()],
            mensaje:       "RFC {$rs->rfc} actualizado correctamente."
        );
    }

    /**
     * DELETE /familias/razon-social/{id}
     * Desactiva (borrado lógico) una razón social.
     */
    public function destroy(int $id)
    {
        $this->soloAdminCaja();

        $rs = RazonSocialContacto::with('contacto')->findOrFail($id);
        $familiaId = $rs->contacto->familia_id;
        $rfc = $rs->rfc;

        $rs->update(['activo' => false]);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams:   [$familiaId],
            jsonData:      [],
            mensaje:       "RFC {$rfc} desactivado."
        );
    }

    /**
     * POST /familias/razon-social/{id}/principal
     * Marca una razón social como la principal del contacto.
     */
    public function setPrincipal(int $id)
    {
        $this->soloAdminCaja();

        $rs = RazonSocialContacto::with('contacto')->findOrFail($id);

        RazonSocialContacto::where('contacto_id', $rs->contacto_id)
            ->update(['es_principal' => false]);

        $rs->update(['es_principal' => true]);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams:   [$rs->contacto->familia_id],
            jsonData:      ['razon_social' => $rs->fresh()],
            mensaje:       "RFC {$rs->rfc} marcado como principal."
        );
    }

    // ── Helper privado ───────────────────────────────────

    private function soloAdminCaja(): void
    {
        if (!in_array(auth()->user()->rol, ['administrador', 'caja'])) {
            abort(403, 'Solo administrador o caja pueden gestionar datos de facturación.');
        }
    }
}
