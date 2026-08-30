<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;

/**
 * View Composer para inyectar la configuración del portal del padre
 * en todas las vistas que usen el layout principal.
 *
 * Se registra en AppServiceProvider y se ejecuta automáticamente
 * en cada request. Permite controlar funciones del portal desde
 * la vista de Configuración sin necesidad de pasar variables manualmente.
 */
class SettingComposer
{
    public function compose(View $view): void
    {
        static $setting = null;

        if ($setting === null) {
            $setting = Setting::find(1);
        }

        $view->with([
            'portalFotosHabilitado' => (bool) $setting?->portal_fotos_habilitado,
            'portalAutorizadoRecogerHabilitado' => (bool) $setting?->portal_autorizado_recoger_habilitado,
        ]);
    }
}
