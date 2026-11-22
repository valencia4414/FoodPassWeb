<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    use HasFactory;

    protected $table = 'restaurantes';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
    ];

    protected $casts = [
        'creado_en'      => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function getActivoAttribute(): bool
    {
        return true;
    }

    public function platillos()
    {
        return $this->hasMany(Platillo::class, 'restaurante_id');
    }
}
