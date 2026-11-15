<?php

namespace App\Http\Controllers;

use App\Models\Platillo;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuDigitalController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        // Restaurante principal (Cafetería SENA)
        $restaurante = Restaurante::first();

        // Filtro de categoría desde el tab activo
        $categoriaActiva = $request->get('categoria', 'todos');

        // Cargar platillos con su categoría
        $platillos = Platillo::with('categoriaRelacion')
            ->when($restaurante, fn($q) => $q->where('restaurante_id', $restaurante->id))
            ->orderBy('nombre')
            ->get();

        if ($categoriaActiva !== 'todos') {
            $platillos = $platillos->filter(function ($item) use ($categoriaActiva) {
                return strtolower($item->categoria) === strtolower($categoriaActiva);
            })->values();
        }

        // Platillo hero destacado
        $platilloHero = $platillos->firstWhere('disponible', true) ?? $platillos->first();

        return view('men_digital_foodpass.menu_digital', compact(
            'usuario',
            'restaurante',
            'platillos',
            'platilloHero',
            'categoriaActiva'
        ));
    }
}
