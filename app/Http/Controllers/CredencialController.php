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
    
    // Traemos al alumno con sus relaciones necesarias
    $alumno = Alumno::with(['inscripciones.grupo.grado', 'inscripciones.ciclo'])->findOrFail($alumno_id);

    // 1. Armamos el nombre (tu lógica actual está bien)
    $alumno->nombre_render = trim($alumno->nombre . ' ' . $alumno->ap_paterno . ' ' . ($alumno->ap_materno ?? ''));

    // 2. BUSCAMOS EL ID DEL CICLO ACTIVO (Esto es lo que faltaba)
    // Buscamos la inscripción que esté marcada como activa
    $inscripcion = $alumno->inscripciones->where('activo', 1)->first();

    // 3. Asignamos los datos académicos al objeto alumno para usarlos en la vista
    $alumno->grupo_render = $inscripcion ? $inscripcion->grupo->nombre : 'Sin Grupo';
    $alumno->grado_render = $inscripcion ? $inscripcion->grupo->grado->nombre : 'Sin Grado';
    
    // Si no hay inscripción activa, el ciclo lo sacará el Composer globalmente en la vista
    
    return view('credenciales.preview', compact('credencial', 'alumno'));
    }
}