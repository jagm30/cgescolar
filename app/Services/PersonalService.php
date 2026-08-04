<?php

namespace App\Services;

use App\Models\Personal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PersonalService
{
    /**
     * Registra un nuevo empleado, opcionalmente subiendo su foto.
     */
    public function crear(array $datos): Personal
    {
        if (isset($datos['foto']) && $datos['foto'] instanceof UploadedFile) {
            $datos['foto_url'] = $datos['foto']->store('personal/fotos', 'public');
        }

        unset($datos['foto']);

        return Personal::create($datos);
    }

    /**
     * Actualiza los datos de un empleado, reemplazando la foto si se envía una nueva.
     */
    public function actualizar(Personal $empleado, array $datos): Personal
    {
        if (isset($datos['foto']) && $datos['foto'] instanceof UploadedFile) {
            if ($empleado->foto_url) {
                Storage::disk('public')->delete($empleado->foto_url);
            }
            $datos['foto_url'] = $datos['foto']->store('personal/fotos', 'public');
        }

        unset($datos['foto']);

        $empleado->update($datos);

        return $empleado->fresh();
    }

    /**
     * Elimina el empleado y su foto del disco.
     */
    public function eliminar(Personal $empleado): void
    {
        if ($empleado->foto_url) {
            Storage::disk('public')->delete($empleado->foto_url);
        }

        $empleado->delete();
    }
}
