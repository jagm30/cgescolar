<?php

namespace App\Http\Controllers;

use App\Models\ConfigFiscal;
use App\Models\Setting;
use App\Services\FacturaComService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $setting      = Setting::find(1) ?? new Setting(['nombre_escuela' => 'CGESCOLAR']);
        $configFiscal = ConfigFiscal::first() ?? new ConfigFiscal();

        return view('settings.index', compact('setting', 'configFiscal'));
    }

    /** POST /configuracion/fiscal */
    public function updateFiscal(Request $request)
    {
        $data = $request->validate([
            'rfc'            => ['required', 'string', 'min:12', 'max:13'],
            'razon_social'   => ['required', 'string', 'max:300'],
            'regimen_fiscal' => ['required', 'string', 'max:10'],
            'serie'          => ['required', 'string', 'max:5'],
            'serie_id'       => ['nullable', 'integer', 'min:1'],
        ]);

        $data['rfc']   = strtoupper($data['rfc']);
        $data['serie'] = strtoupper($data['serie']);

        $config = ConfigFiscal::first();

        if ($config) {
            $config->update($data);
        } else {
            $data['folio_actual'] = 1;
            ConfigFiscal::create($data);
        }

        return back()->with('success', 'Configuración fiscal guardada correctamente.');
    }

    /**
     * GET /fiscal/verificar-series
     *
     * Consulta factura.com y devuelve las series configuradas en la cuenta.
     * Útil para confirmar que el serie_id en config_fiscal corresponde a una serie válida.
     */
    public function verificarSeries(FacturaComService $factura)
    {
        $config = ConfigFiscal::first();

        try {
            $series = $factura->listarSeries();

            return response()->json([
                'ok'       => true,
                'series'   => $series,
                'serie_id' => $config?->serie_id,
                'url'      => config('factura.url'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'mensaje' => $e->getMessage(),
                'url'     => config('factura.url'),
            ], 422);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'nombre_escuela' => 'required|string|max:255',
            'escuela_logo'   => 'nullable|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        // Buscamos el registro 1 o creamos uno nuevo
        $setting = Setting::findOrNew(1);

        if (!$setting->exists) {
            $setting->id = 1;
        }

        $setting->nombre_escuela = $request->nombre_escuela;

        if ($request->hasFile('escuela_logo')) {
            $file = $request->file('escuela_logo');
            $fileName = 'logo_reportes.png';
            $file->move(public_path('imgs_escuela/reportes/'), $fileName);
            $setting->logo_ruta = $fileName;
        }

        $setting->save();

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
