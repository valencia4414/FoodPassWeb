<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'name',
        'email',
        'password',
        'password_hash',
        'documento',
        'activo',
        'idioma_preferido',
        'remember_token',
    ];

    protected $hidden = [
        'password_hash',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    /**
     * Mapeo de autenticación para password_hash
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // No-op para compatibilidad con esquema V2 (sin columna remember_token)
    }

    /**
     * Accesores y mutadores para compatibilidad 'name' <-> 'nombre'
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['nombre'] ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['nombre'] = $value;
    }

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password_hash'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function getFotoPerfilAttribute()
    {
        return $this->attributes['foto_perfil'] ?? null;
    }

    /**
     * Relación de roles mediante rol_usuario
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rol_usuario', 'usuario_id', 'rol_id');
    }

    public function getRoleAttribute(): string
    {
        return $this->roles->first()?->nombre ?? 'cliente';
    }

    public function isAdmin(): bool
    {
        return $this->roles()->where('nombre', 'admin')->exists();
    }

    public function isInstructor(): bool
    {
        return $this->roles()->where('nombre', 'instructor')->exists();
    }

    public function isRestaurante(): bool
    {
        return $this->roles()->whereIn('nombre', ['restaurante', 'operador_restaurante', 'tecnico'])->exists();
    }

    /**
     * Valida si el usuario es beneficiario activo en el padrón oficial (beneficiarios_sena).
     * Instructores y administradores no reciben apoyo alimentario de aprendices.
     */
    public function isBeneficiario(): bool
    {
        if ($this->isAdmin() || $this->isInstructor() || $this->isRestaurante()) {
            return false;
        }

        return BeneficiarioSena::where(function ($q) {
                $q->where('email', $this->email);
                if (!empty($this->documento)) {
                    $q->orWhere('numero_documento', $this->documento);
                }
            })
            ->where('activo', true)
            ->exists();
    }

    public function getEsBeneficiarioSenaAttribute(): bool
    {
        return $this->isBeneficiario();
    }

    public function isCliente(): bool
    {
        return in_array($this->role, ['cliente', 'aprendiz']);
    }

    public function nombreRolVisible(): string
    {
        if ($this->isAdmin()) {
            return 'Administrador SENA';
        }
        if ($this->isInstructor()) {
            return 'Instructor SENA';
        }
        if ($this->isRestaurante()) {
            return 'Personal Cafetería';
        }
        if ($this->isBeneficiario()) {
            return 'Aprendiz Beneficiario (Activo)';
        }
        return 'Aprendiz SENA';
    }

    public function canjes()
    {
        return $this->hasMany(Canje::class, 'usuario_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }
}
