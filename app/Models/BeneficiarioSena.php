<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficiarioSena extends Model
{
    use HasFactory;

    protected $table = 'beneficiarios_sena';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'numero_documento',
        'nombre_completo',
        'email',
        'activo',
        'sincronizado_at',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'sincronizado_at' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];
}
