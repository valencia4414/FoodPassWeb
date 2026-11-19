<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        $totalPedidos = $usuario->pedidos()->count();
        $pedidosEstaSemana = $usuario->pedidos()
            ->where('creado_en', '>=', now()->startOfWeek())
            ->count();
        $canjesRecientes = $usuario->canjes()
            ->latest('creado_en')
            ->limit(5)
            ->get();

        return view('dashboard_foodpass.dashboard', compact(
            'usuario',
            'totalPedidos',
            'pedidosEstaSemana',
            'canjesRecientes'
        ));
    }
}
