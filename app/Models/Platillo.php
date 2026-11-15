<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Platillo extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('menu_del_dia'));
        static::deleted(fn () => Cache::forget('menu_del_dia'));
    }

    protected $table = 'platillos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'restaurante_id',
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'ingredientes',
        'imagen',
        'disponible',
        'stock',
    ];

    protected $casts = [
        'precio'     => 'decimal:2',
        'disponible' => 'boolean',
        'stock'      => 'integer',
        'creado_en'  => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function categoriaRelacion()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Accesor para mantener compatibilidad con vistas existentes
     */
    public function getCategoriaAttribute()
    {
        return $this->categoriaRelacion?->nombre ?? 'plato_fuerte';
    }

    public function decrementarStock(int $cantidad = 1): void
    {
        if (is_null($this->stock)) {
            return;
        }

        $nuevoStock = max(0, $this->stock - $cantidad);
        $this->stock = $nuevoStock;

        if ($nuevoStock <= 0) {
            $this->disponible = false;
        }

        $this->save();
    }
}
