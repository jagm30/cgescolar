<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRazonSocialContactoRequest;
use App\Models\Auditoria;
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
        $data['rfc'] = strtoupper($data['rfc']);
        $data['registrado_por'] = auth()->id();
        $data['es_principal'] = $request->boolean('es_principal', false);

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
            routeParams: [$familiaId],
            jsonData: ['razon_social' => $rs],
            mensaje: "RFC {$rs->rfc} registrado correctamente.",
            jsonStatus: 201
        );
    }

    /**
     * PUT /familias/razon-social/{id}
     * Actualiza los datos de una razón social existente, incluido el RFC
     * (para corregir capturas erróneas).
     */
    public function update(Request $request, int $id)
    {
        $this->soloAdminCaja();

        $rs = RazonSocialContacto::with('contacto')->findOrFail($id);

        $data = $request->validate([
            'rfc' => ['required', 'string', 'between:12,13', 'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/i'],
            'razon_social' => ['required', 'string', 'max:300'],
            'regimen_fiscal' => ['required', 'string', 'max:10'],
            'domicilio_fiscal' => ['required', 'string', 'size:5', 'regex:/^[0-9]{5}$/'],
            'uso_cfdi_default' => ['required', 'string', 'max:10'],
            'es_principal' => ['boolean'],
        ], [
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El formato del RFC no es válido.',
            'domicilio_fiscal.size' => 'El código postal debe tener exactamente 5 dígitos.',
            'domicilio_fiscal.regex' => 'El código postal debe contener solo números.',
        ]);

        $data['rfc'] = strtoupper($data['rfc']);

        $rfcDuplicado = RazonSocialContacto::where('contacto_id', $rs->contacto_id)
            ->where('rfc', $data['rfc'])
            ->where('id', '!=', $id)
            ->exists();

        if ($rfcDuplicado) {
            return $this->respuestaError('Este RFC ya está registrado para este contacto.');
        }

        if (! empty($data['es_principal'])) {
            RazonSocialContacto::where('contacto_id', $rs->contacto_id)
                ->where('id', '!=', $id)
                ->update(['es_principal' => false]);
        }

        // Si el RFC cambió, el cliente registrado en factura.com quedó obsoleto:
        // se limpia el UID en caché para que se recree con el RFC correcto en la próxima emisión.
        if ($data['rfc'] !== $rs->rfc) {
            $data['factura_uid'] = null;
        }

        $rs->update($data);

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams: [$rs->contacto->familia_id],
            jsonData: ['razon_social' => $rs->fresh()],
            mensaje: "RFC {$rs->rfc} actualizado correctamente."
        );
    }

    /**
     * DELETE /familias/razon-social/{id}
     * Elimina permanentemente una razón social.
     */
    public function destroy(int $id)
    {
        $this->soloAdminCaja();

        $rs = RazonSocialContacto::with('contacto')->findOrFail($id);
        $familiaId = $rs->contacto->familia_id;
        $rfc = $rs->rfc;

        $rs->delete();

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams: [$familiaId],
            jsonData: [],
            mensaje: "RFC {$rfc} eliminado."
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
            routeParams: [$rs->contacto->familia_id],
            jsonData: ['razon_social' => $rs->fresh()],
            mensaje: "RFC {$rs->rfc} marcado como principal."
        );
    }

    /**
     * POST /familias/razon-social/{id}/activo
     * Habilita o deshabilita el RFC para facturar (no lo elimina).
     */
    public function toggleActivo(int $id)
    {
        $this->soloAdminCaja();

        $rs = RazonSocialContacto::with('contacto')->findOrFail($id);
        $anterior = $rs->toArray();

        $rs->update(['activo' => ! $rs->activo]);

        Auditoria::registrar('razon_social_contacto', $rs->id, 'toggle_activo', $anterior, $rs->fresh()->toArray());

        return $this->respuestaExito(
            redirectRoute: 'familias.show',
            routeParams: [$rs->contacto->familia_id],
            jsonData: ['razon_social' => $rs->fresh()],
            mensaje: $rs->activo
                ? "RFC {$rs->rfc} habilitado para facturar."
                : "RFC {$rs->rfc} deshabilitado para facturar."
        );
    }

    // ── Helper privado ───────────────────────────────────

    private function soloAdminCaja(): void
    {
        if (! in_array(auth()->user()->rol, ['administrador', 'caja'])) {
            abort(403, 'Solo administrador o caja pueden gestionar datos de facturación.');
        }
    }
}
