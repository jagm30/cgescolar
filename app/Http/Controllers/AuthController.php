<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** GET /login */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route($this->rutaPorRol(Auth::user()->rol));
        }

        return view('auth.login');
    }

    /** POST /login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        // Buscar usuario activo
        $usuario = Usuario::where('email', $request->email)
            ->where('activo', true)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales incorrectas.']);
        }

        // Login con el guard por defecto usando el modelo Usuario
        Auth::login($usuario, $request->boolean('remember'));

        // Actualizar último acceso
        $usuario->update(['ultimo_acceso' => now()]);

        // Si es usuario interno y no tiene ciclo seleccionado, asignar el activo
        if ($usuario->esInterno() && !$usuario->ciclo_seleccionado_id) {
            $cicloActivo = CicloEscolar::activo()->first();
            if ($cicloActivo) {
                $usuario->update(['ciclo_seleccionado_id' => $cicloActivo->id]);
            }
        }

        Auditoria::registrar('usuario', $usuario->id, 'login', null, [
            'email' => $usuario->email,
            'rol'   => $usuario->rol,
        ]);

        $request->session()->regenerate();

        return redirect()->intended(route($this->rutaPorRol($usuario->rol)));
    }

    /** POST /logout */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Helper ───────────────────────────────────────────

    private function rutaPorRol(string $rol): string
    {
        return match($rol) {
            'administrador' => 'admin.dashboard',
            'caja'          => 'caja.dashboard',
            'recepcion'     => 'recepcion.dashboard',
            'padre'         => 'portal.dashboard',
            default         => 'login',
        };
    }
}
