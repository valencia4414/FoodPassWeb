<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $cuentasGoogleSugeridas = \App\Models\User::where('activo', 1)->take(4)->get();
        return view('login_foodpass.login', compact('cuentasGoogleSugeridas'));
    }

    public function login(Request $request, OtpService $otpService)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'Mínimo 6 caracteres.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Validar credenciales capturando posibles errores de formato de hash
        $isValid = false;
        try {
            $isValid = Auth::validate($credentials);
        } catch (\Throwable $e) {
            $isValid = false;
        }

        if (!$isValid) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales incorrectas.']);
        }

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        // RF21 — MFA para administradores solo si está explícitamente habilitado en .env
        if ($user && $user->isAdmin() && env('ENABLE_ADMIN_OTP', false)) {
            try {
                $otpService->generateAndSend($user);

                $request->session()->put('otp_user_id', $user->id);
                $request->session()->put('otp_remember', $remember);

                return redirect()->route('otp.verify')
                    ->with('status', 'Enviamos un código de verificación a tu correo.');
            } catch (\Throwable $e) {
                // Si falla el envío de correo OTP en local, iniciar sesión directamente
            }
        }

        // Iniciar sesión
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
