<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Canje extends Model
{
    use HasFactory;

    protected $table = 'canjes';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'user_id',
        'beneficiario_sena_id',
        'restaurante_id',
        'monto',
        'fecha_canje',
        'periodo',
        'estado',
        'detalle',
        'codigo_qr',
        'expira_en',
    ];

    protected $casts = [
        'fecha_canje'   => 'date',
        'monto'         => 'decimal:2',
        'expira_en'     => 'datetime',
        'creado_en'     => 'datetime',
        'actualizado_en'=> 'datetime',
    ];

    /**
     * Compatibilidad entre usuario_id y user_id
     */
    public function getUserIdAttribute()
    {
        return $this->attributes['usuario_id'] ?? null;
    }

    public function setUserIdAttribute($value): void
    {
        $this->attributes['usuario_id'] = $value;
    }

    /**
     * Verifica si el QR de este canje aún es válido (no ha pasado la medianoche de su día).
     * La regla estricta: expira a las 12:00 AM (medianoche).
     */
    public function estaVigente(): bool
    {
        if (!$this->expira_en) {
            return false;
        }
        return Carbon::now()->lessThan($this->expira_en) && $this->estado !== 'expirado';
    }

    /**
     * Verifica si el QR ya fue utilizado.
     */
    public function fueCanjeado(): bool
    {
        return in_array($this->estado, ['exitoso', 'entregado']);
    }

    /**
     * Scope: canjes vigentes (no expirados).
     */
    public function scopeVigentes($query)
    {
        return $query->where('expira_en', '>', Carbon::now())
                     ->whereNotIn('estado', ['expirado', 'rechazado']);
    }

    /**
     * Scope: canjes del día actual.
     */
    public function scopeDeHoy($query)
    {
        return $query->whereDate('fecha_canje', today());
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con el restaurante
    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    // Relación con beneficiario SENA
    public function beneficiario()
    {
        return $this->belongsTo(BeneficiarioSena::class, 'beneficiario_sena_id');
    }
}
