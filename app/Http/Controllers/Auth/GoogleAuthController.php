<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirige al flujo de autenticación de Google con selector de cuentas (prompt=select_account).
     * Si no hay credenciales configuradas en .env, abre el selector interactivo en la vista de login.
     */
    public function redirect(Request $request)
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect', url('/auth/google/callback'));

        if (!empty($clientId)) {
            $state = Str::random(40);
            $request->session()->put('google_oauth_state', $state);

            $params = http_build_query([
                'client_id'             => $clientId,
                'redirect_uri'          => $redirectUri,
                'response_type'         => 'code',
                'scope'                 => 'openid email profile',
                'access_type'           => 'offline',
                'include_granted_scopes'=> 'true',
                'prompt'                => 'select_account', // Obliga a Google a mostrar el selector de cuentas del navegador
                'state'                 => $state,
            ]);

            return redirect("https://accounts.google.com/o/oauth2/v2/auth?" . $params);
        }

        // Si aún no se han configurado GOOGLE_CLIENT_ID en .env, abrir el selector visual de cuenta de Google
        return redirect()->route('login', ['google_modal' => 1]);
    }

    /**
     * Procesa la respuesta de Google OAuth callback.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('login')->with('error', 'Se canceló el inicio de sesión con Google: ' . $request->error);
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('login')->with('error', 'No se recibió el código de autorización de Google.');
        }

        try {
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');
            $redirectUri = config('services.google.redirect', url('/auth/google/callback'));

            // Intercambiar código por token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code'          => $code,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]);

            if ($tokenResponse->failed()) {
                return redirect()->route('login')->with('error', 'Error al autenticar con el servidor de Google.');
            }

            $accessToken = $tokenResponse->json('access_token');

            // Obtener perfil del usuario desde Google UserInfo API
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if ($userResponse->failed()) {
                return redirect()->route('login')->with('error', 'No fue posible obtener los datos del usuario de Google.');
            }

            $googleData = $userResponse->json();
            $email = strtolower(trim($googleData['email'] ?? ''));
            $name = trim($googleData['name'] ?? ($googleData['given_name'] ?? 'Usuario Google'));

            if (empty($email)) {
                return redirect()->route('login')->with('error', 'La cuenta de Google no tiene un correo electrónico válido asociado.');
            }

            return $this->iniciarOSincronizarUsuario($email, $name, $request);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en Google OAuth: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Ocurrió un error inesperado al conectar con Google.');
        }
    }

    /**
     * Permite seleccionar una cuenta de Google guardada en el navegador o vincular una nueva.
     */
    public function vincularDirecto(Request $request)
    {
        $request->validate([
            'email'  => 'required|email|max:150',
            'nombre' => 'nullable|string|max:150',
        ], [
            'email.required' => 'Ingresa tu correo de Google.',
            'email.email'    => 'Ingresa un formato de correo válido.',
        ]);

        $email = strtolower(trim($request->email));
        $name = trim($request->nombre ?: explode('@', $email)[0]);

        return $this->iniciarOSincronizarUsuario($email, $name, $request);
    }

    /**
     * Inicia sesión si el usuario ya existe, o lo registra automáticamente asignándole su rol.
     */
    protected function iniciarOSincronizarUsuario(string $email, string $name, Request $request)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            // Usuario ya registrado: verificar si está activo
            if (!$user->activo) {
                return redirect()->route('login')->with('error', 'Tu cuenta se encuentra inactiva. Contacta al administrador.');
            }

            Auth::login($user);
            if ($request->hasSession()) {
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'))
                    ->with('success', "¡Bienvenido, {$user->name}! Has iniciado sesión exitosamente con tu cuenta de Google ({$user->email}).");
            }

            return redirect()->intended(route('dashboard'));
        }

        // Si el usuario no existe, crearlo y vincularlo en la base de datos
        $nuevoUsuario = User::create([
            'nombre'        => $name,
            'email'         => $email,
            'password_hash' => Hash::make(Str::random(32)),
            'activo'        => 1,
        ]);

        // Asignar rol por defecto (4 = cliente / aprendiz)
        DB::table('rol_usuario')->insert([
            'usuario_id' => $nuevoUsuario->id,
            'rol_id'     => 4,
        ]);

        Auth::login($nuevoUsuario);
        if ($request->hasSession()) {
            $request->session()->regenerate();
            return redirect()->route('dashboard')
                ->with('success', "¡Cuenta creada y vinculada exitosamente con Google ({$email})!");
        }

        return redirect()->route('dashboard');
    }
}
