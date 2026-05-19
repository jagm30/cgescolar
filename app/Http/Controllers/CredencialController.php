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

        // 1. Guardar el Fondo del ANVERSO
        if ($request->hasFile('fondo_anverso')) {
            if ($credencial->fondo_anverso) {
                Storage::disk('public')->delete($credencial->fondo_anverso);
            }
            $pathAnverso = $request->file('fondo_anverso')->store('credenciales/fondos', 'public');
            $credencial->fondo_anverso = $pathAnverso;
        }

        // 2. Guardar el Fondo del REVERSO
        if ($request->hasFile('fondo_reverso')) {
            if ($credencial->fondo_reverso) {
                Storage::disk('public')->delete($credencial->fondo_reverso);
            }
            $pathReverso = $request->file('fondo_reverso')->store('credenciales/fondos', 'public');
            $credencial->fondo_reverso = $pathReverso;
        }

        // 3. Guardar la Configuración del ANVERSO
        if ($request->has('config_anverso')) {
            $rawConfigAnverso = $request->input('config_anverso');
            $credencial->config_anverso = is_string($rawConfigAnverso) ? json_decode($rawConfigAnverso, true) : $rawConfigAnverso;
        }

        // 4. Guardar la Configuración del REVERSO
        if ($request->has('config_reverso')) {
            $rawConfigReverso = $request->input('config_reverso');
            $credencial->config_reverso = is_string($rawConfigReverso) ? json_decode($rawConfigReverso, true) : $rawConfigReverso;
        }

        // 5. Persistir cambios en la BD
        $credencial->save();

        return response()->json([
            'status' => 'success',
            'message' => '¡Guardado a doble cara con éxito!'
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
        })->with(['inscripciones.grupo.grado.nivel', 'familia.contactos'])->get(); // <-- Esta es la clave

        // 3. Retornamos la vista del DISEÑADOR (no la de impresion_lote)
        return view('credenciales.edit', [
            'diseno' => $diseno,
            'alumnos' => $alumnos,
            'impresionLote' => true // <-- Esta bandera activa el Modo Visualización que creamos
        ]);
    }
    public function imprimirIndividual($credencial_id, $alumno_id)
    {
        // 1. Buscamos el diseño de la credencial
        $diseno = Credencial::findOrFail($credencial_id);

        // 2. Buscamos a un solo alumno pero trayendo TODAS sus relaciones en cadena
        // (Inscripción -> Grupo -> Grado -> Nivel) para que no falle la vista
        $alumno = Alumno::with(['inscripciones.grupo.grado.nivel', 'familia.contactos'])->findOrFail($alumno_id);

        // 3. TRUCO DE INGENIERO: Envolvemos a ese único alumno en una "Colección"
        // Así la vista Blade cree que es un lote (de 1 solo elemento) y el @foreach funciona perfecto
        $alumnos = collect([$alumno]);

        return view('credenciales.edit', [
            'diseno' => $diseno,
            'alumnos' => $alumnos,
            'impresionLote' => true
        ]);
    }
}
