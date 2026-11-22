<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaBeneficiario extends Model
{
    use HasFactory;

    protected $table = 'cargas_beneficiarios';

    protected $fillable = [
        'nombre_archivo_original',
        'ruta_archivo_privado',
        'formato',
        'total_filas_leidas',
        'aprendices_activados',
        'aprendices_inactivados',
        'errores_count',
        'errores_detalle',
        'ejecutado_por',
    ];

    protected $casts = [
        'errores_detalle' => 'array',
        'total_filas_leidas' => 'integer',
        'aprendices_activados' => 'integer',
        'aprendices_inactivados' => 'integer',
        'errores_count' => 'integer',
    ];
}
