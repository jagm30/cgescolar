<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\CicloEscolar;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; 
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
        // 1. Validamos que vengan los datos, INCLUYENDO la respuesta de Turnstile
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => ['required'], // Campo que envía Cloudflare
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'cf-turnstile-response.required' => 'Por favor, completa la verificación de seguridad.',
        ]);

        // 2. Verificamos el token de Turnstile con los servidores de Cloudflare
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => env('TURNSTILE_SECRET_KEY'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        if (! $turnstileResponse->json('success')) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['cf-turnstile-response' => 'La validación de seguridad falló o expiró. Inténtalo de nuevo.']);
        }

        // 3. Si Turnstile lo aprueba, procedemos con la lógica normal de tu login...
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