<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('documento')->nullable();
            $table->boolean('activo')->default(true);
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('idioma_preferido')->default('es');
            $table->string('foto_perfil')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
        });

        Schema::create('rol_usuario', function (Blueprint $table): void {
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('rol_id');
        });
    }

    public function test_registration_uses_the_foodpass_users_table(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Aprendiz de Prueba',
            'email' => 'aprendiz@example.test',
            'password' => 'FoodPass123!',
            'password_confirmation' => 'FoodPass123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('usuarios', [
            'email' => 'aprendiz@example.test',
            'nombre' => 'Aprendiz de Prueba',
        ]);
    }

    public function test_login_authenticates_using_the_password_hash_column(): void
    {
        $user = User::create([
            'name' => 'Aprendiz',
            'email' => 'aprendiz@example.test',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'missing@example.test',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_profile_saves_the_preferred_language(): void
    {
        $user = User::create([
            'name' => 'Aprendiz',
            'email' => 'aprendiz@example.test',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->actingAs($user)->put(route('perfil.update'), [
            'name' => 'Aprendiz',
            'email' => 'aprendiz@example.test',
            'idioma_preferido' => 'en',
        ]);

        $response->assertRedirect();
        $this->assertSame('en', DB::table('usuarios')->where('id', $user->id)->value('idioma_preferido'));
    }
}
