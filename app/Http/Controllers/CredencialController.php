<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CredencialController extends Controller
{
    public function index()
    {
        $disenos = Credencial::orderBy('created_at', 'desc')->get();
        return view('credenciales.index', compact('disenos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'orientacion' => 'required|in:vertical,horizontal'
        ]);

        $credencial = Credencial::create([
            'nombre' => $request->nombre,
            'orientacion' => $request->orientacion,
            'config_anverso' => [],
            'config_reverso' => []
        ]);

        return redirect()->route('credenciales.edit', $credencial->id)
                        ->with('success', 'Plantilla creada correctamente.');
    }

    public function edit($id)
    {
        $diseno = Credencial::findOrFail($id);
        return view('credenciales.edit', compact('diseno'));
    }

    // Guarda la estructura JSON de las posiciones y estilos
    public function updateConfig(Request $request, $id)
    {
        $credencial = Credencial::findOrFail($id);
        $credencial->update([
            'config_anverso' => $request->configuracion
        ]);

        return response()->json(['status' => 'success', 'message' => '¡Diseño guardado!']);
    }

public function uploadFondo(Request $request, $id)
{
    $credencial = Credencial::findOrFail($id);

    // Usamos el nombre 'fondo' que mandamos en el append del JS
    if ($request->file('fondo')) {
        $file = $request->file('fondo');
        
        if ($credencial->fondo_anverso) {
            Storage::delete($credencial->fondo_anverso);
        }
        
        $path = $file->store('credenciales/fondos', 'public');
        $credencial->update(['fondo_anverso' => $path]);

        return response()->json([
            'status' => 'success', 
            'path' => asset('storage/' . $path)
        ]);
    }

    // Si llega aquí, es que $request->file('fondo') sigue siendo null
    return response()->json(['message' => 'El servidor recibió la petición pero el archivo "fondo" no llegó.'], 422);
}
    public function destroy($id)
    {
        $credencial = Credencial::findOrFail($id);
        if ($credencial->fondo_anverso) Storage::delete($credencial->fondo_anverso);
        if ($credencial->fondo_reverso) Storage::delete($credencial->fondo_reverso);
        $credencial->delete();

        return redirect()->route('credenciales.index')->with('success', 'Eliminado.');
    }
}