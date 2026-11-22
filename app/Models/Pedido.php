<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'user_id',
        'restaurante_id',
        'estado',
        'fecha_pedido',
    ];

    protected $casts = [
        'fecha_pedido'  => 'datetime',
        'creado_en'     => 'datetime',
        'actualizado_en'=> 'datetime',
    ];

    public function getUserIdAttribute()
    {
        return $this->attributes['usuario_id'] ?? null;
    }

    public function setUserIdAttribute($value): void
    {
        $this->attributes['usuario_id'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function usuario()
    {
        return $this->user();
    }

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}
