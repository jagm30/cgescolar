<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use App\Models\Alumno;
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
    $credencial = Credencial::findOrFail($id);

    // 1. Guardar el Fondo
    if ($request->hasFile('fondo')) {
        $path = $request->file('fondo')->store('credenciales/fondos', 'public');
        $credencial->fondo_anverso = $path;
    }

    // 2. Guardar la Configuración
    if ($request->has('configuracion')) {
        $rawConfig = $request->input('configuracion');
        
        // Si es un string (que viene de FormData siempre es string), lo decodificamos
        $decoded = is_string($rawConfig) ? json_decode($rawConfig, true) : $rawConfig;
        
        // Asignamos directamente al modelo
        $credencial->config_anverso = $decoded;
    }

    // 3. Persistir cambios
    $credencial->save();

    return response()->json([
        'status' => 'success',
        'message' => '¡Guardado en BD!',
        'debug_lo_que_se_guardo' => $credencial->config_anverso
    ]);
    }

    public function preview($credencial_id, $alumno_id)
    {
    $credencial = Credencial::findOrFail($credencial_id);
    // Solo cargamos el alumno para que la vista tenga el objeto, pero no usaremos sus datos
    $alumno = Alumno::find($alumno_id) ?? new Alumno();

    return view('credenciales.preview', compact('credencial', 'alumno'));
    }
public function imprimirLote($credencial_id, $grupo_id)
{
    // 1. Buscamos el diseño de credencial
    $diseno = Credencial::findOrFail($credencial_id); 

    // 2. Buscamos a los alumnos y cargamos TODAS las relaciones en cadena
    $alumnos = Alumno::whereHas('inscripciones', function ($query) use ($grupo_id) {
        $query->where('grupo_id', $grupo_id);
    })->with(['inscripciones.grupo.grado.nivel'])->get();

    // 3. Retornamos la vista del DISEÑADOR (no la de impresion_lote)
    return view('credenciales.edit', [
        'diseno' => $diseno,
        'alumnos' => $alumnos,
        'impresionLote' => true // <-- Esta bandera activa el Modo Visualización que creamos
    ]);
}
}