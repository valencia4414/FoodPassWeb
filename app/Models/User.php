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
        return null;
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

    public function isRestaurante(): bool
    {
        return $this->roles()->whereIn('nombre', ['restaurante', 'operador_restaurante', 'tecnico'])->exists();
    }

    public function isBeneficiario(): bool
    {
        return BeneficiarioSena::where('email', $this->email)
            ->where('activo', true)
            ->exists();
    }

    public function getEsBeneficiarioSenaAttribute(): bool
    {
        return $this->isBeneficiario();
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
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
