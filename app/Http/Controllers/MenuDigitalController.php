<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
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

        $categorias = Categoria::orderBy('nombre')->get();
        $categoriaActiva = (string) $request->query('categoria', 'todos');

        if ($categoriaActiva !== 'todos') {
            $categoriaId = filter_var($categoriaActiva, FILTER_VALIDATE_INT);
            $categoriaActiva = $categoriaId !== false && $categorias->contains('id', $categoriaId)
                ? (string) $categoriaId
                : 'todos';
        }

        // Cargar platillos con su categoría
        $platillos = Platillo::with('categoriaRelacion')
            ->when($restaurante, fn($q) => $q->where('restaurante_id', $restaurante->id))
            ->orderBy('nombre')
            ->get();

        return view('men_digital_foodpass.menu_digital', compact(
            'usuario',
            'restaurante',
            'categorias',
            'categoriaActiva',
            'platillos'
        ));
    }
}
