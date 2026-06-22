<?php

namespace App\Http\Controllers;

use App\Enums\TipoPersonal;
use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use App\Services\PersonalService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\Request;

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
        $empleado = $this->service->crear($request->validated());

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
        $empleado = $this->service->actualizar($personal, $request->validated());

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

        $this->service->eliminar($personal);

        return $this->respuestaExito(
            redirectRoute: 'personal.index',
            mensaje: "Empleado '{$nombre}' eliminado correctamente."
        );
    }
}
