<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        // Buscamos el registro 1, si no existe mandamos un objeto vacío
        $setting = Setting::find(1) ?? new Setting(['nombre_escuela' => 'CGESCOLAR']);
        return view('settings.index', compact('setting'));
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
            $file->move(public_path('imgs_escuela'), $fileName);
            $setting->logo_ruta = $fileName;
        }

        $setting->save();

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}