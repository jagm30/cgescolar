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
            return redirect(Auth::user()->rutaDashboard());
        }

        return view('login');
    }

    /** POST /login */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electronico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = Usuario::where('email', $request->email)
            ->where('activo', true)
            ->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password_hash)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales incorrectas.']);
        }

        Auth::login($usuario, $request->boolean('remember'));

        $usuario->update(['ultimo_acceso' => now()]);
        $usuario->save();

        if ($usuario->esInterno() && ! $usuario->ciclo_seleccionado_id) {
            $cicloActivo = CicloEscolar::activo()->first();

            if ($cicloActivo) {
                $usuario->update(['ciclo_seleccionado_id' => $cicloActivo->id]);
            }
        }

        Auditoria::registrar('usuario', $usuario->id, 'login', null, [
            'email' => $usuario->email,
            'rol' => $usuario->rol,
        ]);

        $request->session()->regenerate();

        return redirect()->intended($usuario->rutaDashboard());
    }

    /** POST /logout */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
