<?php

namespace App\Http\Controllers;

use App\Enums\TipoPersonal;
use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use App\Models\Usuario; // <-- Importado para la validación y actualización
use App\Services\PersonalService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // <-- Importado para enviar el correo
use App\Mail\AlertaCambioCorreoMail; // <-- Importado para la plantilla global

class PersonalController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly PersonalService $service) {}

    /** GET /personal */
    public function index(Request $request)
    {
        $query = Personal::query()
            ->when(
                $request->filled('buscar'),
                fn ($q) => $q->buscar($request->buscar)
            )
            ->when(
                $request->filled('activo'),
                fn ($q) => $q->where('activo', $request->boolean('activo'))
            )
            ->when(
                $request->filled('tipo'),
                fn ($q) => $q->where('tipo', $request->tipo)
            );

        $totales = (clone $query)->get();

        $porPagina = in_array((int) $request->input('perPage', 10), [5, 10, 25, 50, 100])
            ? (int) $request->input('perPage', 10)
            : 10;

        $empleados = $query->orderBy('ap_paterno')->orderBy('nombre')
            ->paginate($porPagina)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json($empleados);
        }

        return view('personal.index', [
            'empleados' => $empleados,
            'totales'   => $totales,
            'tipos'     => TipoPersonal::cases(),
        ]);
    }

    /** GET /personal/create */
    public function create()
    {
        return view('personal.create', ['tipos' => TipoPersonal::cases()]);
    }

    /** POST /personal */
    public function store(StorePersonalRequest $request)
    {
        $datos = $request->validated();
        
        // Establecemos si tendrá acceso al sistema (va a la cola de pendientes)
        $datos['tiene_acceso_sistema'] = $request->boolean('tiene_acceso_sistema');
        
        $empleado = $this->service->crear($datos);

        return $this->respuestaExito(
            redirectRoute: 'personal.show',
            routeParams: [$empleado->id],
            jsonData: ['empleado' => $empleado],
            mensaje: "Empleado '{$empleado->nombre_completo}' registrado correctamente.",
            jsonStatus: 201
        );
    }

    /** GET /personal/{personal} */
    public function show(Personal $personal)
    {
        return view('personal.show', ['empleado' => $personal]);
    }

    /** GET /personal/{personal}/edit */
    public function edit(Personal $personal)
    {
        return view('personal.edit', [
            'empleado' => $personal,
            'tipos'    => TipoPersonal::cases(),
        ]);
    }

    /** PUT /personal/{personal} */
    public function update(UpdatePersonalRequest $request, Personal $personal)
    {
        $datos = $request->validated();
        
        $tieneAcceso = $request->boolean('tiene_acceso_sistema');
        $datos['tiene_acceso_sistema'] = $tieneAcceso;

        // 1. Si le quitan el acceso y tenía usuario, desactivamos al usuario por seguridad
        if (!$tieneAcceso && $personal->usuario_id) {
            $personal->usuario()->update(['activo' => false]);
        }

        // 2. SINCRONIZACIÓN CON LA TABLA USUARIO Y ALERTA DE SEGURIDAD
        if ($personal->usuario_id) {
            $usuarioUpdate = [];

            if ($tieneAcceso && empty($datos['email'])) {
                return response()->json([
                    'message' => 'No puedes dejar el correo en blanco porque este empleado tiene una cuenta activa en el sistema.',
                    'errors' => ['email' => ['El correo es obligatorio para mantener el acceso al sistema.']]
                ], 422);
            }

            // Detectar si cambió el correo
            if (!empty($datos['email']) && $personal->email !== $datos['email']) {
                $correoOcupado = Usuario::where('email', $datos['email'])
                    ->where('id', '!=', $personal->usuario_id)
                    ->exists();

                if ($correoOcupado) {
                    return response()->json([
                        'message' => 'El correo electrónico ya está registrado en otra cuenta del sistema.',
                        'errors' => ['email' => ['El correo ya está en uso.']]
                    ], 422);
                }
                
                $usuarioUpdate['email'] = $datos['email'];

                // Notificar a los administradores
                $admins = Usuario::where('rol', 'administrador')->where('activo', true)->pluck('email');
                
                if ($admins->isNotEmpty()) {
                    try {
                        // Extraemos el tipo (Ej: "Docente", "Administrativo")
                        $tipoEtiqueta = is_object($personal->tipo) ? $personal->tipo->value : ($datos['tipo'] ?? 'Empleado');
                        
                        Mail::to($admins)->send(
                            new AlertaCambioCorreoMail(
                                trim($personal->nombre . ' ' . $personal->ap_paterno),
                                $personal->email,
                                $datos['email'],
                                auth()->user()->nombre, 
                                'Personal (' . ucfirst($tipoEtiqueta) . ')' // Identificador dinámico
                            )
                        );
                    } catch (\Exception $e) {
                        // Falla silenciosa
                    }
                }
            }

            // Sincronizar Nombre (Si corrigieron su nombre, actualizar el perfil)
            $nuevoNombreCompleto = trim($datos['nombre'] . ' ' . ($datos['ap_paterno'] ?? ''));
            $nombreAnterior = trim($personal->nombre . ' ' . $personal->ap_paterno);
            
            if ($nuevoNombreCompleto !== $nombreAnterior) {
                $usuarioUpdate['nombre'] = $nuevoNombreCompleto;
            }

            // Aplicamos los cambios en la tabla usuario si hubo alguno
            if (!empty($usuarioUpdate)) {
                $personal->usuario()->update($usuarioUpdate);
            }
        }

        // 3. Procedemos a guardar la actualización del empleado mediante el servicio
        $empleado = $this->service->actualizar($personal, $datos);

        return $this->respuestaExito(
            redirectRoute: 'personal.show',
            routeParams: [$empleado->id],
            jsonData: ['empleado' => $empleado],
            mensaje: "Empleado '{$empleado->nombre_completo}' actualizado correctamente."
        );
    }

    /** DELETE /personal/{personal} */
    public function destroy(Personal $personal)
    {
        $nombre = $personal->nombre_completo;

        if ($personal->usuario_id) {
            $personal->usuario()->update(['activo' => false]);
        }

        $this->service->eliminar($personal);

        return $this->respuestaExito(
            redirectRoute: 'personal.index',
            mensaje: "Empleado '{$nombre}' eliminado correctamente."
        );
    }
}