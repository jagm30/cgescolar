<?php

namespace Database\Seeders;

use App\Models\CicloEscolar;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuariosInitSeeder extends Seeder
{
    /**
     * Usuarios a crear.
     * ap_paterno / ap_materno se usan solo para generar la contraseña.
     * nombre es el nombre completo de display (sin títulos académicos).
     */
    private const USUARIOS = [
        // ── Administradores ──────────────────────────────
        [
            'nombre'     => 'Carmen Guadalupe Villegas Perez',
            'email'      => 'cvillegas@vyce.com.mx',
            'rol'        => 'administrador',
            'ap_paterno' => 'Villegas',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Veronica Perez Zuñiga',
            'email'      => 'tesoreriagenkituxtla@gmail.com',
            'rol'        => 'administrador',
            'ap_paterno' => 'Perez',
            'ap_materno' => 'Zuniga',
        ],
        // ── Caja ────────────────────────────────────────
        [
            'nombre'     => 'Guadalupe Hernandez Perez',
            'email'      => 'auxiliargenkituxtla@gmail.com',
            'rol'        => 'caja',
            'ap_paterno' => 'Hernandez',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Paola Lizeth De Los Santos Solis',
            'email'      => 'auxiliarcontable2genki@gmail.com',
            'rol'        => 'caja',
            'ap_paterno' => 'Santos',
            'ap_materno' => 'Solis',
        ],
        // ── Recepción ────────────────────────────────────
        [
            'nombre'     => 'Julysa Miroslava Carrasco Grajales',
            'email'      => 'direccionprimaria.genkischool@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Carrasco',
            'ap_materno' => 'Grajales',
        ],
        [
            'nombre'     => 'Yanifiath Guizar Manzur',
            'email'      => 'dir.primariagenkischooltuxtla@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Guizar',
            'ap_materno' => 'Manzur',
        ],
        [
            'nombre'     => 'Nashiely Itzayana Espinosa Perez',
            'email'      => 'direcciongenkischool@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Espinosa',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Edy Nelson Somoza Marroquin',
            'email'      => 'direccionacademica.genkischool@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Somoza',
            'ap_materno' => 'Marroquin',
        ],
        [
            'nombre'     => 'Kareem Guadalupe Hernandez Perez',
            'email'      => 'coord.tec.k.genkischool1@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Hernandez',
            'ap_materno' => 'Perez',
        ],
        [
            'nombre'     => 'Landi Magally Morales Carpio',
            'email'      => 'coordinacionprimariagenki@gmail.com',
            'rol'        => 'recepcion',
            'ap_paterno' => 'Morales',
            'ap_materno' => 'Carpio',
        ],
        // ── Admisiones ───────────────────────────────────
        [
            'nombre'     => 'Ivonne Lizeth Martinez Velazquez',
            'email'      => 'admisionesgenkischool@gmail.com',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Martinez',
            'ap_materno' => 'Velazquez',
        ],
        [
            'nombre'     => 'Lidia Flores Pichardo',
            'email'      => 'coordinacion@genkischool.com.mx',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Flores',
            'ap_materno' => 'Pichardo',
        ],
        [
            'nombre'     => 'Karen Guadalupe Vazquez Vazquez',
            'email'      => 'psicopedagogico.genki@gmail.com',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Vazquez',
            'ap_materno' => 'Vazquez',
        ],
        [
            'nombre'     => 'Valeria Monserrath Mena Moreno',
            'email'      => 'psicopedagogicopreescolar.genki@gmail.com',
            'rol'        => 'admisiones',
            'ap_paterno' => 'Mena',
            'ap_materno' => 'Moreno',
        ],
    ];

    public function run(): void
    {
        $cicloId = CicloEscolar::activo()->value('id');

        // ── Vista previa ─────────────────────────────────
        $this->command->info("\n  Usuarios a crear:\n");
        $filas = collect(self::USUARIOS)->map(fn ($u) => [
            $u['nombre'],
            $u['email'],
            $u['rol'],
            $this->generarPassword($u['ap_paterno'], $u['ap_materno']),
        ])->toArray();

        $this->command->table(['Nombre', 'Correo', 'Rol', 'Contraseña'], $filas);

        if (! $this->command->confirm('¿Confirmas la creación de estos usuarios?', true)) {
            $this->command->warn('  Operación cancelada.');
            return;
        }

        // ── Creación ─────────────────────────────────────
        $creados   = [];
        $omitidos  = 0;

        foreach (self::USUARIOS as $datos) {
            if (Usuario::where('email', $datos['email'])->exists()) {
                $this->command->warn("  Omitido (ya existe): {$datos['email']}");
                $omitidos++;
                continue;
            }

            $password = $this->generarPassword($datos['ap_paterno'], $datos['ap_materno']);

            Usuario::create([
                'nombre'               => $datos['nombre'],
                'email'                => $datos['email'],
                'rol'                  => $datos['rol'],
                'password_hash'        => Hash::make($password),
                'activo'               => true,
                'ciclo_seleccionado_id' => $cicloId,
            ]);

            $creados[] = [$datos['nombre'], $datos['email'], $datos['rol'], $password];
        }

        // ── Resumen y exportación ─────────────────────────
        $this->command->info("\n  Resultado: " . count($creados) . " creados | {$omitidos} omitidos.\n");

        if (! empty($creados)) {
            $this->exportarCredenciales($creados);
        }
    }

    /**
     * Fórmula: [3 letras ap. paterno][3 letras ap. materno]2025!
     * Ejemplo: Villegas + Perez → VilPer2025!
     */
    private function generarPassword(string $apPaterno, string $apMaterno): string
    {
        $pat = ucfirst(strtolower(mb_substr($apPaterno, 0, 3)));
        $mat = ucfirst(strtolower(mb_substr($apMaterno, 0, 3)));

        return "{$pat}{$mat}2025!";
    }

    /**
     * Guarda un TXT con las credenciales en storage/app/credenciales_usuarios.txt
     */
    private function exportarCredenciales(array $usuarios): void
    {
        $lineas = ["CREDENCIALES INICIALES — " . now()->format('d/m/Y H:i') . "\n"];
        $lineas[] = str_repeat('=', 70);

        foreach ($usuarios as [$nombre, $email, $rol, $password]) {
            $lineas[] = "\nNombre   : {$nombre}";
            $lineas[] = "Correo   : {$email}";
            $lineas[] = "Rol      : {$rol}";
            $lineas[] = "Contraseña: {$password}";
            $lineas[] = str_repeat('-', 70);
        }

        $contenido = implode("\n", $lineas);
        Storage::disk('local')->put('credenciales_usuarios.txt', $contenido);

        $ruta = storage_path('app/credenciales_usuarios.txt');
        $this->command->info("  Credenciales exportadas a:\n  {$ruta}\n");
    }
}
