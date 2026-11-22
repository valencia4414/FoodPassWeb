<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\BeneficiarioSena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('login_foodpass.register');
    }

    public function register(Request $request)
    {
        // 1. Normalización de cédula / documento (remover espacios, puntos, guiones)
        $rawCedula = $request->input('cedula') ?? $request->input('documento') ?? '';
        $cedulaNormalizada = preg_replace('/[^0-9]/', '', (string) $rawCedula);

        // Inyectar cédula normalizada al request para validaciones estándar
        $request->merge([
            'cedula' => $cedulaNormalizada,
            'email'  => strtolower(trim((string) $request->email)),
        ]);

        $request->validate([
            'cedula'   => 'required|numeric|digits_between:5,20',
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
        ], [
            'cedula.required'       => 'El número de cédula es obligatorio.',
            'cedula.numeric'        => 'La cédula debe contener únicamente números.',
            'cedula.digits_between' => 'La cédula debe tener entre 5 y 20 dígitos.',
            'name.required'         => 'El nombre completo es obligatorio.',
            'email.required'        => 'El correo electrónico es obligatorio.',
            'email.email'           => 'Ingresa un correo electrónico válido.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // 2. Consulta en el padrón oficial de beneficiarios SENA
        $beneficiarioPadron = BeneficiarioSena::where('numero_documento', $cedulaNormalizada)->first();

        // Si la cédula NO está registrada en la base de datos o no está activa
        if (!$beneficiarioPadron || !$beneficiarioPadron->activo) {
            $mensajeError = 'No haces parte del beneficio alimentario SENA. Tu número de cédula no se encuentra registrado en el padrón oficial de beneficiarios habilitados.';

            if ($request->expectsJson() || $request->is('api/*') || $request->is('auth/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $mensajeError,
                    'error'   => 'no_beneficiario',
                ], 403);
            }

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'cedula' => $mensajeError,
                ]);
        }

        // 3. Verificar que no exista ya una cuenta activa o registrada con esa cédula o correo
        $usuarioExistente = User::where('documento', $cedulaNormalizada)
            ->orWhere('email', $request->email)
            ->first();

        if ($usuarioExistente) {
            $mensajeDuplicado = $usuarioExistente->documento === $cedulaNormalizada
                ? 'Ya existe una cuenta registrada con este número de cédula. Por favor inicia sesión.'
                : 'Ya existe una cuenta registrada con este correo electrónico. Por favor inicia sesión.';

            if ($request->expectsJson() || $request->is('api/*') || $request->is('auth/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $mensajeDuplicado,
                    'error'   => 'usuario_duplicado',
                ], 409);
            }

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'cedula' => $mensajeDuplicado,
                ]);
        }

        // 4. Crear la cuenta del usuario
        $nombreUsuario = !empty($request->name) ? trim($request->name) : $beneficiarioPadron->nombre_completo;

        $user = User::create([
            'nombre'        => $nombreUsuario,
            'name'          => $nombreUsuario,
            'email'         => $request->email,
            'documento'     => $cedulaNormalizada,
            'password_hash' => Hash::make($request->password),
            'password'      => $request->password,
            'activo'        => true,
        ]);

        // 5. Asignación ESTRICTA y FORZADA en el backend del rol 'beneficiario'
        $rolBeneficiario = Role::firstOrCreate(
            ['nombre' => 'beneficiario'],
            ['descripcion' => 'Aprendiz Beneficiario SENA']
        );
        $user->roles()->sync([$rolBeneficiario->id]);

        // 6. Iniciar sesión si aplica sesión HTTP
        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        if ($request->expectsJson() || $request->is('api/*') || $request->is('auth/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Cuenta creada exitosamente con rol beneficiario.',
                'user'    => [
                    'id'        => $user->id,
                    'nombre'    => $user->nombre,
                    'email'     => $user->email,
                    'documento' => $user->documento,
                    'rol'       => 'beneficiario',
                ],
            ], 201);
        }

        return redirect()->route('dashboard')->with('success', '¡Cuenta creada con éxito! Tu beneficio alimentario SENA está activo.');
    }
}
