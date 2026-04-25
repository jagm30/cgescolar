<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Esta línea es la que manda

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

    public function uploadFondo(Request $request, $id)
    {
        $credencial = Credencial::findOrFail($id);

        if ($request->hasFile('fondo')) {
            $file = $request->file('fondo');
            
            if ($credencial->fondo_anverso) {
                Storage::disk('public')->delete($credencial->fondo_anverso);
            }
            
            $path = $file->store('credenciales/fondos', 'public');
            $credencial->update(['fondo_anverso' => $path]);

            return response()->json([
                'status' => 'success', 
                'path' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['message' => 'El servidor recibió la petición pero el archivo "fondo" no llegó.'], 422);
    }

    public function destroy($id)
    {
        $credencial = Credencial::findOrFail($id);
        if ($credencial->fondo_anverso) Storage::disk('public')->delete($credencial->fondo_anverso);
        if ($credencial->fondo_reverso) Storage::disk('public')->delete($credencial->fondo_reverso);
        $credencial->delete();

        return redirect()->route('credenciales.index')->with('success', 'Eliminado.');
    }
    public function updateConfig(Request $request, $id)
{
    // Esto es para que en la pestaña Network -> Response veas TODO lo que llegó
    // Si sigue saliendo el error anterior, es que el JS está mandando basura
    if (!$request->has('configuracion') && !$request->hasFile('fondo')) {
        return response()->json([
            'status' => 'error',
            'message' => 'El servidor recibió la petición VACÍA',
            'debug_recibido' => $request->all() 
        ], 422);
    }

    $credencial = Credencial::findOrFail($id);

    // Guardar Fondo
    if ($request->hasFile('fondo')) {
        $path = $request->file('fondo')->store('credenciales/fondos', 'public');
        $credencial->fondo_anverso = $path;
    }

    // Guardar JSON
    if ($request->has('configuracion')) {
        // A veces el FormData llega con comillas extra, aseguramos limpieza
        $datos = is_string($request->configuracion) ? json_decode($request->configuracion, true) : $request->configuracion;
        $credencial->config_anverso = $datos;
    }

    $credencial->save();

    return response()->json([
        'status' => 'success',
        'message' => '¡Guardado con éxito!',
        'debug' => $credencial->config_anverso
    ]);
}
}