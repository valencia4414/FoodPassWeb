<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        // Restringir al rol admin exclusivamente a la gestión de platillos del día
        if ($usuario && $usuario->isAdmin()) {
            return redirect()->route('admin.platillos.index');
        }

        return view('dashboard_foodpass.dashboard', compact('usuario'));
    }
}
